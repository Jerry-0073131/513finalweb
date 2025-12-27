<?php
// pages/customers.php

// 确保会话开始
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config/database.php');

// 检查登录状态
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 检查是否为管理员
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

try {
    // 连接到数据库 - 使用新的静态方法
    $pdo = Database::getPDO();
    
    // 查询 FluentCRM 订阅者
    $query = "SELECT 
                id, 
                first_name, 
                last_name, 
                email, 
                phone, 
                status,
                created_at 
              FROM wpov_fc_subscribers 
              ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database query failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List - TechPioneer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
        }
        
        .customers-container {
            max-width: 1200px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .action-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .export-btn {
            background: linear-gradient(to right, #4CAF50, #45a049);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(76,175,80,0.2);
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76,175,80,0.3);
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }
        
        .search-box input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e6ef;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .search-box button {
            padding: 12px 24px;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .search-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.3);
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .customers-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .customers-table th {
            background: linear-gradient(to right, #4a6fa5, #2c3e50);
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .customers-table th:first-child {
            border-top-left-radius: 10px;
        }
        
        .customers-table th:last-child {
            border-top-right-radius: 10px;
        }
        
        .customers-table td {
            padding: 16px 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .customers-table tr:hover {
            background-color: #f8fafc;
        }
        
        .customers-table tr:last-child td {
            border-bottom: none;
        }
        
        .customer-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
        }
        
        .status-subscribed {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .status-unsubscribed {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .no-data p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
        }
        
        .btn-view {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #2563eb;
        }
        
        .btn-edit {
            background-color: #10b981;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #059669;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .pagination button {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e0e6ef;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: #4a5568;
            transition: all 0.3s;
        }
        
        .pagination button:hover:not(:disabled) {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        #pageInfo {
            font-weight: 600;
            color: #4a5568;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        @media (max-width: 768px) {
            .customers-container {
                margin-top: 60px;
                padding: 0 15px;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .customers-table {
                display: block;
                overflow-x: auto;
            }
            
            .customers-table th,
            .customers-table td {
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="customers-container">
        <div class="page-header">
            <h1>Customer List</h1>
            <p>All registered subscribers from FluentCRM</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="action-bar">
            <a href="export_customers.php" class="export-btn">
                <span>📊</span> Export to CSV
            </a>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search customers by name, email, or phone...">
                <button onclick="searchCustomers()">Search</button>
            </div>
        </div>
        
        <div class="table-container">
            <table class="customers-table" id="customersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subscribers)): ?>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($subscriber['id']); ?></td>
                                <td><?php echo htmlspecialchars($subscriber['first_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($subscriber['last_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($subscriber['email']); ?>">
                                        <?php echo htmlspecialchars($subscriber['email']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($subscriber['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                        $status = strtolower($subscriber['status'] ?? 'unknown');
                                        $statusClass = '';
                                        switch ($status) {
                                            case 'subscribed':
                                                $statusClass = 'status-subscribed';
                                                break;
                                            case 'unsubscribed':
                                                $statusClass = 'status-unsubscribed';
                                                break;
                                            default:
                                                $statusClass = 'status-pending';
                                        }
                                    ?>
                                    <span class="customer-status <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($subscriber['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_customer.php?id=<?php echo $subscriber['id']; ?>" class="btn-small btn-view">View</a>
                                        <?php if ($isAdmin): ?>
                                            <a href="edit_customer.php?id=<?php echo $subscriber['id']; ?>" class="btn-small btn-edit">Edit</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                <p>No customers found in the database.</p>
                                <a href="javascript:location.reload()" style="color: #667eea; text-decoration: none;">↻ Refresh</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($subscribers) && count($subscribers) > 10): ?>
            <div class="pagination">
                <button id="prevPage" onclick="changePage(-1)">Previous</button>
                <span id="pageInfo">Page 1 of 1</span>
                <button id="nextPage" onclick="changePage(1)">Next</button>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include('../includes/footer.php'); ?>
    
    <script>
        // 分页配置
        let currentPage = 1;
        const rowsPerPage = 10;
        let currentData = [];
        
        // 初始化数据
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('#customersTable tbody tr');
            currentData = Array.from(rows).filter(row => row.style.display !== 'none');
            
            if (currentData.length > rowsPerPage) {
                updatePagination();
            }
        });
        
        // 搜索功能
        function searchCustomers() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('customersTable');
            const tr = table.getElementsByTagName('tr');
            
            let visibleRows = 0;
            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const textValue = td[j].textContent || td[j].innerText;
                        if (textValue.toLowerCase().includes(filter)) {
                            found = true;
                            break;
                        }
                    }
                }
                
                if (found) {
                    tr[i].style.display = '';
                    visibleRows++;
                } else {
                    tr[i].style.display = 'none';
                }
            }
            
            const allRows = document.querySelectorAll('#customersTable tbody tr');
            currentData = Array.from(allRows).filter(row => row.style.display !== 'none');
            currentPage = 1;
            
            if (currentData.length > rowsPerPage) {
                updatePagination();
            } else {
                currentData.forEach(row => row.style.display = '');
                document.querySelector('.pagination')?.style.display = 'none';
            }
        }
        
        // 分页功能
        function updatePagination() {
            const pageCount = Math.ceil(currentData.length / rowsPerPage);
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            
            document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${pageCount}`;
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === pageCount;
            
            currentData.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
        }
        
        // 切换页面
        function changePage(direction) {
            const pageCount = Math.ceil(currentData.length / rowsPerPage);
            const newPage = currentPage + direction;
            
            if (newPage >= 1 && newPage <= pageCount) {
                currentPage = newPage;
                updatePagination();
            }
        }
        
        // 按回车键搜索
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchCustomers();
            }
        });
    </script>
</body>
</html>