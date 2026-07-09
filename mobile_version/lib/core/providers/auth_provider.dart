import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api/api_client.dart';
import '../api/api_service.dart';
import '../models/models.dart';

class AuthProvider extends ChangeNotifier {
  AppUser? _user;
  bool _isLoading = false;
  String? _error;
  bool _loggedIn = false;

  AppUser? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isLoggedIn => _loggedIn;

  AuthProvider() {
    _checkSavedToken();
  }

  Future<void> _checkSavedToken() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    if (token != null) {
      _loggedIn = true;
      notifyListeners();
      try {
        _user = await ApiService.getProfile();
        notifyListeners();
      } catch (_) {}
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final data = await ApiService.login(email, password);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', data['token']);
      _user = AppUser.fromJson(data['user']);
      _loggedIn = true;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = _parseError(e);
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> register(String name, String email, String phone, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final data = await ApiService.register(name, email, phone, password);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', data['token']);
      _user = AppUser.fromJson(data['user']);
      _loggedIn = true;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = _parseError(e);
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await ApiService.logout();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    _user = null;
    _loggedIn = false;
    ApiClient.resetInstance();
    notifyListeners();
  }

  Future<void> refreshProfile() async {
    try {
      _user = await ApiService.getProfile();
      notifyListeners();
    } catch (_) {}
  }

  String _parseError(dynamic e) {
    if (e is Exception) {
      return e.toString().replaceAll('Exception: ', '');
    }
    return 'An error occurred. Please try again.';
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
