import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/api/api_service.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/providers/cart_provider.dart';
import '../../core/theme/app_theme.dart';
import '../../widgets/gradient_button.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});
  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final _noteCtrl = TextEditingController();
  bool _isLoading = false;

  @override
  void dispose() { _noteCtrl.dispose(); super.dispose(); }

  Future<void> _checkout() async {
    final auth = context.read<AuthProvider>();
    if (!auth.isLoggedIn) {
      context.push('/login');
      return;
    }

    final cart = context.read<CartProvider>();
    if (cart.isEmpty) return;

    setState(() => _isLoading = true);
    try {
      final payload = cart.toOrderPayload('Main Address', 'COD', note: _noteCtrl.text);
      await ApiService.placeOrder(payload);
      cart.clearCart();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Order placed successfully!'), backgroundColor: AppTheme.success),
        );
        context.go('/orders');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to place order: $e'), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Cart'),
        leading: BackButton(onPressed: () {
          if (context.canPop()) context.pop();
          else context.go('/');
        }),
      ),
      body: cart.isEmpty
          ? Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
              const Text('🛒', style: TextStyle(fontSize: 60)),
              const SizedBox(height: 16),
              const Text('Your cart is empty', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: AppTheme.textPrimary)),
              const SizedBox(height: 8),
              const Text('Looks like you haven\'t added anything yet.', style: TextStyle(color: AppTheme.textMuted)),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => context.go('/browse'),
                child: const Text('Browse Kitchens'),
              ),
            ]))
          : Column(
              children: [
                Expanded(
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: cart.items.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (_, i) {
                      final ci = cart.items[i];
                      return Container(
                        padding: const EdgeInsets.all(12),
                        decoration: AppTheme.glassCard,
                        child: Row(children: [
                          Container(
                            width: 60, height: 60,
                            decoration: BoxDecoration(
                              color: AppTheme.bgCard2,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: ci.item.image != null
                                ? ClipRRect(borderRadius: BorderRadius.circular(10), child: Image.network(ci.item.image!, fit: BoxFit.cover))
                                : const Center(child: Text('🍽️')),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text(ci.item.name, style: const TextStyle(fontWeight: FontWeight.w700, color: AppTheme.textPrimary)),
                              const SizedBox(height: 4),
                              Text('${ci.item.effectivePrice.toStringAsFixed(0)} EGP', style: const TextStyle(color: AppTheme.primary, fontWeight: FontWeight.w600)),
                            ]),
                          ),
                          Row(children: [
                            IconButton(
                              icon: const Icon(Icons.remove_circle_outline, color: AppTheme.textMuted),
                              onPressed: () => cart.decrementItem(ci.item.id),
                            ),
                            Text('${ci.quantity}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                            IconButton(
                              icon: const Icon(Icons.add_circle_outline, color: AppTheme.primary),
                              onPressed: () => cart.addItem(ci.item),
                            ),
                          ]),
                        ]),
                      );
                    },
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: const BoxDecoration(
                    color: AppTheme.bgCard,
                    border: Border(top: BorderSide(color: AppTheme.border)),
                  ),
                  child: SafeArea(
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Text('Order Note (Optional)'),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _noteCtrl,
                        decoration: const InputDecoration(hintText: 'Any special requests?'),
                      ),
                      const SizedBox(height: 16),
                      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                        const Text('Total:', style: TextStyle(color: AppTheme.textSecondary, fontSize: 16)),
                        Text('${cart.total.toStringAsFixed(0)} EGP', style: const TextStyle(color: AppTheme.textPrimary, fontSize: 24, fontWeight: FontWeight.w800)),
                      ]),
                      const SizedBox(height: 16),
                      GradientButton(
                        label: 'Proceed to Checkout',
                        icon: Icons.check,
                        isLoading: _isLoading,
                        onPressed: _checkout,
                      ),
                    ]),
                  ),
                ),
              ],
            ),
    );
  }
}
