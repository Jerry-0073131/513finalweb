<?php
require_once '../config/config.php';
require_once '../includes/header.php';
?>

<section class="about-page">
    <div class="container">
        <h1>About TechPioneer</h1>
        
        <div class="about-content">
            <div class="about-section">
                <h2>Our Story</h2>
                <p>TechPioneer was founded in 2020 with a simple mission: to make cutting-edge technology accessible to everyone. We believe that technology should enhance lives, not complicate them.</p>
                <p>What started as a small online store has grown into a trusted destination for electronics enthusiasts, professionals, and everyday consumers alike.</p>
            </div>
            
            <div class="about-section">
                <h2>Our Mission</h2>
                <p>We're committed to providing our customers with the latest and most reliable electronics at competitive prices. Our team carefully selects each product in our inventory to ensure quality, performance, and value.</p>
            </div>
            
            <!-- 新增地图部分 - 使用静态图片 -->
            <div class="about-section">
                <h2>Our Location</h2>
                <p>Visit our headquarters or get in touch with our team. We're proud to serve customers from our centrally located office.</p>
                
                <div class="location-container">
                    <div class="map-wrapper">
                        <!-- 静态地图图片 -->
                        <div class="map-image-container">
                            <!-- 你可以在这里替换为你的地图图片URL -->
                            <img 
                                src="https://pica.zhimg.com/v2-0d9257d8fc02975b92f9a63989aa3e28_r.jpg" 
                                alt="TechPioneer Headquarters Location Map" 
                                class="map-image"
                                onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" width=\"800\" height=\"450\" viewBox=\"0 0 800 450\"%3E%3Crect width=\"800\" height=\"450\" fill=\"%23f5f5f5\"%3E%3C/rect%3E%3Cg transform=\"translate(400 225)\"%3E%3Ccircle r=\"30\" fill=\"%23e74c3c\" fill-opacity=\"0.7\"%3E%3C/circle%3E%3Cpath d=\"M -20 0 L 20 0 M 0 -20 L 0 20\" stroke=\"white\" stroke-width=\"3\"%3E%3C/path%3E%3C/g%3E%3Ctext x=\"400\" y=\"320\" text-anchor=\"middle\" font-family=\"Arial, sans-serif\" font-size=\"18\" fill=\"%23333\"%3ETechPioneer Headquarters%3C/text%3E%3Ctext x=\"400\" y=\"350\" text-anchor=\"middle\" font-family=\"Arial, sans-serif\" font-size=\"14\" fill=\"%23666\"%3E123 Tech Avenue, New York, NY 10001%3C/text%3E%3C/svg%3E';"
                            >
                            
                            <!-- 点击查看地图的提示 -->
                            <div class="map-overlay">
                                <a href="https://www.google.com/maps/search/?api=1&query=123+Tech+Avenue+New+York+NY+10001" 
                                   target="_blank" 
                                   rel="noopener"
                                   class="map-link-overlay">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>Click to view interactive map</span>
                                </a>
                            </div>
                        </div>
                        
                        <!-- 地图链接 -->
                        <div style="text-align: center; margin-top: 10px; font-size: 12px; color: #666;">
                            <a href="https://www.google.com/maps/search/?api=1&query=123+Tech+Avenue+New+York+NY+10001" 
                               target="_blank" 
                               rel="noopener">
                                <i class="fas fa-map-marker-alt"></i> View on Google Maps
                            </a>
                            &nbsp;|&nbsp;
                            <a href="https://www.openstreetmap.org/?mlat=40.70555&amp;mlon=-73.98784#map=17/40.70555/-73.98784" 
                               target="_blank" 
                               rel="noopener">
                                <i class="fas fa-map"></i> View on OpenStreetMap
                            </a>
                        </div>
                    </div>
                    
                    <div class="location-details">
                        <div class="address-card">
                            <h3><i class="fas fa-map-marker-alt"></i> Headquarters</h3>
                            <p><strong>Address:</strong><br>
                            123 Tech Avenue, Suite 500<br>
                            New York, NY 10001<br>
                            United States</p>
                            
                            <p><strong>Contact:</strong><br>
                            Phone: +1 (555) 123-4567<br>
                            Email: info@techpioneer.com</p>
                            
                            <p><strong>Business Hours:</strong><br>
                            Monday - Friday: 9:00 AM - 6:00 PM<br>
                            Saturday: 10:00 AM - 4:00 PM<br>
                            Sunday: Closed</p>
                            
                            <!-- 直接链接到Google Maps获取导航 -->
                            <a href="https://maps.google.com/?q=123+Tech+Avenue+New+York+NY+10001" 
                               target="_blank" 
                               rel="noopener"
                               class="map-link-btn">
                                <i class="fas fa-directions"></i> Get Directions
                            </a>
                            
                            <!-- 备选链接到Apple Maps -->
                            <a href="https://maps.apple.com/?q=123+Tech+Avenue+New+York+NY+10001" 
                               target="_blank" 
                               rel="noopener"
                               class="map-link-btn apple-maps-btn" style="margin-left: 10px;">
                                <i class="fab fa-apple"></i> Apple Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="about-section">
                <h2>Why Choose TechPioneer?</h2>
                <div class="features-grid">
                    <div class="feature">
                        <h3>Quality Products</h3>
                        <p>We source only from reputable manufacturers and distributors.</p>
                    </div>
                    <div class="feature">
                        <h3>Competitive Prices</h3>
                        <p>We work directly with suppliers to offer the best prices.</p>
                    </div>
                    <div class="feature">
                        <h3>Expert Support</h3>
                        <p>Our team is knowledgeable and ready to help with any questions.</p>
                    </div>
                    <div class="feature">
                        <h3>Fast Shipping</h3>
                        <p>We process and ship orders quickly to get you your products fast.</p>
                    </div>
                </div>
            </div>
            
            <div class="about-section">
                <h2>Our Team</h2>
                <p>Behind TechPioneer is a dedicated team of technology enthusiasts, customer service professionals, and logistics experts. We're passionate about technology and committed to providing an exceptional shopping experience.</p>
            </div>
        </div>
    </div>
</section>

<style>
/* 地图容器样式 */
.location-container {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-top: 20px;
}

.map-wrapper {
    flex: 1;
    min-width: 300px;
}

.location-details {
    flex: 1;
    min-width: 300px;
}

.address-card {
    background: #f9f9f9;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.address-card h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 1.3rem;
}

.address-card h3 i {
    color: #e74c3c;
    margin-right: 10px;
}

.address-card p {
    margin-bottom: 15px;
    line-height: 1.6;
}

.map-link-btn {
    display: inline-block;
    background: #4285f4;
    color: white;
    padding: 10px 15px;
    border-radius: 4px;
    text-decoration: none;
    margin-top: 15px;
    transition: background 0.3s;
    font-size: 14px;
}

.map-link-btn:hover {
    background: #3367d6;
    color: white;
}

.apple-maps-btn {
    background: #000;
}

.apple-maps-btn:hover {
    background: #333;
}

.map-link-btn i {
    margin-right: 5px;
}

/* 地图图片样式 */
.map-image-container {
    position: relative;
    width: 100%;
    height: 450px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.map-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.map-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.map-image-container:hover .map-overlay {
    opacity: 1;
}

.map-link-overlay {
    background: rgba(255, 255, 255, 0.9);
    padding: 12px 20px;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.map-link-overlay:hover {
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* 响应式设计 */
@media (max-width: 768px) {
    .location-container {
        flex-direction: column;
    }
    
    .map-wrapper, .location-details {
        min-width: 100%;
    }
    
    .map-image-container {
        height: 350px;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>