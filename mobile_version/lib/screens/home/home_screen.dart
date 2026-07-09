import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../core/api/api_service.dart';
import '../../core/models/models.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/providers/cart_provider.dart';
import '../../core/theme/app_theme.dart';
import '../../widgets/cards.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final d = await ApiService.getHomeData();
      if (mounted) setState(() { _data = d; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          // ── App Bar ──────────────────────────────────────────────────────
          SliverAppBar(
            expandedHeight: 200,
            floating: false,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF1A0A00), Color(0xFF0D0D0D)],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                ),
                child: SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text(
                                auth.isLoggedIn ? 'Hello, ${auth.user?.name.split(' ').first ?? ''}!' : 'Hello, Guest!',
                                style: const TextStyle(color: AppTheme.textMuted, fontSize: 13),
                              ),
                              const Text('What are you craving?',
                                style: TextStyle(color: AppTheme.textPrimary, fontSize: 20, fontWeight: FontWeight.w800)),
                            ]),
                            Container(
                              width: 44, height: 44,
                              decoration: BoxDecoration(
                                gradient: AppTheme.primaryGradient,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: const Center(child: Icon(Icons.restaurant_menu, color: Colors.white, size: 22)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 14),
                        // Search bar
                        GestureDetector(
                          onTap: () => context.push('/browse'),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            decoration: BoxDecoration(
                              color: AppTheme.bgCard2,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppTheme.border),
                            ),
                            child: const Row(children: [
                              Icon(Icons.search, color: AppTheme.textMuted, size: 20),
                              SizedBox(width: 10),
                              Text('Search kitchens, dishes...', style: TextStyle(color: AppTheme.textMuted)),
                            ]),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),

          if (_loading)
            const SliverFillRemaining(child: Center(child: CircularProgressIndicator(color: AppTheme.primary)))
          else if (_error != null)
            SliverFillRemaining(
              child: Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                const Icon(Icons.warning_amber_rounded, size: 40, color: AppTheme.danger),
                const SizedBox(height: 12),
                Text('Could not load data:\n\n$_error', 
                     style: const TextStyle(color: AppTheme.textMuted, fontSize: 11),
                     textAlign: TextAlign.center),
                const SizedBox(height: 16),
                ElevatedButton(onPressed: _load, child: const Text('Retry')),
              ])),
            )
          else ...[
            // ── Stats strip ──────────────────────────────────────────────
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
                child: Row(children: [
                  _StatChip(label: '500+ Kitchens', icon: Icons.storefront),
                  const SizedBox(width: 10),
                  _StatChip(label: '10K+ Customers', icon: Icons.people_alt),
                  const SizedBox(width: 10),
                  _StatChip(label: '50K+ Orders', icon: Icons.delivery_dining),
                ]),
              ),
            ),

            // ── Categories ───────────────────────────────────────────────
            if ((_data!['categories'] as List?)?.isNotEmpty ?? false) ...[
              _sectionHeader(context, 'Browse by Category', 'Explore'),
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 90,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 0),
                    itemCount: (_data!['categories'] as List).length,
                    separatorBuilder: (_, __) => const SizedBox(width: 10),
                    itemBuilder: (_, i) {
                      final cat = Category.fromJson((_data!['categories'] as List)[i]);
                      final icons = [Icons.restaurant, Icons.cake, Icons.eco, Icons.local_drink, Icons.breakfast_dining, Icons.soup_kitchen, Icons.fastfood, Icons.bento];
                      return GestureDetector(
                        onTap: () => context.push('/browse?cat=${cat.id}'),
                        child: Container(
                          width: 80,
                          decoration: BoxDecoration(
                            color: AppTheme.bgCard,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppTheme.border),
                          ),
                          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                            Icon(icons[i % icons.length], color: AppTheme.primary, size: 26),
                            const SizedBox(height: 4),
                            Text(cat.name,
                              style: const TextStyle(fontSize: 10, color: AppTheme.textSecondary, fontWeight: FontWeight.w600),
                              textAlign: TextAlign.center, maxLines: 1, overflow: TextOverflow.ellipsis),
                          ]),
                        ),
                      );
                    },
                  ),
                ),
              ),
            ],

            // ── Featured Kitchens ────────────────────────────────────────
            if ((_data!['top_kitchens'] as List?)?.isNotEmpty ?? false) ...[
              _sectionHeader(context, 'Top Kitchens', 'Top Rated',
                action: TextButton(onPressed: () => context.push('/browse'), child: const Text('See All'))),
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 270,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
                    itemCount: (_data!['top_kitchens'] as List).length,
                    separatorBuilder: (_, __) => const SizedBox(width: 14),
                    itemBuilder: (_, i) {
                      final k = Kitchen.fromJson((_data!['top_kitchens'] as List)[i]);
                      return SizedBox(
                        width: 220,
                        child: KitchenCard(
                          kitchen: k,
                          onTap: () => context.push('/kitchen/${k.id}'),
                        ),
                      );
                    },
                  ),
                ),
              ),
            ],

            // ── Popular Dishes ───────────────────────────────────────────
            if ((_data!['popular'] as List?)?.isNotEmpty ?? false) ...[
              _sectionHeader(context, 'Popular Dishes', 'Trending'),
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                sliver: SliverGrid(
                  delegate: SliverChildBuilderDelegate(
                    (ctx, i) {
                      final item = MenuItem.fromJson((_data!['popular'] as List)[i]);
                      return MenuItemCard(
                        item: item,
                        onAddToCart: () {
                          context.read<CartProvider>().addItem(item);
                          ScaffoldMessenger.of(ctx).showSnackBar(
                            SnackBar(
                              content: Text('${item.name} added to cart!'),
                              backgroundColor: AppTheme.primary,
                              duration: const Duration(seconds: 1),
                            ),
                          );
                        },
                      );
                    },
                    childCount: (_data!['popular'] as List).length.clamp(0, 8),
                  ),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2, childAspectRatio: 0.72,
                    crossAxisSpacing: 12, mainAxisSpacing: 12,
                  ),
                ),
              ),
            ],

            // ── CTA Card ────────────────────────────────────────────────
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Container(
                  padding: const EdgeInsets.all(24),
                  decoration: AppTheme.primaryCard,
                  child: Column(children: [
                    const Icon(Icons.storefront, size: 36, color: Colors.white),
                    const SizedBox(height: 12),
                    const Text('Start Your Food Business',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.textPrimary),
                      textAlign: TextAlign.center),
                    const SizedBox(height: 8),
                    const Text('Join hundreds of home cooks. Reach thousands of customers.',
                      style: TextStyle(color: AppTheme.textMuted, fontSize: 13),
                      textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () => context.push('/register'),
                      child: const Text('Join as Kitchen'),
                    ),
                  ]),
                ),
              ),
            ),
            const SliverToBoxAdapter(child: SizedBox(height: 100)),
          ],
        ],
      ),
    );
  }

  Widget _sectionHeader(BuildContext context, String title, String subtitle, {Widget? action}) {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 28, 16, 14),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(subtitle, style: const TextStyle(color: AppTheme.primary, fontSize: 11, fontWeight: FontWeight.w700, letterSpacing: 1)),
              Text(title, style: const TextStyle(color: AppTheme.textPrimary, fontSize: 20, fontWeight: FontWeight.w800)),
            ]),
            if (action != null) action,
          ],
        ),
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  final String label;
  final IconData icon;
  const _StatChip({required this.label, required this.icon});
  @override
  Widget build(BuildContext context) => Expanded(
    child: Container(
      padding: const EdgeInsets.symmetric(vertical: 10),
      decoration: BoxDecoration(
        color: AppTheme.bgCard,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(children: [
        Icon(icon, color: AppTheme.primary, size: 20),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(color: AppTheme.textMuted, fontSize: 9, fontWeight: FontWeight.w600), textAlign: TextAlign.center),
      ]),
    ),
  );
}
