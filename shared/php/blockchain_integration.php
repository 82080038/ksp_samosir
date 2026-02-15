<?php
/**
 * Blockchain Integration System for KSP Samosir
 * Digital assets, transparent ledger, smart contracts, decentralized governance
 */

class BlockchainIntegration {
    private $pdo;
    private $web3Provider;
    private $networkConfig;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
        $this->networkConfig = [
            'polygon' => [
                'rpc_url' => 'https://polygon-rpc.com',
                'chain_id' => 137,
                'explorer_url' => 'https://polygonscan.com'
            ],
            'ethereum' => [
                'rpc_url' => 'https://mainnet.infura.io/v3/YOUR_INFURA_KEY',
                'chain_id' => 1,
                'explorer_url' => 'https://etherscan.io'
            ]
        ];
    }

    /**
     * Digital Assets Management
     */
    public function createDigitalAsset($assetData) {
        // Validate asset data
        if (!$this->validateAssetData($assetData)) {
            return ['success' => false, 'error' => 'Invalid asset data'];
        }

        // Deploy smart contract (simulated)
        $contractAddress = $this->deployAssetContract($assetData);

        if (!$contractAddress) {
            return ['success' => false, 'error' => 'Failed to deploy contract'];
        }

        // Save to database
        $stmt = $this->pdo->prepare("
            INSERT INTO digital_assets
            (asset_name, asset_symbol, asset_type, total_supply, contract_address, blockchain_network)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $assetData['name'],
            $assetData['symbol'],
            $assetData['type'],
            $assetData['total_supply'],
            $contractAddress,
            $assetData['network'] ?? 'polygon'
        ]);

        return [
            'success' => true,
            'asset_id' => $this->pdo->lastInsertId(),
            'contract_address' => $contractAddress
        ];
    }

    public function mintDigitalAsset($assetId, $recipientAddress, $amount) {
        $asset = $this->getDigitalAsset($assetId);
        if (!$asset) {
            return ['success' => false, 'error' => 'Asset not found'];
        }

        // Mint tokens (simulated blockchain transaction)
        $txHash = $this->simulateMintTransaction($asset['contract_address'], $recipientAddress, $amount);

        // Record transaction
        $this->recordBlockchainTransaction([
            'transaction_hash' => $txHash,
            'from_address' => '0x0000000000000000000000000000000000000000', // Mint address
            'to_address' => $recipientAddress,
            'value' => $amount,
            'transaction_type' => 'asset_mint',
            'asset_symbol' => $asset['asset_symbol'],
            'status' => 'confirmed'
        ]);

        // Update holdings
        $this->updateAssetHoldings($recipientAddress, $assetId, $amount);

        return [
            'success' => true,
            'transaction_hash' => $txHash,
            'recipient' => $recipientAddress,
            'amount' => $amount
        ];
    }

    public function transferDigitalAsset($fromAddress, $toAddress, $assetId, $amount) {
        // Check balance
        $balance = $this->getAssetBalance($fromAddress, $assetId);
        if ($balance < $amount) {
            return ['success' => false, 'error' => 'Insufficient balance'];
        }

        // Transfer (simulated)
        $txHash = $this->simulateTransferTransaction($fromAddress, $toAddress, $assetId, $amount);

        // Record transaction
        $asset = $this->getDigitalAsset($assetId);
        $this->recordBlockchainTransaction([
            'transaction_hash' => $txHash,
            'from_address' => $fromAddress,
            'to_address' => $toAddress,
            'value' => $amount,
            'transaction_type' => 'asset_transfer',
            'asset_symbol' => $asset['asset_symbol'],
            'status' => 'confirmed'
        ]);

        // Update holdings
        $this->updateAssetHoldings($fromAddress, $assetId, -$amount);
        $this->updateAssetHoldings($toAddress, $assetId, $amount);

        return [
            'success' => true,
            'transaction_hash' => $txHash,
            'from' => $fromAddress,
            'to' => $toAddress,
            'amount' => $amount
        ];
    }

    /**
     * Smart Contracts Management
     */
    public function deploySmartContract($contractData) {
        // Get contract template
        $template = $this->getContractTemplate($contractData['template_type']);
        if (!$template) {
            return ['success' => false, 'error' => 'Contract template not found'];
        }

        // Customize contract with parameters
        $customizedContract = $this->customizeContract($template, $contractData['parameters']);

        // Deploy contract (simulated)
        $contractAddress = $this->deployContract($customizedContract, $contractData['network'] ?? 'polygon');

        if (!$contractAddress) {
            return ['success' => false, 'error' => 'Contract deployment failed'];
        }

        // Save contract to database
        $stmt = $this->pdo->prepare("
            INSERT INTO smart_contracts
            (contract_name, contract_type, contract_address, abi, deployed_network, status)
            VALUES (?, ?, ?, ?, ?, 'deployed')
        ");

        $stmt->execute([
            $contractData['name'],
            $contractData['type'],
            $contractAddress,
            json_encode($customizedContract['abi']),
            $contractData['network'] ?? 'polygon'
        ]);

        $contractId = $this->pdo->lastInsertId();

        // Record deployment interaction
        $this->recordContractInteraction($contractId, 'deploy', 'constructor', $contractData['parameters'], [
            'contract_address' => $contractAddress,
            'network' => $contractData['network'] ?? 'polygon'
        ]);

        return [
            'success' => true,
            'contract_id' => $contractId,
            'contract_address' => $contractAddress
        ];
    }

    public function executeSmartContract($contractId, $methodName, $parameters = []) {
        $contract = $this->getSmartContract($contractId);
        if (!$contract) {
            return ['success' => false, 'error' => 'Contract not found'];
        }

        // Execute contract method (simulated)
        $result = $this->simulateContractExecution($contract['contract_address'], $methodName, $parameters);

        // Record interaction
        $this->recordContractInteraction($contractId, 'call', $methodName, $parameters, $result);

        return [
            'success' => true,
            'contract_id' => $contractId,
            'method' => $methodName,
            'result' => $result
        ];
    }

    /**
     * Decentralized Governance
     */
    public function createGovernanceProposal($proposalData) {
        $stmt = $this->pdo->prepare("
            INSERT INTO governance_proposals
            (proposal_title, proposal_description, proposal_type, proposer_id, status,
             voting_start, voting_end, quorum_required, approval_threshold)
            VALUES (?, ?, ?, ?, 'active', ?, ?, ?, ?)
        ");

        $stmt->execute([
            $proposalData['title'],
            $proposalData['description'],
            $proposalData['type'],
            $proposalData['proposer_id'],
            $proposalData['voting_start'],
            $proposalData['voting_end'],
            $proposalData['quorum_required'] ?? 10.00,
            $proposalData['approval_threshold'] ?? 51.00
        ]);

        return [
            'success' => true,
            'proposal_id' => $this->pdo->lastInsertId()
        ];
    }

    public function castGovernanceVote($proposalId, $voterId, $voteChoice, $votingPower = 1) {
        // Check if proposal is active
        $proposal = $this->getGovernanceProposal($proposalId);
        if (!$proposal || $proposal['status'] !== 'active') {
            return ['success' => false, 'error' => 'Proposal not active'];
        }

        // Check if voter already voted
        $existingVote = fetchRow("
            SELECT id FROM governance_votes
            WHERE proposal_id = ? AND voter_id = ?
        ", [$proposalId, $voterId], 'ii');

        if ($existingVote) {
            return ['success' => false, 'error' => 'Already voted'];
        }

        // Record vote
        $stmt = $this->pdo->prepare("
            INSERT INTO governance_votes
            (proposal_id, voter_id, vote_choice, voting_power)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$proposalId, $voterId, $voteChoice, $votingPower]);

        // Update proposal vote counts
        $this->updateProposalVoteCounts($proposalId);

        // Check if proposal can be resolved
        $this->checkProposalResolution($proposalId);

        return ['success' => true, 'vote_choice' => $voteChoice];
    }

    public function executeGovernanceProposal($proposalId) {
        $proposal = $this->getGovernanceProposal($proposalId);
        if (!$proposal || $proposal['status'] !== 'passed') {
            return ['success' => false, 'error' => 'Proposal not eligible for execution'];
        }

        // Execute proposal based on type
        $executionResult = $this->executeProposalAction($proposal);

        if ($executionResult['success']) {
            // Update proposal status
            $stmt = $this->pdo->prepare("
                UPDATE governance_proposals
                SET status = 'executed', executed_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$proposalId]);

            // Record execution transaction
            $this->recordBlockchainTransaction([
                'transaction_hash' => $executionResult['tx_hash'] ?? uniqid('gov_exec_'),
                'from_address' => 'governance_contract',
                'to_address' => 'system',
                'value' => 0,
                'transaction_type' => 'governance_execution',
                'reference_type' => 'governance_proposal',
                'reference_id' => $proposalId,
                'status' => 'confirmed'
            ]);
        }

        return $executionResult;
    }

    /**
     * Transparent Ledger
     */
    public function getTransactionHistory($address = null, $limit = 50) {
        $whereClause = "";
        $params = [];

        if ($address) {
            $whereClause = "WHERE from_address = ? OR to_address = ?";
            $params = [$address, $address];
        }

        $transactions = fetchAll("
            SELECT * FROM blockchain_transactions
            {$whereClause}
            ORDER BY created_at DESC
            LIMIT ?
        ", array_merge($params, [$limit]), str_repeat('s', count($params)) . 'i');

        return $transactions;
    }

    public function verifyTransaction($transactionHash) {
        $transaction = fetchRow("
            SELECT * FROM blockchain_transactions
            WHERE transaction_hash = ?
        ", [$transactionHash], 's');

        if (!$transaction) {
            return ['verified' => false, 'error' => 'Transaction not found'];
        }

        // Verify transaction integrity (simplified)
        $isValid = $this->verifyTransactionIntegrity($transaction);

        return [
            'verified' => $isValid,
            'transaction' => $transaction,
            'confirmations' => $transaction['confirmations'],
            'timestamp' => $transaction['confirmed_at']
        ];
    }

    /**
     * Token Staking and Rewards
     */
    public function stakeTokens($userId, $assetId, $amount, $durationMonths = 12) {
        $wallet = $this->getUserWallet($userId);
        if (!$wallet) {
            return ['success' => false, 'error' => 'Wallet not found'];
        }

        // Check balance
        $balance = $this->getAssetBalance($wallet['wallet_address'], $assetId);
        if ($balance < $amount) {
            return ['success' => false, 'error' => 'Insufficient balance'];
        }

        // Calculate reward rate based on duration
        $rewardRate = $this->calculateStakingRewardRate($durationMonths);

        // Create staking record
        $stmt = $this->pdo->prepare("
            INSERT INTO token_staking
            (user_id, wallet_address, staked_amount, staking_end, reward_rate)
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MONTH), ?)
        ");

        $stmt->execute([$userId, $wallet['wallet_address'], $amount, $durationMonths, $rewardRate]);

        // Lock tokens (reduce available balance)
        $this->updateAssetHoldings($wallet['wallet_address'], $assetId, -$amount, $amount);

        return [
            'success' => true,
            'staking_id' => $this->pdo->lastInsertId(),
            'amount' => $amount,
            'duration_months' => $durationMonths,
            'reward_rate' => $rewardRate
        ];
    }

    public function claimStakingRewards($stakingId) {
        $staking = fetchRow("
            SELECT * FROM token_staking
            WHERE id = ? AND status = 'active'
        ", [$stakingId], 'i');

        if (!$staking) {
            return ['success' => false, 'error' => 'Staking record not found'];
        }

        // Calculate accumulated rewards
        $daysStaked = (time() - strtotime($staking['staking_start'])) / (60 * 60 * 24);
        $rewards = ($staking['staked_amount'] * $staking['reward_rate'] * $daysStaked) / 365;

        if ($rewards <= 0) {
            return ['success' => false, 'error' => 'No rewards available'];
        }

        // Mint reward tokens
        $this->mintDigitalAsset($staking['asset_id'] ?? 1, $staking['wallet_address'], $rewards);

        // Update staking record
        $stmt = $this->pdo->prepare("
            UPDATE token_staking
            SET accumulated_rewards = accumulated_rewards + ?, last_reward_claim = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$rewards, $stakingId]);

        return [
            'success' => true,
            'rewards_claimed' => $rewards,
            'staking_id' => $stakingId
        ];
    }

    /**
     * Private helper methods
     */
    private function validateAssetData($data) {
        return isset($data['name'], $data['symbol'], $data['total_supply']) &&
               !empty($data['name']) && !empty($data['symbol']) && $data['total_supply'] > 0;
    }

    private function deployAssetContract($assetData) {
        // Simulate contract deployment
        return '0x' . bin2hex(random_bytes(20));
    }

    private function simulateMintTransaction($contractAddress, $recipient, $amount) {
        return '0x' . bin2hex(random_bytes(32));
    }

    private function simulateTransferTransaction($from, $to, $assetId, $amount) {
        return '0x' . bin2hex(random_bytes(32));
    }

    private function recordBlockchainTransaction($txData) {
        $stmt = $this->pdo->prepare("
            INSERT INTO blockchain_transactions
            (transaction_hash, from_address, to_address, value, transaction_type,
             asset_symbol, reference_type, reference_id, status, confirmations, confirmed_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $txData['transaction_hash'],
            $txData['from_address'],
            $txData['to_address'],
            $txData['value'],
            $txData['transaction_type'],
            $txData['asset_symbol'] ?? null,
            $txData['reference_type'] ?? null,
            $txData['reference_id'] ?? null,
            $txData['status'] ?? 'confirmed',
            $txData['confirmations'] ?? 12
        ]);
    }

    private function updateAssetHoldings($walletAddress, $assetId, $amount, $lockedAmount = 0) {
        $stmt = $this->pdo->prepare("
            INSERT INTO asset_holdings (wallet_id, asset_id, balance, locked_balance)
            SELECT w.id, ?, ?, ?
            FROM digital_wallets w
            WHERE w.wallet_address = ?
            ON DUPLICATE KEY UPDATE
            balance = balance + VALUES(balance),
            locked_balance = locked_balance + VALUES(locked_balance)
        ");

        $wallet = fetchRow("SELECT id FROM digital_wallets WHERE wallet_address = ?", [$walletAddress], 's');
        if ($wallet) {
            $stmt->execute([$assetId, $amount, $lockedAmount, $walletAddress]);
        }
    }

    private function getDigitalAsset($assetId) {
        return fetchRow("SELECT * FROM digital_assets WHERE id = ?", [$assetId], 'i');
    }

    private function getAssetBalance($walletAddress, $assetId) {
        $result = fetchRow("
            SELECT balance FROM asset_holdings ah
            JOIN digital_wallets w ON ah.wallet_id = w.id
            WHERE w.wallet_address = ? AND ah.asset_id = ?
        ", [$walletAddress, $assetId], 'si');

        return $result['balance'] ?? 0;
    }

    private function getUserWallet($userId) {
        return fetchRow("SELECT * FROM digital_wallets WHERE user_id = ? AND is_verified = TRUE", [$userId], 'i');
    }

    private function getContractTemplate($templateType) {
        return fetchRow("SELECT * FROM contract_templates WHERE template_type = ? AND is_active = TRUE", [$templateType], 's');
    }

    private function customizeContract($template, $parameters) {
        // Simple parameter replacement (in production, use proper Solidity templating)
        $code = $template['solidity_code'];
        foreach ($parameters as $key => $value) {
            $code = str_replace('{{' . $key . '}}', $value, $code);
        }

        return [
            'code' => $code,
            'abi' => $template['abi_template']
        ];
    }

    private function deployContract($contract, $network) {
        // Simulate contract deployment
        return '0x' . bin2hex(random_bytes(20));
    }

    private function simulateContractExecution($contractAddress, $methodName, $parameters) {
        // Simulate contract method execution
        return ['success' => true, 'result' => 'Method executed successfully'];
    }

    private function recordContractInteraction($contractId, $type, $method, $params, $result) {
        $stmt = $this->pdo->prepare("
            INSERT INTO contract_interactions
            (contract_id, interaction_type, method_name, parameters, result)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$contractId, $type, $method, json_encode($params), json_encode($result)]);
    }

    private function getSmartContract($contractId) {
        return fetchRow("SELECT * FROM smart_contracts WHERE id = ?", [$contractId], 'i');
    }

    private function getGovernanceProposal($proposalId) {
        return fetchRow("SELECT * FROM governance_proposals WHERE id = ?", [$proposalId], 'i');
    }

    private function updateProposalVoteCounts($proposalId) {
        $voteCounts = fetchRow("
            SELECT
                SUM(CASE WHEN vote_choice = 'yes' THEN voting_power ELSE 0 END) as yes_votes,
                SUM(CASE WHEN vote_choice = 'no' THEN voting_power ELSE 0 END) as no_votes,
                SUM(CASE WHEN vote_choice = 'abstain' THEN voting_power ELSE 0 END) as abstain_votes,
                SUM(voting_power) as total_votes
            FROM governance_votes
            WHERE proposal_id = ?
        ", [$proposalId], 'i');

        $stmt = $this->pdo->prepare("
            UPDATE governance_proposals
            SET yes_votes = ?, no_votes = ?, abstain_votes = ?, total_votes = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $voteCounts['yes_votes'] ?? 0,
            $voteCounts['no_votes'] ?? 0,
            $voteCounts['abstain_votes'] ?? 0,
            $voteCounts['total_votes'] ?? 0,
            $proposalId
        ]);
    }

    private function checkProposalResolution($proposalId) {
        $proposal = $this->getGovernanceProposal($proposalId);

        // Check if voting period has ended
        if (strtotime($proposal['voting_end']) > time()) {
            return; // Voting still active
        }

        // Check quorum
        $totalVotingPower = $this->getTotalVotingPower();
        $quorumMet = ($proposal['total_votes'] / $totalVotingPower) * 100 >= $proposal['quorum_required'];

        if (!$quorumMet) {
            $this->updateProposalStatus($proposalId, 'rejected');
            return;
        }

        // Check approval threshold
        $approvalRate = $proposal['total_votes'] > 0 ?
            ($proposal['yes_votes'] / $proposal['total_votes']) * 100 : 0;

        $status = $approvalRate >= $proposal['approval_threshold'] ? 'passed' : 'rejected';
        $this->updateProposalStatus($proposalId, $status);
    }

    private function executeProposalAction($proposal) {
        // Execute based on proposal type
        switch ($proposal['proposal_type']) {
            case 'parameter_change':
                return $this->executeParameterChange($proposal);
            case 'fund_allocation':
                return $this->executeFundAllocation($proposal);
            case 'contract_upgrade':
                return $this->executeContractUpgrade($proposal);
            default:
                return ['success' => false, 'error' => 'Unknown proposal type'];
        }
    }

    private function executeParameterChange($proposal) {
        // Simulate parameter change execution
        return ['success' => true, 'tx_hash' => '0x' . bin2hex(random_bytes(32))];
    }

    private function executeFundAllocation($proposal) {
        // Simulate fund allocation
        return ['success' => true, 'tx_hash' => '0x' . bin2hex(random_bytes(32))];
    }

    private function executeContractUpgrade($proposal) {
        // Simulate contract upgrade
        return ['success' => true, 'tx_hash' => '0x' . bin2hex(random_bytes(32))];
    }

    private function updateProposalStatus($proposalId, $status) {
        $stmt = $this->pdo->prepare("UPDATE governance_proposals SET status = ? WHERE id = ?");
        $stmt->execute([$status, $proposalId]);
    }

    private function getTotalVotingPower() {
        // Simplified - in production, calculate based on token holdings
        return 10000;
    }

    private function verifyTransactionIntegrity($transaction) {
        // Simplified integrity check
        return !empty($transaction['transaction_hash']) && strlen($transaction['transaction_hash']) > 10;
    }

    private function calculateStakingRewardRate($months) {
        // Base rate of 5% APY, bonus for longer staking
        $baseRate = 5.0;
        $bonus = min($months - 3, 9) * 0.5; // Max 4.5% bonus for 12+ months
        return $baseRate + $bonus;
    }

    public function getBlockchainStats() {
        return [
            'total_transactions' => (fetchRow("SELECT COUNT(*) as count FROM blockchain_transactions", [], '') ?? [])['count'] ?? 0,
            'active_contracts' => (fetchRow("SELECT COUNT(*) as count FROM smart_contracts WHERE status = 'active'", [], '') ?? [])['count'] ?? 0,
            'total_assets' => (fetchRow("SELECT COUNT(*) as count FROM digital_assets WHERE is_active = TRUE", [], '') ?? [])['count'] ?? 0,
            'active_proposals' => (fetchRow("SELECT COUNT(*) as count FROM governance_proposals WHERE status = 'active'", [], '') ?? [])['count'] ?? 0,
            'total_staked' => (fetchRow("SELECT COALESCE(SUM(staked_amount), 0) as total FROM token_staking WHERE status = 'active'", [], '') ?? [])['total'] ?? 0
        ];
    }
}

// Helper functions
function createDigitalAsset($assetData) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->createDigitalAsset($assetData);
}

function mintDigitalAsset($assetId, $recipientAddress, $amount) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->mintDigitalAsset($assetId, $recipientAddress, $amount);
}

function transferDigitalAsset($fromAddress, $toAddress, $assetId, $amount) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->transferDigitalAsset($fromAddress, $toAddress, $assetId, $amount);
}

function deploySmartContract($contractData) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->deploySmartContract($contractData);
}

function executeSmartContract($contractId, $methodName, $parameters = []) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->executeSmartContract($contractId, $methodName, $parameters);
}

function createGovernanceProposal($proposalData) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->createGovernanceProposal($proposalData);
}

function castGovernanceVote($proposalId, $voterId, $voteChoice, $votingPower = 1) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->castGovernanceVote($proposalId, $voterId, $voteChoice, $votingPower);
}

function executeGovernanceProposal($proposalId) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->executeGovernanceProposal($proposalId);
}

function getTransactionHistory($address = null, $limit = 50) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->getTransactionHistory($address, $limit);
}

function verifyTransaction($transactionHash) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->verifyTransaction($transactionHash);
}

function stakeTokens($userId, $assetId, $amount, $durationMonths = 12) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->stakeTokens($userId, $assetId, $amount, $durationMonths);
}

function claimStakingRewards($stakingId) {
    $blockchain = new BlockchainIntegration();
    return $blockchain->claimStakingRewards($stakingId);
}

function getBlockchainStats() {
    $blockchain = new BlockchainIntegration();
    return $blockchain->getBlockchainStats();
}
