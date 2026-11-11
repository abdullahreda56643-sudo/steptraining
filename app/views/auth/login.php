<?php
$error = flash('error');
$success = flash('success');
?>

<section class="auth-card" aria-labelledby="auth-title">
    <nav class="auth-tabs" aria-label="الدخول أو التسجيل">
        <a class="auth-tab" href="<?= e(url('/login')) ?>" aria-current="page">
            <svg class="auth-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M12 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/></svg>
            <span>تسجيل الدخول</span>
        </a>
        <a class="auth-tab" href="<?= e(url('/register')) ?>">
            <svg class="auth-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
            <span>إنشاء حساب</span>
        </a>
    </nav>

    <header class="auth-heading">
        <h2 id="auth-title">مرحباً بعودتك</h2>
        <p>سجّل دخولك وواصل رحلتك نحو أداء أفضل في اختبار STEP.</p>
    </header>

    <?php if ($error): ?>
        <div class="auth-alert auth-alert--error" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="auth-alert auth-alert--success" role="status">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.7 2.7L16 9.8"/></svg>
            <span><?= e($success) ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/login')) ?>" class="auth-form" data-auth-form>
        <?= csrfField() ?>

        <div class="auth-field">
            <label for="auth-email">البريد الإلكتروني</label>
            <div class="auth-input-wrap is-ltr">
                <span class="auth-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 8 8 5.5L20 8"/></svg></span>
                <input id="auth-email" class="auth-ltr" type="email" name="email" required maxlength="255" autocomplete="email" autofocus placeholder="name@example.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <div class="auth-field">
            <label for="auth-password">كلمة المرور</label>
            <div class="auth-input-wrap is-ltr">
                <span class="auth-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10.5" width="16" height="10" rx="3"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/></svg></span>
                <input id="auth-password" class="auth-ltr auth-password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                <button type="button" class="auth-password-toggle" id="authPasswordToggle" aria-label="إظهار كلمة المرور">إظهار</button>
            </div>
        </div>

        <button type="submit" class="auth-submit">تسجيل الدخول</button>
    </form>

    <p class="auth-switch">ليس لديك حساب؟ <a href="<?= e(url('/register')) ?>">أنشئ حسابك الآن</a></p>
</section>