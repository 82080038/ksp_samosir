-- AI Features Tables
-- KSP Samosir - AI recommendations and fraud detection

CREATE TABLE ai_recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    recommendation_score DECIMAL(5,2),
    recommendation_reason VARCHAR(255),
    was_purchased TINYINT(1) DEFAULT 0,
    purchased_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES produk(id)
);

CREATE TABLE ai_fraud_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alert_type VARCHAR(50) NOT NULL,
    reference_id INT,
    risk_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    description TEXT NOT NULL,
    alert_data JSON,
    status ENUM('active', 'investigating', 'resolved', 'dismissed') DEFAULT 'active',
    resolved_by INT,
    resolved_at DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resolved_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE ai_model_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_type VARCHAR(50) NOT NULL, -- recommendation, fraud_detection
    training_data JSON,
    model_parameters JSON,
    accuracy_score DECIMAL(5,2),
    last_trained TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_ai_recommendations_user ON ai_recommendations(user_id);
CREATE INDEX idx_ai_recommendations_product ON ai_recommendations(product_id);
CREATE INDEX idx_ai_recommendations_created ON ai_recommendations(created_at);
CREATE INDEX idx_ai_fraud_alerts_type ON ai_fraud_alerts(alert_type);
CREATE INDEX idx_ai_fraud_alerts_risk ON ai_fraud_alerts(risk_level);
CREATE INDEX idx_ai_fraud_alerts_status ON ai_fraud_alerts(status);
CREATE INDEX idx_ai_fraud_alerts_created ON ai_fraud_alerts(created_at);
CREATE INDEX idx_ai_model_data_type ON ai_model_data(model_type);
