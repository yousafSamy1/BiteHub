import 'package:flutter/material.dart';
import '../../core/api/api_service.dart';
import '../../core/models/models.dart';
import '../../core/theme/app_theme.dart';

class OrderDetailScreen extends StatefulWidget {
  final int id;
  const OrderDetailScreen({super.key, required this.id});
  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  Order? _order;
  bool _loading = true;
  String? _error;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    try {
      final o = await ApiService.getOrderDetail(widget.id);
      if (mounted) setState(() { _order = o; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator(color: AppTheme.primary)));
    if (_error != null) return Scaffold(
      appBar: AppBar(),
      body: Center(child: Text(_error!, style: const TextStyle(color: AppTheme.danger))),
    );

    final o = _order!;
    return Scaffold(
      appBar: AppBar(title: Text('Order ${o.orderNumber}')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          // Header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: AppTheme.glassCard,
            child: Column(children: [
              Text(o.status, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppTheme.primary)),
              const SizedBox(height: 8),
              Text(o.createdAt ?? '', style: const TextStyle(color: AppTheme.textMuted)),
            ]),
          ),
          const SizedBox(height: 24),
          
          // Items
          const Text('Order Items', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppTheme.textPrimary)),
          const SizedBox(height: 12),
          Container(
            decoration: AppTheme.glassCard,
            child: ListView.separated(
              shrinkWrap: true, physics: const NeverScrollableScrollPhysics(),
              itemCount: o.items.length,
              separatorBuilder: (_, __) => const Divider(height: 1),
              itemBuilder: (_, i) {
                final item = o.items[i];
                return ListTile(
                  leading: const Text('🍽️', style: TextStyle(fontSize: 24)),
                  title: Text(item.name, style: const TextStyle(color: AppTheme.textPrimary, fontWeight: FontWeight.w600)),
                  subtitle: Text('Qty: ${item.quantity}', style: const TextStyle(color: AppTheme.textMuted)),
                  trailing: Text('${(item.price * item.quantity).toStringAsFixed(0)} EGP', style: const TextStyle(color: AppTheme.primary, fontWeight: FontWeight.w700)),
                );
              },
            ),
          ),
          const SizedBox(height: 24),

          // Total
          Container(
            padding: const EdgeInsets.all(20),
            decoration: AppTheme.glassCard,
            child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Total Paid', style: TextStyle(fontSize: 18, color: AppTheme.textSecondary)),
              Text('${o.total.toStringAsFixed(0)} EGP', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppTheme.primary)),
            ]),
          ),
          
          if (o.address != null) ...[
            const SizedBox(height: 24),
            const Text('Delivery Address', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppTheme.textPrimary)),
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: AppTheme.glassCard,
              child: Row(children: [
                const Icon(Icons.location_on, color: AppTheme.primary),
                const SizedBox(width: 12),
                Expanded(child: Text(o.address!, style: const TextStyle(color: AppTheme.textSecondary))),
              ]),
            ),
          ],
        ]),
      ),
    );
  }
}
