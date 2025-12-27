<?php
// churn_functions.php

function getChurnPredictionDashboardData($db) {
    $data = [];
    
    // 获取流失预测概览
    $query = "
        SELECT 
            risk_level,
            COUNT(*) as count,
            AVG(churn_probability) as avg_probability
        FROM customer_churn_predictions
        WHERE last_prediction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY risk_level
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data['overview'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取高风险客户
    $query = "
        SELECT 
            ccp.*,
            u.first_name,
            u.last_name,
            u.email,
            cf.days_since_last_order,
            cf.order_count,
            cf.total_spent
        FROM customer_churn_predictions ccp
        JOIN wpov_fc_subscribers u ON ccp.user_id = u.id
        LEFT JOIN customer_features cf ON ccp.customer_id = cf.customer_id
        WHERE ccp.risk_level = 'high'
        AND ccp.last_prediction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY ccp.churn_probability DESC
        LIMIT 20
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data['high_risk_customers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $data;
}

function exportChurnData($db, $format = 'csv') {
    $query = "
        SELECT 
            ccp.customer_id,
            u.email,
            u.first_name,
            u.last_name,
            ccp.churn_probability,
            ccp.risk_level,
            cf.days_since_last_order,
            cf.order_count,
            cf.total_spent,
            cf.months_as_customer,
            ccp.last_prediction_date
        FROM customer_churn_predictions ccp
        JOIN wpov_fc_subscribers u ON ccp.user_id = u.id
        LEFT JOIN customer_features cf ON ccp.customer_id = cf.customer_id
        WHERE ccp.last_prediction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY ccp.risk_level, ccp.churn_probability DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format == 'csv') {
        // 生成CSV文件
        $filename = 'churn_predictions_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($data[0]));
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    return $data;
}

function getCustomerChurnInsights($db, $customerId) {
    $query = "
        SELECT 
            cf.*,
            ccp.*,
            (SELECT COUNT(*) FROM orders WHERE user_id = cf.user_id AND status != 'cancelled') as total_orders,
            (SELECT SUM(total_amount) FROM orders WHERE user_id = cf.user_id AND status != 'cancelled') as lifetime_value,
            (SELECT MAX(created_at) FROM orders WHERE user_id = cf.user_id) as last_order_date
        FROM customer_features cf
        LEFT JOIN customer_churn_predictions ccp ON cf.customer_id = ccp.customer_id
        WHERE cf.customer_id = :customer_id
        ORDER BY ccp.last_prediction_date DESC
        LIMIT 1
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':customer_id' => $customerId]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>