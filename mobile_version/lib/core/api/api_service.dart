import 'package:dio/dio.dart';
import '../api/api_client.dart';
import '../models/models.dart';

class ApiService {
  static Dio get _dio => ApiClient.instance;

  // ── Auth ────────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final res = await _dio.post('/login', data: {'email': email, 'password': password});
    return res.data;
  }

  static Future<Map<String, dynamic>> register(String name, String email, String phone, String password) async {
    final res = await _dio.post('/register', data: {
      'name': name, 'email': email, 'phone': phone,
      'password': password, 'password_confirmation': password,
    });
    return res.data;
  }

  static Future<void> logout() async {
    try { await _dio.post('/logout'); } catch (_) {}
  }

  // ── Home ────────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getHomeData() async {
    final res = await _dio.get('/home');
    return res.data;
  }

  // ── Browse ──────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> getBrowseData({String? search}) async {
    final res = await _dio.get('/browse', queryParameters: {'search': search});
    return res.data;
  }

  // ── Kitchen ─────────────────────────────────────────────────────────────
  static Future<Kitchen> getKitchen(int id) async {
    final res = await _dio.get('/kitchen/$id');
    return Kitchen.fromJson(res.data);
  }

  // ── Caterer ─────────────────────────────────────────────────────────────
  static Future<Caterer> getCaterer(int id) async {
    final res = await _dio.get('/caterer/$id');
    return Caterer.fromJson(res.data);
  }

  // ── Menu ────────────────────────────────────────────────────────────────
  static Future<List<MenuItem>> getMenu({int? categoryId, int? kitchenId, String? search}) async {
    final res = await _dio.get('/menu', queryParameters: {
      'category_id': categoryId, 'kitchen_id': kitchenId, 'search': search,
    });
    return (res.data as List).map((m) => MenuItem.fromJson(m)).toList();
  }

  static Future<List<Category>> getCategories() async {
    final res = await _dio.get('/categories');
    return (res.data as List).map((c) => Category.fromJson(c)).toList();
  }

  // ── Orders ──────────────────────────────────────────────────────────────
  static Future<List<Order>> getMyOrders() async {
    final res = await _dio.get('/orders');
    return (res.data as List).map((o) => Order.fromJson(o)).toList();
  }

  static Future<Order> getOrderDetail(int id) async {
    final res = await _dio.get('/orders/$id');
    return Order.fromJson(res.data);
  }

  static Future<Map<String, dynamic>> placeOrder(Map<String, dynamic> data) async {
    final res = await _dio.post('/orders', data: data);
    return res.data;
  }

  // ── Profile ─────────────────────────────────────────────────────────────
  static Future<AppUser> getProfile() async {
    final res = await _dio.get('/profile');
    return AppUser.fromJson(res.data);
  }

  static Future<void> updateProfile(Map<String, dynamic> data) async {
    await _dio.post('/profile/update', data: data);
  }

  static Future<void> changePassword(String current, String newPass) async {
    await _dio.post('/profile/password', data: {
      'current_password': current, 'password': newPass, 'password_confirmation': newPass,
    });
  }

  // ── Addresses ───────────────────────────────────────────────────────────
  static Future<List<UserAddress>> getAddresses() async {
    final res = await _dio.get('/addresses');
    return (res.data as List).map((a) => UserAddress.fromJson(a)).toList();
  }

  static Future<void> saveAddress(String address, double lat, double lng) async {
    await _dio.post('/addresses', data: {'address': address, 'latitude': lat, 'longitude': lng});
  }

  static Future<void> deleteAddress(int id) async {
    await _dio.delete('/addresses/$id');
  }

  // ── Subscriptions ────────────────────────────────────────────────────────
  static Future<List<dynamic>> getSubscriptionPlans() async {
    final res = await _dio.get('/subscriptions');
    return res.data as List;
  }

  static Future<List<Subscription>> getMySubscriptions() async {
    final res = await _dio.get('/my-subscriptions');
    return (res.data as List).map((s) => Subscription.fromJson(s)).toList();
  }

  static Future<void> cancelSubscription(int id) async {
    await _dio.post('/subscriptions/$id/cancel');
  }

  // ── Support ─────────────────────────────────────────────────────────────
  static Future<List<SupportTicket>> getTickets() async {
    final res = await _dio.get('/support/tickets');
    return (res.data as List).map((t) => SupportTicket.fromJson(t)).toList();
  }

  static Future<void> createTicket(String subject, String message) async {
    await _dio.post('/support/tickets', data: {'subject': subject, 'message': message});
  }
}
