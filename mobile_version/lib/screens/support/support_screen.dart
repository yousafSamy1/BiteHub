import 'package:flutter/material.dart';
import '../../core/api/api_service.dart';
import '../../core/models/models.dart';
import '../../core/theme/app_theme.dart';
import '../../widgets/gradient_button.dart';

class SupportScreen extends StatefulWidget {
  const SupportScreen({super.key});
  @override
  State<SupportScreen> createState() => _SupportScreenState();
}

class _SupportScreenState extends State<SupportScreen> {
  List<SupportTicket>? _tickets;
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    try {
      final t = await ApiService.getTickets();
      if (mounted) setState(() { _tickets = t; _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _newTicket() {
    // Bottom sheet to create ticket
    showModalBottomSheet(context: context, backgroundColor: AppTheme.bgCard, isScrollControlled: true, builder: (ctx) {
      final subjCtrl = TextEditingController();
      final msgCtrl = TextEditingController();
      bool isSubmitting = false;

      return StatefulBuilder(builder: (ctx, setModalState) {
        return Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom, left: 24, right: 24, top: 24),
          child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('New Support Ticket', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppTheme.textPrimary)),
            const SizedBox(height: 16),
            TextField(controller: subjCtrl, decoration: const InputDecoration(labelText: 'Subject')),
            const SizedBox(height: 16),
            TextField(controller: msgCtrl, decoration: const InputDecoration(labelText: 'Message'), maxLines: 4),
            const SizedBox(height: 24),
            GradientButton(
              label: 'Submit Ticket',
              isLoading: isSubmitting,
              onPressed: () async {
                if (subjCtrl.text.isEmpty || msgCtrl.text.isEmpty) return;
                setModalState(() => isSubmitting = true);
                try {
                  await ApiService.createTicket(subjCtrl.text, msgCtrl.text);
                  if (ctx.mounted) { Navigator.pop(ctx); _load(); }
                } finally {
                  setModalState(() => isSubmitting = false);
                }
              },
            ),
            const SizedBox(height: 24),
          ]),
        );
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Support'), actions: [
        IconButton(icon: const Icon(Icons.add), onPressed: _newTicket),
      ]),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _tickets == null || _tickets!.isEmpty
              ? Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  const Text('🎧', style: TextStyle(fontSize: 60)),
                  const SizedBox(height: 16),
                  const Text('No support tickets', style: TextStyle(fontSize: 18, color: AppTheme.textMuted)),
                  const SizedBox(height: 24),
                  ElevatedButton(onPressed: _newTicket, child: const Text('Contact Support')),
                ]))
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: _tickets!.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemBuilder: (_, i) {
                    final t = _tickets![i];
                    return Container(
                      padding: const EdgeInsets.all(16),
                      decoration: AppTheme.glassCard,
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                          Expanded(child: Text(t.subject, style: const TextStyle(fontWeight: FontWeight.w700, color: AppTheme.textPrimary))),
                          Text(t.status, style: TextStyle(color: t.status == 'Open' ? AppTheme.success : AppTheme.textMuted, fontSize: 12)),
                        ]),
                        const SizedBox(height: 8),
                        if (t.lastMessage != null) Text(t.lastMessage!, style: const TextStyle(color: AppTheme.textSecondary), maxLines: 2, overflow: TextOverflow.ellipsis),
                      ]),
                    );
                  },
                ),
    );
  }
}
