<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\LectionController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\GradesController;
use App\Http\Controllers\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| PUBLIC routes (no auth)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/students/register', [AuthController::class, 'student_register']);

Route::get('/subjects/{id}', [SubjectController::class, 'edit']); // Оставлено, если действительно нужно


/*
|--------------------------------------------------------------------------
| AUTHENTICATED routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn(Request $r) => $r->user());
    Route::get('/profile', [UserController::class, 'profile']);

    /*
    |--------------------------------------------------------------------------
    | Чтение предметов и тем (все роли)
    |--------------------------------------------------------------------------
    */
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{id}/topics', [TopicController::class, 'index']);
    Route::get('/subjects/{id}/topic/{topic_id}', [TopicController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Тесты — обычные пользователи могут проходить
    |--------------------------------------------------------------------------
    */
    Route::get('/topics/{topic}/test', [TestController::class, 'getTestByTopic']);
    Route::post('/tests/{test}/results', [TestController::class, 'submitTest']);

    Route::get('/grades', [GradesController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Команды — частично публичный доступ
    |--------------------------------------------------------------------------
    */
    Route::get('/commands/students', [CommandController::class, 'getStudents']);
    Route::get('/subjects/{subject_id}/command', [CommandController::class, 'show']);
    Route::get('/subjects/{subject_id}/command-image', [CommandController::class, 'getTeamImage']);


    /*
    |--------------------------------------------------------------------------
    | ADMIN + SUPERADMIN routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,superadmin')->group(function () {

        // Subjects
        Route::post('/subjects/store', [SubjectController::class, 'store']);
        Route::post('/subjects/edit/{subject}', [SubjectController::class, 'update']);

        // Topics
        Route::post('/subjects/{id}/topics/store', [TopicController::class, 'store']);
        Route::post('/subjects/{id}/topics/edit', [TopicController::class, 'update']);
        Route::delete('/topics/{id}', [TopicController::class, 'destroy']);

        // Tests
        Route::post('/tests', [TestController::class, 'createTest']);

        // Lectures
        Route::post('/lections', [LectionController::class, 'store']);
        Route::delete('/lections/{subject_id}/{topic_id}', [LectionController::class, 'destroy']);

        // Commands
        Route::post('/commands', [CommandController::class, 'store']);
        Route::post('/commands/{id}/students', [CommandController::class, 'addStudents']);
        Route::put('/commands/{id}', [CommandController::class, 'update']);
        Route::delete('/commands/{id}', [CommandController::class, 'destroy']);
        Route::post('/commands/{id}/upgrade-photo', [CommandController::class, 'upgradePhoto']);
        Route::post('/commands/{id}/spend-coins-upgrade', [CommandController::class, 'spendCoinsAndUpgrade']);
    });


    /*
    |--------------------------------------------------------------------------
    | SUPERADMIN routes only
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:superadmin')->group(function () {

        Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);

        Route::delete('/tests/{test_id}', [TestController::class, 'deleteTest']);

        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/admin/subject/{id}/commands', [AdminDashboardController::class, 'showSubjectTeams']);
    });
});
