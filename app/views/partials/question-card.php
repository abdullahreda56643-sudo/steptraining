<?php
$questionIndex = (int) ($questionIndex ?? 0);
$savedAnswer = $savedAnswer ?? null;
$answerColumns = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
$isAnswered = (bool) $savedAnswer;
?>
<article id="question-<?= e((string) $question['id']) ?>"
         class="student-question-card"
         data-question-id="<?= e((string) $question['id']) ?>"
         data-answered="<?= $isAnswered ? 'true' : 'false' ?>">
    <div class="student-question-card__meta">
        <span class="student-question-card__number">السؤال <?= e((string) ($questionIndex + 1)) ?><small> / <?= e((string) ($questionTotal ?? '')) ?></small></span>
        <?php if (!empty($question['skill_name_ar'])): ?><span class="student-question-card__skill"><?= e($question['skill_name_ar']) ?></span><?php endif; ?>
    </div>
    <h2 class="student-question-card__prompt" dir="ltr"><?= e($question['text']) ?></h2>
    <div class="student-question-options" role="group" aria-label="خيارات السؤال <?= e((string) ($questionIndex + 1)) ?>" dir="ltr">
        <?php foreach ($answerColumns as $letter => $column): ?>
            <?php
            $isChosen = $savedAnswer && ($savedAnswer['chosen'] ?? '') === $letter;
            $isCorrectOption = $savedAnswer && ($savedAnswer['correct'] ?? '') === $letter;
            $optionClass = 'student-question-option';
            if ($isCorrectOption) {
                $optionClass .= ' is-correct';
            } elseif ($isChosen && empty($savedAnswer['is_correct'])) {
                $optionClass .= ' is-wrong';
            }
            ?>
            <button type="button"
                    class="<?= e($optionClass) ?> practice-option"
                    data-choice="<?= e($letter) ?>"
                    aria-pressed="<?= $isChosen ? 'true' : 'false' ?>"
                    <?= $isAnswered ? 'disabled' : '' ?>>
                <span class="student-question-option__letter"><?= e($letter) ?></span>
                <span class="student-question-option__text"><?= e($question[$column]) ?></span>
                <?php if ($isCorrectOption): ?><span class="student-question-option__result" aria-label="الإجابة الصحيحة">✓</span><?php elseif ($isChosen && empty($savedAnswer['is_correct'])): ?><span class="student-question-option__result" aria-label="إجابتك">×</span><?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>
    <?php if ($isAnswered && !empty($showExplanation)): ?>
        <?php include ROOT_PATH . '/app/views/partials/explanation-box.php'; ?>
    <?php endif; ?>
    <div class="student-question-feedback practice-feedback hidden" aria-live="polite"></div>
    <?php if ($isAnswered): ?>
        <p class="student-question-saved <?= !empty($savedAnswer['is_correct']) ? 'is-correct' : 'is-wrong' ?>" role="status">
            <?= !empty($savedAnswer['is_correct'])
                ? 'إجابة صحيحة، تم حفظها.'
                : (!empty($showExplanation)
                    ? 'إجابة غير صحيحة، راجع الشرح أدناه.'
                    : 'إجابة غير صحيحة، فعّل خيار الشرح التفصيلي أعلى الصفحة لمراجعتها.') ?>
        </p>
    <?php endif; ?>
</article>
