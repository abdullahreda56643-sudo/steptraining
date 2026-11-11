<?php $error = flash('error'); ?>

<section class="auth-card" aria-labelledby="auth-title">
    <nav class="auth-tabs" aria-label="الدخول أو التسجيل">
        <a class="auth-tab" href="<?= e(url('/login')) ?>">
            <svg class="auth-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M12 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/></svg>
            <span>تسجيل الدخول</span>
        </a>
        <a class="auth-tab" href="<?= e(url('/register')) ?>" aria-current="page">
            <svg class="auth-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
            <span>إنشاء حساب</span>
        </a>
    </nav>

    <header class="auth-heading">
        <h2 id="auth-title">ابدأ رحلتك</h2>
        <p>أنشئ حسابك لتبدأ التدريب وتتابع تقدمك في STEP.</p>
    </header>

    <?php if ($error): ?>
        <div class="auth-alert auth-alert--error" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/register')) ?>" class="auth-form" data-auth-form>
        <?= csrfField() ?>

        <div class="auth-field">
            <label for="auth-name">الاسم الكامل</label>
            <div class="auth-input-wrap">
                <input id="auth-name" type="text" name="name" required minlength="2" maxlength="255" autocomplete="name" autofocus placeholder="اكتب اسمك الكامل" value="<?= e($_POST['name'] ?? '') ?>">
            </div>
        </div>

        <div class="auth-field">
            <label for="auth-email">البريد الإلكتروني</label>
            <div class="auth-input-wrap">
                <input id="auth-email" class="auth-ltr" type="email" name="email" required maxlength="255" autocomplete="email" placeholder="name@example.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <div class="auth-field">
            <label for="auth-password">كلمة المرور</label>
            <div class="auth-input-wrap">
                <input id="auth-password" class="auth-ltr auth-password" type="password" name="password" required minlength="8" maxlength="200" autocomplete="new-password" placeholder="٨ أحرف على الأقل">
                <button type="button" class="auth-password-toggle" id="authPasswordToggle" aria-label="إظهار كلمة المرور">إظهار</button>
            </div>
            <div class="auth-strength" id="authStrength" data-level="0" role="meter" aria-label="قوة كلمة المرور" aria-valuemin="0" aria-valuemax="4" aria-valuenow="0">
                <span></span><span></span><span></span><span></span>
            </div>
            <p class="auth-strength-text" id="authStrengthText" aria-live="polite"></p>
        </div>

        <button type="submit" class="auth-submit">إنشاء الحساب</button>
    </form>
</section>
