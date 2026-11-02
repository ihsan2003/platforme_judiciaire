<?php

namespace App\Console\Commands;

use App\Models\Avertissement;
use App\Models\AvertissementJugement;
use App\Models\DegreeJuridiction;
use App\Models\Commune;
use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\Juge;
use App\Models\Jugement;
use App\Models\Partie;
use App\Models\Reclamant;
use App\Models\Reclamation;
use App\Models\ReclamationJugement;
use App\Models\StatutAvertissement;
use App\Models\StatutReclamation;
use App\Models\TypeReclamant;
use App\Models\TypeReclamation;
use App\Models\Province;
use App\Models\StatutDossier;
use App\Models\Tribunal;
use App\Models\TypeAffaire;
use App\Models\TypeJugement;
use App\Models\TypePartie;
use App\Models\User;
use App\Support\NumeroDossier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

/**
 * Import des dossiers depuis un fichier Excel (.xlsx) ou CSV/TSV.
 *
 * Colonnes attendues (en-têtes) :
 *   الحكم | موضوع النزاع | نوعية الملف | الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية
 *   | المدعى عليه | المدعي | ملف عدد | السنة | عدد الملفات | id_tribunal | المحكمة المختصة
 *   | nom_tribunal_corrigé | id_province | المدينة | nom_province_corrigé | nom_commune
 *
 * Idempotent : un dossier (tribunal + numéro) déjà présent est ignoré.
 *
 *   php artisan import:dossiers fichier.xlsx --dry-run
 *   php artisan import:dossiers fichier.xlsx --institution="التعاون الوطني"
 */
class ImportDossiersExcel extends Command
{
    protected $signature = 'import:dossiers
        {fichier : Chemin du fichier .xlsx / .csv / .tsv}
        {--feuille= : Nom de la feuille (défaut : la première)}
        {--dry-run : Simule l\'import (tout est annulé à la fin)}
        {--user= : E-mail de l\'utilisateur « créateur » (défaut : premier utilisateur)}
        {--institution=* : Fragment de nom identifiant la partie institution (est_entraide), ex. "التعاون الوطني"}
        {--creer-types : Crée les types d\'affaire inconnus au lieu de rejeter la ligne}';

    /** Fragments qui signalent une personne morale. */
    private const MORALE = ['مؤسسة', 'مركز', 'مديرية', 'وزارة', 'جماعة', 'شركة', 'مكتب', 'وكالة', 'جمعية', 'بنك', 'مجلس', 'الوطنية', 'الوطني'];

    private array $rapport = [];
    private array $stats = [];
    private array $typesInconnus = [];
    private ?User $createur = null;
    private $typesAffaire;
    private $tribunaux;

    public function handle(): int
    {
        $chemin = $this->argument('fichier');
        if (! is_file($chemin)) {
            $this->error("Fichier introuvable : {$chemin}");
            return self::FAILURE;
        }

        $this->createur = $this->option('user')
            ? User::where('email', $this->option('user'))->first()
            : User::orderBy('id')->first();
        if (! $this->createur) {
            $this->error("Aucun utilisateur trouvé (created_by est obligatoire). Créez un utilisateur ou passez --user=email.");
            return self::FAILURE;
        }

        try {
            $lignes = $this->lireFichier($chemin, $this->option('feuille'));
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        if (count($lignes) < 2) {
            $this->error('Le fichier ne contient aucune ligne de données.');
            return self::FAILURE;
        }

        $entetes = array_map(fn ($h) => $this->nettoyer($h), array_shift($lignes));
        $colonnes = array_flip(array_filter($entetes, fn ($h) => $h !== ''));
        foreach (['ملف عدد', 'السنة', 'نوعية الملف'] as $requise) {
            if (! isset($colonnes[$requise])) {
                $this->error("Colonne obligatoire absente : {$requise}");
                return self::FAILURE;
            }
        }

        $this->typesAffaire = TypeAffaire::all();
        $this->tribunaux = Tribunal::all();
        $dry = (bool) $this->option('dry-run');

        $this->info(($dry ? '[SIMULATION] ' : '') . count($lignes) . ' ligne(s) à traiter.');
        $barre = $this->output->createProgressBar(count($lignes));

        foreach ($lignes as $i => $brut) {
            $numLigne = $i + 2; // en-tête = ligne 1
            $row = [];
            foreach ($colonnes as $nom => $idx) {
                $row[$nom] = $this->nettoyer($brut[$idx] ?? '');
            }

            if (implode('', $row) === '') {
                $barre->advance();
                continue;
            }

            $nature = $this->natureLigne($row['ملف عدد'] ?? '');

            if ($nature === 'vide') {
                [$statut, $message] = ['ignore', 'Sans numéro de dossier : ligne ignorée'];
            } else {
                DB::beginTransaction();
                try {
                    [$statut, $message] = match ($nature) {
                        'avertissement' => $this->traiterAvertissement($row),
                        'reclamation'   => $this->traiterReclamation($row),
                        default         => $this->traiterDossier($row),
                    };
                    $dry ? DB::rollBack() : DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    [$statut, $message] = ['rejete', $e->getMessage()];
                }
            }

            $this->stats[$nature][$statut] = ($this->stats[$nature][$statut] ?? 0) + 1;
            $this->rapport[] = [$numLigne, $nature, $statut, $message, $row['ملف عدد'] ?? ''];
            $barre->advance();
        }

        $barre->finish();
        $this->newLine(2);

        $this->ecrireRapport();
        $libelles = ['dossier' => 'Dossiers', 'avertissement' => 'Avertissements (إنذار)', 'reclamation' => 'Réclamations (شكاية)', 'vide' => 'Sans numéro'];
        $lignesTable = [];
        foreach ($libelles as $nature => $libelle) {
            if (isset($this->stats[$nature])) {
                $st = $this->stats[$nature];
                $lignesTable[] = [$libelle, $st['importe'] ?? 0, $st['ignore'] ?? 0, $st['rejete'] ?? 0];
            }
        }
        $this->table(['Nature', 'Importés', 'Ignorés (doublon / sans numéro)', 'Rejetés'], $lignesTable);

        if ($this->typesInconnus) {
            $this->warn('Types d\'affaire inconnus (relancez avec --creer-types ou corrigez le fichier) :');
            foreach (array_count_values($this->typesInconnus) as $t => $n) {
                $this->line("  - {$t} ({$n})");
            }
        }
        if ($dry) {
            $this->comment('Simulation : aucune donnée n\'a été écrite.');
        }

        return self::SUCCESS;
    }

    /** dossier | avertissement | reclamation | vide, d'après la colonne « ملف عدد ». */
    private function natureLigne(string $numero): string
    {
        $cle = $this->cle($numero);
        if ($cle === '') {
            return 'vide';
        }
        if (str_contains($cle, 'انذار')) {
            return 'avertissement';
        }
        if (str_contains($cle, 'شكاي')) {
            return 'reclamation';
        }
        return 'dossier';
    }

    private function dateDeLigne(array $row): Carbon
    {
        $annee = (int) $this->chiffresLatins($row['السنة'] ?? '');
        if ($annee < 1990 || $annee > ((int) date('Y') + 1)) {
            throw new RuntimeException("Année invalide : « " . ($row['السنة'] ?? '') . " »");
        }
        return Carbon::create($annee, 1, 1)->startOfDay();
    }

    /**
     * Parties de la ligne : [المدعي, المدعى عليه] (null si vide).
     * @return array{0:?Partie,1:?Partie}
     */
    private function partiesDeLigne(array $row): array
    {
        $demandeur = $this->vide($row['المدعي'] ?? '');
        $defendeur = $this->vide($row['المدعى عليه'] ?? '');

        return [
            $demandeur ? $this->trouverOuCreerPartie($demandeur) : null,
            $defendeur ? $this->trouverOuCreerPartie($defendeur) : null,
        ];
    }

    private function typeJugementAuFond(): ?int
    {
        return TypeJugement::where('type_jugement', TypeJugement::AU_FOND)->value('id');
    }

    /** @return array{0:Carbon,1:?string} date du jugement + éventuel avertissement */
    private function dateJugement(string $texte, Carbon $repli): array
    {
        $date = $this->dateDansTexte($texte);
        return $date
            ? [$date, null]
            : [$repli->copy(), "Date du jugement non lisible dans le texte : date d'ouverture utilisée"];
    }

    /** @return array{0:string,1:string} [statut, message] */
    private function traiterAvertissement(array $row): array
    {
        $messages = [];
        $date = $this->dateDeLigne($row);
        [$tribunal, $note] = $this->trouverTribunal($row);
        $note && $messages[] = $note;
        $type = $this->trouverTypeAffaire($row['نوعية الملف'] ?? '');
        $commune = $this->trouverCommune($row, $tribunal);
        [$demandeur, $defendeur] = $this->partiesDeLigne($row);
        $objet = $this->vide($row['موضوع النزاع'] ?? '');

        $existe = Avertissement::where('id_tribunal', $tribunal->id)
            ->where('objet_litige', $objet)
            ->whereDate('date_avertissement', $date->toDateString())
            ->where('id_demandeur', $demandeur?->id)
            ->where('id_defendeur', $defendeur?->id)
            ->when(
                $this->vide($row['الحكم'] ?? ''),
                fn ($q, $t) => $q->whereHas('jugements', fn ($j) => $j->where('contenu_dispositif', $t)),
                fn ($q) => $q->whereDoesntHave('jugements')
            )
            ->exists();
        if ($existe) {
            return ['ignore', 'Déjà importé (même tribunal, objet, date, parties et jugement)'];
        }

        $statut = StatutAvertissement::where('statut_avertissement', 'في طور الإجراء')->value('id')
            ?? throw new RuntimeException("Statut d'avertissement « في طور الإجراء » introuvable");

        $avertissement = Avertissement::create([
            'objet_litige'            => $objet,
            'id_type_affaire'         => $type->id,
            'id_tribunal'             => $tribunal->id,
            'id_province'             => $tribunal->id_province,
            'id_commune'              => $commune?->id,
            'id_statut_avertissement' => $statut,
            'date_avertissement'      => $date,
            'action_service'          => $this->vide($row['الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية'] ?? ''),
            'id_demandeur'            => $demandeur?->id,
            'id_defendeur'            => $defendeur?->id,
            'created_by'              => $this->createur->id,
        ]);

        $texte = $this->vide($row['الحكم'] ?? '');
        if ($texte) {
            [$dateJ, $noteJ] = $this->dateJugement($texte, $date);
            $noteJ && $messages[] = $noteJ;
            $definitif = (bool) preg_match('/قطعي|نهائي/u', $texte);

            AvertissementJugement::create([
                'id_avertissement'   => $avertissement->id,
                'id_juge'            => null,
                'id_type_jugement'   => $this->typeJugementAuFond(),
                'date_jugement'      => $dateJ,
                'contenu_dispositif' => $texte,
                'est_definitif'      => $definitif,
                'created_by'         => $this->createur->id,
            ]);

            if ($definitif || preg_match('/الحفظ|حفظ/u', $texte)) {
                $hifz = StatutAvertissement::where('statut_avertissement', 'حفظ')->value('id');
                $hifz && $avertissement->update(['id_statut_avertissement' => $hifz]);
            }
        }

        return ['importe', implode(' | ', $messages)];
    }

    /** @return array{0:string,1:string} [statut, message] */
    private function traiterReclamation(array $row): array
    {
        $messages = [];
        $date = $this->dateDeLigne($row);
        [$demandeur, $defendeur] = $this->partiesDeLigne($row);

        // Tribunal / type d'affaire : facultatifs pour une réclamation.
        $tribunal = null;
        try {
            [$tribunal, $note] = $this->trouverTribunal($row);
            $note && $messages[] = $note;
        } catch (RuntimeException $e) {
            $messages[] = $e->getMessage() . ' (laissé vide)';
        }
        $type = $this->trouverTypeAffaire($row['نوعية الملف'] ?? '', false);
        if (! $type && ($row['نوعية الملف'] ?? '') !== '') {
            $messages[] = "Type d'affaire inconnu : « {$row['نوعية الملف']} » (laissé vide)";
        }
        $commune = $tribunal ? $this->trouverCommune($row, $tribunal) : null;

        // Convention de l'application : le réclamant est le المدعى عليه
        // (la partie institution étant المدعي).
        $nomReclamant = $this->vide($row['المدعى عليه'] ?? '') ?? $this->vide($row['المدعي'] ?? '');
        if (! $nomReclamant) {
            throw new RuntimeException('Aucune partie (المدعي / المدعى عليه) : réclamant impossible à déterminer');
        }

        $objet = mb_substr($this->vide($row['موضوع النزاع'] ?? '') ?? 'شكاية', 0, 255);

        $existe = Reclamation::where('objet', $objet)
            ->whereDate('date_reception', $date->toDateString())
            ->where('id_tribunal', $tribunal?->id)
            ->where('id_demandeur', $demandeur?->id)
            ->where('id_defendeur', $defendeur?->id)
            ->when(
                $this->vide($row['الحكم'] ?? ''),
                fn ($q, $t) => $q->whereHas('jugements', fn ($j) => $j->where('contenu_dispositif', $t)),
                fn ($q) => $q->whereDoesntHave('jugements')
            )
            ->exists();
        if ($existe) {
            return ['ignore', 'Déjà importée (même objet, date, tribunal, parties et jugement)'];
        }

        $typeReclamant = TypeReclamant::where('type_reclamant', 'آخر')->value('id')
            ?? throw new RuntimeException("Type de réclamant « آخر » introuvable");
        $typeReclamation = TypeReclamation::where('type_reclamation', 'شكايات مرتبطة بالمنازعات القضائية')->value('id');
        $statut = StatutReclamation::where('statut_reclamation', 'قيد المعالجة')->value('id')
            ?? throw new RuntimeException("Statut de réclamation « قيد المعالجة » introuvable");

        $reclamant = Reclamant::firstOrCreate(
            ['nom' => $nomReclamant],
            ['id_type_reclamant' => $typeReclamant]
        );

        $degre = $tribunal?->id_degre ?? DegreeJuridiction::premierDegre()?->id;
        $details = collect([
            $this->vide($row['الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية'] ?? ''),
        ])->filter()->implode("\n");

        $reclamation = Reclamation::create([
            'id_reclamant'          => $reclamant->id,
            'id_type_reclamation'   => $typeReclamation,
            'objet'                 => $objet,
            'date_reception'        => $date,
            'id_statut_reclamation' => $statut,
            'categorie'             => 'قضائية',
            'details'               => $details ?: null,
            'id_province'           => $tribunal?->id_province,
            'id_commune'            => $commune?->id,
            'id_degre'              => $tribunal ? $degre : null,
            'id_tribunal'           => $tribunal?->id,
            'id_type_affaire'       => $type?->id,
            'id_demandeur'          => $demandeur?->id,
            'id_defendeur'          => $defendeur?->id,
        ]);

        $texte = $this->vide($row['الحكم'] ?? '');
        if ($texte) {
            [$dateJ, $noteJ] = $this->dateJugement($texte, $date);
            $noteJ && $messages[] = $noteJ;

            ReclamationJugement::create([
                'id_reclamation'     => $reclamation->id,
                'id_juge'            => null,
                'id_type_jugement'   => $this->typeJugementAuFond(),
                'date_jugement'      => $dateJ,
                'contenu_dispositif' => $texte,
                'est_definitif'      => (bool) preg_match('/قطعي|نهائي/u', $texte),
                'created_by'         => $this->createur->id,
            ]);
        }

        return ['importe', implode(' | ', $messages)];
    }

    /** @return array{0:string,1:string} [statut, message] */
    private function traiterDossier(array $row): array
    {
        $messages = [];

        // ── Année / dates ──────────────────────────────────────────
        $dateOuverture = $this->dateDeLigne($row);

        // ── Numéro de dossier ──────────────────────────────────────
        [$numero, $noteNumero] = $this->numeroCanonique($row['ملف عدد'] ?? '');
        if ($noteNumero) {
            $messages[] = $noteNumero;
        }

        // ── Tribunal ───────────────────────────────────────────────
        [$tribunal, $noteTribunal] = $this->trouverTribunal($row);
        if ($noteTribunal) {
            $messages[] = $noteTribunal;
        }

        // ── Idempotence ────────────────────────────────────────────
        if (DossierTribunal::where('id_tribunal', $tribunal->id)
            ->where('numero_dossier_tribunal', $numero)->exists()) {
            return ['ignore', 'Déjà importé (même tribunal et même numéro)'];
        }

        // ── Type d'affaire ─────────────────────────────────────────
        $type = $this->trouverTypeAffaire($row['نوعية الملف'] ?? '');

        // ── Commune ────────────────────────────────────────────────
        $commune = $this->trouverCommune($row, $tribunal);
        if (! $commune && ($row['nom_commune'] ?? '') !== '') {
            $messages[] = "Commune introuvable : « {$row['nom_commune']} » (laissée vide)";
        }

        // ── Dossier ────────────────────────────────────────────────
        $dossier = DossierJudiciaire::create([
            'objet_litige'      => $this->vide($row['موضوع النزاع'] ?? ''),
            'action_service'    => $this->vide($row['الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية'] ?? ''),
            'id_type_affaire'   => $type->id,
            'date_ouverture'    => $dateOuverture,
            'created_by'        => $this->createur->id,
        ]);

        $degre = $tribunal->id_degre
            ? DegreeJuridiction::find($tribunal->id_degre)
            : DegreeJuridiction::premierDegre();

        $instance = DossierTribunal::create([
            'id_dossier'              => $dossier->id,
            'id_tribunal'             => $tribunal->id,
            'id_degre'                => $degre?->id ?? throw new RuntimeException('Aucun degré de juridiction en base'),
            'id_commune'              => $commune?->id,
            'numero_dossier_tribunal' => $numero,
            'date_debut'              => $dateOuverture,
        ]);

        // ── Parties ────────────────────────────────────────────────
        foreach ([['المدعي', 'المدعي'], ['المدعى عليه', 'المدعى عليه']] as [$colonne, $libelleType]) {
            $nom = $this->vide($row[$colonne] ?? '');
            if (! $nom) {
                continue;
            }
            $typePartie = TypePartie::where('type_partie', $libelleType)->first()
                ?? throw new RuntimeException("Type de partie introuvable : {$libelleType}");
            $partie = $this->trouverOuCreerPartie($nom);

            DB::table('dossier_parties')->insert([
                'id_dossier'      => $dossier->id,
                'id_partie'       => $partie->id,
                'id_type_partie'  => $typePartie->id,
                'est_institution' => (bool) $partie->est_entraide,
                'date_entree'     => $dateOuverture->toDateString(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // ── Jugement ───────────────────────────────────────────────
        $texteHukm = $this->vide($row['الحكم'] ?? '');
        if ($texteHukm) {
            $dateJugement = $this->dateDansTexte($texteHukm);
            if (! $dateJugement) {
                $dateJugement = $dateOuverture->copy();
                $messages[] = "Date du jugement non lisible dans le texte : date d'ouverture utilisée";
            }

            $juge = Juge::firstOrCreate(
                ['id_tribunal' => $tribunal->id, 'nom_complet' => 'غير محدد'],
                ['grade' => null, 'specialisation' => null]
            );
            $typeJugement = TypeJugement::where('type_jugement', TypeJugement::AU_FOND)->first();

            Jugement::create([
                'id_dossier_tribunal' => $instance->id,
                'id_juge'             => $juge->id,
                'id_type_jugement'    => $typeJugement?->id,
                'date_jugement'       => $dateJugement,
                'contenu_dispositif'  => $texteHukm,
                'est_definitif'       => (bool) preg_match('/قطعي|نهائي/u', $texteHukm),
                'created_by'          => $this->createur->id,
            ]);
            // JugementObserver → recalculerStatut() : « تم الحكم »

            if (preg_match('/الحفظ|حفظ/u', $texteHukm)) {
                $statut = StatutDossier::where('statut_dossier', 'حفظ')->first();
                if ($statut) {
                    $dossier->refresh()->update([
                        'id_statut_dossier' => $statut->id,
                        'date_cloture'      => $dateJugement,
                    ]);
                }
            }
        }

        $nbFichiers = (int) $this->chiffresLatins($row['عدد الملفات'] ?? '1');
        if ($nbFichiers > 1) {
            $messages[] = "عدد الملفات = {$nbFichiers} : la ligne a été importée comme UN seul dossier";
        }

        return ['importe', implode(' | ', $messages)];
    }

    // ───────────────────────────────────────────────────────────────
    // Résolution des référentiels
    // ───────────────────────────────────────────────────────────────

    /** @return array{0:string,1:?string} */
    private function numeroCanonique(string $brut): array
    {
        $numero = NumeroDossier::normaliser($brut);
        if ($numero === null) {
            throw new RuntimeException('Numéro de dossier (ملف عدد) vide');
        }

        $p = explode('/', $numero);
        if (count($p) === 3 && ! $this->anneePlausible($p[0]) && $this->anneePlausible($p[2])) {
            // Ordre inversé (ordre/code/année) : 348/7207/2024 → 2024/7207/348
            $corrige = "{$p[2]}/{$p[1]}/{$p[0]}";
            if (NumeroDossier::estValide($corrige, NumeroDossier::STANDARD)) {
                return [$corrige, "Numéro « {$numero} » inversé, corrigé en « {$corrige} »"];
            }
        }

        if (NumeroDossier::estValide($numero, NumeroDossier::STANDARD)) {
            return [$numero, null];
        }

        if (NumeroDossier::estValide($numero, NumeroDossier::CASSATION)) {
            return [$numero, null];
        }

        throw new RuntimeException("Format de numéro non reconnu : « {$brut} »");
    }

    private function anneePlausible(string $s): bool
    {
        return ctype_digit($s) && (int) $s >= 1990 && (int) $s <= ((int) date('Y') + 1);
    }

    /** @return array{0:Tribunal,1:?string} */
    private function trouverTribunal(array $row): array
    {
        $note = null;
        $parId = ($id = (int) $this->chiffresLatins($row['id_tribunal'] ?? '')) ? $this->tribunaux->firstWhere('id', $id) : null;

        $nomExcel = $this->cle($row['nom_tribunal_corrigé'] ?? '');
        $parNom = $nomExcel !== ''
            ? $this->tribunaux->first(fn ($t) => $this->cle($t->nom_tribunal) === $nomExcel)
            : null;

        if ($parId && $parNom && $parId->id !== $parNom->id) {
            $note = "id_tribunal={$parId->id} (« {$parId->nom_tribunal} ») ≠ nom « {$row['nom_tribunal_corrigé']} » : id retenu";
        }

        $tribunal = $parId ?? $parNom;
        if (! $tribunal) {
            throw new RuntimeException("Tribunal introuvable (id_tribunal=" . ($row['id_tribunal'] ?? '') . ", « " . ($row['nom_tribunal_corrigé'] ?? '') . " »)");
        }

        return [$tribunal, $note];
    }

    private function trouverTypeAffaire(string $libelle, bool $obligatoire = true): ?TypeAffaire
    {
        $cle = $this->cleSansOrdre($libelle);
        $type = $cle === '' ? null : $this->typesAffaire->first(fn ($t) => $this->cleSansOrdre($t->affaire) === $cle);

        if ($type) {
            return $type;
        }

        if (! $obligatoire) {
            return null;
        }

        if ($libelle !== '' && $this->option('creer-types')) {
            $type = TypeAffaire::create(['affaire' => $libelle, 'code' => null]);
            $this->typesAffaire->push($type);
            return $type;
        }

        $this->typesInconnus[] = $libelle === '' ? '(vide)' : $libelle;
        throw new RuntimeException("Type d'affaire inconnu : « {$libelle} »");
    }

    private function trouverCommune(array $row, Tribunal $tribunal): ?Commune
    {
        $nom = $this->cle($row['nom_commune'] ?? '');
        if ($nom === '') {
            return null;
        }

        $idProvince = (int) $this->chiffresLatins($row['id_province'] ?? '') ?: null;
        if ($idProvince && ! Province::whereKey($idProvince)->exists()) {
            $idProvince = null;
        }
        if (! $idProvince && ($row['nom_province_corrigé'] ?? '') !== '') {
            $cleP = $this->cle($row['nom_province_corrigé']);
            $idProvince = Province::all()->first(fn ($p) => $this->cle($p->province) === $cleP)?->id;
        }
        $idProvince ??= $tribunal->id_province;

        return Commune::where('id_province', $idProvince)->get()
            ->first(fn ($c) => $this->cle($c->commune) === $nom);
    }

    private function trouverOuCreerPartie(string $nom): Partie
    {
        $existante = Partie::where('nom_partie', $nom)->first();
        if ($existante) {
            return $existante;
        }

        $institution = false;
        foreach ((array) $this->option('institution') as $fragment) {
            if ($fragment !== '' && str_contains($this->cle($nom), $this->cle($fragment))) {
                $institution = true;
            }
        }

        $morale = $institution;
        foreach (self::MORALE as $mot) {
            if (str_contains($nom, $mot)) {
                $morale = true;
            }
        }

        return Partie::create([
            'nom_partie'         => $nom,
            'type_personne'      => $morale ? 'اعتباري' : 'ذاتي',
            'identifiant_unique' => null,
            'est_entraide'       => $institution,
        ]);
    }

    // ───────────────────────────────────────────────────────────────
    // Utilitaires texte
    // ───────────────────────────────────────────────────────────────

    private function nettoyer($v): string
    {
        $v = (string) $v;
        $v = preg_replace('/[\x{200E}\x{200F}\x{061C}\x{202A}-\x{202E}\x{00A0}]/u', ' ', $v);
        return trim(preg_replace('/\s+/u', ' ', $v));
    }

    private function vide(string $v): ?string
    {
        return $v === '' ? null : $v;
    }

    private function chiffresLatins(string $s): string
    {
        return strtr($s, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /** Clé de comparaison : sans diacritiques/tatweel, alef/ya/ta marbouta unifiés. */
    private function cle(string $s): string
    {
        $s = $this->nettoyer($s);
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0640}]/u', '', $s);
        $s = strtr($s, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ي', 'ة' => 'ه']);
        return mb_strtolower($s);
    }

    /** Comme cle() mais insensible à l'ordre des mots (« عقاري مدني » = « مدني عقاري »). */
    private function cleSansOrdre(string $s): string
    {
        $mots = array_filter(array_map(
            fn ($m) => mb_strlen($m) > 3 && str_starts_with($m, 'ال') ? mb_substr($m, 2) : $m,
            explode(' ', $this->cle($s))
        ));
        sort($mots);
        return implode(' ', $mots);
    }

    private function dateDansTexte(string $texte): ?Carbon
    {
        $texte = $this->chiffresLatins($texte);
        if (preg_match_all('#(\d{1,2})\s*[/\-.]\s*(\d{1,2})\s*[/\-.]\s*(\d{4})#', $texte, $m, PREG_SET_ORDER)) {
            foreach ($m as [$_, $j, $mo, $a]) {
                if (checkdate((int) $mo, (int) $j, (int) $a)) {
                    return Carbon::create((int) $a, (int) $mo, (int) $j)->startOfDay();
                }
            }
        }
        return null;
    }

    // ───────────────────────────────────────────────────────────────
    // Lecture du fichier
    // ───────────────────────────────────────────────────────────────

    /** @return array<int,array<int,string>> */
    private function lireFichier(string $chemin, ?string $feuille): array
    {
        $ext = strtolower(pathinfo($chemin, PATHINFO_EXTENSION));

        if (in_array($ext, ['csv', 'tsv', 'txt'], true)) {
            $contenu = file_get_contents($chemin);
            $contenu = preg_replace('/^\xEF\xBB\xBF/', '', $contenu);
            $premiere = strtok($contenu, "\n");
            $sep = substr_count($premiere, "\t") >= substr_count($premiere, ',') ? "\t" : ',';
            $h = fopen('php://temp', 'r+');
            fwrite($h, $contenu);
            rewind($h);
            $lignes = [];
            while (($r = fgetcsv($h, 0, $sep)) !== false) {
                $lignes[] = $r;
            }
            return $lignes;
        }

        if ($ext !== 'xlsx') {
            throw new RuntimeException('Format non pris en charge (utilisez .xlsx, .csv ou .tsv).');
        }

        $zip = new ZipArchive();
        if ($zip->open($chemin) !== true) {
            throw new RuntimeException('Impossible d\'ouvrir le fichier xlsx.');
        }

        $partages = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->si as $si) {
                $t = '';
                if (isset($si->t)) {
                    $t = (string) $si->t;
                } else {
                    foreach ($si->r as $r) {
                        $t .= (string) $r->t;
                    }
                }
                $partages[] = $t;
            }
        }

        // Feuille cible
        $wb = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $cible = null;
        foreach ($wb->sheets->sheet as $s) {
            if ($feuille === null || (string) $s['name'] === $feuille) {
                $rid = (string) $s->attributes('r', true)['id'];
                foreach ($rels->Relationship as $rel) {
                    if ((string) $rel['Id'] === $rid) {
                        $cible = 'xl/' . ltrim(preg_replace('#^/?(xl/)?#', '', (string) $rel['Target']), '/');
                    }
                }
                break;
            }
        }
        if (! $cible || ($xml = $zip->getFromName($cible)) === false) {
            throw new RuntimeException('Feuille introuvable' . ($feuille ? " : {$feuille}" : '.'));
        }

        $sheet = simplexml_load_string($xml);
        $lignes = [];
        foreach ($sheet->sheetData->row as $row) {
            $ligne = [];
            foreach ($row->c as $c) {
                $idx = $this->indexColonne((string) $c['r']);
                $type = (string) $c['t'];
                if ($type === 's') {
                    $val = $partages[(int) $c->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = (string) $c->is->t;
                } else {
                    $val = (string) $c->v;
                    if ($type === '' && preg_match('/^-?\d+\.0+$/', $val)) {
                        $val = (string) (int) $val;
                    }
                }
                $ligne[$idx] = $val;
            }
            $max = $ligne ? max(array_keys($ligne)) : -1;
            $plein = [];
            for ($i = 0; $i <= $max; $i++) {
                $plein[$i] = $ligne[$i] ?? '';
            }
            $lignes[] = $plein;
        }
        $zip->close();

        return $lignes;
    }

    private function indexColonne(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $n = 0;
        foreach (str_split($m[1]) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }
        return $n - 1;
    }

    private function ecrireRapport(): void
    {
        $dossier = storage_path('app');
        if (! is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }
        $fichier = $dossier . '/rapport_import_dossiers_' . date('Ymd_His') . '.csv';
        $h = fopen($fichier, 'w');
        fwrite($h, "\xEF\xBB\xBF");
        fputcsv($h, ['ligne', 'nature', 'statut', 'message', 'numero']);
        foreach ($this->rapport as $r) {
            fputcsv($h, $r);
        }
        fclose($h);
        $this->info("Rapport détaillé : {$fichier}");
    }
}
