<p align="center">
  <img src="https://ui-avatars.com/api/?name=BiteHub&background=ff6b35&color=fff&size=128&bold=true&font-size=0.4" alt="BiteHub Logo" width="128"/>
</p>

<h1 align="center">BiteHub — Homemade Food Delivery & Meal Plan Platform</h1>

<p align="center">
  A full-stack, multi-tenant food delivery marketplace connecting home kitchens, caterers, delivery agents, and customers. BiteHub features an integrated admin back-office, financial settlement engine, AI-powered support bot, real-time order tracking, customizable meal plan subscriptions, and a companion Flutter mobile app.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 10"/>
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.1+"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Flutter-3.x-02569B?style=for-the-badge&logo=flutter&logoColor=white" alt="Flutter"/>
  <img src="https://img.shields.io/badge/Stripe-Integrated-635BFF?style=for-the-badge&logo=stripe&logoColor=white" alt="Stripe"/>
  <img src="https://img.shields.io/badge/Paymob-Integrated-00A6FF?style=for-the-badge" alt="Paymob"/>
</p>

---

## 📖 Table of Contents
- [About The Project](#about-the-project)
- [Platform Workflows (Flows)](#platform-workflows-flows)
  - [1. Standard Order Flow](#1-standard-order-flow)
  - [2. Customized Orders Flow](#2-customized-orders-flow)
  - [3. Meal Plan Subscriptions Flow](#3-meal-plan-subscriptions-flow)
- [Financial System (Wallet, Refunds & Withdrawals)](#financial-system-wallet-refunds--withdrawals)
- [Key Features by Role](#key-features-by-role)
- [Technical Architecture & Stack](#technical-architecture--stack)
- [Database Overview](#database-overview)
- [API Overview](#api-overview)
- [Installation & Setup](#installation--setup)

---

## 🚀 About The Project

**BiteHub** is a production-grade marketplace built specifically for the homemade food and catering industry in Egypt and the MENA region. It unites five distinct user types into one cohesive ecosystem:
- **Customers**: Browse homemade meals, build custom meal plans, chat with kitchens to customize orders, and track deliveries via GPS.
- **Kitchen Owners**: Manage daily menus, offer subscription meal plans, accept customization requests, and manage earnings.
- **Caterers**: Handle large event requests and bulk catering orders.
- **Delivery Agents**: Fulfill deliveries with GPS tracking, OTP-secured handoffs, and earn daily bonuses.
- **Admins & Owners**: Monitor operations via a KPI dashboard, manage financial settlements, handle AI-bot escalations, and approve withdrawals/refunds.

---

## 🔄 Platform Workflows (Flows)

BiteHub is driven by robust real-world workflows that handle edge cases seamlessly.

### 1. Standard Order Flow
1. **Cart & Checkout**: Customers browse multiple kitchens, build their cart, and checkout using Cash on Delivery, Wallet, Stripe, or Paymob. Promo codes and loyalty points can be applied.
2. **Vendor Processing**: The Kitchen Owner receives the order and advances its status (`Pending` ➔ `Confirmed` ➔ `Preparing` ➔ `Ready`).
3. **Smart Agent Assignment**: Once marked `Ready`, the system assigns a Delivery Agent based on proximity and predefined service areas.
4. **Real-Time GPS Tracking**: The Agent updates the status to `Delivering` and broadcasts live coordinates. The customer tracks their order on a live map.
5. **Secure Handoff**: The Agent requests a **4-digit OTP** from the customer to successfully mark the order as `Delivered`.
6. **Financial Settlement**: The system splits the revenue (e.g., 85% Vendor, 15% Platform commission), deducts applicable fees, and credits loyalty points to the customer.

### 2. Customized Orders Flow
To support the nature of homemade food, customers can request specific dish modifications (e.g., "Extra spicy, no onions, gluten-free").
1. **Pre-Order Customization Chat**: Customer initiates a customization request chat directly from the menu item page.
2. **Real-Time Negotiation**: The Customer and Kitchen Owner discuss the request.
3. **Dynamic Pricing**: If the customization requires extra ingredients, the Kitchen Owner can increase the item's price directly inside the chat.
4. **Approval & Add to Cart**: Once approved by the kitchen, the customer can add this newly modified item to their cart and proceed to checkout normally.

### 3. Meal Plan Subscriptions Flow
Kitchens can offer recurring meal plans (e.g., "Healthy Diet Plan: 2 meals/day for 14 days").
1. **Plan Builder**: Customers browse available Kitchen Plans and use the interactive builder to select specific meals for each day.
2. **Time Preferences**: Customers set preferred delivery time slots for their daily meals.
3. **Kitchen Review**: The Kitchen Owner reviews the subscriber's choices, approves them, or adds kitchen notes and modifications.
4. **Flexible Payments**: Customers pay upfront or in installments (via a smart `PaidAmount` balance tracker).
5. **Automated Dispatching**: A daily Cron job (`subscriptions:dispatch-orders`) automatically generates and dispatches the daily orders based on the customer's plan and selected time slots.
6. **Lifecycle**: Subscriptions can be Paused, Resumed, or Cancelled (triggering a pro-rated refund).

---

## 💳 Financial System (Wallet, Refunds & Withdrawals)

BiteHub natively handles internal wallets, split payments, and debts, fully integrated with **Stripe** and **Paymob**.

### Refunds (Request & Approval Based)
To prevent abuse, refunds follow a strict administrative workflow:
1. **Initiate Request**: A customer submits a **Refund Request** (via Support Tickets) for a problematic Order or a Subscription.
2. **Pro-Rated Calculation**: For subscriptions cancelled mid-way, the system automatically calculates a pro-rated refund based strictly on unconsumed meals.
3. **Admin Review**: Admins review the case details and approve or reject the refund.
4. **Wallet Settlement**: Upon approval, the refunded amount is seamlessly deducted from the Vendor's wallet and credited to the Customer's wallet.

### Money Withdrawals (Request & Approval Based)
Vendors and Delivery Agents earn money directly into their BiteHub Wallets.
1. **Initiate Request**: Once a vendor's balance exceeds the minimum threshold (e.g., 50 EGP), they request a withdrawal to their saved payment methods (Bank Transfer, VodafoneCash, or InstaPay).
2. **Admin Verification**: Admins review withdrawal requests on the central dashboard.
3. **Processing & Commission**: Upon processing the external transfer, the admin marks it `Approved`. The system deducts the requested amount plus a **1% platform commission**.
4. **Debt Blocking**: Delivery Agents carrying outstanding cash from "Cash on Delivery" orders are **blocked** from withdrawing online earnings until they settle their cash debt with the platform.

---

## 🛠 Key Features by Role

### 👨‍🍳 Kitchen Owners & Caterers
- **Menu Management**: Manage items, pricing, discount prices, nutritional macros (calories, protein, carbs), and dietary tags.
- **Subscription Plans**: Build and offer daily/weekly meal plans.
- **Advertisements**: Request homepage ad placements for better visibility.
- **Promo Codes**: Issue custom promo codes specifically for their kitchen.
- **KPI Dashboards**: Kitchen-specific analytics on revenue and top-selling items.

### 🛵 Delivery Agents
- **Smart Assignment**: Auto-assigned to nearby ready orders.
- **GPS Tracking**: Push location updates for live customer tracking.
- **OTP Verification**: Secure order handoff via 4-digit code.
- **Debt Management**: Settle collected cash via Stripe/Paymob.
- **Performance Bonuses**: Automated daily bonuses (e.g., 50 EGP for 11+ deliveries).

### 👥 Customers
- **Live Chat**: Chat directly with kitchens/caterers or talk to the **BiteBot AI** for automated support.
- **Wallet & Loyalty**: Top-up wallets and earn loyalty points redeemable as order discounts.
- **Order Tracking**: See real-time driver locations on a map.
- **Address Management**: Save multiple geocoded addresses (latitude/longitude).

### 👑 Admin & Owner Dashboard
- **KPI Dashboards**: Monitor platform revenue, active users, order volume, and day-over-day growth metrics.
- **User Verification**: Approve kitchen, caterer, and delivery agent registration documents.
- **Financial Oversight**: Manage withdrawals, refund requests, and platform commission.
- **BiteBot Inbox**: Intervene in customer support chats escalated by the AI bot.
- **Daily Automated Reports**: PDF KPI reports generated and emailed to admins daily.

---

## 🏗 Technical Architecture & Stack

BiteHub is built following the robust **MVC pattern** with dedicated **Service Layers** (`OrderSettlementService`, `PaymobService`, `DailySummaryService`) to decouple complex business logic from controllers.

- **Backend Framework**: Laravel 10.x (PHP 8.1+)
- **Frontend (Web)**: Blade Templates + Alpine.js + Tailwind CSS 3.x
- **Mobile Application**: Flutter 3.x (Dio, Provider, GoRouter)
- **Database**: MySQL 8.0 (Eloquent ORM)
- **Authentication**: Laravel Breeze (Web) + Sanctum (API Tokens) + 6-digit Email OTPs
- **Payments**: Stripe SDK + Paymob Accept API (with HMAC SHA-512 verification)
- **Task Scheduling**: Laravel Console Kernel
- **Security**: Bilingual Profanity Filters, XSS/CSRF prevention, Role-based middleware.

---

## 🗄 Database Overview

The system uses 76+ migrations. Key entities include:
- `users`, `user_addresses`, `user_phones`: Multi-role centralized user schema.
- `menu_items`, `categories`, `tags`, `item_images`: Comprehensive food cataloging.
- `orders`, `menu_order_items`: Order tracking with JSON tracking coordinates.
- `subscriptions`, `kitchen_plans`, `plan_menu_items`: Complex recurring meal plan engine.
- `payments`, `loyalty_transactions`, `withdrawal_requests`, `refund_requests`: Financial ledgers.
- `live_chats`, `support_tickets`, `error_reports`: Multi-channel communication.

---

## 🔌 API Overview

A full RESTful API powers the Flutter mobile app using **Laravel Sanctum**.

- `POST /api/login`, `POST /api/register`
- `GET /api/home`, `GET /api/browse`, `GET /api/kitchen/{id}`
- `GET /api/orders`, `POST /api/orders`
- `GET /api/subscriptions`, `POST /api/subscriptions/{id}/cancel`
- `POST /api/tickets` (Support Tickets)

*Plus multiple Internal AJAX endpoints for real-time chat, GPS polling, and dashboard fragments.*

---

## ⚙️ Installation & Setup

1. **Clone the repo & install dependencies**
   ```bash
   git clone https://github.com/your-username/bitehub.git
   cd bitehub
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Update `.env` with MySQL credentials, Stripe/Paymob API keys, and Gmail SMTP settings.*

3. **Database & Storage**
   ```bash
   php artisan migrate
   php artisan storage:link
   npm run build
   ```

4. **Run the Server**
   ```bash
   php artisan serve
   npm run dev
   ```

5. **Start Task Scheduler**
   ```bash
   # Required for Meal Plan dispatches and daily reports
   php artisan schedule:run
   ```

---

<p align="center">
  Built with ❤️ using Laravel, Tailwind CSS, Alpine.js, and Flutter
</p>
