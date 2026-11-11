<section class="adm-page">
    <header class="adm-page-header">
        <h2 class="text-2xl font-bold text-ink">إعدادات المنصة</h2>
        <p class="mt-1 text-muted">تؤثر هذه الإعدادات في تجربة الطلاب والحدود المعتمدة للمزايا.</p>
    </header>

    <form method="post" action="<?= e(url('/admin/settings')) ?>" class="card space-y-6 p-5 sm:p-7">
        <?= csrfField() ?>
        <label class="flex items-start gap-3">
            <input type="checkbox" name="default_explanation" value="1" <?= $settings['default_explanation'] === '1' ? 'checked' : '' ?> class="mt-1 accent-green-700">
            <span><span class="block font-semibold text-ink">تفعيل الشرح الفوري افتراضياً</span><span class="mt-1 block text-sm text-muted">يمكن للطالب تغييره في أقسام الاستماع والقراءة والقواعد، بينما يبقى إلزامياً للمفردات.</span></span>
        </label>
        <label class="block text-sm font-medium">عدد أسئلة التحدي اليومي
            <input type="number" name="daily_challenge_size" min="1" max="20" required value="<?= e((string) $settings['daily_challenge_size']) ?>" class="mt-2 block w-full max-w-sm rounded-xl border border-border p-3">
        </label>
        <label class="block text-sm font-medium">عدد أسئلة اختبار تحديد المستوى
            <input type="number" name="placement_size" min="5" max="100" required value="<?= e((string) $settings['placement_size']) ?>" class="mt-2 block w-full max-w-sm rounded-xl border border-border p-3">
        </label>
        <label class="block text-sm font-medium">حد المهارة الضعيفة (نسبة مئوية)
            <input type="number" name="weak_skill_threshold" min="1" max="99" required value="<?= e((string) $settings['weak_skill_threshold']) ?>" class="mt-2 block w-full max-w-sm rounded-xl border border-border p-3">
        </label>
        <div class="border-t border-border pt-5">
            <button class="rounded-xl bg-primary px-6 py-3 font-semibold text-white shadow-sm hover:bg-primaryDark">حفظ الإعدادات</button>
        </div>
    </form>
</section>
