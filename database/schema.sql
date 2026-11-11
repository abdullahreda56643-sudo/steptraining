CREATE DATABASE IF NOT EXISTS `steptraining` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `steptraining`;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student','admin') NOT NULL DEFAULT 'student',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name_en VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NOT NULL,
    tip_text TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS practices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id INT UNSIGNED NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_ar VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    time_limit_min INT NULL,
    CONSTRAINT fk_practices_section FOREIGN KEY (section_id) REFERENCES sections(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NOT NULL,
    section_id INT UNSIGNED NULL,
    CONSTRAINT fk_skills_section FOREIGN KEY (section_id) REFERENCES sections(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS passages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    practice_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NULL,
    audio_path VARCHAR(255) NULL,
    CONSTRAINT fk_passages_practice FOREIGN KEY (practice_id) REFERENCES practices(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    practice_id INT UNSIGNED NOT NULL,
    passage_id INT UNSIGNED NULL,
    skill_id INT UNSIGNED NOT NULL,
    text TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    option_d TEXT NOT NULL,
    correct ENUM('A','B','C','D') NOT NULL,
    explanation_ar TEXT NOT NULL,
    wrong_a_ar TEXT NULL,
    wrong_b_ar TEXT NULL,
    wrong_c_ar TEXT NULL,
    wrong_d_ar TEXT NULL,
    in_placement TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_questions_practice FOREIGN KEY (practice_id) REFERENCES practices(id),
    CONSTRAINT fk_questions_passage FOREIGN KEY (passage_id) REFERENCES passages(id),
    CONSTRAINT fk_questions_skill FOREIGN KEY (skill_id) REFERENCES skills(id),
    INDEX idx_questions_practice_skill (practice_id, skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    practice_id INT UNSIGNED NULL,
    type ENUM('practice','mistakes','custom','placement','daily') NOT NULL DEFAULT 'practice',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP NULL,
    correct_count INT NOT NULL DEFAULT 0,
    total INT NOT NULL DEFAULT 0,
    predicted_score INT NULL,
    CONSTRAINT fk_attempts_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_attempts_user_finished (user_id, finished_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attempt_questions (
    attempt_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    position INT UNSIGNED NOT NULL,
    PRIMARY KEY (attempt_id, question_id),
    UNIQUE KEY uq_attempt_question_position (attempt_id, position),
    CONSTRAINT fk_attempt_questions_attempt FOREIGN KEY (attempt_id) REFERENCES attempts(id),
    CONSTRAINT fk_attempt_questions_question FOREIGN KEY (question_id) REFERENCES questions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attempt_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    chosen ENUM('A','B','C','D') NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attempt_answers_attempt FOREIGN KEY (attempt_id) REFERENCES attempts(id),
    CONSTRAINT fk_attempt_answers_question FOREIGN KEY (question_id) REFERENCES questions(id),
    CONSTRAINT uq_attempt_answer UNIQUE (attempt_id, question_id),
    INDEX idx_attempt_answers_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vocabulary (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(255) NOT NULL,
    meaning_ar TEXT NOT NULL,
    synonym VARCHAR(255) NULL,
    antonym VARCHAR(255) NULL,
    example TEXT NULL,
    section_id INT UNSIGNED NULL,
    practice_id INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_vocabulary_section FOREIGN KEY (section_id) REFERENCES sections(id),
    CONSTRAINT fk_vocabulary_practice FOREIGN KEY (practice_id) REFERENCES practices(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vocab_progress (
    user_id INT UNSIGNED NOT NULL,
    vocab_id INT UNSIGNED NOT NULL,
    status ENUM('known','review') NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, vocab_id),
    CONSTRAINT fk_vocab_progress_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_vocab_progress_word FOREIGN KEY (vocab_id) REFERENCES vocabulary(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS streaks (
    user_id INT UNSIGNED PRIMARY KEY,
    current_streak INT NOT NULL DEFAULT 0,
    longest_streak INT NOT NULL DEFAULT 0,
    last_active_date DATE NULL,
    CONSTRAINT fk_streaks_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_challenges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    challenge_date DATE NOT NULL UNIQUE,
    question_ids JSON NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_challenge_attempts (
    user_id INT UNSIGNED NOT NULL,
    challenge_date DATE NOT NULL,
    attempt_id INT UNSIGNED NOT NULL UNIQUE,
    PRIMARY KEY (user_id, challenge_date),
    CONSTRAINT fk_daily_challenge_attempt_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_daily_challenge_attempt_attempt FOREIGN KEY (attempt_id) REFERENCES attempts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT UNSIGNED PRIMARY KEY,
    show_explanation TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pdf_imports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    type VARCHAR(32) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'completed',
    items_found INT UNSIGNED NOT NULL DEFAULT 0,
    items_inserted INT UNSIGNED NOT NULL DEFAULT 0,
    duplicates_skipped INT UNSIGNED NOT NULL DEFAULT 0,
    failed INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE vocabulary ADD COLUMN IF NOT EXISTS section_id INT UNSIGNED NULL AFTER example;
ALTER TABLE vocabulary ADD COLUMN IF NOT EXISTS practice_id INT UNSIGNED NULL AFTER section_id;
