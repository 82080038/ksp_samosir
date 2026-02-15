<?php
/**
 * Microservices Architecture - AI Service
 * Batch 8: Microservices Implementation
 */

require_once __DIR__ . '/../../config/database_multi.php';

class AIService
{
    private $db;
    private $apiKeys;
    
    public function __construct()
    {
        $this->db = DatabaseManager::getConnection('core');
        $this->apiKeys = [
            'openai' => $_ENV['OPENAI_API_KEY'] ?? null,
            'huggingface' => $_ENV['HUGGINGFACE_API_KEY'] ?? null,
            'google_ai' => $_ENV['GOOGLE_AI_API_KEY'] ?? null
        ];
    }
    
    /**
     * Generate AI product recommendations
     */
    public function generateProductRecommendations($userId, $context = [])
    {
        try {
            // Get user data
            $userData = $this->getUserData($userId);
            $purchaseHistory = $this->getUserPurchaseHistory($userId);
            $browsingHistory = $this->getUserBrowsingHistory($userId);
            
            // Prepare AI prompt
            $prompt = $this->buildRecommendationPrompt($userData, $purchaseHistory, $browsingHistory, $context);
            
            // Call AI API
            $recommendations = $this->callAI('recommendations', $prompt);
            
            if ($recommendations['success']) {
                // Save recommendations to database
                $this->saveRecommendations($userId, $recommendations['data']);
                
                return [
                    'success' => true,
                    'recommendations' => $recommendations['data'],
                    'model_used' => $recommendations['model'] ?? 'gpt-3.5-turbo'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to generate recommendations'];
        } catch (Exception $e) {
            error_log("AI recommendation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Recommendation service unavailable'];
        }
    }
    
    /**
     * Analyze customer sentiment
     */
    public function analyzeCustomerSentiment($customerId, $textData = [])
    {
        try {
            // Get customer interactions
            $interactions = $this->getCustomerInteractions($customerId);
            
            // Combine with provided text data
            $allText = array_merge($interactions, $textData);
            
            if (empty($allText)) {
                return ['success' => false, 'message' => 'No text data available for analysis'];
            }
            
            // Prepare sentiment analysis prompt
            $prompt = $this->buildSentimentPrompt($allText);
            
            // Call AI API
            $sentiment = $this->callAI('sentiment', $prompt);
            
            if ($sentiment['success']) {
                // Save sentiment analysis
                $this->saveSentimentAnalysis($customerId, $sentiment['data']);
                
                return [
                    'success' => true,
                    'sentiment' => $sentiment['data'],
                    'confidence' => $sentiment['confidence'] ?? 0.8,
                    'model_used' => $sentiment['model'] ?? 'sentiment-analysis'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to analyze sentiment'];
        } catch (Exception $e) {
            error_log("Sentiment analysis error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sentiment analysis service unavailable'];
        }
    }
    
    /**
     * Predict customer churn
     */
    public function predictCustomerChurn($customerId)
    {
        try {
            // Get customer features
            $features = $this->getChurnFeatures($customerId);
            
            if (empty($features)) {
                return ['success' => false, 'message' => 'Insufficient customer data'];
            }
            
            // Prepare ML prompt
            $prompt = $this->buildChurnPredictionPrompt($features);
            
            // Call AI API
            $prediction = $this->callAI('prediction', $prompt);
            
            if ($prediction['success']) {
                // Save prediction
                $this->saveChurnPrediction($customerId, $prediction['data']);
                
                return [
                    'success' => true,
                    'churn_probability' => $prediction['data']['probability'] ?? 0,
                    'risk_level' => $prediction['data']['risk_level'] ?? 'low',
                    'factors' => $prediction['data']['factors'] ?? [],
                    'model_used' => $prediction['model'] ?? 'churn-prediction'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to predict churn'];
        } catch (Exception $e) {
            error_log("Churn prediction error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Churn prediction service unavailable'];
        }
    }
    
    /**
     * Generate intelligent chatbot response
     */
    public function generateChatbotResponse($message, $context = [])
    {
        try {
            // Get conversation history
            $conversationHistory = $context['history'] ?? [];
            $userId = $context['user_id'] ?? null;
            
            // Get relevant knowledge base articles
            $kbArticles = $this->getRelevantKBArticles($message);
            
            // Build chat prompt
            $prompt = $this->buildChatPrompt($message, $conversationHistory, $kbArticles, $context);
            
            // Call AI API
            $response = $this->callAI('chat', $prompt);
            
            if ($response['success']) {
                // Log conversation
                $this->logChatInteraction($userId, $message, $response['data']['response']);
                
                return [
                    'success' => true,
                    'response' => $response['data']['response'],
                    'confidence' => $response['data']['confidence'] ?? 0.8,
                    'sources' => $response['data']['sources'] ?? [],
                    'model_used' => $response['model'] ?? 'gpt-3.5-turbo'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to generate response'];
        } catch (Exception $e) {
            error_log("Chatbot error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Chatbot service unavailable'];
        }
    }
    
    /**
     * Analyze document content
     */
    public function analyzeDocument($documentId, $analysisType = 'summary')
    {
        try {
            // Get document content
            $document = $this->getDocumentContent($documentId);
            
            if (!$document) {
                return ['success' => false, 'message' => 'Document not found'];
            }
            
            // Build analysis prompt
            $prompt = $this->buildDocumentAnalysisPrompt($document['content'], $analysisType);
            
            // Call AI API
            $analysis = $this->callAI('analysis', $prompt);
            
            if ($analysis['success']) {
                // Save analysis results
                $this->saveDocumentAnalysis($documentId, $analysisType, $analysis['data']);
                
                return [
                    'success' => true,
                    'analysis' => $analysis['data'],
                    'analysis_type' => $analysisType,
                    'model_used' => $analysis['model'] ?? 'gpt-3.5-turbo'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to analyze document'];
        } catch (Exception $e) {
            error_log("Document analysis error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Document analysis service unavailable'];
        }
    }
    
    /**
     * Generate sales forecast
     */
    public function generateSalesForecast($period = 30, $granularity = 'daily')
    {
        try {
            // Get historical sales data
            $historicalData = $this->getSalesHistory($period * 2); // Get double period for better prediction
            
            if (empty($historicalData)) {
                return ['success' => false, 'message' => 'Insufficient historical data'];
            }
            
            // Build forecast prompt
            $prompt = $this->buildForecastPrompt($historicalData, $period, $granularity);
            
            // Call AI API
            $forecast = $this->callAI('forecast', $prompt);
            
            if ($forecast['success']) {
                // Save forecast
                $this->saveSalesForecast($forecast['data'], $period, $granularity);
                
                return [
                    'success' => true,
                    'forecast' => $forecast['data'],
                    'period' => $period,
                    'granularity' => $granularity,
                    'confidence' => $forecast['confidence'] ?? 0.8,
                    'model_used' => $forecast['model'] ?? 'sales-forecast'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to generate forecast'];
        } catch (Exception $e) {
            error_log("Sales forecast error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sales forecast service unavailable'];
        }
    }
    
    /**
     * Call AI API
     */
    private function callAI($type, $prompt)
    {
        try {
            // Choose appropriate AI provider and model
            $provider = $this->selectProvider($type);
            
            switch ($provider) {
                case 'openai':
                    return $this->callOpenAI($type, $prompt);
                case 'huggingface':
                    return $this->callHuggingFace($type, $prompt);
                case 'google_ai':
                    return $this->callGoogleAI($type, $prompt);
                default:
                    return $this->callLocalAI($type, $prompt);
            }
        } catch (Exception $e) {
            error_log("AI API call error: " . $e->getMessage());
            return ['success' => false, 'message' => 'AI service unavailable'];
        }
    }
    
    /**
     * Select AI provider based on type and availability
     */
    private function selectProvider($type)
    {
        // Provider selection logic
        if ($this->apiKeys['openai'] && in_array($type, ['recommendations', 'chat', 'analysis'])) {
            return 'openai';
        } elseif ($this->apiKeys['huggingface'] && in_array($type, ['sentiment', 'prediction'])) {
            return 'huggingface';
        } elseif ($this->apiKeys['google_ai'] && in_array($type, ['forecast'])) {
            return 'google_ai';
        }
        
        return 'local'; // Fallback to local AI
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI($type, $prompt)
    {
        try {
            $apiKey = $this->apiKeys['openai'];
            if (!$apiKey) {
                throw new Exception('OpenAI API key not configured');
            }
            
            $model = $this->getOpenAIModel($type);
            
            $data = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->getSystemPrompt($type)],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $this->getMaxTokens($type),
                'temperature' => $this->getTemperature($type)
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('OpenAI API error: ' . $response);
            }
            
            $result = json_decode($response, true);
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            return [
                'success' => true,
                'data' => json_decode($content, true) ?? ['response' => $content],
                'model' => $model,
                'usage' => $result['usage'] ?? []
            ];
        } catch (Exception $e) {
            error_log("OpenAI API error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Call Hugging Face API
     */
    private function callHuggingFace($type, $prompt)
    {
        try {
            $apiKey = $this->apiKeys['huggingface'];
            if (!$apiKey) {
                throw new Exception('HuggingFace API key not configured');
            }
            
            $model = $this->getHuggingFaceModel($type);
            
            $data = [
                'inputs' => $prompt,
                'parameters' => [
                    'max_length' => $this->getMaxTokens($type),
                    'temperature' => $this->getTemperature($type)
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api-inference.huggingface.co/models/{$model}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('HuggingFace API error: ' . $response);
            }
            
            $result = json_decode($response, true);
            
            return [
                'success' => true,
                'data' => $result[0] ?? $result,
                'model' => $model
            ];
        } catch (Exception $e) {
            error_log("HuggingFace API error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Call Google AI API
     */
    private function callGoogleAI($type, $prompt)
    {
        try {
            $apiKey = $this->apiKeys['google_ai'];
            if (!$apiKey) {
                throw new Exception('Google AI API key not configured');
            }
            
            $model = $this->getGoogleAIModel($type);
            
            $data = [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => $this->getTemperature($type),
                    'maxOutputTokens' => $this->getMaxTokens($type)
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('Google AI API error: ' . $response);
            }
            
            $result = json_decode($response, true);
            $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            return [
                'success' => true,
                'data' => json_decode($content, true) ?? ['response' => $content],
                'model' => $model
            ];
        } catch (Exception $e) {
            error_log("Google AI API error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Call local AI (fallback)
     */
    private function callLocalAI($type, $prompt)
    {
        try {
            // Simple rule-based responses as fallback
            $response = $this->generateLocalResponse($type, $prompt);
            
            return [
                'success' => true,
                'data' => $response,
                'model' => 'local-rules'
            ];
        } catch (Exception $e) {
            error_log("Local AI error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Local AI service unavailable'];
        }
    }
    
    /**
     * Generate local rule-based response
     */
    private function generateLocalResponse($type, $prompt)
    {
        switch ($type) {
            case 'recommendations':
                return ['recommendations' => $this->getBasicRecommendations()];
            case 'sentiment':
                return ['sentiment' => 'neutral', 'confidence' => 0.5];
            case 'chat':
                return ['response' => 'I\'m sorry, I\'m currently experiencing technical difficulties. Please try again later.'];
            default:
                return ['response' => 'AI service temporarily unavailable'];
        }
    }
    
    /**
     * Get basic recommendations (fallback)
     */
    private function getBasicRecommendations()
    {
        try {
            $stmt = $this->db->query("
                SELECT p.id, p.nama_produk, p.harga, p.gambar
                FROM produk p
                WHERE p.stok > 0
                ORDER BY RAND()
                LIMIT 5
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Helper methods for data retrieval
     */
    private function getUserData($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getUserPurchaseHistory($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, dp.jumlah, dp.harga as unit_price
                FROM penjualan pen
                JOIN detail_penjualan dp ON pen.id = dp.id_penjualan
                JOIN produk p ON dp.id_produk = p.id
                WHERE pen.user_id = ? AND pen.status_pembayaran = 'lunas'
                ORDER BY pen.tanggal_penjualan DESC
                LIMIT 20
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getUserBrowsingHistory($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_behavior_tracking
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Model and parameter getters
     */
    private function getOpenAIModel($type)
    {
        $models = [
            'recommendations' => 'gpt-3.5-turbo',
            'chat' => 'gpt-3.5-turbo',
            'analysis' => 'gpt-4',
            'sentiment' => 'gpt-3.5-turbo',
            'prediction' => 'gpt-4',
            'forecast' => 'gpt-4'
        ];
        return $models[$type] ?? 'gpt-3.5-turbo';
    }
    
    private function getHuggingFaceModel($type)
    {
        $models = [
            'sentiment' => 'cardiffnlp/twitter-roberta-base-sentiment-latest',
            'prediction' => 'microsoft/DialoGPT-medium',
            'recommendations' => 'microsoft/DialoGPT-medium'
        ];
        return $models[$type] ?? 'microsoft/DialoGPT-medium';
    }
    
    private function getGoogleAIModel($type)
    {
        return 'gemini-1.5-flash';
    }
    
    private function getMaxTokens($type)
    {
        $tokens = [
            'recommendations' => 500,
            'chat' => 300,
            'analysis' => 1000,
            'sentiment' => 100,
            'prediction' => 200,
            'forecast' => 800
        ];
        return $tokens[$type] ?? 300;
    }
    
    private function getTemperature($type)
    {
        $temps = [
            'recommendations' => 0.7,
            'chat' => 0.8,
            'analysis' => 0.3,
            'sentiment' => 0.2,
            'prediction' => 0.1,
            'forecast' => 0.2
        ];
        return $temps[$type] ?? 0.5;
    }
    
    private function getSystemPrompt($type)
    {
        $prompts = [
            'recommendations' => 'You are a product recommendation AI. Analyze user data and provide personalized product recommendations in JSON format.',
            'chat' => 'You are a helpful customer service assistant for KSP Samosir. Provide accurate, helpful responses based on the knowledge base.',
            'analysis' => 'You are a document analysis AI. Analyze the provided text and return structured insights.',
            'sentiment' => 'You are a sentiment analysis AI. Analyze text and return sentiment classification with confidence scores.',
            'prediction' => 'You are a predictive analytics AI. Analyze data patterns and provide predictions with confidence levels.',
            'forecast' => 'You are a forecasting AI. Analyze historical data and generate accurate forecasts with confidence intervals.'
        ];
        return $prompts[$type] ?? 'You are a helpful AI assistant.';
    }
    
    /**
     * Save AI results to database (placeholder methods)
     */
    private function saveRecommendations($userId, $recommendations)
    {
        // Implementation to save recommendations
        return true;
    }
    
    private function saveSentimentAnalysis($customerId, $sentiment)
    {
        // Implementation to save sentiment analysis
        return true;
    }
    
    private function saveChurnPrediction($customerId, $prediction)
    {
        // Implementation to save churn prediction
        return true;
    }
    
    private function logChatInteraction($userId, $message, $response)
    {
        // Implementation to log chat interactions
        return true;
    }
    
    private function saveDocumentAnalysis($documentId, $type, $analysis)
    {
        // Implementation to save document analysis
        return true;
    }
    
    private function saveSalesForecast($forecast, $period, $granularity)
    {
        // Implementation to save sales forecast
        return true;
    }
    
    /**
     * Build prompts (placeholder methods)
     */
    private function buildRecommendationPrompt($userData, $purchaseHistory, $browsingHistory, $context)
    {
        return "Generate product recommendations based on user data";
    }
    
    private function buildSentimentPrompt($textData)
    {
        return "Analyze sentiment of: " . implode(' ', $textData);
    }
    
    private function buildChurnPredictionPrompt($features)
    {
        return "Predict churn probability based on: " . json_encode($features);
    }
    
    private function buildChatPrompt($message, $history, $kbArticles, $context)
    {
        return "Respond to: {$message} with context: " . json_encode($context);
    }
    
    private function buildDocumentAnalysisPrompt($content, $type)
    {
        return "Analyze document ({$type}): {$content}";
    }
    
    private function buildForecastPrompt($historicalData, $period, $granularity)
    {
        return "Generate {$period}-day {$granularity} forecast based on: " . json_encode($historicalData);
    }
    
    /**
     * Get additional data methods (placeholders)
     */
    private function getCustomerInteractions($customerId)
    {
        return [];
    }
    
    private function getRelevantKBArticles($message)
    {
        return [];
    }
    
    private function getDocumentContent($documentId)
    {
        return null;
    }
    
    private function getSalesHistory($days)
    {
        return [];
    }
    
    private function getChurnFeatures($customerId)
    {
        return [];
    }
}
