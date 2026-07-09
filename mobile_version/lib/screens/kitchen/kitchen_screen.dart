import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/api/api_service.dart';
import '../../core/models/models.dart';
import '../../core/providers/cart_provider.dart';
import '../../core/theme/app_theme.dart';
import '../../widgets/cards.dart';

class KitchenScreen extends StatefulWidget {
  final int id;
  const KitchenScreen({super.key, required this.id});
  @override
  State<KitchenScreen> createState() => _KitchenScreenState();
}

class _KitchenScreenState extends State<KitchenScreen> {
  Kitchen? _kitchen;
  bool _loading = true;
  String? _error;
  String? _selectedCategory;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    try {
      final k = await ApiService.getKitchen(widget.id);
      if (mounted) setState(() { _kitchen = k; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  List<MenuItem> get _filteredMenu {
    if (_selectedCategory == null || _kitchen == null) return _kitchen?.menu ?? [];
    return _kitchen!.menu.where((m) => m.category == _selectedCategory).toList();
  }

  List<String> get _categories {
    if (_kitchen == null) return [];
    return _kitchen!.menu.map((m) => m.category ?? 'Other').toSet().toList();
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator(color: AppTheme.primary)));
    if (_error != null) return Scaffold(
      appBar: AppBar(),
      body: Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        const Text('⚠️', style: TextStyle(fontSize: 40)),
        const SizedBox(height: 12),
        Text(_error!, style: const TextStyle(color: AppTheme.textMuted), textAlign: TextAlign.center),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _load, child: const Text('Retry')),
      ])),
    );

    final k = _kitchen!;
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          // ── Hero ─────────────────────────────────────────────────────────
          SliverAppBar(
            expandedHeight: 260,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                fit: StackFit.expand,
                children: [
                  Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF1A0A00), AppTheme.bgDark],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
                  ),
                  Center(
                    child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                      // Avatar
                      Container(
                        width: 96, height: 96,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: AppTheme.primary, width: 3),
                          boxShadow: [BoxShadow(color: AppTheme.primary.withOpacity(0.4), blurRadius: 20)],
                          color: AppTheme.bgCard2,
                        ),
                        child: ClipOval(
                          child: k.image != null
                              ? Image.network(k.image!, fit: BoxFit.cover)
                              : const Center(child: Text('🍽️', style: TextStyle(fontSize: 40))),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(k.name, style: const TextStyle(color: AppTheme.textPrimary, fontSize: 22, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 6),
                      Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                        const Icon(Icons.star, color: AppTheme.accent, size: 16),
                        const SizedBox(width: 4),
                        Text('${k.rating} (${k.reviewsCount} reviews)',
                          style: const TextStyle(color: AppTheme.textSecondary)),
                        const SizedBox(width: 16),
                        _statusDot(k.status),
                      ]),
                    ]),
                  ),
                ],
              ),
            ),
          ),

          // ── Info strip ───────────────────────────────────────────────────
          SliverToBoxAdapter(
            child: Container(
              color: AppTheme.bgCard,
              padding: const EdgeInsets.all(16),
              child: Row(children: [
                if (k.location != null) ...[
                  const Icon(Icons.location_on, color: AppTheme.primary, size: 16),
                  const SizedBox(width: 6),
                  Text(k.location!, style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13)),
                  const SizedBox(width: 16),
                ],
                if (k.openingTime != null) ...[
                  const Icon(Icons.access_time, color: AppTheme.primary, size: 16),
                  const SizedBox(width: 6),
                  Text('${k.openingTime} – ${k.closingTime}',
                    style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13)),
                ],
              ]),
            ),
          ),

          // ── Description ───────────────────────────────────────────────────
          if (k.description != null)
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
                child: Text(k.description!, style: const TextStyle(color: AppTheme.textSecondary, height: 1.6)),
              ),
            ),

          // ── Category filter ───────────────────────────────────────────────
          if (_categories.isNotEmpty)
            SliverToBoxAdapter(
              child: SizedBox(
                height: 44,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                  children: [
                    _catChip('All', null),
                    ..._categories.map((c) => _catChip(c, c)),
                  ],
                ),
              ),
            ),

          // ── Menu title ────────────────────────────────────────────────────
          const SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.fromLTRB(16, 20, 16, 12),
              child: Text('Menu', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppTheme.textPrimary)),
            ),
          ),

          // ── Menu grid ────────────────────────────────────────────────────
          SliverPadding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 100),
            sliver: SliverGrid(
              delegate: SliverChildBuilderDelegate(
                (ctx, i) {
                  final item = _filteredMenu[i];
                  return MenuItemCard(
                    item: item,
                    onAddToCart: () {
                      ctx.read<CartProvider>().addItem(item);
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(
                        content: Text('${item.name} added!'),
                        backgroundColor: AppTheme.primary,
                        duration: const Duration(seconds: 1),
                      ));
                    },
                  );
                },
                childCount: _filteredMenu.length,
              ),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2, childAspectRatio: 0.7,
                crossAxisSpacing: 12, mainAxisSpacing: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _catChip(String label, String? val) {
    final selected = _selectedCategory == val;
    return GestureDetector(
      onTap: () => setState(() => _selectedCategory = val),
      child: Container(
        margin: const EdgeInsets.only(right: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: selected ? AppTheme.primary.withOpacity(0.15) : AppTheme.bgCard2,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: selected ? AppTheme.primary : AppTheme.border),
        ),
        child: Text(label, style: TextStyle(
          color: selected ? AppTheme.primary : AppTheme.textMuted,
          fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
          fontSize: 13,
        )),
      ),
    );
  }

  Widget _statusDot(String status) {
    Color c;
    switch (status.toLowerCase()) {
      case 'open': c = AppTheme.success; break;
      case 'busy': c = AppTheme.warning; break;
      default: c = AppTheme.danger; break;
    }
    return Row(children: [
      Container(width: 8, height: 8,
        decoration: BoxDecoration(color: c, shape: BoxShape.circle,
          boxShadow: [BoxShadow(color: c.withOpacity(0.7), blurRadius: 6)])),
      const SizedBox(width: 5),
      Text(status, style: TextStyle(color: c, fontSize: 12, fontWeight: FontWeight.w700)),
    ]);
  }
}
