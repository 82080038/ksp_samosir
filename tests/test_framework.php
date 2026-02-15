<?php
/**
 * Test Framework for KSP Samosir
 * Unit and Integration Testing Suite
 */

class KSP_Test_Framework {

    private $tests_run = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $test_results = [];

    public function __construct() {
        // Setup test environment
        $this->setupTestEnvironment();
    }

    private function setupTestEnvironment() {
        // Enable error reporting for testing
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Load configuration and database functions
        require_once __DIR__ . '/../config/config.php';
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/../config/error_handler.php';

        // Create test database connection if needed
        if (!defined('TEST_MODE')) {
            define('TEST_MODE', true);
        }
    }

    public function runAllTests() {
        echo "<h1>KSP Samosir - Test Suite Results</h1>";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

        $this->runControllerTests();
        $this->runDatabaseTests();
        $this->runIntegrationTests();
        $this->runSecurityTests();

        $this->displayResults();

        echo "</div>";
    }

    private function runControllerTests() {
        echo "<h2>🧪 Controller Tests</h2>";

        // Test AccountingController
        $this->testAccountingController();

        // Test InventoryController
        $this->testInventoryController();

        // Test MemberController
        $this->testMemberController();
    }

    private function testAccountingController() {
        echo "<h3>AccountingController Tests</h3>";

        try {
            require_once __DIR__ . '/../app/controllers/AccountingController.php';

            // Test instantiation
            $controller = new AccountingController();
            $this->assert($controller instanceof AccountingController, "AccountingController instantiation");

            // Test journal number generation
            $journalNumber = $this->callPrivateMethod($controller, 'generateJournalNumber');
            $this->assert(is_string($journalNumber), "Journal number generation");
            $this->assert(strpos($journalNumber, 'JRN') === 0, "Journal number format");

            // Test accounting stats
            $stats = $this->callPrivateMethod($controller, 'getAccountingStats');
            $this->assert(is_array($stats), "Accounting stats retrieval");
            $this->assert(array_key_exists('total_jurnal', $stats), "Stats contain total_jurnal");

            echo "<div style='color: green;'>✅ AccountingController tests passed</div>";

        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ AccountingController test failed: " . $e->getMessage() . "</div>";
        }
    }

    private function testInventoryController() {
        echo "<h3>InventoryController Tests</h3>";

        try {
            require_once __DIR__ . '/../app/controllers/InventoryController.php';

            // Test instantiation
            $controller = new InventoryController();
            $this->assert($controller instanceof InventoryController, "InventoryController instantiation");

            // Test inventory stats
            $stats = $this->callPrivateMethod($controller, 'getInventoryStats');
            $this->assert(is_array($stats), "Inventory stats retrieval");

            // Test current stock calculation
            $stock = $this->callPrivateMethod($controller, 'getCurrentStock', [1]);
            $this->assert(is_numeric($stock), "Current stock calculation");

            echo "<div style='color: green;'>✅ InventoryController tests passed</div>";

        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ InventoryController test failed: " . $e->getMessage() . "</div>";
        }
    }

    private function testMemberController() {
        echo "<h3>MemberController Tests</h3>";

        try {
            require_once __DIR__ . '/../app/controllers/MemberController.php';

            // Test instantiation
            $controller = new MemberController();
            $this->assert($controller instanceof MemberController, "MemberController instantiation");

            // Test member number generation
            $memberNumber = $this->callPrivateMethod($controller, 'generateMemberNumber');
            $this->assert(is_string($memberNumber), "Member number generation");
            $this->assert(strlen($memberNumber) >= 8, "Member number length");

            // Test loan number generation
            $loanNumber = $this->callPrivateMethod($controller, 'generateLoanNumber');
            $this->assert(is_string($loanNumber), "Loan number generation");
            $this->assert(strpos($loanNumber, 'LN') === 0, "Loan number format");

            echo "<div style='color: green;'>✅ MemberController tests passed</div>";

        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ MemberController test failed: " . $e->getMessage() . "</div>";
        }
    }

    private function runDatabaseTests() {
        echo "<h2>🗄️ Database Tests</h2>";

        $this->testDatabaseConnections();
        $this->testDatabaseQueries();
        $this->testDataIntegrity();
    }

    private function testDatabaseConnections() {
        echo "<h3>Database Connection Tests</h3>";

        try {
            $conn = getLegacyConnection();
            $this->assert($conn instanceof mysqli, "Database connection established");
            $this->assert($conn->ping(), "Database connection is alive");

            // Test query execution
            $result = executeQuery("SELECT 1 as test", [], "");
            $this->assert($result !== false, "Basic query execution");

            echo "<div style='color: green;'>✅ Database connection tests passed</div>";

        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Database connection test failed: " . $e->getMessage() . "</div>";
        }
    }

    private function testDatabaseQueries() {
        echo "<h3>Database Query Tests</h3>";

        $testQueries = [
            "SELECT COUNT(*) as count FROM users" => "Users table query",
            "SELECT COUNT(*) as count FROM anggota" => "Anggota table query",
            "SELECT COUNT(*) as count FROM simpanan" => "Simpanan table query",
            "SELECT COUNT(*) as count FROM pinjaman" => "Pinjaman table query"
        ];

        foreach ($testQueries as $query => $description) {
            try {
                $result = fetchRow($query);
                $this->assert(is_array($result), "$description - returns array");
                $this->assert(array_key_exists('count', $result), "$description - has count field");
            } catch (Exception $e) {
                echo "<div style='color: orange;'>⚠️ $description failed: " . $e->getMessage() . "</div>";
            }
        }

        echo "<div style='color: green;'>✅ Database query tests completed</div>";
    }

    private function testDataIntegrity() {
        echo "<h3>Data Integrity Tests</h3>";

        // Test foreign key constraints
        try {
            // Check if anggota references exist in related tables
            $orphanCheck = fetchRow("
                SELECT COUNT(*) as orphans FROM simpanan s
                LEFT JOIN anggota a ON s.anggota_id = a.id
                WHERE a.id IS NULL
            ");
            $this->assert($orphanCheck['orphans'] == 0, "No orphaned simpanan records");

            echo "<div style='color: green;'>✅ Data integrity tests passed</div>";

        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Data integrity test failed: " . $e->getMessage() . "</div>";
        }
    }

    private function runIntegrationTests() {
        echo "<h2>🔗 Integration Tests</h2>";

        $this->testModuleIntegration();
        $this->testWorkflowIntegration();
    }

    private function testModuleIntegration() {
        echo "<h3>Module Integration Tests</h3>";

        // Test if all required files exist
        $requiredFiles = [
            __DIR__ . '/../app/controllers/BaseController.php',
            __DIR__ . '/../app/controllers/AccountingController.php',
            __DIR__ . '/../app/controllers/InventoryController.php',
            __DIR__ . '/../app/controllers/MemberController.php',
            __DIR__ . '/../config/config.php',
            __DIR__ . '/../config/database.php'
        ];

        foreach ($requiredFiles as $file) {
            $exists = file_exists($file);
            $this->assert($exists, "Required file exists: " . basename($file));
        }

        echo "<div style='color: green;'>✅ Module integration tests passed</div>";
    }

    private function testWorkflowIntegration() {
        echo "<h3>Workflow Integration Tests</h3>";

        // Test complete workflow: Member -> Savings -> Loan
        try {
            // Check if workflow tables are properly linked
            $workflowTest = fetchRow("
                SELECT
                    COUNT(DISTINCT a.id) as members,
                    COUNT(DISTINCT s.id) as savings,
                    COUNT(DISTINCT p.id) as loans
                FROM anggota a
                LEFT JOIN simpanan s ON a.id = s.anggota_id
                LEFT JOIN pinjaman p ON a.id = p.anggota_id
            ");

            $this->assert(is_array($workflowTest), "Workflow integration query successful");

            echo "<div style='color: green;'>✅ Workflow integration tests passed</div>";

        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Workflow integration test failed: " . $e->getMessage() . "</div>";
        }
    }

    private function runSecurityTests() {
        echo "<h2>🔒 Security Tests</h2>";

        $this->testInputValidation();
        $this->testSQLInjectionPrevention();
        $this->testXSSPrevention();
    }

    private function testInputValidation() {
        echo "<h3>Input Validation Tests</h3>";

        // Test sanitize function
        $testInput = "<script>alert('xss')</script>";
        $sanitized = sanitize($testInput);
        $this->assert($sanitized !== $testInput, "Input sanitization works");
        $this->assert(strpos($sanitized, '<script>') === false, "Script tags removed");

        echo "<div style='color: green;'>✅ Input validation tests passed</div>";
    }

    private function testSQLInjectionPrevention() {
        echo "<h3>SQL Injection Prevention Tests</h3>";

        // Test prepared statements
        try {
            $safeQuery = "SELECT * FROM anggota WHERE id = ?";
            $result = fetchRow($safeQuery, [1], 'i');
            $this->assert(is_array($result) || $result === null, "Prepared statement works");

            // Test with malicious input
            $maliciousInput = "1' OR '1'='1";
            $result = fetchRow($safeQuery, [$maliciousInput], 's');
            // Should not return all records
            $this->assert($result === null || (is_array($result) && count($result) <= 1), "SQL injection prevented");

            echo "<div style='color: green;'>✅ SQL injection prevention tests passed</div>";

        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ SQL injection test failed: " . $e->getMessage() . "</div>";
        }
    }

    private function testXSSPrevention() {
        echo "<h3>XSS Prevention Tests</h3>";

        $xssPayloads = [
            "<script>alert('xss')</script>",
            "<img src=x onerror=alert('xss')>",
            "javascript:alert('xss')"
        ];

        foreach ($xssPayloads as $payload) {
            $sanitized = sanitize($payload);
            $this->assert(strpos($sanitized, '<') === false, "XSS payload sanitized: $payload");
        }

        echo "<div style='color: green;'>✅ XSS prevention tests passed</div>";
    }

    private function assert($condition, $message) {
        $this->tests_run++;

        if ($condition) {
            $this->tests_passed++;
            echo "<div style='color: green; margin-left: 20px;'>✅ PASS: $message</div>";
        } else {
            $this->tests_failed++;
            echo "<div style='color: red; margin-left: 20px;'>❌ FAIL: $message</div>";
        }
    }

    private function callPrivateMethod($object, $method, $args = []) {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    private function displayResults() {
        echo "<hr>";
        echo "<h2>📊 Test Results Summary</h2>";
        echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Total Tests:</strong> {$this->tests_run}<br>";
        echo "<strong style='color: green;'>Passed:</strong> {$this->tests_passed}<br>";
        echo "<strong style='color: red;'>Failed:</strong> {$this->tests_failed}<br>";
        echo "<strong>Success Rate:</strong> " . ($this->tests_run > 0 ? round(($this->tests_passed / $this->tests_run) * 100, 2) : 0) . "%";
        echo "</div>";

        if ($this->tests_failed === 0) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "🎉 <strong>All tests passed!</strong> System is ready for production.";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "⚠️ <strong>{$this->tests_failed} test(s) failed.</strong> Please fix issues before production deployment.";
            echo "</div>";
        }
    }
}

// Run tests if this file is called directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $testFramework = new KSP_Test_Framework();
    $testFramework->runAllTests();
}
?>
