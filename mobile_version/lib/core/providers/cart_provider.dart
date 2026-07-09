import 'package:flutter/material.dart';
import '../models/models.dart';

class CartProvider extends ChangeNotifier {
  final List<CartItem> _items = [];

  List<CartItem> get items => List.unmodifiable(_items);
  int get itemCount => _items.fold(0, (sum, i) => sum + i.quantity);
  double get total => _items.fold(0, (sum, i) => sum + i.subtotal);
  bool get isEmpty => _items.isEmpty;

  // Kitchen/caterer constraint — only one kitchen at a time
  int? _kitchenId;
  int? _catererId;
  int? get kitchenId => _kitchenId;

  void addItem(MenuItem item) {
    // If different kitchen, clear cart
    if (_kitchenId != null && item.kitchenId != null && _kitchenId != item.kitchenId) {
      _items.clear();
      _kitchenId = null;
      _catererId = null;
    }
    if (_catererId != null && item.catererId != null && _catererId != item.catererId) {
      _items.clear();
      _kitchenId = null;
      _catererId = null;
    }

    final existing = _items.where((ci) => ci.item.id == item.id).toList();
    if (existing.isNotEmpty) {
      existing.first.quantity++;
    } else {
      _items.add(CartItem(item: item));
      _kitchenId ??= item.kitchenId;
      _catererId ??= item.catererId;
    }
    notifyListeners();
  }

  void removeItem(int itemId) {
    _items.removeWhere((ci) => ci.item.id == itemId);
    if (_items.isEmpty) { _kitchenId = null; _catererId = null; }
    notifyListeners();
  }

  void decrementItem(int itemId) {
    final idx = _items.indexWhere((ci) => ci.item.id == itemId);
    if (idx != -1) {
      if (_items[idx].quantity > 1) {
        _items[idx].quantity--;
      } else {
        _items.removeAt(idx);
        if (_items.isEmpty) { _kitchenId = null; _catererId = null; }
      }
      notifyListeners();
    }
  }

  void clearCart() {
    _items.clear();
    _kitchenId = null;
    _catererId = null;
    notifyListeners();
  }

  Map<String, dynamic> toOrderPayload(String addressText, String paymentMethod, {String? note}) {
    return {
      'items': _items.map((ci) => ci.toJson()).toList(),
      'address': addressText,
      'payment_method': paymentMethod,
      'note': note ?? '',
      'kitchen_id': _kitchenId,
      'caterer_id': _catererId,
      'total': total,
    };
  }
}
