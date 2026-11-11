<?php
$answer = $savedAnswer ?? [];
$reasons = ['A' => 'wrong_a_ar', 'B' => 'wrong_b_ar', 'C' => 'wrong_c_ar', 'D' => 'wrong_d_ar'];
$answerColumns = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
$chosenLetter = $answer['chosen'] ?? '';
$correctLetter = $answer['correct'] ?? '';
?>
<?php if ($answer): ?>
    <aside class="student-explanation" aria-label="شرح الإجابة">
        <div class="student-explanation__heading">
            <span class="student-explanation__icon" aria-hidden="true"><?= !empty($answer['is_correct']) ? '✓' : 'i' ?></span>
            <div>
                <h3><?= !empty($answer['is_correct']) ? 'إجابة صحيحة' : 'لنتعلم من هذا السؤال' ?></h3>
                <?php if ($correctLetter !== ''): ?><p>الإجابة الصحيحة: <b dir="ltr"><?= e($correctLetter) ?></b></p><?php endif; ?>
            </div>
        </div>
        <?php if ($chosenLetter !== '' && $chosenLetter !== $correctLetter && isset($answerColumns[$chosenLetter], $question[$answerColumns[$chosenLetter]])): ?>
            <p class="student-explanation__choice">اختيارك: <span dir="ltr"><?= e($chosenLetter) ?>. <?= e($question[$answerColumns[$chosenLetter]]) ?></span></p>
        <?php endif; ?>
        <?php if (!empty($answer['explanation_ar'])): ?>
            <p class="student-explanation__body"><?= e($answer['explanation_ar']) ?></p>
        <?php endif; ?>
        <?php
        $availableReasons = [];
        foreach ($reasons as $letter => $column) {
            if ($letter !== $correctLetter && !empty($answer[$column])) {
                $availableReasons[$letter] = $answer[$column];
            }
        }
        ?>
        <?php if ($availableReasons): ?>
            <details class="student-explanation__details">
                <summary>لماذا الخيارات الأخرى غير مناسبة؟</summary>
                <ul>
                    <?php foreach ($availableReasons as $letter => $reason): ?>
                        <li><b dir="ltr"><?= e($letter) ?>.</b> <?= e($reason) ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>
    </aside>
<?php endif; ?>
