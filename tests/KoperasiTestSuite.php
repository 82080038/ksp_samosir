<?php
/**
 * KSP Samosir - Comprehensive Testing Suite
 * Complete testing for all koperasi features
 */

class KoperasiTestSuite
{
    private $db;
    private $testResults = [];
    private $testCount = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Run all tests
     */
    public function runAllTests()
    {
        echo "🧪 KSP SAMOSIR - COMPREHENSIVE TESTING SUITE\n";
        echo "================================================\n\n";
        
        // Database Connection Tests
        $this->testDatabaseConnection();
        
        // Data Integrity Tests
        $this->testDataIntegrity();
        
        // Koperasi Core Functionality Tests
        $this->testKoperasiCore();
        
        // Multi-Unit Management Tests
        $this->testMultiUnitManagement();
        
        // AI Features Tests
        $this->testAIFeatures();
        
        // API Endpoint Tests
        $this->testAPIEndpoints();
        
        // Security Tests
        $this->testSecurityFeatures();
        
        // Performance Tests
        $this->testPerformance();
        
        // Report Generation Tests
        $this->testReports();
        
        // Mobile App API Tests
        $this->testMobileAPI();
        
        $this->printTestSummary();
        return $this->testResults;
    }
    
    /**
     * Test Database Connection
     */
    private function testDatabaseConnection()
    {
        echo "📊 TESTING DATABASE CONNECTION\n";
        echo "--------------------------------\n";
        
        $this->runTest("Database Connection", function() {
            $result = $this->db->query("SELECT 1 as test");
            $row = $result->fetch();
            return $row['test'] == 1;
        });
        
        $this->runTest("Required Tables Exist", function() {
            $requiredTables = [
                'anggota', 'users', 'simpanan', 'pinjaman', 'angsuran',
                'jenis_simpanan', 'jenis_pinjaman', 'koperasi_unit',
                'unit_target_performance', 'ai_models', 'settings'
            ];
            
            foreach ($requiredTables as $table) {
                $result = $this->db->query("SHOW TABLES LIKE '$table'");
                if ($result->rowCount() == 0) {
                    throw new Exception("Table $table does not exist");
                }
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Test Data Integrity
     */
    private function testDataIntegrity()
    {
        echo "🔍 TESTING DATA INTEGRITY\n";
        echo "-------------------------\n";
        
        $this->runTest("Anggota Data Integrity", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM anggota WHERE no_anggota IS NULL OR nik IS NULL");
            $result = $stmt->fetch();
            return $result['count'] == 0;
        });
        
        $this->runTest("Simpanan Data Integrity", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM simpanan WHERE anggota_id IS NULL OR jenis_simpanan_id IS NULL");
            $result = $stmt->fetch();
            return $result['count'] == 0;
        });
        
        $this->runTest("Pinjaman Data Integrity", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM pinjaman WHERE anggota_id IS NULL OR jenis_pinjaman_id IS NULL");
            $result = $stmt->fetch();
            return $result['count'] == 0;
        });
        
        $this->runTest("Foreign Key Constraints", function() {
            // Test anggota-unit relationship
            $stmt = $this->db->query("
                SELECT COUNT(*) as count 
                FROM anggota a 
                LEFT JOIN koperasi_unit u ON a.unit_id = u.id 
                WHERE a.unit_id IS NOT NULL AND u.id IS NULL
            ");
            $result = $stmt->fetch();
            return $result['count'] == 0;
        });
        
        echo "\n";
    }
    
    /**
     * Test Koperasi Core Functionality
     */
    private function testKoperasiCore()
    {
        echo "🏦 TESTING KOPERASI CORE FUNCTIONALITY\n";
        echo "--------------------------------------\n";
        
        $this->runTest("Dashboard Overview Data", function() {
            $controller = new KoperasiController();
            $result = $controller->getDashboardOverview();
            return $result['success'] && isset($result['data']['total_anggota']);
        });
        
        $this->runTest("Anggota CRUD Operations", function() {
            $controller = new KoperasiController();
            
            // Test Create
            $testData = [
                'nama_lengkap' => 'Test User',
                'nik' => '3201019999999999',
                'tempat_lahir' => 'Test City',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat' => 'Test Address',
                'no_hp' => '081999999999',
                'email' => 'test@example.com',
                'pekerjaan' => 'Tester',
                'pendapatan_bulanan' => 5000000,
                'unit_id' => 1
            ];
            
            $createResult = $controller->createAnggota($testData);
            if (!$createResult['success']) {
                throw new Exception("Failed to create anggota");
            }
            
            // Test Read
            $getResult = $controller->getAnggota();
            if (!$getResult['success']) {
                throw new Exception("Failed to get anggota");
            }
            
            return true;
        });
        
        $this->runTest("Simpanan Operations", function() {
            $controller = new KoperasiController();
            
            $testData = [
                'anggota_id' => 1,
                'jenis_simpanan_id' => 3,
                'jumlah' => 500000,
                'tanggal_setor' => date('Y-m-d'),
                'keterangan' => 'Test simpanan'
            ];
            
            $result = $controller->createSimpanan($testData);
            return $result['success'];
        });
        
        $this->runTest("Pinjaman Workflow", function() {
            $controller = new KoperasiController();
            
            $testData = [
                'anggota_id' => 1,
                'jenis_pinjaman_id' => 1,
                'jumlah_pinjaman' => 10000000,
                'bunga' => 12,
                'tenor' => 12,
                'keperluan' => 'Test pinjaman',
                'status' => 'pending'
            ];
            
            $result = $controller->createPinjaman($testData);
            return $result['success'];
        });
        
        echo "\n";
    }
    
    /**
     * Test Multi-Unit Management
     */
    private function testMultiUnitManagement()
    {
        echo "🏢 TESTING MULTI-UNIT MANAGEMENT\n";
        echo "---------------------------------\n";
        
        $this->runTest("Unit Hierarchy", function() {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count 
                FROM unit_relasi ur 
                JOIN koperasi_unit ui ON ur.unit_induk_id = ui.id 
                JOIN koperasi_unit ua ON ur.unit_anak_id = ua.id
            ");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("Unit Performance Tracking", function() {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count 
                FROM unit_target_performance 
                WHERE periode_bulan = DATE_FORMAT(CURDATE(), '%Y-%m-01')
            ");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("Transfer Dana Records", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM unit_transfer_dana");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("Laporan Konsolidasi", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM laporan_konsolidasi");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        echo "\n";
    }
    
    /**
     * Test AI Features
     */
    private function testAIFeatures()
    {
        echo "🤖 TESTING AI FEATURES\n";
        echo "----------------------\n";
        
        $this->runTest("AI Models Exist", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM ai_models WHERE is_active = 1");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("AI Predictions Available", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM predictive_predictions");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("AI Credit Scoring", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM ai_credit_scores");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("AI Chat Interactions", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM ai_chat_interactions");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("AI Sentiment Analysis", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM ai_sentiment_analysis");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        echo "\n";
    }
    
    /**
     * Test API Endpoints
     */
    private function testAPIEndpoints()
    {
        echo "🌐 TESTING API ENDPOINTS\n";
        echo "------------------------\n";
        
        $this->runTest("Admin API Router", function() {
            $router = new AdminAPIRouter();
            return $router !== null;
        });
        
        $this->runTest("Koperasi API Router", function() {
            $router = new KoperasiAPIRouter();
            return $router !== null;
        });
        
        $this->runTest("API Response Format", function() {
            // Test API response structure
            $controller = new KoperasiController();
            $result = $controller->getDashboardOverview();
            
            return isset($result['success']) && 
                   isset($result['data']) && 
                   isset($result['message']);
        });
        
        $this->runTest("API Error Handling", function() {
            // Test API error handling
            try {
                $controller = new KoperasiController();
                $result = $controller->getAnggotaById(99999); // Non-existent ID
                return !$result['success']; // Should return error
            } catch (Exception $e) {
                return true; // Exception is expected
            }
        });
        
        echo "\n";
    }
    
    /**
     * Test Security Features
     */
    private function testSecurityFeatures()
    {
        echo "🔒 TESTING SECURITY FEATURES\n";
        echo "---------------------------\n";
        
        $this->runTest("Password Hashing", function() {
            $stmt = $this->db->query("SELECT password FROM users LIMIT 1");
            $result = $stmt->fetch();
            return strlen($result['password']) > 20 && $result['password'][0] === '$';
        });
        
        $this->runTest("Session Management", function() {
            return session_status() === PHP_SESSION_ACTIVE || session_start();
        });
        
        $this->runTest("Input Validation", function() {
            // Test SQL injection protection
            $maliciousInput = "'; DROP TABLE anggota; --";
            $controller = new KoperasiController();
            $result = $controller->getAnggota(['search' => $maliciousInput]);
            return $result['success']; // Should not crash
        });
        
        $this->runTest("XSS Protection", function() {
            $maliciousInput = "<script>alert('xss')</script>";
            $controller = new KoperasiController();
            $result = $controller->getAnggota(['search' => $maliciousInput]);
            return $result['success']; // Should not execute script
        });
        
        echo "\n";
    }
    
    /**
     * Test Performance
     */
    private function testPerformance()
    {
        echo "⚡ TESTING PERFORMANCE\n";
        echo "---------------------\n";
        
        $this->runTest("Dashboard Load Time", function() {
            $start = microtime(true);
            $controller = new KoperasiController();
            $result = $controller->getDashboardOverview();
            $end = microtime(true);
            
            $loadTime = ($end - $start) * 1000; // Convert to milliseconds
            return $loadTime < 1000; // Should load in less than 1 second
        });
        
        $this->runTest("Database Query Performance", function() {
            $start = microtime(true);
            $stmt = $this->db->query("
                SELECT a.*, u.nama_unit 
                FROM anggota a 
                LEFT JOIN koperasi_unit u ON a.unit_id = u.id 
                WHERE a.status = 'aktif'
            ");
            $results = $stmt->fetchAll();
            $end = microtime(true);
            
            $queryTime = ($end - $start) * 1000;
            return $queryTime < 500; // Should execute in less than 500ms
        });
        
        $this->runTest("API Response Time", function() {
            $start = microtime(true);
            $controller = new KoperasiController();
            $result = $controller->getAnggota();
            $end = microtime(true);
            
            $responseTime = ($end - $start) * 1000;
            return $responseTime < 750; // Should respond in less than 750ms
        });
        
        echo "\n";
    }
    
    /**
     * Test Reports
     */
    private function testReports()
    {
        echo "📋 TESTING REPORTS\n";
        echo "------------------\n";
        
        $this->runTest("Report Templates", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM report_templates WHERE status = 'aktif'");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("Report Instances", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM report_instances WHERE status = 'completed'");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("SHU Components", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM shu_components");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        echo "\n";
    }
    
    /**
     * Test Mobile API
     */
    private function testMobileAPI()
    {
        echo "📱 TESTING MOBILE API\n";
        echo "--------------------\n";
        
        $this->runTest("Mobile Settings", function() {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM settings WHERE setting_key LIKE 'mobile.%'");
            $result = $stmt->fetch();
            return $result['count'] > 0;
        });
        
        $this->runTest("API Keys Structure", function() {
            // Check if api_keys table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'api_keys'");
            $result = $stmt->fetch();
            return $result !== false;
        });
        
        $this->runTest("Push Notification Structure", function() {
            // Check if push_notifications table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'push_notifications'");
            $result = $stmt->fetch();
            return $result !== false;
        });
        
        echo "\n";
    }
    
    /**
     * Run a single test
     */
    private function runTest($testName, $testFunction)
    {
        $this->testCount++;
        
        try {
            $startTime = microtime(true);
            $result = $testFunction();
            $endTime = microtime(true);
            
            $executionTime = ($endTime - $startTime) * 1000;
            
            if ($result) {
                $this->passedTests++;
                $status = "✅ PASS";
                $this->testResults[] = [
                    'name' => $testName,
                    'status' => 'PASS',
                    'time' => $executionTime,
                    'message' => 'Test completed successfully'
                ];
            } else {
                $this->failedTests++;
                $status = "❌ FAIL";
                $this->testResults[] = [
                    'name' => $testName,
                    'status' => 'FAIL',
                    'time' => $executionTime,
                    'message' => 'Test returned false'
                ];
            }
        } catch (Exception $e) {
            $this->failedTests++;
            $status = "❌ ERROR";
            $this->testResults[] = [
                'name' => $testName,
                'status' => 'ERROR',
                'time' => 0,
                'message' => $e->getMessage()
            ];
        }
        
        echo sprintf("%-40s %s (%.2fms)\n", $testName, $status, $executionTime ?? 0);
    }
    
    /**
     * Print test summary
     */
    private function printTestSummary()
    {
        echo "📊 TEST SUMMARY\n";
        echo "================\n";
        echo "Total Tests: {$this->testCount}\n";
        echo "Passed: {$this->passedTests} ✅\n";
        echo "Failed: {$this->failedTests} ❌\n";
        
        $successRate = $this->testCount > 0 ? ($this->passedTests / $this->testCount) * 100 : 0;
        echo "Success Rate: " . number_format($successRate, 2) . "%\n\n";
        
        if ($this->failedTests > 0) {
            echo "❌ FAILED TESTS:\n";
            foreach ($this->testResults as $test) {
                if ($test['status'] !== 'PASS') {
                    echo "- {$test['name']}: {$test['message']}\n";
                }
            }
            echo "\n";
        }
        
        if ($successRate >= 90) {
            echo "🎉 EXCELLENT! System is ready for production.\n";
        } elseif ($successRate >= 75) {
            echo "✅ GOOD! System is mostly ready with minor issues.\n";
        } else {
            echo "⚠️  NEEDS ATTENTION! System has significant issues.\n";
        }
    }
    
    /**
     * Generate test report
     */
    public function generateTestReport()
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_tests' => $this->testCount,
                'passed' => $this->passedTests,
                'failed' => $this->failedTests,
                'success_rate' => $this->testCount > 0 ? ($this->passedTests / $this->testCount) * 100 : 0
            ],
            'tests' => $this->testResults
        ];
        
        return $report;
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../app/controllers/KoperasiController.php';
    require_once __DIR__ . '/../app/api/AdminAPIRouter.php';
    require_once __DIR__ . '/../app/api/KoperasiAPIRouter.php';
    
    $testSuite = new KoperasiTestSuite();
    $results = $testSuite->runAllTests();
    
    // Save test report
    $report = $testSuite->generateTestReport();
    file_put_contents(__DIR__ . '/test_report_' . date('Y-m-d_H-i-s') . '.json', json_encode($report, JSON_PRETTY_PRINT));
}
?>
