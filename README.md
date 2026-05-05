# Kumachi

Kumachi is a Laravel-based coffee ordering and operations management system with three main audiences:

- **Customer**: browse menu, customize drinks, cart, checkout, order history, rewards, profile
- **Staff/Kiosk**: in-store ordering flow for faster assisted checkout
- **Admin**: product/catalog management, orders, inventory, suppliers, purchase orders, reports, user management

## Project Description

Kumachi combines front-of-house ordering and back-of-house operations in one app. It supports customized beverage ordering (size, sugar, ice, add-ons), loyalty points, saved payment methods (mock flow), inventory tracking, and procurement workflows.

## Key Features

- **Authentication & Access Control**
  - Registration with verification flow
  - Login/logout, password reset (including OTP flow)
  - Role-based access (`admin`, `staff`, customer)
- **Customer Ordering**
  - Product catalog with categories, sizes, and add-ons
  - Cart management with quantity controls and customizations
  - Checkout with promo and rewards application
  - Order history, order detail, receipt, reorder, cancel
- **Kiosk Flow**
  - Staff kiosk mode for assisted in-store ordering
  - Kiosk cart/checkout/order receipt
- **Admin Operations**
  - Manage products, categories, add-ons, promotions, users
  - Manage suppliers, inventories, purchase orders
  - Receive purchase orders and update inventory
  - Order status handling and admin reporting
- **Notifications & Loyalty**
  - In-app notification center
  - Loyalty points earn/redeem records tied to orders

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Database**: MySQL
- **Frontend**: Blade templates + custom CSS (with minimal Tailwind/Breeze remnants)
- **Build tools**: Vite, npm
- **Testing**: PHPUnit / Laravel test suite (feature + unit tests)

## System Modules

- Customer Web
- Kiosk (Staff)
- Admin Dashboard
- Inventory & Procurement
- Promotions & Loyalty
- Notifications

## Setup Instructions

### 1) Clone and install dependencies

```bash
composer install
npm install
```

### 2) Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` database/mail settings based on your local setup.

### 3) Run migrations

```bash
php artisan migrate
```

### 4) Start the app

```bash
php artisan serve
npm run dev
```

## Testing

Run all tests:

```bash
php artisan test
```

Current suite includes unit and feature tests for:

- auth flows
- cart and checkout flows
- kiosk access
- admin management modules
- profile and user operations

## Database Notes

- Migrations are aligned with live schema.
- Recent cleanup removed unused `reviews` and `stock_alerts` tables.
- `notifications.type` was removed as unused.

## Important Routes (High Level)

- Customer: `/home`, `/menu`, `/cart`, `/checkout`, `/orders`, `/rewards`, `/profile`
- Kiosk: `/kiosk`, `/kiosk/menu`, `/kiosk/cart`, `/kiosk/checkout`
- Admin: `/admin` with module subroutes for products, orders, inventory, suppliers, purchase orders, reports

## Security & Operational Notes

- Verify role middleware for admin/staff routes before deployment.
- Review mail config for verification/reset in non-local environments.
- Always back up production DB before running destructive schema migrations.

## License

This project is currently maintained as an internal/academic system.  
Set your preferred license here (for example: MIT) if you plan to distribute it publicly.
