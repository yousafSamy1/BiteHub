import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/api/api_service.dart';
import '../../core/models/models.dart';
import '../../core/providers/cart_provider.dart';
import '../../core/theme/app_theme.dart';
import '../../widgets/cards.dart';

class BrowseScreen extends StatefulWidget {
  const BrowseScreen({super.key});
  @override
  State<BrowseScreen> createState() => _BrowseScreenState();
}

class _BrowseScreenState extends State<BrowseScreen> with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  final _searchCtrl = TextEditingController();
  List<Kitchen> _kitchens = [];
  List<Caterer> _caterers = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    _load();
  }

  @override
  void dispose() { _tabCtrl.dispose(); _searchCtrl.dispose(); super.dispose(); }

  Future<void> _load({String? search}) async {
    setState(() => _loading = true);
    try {
      final d = await ApiService.getBrowseData(search: search);
      if (mounted) setState(() {
        _kitchens = (d['kitchens'] as List? ?? []).map((k) => Kitchen.fromJson(k)).toList();
        _caterers = (d['caterers'] as List? ?? []).map((c) => Caterer.fromJson(c)).toList();
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Column(children: [
          // Header
          Container(
            color: AppTheme.bgCard,
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
            child: Column(children: [
              const Row(children: [
                Text('Discover', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppTheme.textPrimary)),
              ]),
              const SizedBox(height: 12),
              // Search
              TextField(
                controller: _searchCtrl,
                decoration: InputDecoration(
                  hintText: 'Search kitchens, caterers...',
                  prefixIcon: const Icon(Icons.search, color: AppTheme.textMuted),
                  suffixIcon: _searchCtrl.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, color: AppTheme.textMuted),
                          onPressed: () { _searchCtrl.clear(); _load(); },
                        )
                      : null,
                ),
                onSubmitted: (v) => _load(search: v),
                onChanged: (v) { if (v.isEmpty) _load(); setState(() {}); },
              ),
              const SizedBox(height: 12),
              // Tabs
              TabBar(
                controller: _tabCtrl,
                indicatorColor: AppTheme.primary,
                labelColor: AppTheme.primary,
                unselectedLabelColor: AppTheme.textMuted,
                dividerColor: AppTheme.border,
                tabs: [
                  Tab(text: 'Kitchens (${_kitchens.length})'),
                  Tab(text: 'Caterers (${_caterers.length})'),
                ],
              ),
            ]),
          ),
          // Body
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
                : TabBarView(
                    controller: _tabCtrl,
                    children: [
                      _KitchenList(kitchens: _kitchens),
                      _CatererList(caterers: _caterers),
                    ],
                  ),
          ),
        ]),
      ),
    );
  }
}

class _KitchenList extends StatelessWidget {
  final List<Kitchen> kitchens;
  const _KitchenList({required this.kitchens});
  @override
  Widget build(BuildContext context) {
    if (kitchens.isEmpty) return const Center(
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        Text('😔', style: TextStyle(fontSize: 40)),
        SizedBox(height: 12),
        Text('No kitchens found', style: TextStyle(color: AppTheme.textMuted)),
      ]),
    );
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: kitchens.length,
      separatorBuilder: (_, __) => const SizedBox(height: 14),
      itemBuilder: (ctx, i) => KitchenCard(
        kitchen: kitchens[i],
        onTap: () => ctx.push('/kitchen/${kitchens[i].id}'),
      ),
    );
  }
}

class _CatererList extends StatelessWidget {
  final List<Caterer> caterers;
  const _CatererList({required this.caterers});
  @override
  Widget build(BuildContext context) {
    if (caterers.isEmpty) return const Center(
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        Text('😔', style: TextStyle(fontSize: 40)),
        SizedBox(height: 12),
        Text('No caterers found', style: TextStyle(color: AppTheme.textMuted)),
      ]),
    );
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: caterers.length,
      itemBuilder: (_, i) {
        final c = caterers[i];
        return Container(
          margin: const EdgeInsets.only(bottom: 14),
          decoration: AppTheme.glassCard,
          child: ListTile(
            contentPadding: const EdgeInsets.all(16),
            leading: Container(
              width: 56, height: 56,
              decoration: BoxDecoration(
                gradient: AppTheme.primaryGradient,
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Center(child: Text('👨‍🍳', style: TextStyle(fontSize: 28))),
            ),
            title: Text(c.name, style: const TextStyle(fontWeight: FontWeight.w700, color: AppTheme.textPrimary)),
            subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const SizedBox(height: 4),
              Row(children: [
                const Icon(Icons.star, color: AppTheme.accent, size: 14),
                const SizedBox(width: 4),
                Text('${c.rating} (${c.reviewsCount} reviews)', style: const TextStyle(color: AppTheme.textMuted, fontSize: 12)),
              ]),
            ]),
            trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: AppTheme.textMuted),
          ),
        );
      },
    );
  }
}
