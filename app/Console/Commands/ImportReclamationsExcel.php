<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ImporteDepuisExcel;
use App\Models\ActionReclamation;
use App\Models\DegreeJuridiction;
use App\Models\Reclamant;
use App\Models\Reclamation;
use App\Models\ReclamationJugement;
use App\Models\StatutReclamation;
use App\Models\TypeAction;
use App\Models\TypeReclamant;
use App\Models\TypeReclamation;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Import des réclamations (شكاية) depuis un fichier Excel (.xlsx) ou CSV/TSV,
 * séparément du fichier des dossiers.
 *
 * Colonnes attendues (en-têtes) :
 *   ملاحظات | موضوع النزاع | نوعية الملف | إحالة الى | نوع الإجراء | نوع المدعى عليه
 *   | المدعى عليه | المدعي | ملف عدد | السنة
 *   | nom_tribunal_corrigé | nom_province_corrigé | nom_commune
 *
 * Obligatoires : السنة، المدعى عليه (ou المدعي) pour déterminer le réclamant.
 * Facultatives : tribunal, نوعية الملف (laissés vides si non reconnus), الحكم.
 *
 * Correspondances :
 *   المدعى عليه      → réclamant (convention de l'application), type = « نوع المدعى عليه »
 *                      (défaut : « آخر »)
 *   المدعي           → demandeur (partie institution)
 *   ملاحظات          → détails de la réclamation
 *   نوع الإجراء      → action de la réclamation (رد / إحالة), commentaire = « إحالة الى »
 *   ملف عدد          → ignoré (« شكاية » n'est pas un numéro de dossier)
 *
 * Idempotent : même objet + année + tribunal + parties = déjà importée.
 *
 *   php artisan import:reclamations reclamations.xlsx --dry-run
 */
class ImportReclamationsExcel extends Command
{
    use ImporteDepuisExcel;

    protected $signature = 'import:reclamations
        {fichier : Chemin du fichier .xlsx / .csv / .tsv}
        {--feuille= : Nom de la feuille (défaut : la première)}
        {--dry-run : Simule l\'import (tout est annulé à la fin)}
        {--user= : E-mail de l\'utilisateur « créateur » (défaut : premier utilisateur)}
        {--institution=* : Fragment de nom identifiant la partie institution (est_entraide), ex. "التعاون الوطني"}';

    protected $description = 'Importe les réclamations (شكاية) depuis un fichier Excel/CSV dédié';

    private $typesReclamant;
    private $typesAction;

    public function handle(): int
    {
        $this->typesReclamant = TypeReclamant::all();
        $this->typesAction = TypeAction::all();

        return $this->importerFichier(
            colonnesRequises: ['السنة'],
            nature: fn (array $row) => 'reclamation',
            traiter: fn (string $nature, array $row) => $this->traiterReclamation($row),
            libelles: ['reclamation' => 'Réclamations (شكاية)'],
            prefixeRapport: 'rapport_import_reclamations',
        );
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
        if ($tribunal && ! $commune && ($row['nom_commune'] ?? '') !== '') {
            $messages[] = "Commune introuvable : « {$row['nom_commune']} » (laissée vide)";
        }

        // Convention de l'application : le réclamant est le المدعى عليه
        // (la partie institution étant المدعي).
        $nomReclamant = $this->vide($row['المدعى عليه'] ?? '') ?? $this->vide($row['المدعي'] ?? '');
        if (! $nomReclamant) {
            throw new RuntimeException('Aucune partie (المدعي / المدعى عليه) : réclamant impossible à déterminer');
        }

        $objet = mb_substr($this->vide($row['موضوع النزاع'] ?? '') ?? 'شكاية', 0, 255);
        $texteHukm = $this->vide($row['الحكم'] ?? '');

        $existe = Reclamation::where('objet', $objet)
            ->whereDate('date_reception', $date->toDateString())
            ->where('id_tribunal', $tribunal?->id)
            ->where('id_demandeur', $demandeur?->id)
            ->where('id_defendeur', $defendeur?->id)
            ->when(
                $texteHukm,
                fn ($q, $t) => $q->whereHas('jugements', fn ($j) => $j->where('contenu_dispositif', $t)),
                fn ($q) => $q->whereDoesntHave('jugements')
            )
            ->exists();
        if ($existe) {
            return ['ignore', 'Déjà importée (même objet, date, tribunal, parties et jugement)'];
        }

        // Type de réclamant : colonne « نوع المدعى عليه » (جمعية, مرفق عمومي…), sinon « آخر ».
        $libelleTypeReclamant = $row['نوع المدعى عليه'] ?? '';
        $typeReclamant = $this->trouverParLibelle($this->typesReclamant, 'type_reclamant', $libelleTypeReclamant);
        if (! $typeReclamant && $libelleTypeReclamant !== '') {
            $messages[] = "Type de réclamant inconnu : « {$libelleTypeReclamant} » (« آخر » utilisé)";
        }
        $typeReclamant ??= $this->typesReclamant->firstWhere('type_reclamant', 'آخر')
            ?? throw new RuntimeException("Type de réclamant « آخر » introuvable");

        $typeReclamation = TypeReclamation::idJudiciaire();
        $statut = StatutReclamation::where('statut_reclamation', 'قيد المعالجة')->value('id')
            ?? throw new RuntimeException("Statut de réclamation « قيد المعالجة » introuvable");

        $reclamant = Reclamant::firstOrCreate(
            ['nom' => $nomReclamant],
            ['id_type_reclamant' => $typeReclamant->id]
        );

        $degre = $tribunal?->id_degre ?? DegreeJuridiction::premierDegre()?->id;

        $reclamation = new Reclamation([
            'id_reclamant'          => $reclamant->id,
            'id_type_reclamation'   => $typeReclamation,
            'objet'                 => $objet,
            'date_reception'        => $date,
            'id_statut_reclamation' => $statut,
            'details'               => $this->vide($row['ملاحظات'] ?? ''),
            'id_province'           => $tribunal?->id_province,
            'id_commune'            => $commune?->id,
            'id_degre'              => $tribunal ? $degre : null,
            'id_tribunal'           => $tribunal?->id,
            'id_type_affaire'       => $type?->id,
            'id_demandeur'          => $demandeur?->id,
            'id_defendeur'          => $defendeur?->id,
        ]);
        // « categorie » n'est pas dans $fillable : affectation explicite.
        $reclamation->categorie = 'قضائية';
        $reclamation->save();

        // Action : نوع الإجراء (إحالة / رد) + destinataire « إحالة الى ».
        $libelleAction = $row['نوع الإجراء'] ?? '';
        $referent = $this->vide($row['إحالة الى'] ?? '');
        if ($libelleAction !== '') {
            $typeAction = $this->trouverParLibelle($this->typesAction, 'type_action', $libelleAction);
            if ($typeAction) {
                ActionReclamation::create([
                    'id_reclamation' => $reclamation->id,
                    'id_type_action' => $typeAction->id,
                    'statut_action'  => null,
                    'date_action'    => $date,
                    'id_structure'   => null,
                    'commentaire'    => $referent ? "إحالة الى : {$referent}" : null,
                    'created_by'     => $this->createur->id,
                ]);
            } else {
                $messages[] = "Type d'action inconnu : « {$libelleAction} » (action non créée)";
            }
        } elseif ($referent) {
            $messages[] = "« إحالة الى » ({$referent}) ignoré : « نوع الإجراء » vide";
        }

        // Jugement (colonne facultative « الحكم »)
        if ($texteHukm) {
            [$dateJ, $noteJ] = $this->dateJugement($texteHukm, $date);
            $noteJ && $messages[] = $noteJ;

            ReclamationJugement::create([
                'id_reclamation'     => $reclamation->id,
                'id_juge'            => null,
                'id_type_jugement'   => $this->typeJugementAuFond(),
                'date_jugement'      => $dateJ,
                'contenu_dispositif' => $texteHukm,
                'est_definitif'      => (bool) preg_match('/قطعي|نهائي/u', $texteHukm),
                'created_by'         => $this->createur->id,
            ]);
        }

        return ['importe', implode(' | ', $messages)];
    }
}
