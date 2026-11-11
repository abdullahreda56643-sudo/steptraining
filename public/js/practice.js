(() => {
    const app = document.getElementById('practice-app');
    if (!app || !app.dataset.attemptId) return;

    const attemptId = Number(app.dataset.attemptId);
    const csrfToken = app.dataset.csrfToken;
    const total = Number(app.dataset.total);
    const requiresAllAnswers = app.dataset.requiresAll === 'true';
    let showExplanation = app.dataset.showExplanation === 'true';
    const progressCount = document.getElementById('practice-progress-count');
    const progressBar = document.getElementById('practice-progress-bar');
    const errorBox = document.getElementById('practice-error');
    const finishButton = document.getElementById('finish-practice');
    const resultDialog = document.getElementById('practice-result');
    let answered = app.querySelectorAll('[data-answered="true"]').length;
    let finishing = false;
    const explanationToggle = document.getElementById('explanation-toggle');
    const explanationStatus = document.getElementById('explanation-toggle-status');

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const clearError = () => {
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    };

    const request = async (url, body) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(body)
        });

        const payload = await response.json();
        if (!response.ok || !payload.ok) {
            throw new Error(payload.error || 'تعذر إكمال الطلب. حاول مرة أخرى.');
        }

        return payload.data;
    };

    const setProgress = () => {
        if (progressCount) progressCount.textContent = String(answered);
        if (progressBar) {
            const percentage = total ? Math.round((answered / total) * 100) : 0;
            progressBar.style.width = `${percentage}%`;
            progressBar.parentElement.setAttribute('aria-valuenow', String(answered));
        }
        if (finishButton && !finishing) {
            const needsMoreAnswers = requiresAllAnswers && answered < total;
            finishButton.disabled = needsMoreAnswers;
            finishButton.textContent = needsMoreAnswers
                ? 'أجب عن جميع الأسئلة لإكمال الاختبار'
                : 'إنهاء التدريب وعرض النتيجة';
        }
    };

    const addExplanation = (card, data) => {
        const box = card.querySelector('.practice-feedback');
        box.replaceChildren();
        box.className = 'student-question-feedback practice-feedback is-visible';
        box.classList.add(data.is_correct ? 'is-correct' : 'is-wrong');

        const heading = document.createElement('p');
        heading.className = 'font-semibold text-primary';
        heading.textContent = data.is_correct ? 'إجابة صحيحة' : `الإجابة الصحيحة: ${data.correct}`;
        box.appendChild(heading);

        if (data.explanation) {
            const explanation = document.createElement('p');
            explanation.className = 'mt-2 leading-7 text-ink';
            explanation.textContent = data.explanation;
            box.appendChild(explanation);
        }

        if (data.wrong_reasons) {
            const reasons = document.createElement('ul');
            reasons.className = 'mt-3 space-y-2 text-sm text-muted';
            card.querySelectorAll('.practice-option').forEach((option) => {
                const letter = option.dataset.choice;
                const reason = data.wrong_reasons[letter];
                if (letter === data.correct || !reason) return;

                const item = document.createElement('li');
                const optionText = option.querySelector('span:last-child')?.textContent.trim() || '';
                item.textContent = `${letter}. ${optionText}: ${reason}`;
                reasons.appendChild(item);
            });
            if (reasons.childElementCount > 0) {
                box.appendChild(reasons);
            }
        }

        box.classList.remove('hidden');
    };

    app.querySelectorAll('.practice-option').forEach((button) => {
        button.addEventListener('click', async () => {
            const card = button.closest('[data-question-id]');
            if (card.dataset.answered === 'true') return;

            clearError();
            const options = card.querySelectorAll('.practice-option');
            options.forEach((option) => { option.disabled = true; });

            try {
                const data = await request(app.dataset.submitUrl, {
                    _csrf: csrfToken,
                    attempt_id: attemptId,
                    question_id: Number(card.dataset.questionId),
                    choice: button.dataset.choice
                });

                card.dataset.answered = 'true';
                answered += 1;
                options.forEach((option) => {
                    const selected = option.dataset.choice === button.dataset.choice;
                    const correct = option.dataset.choice === data.correct;
                    option.setAttribute('aria-pressed', String(selected));
                    option.classList.remove('hover:border-primary', 'hover:bg-[#F7FAF8]');
                    if (correct) {
                        option.classList.add('is-correct');
                        option.classList.add('border-green-500', 'bg-green-50', 'text-green-800');
                    } else if (selected) {
                        option.classList.add('is-wrong');
                        option.classList.add('border-red-400', 'bg-red-50', 'text-red-800');
                    }
                });

                const questionLink = app.querySelector(`.student-question-map a[href="#question-${card.dataset.questionId}"]`);
                questionLink?.classList.add('is-answered');
                if (questionLink) {
                    questionLink.setAttribute('aria-label', `السؤال ${Number(questionLink.textContent)}، تمت الإجابة`);
                }

                if (showExplanation) {
                    addExplanation(card, data);
                } else {
                    const feedback = card.querySelector('.practice-feedback');
                    feedback.textContent = data.is_correct ? 'إجابة صحيحة.' : `الإجابة الصحيحة: ${data.correct}`;
                    feedback.classList.remove('hidden');
                    feedback.classList.add('is-visible', data.is_correct ? 'is-correct' : 'is-wrong');
                }

                setProgress();
            } catch (error) {
                options.forEach((option) => { option.disabled = false; });
                showError(error.message);
            }
        });
    });

    explanationToggle?.addEventListener('change', async () => {
        const enabled = explanationToggle.checked;
        explanationToggle.disabled = true;
        if (explanationStatus) explanationStatus.textContent = 'جارٍ حفظ الإعداد...';
        clearError();
        try {
            await request(app.dataset.toggleExplanationUrl, {
                _csrf: csrfToken,
                enabled
            });
            showExplanation = enabled;
            window.location.reload();
        } catch (error) {
            explanationToggle.checked = !enabled;
            explanationToggle.disabled = false;
            if (explanationStatus) explanationStatus.textContent = '';
            showError(error.message);
        }
    });

    finishButton?.addEventListener('click', async () => {
        if (finishing) return;
        clearError();
        finishing = true;
        finishButton.disabled = true;
        finishButton.textContent = 'جارٍ حفظ النتيجة...';

        try {
            const data = await request(app.dataset.finishUrl, {
                _csrf: csrfToken,
                attempt_id: attemptId
            });
            const score = document.getElementById('practice-result-score');
            const band = document.getElementById('practice-result-band');
            const gap = document.getElementById('practice-result-gap');
            const title = document.getElementById('practice-result-title');
            const link = document.getElementById('practice-result-link');
            const streak = document.getElementById('practice-result-streak');
            if (data.placement) {
                title.textContent = 'نتيجة اختبار تحديد المستوى';
                score.textContent = `${data.accuracy}%`;
                band.textContent = `مستواك المبدئي: ${data.placement.level}`;
                gap.textContent = data.placement.plan.join(' ');
                link.href = app.dataset.placementUrl;
                link.textContent = 'عرض خطة المستوى';
            } else {
                score.textContent = `${data.prediction.score} / 100`;
                band.textContent = `${data.prediction.band} — دقة إجاباتك ${data.accuracy}%`;
                gap.textContent = `تبقّى ${data.prediction.gap} درجة للوصول إلى الشريحة التالية.`;
                if (data.attempt_type === 'daily') {
                    link.href = app.dataset.dailyUrl;
                    link.textContent = 'العودة للتحدي اليومي';
                }
            }
            if (data.streak) {
                streak.textContent = `سلسلة النشاط: ${data.streak.current_streak} يوم`;
            }
            document.dispatchEvent(new Event('step:practice-finished'));
            resultDialog.showModal();
        } catch (error) {
            finishing = false;
            setProgress();
            showError(error.message);
        }
    });

    document.getElementById('close-result')?.addEventListener('click', () => resultDialog.close());

    setProgress();
})();