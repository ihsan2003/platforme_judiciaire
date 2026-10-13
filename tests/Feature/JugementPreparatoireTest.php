<?php

namespace Tests\Feature;

use App\Models\Audience;
use App\Models\DossierTribunal;
use App\Models\Jugement;
use App\Models\TypeJugement;
use App\Models\TypeRecours;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Certains fichiers de app/ n'ont pas la même casse que leur classe
// (ex. Statutdossierhistorique.php ↔ StatutDossierHistorique) : OK sous Windows/macOS,
// introuvable sous Linux. Ce filet ne sert qu'à faire tourner ce test sous Linux.
spl_autoload_register(function (string $class) {
    if (! str_starts_with($class, 'App\\')) {
        return;
    }
    $rel = str_replace('\\', '/', substr($class, 4));
    foreach (glob(dirname(__DIR__, 2) . '/app/' . dirname($rel) . '/*.php') ?: [] as $file) {
        if (strcasecmp(basename($file), basename($rel) . '.php') === 0) {
            require_once $file;
            return;
        }
    }
});

/**
 * Règles métier du حكم تمهيدي (jugement préparatoire) :
 *  - ne clôt pas l'instance et ne verrouille pas les audiences ;
 *  - ne devient jamais "définitif" et n'est pas attaquable séparément ;
 *  - n'entre ni dans la finance, ni dans l'exécution, ni dans les alertes.
 */
class JugementPreparatoireTest extends TestCase
{
    use RefreshDatabase;

    private int $dtId;
    private int $typeHoukm;
    private int $typeExpertise;
    private int $statutEnCours;
    private int $statutJuge;

    protected function setUp(): void
    {
        parent::setUp();

        // Les FK ne sont pas l'objet du test : on insère uniquement les colonnes utiles.
        // (dans la transaction de test, on diffère le contrôle : il n'a jamais lieu car rollback)
        DB::statement('PRAGMA defer_foreign_keys = ON');

        DB::table('users')->insert([
            'id' => 1, 'name' => 'Tester', 'email' => 't@example.test', 'password' => 'x',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('juges')->insert(['id' => 1, 'nom_complet' => 'Juge', 'id_tribunal' => 1,
            'created_at' => now(), 'updated_at' => now()]);

        $this->statutEnCours = DB::table('statut_dossiers')->insertGetId(['statut_dossier' => 'جاري']);
        $this->statutJuge    = DB::table('statut_dossiers')->insertGetId(['statut_dossier' => 'تم الحكم']);
        DB::table('dossier_judiciaires')->insert([
            'id' => 1, 'numero_dossier_tribunal' => '2026 / 1101 / 1', 'id_type_affaire' => 1,
            'id_statut_dossier' => $this->statutEnCours, 'date_ouverture' => '2026-01-01',
            'created_by' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->typeHoukm     = DB::table('type_audiences')->insertGetId(['type_audience' => 'الحكم']);
        $this->typeExpertise = DB::table('type_audiences')->insertGetId(['type_audience' => 'الخبرة']);

        // Parties requises (RG01) : المدعي + المدعى عليه
        $demandeur = DB::table('type_parties')->insertGetId(['type_partie' => 'المدعي']);
        $defendeur = DB::table('type_parties')->insertGetId(['type_partie' => 'المدعى عليه']);
        foreach ([1 => 'المدعي', 2 => 'المدعى عليه'] as $idPartie => $nom) {
            DB::table('parties')->insert([
                'id' => $idPartie, 'nom_partie' => $nom, 'type_personne' => 'ذاتي',
                'identifiant_unique' => 'CIN' . $idPartie, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach ([[1, $demandeur], [2, $defendeur]] as [$idPartie, $idType]) {
            DB::table('dossier_parties')->insert([
                'id_dossier' => 1, 'id_partie' => $idPartie, 'id_type_partie' => $idType,
                'date_entree' => '2026-01-01', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        TypeRecours::create(['type_recours' => 'استئناف', 'delai_legal_jours' => 30]);

        $this->dtId = DB::table('dossier_tribunaux')->insertGetId([
            'id_dossier' => 1, 'id_tribunal' => 1, 'id_degre' => 1,
            'date_debut' => '2026-01-01', 'date_fin' => null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function audience(int $type, string $date): Audience
    {
        return Audience::create([
            'id_dossier_tribunal' => $this->dtId, 'id_type_audience' => $type, 'id_juge' => 1,
            'presence_demandeur' => true, 'presence_defendeur' => true, 'date_audience' => $date,
        ]);
    }

    private function jugement(bool $preparatoire, string $date): Jugement
    {
        return Jugement::create([
            'id_dossier_tribunal' => $this->dtId, 'id_juge' => 1,
            'id_type_jugement'    => $preparatoire ? TypeJugement::idPreparatoire() : TypeJugement::idAuFond(),
            'date_jugement'       => $date, 'contenu_dispositif' => 'منطوق الحكم للاختبار',
            'est_definitif'       => false, 'created_by' => 1,
        ]);
    }

    public function test_migration_seeds_both_types_and_existing_rows_default_to_au_fond(): void
    {
        $this->assertNotNull(TypeJugement::idPreparatoire());
        $this->assertNotNull(TypeJugement::idAuFond());

        DB::table('jugements')->insert([
            'id_dossier_tribunal' => $this->dtId, 'id_juge' => 1, 'date_jugement' => '2026-02-01',
            'contenu_dispositif' => 'x', 'created_by' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->assertFalse(Jugement::first()->estPreparatoire());
    }

    public function test_preparatoire_does_not_lock_the_instance(): void
    {
        $dt = DossierTribunal::find($this->dtId);

        $this->audience($this->typeHoukm, '2026-03-01');
        $this->assertNotNull($dt->audienceHoukmPourJugementAuFond());

        $this->jugement(true, '2026-03-01');

        $this->assertTrue($dt->aUnJugement());                 // il existe bien un jugement…
        $this->assertFalse($dt->aUnJugementAuFond());          // …mais pas sur le fond
        $this->assertNull($dt->audienceHoukmPourJugementAuFond(), 'l\'audience الحكم déjà utilisée est consommée');
        $this->assertSame('Jugement préparatoire rendu', $dt->statut_label);

        // L'instruction continue : nouvelle audience, puis nouvelle audience الحكم
        $this->audience($this->typeExpertise, '2026-04-01');
        $houkm = $this->audience($this->typeHoukm, '2026-05-01');

        $this->assertSame($houkm->id, $dt->audienceHoukmPourJugementAuFond()->id);
        $this->assertSame('En délibéré', $dt->fresh()->statut_label);

        $this->jugement(false, '2026-05-01');
        $this->assertTrue($dt->fresh()->aUnJugementAuFond());
        $this->assertSame('Jugement rendu', $dt->fresh()->statut_label);
    }

    public function test_preparatoire_is_never_definitive_nor_appealable(): void
    {
        $prep = $this->jugement(true, now()->subDays(100)->toDateString());

        $this->assertTrue($prep->estPreparatoire());
        $this->assertFalse($prep->verifierEtMarquerDefinitif());
        $this->assertFalse($prep->fresh()->est_definitif);
        $this->assertFalse($prep->peutFaireObjetRecours());
        $this->assertNull($prep->delai_recours_restant);

    }

    public function test_dossier_status_only_moves_on_the_merits_judgment(): void
    {
        $dossier = \App\Models\DossierJudiciaire::find(1);

        $this->jugement(true, '2026-03-01');
        $this->assertSame($this->statutEnCours, $dossier->fresh()->id_statut_dossier,
            'un حكم تمهيدي ne doit pas faire passer le dossier à "تم الحكم"');
        $this->assertNull($dossier->fresh()->jugementValid);

        $fond = $this->jugement(false, '2026-06-01');
        $this->assertSame($this->statutJuge, $dossier->fresh()->id_statut_dossier);
        $this->assertSame($fond->id, $dossier->fresh()->load('dossierTribunaux.jugements')->jugementValid->id);
    }

    public function test_scopes_split_the_two_kinds(): void
    {
        $this->jugement(true, '2026-03-01');
        $this->jugement(true, '2026-03-15');
        $this->jugement(false, '2026-06-01');

        $this->assertSame(2, Jugement::preparatoires()->count());
        $this->assertSame(1, Jugement::auFond()->count());
    }

    public function test_check_delais_command_ignores_preparatoire(): void
    {
        $prep = $this->jugement(true, now()->subDays(200)->toDateString());

        $this->artisan('recours:check-delais')->assertSuccessful();

        $this->assertFalse($prep->fresh()->est_definitif);
    }

    // ─────────────────────────────────────────────────────────────
    // Flux HTTP (JugementController::store / RecoursController::store)
    // ─────────────────────────────────────────────────────────────

    private function payload(bool $preparatoire, string $date): array
    {
        return [
            'id_dossier_tribunal' => $this->dtId,
            'id_juge'             => 1,
            'id_type_jugement'    => $preparatoire ? TypeJugement::idPreparatoire() : TypeJugement::idAuFond(),
            'date_jugement'       => $date,
            'contenu_dispositif'  => 'تعيين خبير لإنجاز خبرة حسابية',
        ];
    }

    private function actingAsUser(): void
    {
        \Illuminate\Support\Facades\Gate::before(fn () => true); // la policy n'est pas l'objet du test
        $this->actingAs(\App\Models\User::find(1));
    }

    public function test_http_full_cycle_preparatoire_then_merits(): void
    {
        $this->actingAsUser();

        $this->audience($this->typeExpertise, '2026-03-01');

        // 1) Préparatoire sur une audience d'expertise (pas "الحكم")
        $this->post(route('jugements.store'), $this->payload(true, '2026-03-01'))
            ->assertSessionHasNoErrors();

        $prep = Jugement::preparatoires()->first();
        $this->assertNotNull($prep, 'le حكم تمهيدي doit être enregistré');
        $this->assertFalse($prep->est_definitif);
        $this->assertSame(0, DB::table('finances')->count());
        $this->assertSame(0, DB::table('jugement_parties')->count());
        $this->assertNull(DossierTribunal::find($this->dtId)->date_fin, 'l\'instance reste ouverte');

        // 2) Date ne correspondant à aucune audience → refusée
        $this->post(route('jugements.store'), $this->payload(true, '2026-03-02'))
            ->assertSessionHasErrors('date_jugement');

        // 3) Recours contre le préparatoire → refusé
        $this->post(route('jugements.recours.store', $prep), [
            'id_type_recours' => TypeRecours::first()->id,
            'date_recours'    => now()->toDateString(),
        ])->assertSessionHas('error');
        $this->assertSame(0, DB::table('recours')->count());

        // 4) Jugement final impossible sans nouvelle audience "الحكم"
        $this->audience($this->typeHoukm, '2026-03-01'); // même jour que le préparatoire : consommée
        $this->post(route('jugements.store'), $this->payload(false, '2026-03-01'))
            ->assertSessionHas('error');
        $this->assertSame(0, Jugement::auFond()->count());

        // 5) Nouvelle audience "الحكم" → jugement final accepté, une seule fois
        $this->audience($this->typeHoukm, '2026-05-01');
        $this->post(route('jugements.store'), $this->payload(false, '2026-05-01'))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, Jugement::auFond()->count());

        $this->post(route('jugements.store'), $this->payload(false, '2026-05-01'))
            ->assertSessionHas('error');
        $this->assertSame(1, Jugement::auFond()->count());

        // 6) Après le jugement sur le fond, plus de préparatoire possible
        $this->post(route('jugements.store'), $this->payload(true, '2026-03-01'))
            ->assertSessionHas('error');
        $this->assertSame(1, Jugement::preparatoires()->count());
    }

    public function test_http_index_and_show_render_for_a_preparatoire(): void
    {
        $this->withoutVite(); // pas de build front dans l'environnement de test
        $this->actingAsUser();
        $prep = $this->jugement(true, '2026-03-01');

        $this->get(route('jugements.index'))->assertOk()->assertSee('حكم تمهيدي');
        $this->get(route('jugements.index', ['type_jugement' => TypeJugement::idAuFond()]))
            ->assertOk()->assertDontSee('2026 / 1101 / 1');
        $this->get(route('jugements.show', $prep))->assertOk()->assertSee('لا يُطعن فيه استقلالاً', false);

        $this->audience($this->typeExpertise, '2026-04-01');
        $this->get(route('jugements.create', ['dossier_id' => 1]))
            ->assertOk()->assertSee('نوع الحكم')->assertSee('حكم فاصل في الموضوع');
        $this->get(route('jugements.edit', $prep))
            ->assertOk()->assertSee('نوع الحكم غير قابل للتعديل');
    }

    public function test_preparatoire_is_possible_after_a_non_houkm_audience_without_any_houkm_audience(): void
    {
        $this->withoutVite();
        $this->actingAsUser();

        // Seulement une audience de type "الخبرة" (aucune audience "الحكم" dans l'instance)
        $aud = $this->audience($this->typeExpertise, '2026-03-01');
        $dt  = DossierTribunal::find($this->dtId);
        $this->assertNull($dt->audienceHoukm());

        // Le bouton "حكم تمهيدي" est proposé sur la fiche de l'audience, pas "تسجيل الحكم"
        $this->get(route('audiences.show', $aud))
            ->assertOk()
            ->assertSee('تسجيل حكم تمهيدي')
            ->assertDontSee('>تسجيل الحكم<', false);

        // La fiche du dossier propose aussi le bouton
        $this->get(route('dossiers.show', 1))->assertOk()->assertSee('تسجيل حكم تمهيدي');

        // Le formulaire présélectionne le type via ?type=preparatoire
        $this->get(route('jugements.create', ['dossier_id' => 1, 'type' => 'preparatoire']))
            ->assertOk()
            ->assertSee('id="type_jugement_' . TypeJugement::idPreparatoire() . '"', false);

        // Et l'enregistrement fonctionne sans audience "الحكم"
        $this->post(route('jugements.store'), $this->payload(true, '2026-03-01'))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, Jugement::preparatoires()->count());

        // Après le préparatoire : la fiche du dossier le liste, mais ne le traite pas comme jugement final
        $this->get(route('dossiers.show', 1))->assertOk()->assertSee('حكم تمهيدي');
    }

    public function test_houkm_audience_page_offers_both_buttons_then_only_preparatoire_is_removed_after_merits(): void
    {
        $this->withoutVite();
        $this->actingAsUser();

        $houkm = $this->audience($this->typeHoukm, '2026-05-01');

        $this->get(route('audiences.show', $houkm))
            ->assertOk()->assertSee('تسجيل الحكم')->assertSee('تسجيل حكم تمهيدي');

        $this->post(route('jugements.store'), $this->payload(false, '2026-05-01'))
            ->assertSessionHasNoErrors();

        // Jugement final rendu : plus aucun des deux boutons
        $this->get(route('audiences.show', $houkm))
            ->assertOk()->assertDontSee('تسجيل حكم تمهيدي');
    }
}
