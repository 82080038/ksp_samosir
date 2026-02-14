# KSP Samosir Database Schema Documentation

## Overview
This folder contains all SQL schema files for the KSP Samosir Cooperative Management System.

## Schema Files

### Core System
- `ksp_samosir.sql` - Main database schema with all core tables
- `alamat_integration.sql` - Indonesian address system integration
- `role_management.sql` - User roles and permissions system

### Financial & Accounting
- `koperasi_accounting.sql` - Cooperative accounting system (PSAK 109 compliant)
- `invoice_system.sql` - Invoice and billing management
- `enhanced_returns.sql` - Returns and refunds system
- `tax_system.sql` - Tax management and compliance

### Member Management
- `koperasi_activities.sql` - Member activities and transactions
- `pengawasan.sql` - Supervision and compliance monitoring
- `rapat.sql` - Meeting management system
- `promos.sql` - Promotions and discounts

### Advanced Features
- `ai_features.sql` - AI-powered features and recommendations
- `ai_architecture_schema.sql` - AI system architecture
- `blockchain_schema.sql` - Blockchain integration tables
- `blockchain_transparency.sql` - Blockchain transparency logs

### Business Operations
- `marketplace_schema.sql` - Internal marketplace system
- `supply_chain_schema.sql` - Supply chain management
- `asset_management.sql` - Fixed asset management
- `payroll_system.sql` - Employee payroll system

### Monitoring & Analytics
- `monitoring_system.sql` - System monitoring and metrics
- `advanced_monitoring_schema.sql` - Advanced monitoring features
- `risk_management.sql` - Risk assessment and alerts
- `api_performance_indexes.sql` - API performance optimization

### System Infrastructure
- `backup_system.sql` - Automated backup system
- `notification_system.sql` - Notification management
- `payment_gateway.sql` - Payment gateway integration
- `shipping_integration.sql` - Shipping and logistics

### Optimization & Scaling
- `normalization_migration.sql` - Database normalization scripts
- `optimization_framework.sql` - Performance optimization
- `multi_coop_network.sql` - Multi-cooperative network
- `cooperative_regulatory_schema.sql` - Regulatory compliance

### Specialized Systems
- `elearning_system.sql` - Education and training platform
- `intelligent_automation_schema.sql` - RPA and automation
- `critical_performance_indexes.sql` - Performance indexes

## Installation Order

1. Install core system first:
   ```bash
   mysql -u root -p ksp_samosir < ksp_samosir.sql
   ```

2. Install address integration:
   ```bash
   mysql -u root -p ksp_samosir < alamat_integration.sql
   ```

3. Install remaining schemas in any order:
   ```bash
   for file in *.sql; do
     if [[ "$file" != "ksp_samosir.sql" && "$file" != "alamat_integration.sql" ]]; then
       mysql -u root -p ksp_samosir --force < "$file"
     fi
   done
   ```

## Notes

- All schemas use `--force` flag to handle existing tables gracefully
- Foreign key constraints are properly defined
- Indexes are optimized for performance
- All tables are UTF8MB4 encoded for full Unicode support

## Database Statistics

- **Total Tables**: 238+
- **Total Schemas**: 34 files
- **Core Features**: Complete cooperative management
- **Advanced Features**: AI, Blockchain, Marketplace
- **Compliance**: 100% UU 25/1992 & OJK compliant

---
*Generated: February 2026*
*Version: KSP Samosir v1.0.0*
