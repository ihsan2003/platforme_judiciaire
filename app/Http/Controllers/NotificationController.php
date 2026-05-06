<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────
    // INDEX — Page complète des notifications
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Notification::pourUtilisateur(Auth::id())
            ->latest();

        // Filtre non lues seulement
        if ($request->boolean('non_lues')) {
            $query->nonLues();
        }

        // Filtre par niveau
        if ($request->filled('niveau')) {
            $query->parNiveau($request->niveau);
        }

        $notifications = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => Notification::pourUtilisateur(Auth::id())->count(),
            'non_lues' => Notification::pourUtilisateur(Auth::id())->nonLues()->count(),
            'danger'   => Notification::pourUtilisateur(Auth::id())->nonLues()->parNiveau('danger')->count(),
            'warning'  => Notification::pourUtilisateur(Auth::id())->nonLues()->parNiveau('warning')->count(),
        ];

        return view('notifications.index', compact('notifications', 'stats'));
    }

    // ─────────────────────────────────────────
    // DROPDOWN (AJAX) — Dernières 10 non lues
    // ─────────────────────────────────────────
    public function dropdown(): JsonResponse
    {
        $notifications = Notification::pourUtilisateur(Auth::id())
            ->nonLues()
            ->latest()
            ->limit(10)
            ->get();

        $totalNonLues = Notification::pourUtilisateur(Auth::id())
            ->nonLues()
            ->count();

        return response()->json([
            'notifications' => $notifications->map(fn($n) => [
                'id'         => $n->id,
                'message'    => $n->message,
                'details'    => $n->details,
                'niveau'     => $n->niveau,
                'couleur'    => $n->couleur,
                'icone'      => $n->icone,
                'categorie'  => $n->categorie,
                'url_action' => $n->url_action,
                'temps'      => $n->created_at->diffForHumans(),
            ]),
            'total_non_lues' => $totalNonLues,
        ]);
    }

    // ─────────────────────────────────────────
    // MARQUER UNE NOTIFICATION COMME LUE
    // ─────────────────────────────────────────
    public function marquerLue(Notification $notification): JsonResponse|RedirectResponse
    {
        // Sécurité : seul le propriétaire peut marquer
        abort_unless($notification->id_utilisateur === Auth::id(), 403);

        $notification->marquerCommeLue();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        // Si l'action a une URL cible, y rediriger
        if ($notification->url_action) {
            return redirect($notification->url_action);
        }

        return redirect()->route('notifications.index');
    }

    // ─────────────────────────────────────────
    // TOUT MARQUER COMME LU
    // ─────────────────────────────────────────
    public function toutMarquerLu(): JsonResponse|RedirectResponse
    {
        $count = Notification::pourUtilisateur(Auth::id())
            ->nonLues()
            ->update([
                'est_lue'      => true,
                'date_lecture' => now(),
            ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $count,
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', "{$count} notification(s) marquée(s) comme lue(s).");
    }

    // ─────────────────────────────────────────
    // SUPPRIMER UNE NOTIFICATION
    // ─────────────────────────────────────────
    public function destroy(Notification $notification): JsonResponse|RedirectResponse
    {
        abort_unless($notification->id_utilisateur === Auth::id(), 403);

        $notification->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification supprimée.');
    }

    // ─────────────────────────────────────────
    // GÉNÉRER MANUELLEMENT (admin / debug)
    // ─────────────────────────────────────────
    public function generer(NotificationService $service): RedirectResponse
    {
        $this->middleware('permission:manage users');

        $count = $service->genererPourUtilisateur(Auth::user());

        return redirect()->route('notifications.index')
            ->with('success', "{$count} nouvelle(s) notification(s) générée(s).");
    }

    // ─────────────────────────────────────────
    // COMPTEUR (AJAX) — pour le badge topbar
    // ─────────────────────────────────────────
    public function compteur(): JsonResponse
    {
        $count = Notification::pourUtilisateur(Auth::id())
            ->nonLues()
            ->count();

        return response()->json(['count' => $count]);
    }
}