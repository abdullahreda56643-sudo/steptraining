<?php
$tipTitle = $tipTitle ?? 'نصيحة استراتيجية';
$tipText = trim((string) ($tipText ?? ''));
?>
<aside class="student-tip" aria-label="<?= e($tipTitle) ?>">
    <span class="student-tip__icon" aria-hidden="true">✦</span>
    <div>
        <h2><?= e($tipTitle) ?></h2>
        <p><?= e($tipText !== '' ? $tipText : 'اقرأ المطلوب بعناية، استبعد الخيارات غير المناسبة، ثم راجع سبب اختيارك لتتعلم من كل سؤال.') ?></p>
    </div>
</aside>
