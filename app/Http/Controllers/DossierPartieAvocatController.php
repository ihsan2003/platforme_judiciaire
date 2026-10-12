<?php

namespace App\Http\Controllers;

use App\Models\DossierJudiciaire;
use App\Models\DossierPartie;
use App\Models\DossierPartieAvocat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DossierPartieAvocatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Ajouter un avocat à une partie dans ce dossier.
     * id_degre vide = avocat présent durant tout le processus du dossier.
     */
    public function store(Request $request, DossierJudiciaire $dossier, DossierPartie $partie): RedirectResponse
    {
        $this->authorize('update', $dossier);
        abort_unless($partie->id_dossier === $dossier->id, 403);

        $data = $request->validate([
            'id_avocat'  => ['required', 'exists:avocats,id'],
            'id_degre'   => ['nullable', 'exists:degre_juridictions,id'],
            'date_debut' => ['nullable', 'date'],
            'date_fin'   => ['nullable', 'date', 'after_or_equal:date_debut'],
        ], [
            'id_avocat.required'        => 'يرجى اختيار المحامي.',
            'date_fin.after_or_equal'   => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
        ]);

        $existe = DossierPartieAvocat::where('id_dossier_partie', $partie->id)
            ->where('id_avocat', $data['id_avocat'])
            ->where(function ($q) use ($data) {
                empty($data['id_degre'])
                    ? $q->whereNull('id_degre')
                    : $q->where('id_degre', $data['id_degre']);
            })
            ->exists();

        if ($existe) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->withFragment('tab-parties')
                ->with('error', 'هذا المحامي مسجل مسبقاً لهذه الجهة بنفس المرحلة.');
        }

        DossierPartieAvocat::create([
            'id_dossier_partie' => $partie->id,
            'id_avocat'         => $data['id_avocat'],
            'id_degre'          => $data['id_degre'] ?? null,
            'date_debut'        => $data['date_debut'] ?? null,
            'date_fin'          => $data['date_fin'] ?? null,
        ]);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-parties')
            ->with('success', 'تمت إضافة المحامي بنجاح.');
    }

    /** Retirer un avocat d'une partie dans ce dossier. */
    public function destroy(DossierJudiciaire $dossier, DossierPartie $partie, DossierPartieAvocat $affectation): RedirectResponse
    {
        $this->authorize('update', $dossier);
        abort_unless($partie->id_dossier === $dossier->id, 403);
        abort_unless($affectation->id_dossier_partie === $partie->id, 403);

        $affectation->delete();

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-parties')
            ->with('success', 'تم حذف المحامي من هذه الجهة.');
    }
}
