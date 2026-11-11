<?php
$user = Auth::user();
$currentStreak = 0;
if ($user) {
    $currentStreak = (int) Streak::forUser((int) $user['id'])['current_streak'];
}
$studentLinks = [
    ['/', 'الرئيسية'],
    ['/daily', 'التحدي اليومي'],
    ['/analysis', 'تحليل الأداء'],
    ['/mistakes', 'مراجعة الأخطاء'],
    ['/placement', 'تحديد المستوى'],
    ['/vocab', 'المفردات'],
];
$currentPath = rtrim((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/');
$currentPath = $currentPath !== '' ? $currentPath : '/';
?>

<nav class="student-site-nav" aria-label="التنقل الرئيسي">
    <div class="student-site-nav__main">
        <div class="student-site-nav__brand">
            <a href="<?= e(url('/')) ?>" aria-label="STEP Smart Practice، الرئيسية">
                <span class="student-site-nav__mark" aria-hidden="true">S</span>
                <span><b>STEP</b><small>Smart Practice</small></span>
            </a>
        </div>
        <?php if (($user['role'] ?? '') === 'student'): ?>
            <div class="student-site-nav__links" aria-label="روابط الطالب">
                <?php foreach ($studentLinks as [$path, $label]): ?>
                    <?php
                    $linkPath = rtrim((string) (parse_url(url($path), PHP_URL_PATH) ?: '/'), '/');
                    $linkPath = $linkPath !== '' ? $linkPath : '/';
                    ?>
                    <a href="<?= e(url($path)) ?>"<?= $currentPath === $linkPath ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="student-site-nav__account">
            <?php if ($user && ($user['role'] ?? '') === 'student'): ?>
                <span class="student-site-nav__streak" title="أيام الالتزام المتتالية">🔥 <?= e((string) $currentStreak) ?></span>
            <?php endif; ?>
            <?php if ($user): ?>
                <div class="student-site-nav__user">
                    <span class="student-site-nav__avatar" aria-hidden="true"><?= e(mb_substr(($user['name'] ?? 'مستخدم'), 0, 1, 'UTF-8')) ?></span>
                    <span class="student-site-nav__user-name"><b><?= e($user['name'] ?? 'زائر') ?></b><small><?= ($user['role'] ?? '') === 'admin' ? 'مشرف' : 'طالب' ?></small></span>
                </div>
                <a class="student-site-nav__logout" href="<?= e(url('/logout')) ?>">خروج</a>
            <?php else: ?>
                <a class="student-site-nav__login" href="<?= e(url('/login')) ?>">تسجيل الدخول</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if (($user['role'] ?? '') === 'student'): ?>
        <div class="student-site-nav__mobile-links" aria-label="روابط الطالب">
            <?php foreach ($studentLinks as [$path, $label]): ?>
                <?php
                $linkPath = rtrim((string) (parse_url(url($path), PHP_URL_PATH) ?: '/'), '/');
                $linkPath = $linkPath !== '' ? $linkPath : '/';
                ?>
                <a href="<?= e(url($path)) ?>"<?= $currentPath === $linkPath ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</nav>
