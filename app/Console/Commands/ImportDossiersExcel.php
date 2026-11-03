<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ImporteDepuisExcel;
use App\Models\DegreeJuridiction;
use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\Juge;
use App\Models\Jugement;
use App\Models\StatutDossier;
use App\Models\TypeJugement;
use App\Models\TypePartie;
use App\Support\NumeroDossier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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
 * Les avertissements (إنذار) et les réclamations (شكاية) ont chacun leur propre
 * commande et leur propre fichier : import:avertissements et import:reclamations.
 * Si de telles lignes se trouvent dans ce fichier, elles sont ignorées (et signalées).
 *
 *   php artisan import:dossiers fichier.xlsx --dry-run
 *   php artisan import:dossiers fichier.xlsx --institution="التعاون الوطني"
 */
class ImportDossiersExcel extends Command
{
    use ImporteDepuisExcel;

    protected $signature = 'import:dossiers
        {fichier : Chemin du fichier .xlsx / .csv / .tsv}
        {--feuille= : Nom de la feuille (défaut : la première)}
        {--dry-run : Simule l\'import (tout est annulé à la fin)}
        {--user= : E-mail de l\'utilisateur « créateur » (défaut : premier utilisateur)}
        {--institution=* : Fragment de nom identifiant la partie institution (est_entraide), ex. "التعاون الوطني"}
        {--creer-types : Crée les types d\'affaire inconnus au lieu de rejeter la ligne}';

    protected $description = 'Importe les dossiers judiciaires depuis un fichier Excel/CSV (avertissements et réclamations : voir import:avertissements / import:reclamations)';

    public function handle(): int
    {
        return $this->importerFichier(
            colonnesRequises: ['ملف عدد', 'السنة', 'نوعية الملف'],
            nature: fn (array $row) => $this->natureLigne($row['ملف عدد'] ?? ''),
            traiter: fn (string $nature, array $row) => match ($nature) {
                'avertissement' => ['ignore', 'Avertissement (إنذار) : à importer avec « php artisan import:avertissements »'],
                'reclamation'   => ['ignore', 'Réclamation (شكاية) : à importer avec « php artisan import:reclamations »'],
                default         => $this->traiterDossier($row),
            },
            libelles: [
                'dossier'       => 'Dossiers',
                'avertissement' => 'Avertissements (إنذار) — hors périmètre',
                'reclamation'   => 'Réclamations (شكاية) — hors périmètre',
                'vide'          => 'Sans numéro',
            ],
            prefixeRapport: 'rapport_import_dossiers',
            cleNumero: 'ملف عدد',
        );
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

        // ── Parties ────────────────────────────────────────────────
        [$demandeur, $defendeur] = $this->partiesDeLigne($row);
        $typeDemandeur = TypePartie::where('type_partie', 'المدعي')->value('id')
            ?? throw new RuntimeException('Type de partie introuvable : المدعي');
        $typeDefendeur = TypePartie::where('type_partie', 'المدعى عليه')->value('id')
            ?? throw new RuntimeException('Type de partie introuvable : المدعى عليه');

        // ── Dossier : les lignes aux mêmes parties forment un seul dossier ──
        $dossier = null;
        if ($demandeur && $defendeur) {
            $idExistant = DB::table('dossier_parties as a')
                ->join('dossier_parties as b', 'a.id_dossier', '=', 'b.id_dossier')
                ->join('dossier_judiciaires as d', 'd.id', '=', 'a.id_dossier')
                ->whereNull('d.deleted_at')
                ->where('a.id_partie', $demandeur->id)->where('a.id_type_partie', $typeDemandeur)
                ->where('b.id_partie', $defendeur->id)->where('b.id_type_partie', $typeDefendeur)
                ->orderBy('a.id_dossier')
                ->value('a.id_dossier');
            $dossier = $idExistant ? DossierJudiciaire::find($idExistant) : null;
        }

        $objet  = $this->vide($row['موضوع النزاع'] ?? '');
        $action = $this->vide($row['الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية'] ?? '');

        if ($dossier) {
            $messages[] = "Rattaché au dossier #{$dossier->id} (mêmes parties)";
            $maj = [];
            if (! $dossier->objet_litige && $objet) {
                $maj['objet_litige'] = $objet;
            }
            if (! $dossier->action_service && $action) {
                $maj['action_service'] = $action;
            }
            if ($dossier->date_ouverture && $dateOuverture->lt($dossier->date_ouverture)) {
                $maj['date_ouverture'] = $dateOuverture;
            }
            $maj && $dossier->update($maj);
        } else {
            $dossier = DossierJudiciaire::create([
                'objet_litige'    => $objet,
                'action_service'  => $action,
                'id_type_affaire' => $type->id,
                'date_ouverture'  => $dateOuverture,
                'created_by'      => $this->createur->id,
            ]);

            foreach ([[$demandeur, $typeDemandeur], [$defendeur, $typeDefendeur]] as [$partie, $idType]) {
                if (! $partie) {
                    continue;
                }
                DB::table('dossier_parties')->insert([
                    'id_dossier'      => $dossier->id,
                    'id_partie'       => $partie->id,
                    'id_type_partie'  => $idType,
                    'est_institution' => (bool) $partie->est_entraide,
                    'date_entree'     => $dateOuverture->toDateString(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

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

        return ['importe', implode(' | ', $messages)];
    }

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
}
