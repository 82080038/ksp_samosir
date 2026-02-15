<?php
/**
 * Knowledge Base System Controller
 * Batch 6: Customer Service Enhancement
 */

class KnowledgeBaseController
{
    private $db;
    
    public function __construct()
    {
        $this->db = getLegacyConnection();
    }
    
    /**
     * Get knowledge base homepage with categories and featured articles
     */
    public function getKnowledgeBaseHome()
    {
        try {
            return [
                'categories' => $this->getCategories(),
                'featured_articles' => $this->getFeaturedArticles(),
                'popular_articles' => $this->getPopularArticles(5),
                'recent_articles' => $this->getRecentArticles(5),
                'quick_links' => $this->getQuickLinks(),
                'faqs' => $this->getPopularFAQs(5)
            ];
        } catch (Exception $e) {
            error_log("Error getting knowledge base home: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all categories with article counts
     */
    public function getCategories($parentId = null)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    kc.*,
                    COUNT(ka.id) as article_count,
                    COUNT(CASE WHEN ka.status = 'published' THEN 1 END) as published_count
                FROM kb_categories kc
                LEFT JOIN kb_articles ka ON kc.id = ka.category_id
                WHERE kc.parent_id " . ($parentId ? "= ?" : "IS NULL") . "
                AND kc.is_active = 1
                GROUP BY kc.id
                ORDER BY kc.sort_order, kc.name
            ");
            
            if ($parentId !== null) {
                $stmt->execute([$parentId]);
            } else {
                $stmt->execute();
            }
            
            $categories = $stmt->fetchAll();
            
            // Get subcategories for each category
            foreach ($categories as &$category) {
                $category['subcategories'] = $this->getCategories($category['id']);
            }
            
            return $categories;
        } catch (Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get featured articles
     */
    public function getFeaturedArticles($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ka.*,
                    kc.name as category_name,
                    kc.color as category_color,
                    u.full_name as author_name
                FROM kb_articles ka
                JOIN kb_categories kc ON ka.category_id = kc.id
                JOIN users u ON ka.author_id = u.id
                WHERE ka.featured = 1 
                AND ka.status = 'published'
                ORDER BY ka.published_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting featured articles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get popular articles
     */
    public function getPopularArticles($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ka.*,
                    kc.name as category_name,
                    kc.color as category_color,
                    u.full_name as author_name
                FROM kb_articles ka
                JOIN kb_categories kc ON ka.category_id = kc.id
                JOIN users u ON ka.author_id = u.id
                WHERE ka.status = 'published'
                ORDER BY ka.views_count DESC, ka.published_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting popular articles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent articles
     */
    public function getRecentArticles($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ka.*,
                    kc.name as category_name,
                    kc.color as category_color,
                    u.full_name as author_name
                FROM kb_articles ka
                JOIN kb_categories kc ON ka.category_id = kc.id
                JOIN users u ON ka.author_id = u.id
                WHERE ka.status = 'published'
                ORDER BY ka.published_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent articles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get article by slug
     */
    public function getArticleBySlug($slug)
    {
        try {
            // Increment view count
            $this->db->prepare("UPDATE kb_articles SET views_count = views_count + 1 WHERE slug = ?")->execute([$slug]);
            
            $stmt = $this->db->prepare("
                SELECT 
                    ka.*,
                    kc.name as category_name,
                    kc.color as category_color,
                    u.full_name as author_name,
                    u.email as author_email
                FROM kb_articles ka
                JOIN kb_categories kc ON ka.category_id = kc.id
                JOIN users u ON ka.author_id = u.id
                WHERE ka.slug = ? AND ka.status = 'published'
            ");
            $stmt->execute([$slug]);
            $article = $stmt->fetch();
            
            if ($article) {
                // Get related articles
                $article['related_articles'] = $this->getRelatedArticles($article['id'], $article['category_id'], 5);
                // Get attachments
                $article['attachments'] = $this->getArticleAttachments($article['id']);
            }
            
            return $article;
        } catch (Exception $e) {
            error_log("Error getting article by slug: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get related articles
     */
    private function getRelatedArticles($articleId, $categoryId, $limit = 5)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ka.id,
                    ka.title,
                    ka.slug,
                    ka.excerpt,
                    kc.name as category_name,
                    kc.color as category_color
                FROM kb_articles ka
                JOIN kb_categories kc ON ka.category_id = kc.id
                WHERE ka.id != ? 
                AND ka.category_id = ?
                AND ka.status = 'published'
                ORDER BY ka.views_count DESC
                LIMIT ?
            ");
            $stmt->execute([$articleId, $categoryId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting related articles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get article attachments
     */
    private function getArticleAttachments($articleId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM kb_article_attachments 
                WHERE article_id = ? 
                ORDER BY filename
            ");
            $stmt->execute([$articleId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting article attachments: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search knowledge base
     */
    public function searchKnowledgeBase($query, $categoryId = null, $limit = 20)
    {
        try {
            // Log search
            $this->logSearch($query);
            
            $sql = "
                SELECT 
                    ka.*,
                    kc.name as category_name,
                    kc.color as category_color,
                    u.full_name as author_name,
                    MATCH(ka.title, ka.content, ka.search_keywords) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance_score
                FROM kb_articles ka
                JOIN kb_categories kc ON ka.category_id = kc.id
                JOIN users u ON ka.author_id = u.id
                WHERE ka.status = 'published'
                AND MATCH(ka.title, ka.content, ka.search_keywords) AGAINST(? IN NATURAL LANGUAGE MODE)
            ";
            
            $params = [$query, $query];
            
            if ($categoryId) {
                $sql .= " AND ka.category_id = ?";
                $params[] = $categoryId;
            }
            
            $sql .= " ORDER BY relevance_score DESC, ka.views_count DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            
            // Update search log with results count
            if (!empty($results)) {
                $this->updateSearchLog($query, count($results));
            }
            
            return $results;
        } catch (Exception $e) {
            error_log("Error searching knowledge base: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log search query
     */
    private function logSearch($query)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO kb_search_logs 
                (user_id, session_id, search_query, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                session_id(),
                $query,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Error logging search: " . $e->getMessage());
        }
    }
    
    /**
     * Update search log with results count
     */
    private function updateSearchLog($query, $resultsCount)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE kb_search_logs 
                SET results_count = ? 
                WHERE search_query = ? 
                AND session_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$resultsCount, $query, session_id()]);
        } catch (Exception $e) {
            error_log("Error updating search log: " . $e->getMessage());
        }
    }
    
    /**
     * Get FAQs
     */
    public function getFAQs($categoryId = null)
    {
        try {
            $sql = "
                SELECT 
                    kf.*,
                    kc.name as category_name,
                    u.full_name as created_by_name
                FROM kb_faqs kf
                LEFT JOIN kb_categories kc ON kf.category_id = kc.id
                JOIN users u ON kf.created_by = u.id
                WHERE kf.is_active = 1
            ";
            
            $params = [];
            
            if ($categoryId) {
                $sql .= " AND kf.category_id = ?";
                $params[] = $categoryId;
            }
            
            $sql .= " ORDER BY kf.order_index, kf.question";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting FAQs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get popular FAQs
     */
    public function getPopularFAQs($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    kf.*,
                    kc.name as category_name
                FROM kb_faqs kf
                LEFT JOIN kb_categories kc ON kf.category_id = kc.id
                WHERE kf.is_active = 1
                ORDER BY kf.views_count DESC, kf.helpful_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting popular FAQs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get quick links
     */
    public function getQuickLinks($categoryId = null)
    {
        try {
            $sql = "
                SELECT 
                    kql.*,
                    kc.name as category_name,
                    kc.color as category_color
                FROM kb_quick_links kql
                LEFT JOIN kb_categories kc ON kql.category_id = kc.id
                WHERE kql.is_active = 1
            ";
            
            $params = [];
            
            if ($categoryId) {
                $sql .= " AND kql.category_id = ?";
                $params[] = $categoryId;
            }
            
            $sql .= " ORDER BY kql.order_index, kql.title";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting quick links: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Submit article feedback
     */
    public function submitArticleFeedback($articleId, $feedbackType, $rating = null, $comment = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO kb_article_feedback 
                (article_id, user_id, feedback_type, rating, comment, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $articleId,
                $_SESSION['user_id'] ?? null,
                $feedbackType,
                $rating,
                $comment,
                $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
            
            if ($result) {
                // Update article feedback counts
                if ($feedbackType === 'helpful') {
                    $this->db->prepare("UPDATE kb_articles SET helpful_count = helpful_count + 1 WHERE id = ?")->execute([$articleId]);
                } elseif ($feedbackType === 'not_helpful') {
                    $this->db->prepare("UPDATE kb_articles SET not_helpful_count = not_helpful_count + 1 WHERE id = ?")->execute([$articleId]);
                }
                
                return ['success' => true, 'message' => 'Feedback submitted successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to submit feedback'];
        } catch (Exception $e) {
            error_log("Error submitting feedback: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Get knowledge base analytics
     */
    public function getKnowledgeBaseAnalytics($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');
            
            return [
                'overview' => $this->getOverviewStats($startDate, $endDate),
                'popular_articles' => $this->getPopularArticlesAnalytics($startDate, $endDate),
                'search_analytics' => $this->getSearchAnalytics($startDate, $endDate),
                'category_performance' => $this->getCategoryPerformance($startDate, $endDate),
                'feedback_summary' => $this->getFeedbackSummary($startDate, $endDate)
            ];
        } catch (Exception $e) {
            error_log("Error getting knowledge base analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get overview statistics
     */
    private function getOverviewStats($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT ka.id) as total_articles,
                    COUNT(DISTINCT CASE WHEN ka.status = 'published' THEN ka.id END) as published_articles,
                    SUM(ka.views_count) as total_views,
                    COUNT(DISTINCT ksl.user_id) as unique_searchers,
                    COUNT(DISTINCT ksl.search_query) as unique_searches
                FROM kb_articles ka
                LEFT JOIN kb_search_logs ksl ON ksl.created_at BETWEEN ? AND ?
                WHERE ka.created_at BETWEEN ? AND ?
            ");
            $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting overview stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get popular articles analytics
     */
    private function getPopularArticlesAnalytics($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ka.title,
                    ka.views_count,
                    ka.helpful_count,
                    ka.not_helpful_count,
                    kc.name as category_name,
                    ROUND(ka.helpful_count * 100.0 / NULLIF(ka.helpful_count + ka.not_helpful_count, 0), 2) as helpful_percentage
                FROM kb_articles ka
                JOIN kb_categories kc ON ka.category_id = kc.id
                WHERE ka.status = 'published'
                ORDER BY ka.views_count DESC
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting popular articles analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get search analytics
     */
    private function getSearchAnalytics($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    search_query,
                    COUNT(*) as search_count,
                    COUNT(DISTINCT user_id) as unique_users,
                    AVG(results_count) as avg_results,
                    COUNT(clicked_article_id) as click_count
                FROM kb_search_logs
                WHERE created_at BETWEEN ? AND ?
                GROUP BY search_query
                HAVING search_count >= 2
                ORDER BY search_count DESC
                LIMIT 10
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting search analytics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get category performance
     */
    private function getCategoryPerformance($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    kc.name,
                    COUNT(ka.id) as article_count,
                    SUM(ka.views_count) as total_views,
                    AVG(ka.views_count) as avg_views_per_article
                FROM kb_categories kc
                LEFT JOIN kb_articles ka ON kc.id = ka.category_id AND ka.status = 'published'
                GROUP BY kc.id, kc.name
                ORDER BY total_views DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting category performance: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get feedback summary
     */
    private function getFeedbackSummary($startDate, $endDate)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    feedback_type,
                    COUNT(*) as feedback_count,
                    AVG(rating) as avg_rating
                FROM kb_article_feedback
                WHERE created_at BETWEEN ? AND ?
                GROUP BY feedback_type
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting feedback summary: " . $e->getMessage());
            return [];
        }
    }
}
