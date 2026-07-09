<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KitchenOwnerController;
use App\Http\Controllers\CatererController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\BiteBotController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ErrorReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| FRONTEND routes (customer-facing, from Bite)
| BACKEND routes (admin dashboard, existing)
|
*/

// ===== FRONTEND Routes =====
Route::get('/', [FrontendController::class, 'home'])->name('frontend.home');
Route::get('/browse', [FrontendController::class, 'browse'])->name('frontend.browse');
Route::get('/kitchen/{id}', [FrontendController::class, 'kitchen'])->name('frontend.kitchen');
Route::get('/caterer/{id}', [FrontendController::class, 'caterer'])->name('frontend.caterer');
Route::get('/menu', [FrontendController::class, 'menu'])->name('frontend.menu');
Route::get('/item/{id}', [FrontendController::class, 'item'])->name('frontend.item');
Route::get('/caterers', [FrontendController::class, 'caterers'])->name('frontend.caterers');
Route::get('/top-kitchens', [FrontendController::class, 'topKitchens'])->name('frontend.top');
Route::get('/meal-plan/builder', [FrontendController::class, 'mealPlanBuilder'])->name('frontend.meal_plan_builder');
Route::get('/api/kitchen/{id}/menu', [FrontendController::class, 'getKitchenMenu']);
Route::post('/meal-plan/builder/store', [FrontendController::class, 'storeMealPlanBuilder'])->name('frontend.meal_plan_builder.store');
Route::post('/meal-plan/pay', [FrontendController::class, 'paySubscriptionInstallment'])->name('frontend.subscription.pay');
Route::get('/plans', [FrontendController::class, 'subscriptions'])->name('frontend.subscriptions');

// Cart & Checkout (guest can view, auth required for checkout)
Route::get('/cart', [CartController::class, 'show'])->name('frontend.cart');
Route::post('/cart/checkout', [CartController::class, 'placeOrder'])->middleware('auth')->name('frontend.checkout');
Route::post('/cart/stripe', [CartController::class, 'stripeCheckout'])->middleware('auth')->name('frontend.stripe.checkout');
Route::get('/cart/stripe/success', [CartController::class, 'stripeSuccess'])->middleware('auth')->name('frontend.stripe.success');
Route::get('/cart/stripe/cancel', [CartController::class, 'stripeCancel'])->name('frontend.stripe.cancel');
Route::post('/cart/apply-promo', [CartController::class, 'applyPromoCode'])->middleware('auth')->name('frontend.cart.apply_promo');
Route::post('/cart/remove-promo', [CartController::class, 'removePromoCode'])->middleware('auth')->name('frontend.cart.remove_promo');
Route::get('/order-tracking/{id}', [FrontendController::class, 'orderTracking'])->middleware('auth')->name('frontend.tracking');
Route::get('/order-tracking/{id}/data', [FrontendController::class, 'orderTrackingData'])->middleware('auth')->name('frontend.tracking.data');
Route::post('/order-tracking/{id}/cancel', [FrontendController::class, 'cancelOrder'])->middleware('auth')->name('frontend.order.cancel');
Route::post('/order/rate/{id}', [FrontendController::class, 'rateOrder'])->middleware('auth')->name('frontend.order.rate');
Route::post('/order/{id}/reorder', [CartController::class, 'reorder'])->middleware('auth')->name('frontend.reorder');
Route::get('/goodbye', fn() => view('frontend.goodbye'))->name('frontend.goodbye');

// Catering & Subscriptions (frontend)
Route::get('/catering', [FrontendController::class, 'cateringForm'])->name('frontend.catering');
Route::post('/catering', [FrontendController::class, 'storeCatering'])->middleware('auth')->name('frontend.catering.store');
Route::get('/subscribe', [FrontendController::class, 'subscribe'])->name('frontend.subscribe');
Route::post('/subscribe', [FrontendController::class, 'storeSubscription'])->middleware('auth')->name('frontend.subscribe.store');
Route::post('/subscribe/stripe', [FrontendController::class, 'stripeSubscription'])->middleware('auth')->name('frontend.stripe.subscribe');
Route::post('/subscribe/request', [FrontendController::class, 'storeSubscriptionRequest'])->middleware('auth')->name('frontend.subscribe.request');
Route::get('/subscribe/stripe/success', [FrontendController::class, 'stripeSubscriptionSuccess'])->middleware('auth')->name('frontend.stripe.subscribe.success');
Route::get('/subscription/stripe/install-success', [FrontendController::class, 'stripeSubscriptionInstallmentSuccess'])->middleware('auth')->name('frontend.stripe.subscription.process.success');

// Subscription management (customer)
Route::middleware(['auth', 'role:Customer'])->group(function () {
    Route::post('/subscription/{id}/cancel', [FrontendController::class, 'cancelSubscription'])->name('frontend.subscription.cancel');
    Route::post('/subscription/{id}/pause', [FrontendController::class, 'pauseSubscription'])->name('frontend.subscription.pause');
    Route::post('/subscription/{id}/resume', [FrontendController::class, 'resumeSubscription'])->name('frontend.subscription.resume');
    Route::post('/subscription/{id}/renew', [FrontendController::class, 'renewSubscription'])->name('frontend.subscription.renew');
    Route::post('/subscription/{id}/delete-pending', [FrontendController::class, 'deletePendingSubscription'])->name('frontend.subscription.delete_pending');

    // Subscription Payment
    Route::get('/subscription/{id}/pay', [FrontendController::class, 'subscriptionPaymentPage'])->name('frontend.subscription.payment');
    Route::post('/subscription/{id}/pay/process', [FrontendController::class, 'processSubscriptionPayment'])->name('frontend.subscription.payment.process');
    Route::post('/subscription/{id}/pay/stripe', [FrontendController::class, 'stripeSubscriptionInstallmentWait'])->name('frontend.subscription.payment.stripe');

    // Subscriptions
    Route::get('/plans', [FrontendController::class, 'subscriptions'])->name('frontend.subscriptions');
    Route::post('/plans/subscribe', [FrontendController::class, 'subscribe'])->name('frontend.plans.subscribe');
    Route::post('/checkout/subscription', [FrontendController::class, 'storeSubscription'])->name('frontend.checkout.subscription');

    // Refund Requests
    Route::post('/refund/request', [FrontendController::class, 'requestRefund'])->name('frontend.refund.request');

    // Subscription Chat for Customer
    Route::get('/plans/{id}/chat', [ChatController::class, 'customerSubscriptionChat'])->name('frontend.subscriptions.chat');
    Route::post('/plans/{id}/chat', [ChatController::class, 'customerSendSubscriptionMessage'])->name('frontend.subscriptions.chat.send');

    // Customer Support Tickets
    Route::get('/support', [SupportController::class, 'customerIndex'])->name('customer.support');
    Route::post('/support', [SupportController::class, 'customerStore'])->name('customer.support.store');
    Route::get('/support/{id}', [SupportController::class, 'customerShow'])->name('customer.support.show');
});

// Customer Profile (frontend)
Route::get('/my-profile', [FrontendController::class, 'myProfile'])->middleware('auth')->name('frontend.profile');
Route::post('/my-profile', [FrontendController::class, 'updateProfile'])->middleware('auth')->name('frontend.profile.update');
Route::post('/my-profile/password', [FrontendController::class, 'updatePassword'])->middleware('auth')->name('frontend.profile.password');
Route::delete('/my-profile/delete', [FrontendController::class, 'deleteAccount'])->middleware('auth')->name('frontend.profile.delete');

// Chat with Kitchen/Chef (customer side)
Route::middleware('auth')->group(function () {
    Route::get('/order-tracking/{orderId}/chat', [FrontendController::class, 'chatWithOrder'])->name('frontend.chat.order');
    Route::post('/order-tracking/{orderId}/chat/send', [FrontendController::class, 'sendOrderChatMessage'])->name('frontend.chat.send');
    Route::get('/order-tracking/{orderId}/chat/messages', [FrontendController::class, 'getOrderChatMessages'])->name('frontend.chat.messages');

    // NEW: Pre-order customization routes
    Route::post('/chat/preorder/send', [ChatController::class, 'sendPreOrderRequest'])->name('frontend.chat.preorder.send');
    Route::get('/chat/preorder/{itemId}/messages', [ChatController::class, 'getPreOrderMessages'])->name('frontend.chat.preorder.messages');
    Route::post('/cart/customization/{id}/used', [CartController::class, 'markCustomizationUsed'])->name('frontend.cart.customization.used');
    Route::get('/api/cart/customization-count', [CartController::class, 'getCustomizationCount'])->name('frontend.cart.customization.count');
    Route::delete('/cart/customization/{id}', [CartController::class, 'deleteCustomizationRequest'])->name('frontend.cart.customization.delete');
    Route::get('/api/chat/active-sessions', [ChatController::class, 'getActiveSessions'])->name('frontend.chat.active_sessions');
});

// ===== BACKEND Routes (Admin Dashboard) — protected by auth + role:Admin,Owner =====
Route::middleware(['auth', 'role:Admin,Owner', 'audit'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::get('/kpi', [AdminController::class, 'AdminKPI'])->name('admin.kpi');

    // Owner-only Management Routes
    Route::middleware(['role:Owner'])->group(function() {
        Route::get('/admins', [AdminController::class, 'adminList'])->name('admin.admins.list');
        Route::post('/admins/store', [AdminController::class, 'storeAdmin'])->name('admin.admins.store');
        Route::post('/admins/{id}/toggle-role', [AdminController::class, 'toggleRole'])->name('admin.admins.toggle-role');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit.logs');
    });
    Route::get('/report/download', [AdminController::class, 'downloadDailySummary'])->name('admin.report.download');

    // Profile & Security
    Route::get('/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::post('/profile/store', [AdminController::class, 'store'])->name('admin.store');
    Route::get('/change-password', [AdminController::class, 'AdminchangePassword'])->name('admin.change.password');
    Route::post('/update-password', [AdminController::class, 'AdminUpdatePassword'])->name('admin.update.password');
    Route::get('/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::post('/users/{id}/suspend', [AdminController::class, 'suspendUser'])->name('admin.users.suspend');
    Route::post('/users/{id}/activate', [AdminController::class, 'activateUser'])->name('admin.users.activate');

    // Wallets
    Route::get('/wallets', [AdminController::class, 'wallets'])->name('admin.wallets');

    // Kitchens
    Route::get('/kitchens', [AdminController::class, 'kitchens'])->name('admin.kitchens');
    Route::post('/kitchens/{id}/verify', [AdminController::class, 'verifyKitchen'])->name('admin.kitchens.verify');
    Route::post('/kitchens/{id}/reject', [AdminController::class, 'rejectKitchen'])->name('admin.kitchens.reject');
    Route::post('/kitchens/{id}/suspend', [AdminController::class, 'suspendKitchen'])->name('admin.kitchens.suspend');
    Route::post('/kitchens/{id}/activate', [AdminController::class, 'activateKitchen'])->name('admin.kitchens.activate');

    // Caterers
    Route::get('/caterers', [AdminController::class, 'caterers'])->name('admin.caterers');
    Route::post('/caterers/{id}/toggle', [AdminController::class, 'toggleCaterer'])->name('admin.caterers.toggle');

    // Agents
    Route::get('/agents', [AdminController::class, 'agents'])->name('admin.agents');
    Route::post('/agents/store', [AdminController::class, 'storeAgent'])->name('admin.agents.store');
    Route::post('/agents/{id}/approve', [AdminController::class, 'approveAgent'])->name('admin.agents.approve');
    Route::post('/agents/{id}/status', [AdminController::class, 'updateAgentStatus'])->name('admin.agents.status');

    // Orders
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.status');
    Route::post('/orders/{id}/assign', [AdminController::class, 'assignAgent'])->name('admin.orders.assign');

    // Advertisements
    Route::get('/ads', [AdminController::class, 'ads'])->name('admin.ads');
    Route::post('/ads', [AdminController::class, 'storeAd'])->name('admin.ads.store');
    Route::post('/ads/{id}/toggle', [AdminController::class, 'toggleAd'])->name('admin.ads.toggle');
    Route::post('/ads/{id}/approve', [AdminController::class, 'approveAd'])->name('admin.ads.approve');
    Route::post('/ads/{id}/reject', [AdminController::class, 'rejectAd'])->name('admin.ads.reject');
    Route::delete('/ads/{id}', [AdminController::class, 'deleteAd'])->name('admin.ads.delete');

    // Chat (Admin now has write access)
    Route::get('/orders/{id}/chat', [ChatController::class, 'adminOrderChat'])->name('admin.orders.chat');
    Route::post('/orders/{id}/chat', [ChatController::class, 'adminSendMessage'])->name('admin.orders.chat.send');

    // Subscriptions
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('admin.subscriptions');
    Route::post('/subscriptions/{id}/cancel', [AdminController::class, 'cancelSubscription'])->name('admin.subscriptions.cancel');

    // Loyalty Points
    Route::get('/loyalty', [AdminController::class, 'loyalty'])->name('admin.loyalty');
    Route::post('/loyalty/add', [AdminController::class, 'addLoyaltyPoints'])->name('admin.loyalty.add');

    // Catering Requests
    Route::get('/catering', [AdminController::class, 'catering'])->name('admin.catering');
    Route::post('/catering/{id}/status', [AdminController::class, 'updateCateringStatus'])->name('admin.catering.status');

    // Categories
    Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::post('/categories/{id}/update', [AdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])->name('admin.categories.delete');

    // Reports & Support Tickets
    Route::get('/reports', [SupportController::class, 'adminIndex'])->name('admin.reports');
    Route::get('/reports/{id}', [SupportController::class, 'adminShow'])->name('admin.reports.show');
    Route::post('/reports/{id}/reply', [SupportController::class, 'adminReply'])->name('admin.reports.reply');
    Route::post('/reports/{id}/status', [SupportController::class, 'adminUpdateStatus'])->name('admin.reports.status');
    Route::post('/reports/{id}/process-refund', [SupportController::class, 'adminProcessRefund'])->name('admin.reports.process_refund');

    // Refund Management
    Route::get('/refunds', [AdminController::class, 'refundRequests'])->name('admin.refunds');
    Route::post('/refunds/{id}/approve', [AdminController::class, 'approveRefund'])->name('admin.refund.approve');
    Route::post('/refunds/{id}/reject', [AdminController::class, 'rejectRefund'])->name('admin.refund.reject');

    // Promo Codes Management
    Route::get('/promo-codes', [AdminController::class, 'promoCodes'])->name('admin.promo_codes');
    Route::post('/promo-codes', [AdminController::class, 'storePromoCode'])->name('admin.promo_codes.store');
    Route::post('/promo-codes/{id}/update', [AdminController::class, 'updatePromoCode'])->name('admin.promo_codes.update');
    Route::post('/promo-codes/{id}/toggle', [AdminController::class, 'togglePromoCode'])->name('admin.promo_codes.toggle');
    Route::delete('/promo-codes/{id}', [AdminController::class, 'deletePromoCode'])->name('admin.promo_codes.delete');
    Route::post('/promo-codes/{id}/announce', [AdminController::class, 'announcePromoCode'])->name('admin.promo_codes.announce');
});


// ===== Kitchen Owner Panel — protected by auth + role:KitchenOwner,Admin =====
Route::middleware(['auth', 'role:KitchenOwner,Admin,Owner'])->prefix('admin/kitchen')->group(function () {
    Route::get('/dashboard', [KitchenOwnerController::class, 'KitchenDashboard'])->name('kitchen.dashboard');
    Route::get('/kpi', [KitchenOwnerController::class, 'KitchenKPI'])->name('kitchen.kpi');
    Route::get('/profile', [KitchenOwnerController::class, 'KitchenProfile'])->name('kitchen.profile');
    Route::post('/profile/store', [KitchenOwnerController::class, 'store'])->name('kitchen.store');
    Route::post('/profile/delete', [KitchenOwnerController::class, 'deleteAccount'])->name('kitchen.profile.delete');
    Route::get('/change-password', [KitchenOwnerController::class, 'KitchenChangePassword'])->name('kitchen.change.password');
    Route::post('/update-password', [KitchenOwnerController::class, 'KitchenUpdatePassword'])->name('kitchen.update.password');
    Route::get('/logout', [KitchenOwnerController::class, 'KitchenLogout'])->name('kitchen.logout');

    // Wallet Topup
    Route::post('/wallet/topup', [KitchenOwnerController::class, 'stripeWalletTopupWait'])->name('kitchen.wallet.topup');
    Route::get('/wallet/topup/success', [KitchenOwnerController::class, 'stripeWalletTopupSuccess'])->name('kitchen.wallet.topup.success');


    // Menu CRUD
    Route::get('/menu', [KitchenOwnerController::class, 'menuItems'])->name('kitchen.menu');
    Route::post('/menu', [KitchenOwnerController::class, 'storeItem'])->name('kitchen.menu.store');
    Route::post('/menu/{id}/update', [KitchenOwnerController::class, 'updateItem'])->name('kitchen.menu.update');
    Route::post('/menu/{id}/toggle', [KitchenOwnerController::class, 'toggleItem'])->name('kitchen.menu.toggle');
    Route::delete('/menu/{id}', [KitchenOwnerController::class, 'deleteItem'])->name('kitchen.menu.delete');

    // Orders
    Route::get('/orders', [KitchenOwnerController::class, 'kitchenOrders'])->name('kitchen.orders');
    Route::post('/orders/{id}/status', [KitchenOwnerController::class, 'updateKitchenOrderStatus'])->name('kitchen.orders.status');

    // Kitchen Subscriptions
    Route::get('/subscriptions', [KitchenOwnerController::class, 'subscriptions'])->name('kitchen.subscriptions');
    Route::get('/subscription-requests', [KitchenOwnerController::class, 'subscriptionRequests'])->name('kitchen.subscriptions.requests');
    Route::post('/subscription-requests/{id}/approve', [KitchenOwnerController::class, 'approveSubscriptionRequest'])->name('kitchen.subscriptions.approve');
    Route::post('/subscription-requests/{id}/reject', [KitchenOwnerController::class, 'rejectSubscriptionRequest'])->name('kitchen.subscriptions.reject');
    Route::post('/subscriptions/item/{sub_id}/{item_id}', [KitchenOwnerController::class, 'updateSubscriptionItem'])->name('kitchen.subscriptions.update_item');

    // Kitchen Plans (NEW)
    Route::get('/plans', [KitchenOwnerController::class, 'plans'])->name('kitchen.plans');
    Route::get('/plans/create', [KitchenOwnerController::class, 'createPlan'])->name('kitchen.plans.create');
    Route::post('/plans/store', [KitchenOwnerController::class, 'storePlan'])->name('kitchen.plans.store');
    Route::get('/plans/edit/{id}', [KitchenOwnerController::class, 'editPlan'])->name('kitchen.plans.edit');
    Route::post('/plans/update/{id}', [KitchenOwnerController::class, 'updatePlan'])->name('kitchen.plans.update');
    Route::post('/plans/delete/{id}', [KitchenOwnerController::class, 'deletePlan'])->name('kitchen.plans.delete');
    Route::get('/plans/{id}/subscribers', [KitchenOwnerController::class, 'planSubscribers'])->name('kitchen.plans.subscribers');
    Route::post('/subscriptions/{id}/cancel', [KitchenOwnerController::class, 'cancelSubscriptionByOwner'])->name('kitchen.subscriptions.cancel');

    // Advertisements
    Route::get('/ads', [KitchenOwnerController::class, 'ads'])->name('kitchen.ads');
    Route::post('/ads', [KitchenOwnerController::class, 'storeAd'])->name('kitchen.ads.store');

    // Promo Codes
    Route::get('/promo-codes', [KitchenOwnerController::class, 'promoCodes'])->name('kitchen.promo_codes');
    Route::post('/promo-codes', [KitchenOwnerController::class, 'storePromoCode'])->name('kitchen.promo_codes.store');
    Route::post('/promo-codes/{id}/update', [KitchenOwnerController::class, 'updatePromoCode'])->name('kitchen.promo_codes.update');
    Route::post('/promo-codes/{id}/toggle', [KitchenOwnerController::class, 'togglePromoCode'])->name('kitchen.promo_codes.toggle');
    Route::delete('/promo-codes/{id}', [KitchenOwnerController::class, 'deletePromoCode'])->name('kitchen.promo_codes.delete');
    Route::post('/promo-codes/{id}/announce', [KitchenOwnerController::class, 'announcePromoCode'])->name('kitchen.promo_codes.announce');

    // Chat
    Route::get('/orders/{id}/chat', [ChatController::class, 'kitchenOrderChat'])->name('kitchen.orders.chat');
    Route::post('/orders/{id}/chat', [ChatController::class, 'kitchenSendMessage'])->name('kitchen.orders.chat.send');
    Route::post('/chat/{chatId}/approve', [ChatController::class, 'kitchenApproveRequest'])->name('kitchen.chat.approve');
    Route::post('/chat/{chatId}/reject', [ChatController::class, 'kitchenRejectRequest'])->name('kitchen.chat.reject');

    // Pre-order requests (new)
    Route::get('/customization-requests', [ChatController::class, 'kitchenPreOrderRequests'])->name('kitchen.customization.requests');
    Route::get('/preorder/chat/{menuItemId}/{customerId}', [ChatController::class, 'kitchenPreOrderChat'])->name('kitchen.preorder.chat');
    Route::post('/preorder/chat/{menuItemId}/{customerId}', [ChatController::class, 'kitchenSendPreOrderReply'])->name('kitchen.preorder.chat.send');

    // Subscription requests chat
    Route::get('/subscriptions/{id}/chat', [ChatController::class, 'kitchenSubscriptionChat'])->name('kitchen.subscriptions.chat');
    Route::post('/subscriptions/{id}/chat', [ChatController::class, 'kitchenSendSubscriptionReply'])->name('kitchen.subscriptions.chat.send');

    // Categories
    Route::get('/categories', [KitchenOwnerController::class, 'categories'])->name('kitchen.categories');
    Route::post('/categories', [KitchenOwnerController::class, 'storeCategory'])->name('kitchen.categories.store');
    Route::post('/categories/{id}/update', [KitchenOwnerController::class, 'updateCategory'])->name('kitchen.categories.update');
    Route::delete('/categories/{id}', [KitchenOwnerController::class, 'deleteCategory'])->name('kitchen.categories.delete');
    // Plan Requests Form toggle
    Route::post('/dashboard/toggle-plan-requests', [KitchenOwnerController::class, 'togglePlanRequests'])->name('kitchen.toggle_plan_requests');
    Route::post('/dashboard/update-hours', [KitchenOwnerController::class, 'updateHours'])->name('kitchen.update_hours');

    // Kitchen Support Tickets
    Route::get('/support', [SupportController::class, 'kitchenIndex'])->name('kitchen.support');
    Route::post('/support', [SupportController::class, 'kitchenStore'])->name('kitchen.support.store');

    // Refund Tracking
    Route::get('/refunds', [KitchenOwnerController::class, 'refunds'])->name('kitchen.refunds');
});

// ===== Caterer Panel — protected by auth + role:Caterer =====
Route::middleware(['auth', 'role:Caterer'])->prefix('admin/caterer')->group(function () {
    Route::get('/dashboard', [CatererController::class, 'CatererDashboard'])->name('caterer.dashboard');
    Route::get('/kpi', [CatererController::class, 'CatererKPI'])->name('caterer.kpi');
    Route::get('/profile', [CatererController::class, 'CatererProfile'])->name('caterer.profile');
    Route::post('/profile/store', [CatererController::class, 'store'])->name('caterer.store');
    Route::post('/profile/delete', [CatererController::class, 'deleteAccount'])->name('caterer.profile.delete');
    Route::get('/change-password', [CatererController::class, 'CatererChangePassword'])->name('caterer.change.password');
    Route::post('/update-password', [CatererController::class, 'CatererUpdatePassword'])->name('caterer.update.password');
    Route::get('/logout', [CatererController::class, 'CatererLogout'])->name('caterer.logout');

    // Catering Requests
    Route::get('/requests', [CatererController::class, 'myRequests'])->name('caterer.requests');
    Route::post('/requests/{id}/update', [CatererController::class, 'updateRequest'])->name('caterer.requests.update');

    // Menu Items
    Route::get('/menu', [CatererController::class, 'menuItems'])->name('caterer.menu');
    Route::post('/menu', [CatererController::class, 'storeItem'])->name('caterer.menu.store');
    Route::post('/menu/{id}/update', [CatererController::class, 'updateItem'])->name('caterer.menu.update');
    Route::post('/menu/{id}/toggle', [CatererController::class, 'toggleItem'])->name('caterer.menu.toggle');
    Route::delete('/menu/{id}', [CatererController::class, 'deleteItem'])->name('caterer.menu.delete');

    // Orders (Incoming from custom requests)
    Route::get('/orders', [CatererController::class, 'catererOrders'])->name('caterer.orders');
    Route::post('/orders/{id}/status', [CatererController::class, 'updateCatererOrderStatus'])->name('caterer.orders.status');

    // Advertisements
    Route::get('/ads', [CatererController::class, 'ads'])->name('caterer.ads');
    Route::post('/ads', [CatererController::class, 'storeAd'])->name('caterer.ads.store');

    // Promo Codes
    Route::get('/promo-codes', [CatererController::class, 'promoCodes'])->name('caterer.promo_codes');
    Route::post('/promo-codes', [CatererController::class, 'storePromoCode'])->name('caterer.promo_codes.store');
    Route::post('/promo-codes/{id}/update', [CatererController::class, 'updatePromoCode'])->name('caterer.promo_codes.update');
    Route::post('/promo-codes/{id}/toggle', [CatererController::class, 'togglePromoCode'])->name('caterer.promo_codes.toggle');
    Route::delete('/promo-codes/{id}', [CatererController::class, 'deletePromoCode'])->name('caterer.promo_codes.delete');
    Route::post('/promo-codes/{id}/announce', [CatererController::class, 'announcePromoCode'])->name('caterer.promo_codes.announce');

    // Chat
    Route::get('/orders/{id}/chat', [ChatController::class, 'catererOrderChat'])->name('caterer.orders.chat');
    Route::post('/orders/{id}/chat', [ChatController::class, 'catererSendMessage'])->name('caterer.orders.chat.send');
    Route::post('/chat/{chatId}/approve', [ChatController::class, 'catererApproveRequest'])->name('caterer.chat.approve');
    Route::post('/chat/{chatId}/reject', [ChatController::class, 'catererRejectRequest'])->name('caterer.chat.reject');

    // Pre-order requests (new)
    Route::get('/customization-requests', [ChatController::class, 'catererPreOrderRequests'])->name('caterer.customization.requests');
    Route::get('/preorder/chat/{menuItemId}/{customerId}', [ChatController::class, 'catererPreOrderChat'])->name('caterer.preorder.chat');
    Route::post('/preorder/chat/{menuItemId}/{customerId}', [ChatController::class, 'catererSendPreOrderReply'])->name('caterer.preorder.chat.send');

    // Categories
    Route::get('/categories', [CatererController::class, 'categories'])->name('caterer.categories');
    Route::post('/categories', [CatererController::class, 'storeCategory'])->name('caterer.categories.store');
    Route::post('/categories/{id}/update', [CatererController::class, 'updateCategory'])->name('caterer.categories.update');
    Route::delete('/categories/{id}', [CatererController::class, 'deleteCategory'])->name('caterer.categories.delete');

    // Caterer Support Tickets
    Route::get('/support', [SupportController::class, 'catererIndex'])->name('caterer.support');
    Route::post('/support', [SupportController::class, 'catererStore'])->name('caterer.support.store');

    Route::get('/refunds', [CatererController::class, 'refunds'])->name('caterer.refunds');
    Route::post('/dashboard/update-hours', [CatererController::class, 'updateHours'])->name('caterer.update_hours');
});

// ===== Delivery Agent Panel — protected by auth + role:DeliveryAgent =====
Route::middleware(['auth', 'role:DeliveryAgent'])->prefix('admin/agent')->group(function () {
    // Verification Gateway
    Route::get('/verify', [AgentController::class, 'showVerificationForm'])->name('agent.verify.page');
    Route::post('/verify', [AgentController::class, 'processVerification'])->name('agent.verify.process');

    Route::get('/dashboard', [AgentController::class, 'AgentDashboard'])->name('agent.dashboard');
    Route::post('/status', [AgentController::class, 'updateStatus'])->name('agent.update.status');
    Route::post('/location', [AgentController::class, 'updateServiceLocation'])->name('agent.update.location');
    Route::get('/profile', [AgentController::class, 'AgentProfile'])->name('agent.profile');
    Route::post('/profile/store', [AgentController::class, 'store'])->name('agent.store');
    Route::get('/change-password', [AgentController::class, 'AgentChangePassword'])->name('agent.change.password');
    Route::post('/update-password', [AgentController::class, 'AgentUpdatePassword'])->name('agent.update.password');
    Route::get('/logout', [AgentController::class, 'AgentLogout'])->name('agent.logout');

    // Deliveries
    Route::get('/deliveries', [AgentController::class, 'myDeliveries'])->name('agent.deliveries');
    Route::get('/deliveries/{id}/details', [AgentController::class, 'showDeliveryDetails'])->name('agent.delivery.details');
    Route::post('/deliveries/{id}/update', [AgentController::class, 'updateDeliveryStatus'])->name('agent.deliveries.update');
    Route::post('/deliveries/{id}/location', [AgentController::class, 'updateLocation'])->name('agent.deliveries.location');
    Route::post('/settle-debt', [AgentController::class, 'settleDebtWithWallet'])->name('agent.settle_debt');
    Route::post('/settle-debt/paymob', [AgentController::class, 'paymobDebtSettleWait'])->name('agent.paymob.settle');
    Route::get('/settle-debt/paymob/success', [AgentController::class, 'paymobDebtSettleSuccess'])->name('agent.paymob.success');
});

// ===== Breeze Profile Routes =====
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $dashRoutes = [
            'Admin' => '/admin/dashboard',
            'KitchenOwner' => '/admin/kitchen/dashboard',
            'Caterer' => '/admin/caterer/dashboard',
            'DeliveryAgent' => '/admin/agent/dashboard',
            'Customer' => '/dashboard/customer',
        ];
        return redirect($dashRoutes[$user->Role] ?? '/');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customer dashboard
    Route::get('/dashboard/customer', [FrontendController::class, 'customerDashboard'])
        ->middleware('role:Customer')->name('dashboard.customer');

    // Customer Addresses
    Route::get('/dashboard/addresses', [FrontendController::class, 'myAddresses'])
        ->middleware('role:Customer')->name('frontend.addresses');
    Route::post('/dashboard/addresses', [FrontendController::class, 'storeAddress'])
        ->middleware('role:Customer')->name('frontend.addresses.store');
    Route::post('/dashboard/addresses/{id}/primary', [FrontendController::class, 'setPrimaryAddress'])
        ->middleware('role:Customer')->name('frontend.addresses.primary');
    Route::delete('/dashboard/addresses/{id}', [FrontendController::class, 'deleteAddress'])
        ->middleware('role:Customer')->name('frontend.addresses.delete');
});

// Breeze Auth Routes (login, register, logout, password reset, etc.)
require __DIR__ . '/auth.php';

// Notification Routes
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/clear', [NotificationController::class, 'clearAll'])->name('notifications.clear');
    Route::get('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/latest', [NotificationController::class, 'getLatestNotifications'])->name('notifications.latest');
});

// BiteBot Support Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/bitebot/send', [BiteBotController::class, 'sendMessage'])->name('bitebot.send');
    Route::get('/bitebot/history', [BiteBotController::class, 'fetchHistory'])->name('bitebot.history');
    Route::get('/bitebot/unread', [BiteBotController::class, 'checkUnread'])->name('bitebot.unread');
});

// Admin Support Inquiry Routes
Route::middleware(['auth', 'role:Admin,Owner'])->group(function () {
    Route::get('/admin/inquiries', [BiteBotController::class, 'adminInquiries'])->name('admin.inquiries');
    Route::get('/admin/inquiries/chat/{id}', [BiteBotController::class, 'adminChat'])->name('admin.inquiry.chat');
    Route::post('/admin/inquiries/reply/{id}', [BiteBotController::class, 'adminReply'])->name('admin.inquiry.reply');
});

// PayMob Routes
Route::get('/paymob/callback', [App\Http\Controllers\PaymobController::class, 'callback'])->name('paymob.callback');
Route::post('/paymob/webhook', [App\Http\Controllers\PaymobController::class, 'processed'])->name('paymob.webhook');
Route::post('/order/paymob/checkout', [App\Http\Controllers\CartController::class, 'paymobCheckout'])->name('frontend.paymob.checkout');
Route::post('/subscription/paymob/checkout/{id}', [App\Http\Controllers\FrontendController::class, 'paymobSubscriptionInstallmentWait'])->name('frontend.paymob.subscription_checkout');
Route::post('/wallet/paymob/topup', [App\Http\Controllers\KitchenOwnerController::class, 'paymobWalletTopupWait'])->name('wallet.paymob.topup');

// PayMob Processors (Success handlers)
Route::get('/paymob/process/order', [App\Http\Controllers\CartController::class, 'paymobSuccess'])->name('paymob.order.process');
Route::get('/paymob/process/subscription', [App\Http\Controllers\FrontendController::class, 'paymobSubscriptionSuccess'])->name('paymob.subscription.process');
Route::get('/paymob/process/topup', [App\Http\Controllers\KitchenOwnerController::class, 'paymobTopupSuccess'])->name('paymob.topup.process');
// Withdrawal Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/withdraw/request', [App\Http\Controllers\WithdrawalController::class, 'store'])->name('withdraw.store');
    Route::get('/withdraw/methods', [App\Http\Controllers\WithdrawalController::class, 'index'])->name('withdraw.methods.index');
    Route::post('/withdraw/methods/store', [App\Http\Controllers\WithdrawalController::class, 'storeMethod'])->name('withdraw.methods.store');
    Route::delete('/withdraw/methods/delete/{id}', [App\Http\Controllers\WithdrawalController::class, 'deleteMethod'])->name('withdraw.methods.delete');
    Route::get('/api/withdraw/methods', [App\Http\Controllers\WithdrawalController::class, 'getMethods'])->name('api.withdraw.methods');
});

// Admin Withdrawal Management
Route::middleware(['auth', 'role:Admin,Owner'])->group(function () {
    Route::get('/admin/withdrawals', [App\Http\Controllers\WithdrawalController::class, 'adminIndex'])->name('admin.withdrawals.index');
    Route::post('/admin/withdrawals/update/{id}', [App\Http\Controllers\WithdrawalController::class, 'adminUpdate'])->name('admin.withdrawals.update');

    // Error Reports Management
    Route::get('/admin/error-reports', [App\Http\Controllers\AdminController::class, 'errorReports'])->name('admin.error-reports');
    Route::post('/admin/error-reports/update/{id}', [App\Http\Controllers\AdminController::class, 'updateErrorReport'])->name('admin.error-reports.update');
});

// Error Reporting Route
Route::post('/report-error', [ErrorReportController::class, 'store'])->name('error.report');

// Real-time Updates
Route::get('/realtime/stats', [AdminController::class, 'getRealtimeStats'])->middleware('auth')->name('admin.realtime.stats');
Route::get('/admin/orders/table-fragment', [AdminController::class, 'ordersTableFragment'])->middleware(['auth', 'role:Admin,Owner'])->name('admin.orders.fragment');
