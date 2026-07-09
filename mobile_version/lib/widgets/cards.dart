import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:shimmer/shimmer.dart';
import '../core/theme/app_theme.dart';
import '../core/models/models.dart';

class KitchenCard extends StatelessWidget {
  final Kitchen kitchen;
  final VoidCallback? onTap;

  const KitchenCard({super.key, required this.kitchen, this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: AppTheme.glassCard,
        clipBehavior: Clip.hardEdge,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image header
            SizedBox(
              height: 140,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  kitchen.image != null
                      ? CachedNetworkImage(
                          imageUrl: kitchen.image!,
                          fit: BoxFit.cover,
                          placeholder: (_, __) => _shimmer(),
                          errorWidget: (_, __, ___) => _placeholder(),
                        )
                      : _placeholder(),
                  // Dark gradient overlay
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [Colors.transparent, Colors.black.withOpacity(0.6)],
                      ),
                    ),
                  ),
                  // Status badge
                  Positioned(
                    top: 10, right: 10,
                    child: _StatusBadge(status: kitchen.status),
                  ),
                  // Sponsored badge
                  if (kitchen.isSponsored)
                    Positioned(
                      top: 10, left: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          gradient: AppTheme.primaryGradient,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text('⭐ Promoted',
                          style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700)),
                      ),
                    ),
                ],
              ),
            ),
            // Body
            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(kitchen.name,
                          style: const TextStyle(
                            fontWeight: FontWeight.w700, fontSize: 15,
                            color: AppTheme.textPrimary,
                          ),
                          maxLines: 1, overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (kitchen.isVerified)
                        const Icon(Icons.verified, color: Colors.green, size: 16),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.star, color: AppTheme.accent, size: 14),
                      const SizedBox(width: 4),
                      Text(kitchen.rating.toStringAsFixed(1),
                        style: const TextStyle(color: AppTheme.textPrimary, fontSize: 13, fontWeight: FontWeight.w600)),
                      const SizedBox(width: 4),
                      Text('(${kitchen.reviewsCount})',
                        style: const TextStyle(color: AppTheme.textMuted, fontSize: 12)),
                    ],
                  ),
                  if (kitchen.location != null) ...[
                    const SizedBox(height: 4),
                    Row(children: [
                      const Icon(Icons.location_on, color: AppTheme.textMuted, size: 12),
                      const SizedBox(width: 4),
                      Text(kitchen.location!, style: const TextStyle(color: AppTheme.textMuted, fontSize: 12)),
                    ]),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _placeholder() => Container(
    color: AppTheme.bgCard2,
    child: const Center(child: Text('🍽️', style: TextStyle(fontSize: 40))),
  );

  Widget _shimmer() => Shimmer.fromColors(
    baseColor: AppTheme.bgCard2,
    highlightColor: AppTheme.border,
    child: Container(color: AppTheme.bgCard2),
  );
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color color;
    switch (status.toLowerCase()) {
      case 'open': color = const Color(0xFF4ADE80); break;
      case 'busy': color = const Color(0xFFFBBF24); break;
      default:     color = const Color(0xFFF87171); break;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.5)),
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Container(
          width: 6, height: 6,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle,
            boxShadow: [BoxShadow(color: color.withOpacity(0.8), blurRadius: 4)]),
        ),
        const SizedBox(width: 5),
        Text(status, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w700)),
      ]),
    );
  }
}

// ── Menu Item Card ──────────────────────────────────────────────────────────
class MenuItemCard extends StatelessWidget {
  final MenuItem item;
  final VoidCallback? onAddToCart;

  const MenuItemCard({super.key, required this.item, this.onAddToCart});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: AppTheme.glassCard,
      clipBehavior: Clip.hardEdge,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image
          SizedBox(
            height: 130,
            child: Stack(fit: StackFit.expand, children: [
              item.image != null
                  ? CachedNetworkImage(
                      imageUrl: item.image!,
                      fit: BoxFit.cover,
                      placeholder: (_, __) => _shimmer(),
                      errorWidget: (_, __, ___) => _foodPlaceholder(),
                    )
                  : _foodPlaceholder(),
              if (item.discountPrice != null)
                Positioned(
                  top: 8, left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppTheme.danger.withOpacity(0.9),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      '-${(((item.price - item.discountPrice!) / item.price) * 100).round()}%',
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700),
                    ),
                  ),
                ),
            ]),
          ),
          // Info
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(item.name,
                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppTheme.textPrimary),
                maxLines: 1, overflow: TextOverflow.ellipsis),
              if (item.category != null) ...[
                const SizedBox(height: 3),
                Text(item.category!, style: const TextStyle(color: AppTheme.textMuted, fontSize: 11)),
              ],
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    if (item.discountPrice != null)
                      Text('${item.price.toStringAsFixed(0)} EGP',
                        style: const TextStyle(
                          color: AppTheme.textMuted, fontSize: 11,
                          decoration: TextDecoration.lineThrough,
                        )),
                    Text(
                      '${item.effectivePrice.toStringAsFixed(0)} EGP',
                      style: const TextStyle(color: AppTheme.primary, fontWeight: FontWeight.w800, fontSize: 15),
                    ),
                  ]),
                  GestureDetector(
                    onTap: onAddToCart,
                    child: Container(
                      width: 36, height: 36,
                      decoration: BoxDecoration(
                        gradient: AppTheme.primaryGradient,
                        borderRadius: BorderRadius.circular(10),
                        boxShadow: [BoxShadow(color: AppTheme.primary.withOpacity(0.4), blurRadius: 8)],
                      ),
                      child: const Icon(Icons.add, color: Colors.white, size: 20),
                    ),
                  ),
                ],
              ),
            ]),
          ),
        ],
      ),
    );
  }

  Widget _foodPlaceholder() => Container(
    color: AppTheme.bgCard2,
    child: const Center(child: Icon(Icons.restaurant_menu, size: 36, color: AppTheme.textMuted)),
  );

  Widget _shimmer() => Shimmer.fromColors(
    baseColor: AppTheme.bgCard2,
    highlightColor: AppTheme.border,
    child: Container(color: AppTheme.bgCard2),
  );
}
