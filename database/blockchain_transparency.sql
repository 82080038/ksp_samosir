-- Blockchain Transparency Tables
-- KSP Samosir - Immutable transaction and governance records

CREATE TABLE blockchain_blocks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    block_data JSON NOT NULL,
    previous_hash VARCHAR(64) NOT NULL DEFAULT '0',
    current_hash VARCHAR(64) NOT NULL,
    block_type ENUM('sale', 'payment', 'loan', 'savings', 'governance', 'general') NOT NULL,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

CREATE TABLE block_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    block_id INT NOT NULL,
    verification_hash VARCHAR(64) NOT NULL,
    verification_status ENUM('valid', 'invalid', 'tampered') DEFAULT 'valid',
    verified_by INT,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (block_id) REFERENCES blockchain_blocks(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

CREATE TABLE transparency_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    old_data JSON,
    new_data JSON,
    recorded_by INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_blockchain_blocks_type ON blockchain_blocks(block_type);
CREATE INDEX idx_blockchain_blocks_created ON blockchain_blocks(created_at);
CREATE INDEX idx_blockchain_blocks_hash ON blockchain_blocks(current_hash);
CREATE INDEX idx_block_verifications_block ON block_verifications(block_id);
CREATE INDEX idx_block_verifications_status ON block_verifications(verification_status);
CREATE INDEX idx_transparency_logs_action ON transparency_logs(action);
CREATE INDEX idx_transparency_logs_entity ON transparency_logs(entity_type, entity_id);
