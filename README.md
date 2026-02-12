# KSP Samosir - Cooperative Management System

A modern web-based cooperative management system for Koperasi Pemasaran Kepolisian Polres Samosir, built with PHP and Bootstrap 5.

## 🚀 Quick Start

### Prerequisites
- Apache 2.4+ with PHP 8.0+
- MySQL 5.7+ or MariaDB 10.2+
- Web browser (Chrome, Firefox, Safari, Edge)

### Installation
1. Clone this repository:
```bash
git clone https://github.com/82080038/ksp_samosir.git
cd ksp_samosir
```

2. Setup database:
```sql
CREATE DATABASE ksp_samosir;
mysql -u root -p ksp_samosir < sql/ksp_samosir.sql
```

3. Configure Apache (add to `/etc/apache2/sites-available/`):
```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /path/to/ksp_samosir
    
    <Directory /path/to/ksp_samosir>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. Set permissions:
```bash
sudo chown -R www-data:www-data /path/to/ksp_samosir
sudo chmod -R 755 /path/to/ksp_samosir
```

5. Access application:
**http://localhost/ksp_samosir/**

## 📋 Features

### Core Modules
- 📊 **Dashboard** - Statistics and activity overview
- 👥 **Anggota** - Member management with address integration
- 💰 **Simpanan** - Savings with interest calculation
- 💸 **Pinjaman** - Loans with approval workflow
- 📦 **Produk** - Product catalog management
- 🛒 **Penjualan** - Sales transactions
- 📈 **Laporan** - Financial reports
- ⚙️ **Settings** - Cooperative parameters

### Advanced Features
- 🔐 **Role Management** - 5 roles with granular permissions
- 📚 **Accounting System** - COA, journals, SHU calculation
- 🗺️ **Address Management** - Indonesian address database
- ⚖️ **Compliance System** - UU 25/1992 compliant
- 📱 **Responsive Design** - Mobile-friendly interface
- 🔄 **AJAX Integration** - Real-time data loading

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+ with PDO
- **Frontend**: Bootstrap 5, jQuery 3.6
- **Database**: MySQL/MariaDB
- **Architecture**: MVC Pattern
- **Compliance**: UU No. 25 Tahun 1992

## 📱 Mobile Support

Fully responsive design with:
- Mobile navigation with hamburger menu
- Touch-friendly interface
- Adaptive layouts for all screen sizes
- Bootstrap 5 grid system

## 🔧 Development Mode

Currently configured for development:
- Authentication disabled for easy testing
- All modules accessible without login
- Extended session lifetime
- Error reporting enabled

See `docs/DISABLED_FEATURES_DEV.md` for production setup.

## 📊 Database Structure

### Main Database: `ksp_samosir`
- Users and roles management
- Cooperative operations (anggota, simpanan, pinjaman)
- Accounting system (COA, journals, SHU)
- Activities and compliance tracking

### External Database: `alamat_db`
- Indonesian address data (provinces, regencies, districts, villages)
- Direct lookup without local storage
- 34 provinces with complete hierarchy

## 📖 Documentation

- [`SETUP.md`](SETUP.md) - Installation and configuration guide
- [`docs/DOKUMENTASI_LENGKAP.md`](docs/DOKUMENTASI_LENGKAP.md) - Complete system documentation
- [`docs/DISABLED_FEATURES_DEV.md`](docs/DISABLED_FEATURES_DEV.md) - Development vs production differences

## 🔐 Default Credentials (Development)

- **Username**: admin
- **Password**: admin123

*Authentication is disabled in development mode - all pages accessible without login.*

## 📋 Compliance

This system complies with:
- **UU No. 25 Tahun 1992** tentang Perkoperasian
- **AD/ART Koperasi Pemasaran Kepolisian Polres Samosir**
- Indonesian cooperative accounting standards

## 🤝 Contributing

1. Fork this repository
2. Create feature branch: `git checkout -b feature-name`
3. Commit changes: `git commit -m 'Add feature'`
4. Push to branch: `git push origin feature-name`
5. Submit pull request

## 📄 License

This project is proprietary software for Koperasi Pemasaran Kepolisian Polres Samosir.

## 📞 Support

For technical support:
- Check the documentation in `docs/` folder
- Review setup instructions in `SETUP.md`
- Use browser developer tools for debugging

---

**Koperasi Pemasaran Kepolisian Polres Samosir**  
*Modern Cooperative Management System*
