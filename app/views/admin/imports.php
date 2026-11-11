<?php
$previewPath = (string) ($_SESSION['pdf_import_preview'] ?? '');
$previewFile = $previewPath !== ''
    && is_file($previewPath)
    && str_starts_with(realpath($previewPath) ?: '', realpath(ROOT_PATH . '/storage/imports') ?: ROOT_PATH)
    ? $previewPath
    : '';
$preview = $previewFile !== ''
    ? json_decode((string) file_get_contents($previewFile), true)
    : null;
$preview = is_array($preview) ? $preview : null;
?>
<section class="import-page">
    <header class="import-hero">
        <span class="import-hero-icon" aria-hidden="true">
            <svg class="ic"><use href="#a-upload"/></svg>
        </span>
        <div>
            <span class="adm-eyebrow">إدارة المحتوى</span>
            <h2>استيراد المحتوى من مكان واحد</h2>
            <p>ارفع ملفات PDF لاستخراج الأسئلة والمفردات، أو استورد بنك المفردات من ملف CSV — من مكان واحد.</p>
        </div>
        <span class="import-hero-count"><i aria-hidden="true"></i> مساحة استيراد موحّدة</span>
    </header>

    <section class="import-type-grid" aria-label="أنواع الاستيراد">
        <article class="import-type-card">
            <div class="import-type-head">
                <span class="import-type-icon" aria-hidden="true"><svg class="ic"><use href="#a-file"/></svg></span>
                <div>
                    <h2>استيراد من PDF</h2>
                    <p>استخرج الأسئلة أو المفردات، راجعها قبل نشرها.</p>
                </div>
                <span class="import-type-badge">تحليل ومعاينة</span>
            </div>
            <form method="post" action="<?= e(url('/admin/imports/preview')) ?>" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <label class="import-drop">
                    <input type="file" name="pdf" accept="application/pdf,.pdf" required aria-label="اختر ملف PDF" data-import-file>
                    <span class="import-drop-icon">PDF</span>
                    <strong data-selected-name>اختر ملف PDF من جهازك</strong>
                    <small>حتى 15 ميجابايت · ستراجع العناصر والتكرارات قبل النشر</small>
                </label>
                <label class="import-drop import-drop--audio">
                    <input type="file" name="audio_files[]" accept=".mp3,.wav,.m4a,.ogg,audio/*" multiple aria-label="ارفق ملفات صوتية اختيارية" data-import-file>
                    <span class="import-drop-icon">AUDIO</span>
                    <strong data-selected-name>إرفاق ملفات استماع (اختياري)</strong>
                    <small>MP3 أو WAV أو M4A أو OGG · حتى 30 ميجابايت للملف</small>
                </label>
                <div class="adm-actions">
                    <button class="adm-btn adm-btn--accent" type="submit">
                        <svg class="ic ic-sm" aria-hidden="true"><use href="#a-upload"/></svg>
                        تحليل ومعاينة PDF
                    </button>
                </div>
            </form>
        </article>

        <article class="import-type-card">
            <div class="import-type-head">
                <span class="import-type-icon" aria-hidden="true"><svg class="ic"><use href="#a-book"/></svg></span>
                <div>
                    <h2>استيراد المفردات من CSV</h2>
                    <p>أضف مجموعة كلمات إلى بنك المفردات دفعة واحدة.</p>
                </div>
                <span class="import-type-badge">حتى 1000 صف</span>
            </div>
            <p class="import-csv-note">رتّب الأعمدة بهذا التسلسل. يمكن أن يحتوي الصف الأول على أسماء الأعمدة.</p>
            <ol class="import-csv-columns" aria-label="ترتيب أعمدة ملف CSV">
                <li>word</li>
                <li>meaning_ar</li>
                <li>synonym</li>
                <li>antonym</li>
                <li>example</li>
            </ol>
            <form method="post" action="<?= e(url('/admin/imports/vocabulary')) ?>" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <label class="import-drop import-csv-drop">
                    <input type="file" name="csv" accept=".csv,text/csv" required aria-label="اختر ملف CSV للمفردات" data-import-file>
                    <span class="import-drop-icon">CSV</span>
                    <strong data-selected-name>اختر ملف المفردات</strong>
                    <small>CSV بحد أقصى 2 ميجابايت · الترميز UTF-8</small>
                </label>
                <div class="adm-actions">
                    <button class="adm-btn adm-btn--accent" type="submit">
                        <svg class="ic ic-sm" aria-hidden="true"><use href="#a-upload"/></svg>
                        استيراد المفردات
                    </button>
                </div>
            </form>
        </article>
    </section>

    <section class="import-workflow" aria-label="خطوات الاستيراد">
        <div class="import-workflow-step"><b>١</b><span><strong>اختر صيغة الملف</strong><small>PDF للمحتوى، أو CSV للمفردات.</small></span></div>
        <div class="import-workflow-step"><b>٢</b><span><strong>راجع البيانات</strong><small>تظهر معاينة PDF قبل النشر إلى المنصة.</small></span></div>
        <div class="import-workflow-step"><b>٣</b><span><strong>تابع النتيجة</strong><small>تظهر عمليات الاستيراد المكتملة في السجل.</small></span></div>
    </section>

    <?php if ($preview): ?>
        <?php $items = $preview['items'] ?? []; ?>
        <form method="post" action="<?= e(url('/admin/imports/publish')) ?>" class="adm-panel import-preview" id="import-preview">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <div class="adm-panel-head">
                <div>
                    <span class="adm-panel-kicker">الخطوة التالية · مراجعة المحتوى المستخرج</span>
                    <h2><?= e((string) ($preview['name'] ?? 'ملف PDF')) ?></h2>
                </div>
                <span class="status-pill status-pill--neutral"><?= count($items) ?> عنصر</span>
            </div>

            <?php if (($preview['type'] ?? '') === 'vocab'): ?>
                <div class="adm-grid-2 import-destinations">
                    <label class="adm-field">قسم المفردات
                        <select name="section_id" required>
                            <option value="">اختر القسم...</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?= (int) $section['id'] ?>" <?= mb_stripos((string) $section['name_ar'], 'مفرد') !== false ? 'selected' : '' ?>>
                                    <?= e($section['name_ar']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="adm-field">التوزيع المقترح
                        <div class="status-pill status-pill--neutral">بنك المفردات · بلا تكرار</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="adm-grid-2 import-destinations">
                    <label class="adm-field">التدريب الوجهة
                        <select name="practice_id" required>
                            <option value="">اختر التدريب...</option>
                            <?php foreach ($practices as $practice): ?>
                                <option value="<?= (int) $practice['id'] ?>"
                                    <?= (($preview['type'] ?? '') === 'questions' && mb_stripos((string) $practice['section_name'], 'استماع') !== false) ? 'selected' : '' ?>>
                                    <?= e($practice['section_name'] . ' — ' . $practice['title_ar']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="adm-field">المهارة
                        <select name="skill_id" required>
                            <option value="">اختر المهارة...</option>
                            <?php foreach ($skills as $skill): ?>
                                <option value="<?= (int) $skill['id'] ?>"
                                    <?= mb_stripos((string) $skill['name_ar'], 'تفصيلي') !== false ? 'selected' : '' ?>>
                                    <?= e($skill['name_ar']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            <?php endif; ?>

            <?php if (!empty($preview['media_links'])): ?>
                <div class="import-media-links">
                    <strong>روابط صوت اكتشفت داخل الملف</strong>
                    <?php foreach ($preview['media_links'] as $mediaLink): ?>
                        <a href="<?= e((string) $mediaLink) ?>" target="_blank" rel="noopener"><?= e((string) $mediaLink) ?></a>
                    <?php endforeach; ?>
                    <small>تُجلب الملفات الصوتية من Google Drive فقط إذا كانت متاحة للعامة.</small>
                </div>
            <?php endif; ?>

            <?php if (($preview['type'] ?? '') === 'questions' && !empty($preview['audio_files'])): ?>
                <div class="import-audio-map">
                    <div class="adm-panel-head">
                        <div><span class="adm-panel-kicker">ربط الصوتيات</span><h2>اختر المقطع لكل تسجيل</h2></div>
                    </div>
                    <?php $groups = array_values(array_unique(array_filter(array_map(static fn ($item) => trim((string) ($item['group'] ?? '')), $items)))); ?>
                    <?php foreach (($preview['audio_files'] ?? []) as $audioIndex => $audioFile): ?>
                        <div class="import-audio-row">
                            <div><strong><?= e((string) $audioFile['name']) ?></strong><small>ملف استماع مرفوع</small></div>
                            <label class="adm-field">
                                المقطع
                                <select name="audio_passage_<?= (int) $audioIndex ?>">
                                    <option value="">بدون ربط</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= e($group) ?>" <?= mb_stripos((string) $audioFile['name'], (string) substr($group, 0, 8)) !== false ? 'selected' : '' ?>>
                                            <?= e($group) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <audio controls preload="metadata" src="<?= e(asset((string) $audioFile['path'])) ?>"></audio>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="import-items">
                <?php foreach (array_values($items) as $previewIndex => $item): ?>
                    <?php $itemIndex = isset($item['index']) ? (int) $item['index'] : $previewIndex; ?>
                    <?php $duplicate = ($item['duplicate_file'] ?? false) || ($item['duplicate_db'] ?? false); ?>
                    <label class="import-item <?= $duplicate ? 'is-duplicate' : '' ?>">
                        <input type="checkbox" name="publish[]" value="<?= $itemIndex ?>" <?= $duplicate ? 'disabled' : 'checked' ?>>
                        <span>
                            <strong><?= ($preview['type'] ?? '') === 'vocab' ? e((string) $item['word']) : e((string) $item['text']) ?></strong>
                            <?php if (($preview['type'] ?? '') === 'vocab'): ?><small><?= e((string) $item['meaning_ar']) ?></small><?php endif; ?>
                        </span>
                        <?php if ($item['duplicate_db'] ?? false): ?><em>مكرر في قاعدة البيانات</em>
                        <?php elseif ($item['duplicate_file'] ?? false): ?><em>مكرر داخل الملف</em>
                        <?php else: ?><em>جديد · ثقة <?= (int) ($item['confidence'] ?? 0) ?>%</em><?php endif; ?>
                    </label>
                <?php endforeach; ?>
                <?php if (!$items): ?><div class="adm-empty">لم يتم التعرف على عناصر واضحة. تحقق أن ملف PDF يحتوي نصاً قابلاً للاستخراج.</div><?php endif; ?>
            </div>
            <?php if ($items): ?><div class="adm-actions adm-actions--end"><button class="adm-btn adm-btn--accent" type="submit">تأكيد استيراد العناصر المحددة</button></div><?php endif; ?>
        </form>
    <?php endif; ?>

    <article class="adm-panel">
        <div class="adm-panel-head">
            <div class="import-history-title">
                <div><span class="adm-panel-kicker">متابعة العمليات</span><h2>سجل الاستيراد</h2></div>
                <span class="import-history-count"><?= count($imports) ?></span>
            </div>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead><tr><th>الملف</th><th>النوع</th><th>تم العثور</th><th>تمت الإضافة</th><th>المكررات</th><th>الفشل</th><th>التاريخ</th></tr></thead>
                <tbody>
                <?php foreach ($imports as $import): ?>
                    <tr>
                        <td><?= e($import['filename']) ?></td>
                        <td><?= e(($import['type'] ?? '') === 'vocabulary-csv' ? 'مفردات CSV' : (($import['type'] ?? '') === 'vocab' ? 'مفردات PDF' : (($import['type'] ?? '') === 'questions' ? 'أسئلة PDF' : 'محتوى PDF'))) ?></td>
                        <td><?= (int) $import['items_found'] ?></td>
                        <td><?= (int) $import['items_inserted'] ?></td>
                        <td><?= (int) $import['duplicates_skipped'] ?></td>
                        <td><?= (int) $import['failed'] ?></td>
                        <td><?= e(date('Y-m-d H:i', strtotime((string) $import['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$imports): ?><tr><td colspan="7"><div class="adm-empty">ستظهر هنا عمليات استيراد PDF وCSV بعد تنفيذها.</div></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
