<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reclamations\ReclamationJugementRequest;
use App\Models\Reclamation;
use App\Models\ReclamationJugement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Jugements d'une réclamation (routes imbriquées :
 * reclamations/{reclamation}/jugements/...).
 */
class ReclamationJugementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Suppression réservée à l'admin (comme la suppression d'une réclamation).
        $this->middleware('role:admin')->only('destroy');
    }

    public function store(ReclamationJugementRequest $request, Reclamation $reclamation): RedirectResponse
    {
        $reclamation->jugements()->create(
            $request->validated() + ['created_by' => Auth::id()]
        );

        return $this->retour($reclamation, 'تم تسجيل الحكم بنجاح.');
    }

    public function update(
        ReclamationJugementRequest $request,
        Reclamation $reclamation,
        ReclamationJugement $jugement
    ): RedirectResponse {
        abort_unless($jugement->id_reclamation === $reclamation->id, 404);

        $jugement->update($request->validated());

        return $this->retour($reclamation, 'تم تحديث الحكم بنجاح.');
    }

    public function destroy(Reclamation $reclamation, ReclamationJugement $jugement): RedirectResponse
    {
        abort_unless($jugement->id_reclamation === $reclamation->id, 404);

        $jugement->delete();

        return $this->retour($reclamation, 'تم حذف الحكم.');
    }

    private function retour(Reclamation $reclamation, string $message): RedirectResponse
    {
        return redirect()
            ->to(route('reclamations.show', $reclamation) . '#tab-jugements')
            ->with('success', $message);
    }
}
