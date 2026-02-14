# KSP Samosir - Technical Documentation

## Database Normalization Documentation

### Overview
This document describes the database normalization process implemented for the KSP Samosir cooperative management system. The database has been normalized to Third Normal Form (3NF) to eliminate data redundancy, ensure data integrity, and improve query performance.

### Normalization Process Summary

#### Original Issues
- **Redundant Data**: Member information duplicated between `users` and `anggota` tables
- **Address/Contact Repetition**: Address and contact fields repeated across multiple tables
- **Status/Category Denormalization**: Status and category values stored as strings instead of referenced IDs
- **Partial Dependencies**: Some tables had fields depending only on part of composite primary keys
- **Transitive Dependencies**: Fields depending on other non-key fields

#### Normalization Applied
1. **1NF (First Normal Form)**: All fields contain atomic values, no repeating groups
2. **2NF (Second Normal Form)**: Removed partial dependencies on composite keys
3. **3NF (Third Normal Form)**: Removed transitive dependencies on non-key fields

---

## Database Schema Documentation

### Reference Tables

#### `status_types`
Centralized status values for all entities in the system.
```sql
CREATE TABLE status_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(50) NOT NULL, -- 'member', 'employee', 'supplier', 'asset', etc.
    status_code VARCHAR(50) NOT NULL,
    status_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Usage Examples:**
- Member statuses: 'active', 'inactive', 'suspended'
- Employee statuses: 'active', 'inactive', 'terminated'
- Asset conditions: 'excellent', 'good', 'fair', 'poor', 'critical'

#### `categories`
Centralized category values for different types of data.
```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_type VARCHAR(50) NOT NULL, -- 'product', 'service', 'asset', 'expense', etc.
    category_code VARCHAR(50) NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    parent_category_id INT,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES categories(id)
);
```

**Usage Examples:**
- Product categories: 'food', 'beverage', 'household'
- Service types: 'consultation', 'maintenance'
- Asset types: 'building', 'vehicle', 'equipment'

#### `departments` & `positions`
Organizational structure for employees.
```sql
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_code VARCHAR(20) UNIQUE NOT NULL,
    department_name VARCHAR(100) NOT NULL,
    description TEXT,
    manager_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE positions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    position_code VARCHAR(20) UNIQUE NOT NULL,
    position_name VARCHAR(100) NOT NULL,
    department_id INT,
    level INT DEFAULT 1,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

### Address & Contact Normalization

#### `addresses`
Normalized address storage.
```sql
CREATE TABLE addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    address_type ENUM('home', 'office', 'billing', 'shipping', 'other') DEFAULT 'home',
    street_address VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Indonesia',
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    is_primary TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `contacts`
Normalized contact information storage.
```sql
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contact_type ENUM('phone', 'mobile', 'email', 'fax', 'website') NOT NULL,
    contact_value VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Relationship Tables
```sql
CREATE TABLE entity_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entity_type ENUM('member', 'supplier', 'employee', 'customer', 'other') NOT NULL,
    entity_id INT NOT NULL,
    address_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE CASCADE
);

CREATE TABLE entity_contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entity_type ENUM('member', 'supplier', 'employee', 'customer', 'other') NOT NULL,
    entity_id INT NOT NULL,
    contact_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
);
```

---

## Application Code Changes

### Controller Updates

#### Updated Controllers
The following controllers have been updated to work with normalized schema:

1. **AnggotaController** - Uses normalized address/contact queries
2. **PayrollController** - Uses department/position references
3. **PemasokController** - Uses category/status references
4. **AssetController** - Uses category/status references
5. **PenjualanController** - Uses customer address/contact
6. **CustomerServiceController** - Uses member data queries
7. **ShuController** - Uses normalized member queries
8. **LaporanController** - Updated member activity queries

#### Query Pattern Changes

**Before (Denormalized):**
```php
// Old way - direct field access
$member = fetchRow("SELECT * FROM anggota WHERE id = ?", [$id]);
echo $member['alamat']; // Direct field access
```

**After (Normalized):**
```php
// New way - joined with normalized tables
$member = fetchRow("
    SELECT
        a.id, a.nama_lengkap, a.nik,
        COALESCE(addr.street_address, a.alamat) as alamat,
        COALESCE(c.contact_value, a.no_hp) as no_hp
    FROM anggota a
    LEFT JOIN addresses addr ON a.address_id = addr.id AND addr.is_primary = 1
    LEFT JOIN contacts c ON a.primary_contact_id = c.id AND c.contact_type = 'phone'
    WHERE a.id = ?
", [$id]);
```

### Backward Compatibility

The application maintains backward compatibility during the transition period:
- Old denormalized fields are still accessible
- COALESCE functions ensure data availability
- Gradual migration path for existing data

---

## Migration Guide

### Data Migration Process

1. **Backup Existing Data**
   ```bash
   mysqldump -u username -p ksp_samosir > backup_before_normalization.sql
   ```

2. **Run Normalization Script**
   ```bash
   mysql -u username -p ksp_samosir < database/normalization_migration.sql
   ```

3. **Migrate Existing Data** (if needed)
   - Addresses from `alamat` fields to `addresses` table
   - Contacts from `no_hp`, `email` fields to `contacts` table
   - Status/category strings to reference IDs

4. **Update Application Code**
   - Deploy updated controllers
   - Test all functionality
   - Monitor for issues

### Rollback Plan

If issues occur during migration:

1. **Restore from Backup**
   ```bash
   mysql -u username -p ksp_samosir < backup_before_normalization.sql
   ```

2. **Remove Migration Changes**
   ```sql
   -- Drop new tables if needed
   DROP TABLE IF EXISTS entity_contacts;
   DROP TABLE IF EXISTS entity_addresses;
   DROP TABLE IF EXISTS contacts;
   DROP TABLE IF EXISTS addresses;
   -- etc.
   ```

---

## Best Practices for Normalized Database

### Query Optimization

1. **Use Proper Joins**
   ```sql
   -- Good: Explicit JOIN with normalized tables
   SELECT m.nama_lengkap, addr.street_address, c.contact_value
   FROM anggota m
   LEFT JOIN addresses addr ON m.address_id = addr.id
   LEFT JOIN contacts c ON m.primary_contact_id = c.id;

   -- Avoid: Subqueries in SELECT
   SELECT nama_lengkap,
          (SELECT street_address FROM addresses WHERE id = anggota.address_id) as alamat
   FROM anggota;
   ```

2. **Index Strategy**
   - Primary keys automatically indexed
   - Foreign keys should be indexed
   - Consider composite indexes for common query patterns

3. **Query Performance**
   - Use EXPLAIN to analyze query execution
   - Avoid SELECT * in production
   - Use LIMIT for large result sets

### Data Integrity

1. **Foreign Key Constraints**
   - Always define foreign key relationships
   - Use CASCADE for related data management
   - Handle constraint violations gracefully

2. **Reference Data Management**
   - Use status_types and categories tables for consistency
   - Avoid hard-coded status values in application code
   - Maintain referential integrity

### Application Development

1. **Model Layer**
   - Create repository classes for complex queries
   - Use prepared statements for security
   - Implement caching for frequently accessed reference data

2. **Error Handling**
   - Handle foreign key constraint violations
   - Provide meaningful error messages
   - Log database errors for debugging

3. **Testing**
   - Test all CRUD operations
   - Verify data integrity constraints
   - Performance test with realistic data volumes

---

## API Documentation

### Status Types API
```php
// Get all active status types for a category
$statuses = fetchAll("SELECT * FROM status_types WHERE category = ? AND is_active = 1", [$category]);

// Example usage in forms
foreach ($statuses as $status) {
    echo "<option value='{$status['status_code']}'>{$status['status_name']}</option>";
}
```

### Categories API
```php
// Get categories with hierarchy
$categories = fetchAll("
    SELECT c.*, p.category_name as parent_name
    FROM categories c
    LEFT JOIN categories p ON c.parent_category_id = p.id
    WHERE c.category_type = ? AND c.is_active = 1
    ORDER BY c.category_name
", [$category_type]);
```

### Address/Contact Management
```php
// Insert new address
$stmt = $conn->prepare("INSERT INTO addresses (address_type, street_address, city, postal_code) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $type, $street, $city, $postal);

// Link address to entity
$stmt = $conn->prepare("INSERT INTO entity_addresses (entity_type, entity_id, address_id) VALUES (?, ?, ?)");
$stmt->bind_param('sii', $entity_type, $entity_id, $address_id);
```

---

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Errors**
   - Ensure referenced records exist before inserting
   - Check for orphaned records after deletion
   - Use transactions for multi-table operations

2. **Query Performance Issues**
   - Add missing indexes on foreign keys
   - Optimize JOIN order
   - Consider query result caching

3. **Data Migration Problems**
   - Validate data before migration
   - Handle NULL values appropriately
   - Test migration on development environment first

### Monitoring Queries

```sql
-- Check for orphaned records
SELECT COUNT(*) FROM anggota WHERE address_id IS NOT NULL AND address_id NOT IN (SELECT id FROM addresses);

-- Monitor query performance
SELECT sql_text, exec_count, avg_timer_wait/1000000000 as avg_time_sec
FROM performance_schema.events_statements_summary_by_digest
WHERE sql_text LIKE '%anggota%' ORDER BY avg_timer_wait DESC;

-- Check foreign key constraints
SELECT constraint_name, table_name, column_name, referenced_table_name, referenced_column_name
FROM information_schema.key_column_usage
WHERE referenced_table_schema = 'ksp_samosir' AND referenced_table_name IS NOT NULL;
```

---

## Future Enhancements

1. **Advanced Indexing**
   - Full-text search indexes
   - Spatial indexes for location data
   - Partial indexes for filtered queries

2. **Data Archiving**
   - Implement table partitioning
   - Archive old transaction data
   - Automated cleanup procedures

3. **Replication & High Availability**
   - Master-slave replication setup
   - Read/write splitting
   - Failover procedures

4. **Advanced Analytics**
   - Materialized views for complex aggregations
   - Data warehouse integration
   - Real-time analytics capabilities

---

## Support & Maintenance

### Regular Maintenance Tasks

1. **Index Maintenance**
   ```sql
   ANALYZE TABLE addresses, contacts, entity_addresses, entity_contacts;
   ```

2. **Reference Data Updates**
   - Regularly review and update status types
   - Maintain category hierarchies
   - Update department/position structures

3. **Performance Monitoring**
   - Monitor slow queries
   - Check index usage statistics
   - Optimize based on usage patterns

### Contact Information

For technical support regarding the normalized database:
- Database Administrator
- System Developer Team
- Documentation maintained in `/docs/database/` directory

---

*This documentation is automatically updated with each database schema change. Last updated: [Current Date]*
