# Developer Training: Normalized Database Usage

## KSP Samosir - Developer Guide for Normalized Database

### Introduction

This guide provides developers with the knowledge and best practices for working with the normalized database schema in the KSP Samosir cooperative management system.

### Learning Objectives

By the end of this training, developers will be able to:
- Understand the normalized database structure
- Write efficient queries using normalized tables
- Maintain data integrity in the normalized schema
- Handle common development scenarios
- Troubleshoot normalization-related issues

---

## Module 1: Understanding Normalization

### What is Database Normalization?

Database normalization is the process of organizing data in a database to:
- **Eliminate redundancy**: Avoid duplicate data storage
- **Ensure data integrity**: Maintain consistent data relationships
- **Improve performance**: Optimize query execution
- **Simplify maintenance**: Easier to modify and extend

### Normalization Forms

#### 1NF (First Normal Form)
- **Rule**: All columns must contain atomic (indivisible) values
- **Example**: Instead of storing "John,Doe" in one field, use separate first_name and last_name fields

#### 2NF (Second Normal Form)
- **Rule**: Remove partial dependencies (fields that depend only on part of a composite primary key)
- **Example**: In a table with composite key (order_id, product_id), price should not depend only on product_id

#### 3NF (Third Normal Form)
- **Rule**: Remove transitive dependencies (fields that depend on non-key fields)
- **Example**: If department depends on employee, and manager depends on department, then manager should not be stored with employee

---

## Module 2: Schema Overview

### Reference Tables

#### Status Types Table
```sql
-- Centralized status management
SELECT * FROM status_types WHERE category = 'member';
-- Returns: active, inactive, suspended
```

#### Categories Table
```sql
-- Hierarchical category system
SELECT c.category_name, p.category_name as parent
FROM categories c
LEFT JOIN categories p ON c.parent_category_id = p.id
WHERE c.category_type = 'product';
```

#### Addresses & Contacts
```sql
-- Normalized address storage
SELECT * FROM addresses WHERE address_type = 'home';

-- Normalized contact storage
SELECT * FROM contacts WHERE contact_type = 'email';

-- Entity relationships
SELECT ea.*, addr.street_address
FROM entity_addresses ea
JOIN addresses addr ON ea.address_id = addr.id
WHERE ea.entity_type = 'member' AND ea.entity_id = ?;
```

---

## Module 3: Query Patterns

### Basic Query Patterns

#### 1. Member Data with Address & Contact
```php
// RECOMMENDED: Use normalized joins
$member = fetchRow("
    SELECT
        a.id, a.no_anggota, a.nama_lengkap, a.nik, a.status,
        COALESCE(addr.street_address, a.alamat) as alamat,
        COALESCE(c_phone.contact_value, a.no_hp) as no_hp,
        COALESCE(c_email.contact_value, a.email) as email,
        st.status_name as status_text
    FROM anggota a
    LEFT JOIN addresses addr ON a.address_id = addr.id AND addr.is_primary = 1
    LEFT JOIN contacts c_phone ON a.primary_contact_id = c_phone.id AND c_phone.contact_type = 'phone'
    LEFT JOIN contacts c_email ON c_email.id = (SELECT id FROM contacts WHERE contact_type = 'email' AND id IN (SELECT contact_id FROM entity_contacts WHERE entity_type = 'member' AND entity_id = a.id) LIMIT 1)
    LEFT JOIN status_types st ON st.category = 'member' AND st.status_code = a.status
    WHERE a.id = ?
", [$member_id]);

// AVOID: Direct field access (legacy support only)
$member = fetchRow("SELECT * FROM anggota WHERE id = ?", [$member_id]);
```

#### 2. Employee Data with Department & Position
```php
$employee = fetchRow("
    SELECT
        e.*, d.department_name, p.position_name,
        COALESCE(addr.street_address, e.address) as address,
        COALESCE(c.contact_value, e.phone) as phone
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN positions p ON e.position_id = p.id
    LEFT JOIN addresses addr ON e.address_id = addr.id
    LEFT JOIN contacts c ON e.primary_contact_id = c.id AND c.contact_type = 'phone'
    WHERE e.id = ?
", [$employee_id]);
```

#### 3. Supplier Data with Category & Status
```php
$supplier = fetchRow("
    SELECT
        s.*, c.category_name, st.status_name,
        COALESCE(addr.street_address, s.alamat) as address,
        COALESCE(c_phone.contact_value, s.no_telepon) as phone
    FROM suppliers s
    LEFT JOIN categories c ON s.supplier_category_id = c.id
    LEFT JOIN status_types st ON st.category = 'supplier' AND st.status_code = s.status
    LEFT JOIN addresses addr ON s.address_id = addr.id AND addr.is_primary = 1
    LEFT JOIN contacts c_phone ON s.primary_contact_id = c_phone.id AND c_phone.contact_type = 'phone'
    WHERE s.id = ?
", [$supplier_id]);
```

### Advanced Query Patterns

#### Inserting New Entity with Address & Contact
```php
function createMemberWithAddress($data) {
    runInTransaction(function($conn) use ($data) {
        // 1. Insert address
        $stmt = $conn->prepare("INSERT INTO addresses (address_type, street_address, city, postal_code) VALUES ('home', ?, ?, ?)");
        $stmt->bind_param('sss', $data['street_address'], $data['city'], $data['postal_code']);
        $stmt->execute();
        $address_id = $stmt->insert_id;

        // 2. Insert contact (phone)
        $stmt = $conn->prepare("INSERT INTO contacts (contact_type, contact_value) VALUES ('phone', ?)");
        $stmt->bind_param('s', $data['phone']);
        $stmt->execute();
        $phone_id = $stmt->insert_id;

        // 3. Insert contact (email)
        $stmt = $conn->prepare("INSERT INTO contacts (contact_type, contact_value) VALUES ('email', ?)");
        $stmt->bind_param('s', $data['email']);
        $stmt->execute();
        $email_id = $stmt->insert_id;

        // 4. Insert member
        $stmt = $conn->prepare("INSERT INTO anggota (no_anggota, nama_lengkap, nik, address_id, primary_contact_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', $data['no_anggota'], $data['nama_lengkap'], $data['nik'], $address_id, $phone_id);
        $stmt->execute();
        $member_id = $stmt->insert_id;

        // 5. Link additional contacts
        $stmt = $conn->prepare("INSERT INTO entity_contacts (entity_type, entity_id, contact_id) VALUES ('member', ?, ?)");
        $stmt->bind_param('ii', $member_id, $email_id);
        $stmt->execute();

        // 6. Link address
        $stmt = $conn->prepare("INSERT INTO entity_addresses (entity_type, entity_id, address_id) VALUES ('member', ?, ?)");
        $stmt->bind_param('ii', $member_id, $address_id);
        $stmt->execute();
    });
}
```

#### Updating Entity Data
```php
function updateMemberAddress($member_id, $address_data) {
    runInTransaction(function($conn) use ($member_id, $address_data) {
        // Check if member has existing address
        $existing = fetchRow("SELECT address_id FROM anggota WHERE id = ?", [$member_id], 'i');

        if ($existing && $existing['address_id']) {
            // Update existing address
            $stmt = $conn->prepare("UPDATE addresses SET street_address = ?, city = ?, postal_code = ? WHERE id = ?");
            $stmt->bind_param('sssi', $address_data['street'], $address_data['city'], $address_data['postal'], $existing['address_id']);
        } else {
            // Insert new address and link to member
            $stmt = $conn->prepare("INSERT INTO addresses (address_type, street_address, city, postal_code) VALUES ('home', ?, ?, ?)");
            $stmt->bind_param('sss', $address_data['street'], $address_data['city'], $address_data['postal']);
            $stmt->execute();
            $address_id = $stmt->insert_id;

            // Update member with address reference
            $stmt = $conn->prepare("UPDATE anggota SET address_id = ? WHERE id = ?");
            $stmt->bind_param('ii', $address_id, $member_id);
            $stmt->execute();

            // Link in relationship table
            $stmt = $conn->prepare("INSERT INTO entity_addresses (entity_type, entity_id, address_id) VALUES ('member', ?, ?) ON DUPLICATE KEY UPDATE address_id = VALUES(address_id)");
            $stmt->bind_param('ii', $member_id, $address_id);
        }
    });
}
```

---

## Module 4: Common Development Scenarios

### Scenario 1: Creating New Member
```php
function createNewMember($memberData) {
    // 1. Validate input data
    $errors = validateMemberData($memberData);
    if (!empty($errors)) return $errors;

    // 2. Insert address
    $address_id = insertAddress($memberData['address']);

    // 3. Insert contacts
    $phone_id = insertContact('phone', $memberData['phone']);
    $email_id = insertContact('email', $memberData['email']);

    // 4. Insert member record
    $member_id = insertMember($memberData, $address_id, $phone_id);

    // 5. Link additional contacts
    linkEntityContact('member', $member_id, $email_id);

    return $member_id;
}
```

### Scenario 2: Updating Employee Information
```php
function updateEmployee($employee_id, $updateData) {
    // 1. Get current employee data
    $current = fetchRow("SELECT * FROM employees WHERE id = ?", [$employee_id]);

    // 2. Update basic employee data
    updateEmployeeBasicInfo($employee_id, $updateData);

    // 3. Update address if provided
    if (isset($updateData['address'])) {
        updateEmployeeAddress($employee_id, $updateData['address']);
    }

    // 4. Update contacts if provided
    if (isset($updateData['contacts'])) {
        updateEmployeeContacts($employee_id, $updateData['contacts']);
    }

    // 5. Update department/position if changed
    if (isset($updateData['department_id'])) {
        updateEmployeeDepartment($employee_id, $updateData['department_id']);
    }
}
```

### Scenario 3: Reporting with Normalized Data
```php
function generateMemberReport() {
    // Use normalized joins for comprehensive data
    $members = fetchAll("
        SELECT
            a.id, a.no_anggota, a.nama_lengkap, a.tanggal_gabung,
            st.status_name as status,
            d.department_name,
            addr.city as city,
            c_phone.contact_value as phone,
            c_email.contact_value as email
        FROM anggota a
        LEFT JOIN status_types st ON st.category = 'member' AND st.status_code = a.status
        LEFT JOIN employees e ON e.id = a.id  -- If member is also employee
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN addresses addr ON a.address_id = addr.id
        LEFT JOIN contacts c_phone ON a.primary_contact_id = c_phone.id AND c_phone.contact_type = 'phone'
        LEFT JOIN contacts c_email ON c_email.id = (SELECT id FROM contacts WHERE contact_type = 'email' AND id IN (SELECT contact_id FROM entity_contacts WHERE entity_type = 'member' AND entity_id = a.id) LIMIT 1)
        WHERE a.status = 'active'
        ORDER BY a.nama_lengkap
    ");

    return $members;
}
```

---

## Module 5: Best Practices

### Query Optimization

1. **Use Proper Indexes**
```sql
-- Ensure foreign keys are indexed
CREATE INDEX idx_member_address ON anggota(address_id);
CREATE INDEX idx_member_contact ON anggota(primary_contact_id);

-- Composite indexes for common queries
CREATE INDEX idx_entity_address_type_id ON entity_addresses(entity_type, entity_id);
```

2. **Avoid N+1 Query Problems**
```php
// BAD: N+1 queries
$members = fetchAll("SELECT id, nama_lengkap FROM anggota");
foreach ($members as &$member) {
    $member['address'] = fetchRow("SELECT street_address FROM addresses WHERE id = ?", [$member['address_id']]);
}

// GOOD: Single query with JOIN
$members = fetchAll("
    SELECT a.id, a.nama_lengkap, addr.street_address
    FROM anggota a
    LEFT JOIN addresses addr ON a.address_id = addr.id
");
```

3. **Use Prepared Statements**
```php
// Always use prepared statements for security
$stmt = $conn->prepare("SELECT * FROM anggota WHERE id = ?");
$stmt->bind_param('i', $member_id);
$stmt->execute();
$result = $stmt->get_result();
```

### Data Integrity

1. **Handle Foreign Key Constraints**
```php
try {
    // Attempt database operation
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1451) {
        // Foreign key constraint violation
        flashMessage('error', 'Cannot delete: Record is referenced by other data');
    } else {
        // Other database error
        flashMessage('error', 'Database error: ' . $e->getMessage());
    }
}
```

2. **Validate Reference Data**
```php
function validateStatus($status, $category) {
    $valid_status = fetchRow("SELECT id FROM status_types WHERE category = ? AND status_code = ? AND is_active = 1", [$category, $status]);
    return $valid_status !== null;
}

function validateCategory($category_id, $category_type) {
    $valid_category = fetchRow("SELECT id FROM categories WHERE id = ? AND category_type = ? AND is_active = 1", [$category_id, $category_type]);
    return $valid_category !== null;
}
```

### Error Handling

1. **Graceful Degradation**
```php
function getMemberAddress($member_id) {
    try {
        $address = fetchRow("
            SELECT addr.street_address, addr.city, addr.postal_code
            FROM addresses addr
            JOIN anggota a ON a.address_id = addr.id
            WHERE a.id = ? AND addr.is_primary = 1
        ", [$member_id]);

        return $address ?: ['street_address' => 'Address not available'];
    } catch (Exception $e) {
        error_log("Error fetching member address: " . $e->getMessage());
        return ['street_address' => 'Address temporarily unavailable'];
    }
}
```

2. **Transaction Management**
```php
function transferMember($member_id, $new_department_id) {
    runInTransaction(function($conn) use ($member_id, $new_department_id) {
        // Verify department exists
        $dept = fetchRow("SELECT id FROM departments WHERE id = ? AND is_active = 1", [$new_department_id]);
        if (!$dept) throw new Exception("Invalid department");

        // Update employee record
        $stmt = $conn->prepare("UPDATE employees SET department_id = ? WHERE id = ?");
        $stmt->bind_param('ii', $new_department_id, $member_id);
        $stmt->execute();

        // Log the transfer
        $stmt = $conn->prepare("INSERT INTO transfer_logs (employee_id, old_department_id, new_department_id, transfer_date) VALUES (?, (SELECT department_id FROM employees WHERE id = ?), ?, CURDATE())");
        $stmt->bind_param('iii', $member_id, $member_id, $new_department_id);
        $stmt->execute();
    });
}
```

---

## Module 6: Testing & Debugging

### Unit Testing for Database Operations
```php
class DatabaseTest {
    public function testMemberCreation() {
        // Test member creation with address and contacts
        $testData = [
            'no_anggota' => 'TEST001',
            'nama_lengkap' => 'Test Member',
            'nik' => '1234567890123456',
            'address' => ['street' => 'Jl. Test 123', 'city' => 'Test City'],
            'phone' => '081234567890',
            'email' => 'test@example.com'
        ];

        $member_id = createNewMember($testData);

        // Verify member was created
        $member = fetchRow("SELECT * FROM anggota WHERE id = ?", [$member_id]);
        assert($member['nama_lengkap'] === 'Test Member');

        // Verify address was linked
        $address_link = fetchRow("SELECT * FROM entity_addresses WHERE entity_type = 'member' AND entity_id = ?", [$member_id]);
        assert($address_link !== null);

        // Verify contacts were linked
        $contact_links = fetchAll("SELECT * FROM entity_contacts WHERE entity_type = 'member' AND entity_id = ?", [$member_id]);
        assert(count($contact_links) >= 2); // Phone and email
    }
}
```

### Performance Testing
```php
function benchmarkQuery($query, $params = [], $iterations = 100) {
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        fetchAll($query, $params);
        $times[] = microtime(true) - $start;
    }

    $avg_time = array_sum($times) / count($times);
    $max_time = max($times);
    $min_time = min($times);

    return [
        'avg_time' => $avg_time,
        'max_time' => $max_time,
        'min_time' => $min_time,
        'iterations' => $iterations
    ];
}

// Test normalized query performance
$result = benchmarkQuery("
    SELECT a.nama_lengkap, addr.city, c.contact_value
    FROM anggota a
    LEFT JOIN addresses addr ON a.address_id = addr.id
    LEFT JOIN contacts c ON a.primary_contact_id = c.id
    LIMIT 50
");

echo "Average query time: " . ($result['avg_time'] * 1000) . "ms\n";
```

---

## Module 7: Troubleshooting

### Common Issues & Solutions

#### Issue 1: Foreign Key Constraint Violations
```php
// Error: Cannot delete department because employees reference it
// Solution: Handle cascading deletes or reassign employees first

function deleteDepartment($dept_id) {
    // Check for employees in department
    $employees = fetchAll("SELECT id, full_name FROM employees WHERE department_id = ?", [$dept_id]);

    if (!empty($employees)) {
        // Option 1: Prevent deletion
        throw new Exception("Cannot delete department: " . count($employees) . " employees assigned");

        // Option 2: Reassign employees to default department
        $default_dept = fetchRow("SELECT id FROM departments WHERE department_code = 'GENERAL'");
        if ($default_dept) {
            $stmt = $conn->prepare("UPDATE employees SET department_id = ? WHERE department_id = ?");
            $stmt->bind_param('ii', $default_dept['id'], $dept_id);
            $stmt->execute();
        }
    }

    // Now safe to delete
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param('i', $dept_id);
    $stmt->execute();
}
```

#### Issue 2: Slow Queries Due to Complex Joins
```php
// Problem: Complex joins causing slow performance
$slow_query = "
    SELECT a.*, addr.*, c.*, st.*, cat.*
    FROM anggota a
    LEFT JOIN addresses addr ON a.address_id = addr.id
    LEFT JOIN contacts c ON a.primary_contact_id = c.id
    LEFT JOIN status_types st ON st.category = 'member' AND st.status_code = a.status
    LEFT JOIN categories cat ON cat.id = a.category_id
";

// Solution 1: Select only needed columns
$optimized_query = "
    SELECT a.id, a.nama_lengkap, addr.city, c.contact_value, st.status_name
    FROM anggota a
    LEFT JOIN addresses addr ON a.address_id = addr.id
    LEFT JOIN contacts c ON a.primary_contact_id = c.id
    LEFT JOIN status_types st ON st.category = 'member' AND st.status_code = a.status
";

// Solution 2: Add strategic indexes
CREATE INDEX idx_member_status ON anggota(status);
CREATE INDEX idx_address_city ON addresses(city);
CREATE INDEX idx_contact_value ON contacts(contact_value(50));

// Solution 3: Use query result caching
$cache_key = 'member_data_' . $member_id;
$member_data = getCache($cache_key);
if (!$member_data) {
    $member_data = fetchRow($optimized_query, [$member_id]);
    setCache($cache_key, $member_data, 3600); // Cache for 1 hour
}
```

#### Issue 3: Data Inconsistency
```php
// Problem: Status values are inconsistent
// Solution: Always use reference table values

function updateMemberStatus($member_id, $new_status) {
    // Validate status exists in reference table
    $valid_status = fetchRow("SELECT id FROM status_types WHERE category = 'member' AND status_code = ? AND is_active = 1", [$new_status]);

    if (!$valid_status) {
        throw new Exception("Invalid status: $new_status");
    }

    // Update using validated status
    $stmt = $conn->prepare("UPDATE anggota SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $new_status, $member_id);
    $stmt->execute();
}
```

---

## Module 8: Advanced Topics

### Working with Hierarchical Data
```php
// Get category hierarchy
function getCategoryHierarchy($parent_id = null, $level = 0) {
    $categories = fetchAll("
        SELECT id, category_name, category_code,
               (SELECT COUNT(*) FROM categories WHERE parent_category_id = c.id) as has_children
        FROM categories c
        WHERE (? IS NULL AND parent_category_id IS NULL) OR parent_category_id = ?
        ORDER BY category_name
    ", [$parent_id, $parent_id]);

    foreach ($categories as &$category) {
        $category['level'] = $level;
        $category['indent'] = str_repeat('—', $level);

        if ($category['has_children'] > 0) {
            $category['children'] = getCategoryHierarchy($category['id'], $level + 1);
        }
    }

    return $categories;
}

// Usage
$hierarchy = getCategoryHierarchy();
// Returns nested category structure
```

### Audit Trail Implementation
```php
function logDataChange($table, $record_id, $action, $old_data = null, $new_data = null) {
    $user_id = $_SESSION['user']['id'] ?? null;

    runInTransaction(function($conn) use ($table, $record_id, $action, $old_data, $new_data, $user_id) {
        $stmt = $conn->prepare("
            INSERT INTO audit_trail (table_name, record_id, action, old_data, new_data, user_id, changed_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $old_json = $old_data ? json_encode($old_data) : null;
        $new_json = $new_data ? json_encode($new_data) : null;

        $stmt->bind_param('sissss', $table, $record_id, $action, $old_json, $new_json, $user_id);
        $stmt->execute();
    });
}

// Usage
$old_member = fetchRow("SELECT * FROM anggota WHERE id = ?", [$member_id]);
updateMember($member_id, $update_data);
$new_member = fetchRow("SELECT * FROM anggota WHERE id = ?", [$member_id]);
logDataChange('anggota', $member_id, 'UPDATE', $old_member, $new_member);
```

### Database Migration Scripts
```php
function runDatabaseMigration($version) {
    $migrations = [
        '1.0' => [
            "ALTER TABLE anggota ADD COLUMN address_id INT",
            "ALTER TABLE anggota ADD FOREIGN KEY (address_id) REFERENCES addresses(id)",
            "CREATE INDEX idx_member_address ON anggota(address_id)"
        ],
        '1.1' => [
            "ALTER TABLE employees ADD COLUMN department_id INT",
            "ALTER TABLE employees ADD FOREIGN KEY (department_id) REFERENCES departments(id)",
            "INSERT INTO departments (department_code, department_name) VALUES ('HR', 'Human Resources')"
        ]
    ];

    if (isset($migrations[$version])) {
        runInTransaction(function($conn) use ($migrations, $version) {
            foreach ($migrations[$version] as $sql) {
                $conn->query($sql);
            }

            // Update schema version
            $stmt = $conn->prepare("INSERT INTO schema_versions (version, applied_at) VALUES (?, NOW())");
            $stmt->bind_param('s', $version);
            $stmt->execute();
        });
    }
}
```

---

## Summary

### Key Takeaways

1. **Always use reference tables** for status and category values
2. **Normalize address and contact information** using dedicated tables
3. **Use proper JOINs** instead of subqueries for better performance
4. **Maintain foreign key relationships** for data integrity
5. **Test queries thoroughly** before deploying to production
6. **Monitor query performance** and optimize as needed
7. **Use transactions** for multi-table operations
8. **Implement proper error handling** for database operations

### Resources

- **Database Schema Documentation**: `/docs/DATABASE_NORMALIZATION.md`
- **API Documentation**: `/docs/API_REFERENCE.md`
- **Performance Guidelines**: `/docs/PERFORMANCE_GUIDELINES.md`
- **Migration Scripts**: `/database/migrations/`

### Support

For questions about normalized database usage:
- Check existing documentation first
- Review code examples in this guide
- Contact database administrator for complex queries
- Use development environment for testing changes

---

*This training guide should be reviewed annually and updated as the system evolves.*
