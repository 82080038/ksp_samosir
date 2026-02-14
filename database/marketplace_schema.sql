-- Marketplace Platform Database Schema for KSP Samosir

-- Marketplace products (extensions of existing products)
CREATE TABLE IF NOT EXISTS marketplace_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT, -- Reference to main products table
    seller_id INT NOT NULL, -- Member or cooperative selling
    seller_type ENUM('member', 'cooperative', 'vendor') DEFAULT 'member',
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    price DECIMAL(12,2) NOT NULL,
    original_price DECIMAL(12,2),
    quantity_available INT NOT NULL DEFAULT 0,
    condition_status ENUM('new', 'used', 'refurbished') DEFAULT 'new',
    location VARCHAR(100),
    images JSON, -- Array of image URLs
    tags JSON, -- Array of tags for search
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'sold_out', 'removed') DEFAULT 'active',
    view_count INT DEFAULT 0,
    favorite_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_seller (seller_id, seller_type),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_featured (featured),
    INDEX idx_created_at (created_at)
);

-- Marketplace categories
CREATE TABLE IF NOT EXISTS marketplace_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT,
    icon VARCHAR(50),
    sort_order INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_parent (parent_id),
    INDEX idx_active (active),
    INDEX idx_sort (sort_order)
);

-- Marketplace transactions (separate from cooperative transactions)
CREATE TABLE IF NOT EXISTS marketplace_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_number VARCHAR(20) UNIQUE NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    platform_fee DECIMAL(10,2) DEFAULT 0,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_address TEXT,
    shipping_method VARCHAR(50),
    tracking_number VARCHAR(100),
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    delivered_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_buyer (buyer_id),
    INDEX idx_seller (seller_id),
    INDEX idx_product (product_id),
    INDEX idx_status (status),
    INDEX idx_payment (payment_status),
    INDEX idx_created_at (created_at)
);

-- Product reviews and ratings
CREATE TABLE IF NOT EXISTS marketplace_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    product_id INT NOT NULL,
    seller_id INT NOT NULL,
    rating DECIMAL(3,1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    comment TEXT,
    images JSON, -- Review images
    verified_purchase BOOLEAN DEFAULT FALSE,
    helpful_votes INT DEFAULT 0,
    reported BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'hidden', 'removed') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES marketplace_transactions(id),
    INDEX idx_product (product_id),
    INDEX idx_seller (seller_id),
    INDEX idx_reviewer (reviewer_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- User favorites/wishlist
CREATE TABLE IF NOT EXISTS marketplace_favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, product_id),
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
);

-- Shopping cart
CREATE TABLE IF NOT EXISTS marketplace_cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
);

-- Digital products (insurance, investment products, etc.)
CREATE TABLE IF NOT EXISTS digital_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_type ENUM('insurance', 'investment', 'loan_product', 'savings_plan') NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    provider_id INT, -- Insurance company, investment firm, etc.
    terms_conditions TEXT,
    pricing_model JSON, -- Flexible pricing structure
    eligibility_criteria JSON,
    features JSON,
    documents_required JSON,
    status ENUM('active', 'inactive', 'coming_soon') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (product_type),
    INDEX idx_provider (provider_id),
    INDEX idx_status (status)
);

-- B2B Integration partners
CREATE TABLE IF NOT EXISTS b2b_partners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    partner_name VARCHAR(200) NOT NULL,
    partner_type VARCHAR(50) NOT NULL,
    api_endpoint VARCHAR(500),
    api_key VARCHAR(255),
    shared_services JSON,
    integration_status ENUM('pending', 'testing', 'active', 'inactive') DEFAULT 'pending',
    last_sync DATETIME,
    contact_person VARCHAR(100),
    contact_email VARCHAR(100),
    contract_start DATE,
    contract_end DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (partner_type),
    INDEX idx_status (integration_status),
    INDEX idx_last_sync (last_sync)
);

-- Loyalty program
CREATE TABLE IF NOT EXISTS loyalty_program (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    current_points INT DEFAULT 0,
    total_points_earned INT DEFAULT 0,
    total_points_redeemed INT DEFAULT 0,
    tier ENUM('bronze', 'silver', 'gold', 'platinum') DEFAULT 'bronze',
    tier_upgrade_date DATE,
    tier_expiry_date DATE,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_member (member_id),
    INDEX idx_tier (tier),
    INDEX idx_status (status)
);

-- Loyalty points transactions
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    transaction_type ENUM('earned', 'redeemed', 'expired', 'bonus') NOT NULL,
    points INT NOT NULL,
    reason VARCHAR(200),
    reference_type VARCHAR(50), -- 'marketplace_purchase', 'savings_deposit', etc.
    reference_id INT,
    expiry_date DATE,
    processed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_member (member_id),
    INDEX idx_type (transaction_type),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_expiry (expiry_date),
    INDEX idx_processed (processed_at)
);

-- Loyalty rewards catalog
CREATE TABLE IF NOT EXISTS loyalty_rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reward_name VARCHAR(200) NOT NULL,
    description TEXT,
    reward_type ENUM('discount', 'free_product', 'cashback', 'exclusive_access') NOT NULL,
    points_required INT NOT NULL,
    value DECIMAL(10,2),
    quantity_available INT,
    valid_from DATE,
    valid_until DATE,
    image_url VARCHAR(500),
    terms_conditions TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (reward_type),
    INDEX idx_points (points_required),
    INDEX idx_active (active),
    INDEX idx_valid (valid_from, valid_until)
);

-- Marketplace analytics
CREATE TABLE IF NOT EXISTS marketplace_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(10,2),
    dimension VARCHAR(50), -- 'category', 'seller_type', 'time_period', etc.
    dimension_value VARCHAR(100),
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (metric_type),
    INDEX idx_dimension (dimension, dimension_value),
    INDEX idx_recorded (recorded_at)
);

-- Search and recommendations
CREATE TABLE IF NOT EXISTS product_recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    recommendation_score DECIMAL(5,4),
    recommendation_reason VARCHAR(100),
    algorithm_used VARCHAR(50),
    shown BOOLEAN DEFAULT FALSE,
    clicked BOOLEAN DEFAULT FALSE,
    purchased BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_product (product_id),
    INDEX idx_score (recommendation_score),
    INDEX idx_created (created_at)
);

-- Promotional campaigns
CREATE TABLE IF NOT EXISTS marketplace_campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_name VARCHAR(200) NOT NULL,
    campaign_type ENUM('discount', 'bogo', 'flash_sale', 'loyalty_bonus') NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    target_audience JSON, -- Member segments
    discount_percentage DECIMAL(5,2),
    discount_amount DECIMAL(10,2),
    minimum_purchase DECIMAL(10,2),
    usage_limit INT,
    usage_count INT DEFAULT 0,
    coupon_code VARCHAR(20),
    active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (campaign_type),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_active (active),
    INDEX idx_code (coupon_code)
);

-- Default marketplace categories
INSERT IGNORE INTO marketplace_categories (name, description, icon, sort_order) VALUES
('Electronics', 'Electronic devices and gadgets', 'device-desktop', 1),
('Fashion & Clothing', 'Clothing, shoes, and fashion accessories', 'shirt', 2),
('Home & Garden', 'Home improvement and gardening supplies', 'house', 3),
('Books & Education', 'Books, educational materials, and courses', 'book', 4),
('Sports & Recreation', 'Sports equipment and recreational items', 'balloon', 5),
('Automotive', 'Car parts, accessories, and automotive services', 'car', 6),
('Health & Beauty', 'Health products and beauty items', 'heart', 7),
('Services', 'Professional services and consultations', 'wrench', 8),
('Collectibles', 'Rare items and collectibles', 'star', 9),
('Other', 'Miscellaneous items', 'box', 10);

-- Default loyalty rewards
INSERT IGNORE INTO loyalty_rewards (reward_name, description, reward_type, points_required, value, quantity_available) VALUES
('5% Discount Voucher', 'Get 5% discount on your next purchase', 'discount', 100, 50000, 1000),
('Free Shipping', 'Free shipping on orders over Rp 100,000', 'discount', 150, 25000, 500),
('Exclusive Member Event', 'Invitation to exclusive member-only events', 'exclusive_access', 300, 0, 50),
('Bonus Savings Interest', 'Extra 0.5% interest on savings for 3 months', 'cashback', 200, 100000, 200);
