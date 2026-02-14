-- Blockchain Integration Database Schema for KSP Samosir

-- Digital Assets tables
CREATE TABLE IF NOT EXISTS digital_assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_name VARCHAR(100) NOT NULL,
    asset_symbol VARCHAR(20) NOT NULL,
    asset_type ENUM('savings_token', 'loan_token', 'equity_token', 'reward_token') DEFAULT 'savings_token',
    total_supply DECIMAL(20,8) NOT NULL,
    circulating_supply DECIMAL(20,8) DEFAULT 0,
    contract_address VARCHAR(100),
    blockchain_network VARCHAR(50) DEFAULT 'polygon',
    decimals INT DEFAULT 18,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_symbol (asset_symbol),
    INDEX idx_type (asset_type),
    INDEX idx_active (is_active)
);

CREATE TABLE IF NOT EXISTS digital_wallets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    wallet_address VARCHAR(100) UNIQUE NOT NULL,
    wallet_type ENUM('hot', 'cold', 'custodial') DEFAULT 'custodial',
    blockchain_network VARCHAR(50) DEFAULT 'polygon',
    balance DECIMAL(20,8) DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_address (wallet_address),
    INDEX idx_verified (is_verified)
);

CREATE TABLE IF NOT EXISTS asset_holdings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    wallet_id INT NOT NULL,
    asset_id INT NOT NULL,
    balance DECIMAL(20,8) NOT NULL DEFAULT 0,
    locked_balance DECIMAL(20,8) DEFAULT 0,
    last_transaction_hash VARCHAR(100),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_holding (wallet_id, asset_id),
    INDEX idx_wallet (wallet_id),
    INDEX idx_asset (asset_id)
);

-- Transparent Ledger tables
CREATE TABLE IF NOT EXISTS blockchain_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_hash VARCHAR(100) UNIQUE NOT NULL,
    block_number BIGINT,
    block_hash VARCHAR(100),
    from_address VARCHAR(100) NOT NULL,
    to_address VARCHAR(100) NOT NULL,
    value DECIMAL(20,8) NOT NULL,
    gas_used BIGINT,
    gas_price DECIMAL(20,8),
    transaction_fee DECIMAL(20,8),
    transaction_type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'confirmed', 'failed') DEFAULT 'pending',
    confirmations INT DEFAULT 0,
    asset_symbol VARCHAR(20),
    reference_type VARCHAR(50), -- 'loan', 'savings', 'marketplace'
    reference_id INT,
    metadata JSON,
    confirmed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hash (transaction_hash),
    INDEX idx_from (from_address),
    INDEX idx_to (to_address),
    INDEX idx_type (transaction_type),
    INDEX idx_status (status),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_confirmed (confirmed_at)
);

CREATE TABLE IF NOT EXISTS ledger_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    account_type VARCHAR(50) NOT NULL,
    account_id INT NOT NULL,
    debit DECIMAL(20,8) DEFAULT 0,
    credit DECIMAL(20,8) DEFAULT 0,
    balance DECIMAL(20,8) DEFAULT 0,
    asset_symbol VARCHAR(20),
    description TEXT,
    entry_type ENUM('user_transaction', 'system_adjustment', 'fee', 'reward') DEFAULT 'user_transaction',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction (transaction_id),
    INDEX idx_account (account_type, account_id),
    INDEX idx_asset (asset_symbol),
    INDEX idx_created (created_at)
);

-- Smart Contracts tables
CREATE TABLE IF NOT EXISTS smart_contracts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contract_name VARCHAR(100) NOT NULL,
    contract_type ENUM('loan_agreement', 'savings_contract', 'governance_token', 'reward_system') NOT NULL,
    contract_address VARCHAR(100) UNIQUE,
    abi JSON,
    bytecode TEXT,
    deployed_network VARCHAR(50) DEFAULT 'polygon',
    deployer_address VARCHAR(100),
    deployment_tx_hash VARCHAR(100),
    status ENUM('draft', 'deployed', 'active', 'paused', 'deprecated') DEFAULT 'draft',
    version VARCHAR(20) DEFAULT '1.0.0',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (contract_type),
    INDEX idx_status (status),
    INDEX idx_network (deployed_network)
);

CREATE TABLE IF NOT EXISTS contract_interactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contract_id INT NOT NULL,
    interaction_type ENUM('deploy', 'call', 'query', 'event') NOT NULL,
    method_name VARCHAR(100),
    parameters JSON,
    result JSON,
    transaction_hash VARCHAR(100),
    gas_used BIGINT,
    status ENUM('success', 'failed', 'pending') DEFAULT 'success',
    executed_by INT,
    executed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contract (contract_id),
    INDEX idx_type (interaction_type),
    INDEX idx_hash (transaction_hash),
    INDEX idx_executed (executed_at)
);

CREATE TABLE IF NOT EXISTS contract_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) NOT NULL,
    template_type VARCHAR(50) NOT NULL,
    solidity_code TEXT NOT NULL,
    abi_template JSON,
    parameters JSON,
    description TEXT,
    version VARCHAR(20) DEFAULT '1.0.0',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (template_type),
    INDEX idx_active (is_active)
);

-- Decentralized Governance tables
CREATE TABLE IF NOT EXISTS governance_proposals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proposal_title VARCHAR(200) NOT NULL,
    proposal_description TEXT NOT NULL,
    proposal_type ENUM('parameter_change', 'fund_allocation', 'contract_upgrade', 'membership_rules') NOT NULL,
    proposer_id INT NOT NULL,
    proposer_address VARCHAR(100),
    status ENUM('draft', 'active', 'passed', 'rejected', 'executed') DEFAULT 'draft',
    voting_start DATETIME,
    voting_end DATETIME,
    quorum_required DECIMAL(5,2) DEFAULT 10.00, -- Percentage of total voting power
    approval_threshold DECIMAL(5,2) DEFAULT 51.00, -- Percentage of votes
    total_votes DECIMAL(20,8) DEFAULT 0,
    yes_votes DECIMAL(20,8) DEFAULT 0,
    no_votes DECIMAL(20,8) DEFAULT 0,
    abstain_votes DECIMAL(20,8) DEFAULT 0,
    execution_tx_hash VARCHAR(100),
    executed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_proposer (proposer_id),
    INDEX idx_status (status),
    INDEX idx_voting (voting_start, voting_end)
);

CREATE TABLE IF NOT EXISTS governance_votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proposal_id INT NOT NULL,
    voter_id INT NOT NULL,
    voter_address VARCHAR(100),
    vote_choice ENUM('yes', 'no', 'abstain') NOT NULL,
    voting_power DECIMAL(20,8) NOT NULL,
    vote_tx_hash VARCHAR(100),
    voted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (proposal_id, voter_id),
    INDEX idx_proposal (proposal_id),
    INDEX idx_voter (voter_id),
    INDEX idx_choice (vote_choice),
    INDEX idx_voted (voted_at)
);

CREATE TABLE IF NOT EXISTS governance_delegates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    delegator_id INT NOT NULL,
    delegate_id INT NOT NULL,
    voting_power DECIMAL(20,8) NOT NULL,
    delegation_start DATETIME DEFAULT CURRENT_TIMESTAMP,
    delegation_end DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_delegation (delegator_id, delegate_id),
    INDEX idx_delegator (delegator_id),
    INDEX idx_delegate (delegate_id),
    INDEX idx_active (is_active)
);

-- Token Economics tables
CREATE TABLE IF NOT EXISTS token_economics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    token_symbol VARCHAR(20) NOT NULL,
    economic_model VARCHAR(50) NOT NULL,
    total_supply DECIMAL(20,8) NOT NULL,
    circulating_supply DECIMAL(20,8) DEFAULT 0,
    staking_rewards DECIMAL(5,2) DEFAULT 0, -- Annual percentage
    transaction_fees DECIMAL(5,2) DEFAULT 0,
    burn_rate DECIMAL(5,2) DEFAULT 0,
    vesting_schedule JSON,
    distribution_schedule JSON,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_token (token_symbol)
);

CREATE TABLE IF NOT EXISTS token_staking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    wallet_address VARCHAR(100) NOT NULL,
    staked_amount DECIMAL(20,8) NOT NULL,
    staking_start DATETIME DEFAULT CURRENT_TIMESTAMP,
    staking_end DATETIME,
    reward_rate DECIMAL(5,2) NOT NULL,
    accumulated_rewards DECIMAL(20,8) DEFAULT 0,
    status ENUM('active', 'unstaking', 'completed') DEFAULT 'active',
    unstaking_tx_hash VARCHAR(100),
    INDEX idx_user (user_id),
    INDEX idx_wallet (wallet_address),
    INDEX idx_status (status),
    INDEX idx_staking_end (staking_end)
);

-- Oracle and External Data Integration
CREATE TABLE IF NOT EXISTS blockchain_oracles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    oracle_name VARCHAR(100) NOT NULL,
    oracle_type VARCHAR(50) NOT NULL,
    data_source VARCHAR(200) NOT NULL,
    update_frequency INT DEFAULT 3600, -- seconds
    last_update DATETIME,
    confidence_score DECIMAL(5,4) DEFAULT 1.0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (oracle_type),
    INDEX idx_active (is_active),
    INDEX idx_last_update (last_update)
);

CREATE TABLE IF NOT EXISTS oracle_data_feeds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    oracle_id INT NOT NULL,
    data_key VARCHAR(100) NOT NULL,
    data_value DECIMAL(20,8),
    data_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    block_number BIGINT,
    transaction_hash VARCHAR(100),
    INDEX idx_oracle (oracle_id),
    INDEX idx_key (data_key),
    INDEX idx_timestamp (data_timestamp)
);

-- Cross-chain Bridge tables
CREATE TABLE IF NOT EXISTS chain_bridges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bridge_name VARCHAR(100) NOT NULL,
    source_chain VARCHAR(50) NOT NULL,
    target_chain VARCHAR(50) NOT NULL,
    bridge_contract VARCHAR(100),
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    daily_volume_limit DECIMAL(20,8),
    current_daily_volume DECIMAL(20,8) DEFAULT 0,
    fee_structure JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_chains (source_chain, target_chain),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS bridge_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bridge_id INT NOT NULL,
    user_id INT NOT NULL,
    source_tx_hash VARCHAR(100) NOT NULL,
    target_tx_hash VARCHAR(100),
    amount DECIMAL(20,8) NOT NULL,
    asset_symbol VARCHAR(20) NOT NULL,
    fee_amount DECIMAL(20,8) DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    initiated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    INDEX idx_bridge (bridge_id),
    INDEX idx_user (user_id),
    INDEX idx_source_tx (source_tx_hash),
    INDEX idx_target_tx (target_tx_hash),
    INDEX idx_status (status)
);
