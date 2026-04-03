<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DossierJudiciaireController;
use App\Http\Controllers\AudienceController;
use App\Http\Controllers\JugementController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\ExecutionController;
use App\Http\Controllers\PartieController;
use App\Http\Controllers\AvocatController;
use App\Http\Controllers\TribunalController;
use App\Http\Controllers\JugeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecoursController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StructureController;
use App\Http\Controllers\DossierPartieController;
use App\Http\Controllers\DossierTribunalController;

/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/
Auth::routes();

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Routes protégées
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Dossiers Judiciaires
    |--------------------------------------------------------------------------
    */
    Route::resource('dossiers', DossierJudiciaireController::class);

    // ── Parties d'un dossier ──────────────────────────────────────────────
    Route::prefix('dossiers/{dossier}/parties')->name('dossiers.parties.')->group(function () {
        Route::get('/', fn($dossier) => redirect()->route('dossiers.show', $dossier)); // ← ajout
        Route::post('/',           [DossierPartieController::class, 'store'])  ->name('store');
        Route::put('/{partie}',    [DossierPartieController::class, 'update']) ->name('update');
        Route::delete('/{partie}', [DossierPartieController::class, 'destroy'])->name('destroy');
    });

    // ── Tribunaux d'un dossier ────────────────────────────────────────────
    Route::prefix('dossiers/{dossier}/tribunaux')->name('dossiers.tribunaux.')->group(function () {
        Route::post('/',             [DossierTribunalController::class, 'store'])  ->name('store');
        Route::put('/{tribunal}',    [DossierTribunalController::class, 'update']) ->name('update');
        Route::delete('/{tribunal}', [DossierTribunalController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Entités de référence
    |--------------------------------------------------------------------------
    */
    Route::resource('parties',  PartieController::class);
    Route::resource('avocats',  AvocatController::class);
    Route::resource('tribunaux', TribunalController::class);
    Route::resource('juges',    JugeController::class);
    Route::resource('audiences', AudienceController::class);
    Route::resource('jugements', JugementController::class);
    Route::resource('executions', ExecutionController::class);

    /*
    |--------------------------------------------------------------------------
    | Audiences
    |--------------------------------------------------------------------------
    
    Route::prefix('audiences')->name('audiences.')->group(function () {
        Route::get('/',                [AudienceController::class, 'index'])  ->name('index');
        Route::get('/create',          [AudienceController::class, 'create']) ->name('create');
        Route::post('/',               [AudienceController::class, 'store'])  ->name('store');
        Route::get('/{audience}',      [AudienceController::class, 'show'])   ->name('show');
        Route::get('/{audience}/edit', [AudienceController::class, 'edit'])   ->name('edit');
        Route::put('/{audience}',      [AudienceController::class, 'update']) ->name('update');
        Route::delete('/{audience}',   [AudienceController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Jugements
    |--------------------------------------------------------------------------
    
    Route::prefix('jugements')->name('jugements.')->group(function () {
        Route::get('/',                [JugementController::class, 'index'])  ->name('index');
        Route::get('/create',          [JugementController::class, 'create']) ->name('create');
        Route::post('/',               [JugementController::class, 'store'])  ->name('store');
        Route::get('/{jugement}',      [JugementController::class, 'show'])   ->name('show');
        Route::get('/{jugement}/edit', [JugementController::class, 'edit'])   ->name('edit');
        Route::put('/{jugement}',      [JugementController::class, 'update']) ->name('update');
        Route::delete('/{jugement}',   [JugementController::class, 'destroy'])->name('destroy');

        Route::post('/{jugement}/recours',          [RecoursController::class,  'store'])        ->name('recours.store');
        Route::delete('/{jugement}/recours/{recours}', [RecoursController::class, 'destroy'])    ->name('recours.destroy');
        Route::post('/{jugement}/finance',          [JugementController::class, 'storeFinance']) ->name('finance.store');
        Route::put('/{jugement}/finance',           [JugementController::class, 'updateFinance'])->name('finance.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Réclamations
    |--------------------------------------------------------------------------
    */
    Route::prefix('reclamations')->name('reclamations.')->group(function () {
        Route::get('/',                    [ReclamationController::class, 'index'])  ->name('index');
        Route::get('/create',              [ReclamationController::class, 'create']) ->name('create');
        Route::post('/',                   [ReclamationController::class, 'store'])  ->name('store');
        Route::get('/{reclamation}',       [ReclamationController::class, 'show'])   ->name('show');
        Route::get('/{reclamation}/edit',  [ReclamationController::class, 'edit'])   ->name('edit');
        Route::put('/{reclamation}',       [ReclamationController::class, 'update']) ->name('update');
        Route::delete('/{reclamation}',    [ReclamationController::class, 'destroy'])->name('destroy');
        Route::post('/{reclamation}/actions', [ReclamationController::class, 'addAction'])->name('actions.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Exécutions
    |--------------------------------------------------------------------------
    
    Route::prefix('executions')->name('executions.')->group(function () {
        Route::get('/',                  [ExecutionController::class, 'index'])  ->name('index');
        Route::get('/create',            [ExecutionController::class, 'create']) ->name('create');
        Route::post('/',                 [ExecutionController::class, 'store'])  ->name('store');
        Route::get('/{execution}',       [ExecutionController::class, 'show'])   ->name('show');
        Route::get('/{execution}/edit',  [ExecutionController::class, 'edit'])   ->name('edit');
        Route::put('/{execution}',       [ExecutionController::class, 'update']) ->name('update');
    });

    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::post('/',                      [DocumentController::class, 'store'])   ->name('store');
        Route::get('/{document}/download',    [DocumentController::class, 'download'])->name('download');
        Route::delete('/{document}',          [DocumentController::class, 'destroy']) ->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                          [NotificationController::class, 'index'])        ->name('index');
        Route::post('/{notification}/lire',      [NotificationController::class, 'marquerLue'])   ->name('lire');
        Route::post('/tout-lire',                [NotificationController::class, 'toutMarquerLu'])->name('tout-lire');
    });

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('permission:manage users')->group(function () {
        Route::resource('users',      UserController::class);
        Route::resource('structures', StructureController::class);
    });
});