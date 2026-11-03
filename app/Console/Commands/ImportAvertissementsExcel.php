<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ImporteDepuisExcel;
use App\Models\Avertissement;
use App\Models\AvertissementJugement;
use App\Models\StatutAvertissement;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Import des avertissements (إنذار) depuis un fichier Excel (.xlsx) ou CSV/TSV,
 * séparément du fichier des dossiers.
 *
 * Colonnes attendues (en-têtes) :
 *   الحالة | ملاحظات | موضوع النزاع | نوعية الملف
 *   | الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية
 *   | المدعى عليه | المدعي | ملف عدد | السنة
 *   | nom_tribunal_corrigé | nom_province_corrigé | nom_commune
 *
 * Obligatoires : السنة، نوعية الملف، nom_tribunal_corrigé (ou id_tribunal).
 * Facultative  : الحكم (si présente, crée le jugement lié).
 *
 * Correspondances :
 *   الحالة      → statut de l'avertissement (défaut : « في طور الإجراء »)
 *   ملاحظات     → commentaire
 *   ملف عدد     → ignoré (« إنذار » n'est pas un numéro de dossier)
 *
 * Idempotent : même tribunal + objet + année + parties = déjà importé.
 *
 *   php artisan import:avertissements avertissements.xlsx --dry-run
 */
class ImportAvertissementsExcel extends Command
{
    use ImporteDepuisExcel;

    protected $signature = 'import:avertissements
        {fichier : Chemin du fichier .xlsx / .csv / .tsv}
        {--feuille= : Nom de la feuille (défaut : la première)}
        {--dry-run : Simule l\'import (tout est annulé à la fin)}
        {--user= : E-mail de l\'utilisateur « créateur » (défaut : premier utilisateur)}
        {--institution=* : Fragment de nom identifiant la partie institution (est_entraide), ex. "التعاون الوطني"}
        {--creer-types : Crée les types d\'affaire inconnus au lieu de rejeter la ligne}';

    protected $description = 'Importe les avertissements (إنذار) depuis un fichier Excel/CSV dédié';

    private $statuts;

    public function handle(): int
    {
        $this->statuts = StatutAvertissement::all();

        return $this->importerFichier(
            colonnesRequises: ['السنة', 'نوعية الملف'],
            nature: fn (array $row) => 'avertissement',
            traiter: fn (string $nature, array $row) => $this->traiterAvertissement($row),
            libelles: ['avertissement' => 'Avertissements (إنذار)'],
            prefixeRapport: 'rapport_import_avertissements',
        );
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
        if (! $commune && ($row['nom_commune'] ?? '') !== '') {
            $messages[] = "Commune introuvable : « {$row['nom_commune']} » (laissée vide)";
        }

        [$demandeur, $defendeur] = $this->partiesDeLigne($row);
        $objet = $this->vide($row['موضوع النزاع'] ?? '');
        $texteHukm = $this->vide($row['الحكم'] ?? '');

        $existe = Avertissement::where('id_tribunal', $tribunal->id)
            ->where('objet_litige', $objet)
            ->whereDate('date_avertissement', $date->toDateString())
            ->where('id_demandeur', $demandeur?->id)
            ->where('id_defendeur', $defendeur?->id)
            ->when(
                $texteHukm,
                fn ($q, $t) => $q->whereHas('jugements', fn ($j) => $j->where('contenu_dispositif', $t)),
                fn ($q) => $q->whereDoesntHave('jugements')
            )
            ->exists();
        if ($existe) {
            return ['ignore', 'Déjà importé (même tribunal, objet, date, parties et jugement)'];
        }

        // Statut : colonne « الحالة » si reconnue, sinon statut par défaut.
        $libelleStatut = $row['الحالة'] ?? '';
        $statut = $this->trouverParLibelle($this->statuts, 'statut_avertissement', $libelleStatut);
        if (! $statut && $libelleStatut !== '') {
            $messages[] = "Statut inconnu : « {$libelleStatut} » (statut par défaut utilisé)";
        }
        $statut ??= $this->statuts->firstWhere('statut_avertissement', StatutAvertissement::DEFAUT)
            ?? throw new RuntimeException("Statut d'avertissement « " . StatutAvertissement::DEFAUT . " » introuvable");

        $avertissement = Avertissement::create([
            'objet_litige'            => $objet,
            'id_type_affaire'         => $type->id,
            'id_tribunal'             => $tribunal->id,
            'id_province'             => $tribunal->id_province,
            'id_commune'              => $commune?->id,
            'id_statut_avertissement' => $statut->id,
            'date_avertissement'      => $date,
            'action_service'          => $this->vide($row['الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية'] ?? ''),
            'commentaire'             => $this->vide($row['ملاحظات'] ?? ''),
            'id_demandeur'            => $demandeur?->id,
            'id_defendeur'            => $defendeur?->id,
            'created_by'              => $this->createur->id,
        ]);

        // Jugement (colonne facultative « الحكم »)
        if ($texteHukm) {
            [$dateJ, $noteJ] = $this->dateJugement($texteHukm, $date);
            $noteJ && $messages[] = $noteJ;
            $definitif = (bool) preg_match('/قطعي|نهائي/u', $texteHukm);

            AvertissementJugement::create([
                'id_avertissement'   => $avertissement->id,
                'id_juge'            => null,
                'id_type_jugement'   => $this->typeJugementAuFond(),
                'date_jugement'      => $dateJ,
                'contenu_dispositif' => $texteHukm,
                'est_definitif'      => $definitif,
                'created_by'         => $this->createur->id,
            ]);

            if ($definitif || preg_match('/الحفظ|حفظ/u', $texteHukm)) {
                $idHifz = StatutAvertissement::idClassement();
                $idHifz && $avertissement->update(['id_statut_avertissement' => $idHifz]);
            }
        }

        return ['importe', implode(' | ', $messages)];
    }
}
