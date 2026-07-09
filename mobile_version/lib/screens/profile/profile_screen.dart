import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/api_service.dart';
import '../../core/models/models.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/theme/app_theme.dart';
import '../../widgets/gradient_button.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});
  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    final auth = context.read<AuthProvider>();
    if (auth.user != null) {
      _nameCtrl.text = auth.user!.name;
      // Phone is not in the model right now, would come from profile refresh
    }
  }

  @override
  void dispose() { _nameCtrl.dispose(); _phoneCtrl.dispose(); super.dispose(); }

  Future<void> _save() async {
    setState(() => _loading = true);
    try {
      await ApiService.updateProfile({'full_name': _nameCtrl.text, 'phone': _phoneCtrl.text});
      if (mounted) {
        await context.read<AuthProvider>().refreshProfile();
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Profile updated'), backgroundColor: AppTheme.success));
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString()), backgroundColor: AppTheme.danger));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    if (!auth.isLoggedIn) {
      return Scaffold(
        appBar: AppBar(title: const Text('Profile')),
        body: Center(child: ElevatedButton(onPressed: () => context.push('/login'), child: const Text('Login'))),
      );
    }
    
    final u = auth.user!;
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Profile'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout, color: AppTheme.danger),
            onPressed: () { auth.logout(); context.go('/'); },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(children: [
          // Header card
          Container(
            padding: const EdgeInsets.all(24),
            decoration: AppTheme.glassCard,
            child: Column(children: [
              Container(
                width: 80, height: 80,
                decoration: const BoxDecoration(shape: BoxShape.circle, gradient: AppTheme.primaryGradient),
                child: Center(child: Text(u.name.substring(0, 1).toUpperCase(), style: const TextStyle(fontSize: 32, color: Colors.white, fontWeight: FontWeight.w800))),
              ),
              const SizedBox(height: 16),
              Text(u.name, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppTheme.textPrimary)),
              Text(u.email, style: const TextStyle(color: AppTheme.textMuted)),
              const SizedBox(height: 24),
              Row(children: [
                Expanded(child: _stat('Wallet', '${u.walletBalance.toStringAsFixed(0)} EGP', Icons.account_balance_wallet, AppTheme.success)),
                const SizedBox(width: 12),
                Expanded(child: _stat('BitePoints', '${u.loyaltyPoints}', Icons.star, AppTheme.accent)),
              ]),
            ]),
          ),
          const SizedBox(height: 24),
          
          // Form
          Container(
            padding: const EdgeInsets.all(20),
            decoration: AppTheme.glassCard,
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Text('Personal Info', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppTheme.textPrimary)),
              const SizedBox(height: 16),
              TextField(
                controller: _nameCtrl,
                decoration: const InputDecoration(labelText: 'Full Name'),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _phoneCtrl,
                decoration: const InputDecoration(labelText: 'Phone Number (Optional)'),
                keyboardType: TextInputType.phone,
              ),
              const SizedBox(height: 24),
              GradientButton(
                label: 'Save Changes',
                isLoading: _loading,
                onPressed: _save,
              ),
            ]),
          ),
          
          const SizedBox(height: 24),
          ListTile(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            tileColor: AppTheme.bgCard,
            leading: const Icon(Icons.calendar_month, color: AppTheme.primary),
            title: const Text('My Meal Plans', style: TextStyle(color: AppTheme.textPrimary, fontWeight: FontWeight.w600)),
            trailing: const Icon(Icons.arrow_forward_ios, size: 16, color: AppTheme.textMuted),
            onTap: () => context.push('/subscriptions'),
          ),
          const SizedBox(height: 12),
          ListTile(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            tileColor: AppTheme.bgCard,
            leading: const Icon(Icons.support_agent, color: AppTheme.accent),
            title: const Text('Support Tickets', style: TextStyle(color: AppTheme.textPrimary, fontWeight: FontWeight.w600)),
            trailing: const Icon(Icons.arrow_forward_ios, size: 16, color: AppTheme.textMuted),
            onTap: () => context.push('/support'),
          ),
          const SizedBox(height: 40),
        ]),
      ),
    );
  }

  Widget _stat(String label, String val, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: AppTheme.bgCard2, borderRadius: BorderRadius.circular(12)),
      child: Column(children: [
        Icon(icon, color: color, size: 24),
        const SizedBox(height: 8),
        Text(val, style: TextStyle(color: color, fontWeight: FontWeight.w800, fontSize: 16)),
        Text(label, style: const TextStyle(color: AppTheme.textMuted, fontSize: 11)),
      ]),
    );
  }
}
