# 🖨️ PrintPro Management System

Sistem Manajemen Percetakan Professional dengan PHP & MySQL

## 📋 Features

- ✅ **User Management** - Admin, Manager, Staff roles
- ✅ **Customer Management** - CRUD customers
- ✅ **Product Management** - CRUD products/services
- ✅ **Order Management** - CRUD orders with status tracking
- ✅ **Authentication** - Login/logout system
- ✅ **Dashboard** - Overview statistics
- ✅ **Responsive Design** - Mobile friendly

## 🚀 Installation

### Prerequisites
- XAMPP/WAMP/LAMP
- PHP 7.4+
- MySQL 5.7+
- Web browser

### Quick Setup

1. **Clone/Download** project ke folder `htdocs/printing-management`

2. **Start XAMPP** services (Apache + MySQL)

3. **Run Installer**:
   \`\`\`
   http://localhost/printing-management/setup/install.php
   \`\`\`

4. **Test Installation**:
   \`\`\`
   http://localhost/printing-management/test-users.php
   \`\`\`

5. **Login**:
   \`\`\`
   http://localhost/printing-management/login.php
   \`\`\`

## 🔑 Default Login Credentials

**Password untuk semua user: `admin123`**

### 👑 Admin Users
- `admin` / `admin123` - Administrator Utama
- `superadmin` / `admin123` - Super Administrator

### 👔 Manager Users  
- `manager1` / `admin123` - Manager Operasional
- `manager2` / `admin123` - Manager Penjualan

### 👷 Staff Users
- `staff1` / `admin123` - Staff Produksi
- `staff2` / `admin123` - Staff Customer Service
- `staff3` / `admin123` - Staff Design
- `staff4` / `admin123` - Staff Finishing

### 🧪 Demo Users
- `demo` / `admin123` - Demo User
- `guest` / `admin123` - Guest User (Inactive)

## 📁 Project Structure

\`\`\`
printing-management/
├── config/
│   └── database.php          # Database configuration
├── api/
│   ├── auth.php              # Authentication API
│   ├── users.php             # Users CRUD API
│   ├── customers.php         # Customers CRUD API
│   ├── products.php          # Products CRUD API
│   └── orders.php            # Orders CRUD API
├── database/
│   └── setup_database.sql    # Database setup script
├── setup/
│   └── install.php           # Installation wizard
├── .vscode/
│   └── settings.json         # VS Code settings
├── login.php                 # Login page
├── index.php                 # Main dashboard
├── test-users.php           # User testing page
└── README.md                # This file
\`\`\`

## 🛠️ Development

### VS Code Setup

1. **Install Extensions**:
   - PHP Intelephense
   - SQLTools
   - MySQL (SQLTools driver)

2. **Configure PHP Path** in `.vscode/settings.json`:
   \`\`\`json
   {
       "php.validate.executablePath": "C:\\xampp\\php\\php.exe",
       "php.executablePath": "C:\\xampp\\php\\php.exe"
   }
   \`\`\`

### Database Connection

Edit `config/database.php`:
\`\`\`php
private $host = "localhost";
private $db_name = "printing_management";
private $username = "root";
private $password = "";
\`\`\`

## 🔧 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL is running
   - Verify database credentials in `config/database.php`
   - Make sure database `printing_management` exists

2. **Login Failed**
   - Run installer: `setup/install.php`
   - Check users exist: `test-users.php`
   - Default password: `admin123`

3. **Permission Errors**
   - Check file permissions
   - Make sure Apache has read/write access

### Reset Database

Run installer again: `http://localhost/printing-management/setup/install.php`

## 📊 Database Schema

### Tables
- `users` - User accounts and authentication
- `customers` - Customer information
- `products` - Products/services catalog
- `orders` - Order management with status tracking

### Relationships
- `orders.customer_id` → `customers.id`
- `orders.product_id` → `products.id`
- `orders.user_id` → `users.id`

## 🎯 Usage

1. **Login** with admin credentials
2. **Manage Customers** - Add/edit customer information
3. **Manage Products** - Add/edit products and services
4. **Create Orders** - Process customer orders
5. **Track Status** - Monitor order progress
6. **User Management** - Add/manage staff accounts

## 🔒 Security Features

- Password hashing with PHP `password_hash()`
- SQL injection protection with PDO prepared statements
- Session management
- Role-based access control
- Input validation and sanitization

## 📱 Responsive Design

- Mobile-friendly interface
- Bootstrap-like styling
- Touch-friendly buttons
- Responsive tables

## 🚀 Production Deployment

1. **Update database credentials** in `config/database.php`
2. **Change default passwords** for all users
3. **Remove test files** (`test-users.php`, `setup/install.php`)
4. **Enable HTTPS** for secure authentication
5. **Set proper file permissions**

## 📞 Support

For issues and questions:
- Check troubleshooting section
- Review error logs in browser console
- Verify XAMPP services are running

---

**PrintPro Management System** - Professional Printing Business Management Solution
