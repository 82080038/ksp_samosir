<?php
/**
 * Voting Controller
 * Based on KSP-PEB voting.php analysis
 */

class VotingController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get active voting sessions
     */
    public function getActiveSessions()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT vs.*, u.name as created_by_name,
                       COUNT(DISTINCT vb.user_id) as voter_count,
                       COUNT(DISTINCT va.user_id) as attendee_count
                FROM voting_sessions vs
                LEFT JOIN users u ON vs.created_by = u.id
                LEFT JOIN voting_ballots vb ON vs.id = vb.session_id
                LEFT JOIN voting_attendance va ON vs.id = va.session_id
                WHERE vs.status = 'active' 
                AND vs.start_time <= NOW() 
                AND vs.end_time >= NOW()
                GROUP BY vs.id
                ORDER BY vs.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting active sessions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get voting session by ID
     */
    public function getSessionById($sessionId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT vs.*, u.name as created_by_name
                FROM voting_sessions vs
                LEFT JOIN users u ON vs.created_by = u.id
                WHERE vs.id = ?
            ");
            $stmt->execute([$sessionId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting session: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get voting options for session
     */
    public function getSessionOptions($sessionId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT vo.*, 
                       COUNT(vb.id) as votes_count
                FROM voting_options vo
                LEFT JOIN voting_ballots vb ON vo.id = vb.option_id
                WHERE vo.session_id = ?
                GROUP BY vo.id
                ORDER BY vo.option_order
            ");
            $stmt->execute([$sessionId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting session options: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user has voted in session
     */
    public function hasUserVoted($sessionId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM voting_ballots 
                WHERE session_id = ? AND user_id = ?
            ");
            $stmt->execute([$sessionId, $userId]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("Error checking vote: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Submit vote
     */
    public function submitVote($sessionId, $userId, $optionId)
    {
        try {
            // Check if session is active
            $session = $this->getSessionById($sessionId);
            if (!$session || $session['status'] !== 'active') {
                return ['success' => false, 'message' => 'Voting session is not active'];
            }
            
            // Check if user has already voted
            if ($this->hasUserVoted($sessionId, $userId)) {
                return ['success' => false, 'message' => 'You have already voted in this session'];
            }
            
            // Check if session is still within time range
            if (strtotime($session['start_time']) > time() || strtotime($session['end_time']) < time()) {
                return ['success' => false, 'message' => 'Voting session is not within valid time range'];
            }
            
            // Record attendance
            $this->recordAttendance($sessionId, $userId);
            
            // Insert vote
            $stmt = $this->db->prepare("
                INSERT INTO voting_ballots (session_id, user_id, option_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $sessionId,
                $userId,
                $optionId,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            if ($result) {
                // Update vote count
                $this->updateVoteCount($optionId);
                
                // Check if minimum votes reached
                $this->checkMinimumVotes($sessionId);
                
                return ['success' => true, 'message' => 'Vote submitted successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to submit vote'];
        } catch (Exception $e) {
            error_log("Error submitting vote: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get voting results
     */
    public function getVotingResults($sessionId)
    {
        try {
            $session = $this->getSessionById($sessionId);
            if (!$session) {
                return ['success' => false, 'message' => 'Session not found'];
            }
            
            // Check if results are visible
            if ($session['status'] !== 'closed' && !$session['results_visible_after_close']) {
                return ['success' => false, 'message' => 'Results are not yet visible'];
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    vo.option_text,
                    vo.votes_count,
                    ROUND(vo.votes_count * 100.0 / (
                        SELECT COUNT(*) FROM voting_ballots WHERE session_id = ?
                    ), 2) as percentage
                FROM voting_options vo
                WHERE vo.session_id = ?
                ORDER BY vo.votes_count DESC
            ");
            $stmt->execute([$sessionId, $sessionId]);
            $results = $stmt->fetchAll();
            
            $totalVotes = $this->getTotalVotes($sessionId);
            
            return [
                'success' => true,
                'session' => $session,
                'results' => $results,
                'total_votes' => $totalVotes
            ];
        } catch (Exception $e) {
            error_log("Error getting results: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get results'];
        }
    }
    
    /**
     * Get user's voting history
     */
    public function getUserVotingHistory($userId, $limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    vb.voted_at,
                    vs.title,
                    vs.session_type,
                    vs.status,
                    vo.option_text as voted_option
                FROM voting_ballots vb
                JOIN voting_sessions vs ON vb.session_id = vs.id
                JOIN voting_options vo ON vb.option_id = vo.id
                WHERE vb.user_id = ?
                ORDER BY vb.voted_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting voting history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create new voting session
     */
    public function createSession($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO voting_sessions 
                (title, description, session_type, start_time, end_time, created_by, 
                 min_votes_required, allow_abstain, is_secret, results_visible_after_close)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['title'],
                $data['description'] ?? '',
                $data['session_type'] ?? 'umum',
                $data['start_time'],
                $data['end_time'],
                $data['created_by'],
                $data['min_votes_required'] ?? 1,
                $data['allow_abstain'] ?? true,
                $data['is_secret'] ?? false,
                $data['results_visible_after_close'] ?? true
            ]);
            
            if ($result) {
                $sessionId = $this->db->lastInsertId();
                
                // Add voting options
                if (!empty($data['options'])) {
                    foreach ($data['options'] as $index => $option) {
                        $this->addVotingOption($sessionId, $option, $index + 1);
                    }
                }
                
                return ['success' => true, 'session_id' => $sessionId];
            }
            
            return ['success' => false, 'message' => 'Failed to create session'];
        } catch (Exception $e) {
            error_log("Error creating session: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Add voting option to session
     */
    private function addVotingOption($sessionId, $optionText, $order)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO voting_options (session_id, option_text, option_order)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$sessionId, $optionText, $order]);
        } catch (Exception $e) {
            error_log("Error adding option: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record attendance
     */
    private function recordAttendance($sessionId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO voting_attendance (session_id, user_id, attendance_type)
                VALUES (?, ?, 'hadir')
            ");
            return $stmt->execute([$sessionId, $userId]);
        } catch (Exception $e) {
            error_log("Error recording attendance: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update vote count for option
     */
    private function updateVoteCount($optionId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE voting_options 
                SET votes_count = (
                    SELECT COUNT(*) FROM voting_ballots WHERE option_id = ?
                )
                WHERE id = ?
            ");
            return $stmt->execute([$optionId, $optionId]);
        } catch (Exception $e) {
            error_log("Error updating vote count: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if minimum votes reached and close session if needed
     */
    private function checkMinimumVotes($sessionId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT vs.min_votes_required, COUNT(DISTINCT vb.user_id) as current_votes
                FROM voting_sessions vs
                LEFT JOIN voting_ballots vb ON vs.id = vb.session_id
                WHERE vs.id = ?
                GROUP BY vs.id
            ");
            $stmt->execute([$sessionId]);
            $result = $stmt->fetch();
            
            if ($result && $result['current_votes'] >= $result['min_votes_required']) {
                // Auto-close session when minimum votes reached
                $stmt = $this->db->prepare("
                    UPDATE voting_sessions 
                    SET status = 'closed' 
                    WHERE id = ?
                ");
                $stmt->execute([$sessionId]);
            }
        } catch (Exception $e) {
            error_log("Error checking minimum votes: " . $e->getMessage());
        }
    }
    
    /**
     * Get total votes for session
     */
    private function getTotalVotes($sessionId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM voting_ballots WHERE session_id = ?
            ");
            $stmt->execute([$sessionId]);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total votes: " . $e->getMessage());
            return 0;
        }
    }
}
