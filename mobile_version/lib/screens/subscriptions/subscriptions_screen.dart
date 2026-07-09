import 'package:flutter/material.dart';
import '../../core/api/api_service.dart';
import '../../core/models/models.dart';
import '../../core/theme/app_theme.dart';

class SubscriptionsScreen extends StatefulWidget {
  const SubscriptionsScreen({super.key});
  @override
  State<SubscriptionsScreen> createState() => _SubscriptionsScreenState();
}

class _SubscriptionsScreenState extends State<SubscriptionsScreen> {
  List<Subscription>? _subs;
  bool _loading = true;
  String? _error;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    try {
      final s = await ApiService.getMySubscriptions();
      if (mounted) setState(() { _subs = s; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator(color: AppTheme.primary)));
    if (_error != null) return Scaffold(appBar: AppBar(), body: Center(child: Text(_error!, style: const TextStyle(color: AppTheme.danger))));

    return Scaffold(
      appBar: AppBar(title: const Text('My Meal Plans')),
      body: _subs!.isEmpty
          ? const Center(child: Text('No active meal plans. Visit a kitchen to subscribe!', style: TextStyle(color: AppTheme.textMuted)))
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: _subs!.length,
              separatorBuilder: (_, __) => const SizedBox(height: 16),
              itemBuilder: (_, i) {
                final s = _subs![i];
                return Container(
                  padding: const EdgeInsets.all(20),
                  decoration: AppTheme.glassCard,
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                      Text(s.planTitle ?? '${s.planTime} Plan', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.textPrimary)),
                      _StatusBadge(status: s.status),
                    ]),
                    const SizedBox(height: 8),
                    if (s.kitchenName != null) Text('Kitchen: ${s.kitchenName}', style: const TextStyle(color: AppTheme.textSecondary)),
                    const SizedBox(height: 4),
                    Text('${s.startDate ?? ''} to ${s.endDate ?? ''}', style: const TextStyle(color: AppTheme.textMuted, fontSize: 12)),
                    const SizedBox(height: 16),
                    Text('${s.price.toStringAsFixed(0)} EGP', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppTheme.primary)),
                  ]),
                );
              },
            ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color color = status == 'Active' ? AppTheme.success : AppTheme.warning;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: color.withOpacity(0.15), borderRadius: BorderRadius.circular(20)),
      child: Text(status, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w700)),
    );
  }
}
