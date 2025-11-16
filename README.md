# Multi-Tenant E-Commerce Platform

A robust multi-tenant e-commerce platform built with Laravel 11, Vue 3, and Inertia.js. This system allows multiple independent stores (tenants) to operate on a single codebase with complete data isolation.

## 🚀 Features

### Multi-Tenancy Architecture
- **Complete Data Isolation** - Each tenant has a separate PostgreSQL database
- **Domain-based Identification** - Tenants are identified by their subdomain
- **Landlord Dashboard** - Central management system for tenant administration
- **Dynamic Tenant Provisioning** - Create new stores with automatic database setup

### E-Commerce Functionality
- **Product Management** - Full CRUD operations with image support
- **Shopping Cart System** - Add, update, remove items with real-time total calculation
- **User Authentication** - Secure login/register system with Laravel Breeze
- **Responsive Design** - Mobile-friendly UI with Tailwind CSS

### Technical Stack
- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS
- **Database:** PostgreSQL (separate databases per tenant)
- **Multi-tenancy:** stancl/tenancy package
- **Testing:** PHPUnit with comprehensive test coverage

---

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js & npm
- PostgreSQL 14+
- Git

---

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/rizalefhndi/multi-tenant-ecommerce.git
cd multi-tenant-ecommerce
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file with your PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=multi_tenant_ecommerce
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations

```bash
# Run central database migrations
php artisan migrate

# Seed landlord admin user
php artisan db:seed --class=LandlordAdminSeeder
```

### 6. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## 👤 Default Credentials

### Landlord Admin (Central Management)
- **URL:** `http://localhost:8000/login`
- **Email:** `admin@landlord.local`
- **Password:** `password`

### Tenant Users (After creating a tenant and seeding)
- **Admin:** `admin@store.local` / `password`
- **Customer:** `customer@store.local` / `password`

---

## 🏪 Creating Your First Tenant

### Method 1: Via Landlord Dashboard (Recommended)

1. Login as landlord admin at `http://localhost:8000/login`
2. Go to **Tenants** menu
3. Click **Create New Tenant**
4. Fill in the form:
   - **Tenant ID:** `shop1` (unique identifier)
   - **Name:** `My First Shop`
   - **Domain:** `shop1` (will become `shop1.localhost`)
5. Click **Create Tenant**
6. After creation, run migrations and seed data:
   - Click **Migrate** button to create tenant database tables
   - Click **Seed** button to populate with sample products

### Method 2: Via Artisan Console

```bash
# Create tenant
php artisan tinker

>>> $tenant = App\Models\Tenant::create(['id' => 'shop1']);
>>> $tenant->domains()->create(['domain' => 'shop1.localhost']);
>>> exit

# Run tenant migrations
php artisan tenants:migrate --tenants=shop1

# Seed tenant data
php artisan tenants:seed --tenants=shop1 --class=TenantUserSeeder
php artisan tenants:seed --tenants=shop1 --class=ProductSeeder
```

### Accessing Your Tenant Store

**Development:** Add to your hosts file:
```
127.0.0.1 shop1.localhost
```

Then visit: `http://shop1.localhost:8000`

---

## 📦 Database Seeders

### LandlordAdminSeeder
Creates the central admin user for landlord dashboard.

```bash
php artisan db:seed --class=LandlordAdminSeeder
```

**Creates:**
- Name: `Landlord Admin`
- Email: `admin@landlord.local`
- Password: `password`

### TenantUserSeeder
Creates default users for a tenant store.

```bash
php artisan tenants:seed --tenants=shop1 --class=TenantUserSeeder
```

**Creates:**
- Admin Store: `admin@store.local` / `password`
- Customer Test: `customer@store.local` / `password`

### ProductSeeder
Populates tenant store with 15 sample products.

```bash
php artisan tenants:seed --tenants=shop1 --class=ProductSeeder
```

**Sample Products (15 items):**
1. Laptop Gaming ASUS ROG - Rp 18,500,000
2. Samsung Galaxy S24 Ultra - Rp 19,999,000
3. MacBook Air M3 - Rp 16,999,000
4. Sony WH-1000XM5 - Rp 5,499,000
5. iPad Pro 12.9 inch - Rp 17,999,000
6. Logitech MX Master 3S - Rp 1,599,000
7. Keychron K8 Pro - Rp 2,199,000
8. LG UltraGear 27" Gaming Monitor - Rp 4,999,000
9. Razer DeathAdder V3 Pro - Rp 2,499,000
10. Samsung 980 PRO 1TB NVMe SSD - Rp 2,299,000
11. Corsair Vengeance RGB 32GB DDR5 - Rp 3,199,000
12. NZXT H7 Flow RGB Case - Rp 2,499,000
13. Cooler Master V850 SFX Gold PSU - Rp 2,799,000
14. Western Digital My Passport 5TB - Rp 2,599,000
15. TP-Link Archer AX73 WiFi 6 Router - Rp 1,499,000

All products include:
- High-quality images from Unsplash
- Detailed descriptions
- Stock information
- Active status

---

## 🧪 Testing

This project includes comprehensive test coverage for multi-tenant functionality.

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suites

```bash
# tenant isolation test
php artisan test --filter TenantIsolationTest

```

### Test Coverage

**Tenant Isolation Tests (7 tests, 42 assertions)**
- ✅ `test_tenant_databases_are_isolated_products` - Verifies product data isolation
- ✅ `test_tenant_databases_are_isolated_users` - Verifies user data isolation
- ✅ `test_multiple_tenants_can_have_same_user_email_in_different_databases` - Tests email uniqueness per tenant
- ✅ `test_tenant_product_ids_do_not_conflict` - Ensures ID auto-increment is isolated
- ✅ `test_deleting_tenant_data_does_not_affect_other_tenants` - Validates delete operations safety
- ✅ `test_central_database_remains_separate_from_tenant_data` - Confirms central/tenant separation
- ✅ `test_tenant_switch_maintains_data_integrity` - Tests context switching reliability


### Test Documentation

Detailed test documentation available in:
- `docs/MULTI_TENANT_ISOLATION_TESTS.md` - tenant testing isolation

---

## 📁 Project Structure

```
multi-tenant-ecommerce/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/              # Authentication controllers
│   │   │   ├── Landlord/          # Landlord management controllers
│   │   │   ├── CartController.php
│   │   │   └── ProductController.php
│   │   └── Middleware/            # Custom middleware
│   ├── Models/
│   │   ├── Cart.php               # Shopping cart model
│   │   ├── CartItem.php           # Cart items model
│   │   ├── Product.php            # Product model
│   │   ├── Tenant.php             # Tenant model (extends stancl)
│   │   └── User.php               # User model
│   └── Providers/
│       └── TenancyServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── [central migrations]   # Central database tables
│   │   └── tenant/                # Tenant database tables
│   │       ├── *_create_users_table.php
│   │       ├── *_create_products_table.php
│   │       ├── *_create_carts_table.php
│   │       ├── *_create_cart_items_table.php
│   │       ├── *_create_sessions_table.php
│   │       └── *_create_cache_table.php
│   └── seeders/
│       ├── LandlordAdminSeeder.php
│       ├── TenantUserSeeder.php
│       └── ProductSeeder.php
├── resources/
│   ├── js/
│   │   ├── Components/            # Reusable Vue components
│   │   ├── Layouts/
│   │   │   └── AuthenticatedLayout.vue
│   │   └── Pages/
│   │       ├── Landlord/          # Landlord dashboard pages
│   │       ├── Products/          # Product pages
│   │       └── Carts/             # Shopping cart pages
│   └── views/
│       └── app.blade.php
├── routes/
│   ├── web.php                    # Landlord routes
│   ├── tenant.php                 # Tenant routes
│   └── auth.php                   # Authentication routes
├── tests/
│   ├── Feature/
│   │   ├── TenantIsolationTest.php
│   │   ├── TenantIsolationTest.php
│   │   ├── ProductCRUDTest.php
│   │   └── CartFunctionalityTest.php
│   └── Unit/
│   │   ├── Example.php
└── docs/
    └── TENANT_ISOLATION_TESTS.md
```

---

## 🔧 Common Artisan Commands

### Tenant Management

```bash
# List all tenants
php artisan tenants:list

# Run migrations for all tenants
php artisan tenants:migrate

# Run migrations for specific tenant
php artisan tenants:migrate --tenants=shop1

# Rollback tenant migrations
php artisan tenants:rollback

# Seed all tenants
php artisan tenants:seed

# Seed specific tenant
php artisan tenants:seed --tenants=shop1 --class=ProductSeeder

# Run fresh migrations for all tenants
php artisan tenants:migrate-fresh
```

### Development

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear

# Queue worker (if using queues)
php artisan queue:work

# Schedule worker (if using scheduled tasks)
php artisan schedule:work
```

---

## 🌐 Multi-Tenant Architecture

### How It Works

1. **Central Database** (`multi_tenant_ecommerce`)
   - Stores tenant metadata (ID, name)
   - Stores domain mappings
   - Landlord admin users
   - Central configuration

2. **Tenant Databases** (e.g., `tenant_shop1`)
   - Separate database per tenant
   - Complete data isolation
   - Independent users, products, carts
   - Same schema across all tenants

3. **Domain Identification**
   - Landlord: `localhost` or `127.0.0.1`
   - Tenant: `{tenant_id}.localhost` (e.g., `shop1.localhost`)
   - Middleware automatically detects and switches context

### Request Flow

```
User Request → Domain Detection → Tenant Identification → Database Switch → Route Handling
```

**Example:**
- Request to `localhost` → Landlord context → Central database
- Request to `shop1.localhost` → Tenant context → `tenant_shop1` database

---

## 🎨 Frontend Features

### Landlord Dashboard
- Tenant list with statistics
- Create/edit/delete tenants
- Run migrations per tenant
- Seed data per tenant
- Domain management

### Tenant Storefront
- Product catalog with pagination
- Product detail pages
- Shopping cart with real-time updates
- Add/remove/update cart items
- User profile management
- Responsive navigation

### UI Components
- Flash messages for user feedback
- Dropdown menus
- Modal dialogs
- Form validation
- Loading states
- Mobile-responsive design

---

## 🔐 Security Features

- CSRF protection on all forms
- Password hashing with bcrypt
- SQL injection prevention via Eloquent ORM
- XSS protection in Vue templates
- Database-level tenant isolation
- Session management per tenant
- Email verification support
- Password reset functionality

---

## 📚 API Routes

### Landlord Routes (localhost)

```
GET    /login                     - Login page
POST   /login                     - Authenticate user
POST   /logout                    - Logout user
GET    /register                  - Registration page
POST   /register                  - Register new user

GET    /landlord/dashboard        - Landlord dashboard
GET    /landlord/tenants          - List tenants
GET    /landlord/tenants/create   - Create tenant form
POST   /landlord/tenants          - Store new tenant
GET    /landlord/tenants/{id}/edit - Edit tenant form
PUT    /landlord/tenants/{id}     - Update tenant
DELETE /landlord/tenants/{id}     - Delete tenant
POST   /landlord/tenants/{id}/migrate - Run tenant migrations
POST   /landlord/tenants/{id}/seed - Seed tenant data

GET    /profile                   - Profile page
PATCH  /profile                   - Update profile
DELETE /profile                   - Delete account
```

### Tenant Routes ({tenant}.localhost)

```
GET    /login                     - Tenant login
POST   /login                     - Authenticate tenant user
POST   /logout                    - Logout tenant user

GET    /dashboard                 - Tenant dashboard
GET    /products                  - Product list
GET    /products/{id}             - Product detail
GET    /cart                      - View cart
POST   /cart/add/{product}        - Add to cart
PUT    /cart/update/{item}        - Update cart item
DELETE /cart/remove/{item}        - Remove from cart
DELETE /cart/clear                - Clear entire cart

GET    /profile                   - User profile
PATCH  /profile                   - Update profile
```

---

## 🚧 Troubleshooting

### Subdomain Not Working

Add to your hosts file:

**Windows:** `C:\Windows\System32\drivers\etc\hosts`
**Linux/Mac:** `/etc/hosts`

```
127.0.0.1 shop1.localhost
127.0.0.1 shop2.localhost
```

### Database Connection Error

Check PostgreSQL is running:
```bash
# Windows
services.msc (look for PostgreSQL)

# Linux/Mac
sudo systemctl status postgresql
```

### Tenant Database Not Found

Run migrations for the tenant:
```bash
php artisan tenants:migrate --tenants=shop1
```

### Assets Not Loading

Rebuild frontend assets:
```bash
npm run build
php artisan optimize:clear
```

### Tests Failing

Ensure test database is configured:
```bash
php artisan config:clear
php artisan test
```

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Author

**Rizal Efendi**
- GitHub: [@rizalefhndi](https://github.com/rizalefhndi)

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [stancl/tenancy](https://tenancyforlaravel.com) - Multi-tenancy package
- [Inertia.js](https://inertiajs.com) - Modern monolith approach
- [Vue.js](https://vuejs.org) - Progressive JavaScript framework
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Unsplash](https://unsplash.com) - High-quality product images

---

## 📞 Support

For issues, questions, or contributions, please:
1. Check existing [GitHub Issues](https://github.com/rizalefhndi/multi-tenant-ecommerce/issues)
2. Create a new issue with detailed information
3. Submit pull requests for improvements

---

**Happy Coding! 🎉**
