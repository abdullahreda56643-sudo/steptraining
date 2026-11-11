<?php

declare(strict_types=1);

class SettingsAdminController extends AdminBaseController
{
    private const DEFAULTS = [
        'default_explanation' => '1',
        'daily_challenge_size' => '5',
        'placement_size' => '15',
        'weak_skill_threshold' => '60',
    ];

    public function index(): void
    {
        $this->guard();
        $settings = self::DEFAULTS;
        $rows = Database::getConnection()->query('SELECT `key`,value FROM settings')->fetchAll();
        foreach ($rows as $row) {
            if (array_key_exists($row['key'], $settings)) {
                $settings[$row['key']] = $row['value'];
            }
        }
        $this->page('settings', 'إعدادات المنصة', ['settings' => $settings]);
    }

    public function save(): void
    {
        $this->handlePost(function (): void {
            $values = [
                'default_explanation' => isset($_POST['default_explanation']) ? '1' : '0',
                'daily_challenge_size' => $this->boundedInteger('daily_challenge_size', 5, 1, 20),
                'placement_size' => $this->boundedInteger('placement_size', 15, 5, 100),
                'weak_skill_threshold' => $this->boundedInteger('weak_skill_threshold', 60, 1, 99),
            ];
            $stmt = Database::getConnection()->prepare(
                'INSERT INTO settings (`key`,value) VALUES (:setting_key,:setting_value)
                 ON DUPLICATE KEY UPDATE value=VALUES(value)'
            );
            foreach ($values as $key => $value) {
                $stmt->execute(['setting_key' => $key, 'setting_value' => (string) $value]);
            }
        }, '/admin/settings');
    }

    private function boundedInteger(string $key, int $default, int $min, int $max): int
    {
        $value = filter_var($_POST[$key] ?? $default, FILTER_VALIDATE_INT);
        if ($value === false || $value < $min || $value > $max) {
            throw new InvalidArgumentException('إحدى قيم الإعدادات خارج النطاق المسموح.');
        }

        return $value;
    }
}
