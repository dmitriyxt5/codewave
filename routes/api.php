<?php
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\LectionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TopicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/students/register', [AuthController::class, 'student_register']);


Route::get('/subjects', [SubjectController::class, 'index']);
Route::get('/subjects/{id}', [SubjectController::class, 'edit']);

// Protected routes (require auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/profile', [UserController::class, 'profile']);

    // Subject routes (admin actions)
    Route::post('/subjects/edit/{subject}', [SubjectController::class, 'update']);
    Route::post('/subjects/store', [SubjectController::class, 'store']);
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);

    // Topic routes
    Route::get('/subjects/{id}/topics', [TopicController::class, 'index']);
    Route::get('/subjects/{id}/topic/{topic_id}', [TopicController::class, 'show']);
    Route::post('/subjects/{id}/topics/store', [TopicController::class, 'store']);
    Route::post('/subjects/{id}/topics/edit', [TopicController::class, 'update']);
    Route::delete('/topics/{id}', [TopicController::class, 'destroy']);

    // Test routes
    Route::get('/topics/{topic}/test', [TestController::class, 'getTestByTopic']);
    Route::post('/tests/{test}/results', [TestController::class, 'submitTest']);
//    Route::get('/topics/{topic}/test', [TestController::class, 'getTestByTopic']);
    Route::post('/tests', [TestController::class, 'createTest']);
    Route::delete('/tests/{test_id}', [TestController::class, 'deleteTest']);

    // Lection routes
    Route::get('/subjects/{subject}/lection', [LectionController::class, 'show']);
    Route::post('/lections', [LectionController::class, 'store']);
    Route::delete('/lections/{subject_id}/{topic_id}', [LectionController::class, 'destroy']);
    Route::get('/subjects/{subject_id}/topic/{topic_id}/lection_show', [LectionController::class, 'find']);

    // Criteria routes
    Route::get('/topics/{topic_id}/criteria', [CriteriaController::class, 'index']);
    Route::post('/topics/{topic_id}/criteria', [CriteriaController::class, 'store']);
    Route::delete('/topics/{topic_id}/criteria/{id}', [CriteriaController::class, 'destroy']);

    // Command routes
    Route::post('/commands', [CommandController::class, 'store']);
    Route::get('/commands/students', [CommandController::class, 'getStudents']);
    Route::post('/commands/{id}/students', [CommandController::class, 'addStudents']);
    Route::put('/commands/{id}', [CommandController::class, 'update']);
    Route::delete('/commands/{id}', [CommandController::class, 'destroy']);
    Route::get('/subjects/{subject_id}/topic/{topic_id}/command', [CommandController::class, 'show']);
    Route::post('/commands/{id}/upgrade-photo', [CommandController::class, 'upgradePhoto']);
    Route::post('/commands/{id}/spend-coins-upgrade', [CommandController::class, 'spendCoinsAndUpgrade']);
    Route::get('/subjects/{subject_id}/command-image', [CommandController::class, 'getTeamImage']); // New route
});
