INSERT INTO settings (`key`, value) VALUES
('weak_skill_threshold', '60'),
('default_explanation', '1'),
('daily_challenge_size', '5'),
('placement_size', '15');

INSERT INTO sections (slug, name_en, name_ar, tip_text, sort_order) VALUES
('listening', 'Listening', 'استماع', 'ركز على الفكرة العامة وتحديد التفاصيل المهمة أثناء الاستماع.', 1),
('reading', 'Reading', 'قراءة', 'اقرأ الفقرة أولاً ثم اقرأ الأسئلة بسرعة مع أوضح التفاصيل.', 2),
('grammar', 'Grammar', 'قواعد', 'راجع القواعد الأساسية وراقب أخطاء التوترات والأدوات.', 3),
('vocabulary', 'Vocabulary', 'مفردات', 'تعلم الكلمات في سياق جملة لتثبيت المعنى بسرعة.', 4);

INSERT INTO skills (name_en, name_ar, section_id) VALUES
('Tenses', 'الأزمنة', 3),
('Prepositions', 'حروف الجر', 3),
('Conjunctions', 'أدوات الربط', 2),
('Main Idea', 'الفكرة الرئيسة', 2),
('Inference', 'الاستنتاج', 2),
('Synonyms', 'المتطابقات', 4),
('Detail', 'معلومة تفصيلية', 1);

INSERT INTO users (name, email, password_hash, role, is_active) VALUES
('Admin', 'admin@stepdemo.com', '$2y$10$P1Oftqc6zHE5UvX2ftBG1eM0A4hvVmBupeA.Rc16nq2bda/Z7VB1C', 'admin', 1);

INSERT INTO practices (section_id, title_en, title_ar, sort_order, is_active, time_limit_min) VALUES
(1, 'Model 1', 'تدريب 1', 1, 1, 20),
(1, 'Model 2', 'تدريب 2', 2, 1, 20),
(2, 'Model 1', 'تدريب 1', 1, 1, 25),
(3, 'Model 1', 'تدريب 1', 1, 1, 18),
(4, 'Model 1', 'تدريب 1', 1, 1, 15);
