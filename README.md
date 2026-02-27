# RELAPSE - Premium Gadget Rental System
## Setup Guide for XAMPP

---

## 🚀 Quick Setup

### 1. Copy to XAMPP
```
Copy the entire `relapse` folder to: C:\xampp\htdocs\relapse
```

### 2. Start XAMPP
- Start **Apache** and **MySQL** in XAMPP Control Panel

### 3. Import Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Choose file: `relapse/database.sql`
4. Click **Go**

### 4. Open the App
- User App: `http://localhost/relapse`
- Admin Panel: Login with admin credentials below

---

## 👤 Default Credentials

### Admin Account
- **Email:** admin@relapse.com  
- **Password:** Admin@2024

> ⚠️ Change this password immediately after first login!

---

## 📁 Project Structure

```
relapse/
├── index.php          # Landing/splash page
├── database.sql       # Database setup
├── css/
│   └── style.css      # Main styles + animations
├── js/
│   └── app.js         # JavaScript utilities + API calls
├── php/
│   └── config.php     # Database config + helpers
├── api/
│   ├── auth.php       # Login, register, logout
│   ├── products.php   # Product CRUD + search
│   ├── rentals.php    # Rental management
│   └── user.php       # Profile, notifications, messages
├── pages/
│   ├── header.php     # Page header + nav
│   ├── footer.php     # Bottom nav
│   ├── login.php      # Login page
│   ├── register.php   # Register page
│   ├── home.php       # Home dashboard
│   ├── browse.php     # Browse & filter products
│   ├── search.php     # Search page
│   ├── product-detail.php  # Product info + rent modal
│   ├── my-rentals.php # User rentals list
│   ├── rental-detail.php   # Single rental detail
│   ├── notifications.php   # Notifications
│   ├── messages.php   # Contact/support messages
│   └── profile.php    # User profile
├── admin/
│   ├── header.php     # Admin header + sidebar
│   ├── footer.php     # Admin footer
│   ├── admin.css      # Admin styles
│   ├── dashboard.php  # Stats, charts, recent rentals
│   ├── rentals.php    # Manage all rentals
│   ├── products.php   # Add/edit/delete products
│   ├── users.php      # View all users
│   └── messages.php   # Reply to support messages
└── uploads/
    ├── products/      # Product images
    └── avatars/       # User avatars
```

---

## 🔌 API Endpoints

### Auth API (`/api/auth.php`)
| Action | Method | Description |
|--------|--------|-------------|
| login | POST | Authenticate user |
| register | POST | Create new account |
| logout | POST | End session |
| check | GET | Check auth status |

### Products API (`/api/products.php`)
| Action | Method | Description |
|--------|--------|-------------|
| list | GET | Get all products (paginated) |
| featured | GET | Get featured products |
| detail | GET | Single product detail |
| search | GET | Search products |
| categories | GET | Get all categories |
| add | POST | Add product (admin) |
| update | POST | Update product (admin) |
| delete | POST | Delete product (admin) |

### Rentals API (`/api/rentals.php`)
| Action | Method | Description |
|--------|--------|-------------|
| create | POST | Create new rental |
| list | GET | Get user's rentals |
| detail | GET | Single rental detail |
| cancel | POST | Cancel rental |
| pay | POST | Mark rental as paid |
| all | GET | All rentals (admin) |
| update_status | POST | Update status (admin) |

### User API (`/api/user.php`)
| Action | Method | Description |
|--------|--------|-------------|
| profile | GET | Get user profile |
| update_profile | POST | Update profile/avatar |
| change_password | POST | Change password |
| stats | GET | User stats |
| list | GET | Get notifications |
| read | POST | Mark notification read |
| read_all | POST | Mark all read |
| messages | GET | Get user messages |
| send_message | POST | Send support message |
| all_users | GET | All users (admin) |
| admin_stats | GET | Admin dashboard stats |

---

## 🎨 Features

### User Features
- ✅ Splash screen with animations
- ✅ Sign up / Login with validation
- ✅ Browse & filter gadgets by category
- ✅ Search products in real-time
- ✅ Product detail with specs & reviews
- ✅ Rent with date picker & price calculator
- ✅ My Rentals - view, track, cancel
- ✅ Payment flow integration
- ✅ Push notifications
- ✅ Contact support / messages
- ✅ Edit profile & avatar upload
- ✅ Change password

### Admin Features
- ✅ Dashboard with stats & charts
- ✅ Manage all rentals (update status)
- ✅ Add/edit/delete products with image upload
- ✅ View all users
- ✅ Reply to support messages
- ✅ Revenue chart (last 6 months)

### Technical
- ✅ PHP + MySQL (XAMPP compatible)
- ✅ Mobile-first responsive design
- ✅ Smooth CSS animations
- ✅ Dark blue theme matching RELAPSE brand
- ✅ REST API endpoints
- ✅ Session-based authentication
- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (sanitize function)

---

## ⚙️ Configuration

Edit `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'relapse_db');
define('BASE_URL', 'http://localhost/relapse');
```

---

*RELAPSE © 2024 - Premium Gadget Rental System*
