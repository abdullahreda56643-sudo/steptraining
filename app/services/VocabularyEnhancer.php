<?php

declare(strict_types=1);

class VocabularyEnhancer
{
    private const LEXICON = [
        'abandon' => ['meaning_ar' => 'يتخلى عن', 'synonym' => 'leave, desert', 'antonym' => 'keep, preserve', 'example' => 'They had to abandon the old building.'],
        'accomplish' => ['meaning_ar' => 'ينجز أو يحقق', 'synonym' => 'achieve, complete', 'antonym' => 'fail, abandon', 'example' => 'She will accomplish her goals with hard work.'],
        'acidic' => ['meaning_ar' => 'حامضي أو سام', 'synonym' => 'acid, sour', 'antonym' => 'alkaline, basic', 'example' => 'Lemon juice has an acidic taste.'],
        'ability' => ['meaning_ar' => 'قدرة', 'synonym' => 'capacity, skill', 'antonym' => 'inability', 'example' => 'She has the ability to solve difficult problems.'],
        'achieve' => ['meaning_ar' => 'يحقق', 'synonym' => 'accomplish, attain', 'antonym' => 'fail', 'example' => 'Students can achieve their goals with practice.'],
        'accurate' => ['meaning_ar' => 'دقيق', 'synonym' => 'correct, precise', 'antonym' => 'inaccurate', 'example' => 'The report contains accurate information.'],
        'advantage' => ['meaning_ar' => 'ميزة', 'synonym' => 'benefit, gain', 'antonym' => 'disadvantage', 'example' => 'Good planning gives us an advantage.'],
        'ancient' => ['meaning_ar' => 'قديم عريق', 'synonym' => 'old, historic', 'antonym' => 'modern', 'example' => 'The city has many ancient monuments.'],
        'appear' => ['meaning_ar' => 'يظهر', 'synonym' => 'seem, emerge', 'antonym' => 'disappear', 'example' => 'A storm appeared on the horizon.'],
        'aquatic' => ['meaning_ar' => 'مائي', 'synonym' => 'marine, water-based', 'antonym' => 'terrestrial', 'example' => 'Aquatic plants grow in water.'],
        'assist' => ['meaning_ar' => 'يساعد', 'synonym' => 'help, support', 'antonym' => 'hinder', 'example' => 'The guide will assist visitors at the museum.'],
        'avoid' => ['meaning_ar' => 'يتجنب', 'synonym' => 'prevent, evade', 'antonym' => 'encounter', 'example' => 'Avoid using your phone while driving.'],
        'benefit' => ['meaning_ar' => 'فائدة', 'synonym' => 'advantage, gain', 'antonym' => 'harm', 'example' => 'Regular exercise has many benefits.'],
        'brief' => ['meaning_ar' => 'موجز قصير', 'synonym' => 'concise, short', 'antonym' => 'lengthy', 'example' => 'The manager gave a brief update.'],
        'careful' => ['meaning_ar' => 'حريص', 'synonym' => 'cautious, attentive', 'antonym' => 'careless', 'example' => 'Be careful when crossing the road.'],
        'cause' => ['meaning_ar' => 'سبب', 'synonym' => 'reason, origin', 'antonym' => 'effect', 'example' => 'Scientists investigated the cause of the problem.'],
        'choose' => ['meaning_ar' => 'يختار', 'synonym' => 'select, pick', 'antonym' => 'reject', 'example' => 'You may choose either option.'],
        'complete' => ['meaning_ar' => 'يكمل أو كامل', 'synonym' => 'finish, fulfill', 'antonym' => 'incomplete', 'example' => 'Please complete the form carefully.'],
        'complex' => ['meaning_ar' => 'معقد', 'synonym' => 'complicated, intricate', 'antonym' => 'simple', 'example' => 'The machine has a complex design.'],
        'conflict' => ['meaning_ar' => 'نزاع أو صراع', 'synonym' => 'disagreement, clash', 'antonym' => 'agreement', 'example' => 'The two groups resolved their conflict.'],
        'create' => ['meaning_ar' => 'ينشئ أو يبتكر', 'synonym' => 'make, produce', 'antonym' => 'destroy', 'example' => 'Artists create original works.'],
        'damage' => ['meaning_ar' => 'ضرر', 'synonym' => 'harm, injury', 'antonym' => 'repair', 'example' => 'The storm caused damage to several houses.'],
        'difficult' => ['meaning_ar' => 'صعب', 'synonym' => 'hard, challenging', 'antonym' => 'easy', 'example' => 'The final question was difficult.'],
        'discover' => ['meaning_ar' => 'يكتشف', 'synonym' => 'find, detect', 'antonym' => 'conceal', 'example' => 'Researchers discovered a new species.'],
        'efficient' => ['meaning_ar' => 'فعال ومجد', 'synonym' => 'effective, productive', 'antonym' => 'inefficient', 'example' => 'The new system is efficient and fast.'],
        'enormous' => ['meaning_ar' => 'ضخم جدا', 'synonym' => 'huge, enormous', 'antonym' => 'tiny', 'example' => 'An enormous crowd gathered in the stadium.'],
        'essential' => ['meaning_ar' => 'ضروري أساسي', 'synonym' => 'necessary, vital', 'antonym' => 'unnecessary', 'example' => 'Water is essential for life.'],
        'expand' => ['meaning_ar' => 'يوسع أو يتوسع', 'synonym' => 'increase, enlarge', 'antonym' => 'reduce', 'example' => 'The company plans to expand overseas.'],
        'explain' => ['meaning_ar' => 'يشرح', 'synonym' => 'describe, clarify', 'antonym' => 'confuse', 'example' => 'The teacher explained the lesson clearly.'],
        'failure' => ['meaning_ar' => 'فشل', 'synonym' => 'defeat, collapse', 'antonym' => 'success', 'example' => 'The team learned from its failure.'],
        'frequent' => ['meaning_ar' => 'متكرر', 'synonym' => 'common, regular', 'antonym' => 'rare', 'example' => 'Frequent breaks can improve concentration.'],
        'goal' => ['meaning_ar' => 'هدف', 'synonym' => 'aim, objective', 'antonym' => '—', 'example' => 'Their goal is to finish the project on time.'],
        'gradually' => ['meaning_ar' => 'بالتدريج', 'synonym' => 'steadily, slowly', 'antonym' => 'suddenly', 'example' => 'The weather gradually became warmer.'],
        'improve' => ['meaning_ar' => 'يحسن', 'synonym' => 'enhance, develop', 'antonym' => 'worsen', 'example' => 'Reading regularly can improve your skills.'],
        'increase' => ['meaning_ar' => 'يزيد', 'synonym' => 'rise, grow', 'antonym' => 'decrease', 'example' => 'Prices increased during the year.'],
        'large' => ['meaning_ar' => 'كبير', 'synonym' => 'big, huge', 'antonym' => 'small', 'example' => 'They live in a large apartment.'],
        'method' => ['meaning_ar' => 'طريقة', 'synonym' => 'way, approach', 'antonym' => '—', 'example' => 'This method saves time and effort.'],
        'modern' => ['meaning_ar' => 'حديث', 'synonym' => 'contemporary, current', 'antonym' => 'ancient', 'example' => 'The library has a modern design.'],
        'necessary' => ['meaning_ar' => 'ضروري', 'synonym' => 'essential, needed', 'antonym' => 'optional', 'example' => 'A passport is necessary for travel.'],
        'obtain' => ['meaning_ar' => 'يحصل على', 'synonym' => 'get, acquire', 'antonym' => 'lose', 'example' => 'You can obtain more information online.'],
        'optional' => ['meaning_ar' => 'اختياري', 'synonym' => 'voluntary, discretionary', 'antonym' => 'required', 'example' => 'Attendance at the seminar is optional.'],
        'prevent' => ['meaning_ar' => 'يمنع', 'synonym' => 'stop, avert', 'antonym' => 'allow', 'example' => 'Locks help prevent theft.'],
        'provide' => ['meaning_ar' => 'يوفر', 'synonym' => 'supply, furnish', 'antonym' => 'withhold', 'example' => 'The school provides lunch for students.'],
        'rapid' => ['meaning_ar' => 'سريع', 'synonym' => 'quick, fast', 'antonym' => 'slow', 'example' => 'The town experienced rapid growth.'],
        'reduce' => ['meaning_ar' => 'يقلل', 'synonym' => 'decrease, lessen', 'antonym' => 'increase', 'example' => 'New policies aim to reduce waste.'],
        'require' => ['meaning_ar' => 'يتطلب', 'synonym' => 'need, demand', 'antonym' => 'provide', 'example' => 'This task requires careful attention.'],
        'result' => ['meaning_ar' => 'نتيجة', 'synonym' => 'outcome, consequence', 'antonym' => 'cause', 'example' => 'The experiment produced a clear result.'],
        'significant' => ['meaning_ar' => 'مهم أو ذو دلالة', 'synonym' => 'important, meaningful', 'antonym' => 'minor', 'example' => 'The discovery had a significant impact.'],
        'simple' => ['meaning_ar' => 'بسيط', 'synonym' => 'easy, basic', 'antonym' => 'complex', 'example' => 'The instructions are simple to follow.'],
        'success' => ['meaning_ar' => 'نجاح', 'synonym' => 'achievement, victory', 'antonym' => 'failure', 'example' => 'Hard work is the key to success.'],
        'support' => ['meaning_ar' => 'يدعم أو دعم', 'synonym' => 'assist, back', 'antonym' => 'oppose', 'example' => 'The community showed strong support.'],
        'tiny' => ['meaning_ar' => 'صغير جدا', 'synonym' => 'minute, very small', 'antonym' => 'huge', 'example' => 'A tiny bird sat on the branch.'],
        'useful' => ['meaning_ar' => 'مفيد', 'synonym' => 'helpful, practical', 'antonym' => 'useless', 'example' => 'The guide contains useful information.'],
        'vary' => ['meaning_ar' => 'يتنوع أو يختلف', 'synonym' => 'differ, change', 'antonym' => 'match', 'example' => 'Prices vary between stores.'],
        'widespread' => ['meaning_ar' => 'منتشر على نطاق واسع', 'synonym' => 'common, extensive', 'antonym' => 'local', 'example' => 'The practice is widespread in the region.'],
    ];

    public static function enrich(array $item): array
    {
        $word = trim((string) ($item['word'] ?? ''));
        $key = mb_strtolower($word, 'UTF-8');
        $entry = self::LEXICON[$key] ?? null;

        $item['word'] = $word;
        $item['meaning_ar'] = trim((string) ($item['meaning_ar'] ?? ''));
        $hasArabicMeaning = preg_match('/[\x{0600}-\x{06FF}]/u', $item['meaning_ar']) === 1;
        if ($entry && (!$hasArabicMeaning || $item['meaning_ar'] === '')) {
            $item['meaning_ar'] = $entry['meaning_ar'];
        }
        if (!$hasArabicMeaning && !$entry) {
            $item['meaning_ar'] = 'غير متوفر - يحتاج مراجعة';
        }

        $item['synonym'] = trim((string) ($item['synonym'] ?? ''));
        if ($item['synonym'] === '' && $entry) {
            $item['synonym'] = $entry['synonym'];
        }

        $item['antonym'] = trim((string) ($item['antonym'] ?? ''));
        if ($item['antonym'] === '' && $entry) {
            $item['antonym'] = $entry['antonym'] === '—' ? null : $entry['antonym'];
        }

        $item['example'] = trim((string) ($item['example'] ?? ''));
        if ($item['example'] === '' && $entry) {
            $item['example'] = $entry['example'];
        }
        return $item;
    }
}
