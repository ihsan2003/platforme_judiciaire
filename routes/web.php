<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DossierJudiciaireController;
use App\Http\Controllers\AudienceController;
use App\Http\Controllers\JugementController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\ExecutionController;
use App\Http\Controllers\PartieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AvocatController;
use App\Http\Controllers\TribunalController;
use App\Http\Controllers\JugeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecoursController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StructureController;
use App\Http\Controllers\DossierPartieController;
use App\Http\Controllers\DossierTribunalController;
use App\Http\Controllers\RapportController;


/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Routes protégées
|--------------------------------------------------------------------------
*/


Route::middleware(['auth'])->group(function () {
    Route::get('/rapports/statistiques', [RapportController::class, 'index'])->name('rapports.index');
    Route::post('/rapports/statistiques/export', [RapportController::class, 'export'])->name('rapports.export');
});

Route::get('/api/dashboard/dossiers-par-region', [DashboardController::class, 'dossiersParRegion'])
    ->middleware('auth')
    ->name('dashboard.map.data');

Route::middleware('auth')->prefix('api')->group(function () {
    // Provinces d'une région
    Route::get('/regions/{regionId}/provinces', function ($regionId) {
        $provinces = \App\Models\Province::where('id_region', $regionId)
            ->orderBy('province')
            ->get(['id', 'province']);
        return response()->json($provinces);
    });

    Route::get('/provinces/{provinceId}/degres', function ($provinceId) {
        $degres = \App\Models\DegreeJuridiction::whereHas('tribunaux',
                fn($q) => $q->where('id_province', $provinceId)
            )
            ->orderBy('degre_juridiction')
            ->get(['id', 'degre_juridiction']);
        return response()->json($degres);
    });

    Route::get('/provinces/{provinceId}/degres/{degreId}/tribunaux', function ($provinceId, $degreId) {
        $tribunaux = \App\Models\Tribunal::where('id_province', $provinceId)
            ->where('id_degre', $degreId)
            ->orderBy('nom_tribunal')
            ->get(['id', 'nom_tribunal']);
        return response()->json($tribunaux);
    });

    Route::get('/tribunaux/{tribunalId}/juges', function ($tribunalId) {
        $juges = \App\Models\Juge::where('id_tribunal', $tribunalId)
            ->orderBy('nom_complet')
            ->get(['id', 'nom_complet', 'grade']);
        return response()->json($juges);
    });

});


Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Dossiers Judiciaires
    |--------------------------------------------------------------------------
    */
    Route::resource('dossiers', DossierJudiciaireController::class);

    Route::post('dossiers/{dossier}/cloturer', [DossierJudiciaireController::class, 'cloturer'])
     ->name('dossiers.cloturer');
 

    // ── Parties d'un dossier ──────────────────────────────────────────────
    Route::prefix('dossiers/{dossier}/parties')->name('dossiers.parties.')->group(function () {
        Route::get('/', fn($dossier) => redirect()->route('dossiers.show', $dossier)); // ← ajout
        Route::get('/search', [DossierPartieController::class, 'search']) ->name('search');
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

    

    // ✅ Correct — dossier en paramètre
    Route::post('/dossiers/{dossier}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/dossiers/{dossier}/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/dossiers/{dossier}/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    /*
    |--------------------------------------------------------------------------
    | Entités de référence
    |--------------------------------------------------------------------------
    */
    Route::resource('parties', PartieController::class)->parameters(['parties' => 'partie']);    
    Route::resource('avocats',  AvocatController::class);
    Route::resource('tribunaux', TribunalController::class)->parameters(['tribunaux' => 'tribunal']);    
    Route::resource('juges',    JugeController::class);
    Route::resource('audiences', AudienceController::class);
    Route::resource('jugements', JugementController::class);
    Route::resource('executions', ExecutionController::class);
    Route::resource('finances', FinanceController::class);


    // Dépôt d'un recours sur un jugement spécifique
    Route::post('jugements/{jugement}/recours', [RecoursController::class, 'store'])
        ->name('jugements.recours.store');

    // Redirection de secours si quelqu'un arrive en GET sur cette URL
    Route::get('jugements/{jugement}/recours', function($jugement) {
        return redirect()->route('jugements.show', $jugement);
    })->name('jugements.recours.index');
    
    // Clôture manuelle sans recours (marque le jugement comme définitif)
    Route::post('jugements/{jugement}/cloture-sans-recours', [RecoursController::class, 'cloturerSansRecours'])
        ->name('jugements.cloture-sans-recours');


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
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                              [NotificationController::class, 'index'])        ->name('index');
        Route::get('/dropdown',                      [NotificationController::class, 'dropdown'])     ->name('dropdown');     // AJAX
        Route::get('/compteur',                      [NotificationController::class, 'compteur'])     ->name('compteur');     // AJAX badge
        Route::post('/{notification}/lire',          [NotificationController::class, 'marquerLue'])   ->name('lire');
        Route::post('/tout-lire',                    [NotificationController::class, 'toutMarquerLu'])->name('tout-lire');
        Route::delete('/{notification}',             [NotificationController::class, 'destroy'])      ->name('destroy');
        Route::post('/generer',                      [NotificationController::class, 'generer'])      ->name('generer');      // admin
    });

    /*
    |--------------------------------------------------------------------------
    | Profil utilisateur
    |--------------------------------------------------------------------------
    */
    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


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