<?php
/**
 * KSP Samosir - Comprehensive Security Audit
 * Security testing and vulnerability assessment
 */

class SecurityAuditSuite
{
    private $conn;
    private $securityResults = [];
    private $vulnerabilities = [];
    private $recommendations = [];
    
    public function __construct()
    {
        $this->conn = new mysqli('localhost', 'root', 'root', 'ksp_samosir');
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    /**
     * Run comprehensive security audit
     */
    public function runSecurityAudit()
    {
        echo "🔒 KSP SAMOSIR - COMPREHENSIVE SECURITY AUDIT\n";
        echo "============================================\n\n";
        
        // Authentication & Authorization
        $this->auditAuthentication();
        
        // Data Protection
        $this->auditDataProtection();
        
        // Input Validation
        $this->auditInputValidation();
        
        // SQL Injection Protection
        $this->auditSQLInjection();
        
        // XSS Protection
        $this->auditXSSProtection();
        
        // CSRF Protection
        $this->auditCSRFProtection();
        
        // Session Security
        $this->auditSessionSecurity();
        
        // File Upload Security
        $this->auditFileUploadSecurity();
        
        // API Security
        $this->auditAPISecurity();
        
        // Database Security
        $this->auditDatabaseSecurity();
        
        // Configuration Security
        $this->auditConfigurationSecurity();
        
        $this->generateSecurityReport();
        return $this->securityResults;
    }
    
    /**
     * Audit Authentication & Authorization
     */
    private function auditAuthentication()
    {
        echo "🔐 AUDITING AUTHENTICATION & AUTHORIZATION\n";
        echo "-----------------------------------------\n";
        
        // Check password hashing
        $this->runSecurityTest("Password Hashing", function() {
            $sql = "SELECT password FROM users LIMIT 1";
            $result = $this->conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $isHashed = strlen($row['password']) > 20 && $row['password'][0] === '$';
                if (!$isHashed) {
                    $this->vulnerabilities[] = [
                        'type' => 'Weak Password Storage',
                        'severity' => 'HIGH',
                        'description' => 'Passwords are not properly hashed',
                        'recommendation' => 'Use bcrypt or Argon2 for password hashing'
                    ];
                    return false;
                }
            }
            return true;
        });
        
        // Check default passwords
        $this->runSecurityTest("Default Passwords", function() {
            $defaultPasswords = ['admin', 'password', '123456', 'root'];
            $sql = "SELECT username FROM users WHERE password IN ('" . implode("','", $defaultPasswords) . "')";
            $result = $this->conn->query($sql);
            if ($result->num_rows > 0) {
                $this->vulnerabilities[] = [
                    'type' => 'Default Passwords',
                    'severity' => 'CRITICAL',
                    'description' => 'Users have default passwords',
                    'recommendation' => 'Force password change on first login'
                ];
                return false;
            }
            return true;
        });
        
        // Check role-based access control
        $this->runSecurityTest("RBAC Implementation", function() {
            $sql = "SELECT COUNT(*) as count FROM roles WHERE status = 'aktif'";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            if ($row['count'] < 2) {
                $this->vulnerabilities[] = [
                    'type' => 'Insufficient RBAC',
                    'severity' => 'MEDIUM',
                    'description' => 'Not enough roles for proper access control',
                    'recommendation' => 'Implement proper role-based access control'
                ];
                return false;
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit Data Protection
     */
    private function auditDataProtection()
    {
        echo "🛡️ AUDITING DATA PROTECTION\n";
        echo "---------------------------\n";
        
        // Check sensitive data encryption
        $this->runSecurityTest("Sensitive Data Encryption", function() {
            $sensitiveFields = ['nik', 'no_hp', 'email'];
            $issues = [];
            
            foreach ($sensitiveFields as $field) {
                $sql = "SELECT COUNT(*) as count FROM anggota WHERE $field IS NOT NULL LIMIT 1";
                $result = $this->conn->query($sql);
                $row = $result->fetch_assoc();
                if ($row['count'] > 0) {
                    $issues[] = $field;
                }
            }
            
            if (!empty($issues)) {
                $this->vulnerabilities[] = [
                    'type' => 'Unencrypted Sensitive Data',
                    'severity' => 'HIGH',
                    'description' => 'Sensitive data stored in plain text: ' . implode(', ', $issues),
                    'recommendation' => 'Encrypt sensitive personal data'
                ];
                return false;
            }
            return true;
        });
        
        // Check data backup security
        $this->runSecurityTest("Data Backup Security", function() {
            $backupDir = '/var/www/html/ksp_samosir/backups/';
            if (is_dir($backupDir)) {
                $files = glob($backupDir . '*.sql');
                foreach ($files as $file) {
                    if (is_readable($file)) {
                        $this->vulnerabilities[] = [
                            'type' => 'Insecure Backup Files',
                            'severity' => 'HIGH',
                            'description' => 'Backup files are readable by web server',
                            'recommendation' => 'Move backups outside web root and encrypt them'
                        ];
                        return false;
                    }
                }
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit Input Validation
     */
    private function auditInputValidation()
    {
        echo "✅ AUDITING INPUT VALIDATION\n";
        echo "----------------------------\n";
        
        // Check email validation
        $this->runSecurityTest("Email Format Validation", function() {
            $sql = "SELECT COUNT(*) as count FROM anggota WHERE email NOT REGEXP '^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,4}$' AND email IS NOT NULL";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            if ($row['count'] > 0) {
                $this->vulnerabilities[] = [
                    'type' => 'Invalid Email Format',
                    'severity' => 'LOW',
                    'description' => 'Invalid email formats in database',
                    'recommendation' => 'Implement proper email validation'
                ];
                return false;
            }
            return true;
        });
        
        // Check phone number validation
        $this->runSecurityTest("Phone Number Validation", function() {
            $sql = "SELECT COUNT(*) as count FROM anggota WHERE no_hp NOT REGEXP '^[0-9]+$' AND no_hp IS NOT NULL";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            if ($row['count'] > 0) {
                $this->vulnerabilities[] = [
                    'type' => 'Invalid Phone Format',
                    'severity' => 'LOW',
                    'description' => 'Invalid phone number formats in database',
                    'recommendation' => 'Implement proper phone number validation'
                ];
                return false;
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit SQL Injection Protection
     */
    private function auditSQLInjection()
    {
        echo "💉 AUDITING SQL INJECTION PROTECTION\n";
        echo "-----------------------------------\n";
        
        // Test SQL injection attempts
        $this->runSecurityTest("SQL Injection Protection", function() {
            $maliciousInputs = [
                "'; DROP TABLE anggota; --",
                "' OR '1'='1",
                "'; DELETE FROM users; --",
                "' UNION SELECT * FROM users --"
            ];
            
            foreach ($maliciousInputs as $input) {
                $sql = "SELECT COUNT(*) as count FROM anggota WHERE nama_lengkap = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("s", $input);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                // Check if table still exists
                $tableExists = $this->conn->query("SHOW TABLES LIKE 'anggota'")->num_rows > 0;
                if (!$tableExists) {
                    $this->vulnerabilities[] = [
                        'type' => 'SQL Injection Vulnerability',
                        'severity' => 'CRITICAL',
                        'description' => 'SQL injection successful - tables dropped',
                        'recommendation' => 'Use prepared statements for all queries'
                    ];
                    return false;
                }
            }
            return true;
        });
        
        // Check for dynamic SQL usage
        $this->runSecurityTest("Dynamic SQL Usage", function() {
            $phpFiles = glob('/var/www/html/ksp_samosir/**/*.php');
            $issues = [];
            
            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, '$sql .=') !== false && strpos($content, '$_') !== false) {
                    $issues[] = str_replace('/var/www/html/ksp_samosir/', '', $file);
                }
            }
            
            if (!empty($issues)) {
                $this->vulnerabilities[] = [
                    'type' => 'Potentially Unsafe Dynamic SQL',
                    'severity' => 'MEDIUM',
                    'description' => 'Dynamic SQL construction found in: ' . implode(', ', $issues),
                    'recommendation' => 'Review and secure dynamic SQL usage'
                ];
                return false;
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit XSS Protection
     */
    private function auditXSSProtection()
    {
        echo "🎯 AUDITING XSS PROTECTION\n";
        echo "--------------------------\n";
        
        // Test XSS protection
        $this->runSecurityTest("XSS Protection", function() {
            $xssPayloads = [
                '<script>alert("xss")</script>',
                'javascript:alert("xss")',
                '<img src="x" onerror="alert(1)">',
                '"><script>alert("xss")</script>'
            ];
            
            foreach ($xssPayloads as $payload) {
                $sql = "SELECT COUNT(*) as count FROM anggota WHERE nama_lengkap LIKE ?";
                $stmt = $this->conn->prepare($sql);
                $searchTerm = '%' . $payload . '%';
                $stmt->bind_param("s", $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] > 0) {
                    $this->vulnerabilities[] = [
                        'type' => 'Stored XSS Vulnerability',
                        'severity' => 'HIGH',
                        'description' => 'XSS payload found in database',
                        'recommendation' => 'Implement input sanitization and output encoding'
                    ];
                    return false;
                }
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit CSRF Protection
     */
    private function auditCSRFProtection()
    {
        echo "🔑 AUDITING CSRF PROTECTION\n";
        echo "----------------------------\n";
        
        $this->runSecurityTest("CSRF Token Implementation", function() {
            $phpFiles = glob('/var/www/html/ksp_samosir/**/*.php');
            $hasCSRF = false;
            
            foreach ($phpFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'csrf') !== false || strpos($content, 'CSRF') !== false) {
                    $hasCSRF = true;
                    break;
                }
            }
            
            if (!$hasCSRF) {
                $this->vulnerabilities[] = [
                    'type' => 'Missing CSRF Protection',
                    'severity' => 'MEDIUM',
                    'description' => 'No CSRF protection implementation found',
                    'recommendation' => 'Implement CSRF tokens for all state-changing operations'
                ];
                return false;
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit Session Security
     */
    private function auditSessionSecurity()
    {
        echo "🔒 AUDITING SESSION SECURITY\n";
        echo "---------------------------\n";
        
        $this->runSecurityTest("Session Configuration", function() {
            $sessionIssues = [];
            
            if (ini_get('session.cookie_httponly') != 1) {
                $sessionIssues[] = 'HTTPOnly cookies not enabled';
            }
            
            if (ini_get('session.cookie_secure') != 1 && isset($_SERVER['HTTPS'])) {
                $sessionIssues[] = 'Secure cookies not enabled';
            }
            
            if (ini_get('session.use_only_cookies') != 1) {
                $sessionIssues[] = 'URL session IDs not disabled';
            }
            
            if (!empty($sessionIssues)) {
                $this->vulnerabilities[] = [
                    'type' => 'Insecure Session Configuration',
                    'severity' => 'MEDIUM',
                    'description' => implode(', ', $sessionIssues),
                    'recommendation' => 'Configure secure session settings'
                ];
                return false;
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit File Upload Security
     */
    private function auditFileUploadSecurity()
    {
        echo "📁 AUDITING FILE UPLOAD SECURITY\n";
        echo "-------------------------------\n";
        
        $this->runSecurityTest("File Upload Validation", function() {
            $uploadDir = '/var/www/html/ksp_samosir/uploads/';
            if (is_dir($uploadDir)) {
                $files = glob($uploadDir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'exe', 'bat', 'sh'];
                        
                        if (in_array($extension, $dangerousExtensions)) {
                            $this->vulnerabilities[] = [
                                'type' => 'Dangerous File Upload',
                                'severity' => 'HIGH',
                                'description' => "Dangerous file type uploaded: $extension",
                                'recommendation' => 'Implement strict file type validation'
                            ];
                            return false;
                        }
                    }
                }
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit API Security
     */
    private function auditAPISecurity()
    {
        echo "🌐 AUDITING API SECURITY\n";
        echo "------------------------\n";
        
        $this->runSecurityTest("API Authentication", function() {
            $apiFiles = glob('/var/www/html/ksp_samosir/api/**/*.php');
            $hasAuth = false;
            
            foreach ($apiFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'session_start') !== false || strpos($content, '$_SESSION') !== false) {
                    $hasAuth = true;
                    break;
                }
            }
            
            if (!$hasAuth) {
                $this->vulnerabilities[] = [
                    'type' => 'Unprotected API Endpoints',
                    'severity' => 'HIGH',
                    'description' => 'API endpoints may not require authentication',
                    'recommendation' => 'Implement proper API authentication'
                ];
                return false;
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit Database Security
     */
    private function auditDatabaseSecurity()
    {
        echo "🗄️ AUDITING DATABASE SECURITY\n";
        echo "-----------------------------\n";
        
        $this->runSecurityTest("Database User Privileges", function() {
            $sql = "SELECT SELECT_priv, INSERT_priv, UPDATE_priv, DELETE_priv, FILE_priv FROM mysql.user WHERE User = 'root'";
            $result = $this->conn->query($sql);
            $row = $result->fetch_assoc();
            
            if ($row['FILE_priv'] == 'Y') {
                $this->vulnerabilities[] = [
                    'type' => 'Excessive Database Privileges',
                    'severity' => 'HIGH',
                    'description' => 'Database user has FILE privilege',
                    'recommendation' => 'Remove unnecessary database privileges'
                ];
                return false;
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Audit Configuration Security
     */
    private function auditConfigurationSecurity()
    {
        echo "⚙️ AUDITING CONFIGURATION SECURITY\n";
        echo "----------------------------------\n";
        
        $this->runSecurityTest("Error Reporting", function() {
            if (ini_get('display_errors') == 1) {
                $this->vulnerabilities[] = [
                    'type' => 'Error Display Enabled',
                    'severity' => 'MEDIUM',
                    'description' => 'PHP errors are displayed to users',
                    'recommendation' => 'Disable error display in production'
                ];
                return false;
            }
            return true;
        });
        
        $this->runSecurityTest("Debug Information", function() {
            if (ini_get('expose_php') == 1) {
                $this->vulnerabilities[] = [
                    'type' => 'PHP Version Exposed',
                    'severity' => 'LOW',
                    'description' => 'PHP version is exposed in headers',
                    'recommendation' => 'Disable expose_php in php.ini'
                ];
                return false;
            }
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Run a security test
     */
    private function runSecurityTest($testName, $testFunction)
    {
        try {
            $result = $testFunction();
            $status = $result ? "✅ SECURE" : "❌ VULNERABLE";
            $this->securityResults[] = [
                'name' => $testName,
                'status' => $result ? 'SECURE' : 'VULNERABLE',
                'result' => $result
            ];
        } catch (Exception $e) {
            $status = "⚠️ ERROR";
            $this->securityResults[] = [
                'name' => $testName,
                'status' => 'ERROR',
                'result' => false,
                'error' => $e->getMessage()
            ];
        }
        
        echo sprintf("%-40s %s\n", $testName, $status);
    }
    
    /**
     * Generate security report
     */
    private function generateSecurityReport()
    {
        echo "📋 SECURITY AUDIT SUMMARY\n";
        echo "========================\n";
        
        $totalTests = count($this->securityResults);
        $secureTests = count(array_filter($this->securityResults, function($test) {
            return $test['status'] === 'SECURE';
        }));
        $vulnerableTests = count(array_filter($this->securityResults, function($test) {
            return $test['status'] === 'VULNERABLE';
        }));
        
        echo "Total Security Tests: $totalTests\n";
        echo "Secure: $secureTests ✅\n";
        echo "Vulnerable: $vulnerableTests ❌\n";
        
        $securityScore = $totalTests > 0 ? ($secureTests / $totalTests) * 100 : 0;
        echo "Security Score: " . number_format($securityScore, 2) . "%\n\n";
        
        if (!empty($this->vulnerabilities)) {
            echo "🚨 VULNERABILITIES FOUND:\n";
            echo "-------------------------\n";
            
            $criticalIssues = array_filter($this->vulnerabilities, function($v) {
                return $v['severity'] === 'CRITICAL';
            });
            $highIssues = array_filter($this->vulnerabilities, function($v) {
                return $v['severity'] === 'HIGH';
            });
            $mediumIssues = array_filter($this->vulnerabilities, function($v) {
                return $v['severity'] === 'MEDIUM';
            });
            $lowIssues = array_filter($this->vulnerabilities, function($v) {
                return $v['severity'] === 'LOW';
            });
            
            foreach (['CRITICAL' => $criticalIssues, 'HIGH' => $highIssues, 'MEDIUM' => $mediumIssues, 'LOW' => $lowIssues] as $severity => $issues) {
                if (!empty($issues)) {
                    echo "\n🔴 $severity ISSUES:\n";
                    foreach ($issues as $issue) {
                        echo "- {$issue['type']}: {$issue['description']}\n";
                        echo "  Recommendation: {$issue['recommendation']}\n";
                    }
                }
            }
        }
        
        if ($securityScore >= 90) {
            echo "\n🎉 EXCELLENT! System has strong security posture.\n";
        } elseif ($securityScore >= 75) {
            echo "\n✅ GOOD! System is mostly secure with some improvements needed.\n";
        } elseif ($securityScore >= 60) {
            echo "\n⚠️ FAIR! System needs significant security improvements.\n";
        } else {
            echo "\n❌ POOR! System has serious security vulnerabilities.\n";
        }
        
        // Generate recommendations
        $this->generateRecommendations();
    }
    
    /**
     * Generate security recommendations
     */
    private function generateRecommendations()
    {
        echo "\n📝 SECURITY RECOMMENDATIONS:\n";
        echo "============================\n";
        
        $recommendations = [
            'Implement proper password hashing with bcrypt or Argon2',
            'Enable HTTPS for all communications',
            'Implement CSRF tokens for all form submissions',
            'Add input validation and sanitization for all user inputs',
            'Use prepared statements for all database queries',
            'Enable security headers (HSTS, CSP, X-Frame-Options)',
            'Implement rate limiting for API endpoints',
            'Regular security audits and penetration testing',
            'Keep all software and dependencies updated',
            'Implement proper logging and monitoring'
        ];
        
        foreach ($recommendations as $i => $recommendation) {
            echo ($i + 1) . ". $recommendation\n";
        }
    }
    
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Run security audit
try {
    $securityAudit = new SecurityAuditSuite();
    $results = $securityAudit->runSecurityAudit();
    
    // Save security report
    $report = [
        'timestamp' => date('Y-m-d H:i:s'),
        'vulnerabilities' => $securityAudit->vulnerabilities,
        'recommendations' => $securityAudit->recommendations,
        'results' => $securityAudit->securityResults
    ];
    
    $reportFile = 'security_audit_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
    echo "\n📄 Security report saved to: $reportFile\n";
    
} catch (Exception $e) {
    echo "❌ Security audit failed: " . $e->getMessage() . "\n";
}
?>
