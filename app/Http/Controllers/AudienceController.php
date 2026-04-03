<?php
// app/Http/Controllers/AudienceController.php

namespace App\Http\Controllers;

use App\Http\Requests\Audiences\StoreAudienceRequest;
use App\Http\Requests\Audiences\UpdateAudienceRequest;
use App\Models\Audience;
use App\Models\DossierTribunal;
use App\Models\TypeAudience;
use App\Models\Juge;
use Carbon\Carbon;

class AudienceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────
    // INDEX : liste paginée avec filtres + stats
    // ─────────────────────────────────────────
    public function index()
    {
        $audiences = Audience::with([
                'dossierTribunal.dossier',
                'dossierTribunal.tribunal',
                'typeAudience',
                'juge',
            ])
            ->when(request('juge'),    fn($q, $v) => $q->where('id_juge', $v))
            ->when(request('type'),    fn($q, $v) => $q->where('id_type_audience', $v))
            ->when(request('periode'), function ($q, $v) {
                return match($v) {
                    'passees'  => $q->whereDate('date_audience', '<', today()),
                    'today'    => $q->whereDate('date_audience', today()),
                    'futures'  => $q->whereDate('date_audience', '>', today()),
                    'semaine'  => $q->whereBetween('date_audience', [today(), today()->addDays(7)]),
                    default    => $q,
                };
            })
            ->latest('date_audience')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'aujourd_hui' => Audience::whereDate('date_audience', today())->count(),
            'cette_semaine' => Audience::whereBetween('date_audience', [today(), today()->addDays(7)])->count(),
            'passees_sans_suite' => Audience::whereDate('date_audience', '<', today())
                ->whereNull('resultat_audience')
                ->count(),
        ];

        $juges = Juge::orderBy('nom_complet')->get();
        $typesAudience = TypeAudience::orderBy('type_audience')->get();

        return view('audiences.index', compact('audiences', 'stats', 'juges', 'typesAudience'));
    }

    // ─────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────
    public function create()
    {
        // Charger les dossier_tribunaux actifs avec leurs relations pour l'affichage
        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal'])
            ->whereHas('dossier', fn($q) => $q->whereHas('statut', fn($q) =>
                $q->where('statut_dossier', '!=', 'Clôturé')
            ))
            ->get();

        $typesAudience = TypeAudience::orderBy('type_audience')->get();
        $juges = Juge::with('tribunal')->orderBy('nom_complet')->get();

        return view('audiences.create', compact('dossierTribunaux', 'typesAudience', 'juges'));
    }

    // ─────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────
    public function store(StoreAudienceRequest $request)
    {
        $audience = Audience::create($request->validated());

        return redirect()
            ->route('audiences.show', $audience)
            ->with('success', 'Audience du ' . $audience->date_audience->format('d/m/Y') . ' créée avec succès.');
    }

    // ─────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────
    public function show(Audience $audience)
    {
        $audience->load([
            'dossierTribunal.dossier.typeAffaire',
            'dossierTribunal.dossier.statut',
            'dossierTribunal.tribunal',
            'dossierTribunal.degre',
            'typeAudience',
            'juge.tribunal',
        ]);

        // Prochaine et précédente audience du même dossier_tribunal
        $autresAudiences = Audience::where('id_dossier_tribunal', $audience->id_dossier_tribunal)
            ->where('id', '!=', $audience->id)
            ->orderBy('date_audience', 'desc')
            ->get();

        return view('audiences.show', compact('audience', 'autresAudiences'));
    }

    // ─────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────
    public function edit(Audience $audience)
    {
        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal'])->get();
        $typesAudience = TypeAudience::orderBy('type_audience')->get();
        $juges = Juge::with('tribunal')->orderBy('nom_complet')->get();

        return view('audiences.edit', compact('audience', 'dossierTribunaux', 'typesAudience', 'juges'));
    }

    // ─────────────────────────────────────────
    // UPDATE — corrige aussi le bug table 'dossier_tribunals' vs 'dossier_tribunaux'
    // ─────────────────────────────────────────
    public function update(UpdateAudienceRequest $request, Audience $audience)
    {
        $audience->update($request->validated());

        return redirect()
            ->route('audiences.show', $audience)
            ->with('success', 'Audience mise à jour avec succès.');
    }

    // ─────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────
    public function destroy(Audience $audience)
    {
        $date = $audience->date_audience->format('d/m/Y');
        $audience->delete();

        return redirect()
            ->route('audiences.index')
            ->with('success', "Audience du {$date} supprimée.");
    }
}
