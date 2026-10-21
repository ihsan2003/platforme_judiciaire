<?php

namespace App\Http\Controllers;

use App\Http\Requests\Avertissements\AvertissementJugementRequest;
use App\Models\Avertissement;
use App\Models\AvertissementJugement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Jugements d'un avertissement (routes imbriquées :
 * avertissements/{avertissement}/jugements/...).
 */
class AvertissementJugementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(AvertissementJugementRequest $request, Avertissement $avertissement): RedirectResponse
    {
        $this->authorize('update', $avertissement);

        $avertissement->jugements()->create(
            $request->validated() + ['created_by' => Auth::id()]
        );

        return $this->retour($avertissement, 'تم تسجيل الحكم بنجاح.');
    }

    public function update(
        AvertissementJugementRequest $request,
        Avertissement $avertissement,
        AvertissementJugement $jugement
    ): RedirectResponse {
        $this->authorize('update', $avertissement);
        abort_unless($jugement->id_avertissement === $avertissement->id, 404);

        $jugement->update($request->validated());

        return $this->retour($avertissement, 'تم تحديث الحكم بنجاح.');
    }

    /** Suppression réservée à l'admin (même règle que pour les dossiers). */
    public function destroy(Avertissement $avertissement, AvertissementJugement $jugement): RedirectResponse
    {
        $this->authorize('delete', $avertissement);
        abort_unless($jugement->id_avertissement === $avertissement->id, 404);

        $jugement->delete();

        return $this->retour($avertissement, 'تم حذف الحكم.');
    }

    private function retour(Avertissement $avertissement, string $message): RedirectResponse
    {
        return redirect()
            ->route('avertissements.show', $avertissement)
            ->with('success', $message);
    }
}
