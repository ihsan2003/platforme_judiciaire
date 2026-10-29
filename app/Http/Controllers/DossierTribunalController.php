<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\Tribunal;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class DossierTribunalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->only('destroy');
    }

    /**
     * Assigner un tribunal à un dossier.
     */
    public function store(Request $request, DossierJudiciaire $dossier): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'id_tribunal' => ['required', 'exists:tribunaux,id'],
            'id_degre'    => ['required', 'exists:degre_juridictions,id'],
            'id_commune'  => ['nullable', 'exists:communes,id'],
            // La chambre doit appartenir au tribunal choisi
            'id_chambre'  => ['nullable', Rule::exists('chambres', 'id')->where('id_tribunal', $request->id_tribunal)],
            'date_debut'  => ['required', 'date'],
            'date_fin'    => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        // RG04 — vérification de l'ordre des degrés
        $erreurDegre = $dossier->peutAjouterDegre((int) $request->id_degre);
        if ($erreurDegre) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->withFragment('tab-tribunaux')
                ->with('error', $erreurDegre);
        }

        DossierTribunal::create([
            'id_dossier'  => $dossier->id,
            'id_tribunal' => $request->id_tribunal,
            'id_degre'    => $request->id_degre,
            'id_commune'  => $request->id_commune ?: null,
            'id_chambre'  => $request->id_chambre ?: null,
            'date_debut'  => $request->date_debut,
            'date_fin'    => $request->date_fin,
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
        abort_unless($tribunal->id_dossier === $dossier->id, 403);

        $request->validate([
            'id_degre'   => ['required', 'exists:degre_juridictions,id'],
            'id_commune' => ['nullable', 'exists:communes,id'],
            'id_chambre' => ['nullable', Rule::exists('chambres', 'id')->where('id_tribunal', $tribunal->id_tribunal)],
            'date_debut' => ['required', 'date'],
            'date_fin'   => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $tribunal->update([
            'id_degre'   => $request->id_degre,
            'id_commune' => $request->id_commune ?: null,
            'id_chambre' => $request->id_chambre ?: null,
            'date_debut' => $request->date_debut,
            'date_fin'   => $request->date_fin,
        ]);

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
        abort_unless($tribunal->id_dossier === $dossier->id, 403);

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