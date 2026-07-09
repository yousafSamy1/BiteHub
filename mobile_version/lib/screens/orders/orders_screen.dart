import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/api/api_service.dart';
import '../../core/models/models.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/theme/app_theme.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});
  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  List<Order>? _orders;
  bool _loading = true;
  String? _error;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    final auth = context.read<AuthProvider>();
    if (!auth.isLoggedIn) {
      if (mounted) setState(() { _loading = false; });
      return;
    }
    try {
      final o = await ApiService.getMyOrders();
      if (mounted) setState(() { _orders = o; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    
    if (!auth.isLoggedIn) {
      return Scaffold(
        appBar: AppBar(title: const Text('My Orders')),
        body: Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          const Icon(Icons.lock_outline, size: 60, color: AppTheme.textMuted),
          const SizedBox(height: 16),
          const Text('Please login to view your orders', style: TextStyle(color: AppTheme.textSecondary)),
          const SizedBox(height: 24),
          ElevatedButton(onPressed: () => context.push('/login'), child: const Text('Login')),
        ])),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('My Orders')),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: AppTheme.danger)))
              : _orders == null || _orders!.isEmpty
                  ? Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                      const Text('📦', style: TextStyle(fontSize: 60)),
                      const SizedBox(height: 16),
                      const Text('No orders yet', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: AppTheme.textPrimary)),
                      const SizedBox(height: 8),
                      const Text('Time to order some delicious food!', style: TextStyle(color: AppTheme.textMuted)),
                      const SizedBox(height: 24),
                      ElevatedButton(onPressed: () => context.go('/browse'), child: const Text('Browse Kitchens')),
                    ]))
                  : RefreshIndicator(
                      onRefresh: _load,
                      color: AppTheme.primary,
                      child: ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _orders!.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 16),
                        itemBuilder: (_, i) {
                          final o = _orders![i];
                          return GestureDetector(
                            onTap: () => context.push('/orders/${o.id}'),
                            child: Container(
                              decoration: AppTheme.glassCard,
                              padding: const EdgeInsets.all(16),
                              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                                  Text(o.orderNumber, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: AppTheme.textPrimary)),
                                  _StatusBadge(status: o.status),
                                ]),
                                const SizedBox(height: 8),
                                Text(o.createdAt ?? '', style: const TextStyle(color: AppTheme.textMuted, fontSize: 12)),
                                const Divider(height: 24),
                                Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                                  Text('${o.items.length} items', style: const TextStyle(color: AppTheme.textSecondary)),
                                  Text('${o.total.toStringAsFixed(0)} EGP', style: const TextStyle(fontWeight: FontWeight.w800, color: AppTheme.primary, fontSize: 16)),
                                ]),
                              ]),
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status.toLowerCase()) {
      case 'delivered': color = AppTheme.success; break;
      case 'pending': color = AppTheme.warning; break;
      case 'cancelled': color = AppTheme.danger; break;
      default: color = AppTheme.primary; break;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.5)),
      ),
      child: Text(status, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w700)),
    );
  }
}
