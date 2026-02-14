<?php
require_once __DIR__ . '/BaseController.php';

/**
 * BlockchainController handles transparency features using blockchain-inspired technology.
 * Creates immutable records of transactions, governance decisions, and member activities.
 */
class BlockchainController extends BaseController {
    /**
     * Display transparency dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $stats = $this->getTransparencyStats();
        $recent_blocks = $this->getRecentBlocks();
        $verification_status = $this->getVerificationStatus();

        $this->render(__DIR__ . '/../views/blockchain/index.php', [
            'stats' => $stats,
            'recent_blocks' => $recent_blocks,
            'verification_status' => $verification_status
        ]);
    }

    /**
     * Record transaction in blockchain.
     */
    public function recordTransaction($transaction_id, $transaction_type = 'general') {
        // This would be called automatically when transactions are created
        $transaction_data = $this->getTransactionData($transaction_id, $transaction_type);

        if (!$transaction_data) return false;

        // Create block data
        $block_data = [
            'transaction_id' => $transaction_id,
            'transaction_type' => $transaction_type,
            'data' => json_encode($transaction_data),
            'timestamp' => date('c'),
            'recorded_by' => $_SESSION['user']['id'] ?? 1
        ];

        // Get previous block hash
        $previous_hash = $this->getLatestBlockHash();

        // Calculate current block hash
        $current_hash = $this->calculateBlockHash($block_data, $previous_hash);

        // Store block
        runInTransaction(function($conn) use ($block_data, $previous_hash, $current_hash) {
            $stmt = $conn->prepare("INSERT INTO blockchain_blocks (block_data, previous_hash, current_hash, block_type, recorded_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('ssssi', json_encode($block_data), $previous_hash, $current_hash, $block_data['transaction_type'], $block_data['recorded_by']);
            $stmt->execute();
            $stmt->close();
        });

        return $current_hash;
    }

    /**
     * Record governance decision.
     */
    public function recordGovernanceDecision() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render(__DIR__ . '/../views/blockchain/record_decision.php');
            return;
        }

        $decision_data = [
            'title' => sanitize($_POST['title']),
            'description' => sanitize($_POST['description']),
            'decision_type' => sanitize($_POST['decision_type']),
            'participants' => sanitize($_POST['participants']),
            'outcome' => sanitize($_POST['outcome']),
            'attachments' => sanitize($_POST['attachments'])
        ];

        $block_data = [
            'type' => 'governance_decision',
            'data' => json_encode($decision_data),
            'timestamp' => date('c'),
            'recorded_by' => $_SESSION['user']['id'] ?? 1
        ];

        // Get previous block hash
        $previous_hash = $this->getLatestBlockHash();

        // Calculate current block hash
        $current_hash = $this->calculateBlockHash($block_data, $previous_hash);

        // Store block
        runInTransaction(function($conn) use ($block_data, $previous_hash, $current_hash) {
            $stmt = $conn->prepare("INSERT INTO blockchain_blocks (block_data, previous_hash, current_hash, block_type, recorded_by, created_at) VALUES (?, ?, ?, 'governance', ?, NOW())");
            $stmt->bind_param('sssi', json_encode($block_data), $previous_hash, $current_hash, $block_data['recorded_by']);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Keputusan tata kelola berhasil dicatat dalam blockchain');
        redirect('blockchain/index');
    }

    /**
     * Verify blockchain integrity.
     */
    public function verifyIntegrity() {
        $blocks = fetchAll("SELECT id, block_data, previous_hash, current_hash FROM blockchain_blocks ORDER BY id ASC");

        $verification_results = [];
        $previous_hash = '0'; // Genesis block

        foreach ($blocks as $block) {
            $block_data = json_decode($block['block_data'], true);
            $calculated_hash = $this->calculateBlockHash($block_data, $block['previous_hash']);

            $is_valid = $calculated_hash === $block['current_hash'] && $block['previous_hash'] === $previous_hash;

            $verification_results[] = [
                'block_id' => $block['id'],
                'is_valid' => $is_valid,
                'stored_hash' => $block['current_hash'],
                'calculated_hash' => $calculated_hash,
                'previous_hash_match' => $block['previous_hash'] === $previous_hash
            ];

            if ($is_valid) {
                $previous_hash = $calculated_hash;
            } else {
                break; // Stop verification if chain is broken
            }
        }

        $this->render(__DIR__ . '/../views/blockchain/verification.php', [
            'verification_results' => $verification_results,
            'chain_valid' => !in_array(false, array_column($verification_results, 'is_valid'))
        ]);
    }

    /**
     * View transaction history with blockchain verification.
     */
    public function transactionHistory() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = fetchRow("SELECT COUNT(*) as count FROM blockchain_blocks WHERE block_type IN ('sale', 'payment', 'loan', 'savings')")['count'];
        $totalPages = ceil($total / $perPage);

        $blocks = fetchAll("SELECT bb.*, u.full_name as recorded_by_name FROM blockchain_blocks bb LEFT JOIN users u ON bb.recorded_by = u.id WHERE bb.block_type IN ('sale', 'payment', 'loan', 'savings') ORDER BY bb.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        // Decode block data for display
        foreach ($blocks as &$block) {
            $block['decoded_data'] = json_decode($block['block_data'], true);
        }

        $this->render(__DIR__ . '/../views/blockchain/transaction_history.php', [
            'blocks' => $blocks,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Generate transparency report.
     */
    public function transparencyReport() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $period = $_GET['period'] ?? date('Y-m');

        $report_data = [
            'total_blocks' => fetchRow("SELECT COUNT(*) as total FROM blockchain_blocks WHERE DATE_FORMAT(created_at, '%Y-%m') = ?", [$period], 's')['total'],
            'blocks_by_type' => fetchAll("SELECT block_type, COUNT(*) as count FROM blockchain_blocks WHERE DATE_FORMAT(created_at, '%Y-%m') = ? GROUP BY block_type", [$period], 's'),
            'chain_integrity' => $this->verifyChainIntegrity(),
            'governance_decisions' => fetchAll("SELECT bb.*, u.full_name FROM blockchain_blocks bb LEFT JOIN users u ON bb.recorded_by = u.id WHERE bb.block_type = 'governance' AND DATE_FORMAT(bb.created_at, '%Y-%m') = ? ORDER BY bb.created_at DESC", [$period], 's')
        ];

        $this->render(__DIR__ . '/../views/blockchain/transparency_report.php', [
            'report_data' => $report_data,
            'period' => $period
        ]);
    }

    /**
     * Get block details.
     */
    public function blockDetail($block_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $block = fetchRow("SELECT bb.*, u.full_name as recorded_by_name FROM blockchain_blocks bb LEFT JOIN users u ON bb.recorded_by = u.id WHERE bb.id = ?", [$block_id], 'i');

        if (!$block) {
            flashMessage('error', 'Block tidak ditemukan');
            redirect('blockchain/index');
        }

        $block['decoded_data'] = json_decode($block['block_data'], true);

        // Get verification status
        $verification = $this->verifyBlockIntegrity($block_id);

        $this->render(__DIR__ . '/../views/blockchain/block_detail.php', [
            'block' => $block,
            'verification' => $verification
        ]);
    }

    /**
     * Get transparency statistics.
     */
    private function getTransparencyStats() {
        $stats = [];

        $stats['total_blocks'] = fetchRow("SELECT COUNT(*) as total FROM blockchain_blocks")['total'];
        $stats['blocks_this_month'] = fetchRow("SELECT COUNT(*) as total FROM blockchain_blocks WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)")['total'];
        $stats['governance_decisions'] = fetchRow("SELECT COUNT(*) as total FROM blockchain_blocks WHERE block_type = 'governance'")['total'];
        $stats['chain_integrity'] = $this->verifyChainIntegrity() ? 'Valid' : 'Invalid';

        return $stats;
    }

    /**
     * Get recent blocks.
     */
    private function getRecentBlocks() {
        $blocks = fetchAll("SELECT bb.*, u.full_name as recorded_by_name FROM blockchain_blocks bb LEFT JOIN users u ON bb.recorded_by = u.id ORDER BY bb.created_at DESC LIMIT 5");

        // Decode data for display
        foreach ($blocks as &$block) {
            $decoded = json_decode($block['block_data'], true);
            $block['summary'] = $this->getBlockSummary($decoded, $block['block_type']);
        }

        return $blocks;
    }

    /**
     * Get verification status.
     */
    private function getVerificationStatus() {
        $total_blocks = fetchRow("SELECT COUNT(*) as total FROM blockchain_blocks")['total'];
        $verified_blocks = 0;

        if ($total_blocks > 0) {
            $blocks = fetchAll("SELECT id FROM blockchain_blocks ORDER BY id ASC");
            $previous_hash = '0';

            foreach ($blocks as $block) {
                $verification = $this->verifyBlockIntegrity($block['id']);
                if ($verification['is_valid']) {
                    $verified_blocks++;
                    $previous_hash = $verification['current_hash'];
                } else {
                    break;
                }
            }
        }

        return [
            'total_blocks' => $total_blocks,
            'verified_blocks' => $verified_blocks,
            'verification_percentage' => $total_blocks > 0 ? round(($verified_blocks / $total_blocks) * 100, 2) : 0
        ];
    }

    /**
     * Get latest block hash.
     */
    private function getLatestBlockHash() {
        $latest = fetchRow("SELECT current_hash FROM blockchain_blocks ORDER BY id DESC LIMIT 1");
        return $latest ? $latest['current_hash'] : '0';
    }

    /**
     * Calculate block hash.
     */
    private function calculateBlockHash($block_data, $previous_hash) {
        $data_string = $previous_hash . json_encode($block_data) . time();
        return hash('sha256', $data_string);
    }

    /**
     * Get transaction data for recording.
     */
    private function getTransactionData($transaction_id, $transaction_type) {
        switch ($transaction_type) {
            case 'sale':
                return fetchRow("SELECT p.*, u.full_name as customer_name FROM penjualan p LEFT JOIN users u ON p.pelanggan_id = u.id WHERE p.id = ?", [$transaction_id], 'i');
            case 'payment':
                return fetchRow("SELECT pa.*, p.no_faktur FROM payments pa LEFT JOIN penjualan p ON pa.order_id = p.id WHERE pa.id = ?", [$transaction_id], 'i');
            case 'loan':
                return fetchRow("SELECT l.*, u.full_name as member_name FROM pinjaman l LEFT JOIN users u ON l.anggota_id = u.id WHERE l.id = ?", [$transaction_id], 'i');
            case 'savings':
                return fetchRow("SELECT ts.*, u.full_name as member_name FROM transaksi_simpanan ts LEFT JOIN users u ON ts.anggota_id = u.id WHERE ts.id = ?", [$transaction_id], 'i');
            default:
                return null;
        }
    }

    /**
     * Verify chain integrity.
     */
    private function verifyChainIntegrity() {
        $blocks = fetchAll("SELECT id FROM blockchain_blocks ORDER BY id ASC");
        $previous_hash = '0';

        foreach ($blocks as $block) {
            $verification = $this->verifyBlockIntegrity($block['id']);
            if (!$verification['is_valid'] || $verification['previous_hash'] !== $previous_hash) {
                return false;
            }
            $previous_hash = $verification['current_hash'];
        }

        return true;
    }

    /**
     * Verify single block integrity.
     */
    private function verifyBlockIntegrity($block_id) {
        $block = fetchRow("SELECT * FROM blockchain_blocks WHERE id = ?", [$block_id], 'i');

        if (!$block) return ['is_valid' => false];

        $block_data = json_decode($block['block_data'], true);
        $calculated_hash = $this->calculateBlockHash($block_data, $block['previous_hash']);

        return [
            'is_valid' => $calculated_hash === $block['current_hash'],
            'stored_hash' => $block['current_hash'],
            'calculated_hash' => $calculated_hash,
            'previous_hash' => $block['previous_hash'],
            'current_hash' => $block['current_hash']
        ];
    }

    /**
     * Get block summary for display.
     */
    private function getBlockSummary($decoded_data, $block_type) {
        switch ($block_type) {
            case 'sale':
                return "Penjualan #" . ($decoded_data['id'] ?? 'N/A') . " - Rp " . formatCurrency($decoded_data['total_harga'] ?? 0);
            case 'payment':
                return "Pembayaran #" . ($decoded_data['id'] ?? 'N/A') . " - Rp " . formatCurrency($decoded_data['amount'] ?? 0);
            case 'loan':
                return "Pinjaman #" . ($decoded_data['id'] ?? 'N/A') . " - Rp " . formatCurrency($decoded_data['jumlah_pinjaman'] ?? 0);
            case 'savings':
                return "Simpanan #" . ($decoded_data['id'] ?? 'N/A') . " - Rp " . formatCurrency($decoded_data['jumlah'] ?? 0);
            case 'governance':
                return "Keputusan: " . ($decoded_data['title'] ?? 'N/A');
            default:
                return "Block " . $block_type;
        }
    }
}
