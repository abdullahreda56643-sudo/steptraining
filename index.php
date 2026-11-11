<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: realpath(__DIR__);
    $requestedFile = $documentRoot === false
        ? false
        : realpath($documentRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($requestPath, '/')));
    $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'mp3'];

    if ($requestedFile !== false
        && $documentRoot !== false
        && strpos($requestedFile, $documentRoot . DIRECTORY_SEPARATOR) === 0
        && is_file($requestedFile)
        && in_array(strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION)), $staticExtensions, true)) {
        return false;
    }
}

require __DIR__ . '/config/app.php';

try {
    Database::initialize();
    $router = new Router();

    $router->get('/', [HomeController::class, 'index']);
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->get('/logout', [AuthController::class, 'logout']);
    $router->get('/section/{slug}', [SectionController::class, 'index']);
    $router->get('/vocab', [VocabController::class, 'index']);
    $router->get('/placement', [PlacementController::class, 'index']);
    $router->post('/placement', [PlacementController::class, 'start']);
    $router->get('/daily', [DailyChallengeController::class, 'index']);
    $router->get('/practice/{id}', [PracticeController::class, 'index']);
    $router->get('/practice/attempt/{id}', [PracticeController::class, 'showQuestionAttempt']);
    $router->get('/analysis/custom/{skillId}', [PracticeController::class, 'startCustom']);
    $router->get('/mistakes', [MistakesController::class, 'index']);
    $router->get('/analysis', [AnalysisController::class, 'index']);
    $router->post('/api/submit-answer', [PracticeController::class, 'submitAnswer']);
    $router->post('/api/finish-practice', [PracticeController::class, 'finish']);
    $router->post('/api/toggle-explanation', [PracticeController::class, 'toggleExplanation']);
    $router->post('/api/vocab-progress', [VocabController::class, 'saveProgress']);
    $router->get('/admin', [DashboardController::class, 'index']);
    $router->get('/admin/dashboard', [DashboardController::class, 'index']);
    $router->get('/admin/practices', [PracticeAdminController::class, 'index']);
    $router->post('/admin/practices', [PracticeAdminController::class, 'save']);
    $router->get('/admin/questions', [QuestionAdminController::class, 'index']);
    $router->post('/admin/questions', [QuestionAdminController::class, 'save']);
    $router->get('/admin/passages', [PassageAdminController::class, 'index']);
    $router->post('/admin/passages', [PassageAdminController::class, 'save']);
    $router->get('/admin/vocab', [VocabAdminController::class, 'index']);
    $router->post('/admin/vocab', [VocabAdminController::class, 'save']);
    $router->get('/admin/users', [UserAdminController::class, 'index']);
    $router->post('/admin/users', [UserAdminController::class, 'save']);
    $router->get('/admin/skills', [SkillAdminController::class, 'index']);
    $router->post('/admin/skills', [SkillAdminController::class, 'save']);
    $router->get('/admin/settings', [SettingsAdminController::class, 'index']);
    $router->post('/admin/settings', [SettingsAdminController::class, 'save']);
    $router->get('/admin/imports', [PdfImportAdminController::class, 'index']);
    $router->post('/admin/imports/vocabulary', [VocabAdminController::class, 'importCsvFromCenter']);
    $router->post('/admin/imports/preview', [PdfImportAdminController::class, 'preview']);
    $router->post('/admin/imports/publish', [PdfImportAdminController::class, 'publish']);

    $router->dispatch($_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $e) {
    error_log('Application request failed: ' . $e->getMessage());
    http_response_code(500);
    view('errors/500', ['message' => 'حدث خطأ غير متوقع.'], 'main');
}
