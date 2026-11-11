<?php $resultLink = $resultLink ?? url('/analysis'); ?>
<dialog id="practice-result" class="student-result-dialog" aria-labelledby="practice-result-title" aria-describedby="practice-result-band">
    <div class="student-result-dialog__content">
        <span class="student-result-dialog__badge" aria-hidden="true">✓</span>
        <p class="student-page-kicker">إنجاز جديد</p>
        <h2 id="practice-result-title">اكتمل التدريب</h2>
        <p id="practice-result-score" class="student-result-dialog__score" aria-live="polite"></p>
        <p id="practice-result-band" class="student-result-dialog__band"></p>
        <p id="practice-result-gap" class="student-result-dialog__plan"></p>
        <p id="practice-result-streak" class="student-result-dialog__streak"></p>
        <div class="student-result-dialog__actions">
            <a id="practice-result-link" href="<?= e($resultLink) ?>">متابعة إلى التقرير <span aria-hidden="true">←</span></a>
            <button type="button" id="close-result">العودة إلى الأسئلة</button>
        </div>
    </div>
</dialog>
