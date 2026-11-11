<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

apiDispatch(static function (): void {
    (new PracticeController())->toggleExplanation();
});