<?php
/**
 * Automated Collections System for KSP Samosir
 * AI-powered debt collection with personalized communication strategies
 */

class AutomatedCollections {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: getConnection();
    }

    /**
     * Analyze overdue loans and create collection strategies
     */
    public function analyzeOverdueLoans() {
        $overdueLoans = $this->getOverdueLoans();

        foreach ($overdueLoans as $loan) {
            $strategy = $this->determineCollectionStrategy($loan);
            $this->createCollectionRecord($loan, $strategy);
        }

        return count($overdueLoans);
    }

    /**
     * Execute automated collection actions
     */
    public function executeCollectionActions() {
        $pendingActions = $this->getPendingActions();

        $results = [
            'executed' => 0,
            'failed' => 0,
            'skipped' => 0
        ];

        foreach ($pendingActions as $action) {
            if ($this->shouldExecuteAction($action)) {
                $success = $this->executeAction($action);
                $results[$success ? 'executed' : 'failed']++;
            } else {
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Get overdue loans requiring collection
     */
    private function getOverdueLoans() {
        return fetchAll("
            SELECT
                p.id as loan_id,
                p.member_id,
                a.nama_lengkap,
                a.no_hp,
                a.email,
                p.jumlah_pinjaman,
                p.tanggal_pencairan,
                DATEDIFF(CURDATE(), p.tanggal_pencairan) as days_since_disbursement,
                COALESCE(lp.last_payment_date, p.tanggal_pencairan) as last_payment_date,
                DATEDIFF(CURDATE(), COALESCE(lp.last_payment_date, p.tanggal_pencairan)) as days_overdue,
                (p.jumlah_pinjaman - COALESCE(lp.total_paid, 0)) as amount_overdue,
                COUNT(lp.id) as total_installments,
                COUNT(CASE WHEN lp.status = 'paid_late' THEN 1 END) as late_payments
            FROM pinjaman p
            JOIN anggota a ON p.member_id = a.id
            LEFT JOIN (
                SELECT
                    loan_id,
                    MAX(payment_date) as last_payment_date,
                    SUM(amount_paid) as total_paid
                FROM loan_payments
                GROUP BY loan_id
            ) lp ON p.id = lp.loan_id
            WHERE p.status = 'aktif'
            AND DATEDIFF(CURDATE(), COALESCE(lp.last_payment_date, p.tanggal_pencairan)) > 30
            GROUP BY p.id, a.id, a.nama_lengkap, a.no_hp, a.email, p.jumlah_pinjaman, p.tanggal_pencairan, lp.last_payment_date, lp.total_paid
            HAVING amount_overdue > 0
            ORDER BY days_overdue DESC, amount_overdue DESC
        ", [], '');
    }

    /**
     * Determine optimal collection strategy based on loan data
     */
    private function determineCollectionStrategy($loan) {
        $daysOverdue = $loan['days_overdue'];
        $amountOverdue = $loan['amount_overdue'];
        $latePayments = $loan['late_payments'];

        // Strategy selection logic
        if ($daysOverdue <= 60 && $amountOverdue < 500000) {
            return 'gentle_reminder';
        } elseif ($daysOverdue <= 90 && $amountOverdue < 1000000) {
            return 'standard_collection';
        } elseif ($daysOverdue <= 120 || $latePayments < 3) {
            return 'intensive_collection';
        } else {
            return 'legal_action_preparation';
        }
    }

    /**
     * Create collection record
     */
    private function createCollectionRecord($loan, $strategy) {
        // Check if collection record already exists
        $existing = fetchRow("
            SELECT id FROM collection_automation
            WHERE loan_id = ? AND status = 'active'
        ", [$loan['loan_id']], 'i');

        if ($existing) {
            return $existing['id'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO collection_automation
            (loan_id, member_id, collection_strategy, overdue_days, amount_overdue,
             last_payment_date, next_action_date, priority_level, status)
            VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 DAY), ?, 'active')
        ");

        $priority = $this->getPriorityLevel($loan['days_overdue'], $loan['amount_overdue']);

        $stmt->execute([
            $loan['loan_id'],
            $loan['member_id'],
            $strategy,
            $loan['days_overdue'],
            $loan['amount_overdue'],
            $loan['last_payment_date'],
            $priority
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Get pending collection actions
     */
    private function getPendingActions() {
        return fetchAll("
            SELECT ca.*, a.nama_lengkap, a.no_hp, a.email
            FROM collection_automation ca
            JOIN anggota a ON ca.member_id = a.id
            WHERE ca.status = 'active'
            AND ca.next_action_date <= CURDATE()
            ORDER BY ca.priority_level DESC, ca.amount_overdue DESC
        ", [], '');
    }

    /**
     * Determine if action should be executed
     */
    private function shouldExecuteAction($action) {
        // Check business hours (skip weekends and outside business hours)
        $currentHour = date('H');
        $currentDay = date('N'); // 1 = Monday, 7 = Sunday

        if ($currentDay >= 6) { // Saturday = 6, Sunday = 7
            return false;
        }

        if ($currentHour < 8 || $currentHour > 17) { // Outside 8 AM - 5 PM
            return false;
        }

        // Check if member has been contacted recently (avoid spam)
        $recentContacts = fetchRow("
            SELECT COUNT(*) as contact_count
            FROM collection_actions
            WHERE collection_id = ?
            AND executed_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ", [$action['id']], 'i');

        return $recentContacts['contact_count'] < 2; // Max 2 contacts per day
    }

    /**
     * Execute collection action
     */
    private function executeAction($action) {
        try {
            $template = $this->getCollectionTemplate($action);
            $communication = $this->personalizeCommunication($template, $action);

            // Execute based on action type
            switch ($action['collection_strategy']) {
                case 'gentle_reminder':
                    return $this->sendEmail($action, $communication);

                case 'standard_collection':
                    return $this->sendSMS($action, $communication) &&
                           $this->scheduleFollowUpCall($action);

                case 'intensive_collection':
                    return $this->sendEmail($action, $communication) &&
                           $this->sendSMS($action, $communication) &&
                           $this->initiatePhoneCall($action);

                case 'legal_action_preparation':
                    return $this->sendFormalNotice($action, $communication) &&
                           $this->notifyManagement($action);

                default:
                    return false;
            }
        } catch (Exception $e) {
            error_log("Collection action failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get appropriate collection template
     */
    private function getCollectionTemplate($action) {
        $daysOverdue = $action['overdue_days'];

        // Find matching template
        $template = fetchRow("
            SELECT * FROM collection_templates
            WHERE overdue_days_min <= ?
            AND overdue_days_max >= ?
            AND priority_level = ?
            AND is_active = TRUE
            ORDER BY success_rate DESC
            LIMIT 1
        ", [$daysOverdue, $daysOverdue, $action['priority_level']], 'iis');

        // Fallback to default template
        if (!$template) {
            $template = fetchRow("
                SELECT * FROM collection_templates
                WHERE template_name = 'Friendly Reminder'
                LIMIT 1
            ", [], '');
        }

        return $template;
    }

    /**
     * Personalize communication content
     */
    private function personalizeCommunication($template, $action) {
        $content = $template['content'];
        $subject = $template['subject'];

        // Replace variables
        $replacements = [
            '{member_name}' => $action['nama_lengkap'],
            '{amount_due}' => 'Rp ' . number_format($action['amount_overdue'], 0, ',', '.'),
            '{due_date}' => date('d M Y', strtotime($action['last_payment_date'] . ' +30 days')),
            '{days_overdue}' => $action['overdue_days'],
            '{loan_id}' => $action['loan_id']
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        $subject = str_replace(array_keys($replacements), array_values($replacements), $subject);

        return [
            'subject' => $subject,
            'content' => $content,
            'type' => $template['template_type']
        ];
    }

    /**
     * Send email communication
     */
    private function sendEmail($action, $communication) {
        if (empty($action['email'])) {
            return false; // No email address
        }

        // Log the action
        $this->logCollectionAction($action['id'], 'email', 'sent', $communication);

        // Here you would integrate with email service (SendGrid, AWS SES, etc.)
        // For now, just simulate sending
        error_log("Email sent to {$action['email']}: {$communication['subject']}");

        return true;
    }

    /**
     * Send SMS communication
     */
    private function sendSMS($action, $communication) {
        if (empty($action['no_hp'])) {
            return false; // No phone number
        }

        // Log the action
        $this->logCollectionAction($action['id'], 'sms', 'sent', $communication);

        // Here you would integrate with SMS service (Twilio, Nexmo, etc.)
        // For now, just simulate sending
        error_log("SMS sent to {$action['no_hp']}: " . substr($communication['content'], 0, 100) . "...");

        return true;
    }

    /**
     * Schedule follow-up call
     */
    private function scheduleFollowUpCall($action) {
        // Schedule call for next business day
        $nextBusinessDay = $this->getNextBusinessDay();
        $this->logCollectionAction($action['id'], 'call', 'scheduled', [
            'scheduled_date' => $nextBusinessDay,
            'reason' => 'Follow-up on collection'
        ]);

        return true;
    }

    /**
     * Initiate phone call (for intensive collection)
     */
    private function initiatePhoneCall($action) {
        // Here you would integrate with telephony service
        // For now, just log the attempt
        $this->logCollectionAction($action['id'], 'call', 'initiated', [
            'reason' => 'Intensive collection follow-up'
        ]);

        return true;
    }

    /**
     * Send formal notice
     */
    private function sendFormalNotice($action, $communication) {
        // Send certified mail or formal notice
        $this->logCollectionAction($action['id'], 'formal_notice', 'sent', $communication);

        return true;
    }

    /**
     * Notify management for high-risk cases
     */
    private function notifyManagement($action) {
        // Send notification to management team
        $this->logCollectionAction($action['id'], 'management_notification', 'sent', [
            'message' => 'High-risk collection case requires management attention'
        ]);

        return true;
    }

    /**
     * Log collection action
     */
    private function logCollectionAction($collectionId, $actionType, $status, $details) {
        $stmt = $this->pdo->prepare("
            INSERT INTO collection_actions
            (collection_id, action_type, action_status, executed_date, response_details)
            VALUES (?, ?, ?, NOW(), ?)
        ");

        $stmt->execute([
            $collectionId,
            $actionType,
            $status,
            json_encode($details)
        ]);
    }

    /**
     * Get priority level based on overdue days and amount
     */
    private function getPriorityLevel($daysOverdue, $amountOverdue) {
        if ($daysOverdue > 120 || $amountOverdue > 5000000) {
            return 'critical';
        } elseif ($daysOverdue > 90 || $amountOverdue > 2000000) {
            return 'high';
        } elseif ($daysOverdue > 60 || $amountOverdue > 1000000) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get next business day
     */
    private function getNextBusinessDay() {
        $date = new DateTime('tomorrow');
        $dayOfWeek = $date->format('N');

        // If tomorrow is Saturday (6) or Sunday (7), skip to Monday
        if ($dayOfWeek >= 6) {
            $date->modify('next monday');
        }

        return $date->format('Y-m-d');
    }

    /**
     * Update collection status based on payment received
     */
    public function updateCollectionStatus($loanId, $paymentReceived = false) {
        if ($paymentReceived) {
            // Mark as completed
            $stmt = $this->pdo->prepare("
                UPDATE collection_automation
                SET status = 'completed', updated_at = NOW()
                WHERE loan_id = ?
            ");
            $stmt->execute([$loanId]);
        } else {
            // Update overdue information
            $loan = $this->getOverdueLoans();
            foreach ($loan as $loanData) {
                if ($loanData['loan_id'] == $loanId) {
                    $stmt = $this->pdo->prepare("
                        UPDATE collection_automation
                        SET overdue_days = ?, amount_overdue = ?, updated_at = NOW()
                        WHERE loan_id = ?
                    ");
                    $stmt->execute([
                        $loanData['days_overdue'],
                        $loanData['amount_overdue'],
                        $loanId
                    ]);
                    break;
                }
            }
        }
    }

    /**
     * Get collection performance metrics
     */
    public function getCollectionMetrics() {
        $metrics = [];

        // Recovery rate
        $recovery = fetchRow("
            SELECT
                (SELECT COUNT(*) FROM collection_automation WHERE status = 'completed') /
                (SELECT COUNT(*) FROM collection_automation) * 100 as recovery_rate
        ", [], '');
        $metrics['recovery_rate'] = round($recovery['recovery_rate'] ?? 0, 2);

        // Average days to resolution
        $avgDays = fetchRow("
            SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_resolution_days
            FROM collection_automation
            WHERE status = 'completed'
        ", [], '');
        $metrics['avg_resolution_days'] = round($avgDays['avg_resolution_days'] ?? 0, 1);

        // Contact effectiveness
        $effectiveness = fetchRow("
            SELECT
                (SELECT COUNT(*) FROM collection_actions WHERE action_status = 'responded') /
                (SELECT COUNT(*) FROM collection_actions WHERE action_status IN ('sent', 'delivered')) * 100 as contact_effectiveness
        ", [], '');
        $metrics['contact_effectiveness'] = round($effectiveness['contact_effectiveness'] ?? 0, 2);

        return $metrics;
    }
}

// Helper functions
function analyzeOverdueLoans() {
    $collections = new AutomatedCollections();
    return $collections->analyzeOverdueLoans();
}

function executeCollectionActions() {
    $collections = new AutomatedCollections();
    return $collections->executeCollectionActions();
}

function updateCollectionStatus($loanId, $paymentReceived = false) {
    $collections = new AutomatedCollections();
    $collections->updateCollectionStatus($loanId, $paymentReceived);
}

function getCollectionMetrics() {
    $collections = new AutomatedCollections();
    return $collections->getCollectionMetrics();
}
