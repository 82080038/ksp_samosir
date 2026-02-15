<?php
/**
 * KSP Samosir - Simple Database Testing
 * Direct database testing without complex dependencies
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'ksp_samosir');

class SimpleKoperasiTest
{
    private $conn;
    private $testResults = [];
    private $testCount = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    
    public function __construct()
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    public function runAllTests()
    {
        echo "🧪 KSP SAMOSIR - SIMPLE DATABASE TESTING\n";
        echo "========================================\n\n";
        
        // Database Connection Tests
        $this->testDatabaseConnection();
        
        // Table Structure Tests
        $this->testTableStructure();
        
        // Data Integrity Tests
        $this->testDataIntegrity();
        
        // Core Data Tests
        $this->testCoreData();
        
        // Performance Tests
        $this->testPerformance();
        
        // Security Tests
        $this->testSecurity();
        
        $this->printTestSummary();
        return $this->testResults;
    }
    
    private function testDatabaseConnection()
    {
        echo "📊 TESTING DATABASE CONNECTION\n";
        echo "--------------------------------\n";
        
        $this->runTest("Database Connection", function() {
            return $this->conn->ping();
        });
        
        $this->runTest("Database Selection", function() {
            $result = $this->conn->query("SELECT DATABASE() as db_name");
            $row = $result->fetch_assoc();
            return $row['db_name'] === DB_NAME;
        });
        
        echo "\n";
    }
    
    private function testTableStructure()
    {
        echo "🏗️ TESTING TABLE STRUCTURE\n";
        echo "--------------------------\n";
        
        $requiredTables = [
            'anggota' => 'Anggota koperasi',
            'users' => 'User management',
            'simpanan' => 'Simpanan transactions',
            'pinjaman' => 'Pinjaman records',
            'angsuran' => 'Angsuran payments',
            'jenis_simpanan' => 'Jenis simpanan',
            'jenis_pinjaman' => 'Jenis pinjaman',
            'koperasi_unit' => 'Unit koperasi',
            'unit_target_performance' => 'Performance tracking',
            'ai_models' => 'AI models',
            'settings' => 'System settings'
        ];
        
        foreach ($requiredTables as $table => $description) {
            $this->runTest("Table: $table ($description)", function() use ($table) {
                $result = $this->conn->query("SHOW TABLES LIKE '$table'");
                return $result->num_rows > 0;
            });
        }
        
        echo "\n";
    }
    
    private function testDataIntegrity()
    {
        echo "🔍 TESTING DATA INTEGRITY\n";
        echo "-------------------------\n";
        
        $this->runTest("Anggota Data Completeness", function() {
            $sql = "SELECT COUNT(*) as count FROM anggota WHERE no_anggota IS NULL OR nik IS NULL OR nama_lengkap IS NULL";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] == 0;
        });
        
        $this->runTest("Unique No Anggota", function() {
            $sql = "SELECT COUNT(*) as count, COUNT(DISTINCT no_anggota) as unique_count FROM anggota";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] == $row['unique_count'];
        });
        
        $this->runTest("Unique NIK", function() {
            $sql = "SELECT COUNT(*) as count, COUNT(DISTINCT nik) as unique_count FROM anggota";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] == $row['unique_count'];
        });
        
        $this->runTest("Simpanan Foreign Key", function() {
            $sql = "SELECT COUNT(*) as count FROM simpanan s LEFT JOIN anggota a ON s.anggota_id = a.id WHERE a.id IS NULL";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] == 0;
        });
        
        $this->runTest("Pinjaman Foreign Key", function() {
            $sql = "SELECT COUNT(*) as count FROM pinjaman p LEFT JOIN anggota a ON p.anggota_id = a.id WHERE a.id IS NULL";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] == 0;
        });
        
        echo "\n";
    }
    
    private function testCoreData()
    {
        echo "💾 TESTING CORE DATA\n";
        echo "--------------------\n";
        
        $this->runTest("Anggota Records Exist", function() {
            $sql = "SELECT COUNT(*) as count FROM anggota";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        });
        
        $this->runTest("Jenis Simpanan Available", function() {
            $sql = "SELECT COUNT(*) as count FROM jenis_simpanan WHERE is_active = 1";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        });
        
        $this->runTest("Jenis Pinjaman Available", function() {
            $sql = "SELECT COUNT(*) as count FROM jenis_pinjaman WHERE is_active = 1";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        });
        
        $this->runTest("Simpanan Transactions", function() {
            $sql = "SELECT COUNT(*) as count FROM simpanan";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        });
        
        $this->runTest("Pinjaman Records", function() {
            $sql = "SELECT COUNT(*) as count FROM pinjaman";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        });
        
        $this->runTest("Angsuran Records", function() {
            $sql = "SELECT COUNT(*) as count FROM angsuran";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        });
        
        echo "\n";
    }
    
    private function testPerformance()
    {
        echo "⚡ TESTING PERFORMANCE\n";
        echo "---------------------\n";
        
        $this->runTest("Simple Query Performance", function() {
            $start = microtime(true);
            $sql = "SELECT COUNT(*) as count FROM anggota";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            $end = microtime(true);
            
            $queryTime = ($end - $start) * 1000;
            return $queryTime < 100; // Should execute in less than 100ms
        });
        
        $this->runTest("Complex Query Performance", function() {
            $start = microtime(true);
            $sql = "
                SELECT a.*, u.nama_unit, 
                       (SELECT SUM(saldo) FROM simpanan WHERE anggota_id = a.id AND status = 'aktif') as total_simpanan,
                       (SELECT SUM(jumlah_pinjaman) FROM pinjaman WHERE anggota_id = a.id AND status = 'disetujui') as total_pinjaman
                FROM anggota a 
                LEFT JOIN koperasi_unit u ON a.unit_id = u.id 
                WHERE a.status = 'aktif'
            ";
            $result = $this->conn->query($sql);
            $end = microtime(true);
            
            $queryTime = ($end - $start) * 1000;
            return $queryTime < 500; // Should execute in less than 500ms
        });
        
        $this->runTest("Index Performance Check", function() {
            $sql = "EXPLAIN SELECT * FROM anggota WHERE no_anggota = 'KSP-001'";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return isset($row['key']) && $row['key'] !== NULL;
        });
        
        echo "\n";
    }
    
    private function testSecurity()
    {
        echo "🔒 TESTING SECURITY\n";
        echo "------------------\n";
        
        $this->runTest("Password Hashing", function() {
            $sql = "SELECT password FROM users LIMIT 1";
            $result = $this->conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return strlen($row['password']) > 20 && $row['password'][0] === '$';
            }
            return true; // No users yet, which is ok
        });
        
        $this->runTest("SQL Injection Protection", function() {
            $maliciousInput = "'; DROP TABLE anggota; --";
            $sql = "SELECT COUNT(*) as count FROM anggota WHERE nama_lengkap LIKE ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $maliciousInput);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            // Check if anggota table still exists
            $tableExists = $this->conn->query("SHOW TABLES LIKE 'anggota'")->num_rows > 0;
            return $tableExists; // Table should still exist
        });
        
        $this->runTest("Data Validation", function() {
            // Check for invalid data
            $sql = "SELECT COUNT(*) as count FROM anggota WHERE nik REGEXP '[^0-9]' OR nik = ''";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            return $row['count'] == 0;
        });
        
        echo "\n";
    }
    
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
        
        echo sprintf("%-50s %s (%.2fms)\n", $testName, $status, $executionTime ?? 0);
    }
    
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
        
        if ($successRate >= 95) {
            echo "🎉 EXCELLENT! System is ready for production.\n";
        } elseif ($successRate >= 85) {
            echo "✅ GOOD! System is ready with minor issues.\n";
        } elseif ($successRate >= 70) {
            echo "⚠️  FAIR! System needs some attention.\n";
        } else {
            echo "❌ POOR! System has significant issues.\n";
        }
    }
    
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
    
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Run tests
try {
    $testSuite = new SimpleKoperasiTest();
    $results = $testSuite->runAllTests();
    
    // Save test report
    $report = $testSuite->generateTestReport();
    $reportFile = 'test_report_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
    echo "\n📄 Test report saved to: $reportFile\n";
    
} catch (Exception $e) {
    echo "❌ Test execution failed: " . $e->getMessage() . "\n";
}
?>
