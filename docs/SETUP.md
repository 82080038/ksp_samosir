# KSP Samosir - Setup Guide

## 🚀 Quick Setup

### 1. Database Setup
```bash
# Import database schema
mysql -u root -p ksp_samosir < database/ksp_samosir.sql
```

### 2. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/ksp_samosir/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 3. File Permissions
```bash
chmod 755 -R /var/www/html/ksp_samosir
chown www-data:www-data -R /var/www/html/ksp_samosir
```

### 4. Configuration
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'ksp_samosir');
```

## 🔐 Default Login
- **Username**: admin
- **Password**: admin123

## 📋 Development Checklist

### Phase 1: Core Features (Week 1)
- [ ] Authentication system
- [ ] Dashboard with statistics
- [ ] Anggota management
- [ ] Basic CRUD operations

### Phase 2: Simpan Pinjam (Week 2)
- [ ] Simpanan types and transactions
- [ ] Pinjaman application and approval
- [ ] Angsuran management
- [ ] Financial calculations

### Phase 3: Pemasaran (Week 3)
- [ ] Product management
- [ ] Sales transactions
- [ ] Inventory tracking
- [ ] Customer management

### Phase 4: Laporan & Admin (Week 4)
- [ ] Financial reports
- [ ] User management
- [ ] System settings
- [ ] Data export

## 🛠️ Development Tools

### Required Software
- PHP 8.x
- MySQL 5.7+
- Web server (Apache/Nginx)
- Composer (optional)

### Browser Testing
- Chrome/Chromium
- Firefox
- Safari (for Mac testing)
- Edge (for Windows testing)

### Debugging Tools
- PHP error log: `tail -f logs/error.log`
- Browser developer tools
- MySQL query log

## 📊 Database Schema Overview

### Core Tables
- `users` - User authentication
- `anggota` - Member data
- `simpanan` - Savings accounts
- `pinjaman` - Loan accounts
- `produk` - Product inventory
- `penjualan` - Sales transactions
- `jurnal` - Accounting entries

### Relationships
- Users → Anggota (1:many)
- Anggota → Simpanan (1:many)
- Anggota → Pinjaman (1:many)
- Produk → Penjualan (1:many)

## 🔧 Configuration Options

### System Settings (settings table)
- `app_name` - Application name
- `bunga_simpanan_wajib` - Interest rate for mandatory savings
- `bunga_simpanan_sukarela` - Interest rate for voluntary savings
- `denda_keterlambatan` - Late payment penalty

### User Roles
- `admin` - Full access
- `staff` - Limited access
- `member` - Basic access

## 🚨 Security Considerations

### Must Implement
- [ ] Input validation
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] CSRF protection
- [ ] File upload security
- [ ] Session security

### Recommended
- [ ] HTTPS implementation
- [ ] Rate limiting
- [ ] Audit logging
- [ ] Backup encryption

## 📱 Mobile Compatibility

### Responsive Design
- Bootstrap 5 framework
- Mobile-first approach
- Touch-friendly interface
- Progressive Web App ready

### Testing
- Test on various screen sizes
- Test touch interactions
- Test performance on mobile devices

## 🔄 Deployment Checklist

### Pre-deployment
- [ ] Database backup
- [ ] Configuration review
- [ ] Security audit
- [ ] Performance testing

### Post-deployment
- [ ] Monitor error logs
- [ ] Check database performance
- [ ] Verify all features work
- [ ] Test user workflows

## 📞 Support & Maintenance

### Regular Tasks
- Daily: Check error logs
- Weekly: Database backup
- Monthly: Security updates
- Quarterly: Performance review

### Emergency Contacts
- Database admin: [contact]
- Web server admin: [contact]
- Application support: [contact]

## 🐛 Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check MySQL service
sudo systemctl status mysql

# Check database exists
mysql -u root -p -e "SHOW DATABASES LIKE 'ksp_samosir';"
```

#### 2. File Permission Error
```bash
# Fix permissions
sudo chown www-data:www-data -R /var/www/html/ksp_samosir
sudo chmod 755 -R /var/www/html/ksp_samosir
```

#### 3. Session Issues
```bash
# Check session directory
ls -la /var/lib/php/sessions/
```

#### 4. Upload Issues
```bash
# Check upload directory
mkdir -p public/uploads
chmod 755 public/uploads
```

### Error Codes
- **404**: Page not found
- **500**: Server error
- **403**: Access denied
- **401**: Unauthorized

## 📈 Performance Optimization

### Database Optimization
- Add indexes to frequently queried columns
- Use prepared statements
- Implement query caching
- Regular database maintenance

### Frontend Optimization
- Minify CSS/JS files
- Optimize images
- Enable browser caching
- Use CDN for static assets

### Server Optimization
- Enable Gzip compression
- Use PHP OPcache
- Configure proper memory limits
- Monitor server resources

## 🔄 Updates & Maintenance

### Version Control
- Use Git for source control
- Tag releases properly
- Document changes
- Test before deployment

### Database Migration
- Create migration scripts
- Test migrations on staging
- Backup before migration
- Rollback plan ready

---

## 🎯 Success Metrics

### Performance Targets
- Page load time: <2 seconds
- Database query time: <100ms
- Memory usage: <128MB
- CPU usage: <50%

### User Experience
- Navigation: <3 clicks to main features
- Form submission: <3 seconds
- Search results: <2 seconds
- Report generation: <10 seconds

### System Reliability
- Uptime: >99.5%
- Error rate: <1%
- Response time: <500ms
- Data accuracy: 100%

---

**Next Steps**: Follow the development checklist and implement features phase by phase.
