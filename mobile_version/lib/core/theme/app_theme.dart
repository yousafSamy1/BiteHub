import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // ── Colors ──────────────────────────────────────────────────────────────
  static const Color primary    = Color(0xFFFF6B35);
  static const Color accent     = Color(0xFFFFA726);
  static const Color bgDark     = Color(0xFF0D0D0D);
  static const Color bgCard     = Color(0xFF1A1A1A);
  static const Color bgCard2    = Color(0xFF222222);
  static const Color border     = Color(0xFF2A2A2A);
  static const Color textPrimary   = Color(0xFFF5F5F5);
  static const Color textSecondary = Color(0xFFBBBBBB);
  static const Color textMuted     = Color(0xFF777777);
  static const Color success    = Color(0xFF4ADE80);
  static const Color warning    = Color(0xFFFBBF24);
  static const Color danger     = Color(0xFFF87171);

  // ── Gradients ────────────────────────────────────────────────────────────
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [primary, accent],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient bgGradient = LinearGradient(
    colors: [Color(0xFF1A0A00), bgDark],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );

  // ── Card decoration ──────────────────────────────────────────────────────
  static BoxDecoration get glassCard => BoxDecoration(
    color: bgCard,
    borderRadius: BorderRadius.circular(16),
    border: Border.all(color: border),
    boxShadow: [
      BoxShadow(
        color: Colors.black.withOpacity(0.3),
        blurRadius: 12,
        offset: const Offset(0, 4),
      ),
    ],
  );

  static BoxDecoration get primaryCard => BoxDecoration(
    gradient: const LinearGradient(
      colors: [Color(0xFF2A1A10), Color(0xFF1A1A1A)],
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
    ),
    borderRadius: BorderRadius.circular(16),
    border: Border.all(color: primary.withOpacity(0.3)),
  );

  // ── Theme data ───────────────────────────────────────────────────────────
  static ThemeData get dark => ThemeData(
    useMaterial3: true,
    brightness: Brightness.dark,
    scaffoldBackgroundColor: bgDark,
    colorScheme: const ColorScheme.dark(
      primary: primary,
      secondary: accent,
      surface: bgCard,
      error: danger,
    ),
    textTheme: GoogleFonts.interTextTheme(ThemeData.dark().textTheme).copyWith(
      displayLarge: GoogleFonts.outfit(
        color: textPrimary, fontSize: 32, fontWeight: FontWeight.w800,
      ),
      displayMedium: GoogleFonts.outfit(
        color: textPrimary, fontSize: 24, fontWeight: FontWeight.w700,
      ),
      headlineMedium: GoogleFonts.outfit(
        color: textPrimary, fontSize: 20, fontWeight: FontWeight.w700,
      ),
      titleLarge: GoogleFonts.inter(
        color: textPrimary, fontSize: 16, fontWeight: FontWeight.w600,
      ),
      bodyLarge: GoogleFonts.inter(color: textSecondary, fontSize: 15),
      bodyMedium: GoogleFonts.inter(color: textSecondary, fontSize: 13),
      bodySmall: GoogleFonts.inter(color: textMuted, fontSize: 11),
    ),
    appBarTheme: AppBarTheme(
      backgroundColor: bgCard,
      elevation: 0,
      centerTitle: false,
      titleTextStyle: GoogleFonts.outfit(
        color: textPrimary, fontSize: 20, fontWeight: FontWeight.w700,
      ),
      iconTheme: const IconThemeData(color: textPrimary),
    ),
    bottomNavigationBarTheme: const BottomNavigationBarThemeData(
      backgroundColor: bgCard,
      selectedItemColor: primary,
      unselectedItemColor: textMuted,
      type: BottomNavigationBarType.fixed,
      elevation: 0,
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: bgCard2,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: primary, width: 2),
      ),
      hintStyle: const TextStyle(color: textMuted),
      labelStyle: const TextStyle(color: textSecondary),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: primary,
        foregroundColor: Colors.white,
        elevation: 0,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        textStyle: GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.w700),
      ),
    ),
    cardTheme: CardThemeData(
      color: bgCard,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: border),
      ),
    ),
    dividerTheme: const DividerThemeData(color: border, thickness: 1),
    chipTheme: ChipThemeData(
      backgroundColor: bgCard2,
      selectedColor: primary.withOpacity(0.2),
      labelStyle: const TextStyle(color: textSecondary),
      side: const BorderSide(color: border),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    ),
  );
}
