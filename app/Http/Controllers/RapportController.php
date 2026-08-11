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
