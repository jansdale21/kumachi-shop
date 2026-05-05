You are helping build a Laravel web application called Kumachi, a coffee shop ordering and management system. The system uses Laravel with Blade templating, standard MVC structure, and MySQL database.

Follow a structured development sequence. Do not jump ahead. Build features step-by-step in this exact order.

PHASE 1 — PROJECT SETUP
Set up Laravel project, configure database, run migrations, install authentication (login, register, logout), and create a base Blade layout (app.blade.php).

PHASE 2 — CORE LAYOUT AND NAVIGATION
Create reusable layouts. Build customer layout with navbar (logo, menu, cart, notifications, profile). Build admin layout with sidebar. Set up routes in web.php.

PHASE 3 — PRODUCT SYSTEM
Create Category, Product, ProductSize, Addon, and ProductAddon models. Implement CRUD for admin. Build Menu page and Product Details page for customer.

PHASE 4 — CART SYSTEM
Implement Cart, CartItem, and CartItemAddon logic. Add to cart, update quantity, remove items. Create Cart page.

PHASE 5 — CHECKOUT SYSTEM
Handle order type (pickup or delivery), address selection, and payment method (mock). Create Checkout page and Address management.

PHASE 6 — ORDER SYSTEM
Create Order and OrderItem logic. Save orders from cart, generate order number, manage order status. Build Order confirmation, tracking, and history pages.

PHASE 7 — PAYMENT (MOCK)
Create Payment model. Simulate payment process and store status (pending, paid, failed). Build Payment selection and status pages.

PHASE 8 — RECEIPT
Create receipt Blade view showing full order summary and printable layout.

PHASE 9 — LOYALTY SYSTEM
Use rule: 1 point per ₱50 spent, 100 points = ₱50 discount. Implement loyalty_transactions, earning on order, and redemption in checkout. Add Loyalty dashboard in profile.

PHASE 10 — KIOSK MODE
Create simplified ordering interface using same backend. Optimize for fast ordering. Build kiosk menu, cart, and checkout.

PHASE 11 — ADMIN ORDER MANAGEMENT
Allow admin to view orders, filter by source (online or kiosk), and update status.

PHASE 12 — INVENTORY SYSTEM
Create inventory table. Deduct stock on orders. Track inventory_transactions. Build inventory page.

PHASE 13 — STOCK ALERTS
Detect low stock using reorder_level and create stock_alerts. Build alerts page.

PHASE 14 — SUPPLIER SYSTEM
Create suppliers and supplier_items. Link suppliers to inventory. Build supplier management page.

PHASE 15 — PURCHASE ORDERS
Create purchase_orders and purchase_order_items. Handle restocking and update inventory after receiving stock. Build purchase orders page.

PHASE 16 — PROMOTIONS
Create promotions table and apply discounts in checkout. Build promo management.

PHASE 17 — NOTIFICATIONS
Create notification system for orders, stock alerts, and promotions. Build notifications page.

DEVELOPMENT RULES
Follow MVC structure. Use controllers and Blade views properly. Reuse layouts and components. Test each phase before moving forward. Do not skip steps.