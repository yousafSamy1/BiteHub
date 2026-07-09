double _parseDouble(dynamic value) {
  if (value == null) return 0.0;
  if (value is double) return value;
  if (value is int) return value.toDouble();
  if (value is String) return double.tryParse(value) ?? 0.0;
  return 0.0;
}

int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}

bool _parseBool(dynamic value) {
  if (value == null) return false;
  if (value is bool) return value;
  if (value is int) return value > 0;
  if (value is String) return value == '1' || value.toLowerCase() == 'true';
  return false;
}

// ── Kitchen ──────────────────────────────────────────────────────────────
class Kitchen {
  final int id;
  final String name;
  final String? description;
  final String? image;
  final double rating;
  final int reviewsCount;
  final String status;
  final String? location;
  final String? openingTime;
  final String? closingTime;
  final bool isVerified;
  final bool isSponsored;
  final List<MenuItem> menu;

  Kitchen({
    required this.id,
    required this.name,
    this.description,
    this.image,
    required this.rating,
    required this.reviewsCount,
    required this.status,
    this.location,
    this.openingTime,
    this.closingTime,
    this.isVerified = false,
    this.isSponsored = false,
    this.menu = const [],
  });

  factory Kitchen.fromJson(Map<String, dynamic> j) => Kitchen(
    id: _parseInt(j['id']),
    name: j['name'] ?? '',
    description: j['description'],
    image: j['image'],
    rating: _parseDouble(j['rating']),
    reviewsCount: _parseInt(j['reviews_count']),
    status: j['status'] ?? 'Open',
    location: j['location'],
    openingTime: j['opening_time'],
    closingTime: j['closing_time'],
    isVerified: _parseBool(j['is_verified']),
    isSponsored: _parseBool(j['is_sponsored']),
    menu: (j['menu'] as List? ?? []).map((m) => MenuItem.fromJson(m)).toList(),
  );
}

// ── Caterer ──────────────────────────────────────────────────────────────
class Caterer {
  final int id;
  final String name;
  final String? description;
  final String? image;
  final double rating;
  final int reviewsCount;
  final bool isSponsored;

  Caterer({
    required this.id,
    required this.name,
    this.description,
    this.image,
    required this.rating,
    required this.reviewsCount,
    this.isSponsored = false,
  });

  factory Caterer.fromJson(Map<String, dynamic> j) => Caterer(
    id: _parseInt(j['id']),
    name: j['name'] ?? j['business_name'] ?? '',
    description: j['description'],
    image: j['image'],
    rating: _parseDouble(j['rating']),
    reviewsCount: _parseInt(j['reviews_count']),
    isSponsored: _parseBool(j['is_sponsored']),
  );
}

// ── MenuItem ─────────────────────────────────────────────────────────────
class MenuItem {
  final int id;
  final String name;
  final String? description;
  final double price;
  final double? discountPrice;
  final String? image;
  final String? category;
  final int? kitchenId;
  final int? catererId;

  MenuItem({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.discountPrice,
    this.image,
    this.category,
    this.kitchenId,
    this.catererId,
  });

  double get effectivePrice => discountPrice ?? price;

  factory MenuItem.fromJson(Map<String, dynamic> j) => MenuItem(
    id: _parseInt(j['id']),
    name: j['name'] ?? j['item_name'] ?? '',
    description: j['description'],
    price: _parseDouble(j['price'] ?? j['item_price']),
    discountPrice: j['discount_price'] != null ? _parseDouble(j['discount_price']) : null,
    image: j['image'],
    category: j['category'],
    kitchenId: j['kitchen_id'] != null ? _parseInt(j['kitchen_id']) : null,
    catererId: j['caterer_id'] != null ? _parseInt(j['caterer_id']) : null,
  );
}

// ── Category ─────────────────────────────────────────────────────────────
class Category {
  final int id;
  final String name;
  final String? image;

  Category({required this.id, required this.name, this.image});

  factory Category.fromJson(Map<String, dynamic> j) => Category(
    id: j['id'] ?? 0,
    name: j['name'] ?? '',
    image: j['image'],
  );
}

// ── CartItem ─────────────────────────────────────────────────────────────
class CartItem {
  final MenuItem item;
  int quantity;
  String? note;

  CartItem({required this.item, this.quantity = 1, this.note});

  double get subtotal => item.effectivePrice * quantity;

  Map<String, dynamic> toJson() => {
    'menu_item_id': item.id,
    'quantity': quantity,
    'note': note ?? '',
    'kitchen_id': item.kitchenId,
    'caterer_id': item.catererId,
  };
}

// ── Order ─────────────────────────────────────────────────────────────────
class Order {
  final int id;
  final String orderNumber;
  final String status;
  final double total;
  final String? createdAt;
  final List<OrderItem> items;
  final String? address;
  final String? paymentMethod;

  Order({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.total,
    this.createdAt,
    this.items = const [],
    this.address,
    this.paymentMethod,
  });

  factory Order.fromJson(Map<String, dynamic> j) => Order(
    id: j['id'] ?? 0,
    orderNumber: j['order_number'] ?? '#${j['id']}',
    status: j['status'] ?? 'Pending',
    total: _parseDouble(j['total']),
    createdAt: j['created_at'],
    items: (j['items'] as List? ?? []).map((i) => OrderItem.fromJson(i)).toList(),
    address: j['address'],
    paymentMethod: j['payment_method'],
  );
}

class OrderItem {
  final String name;
  final int quantity;
  final double price;
  final String? image;

  OrderItem({required this.name, required this.quantity, required this.price, this.image});

  factory OrderItem.fromJson(Map<String, dynamic> j) => OrderItem(
    name: j['name'] ?? '',
    quantity: _parseInt(j['quantity'] ?? 1),
    price: _parseDouble(j['price']),
    image: j['image'],
  );
}

// ── User ──────────────────────────────────────────────────────────────────
class AppUser {
  final int id;
  final String name;
  final String email;
  final String role;
  final String? image;
  final double walletBalance;
  final int loyaltyPoints;
  final int totalOrders;

  AppUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.image,
    this.walletBalance = 0,
    this.loyaltyPoints = 0,
    this.totalOrders = 0,
  });

  factory AppUser.fromJson(Map<String, dynamic> j) => AppUser(
    id: _parseInt(j['id']),
    name: j['name'] ?? '',
    email: j['email'] ?? '',
    role: j['role'] ?? 'Customer',
    image: j['image'],
    walletBalance: _parseDouble(j['wallet_balance']),
    loyaltyPoints: _parseInt(j['loyalty_points']),
    totalOrders: _parseInt(j['total_orders']),
  );
}

// ── Address ───────────────────────────────────────────────────────────────
class UserAddress {
  final int id;
  final String address;
  final bool isPrimary;
  final double? latitude;
  final double? longitude;

  UserAddress({
    required this.id,
    required this.address,
    this.isPrimary = false,
    this.latitude,
    this.longitude,
  });

  factory UserAddress.fromJson(Map<String, dynamic> j) => UserAddress(
    id: _parseInt(j['id']),
    address: j['address'] ?? '',
    isPrimary: _parseBool(j['is_primary']),
    latitude: j['latitude'] != null ? _parseDouble(j['latitude']) : null,
    longitude: j['longitude'] != null ? _parseDouble(j['longitude']) : null,
  );
}

// ── Subscription ─────────────────────────────────────────────────────────
class Subscription {
  final int id;
  final String planTime;
  final String status;
  final double price;
  final String? startDate;
  final String? endDate;
  final String? kitchenName;
  final String? planTitle;

  Subscription({
    required this.id,
    required this.planTime,
    required this.status,
    required this.price,
    this.startDate,
    this.endDate,
    this.kitchenName,
    this.planTitle,
  });

  factory Subscription.fromJson(Map<String, dynamic> j) => Subscription(
    id: j['id'] ?? 0,
    planTime: j['plan_time'] ?? 'Daily',
    status: j['status'] ?? 'Pending',
    price: _parseDouble(j['price']),
    startDate: j['start_date'],
    endDate: j['end_date'],
    kitchenName: j['kitchen_name'],
    planTitle: j['plan_title'],
  );
}

// ── SupportTicket ─────────────────────────────────────────────────────────
class SupportTicket {
  final int id;
  final String subject;
  final String status;
  final String? lastMessage;
  final String? createdAt;

  SupportTicket({
    required this.id,
    required this.subject,
    required this.status,
    this.lastMessage,
    this.createdAt,
  });

  factory SupportTicket.fromJson(Map<String, dynamic> j) => SupportTicket(
    id: j['id'] ?? 0,
    subject: j['subject'] ?? '',
    status: j['status'] ?? 'Open',
    lastMessage: j['last_message'],
    createdAt: j['created_at'],
  );
}
