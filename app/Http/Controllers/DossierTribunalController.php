<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\Tribunal;
use App\Models\DegreeJuridiction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DossierTribunalController extends Controller
{
    /**
     * Assigner un tribunal à un dossier.
     */
    public function store(Request $request, DossierJudiciaire $dossier): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'id_tribunal'   => ['required', 'exists:tribunaux,id'],
            'id_degre'      => ['required', 'exists:degre_juridictions,id'],
            'annee_mahakim' => ['required', 'integer', 'min:1900', 'max:2100'],
            'ordre_mahakim' => ['required', 'integer', 'min:1'],
            'date_debut'    => ['required', 'date'],
            'date_fin'      => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        // RG04 — vérification de l'ordre des degrés
        $erreurDegre = $dossier->peutAjouterDegre((int) $request->id_degre);
        if ($erreurDegre) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->withFragment('tab-tribunaux')
                ->with('error', $erreurDegre);
        }

        // Génération automatique du numéro de dossier propre à cette instance,
        // à partir du code de la catégorie (ابتدائي/استئناف) — voir
        // رموز الملفات بالمحاكم المغربية : https://mandili.net/law/25925
        $degre = DegreeJuridiction::findOrFail($request->id_degre);
        $typeAffaire = $dossier->typeAffaire;

        $code = match ($degre->ordre) {
            1       => $typeAffaire?->code,
            2       => $typeAffaire?->code_appel,
            default => null,
        };

        if (in_array($degre->ordre, [1, 2]) && ! $code) {
            $champ = $degre->ordre === 2 ? 'رمز الاستئناف (code_appel)' : 'رمز الفئة';
            return redirect()
                ->route('dossiers.show', $dossier)
                ->withFragment('tab-tribunaux')
                ->with('error', "لا يمكن توليد رقم الملف تلقائيًا: {$champ} غير مضبوط لنوع القضية « {$typeAffaire?->affaire} ». يرجى ضبطه أولاً.");
        }

        $numero = $code
            ? "{$request->annee_mahakim} / {$code} / {$request->ordre_mahakim}"
            : null;

        DossierTribunal::create([
            'id_dossier'              => $dossier->id,
            'id_tribunal'             => $request->id_tribunal,
            'id_degre'                => $request->id_degre,
            'numero_dossier_tribunal' => $numero,
            'date_debut'              => $request->date_debut,
            'date_fin'                => $request->date_fin,
        ]);

        $tribunal = Tribunal::find($request->id_tribunal);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-tribunaux')
            ->with('success', "تم إسناد المحكمة « {$tribunal->nom_tribunal} » إلى الملف بنجاح.");
    }

    /**
     * Mettre à jour les dates ou le degré d'un tribunal assigné.
     */
    public function update(Request $request, DossierJudiciaire $dossier, DossierTribunal $tribunal): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'id_degre'   => ['required', 'exists:degre_juridictions,id'],
            'date_debut' => ['required', 'date'],
            'date_fin'   => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $tribunal->update($request->only(['id_degre', 'date_debut', 'date_fin']));

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-tribunaux')
            ->with('success', 'تم تحديث بيانات المحكمة بنجاح.');
    }

    /**
     * Retirer un tribunal du dossier (uniquement si aucune audience ou jugement lié).
     */
    public function destroy(DossierJudiciaire $dossier, DossierTribunal $tribunal): RedirectResponse
    {
        $this->authorize('update', $dossier);

        if ($tribunal->audiences()->exists() || $tribunal->jugements()->exists()) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->withFragment('tab-tribunaux')
                ->with('error', 'يتعذر حذف هذه المحكمة لأنها تحتوي على جلسات أو أحكام.');
        }

        $tribunal->delete();

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-tribunaux')
            ->with('success', 'تم حذف المحكمة من الملف بنجاح.');
    }
}