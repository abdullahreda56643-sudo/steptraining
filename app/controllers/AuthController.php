<?php

declare(strict_types=1);

class AuthController
{
    public static function showLogin(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            redirect(($user['role'] ?? '') === 'admin' ? '/admin' : '/');
        }

        view('auth/login', ['title' => 'تسجيل الدخول'], 'auth');
    }

    public static function showRegister(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            redirect(($user['role'] ?? '') === 'admin' ? '/admin' : '/');
        }

        view('auth/register', ['title' => 'إنشاء حساب جديد'], 'auth');
    }

    public static function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/register');
        }

        if (!Csrf::validate()) {
            flash('error', 'انتهت صلاحية الجلسة. أعد المحاولة.');
            redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!Validator::required($name) || !Validator::required($email) || !Validator::required($password)) {
            flash('error', 'يرجى تعبئة جميع الحقول.');
            redirect('/register');
        }

        if (!Validator::email($email)) {
            flash('error', 'البريد الإلكتروني غير صالح.');
            redirect('/register');
        }

        if (!Validator::minLength($password, 8)) {
            flash('error', 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.');
            redirect('/register');
        }

        if (User::findByEmail($email) !== null) {
            flash('error', 'هذا البريد الإلكتروني مسجل مسبقاً.');
            redirect('/register');
        }

        $user = User::create($name, $email, $password);
        if (!$user) {
            flash('error', 'تعذر إنشاء الحساب.');
            redirect('/register');
        }

        Auth::login($user);
        flash('success', 'تم إنشاء الحساب بنجاح.');
        redirect('/');
    }

    public static function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
        }

        if (!Csrf::validate()) {
            flash('error', 'رمز الحماية غير صالح.');
            redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::findByEmail($email);
        if ($user && (int) $user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
            Auth::login($user);
            redirect($user['role'] === 'admin' ? '/admin' : '/');
        }

        flash('error', 'بيانات الدخول غير صحيحة.');
        redirect('/login');
    }

    public static function logout(): void
    {
        Auth::logout();
        redirect('/login');
    }
}
