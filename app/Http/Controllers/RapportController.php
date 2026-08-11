<?php

namespace App\Http\Controllers;

use App\Services\RapportStatistiqueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class RapportController extends Controller
{
    /**
     * Formulaire de sélection de la période.
     */
    public function index()
    {
        return view('rapports.index');
    }

    /**
     * Aperçu rapide (AJAX) des principaux indicateurs pour la période
     * sélectionnée, affiché dans le formulaire avant de générer le
     * document Word complet.
     */
    public function apercu(Request $request)
    {
        $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin'   => ['required', 'date', 'after_or_equal:date_debut'],
        ]);

        $debut = Carbon::parse($request->input('date_debut'));
        $fin = Carbon::parse($request->input('date_fin'));

        $service = new RapportStatistiqueService($debut, $fin);
        $valeurs = $service->genererStatistiques();

        return response()->json([
            'dossiers_total'    => $valeurs['dossiers_total'] ?? '0',
            'dossiers_en_cours' => $valeurs['dossiers_en_cours'] ?? '0',
            'dossiers_juges'    => $valeurs['dossiers_juges'] ?? '0',
            'dossiers_executes' => $valeurs['dossiers_executes'] ?? '0',
            'recl_total'        => $valeurs['recl_total'] ?? '0',
            'recl_traitees'     => $valeurs['recl_traitees'] ?? '0',
            'montant_total'     => $valeurs['montant_total'] ?? '0',
            'nb_regions'        => count($service->lignesRegion()),
            'nb_types_affaire'  => count($service->lignesTypeAffaire()),
        ]);
    }

    /**
     * Génère le rapport Word rempli pour la période demandée et le renvoie
     * en téléchargement.
     */
    public function export(Request $request)
    {
        $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin'   => ['required', 'date', 'after_or_equal:date_debut'],
        ]);

        $debut = Carbon::parse($request->input('date_debut'));
        $fin = Carbon::parse($request->input('date_fin'));

        $service = new RapportStatistiqueService($debut, $fin);
        $valeurs = $service->genererStatistiques();

        $templatePath = resource_path('templates/rapport_template.docx');
        $processor = new TemplateProcessor($templatePath);

        // Valeurs simples : ${cle} -> valeur
        foreach ($valeurs as $cle => $valeur) {
            $processor->setValue($cle, htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8'));
        }

        // Tableau 1 : توزيع الملفات حسب طبيعة النزاع (lignes dynamiques)
        $lignesType = $service->lignesTypeAffaire();
        if (count($lignesType) > 0) {
            $processor->cloneRow('type_libelle', count($lignesType));
            foreach ($lignesType as $i => $ligne) {
                $n = $i + 1;
                $processor->setValue("type_libelle#{$n}", htmlspecialchars($ligne['type_libelle'], ENT_QUOTES, 'UTF-8'));
                $processor->setValue("type_nombre#{$n}", htmlspecialchars($ligne['type_nombre'], ENT_QUOTES, 'UTF-8'));
            }
        } else {
            // pas de données : on retire simplement les variables du modèle
            $processor->setValue('type_libelle', '');
            $processor->setValue('type_nombre', '0');
        }

        // Tableau 2 : توزيع الملفات حسب الجهة القضائية (lignes dynamiques)
        $lignesRegion = $service->lignesRegion();
        if (count($lignesRegion) > 0) {
            $processor->cloneRow('reg_nom', count($lignesRegion));
            foreach ($lignesRegion as $i => $ligne) {
                $n = $i + 1;
                foreach ($ligne as $cle => $valeur) {
                    $processor->setValue("{$cle}#{$n}", htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8'));
                }
            }
        } else {
            $processor->setValue('reg_nom', '');
            foreach (['reg_ibtidai', 'reg_istinaf', 'reg_naqd', 'reg_total'] as $cle) {
                $processor->setValue($cle, '0');
            }
        }

        $nomFichier = 'rapport_statistique_' . $debut->format('Y-m-d') . '_' . $fin->format('Y-m-d') . '.docx';
        $cheminTemp = storage_path('app/' . $nomFichier);
        $processor->saveAs($cheminTemp);

        return response()->download($cheminTemp, $nomFichier)->deleteFileAfterSend(true);
    }
}