<?php

namespace App\Http\Controllers;

use App\Models\Reclamation;
use App\Models\Reclamant;
use App\Models\StatutReclamation;
use App\Models\TypeReclamant;
use App\Models\TypeAction;
use App\Models\Structure;
use App\Models\TypeDocument;
use App\Models\ActionReclamation;
use App\Models\Document;
use App\Rules\Telephone;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReclamationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────
    // INDEX — Liste avec filtres et stats
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $reclamations = Reclamation::with([
                'reclamant.typeReclamant',
                'statut',
                'actions' => fn($q) => $q->latest()->limit(1),
            ])
            ->when($request->statut,   fn($q, $v) => $q->whereHas('statut', fn($q) => $q->where('id', $v)))
            ->when($request->type_reclamant, fn($q, $v) =>
                $q->whereHas('reclamant', fn($q) => $q->where('id_type_reclamant', $v))
            )
            ->when($request->search, fn($q, $v) =>
                $q->where(fn($q) =>
                    $q->where('objet', 'like', "%{$v}%")
                      ->orWhereHas('reclamant', fn($q) => $q->where('nom', 'like', "%{$v}%"))
                )
            )
            ->when($request->date_debut, fn($q, $v) => $q->where('date_reception', '>=', $v))
            ->when($request->date_fin,   fn($q, $v) => $q->where('date_reception', '<=', $v))
            ->when($request->periode, function ($q, $v) {
                return match($v) {
                    'ce_mois'    => $q->whereMonth('date_reception', now()->month),
                    'ce_trimestre' => $q->whereBetween('date_reception', [
                        now()->startOfQuarter(), now()->endOfQuarter()
                    ]),
                    'cette_annee' => $q->whereYear('date_reception', now()->year),
                    default => $q,
                };
            })
            ->latest('date_reception')
            ->paginate(15)
            ->withQueryString();

        // Stats groupées (1 seule requête)
        $statsStatuts = Reclamation::query()
            ->join('statut_reclamations', 'reclamations.id_statut_reclamation', '=', 'statut_reclamations.id')
            ->selectRaw('statut_reclamations.statut_reclamation, COUNT(*) as total')
            ->groupBy('statut_reclamations.statut_reclamation')
            ->pluck('total', 'statut_reclamation');

        $stats = [
            'total'      => Reclamation::count(),
            'recues'     => $statsStatuts->get('Reçue', 0),
            'en_cours'   => $statsStatuts->get('En cours', 0),
            'cloturees'  => $statsStatuts->get('Clôturée', 0),
            'en_attente' => Reclamation::enAttente()->count(),
            'ce_mois'    => Reclamation::whereMonth('date_reception', now()->month)->count(),
        ];

        $statuts        = StatutReclamation::orderBy('statut_reclamation')->get();
        $typesReclamant = TypeReclamant::orderBy('type_reclamant')->get();

        return view('reclamations.index', compact(
            'reclamations', 'stats', 'statuts', 'typesReclamant'
        ));
    }

    // ─────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────
    public function create()
    {
        $typesReclamant = TypeReclamant::orderBy('type_reclamant')->get();
        $statuts        = StatutReclamation::orderBy('statut_reclamation')->get();

        return view('reclamations.create', compact('typesReclamant', 'statuts'));
    }

    // ─────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Réclamant
            'nom_reclamant'      => 'required|string|max:255',
            'id_type_reclamant'  => 'required|exists:type_reclamants,id',
            'telephone_reclamant'=> ['nullable', 'regex:/^(\+212|00212|0)(5|6|7)[0-9]{8}$/'],
            'email_reclamant'    => 'nullable|email|max:255',
            'adresse_reclamant'  => 'nullable|string|max:500',
            // Réclamation
            'objet'              => 'required|string|max:500',
            'date_reception'     => 'required|date',
            'details'            => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Créer ou trouver le réclamant
            $reclamant = Reclamant::firstOrCreate(
                [
                    'nom'              => $validated['nom_reclamant'],
                    'id_type_reclamant'=> $validated['id_type_reclamant'],
                ],
                [
                    'telephone' => $validated['telephone_reclamant'] ?? null,
                    'email'     => $validated['email_reclamant'] ?? null,
                    'adresse'   => $validated['adresse_reclamant'] ?? null,
                ]
            );

            // Statut automatique = "قيد المعالجة" (En traitement)
            $statutId = StatutReclamation::where('statut_reclamation', 'قيد المعالجة')->first()?->id
                ?? StatutReclamation::where('statut_reclamation', 'Reçue')->first()?->id
                ?? StatutReclamation::first()?->id;

            $reclamation = Reclamation::create([
                'id_reclamant'          => $reclamant->id,
                'objet'                 => $validated['objet'],
                'date_reception'        => $validated['date_reception'],
                'id_statut_reclamation' => $statutId,
                'details'               => $validated['details'] ?? null,
            ]);

            // Upload document joint (optionnel)
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $path = $file->storeAs(
                    "reclamations/{$reclamation->id}",
                    time() . '_' . $file->getClientOriginalName(),
                    'local'
                );
                $idTypeDoc = \App\Models\TypeDocument::first()?->id;
                $reclamation->documents()->create([
                    'titre_document'   => $file->getClientOriginalName(),
                    'date_depot'       => now()->toDateString(),
                    'fichier_path'     => $path,
                    'id_type_document' => $idTypeDoc,
                ]);
            }
        });

        return redirect()
            ->route('reclamations.index')
            ->with('success', 'Réclamation enregistrée avec succès.');
    }

    // ─────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────
    public function show(Reclamation $reclamation)
    {
        $reclamation->load([
            'reclamant.typeReclamant',
            'statut',
            'actions' => fn($q) => $q->with(['typeAction', 'structure', 'createdBy'])->latest(),
            'documents.typeDocument',
        ]);

        $typesAction    = TypeAction::orderBy('type_action')->get();
        $structures     = Structure::with('typeStructure')->whereNull('id_parent')->with('enfants')->get();
        $statuts        = StatutReclamation::orderBy('statut_reclamation')->get();
        $typesDocuments = TypeDocument::all();

        return view('reclamations.show', compact(
            'reclamation', 'typesAction', 'structures', 'statuts', 'typesDocuments'
        ));
    }

    // ─────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────
    public function edit(Reclamation $reclamation)
    {
        $reclamation->load(['reclamant.typeReclamant', 'statut']);
        $typesReclamant = TypeReclamant::orderBy('type_reclamant')->get();
        $statuts        = StatutReclamation::orderBy('statut_reclamation')->get();

        return view('reclamations.edit', compact('reclamation', 'typesReclamant', 'statuts'));
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────
    public function update(Request $request, Reclamation $reclamation): RedirectResponse
    {
        $validated = $request->validate([
            'objet'                 => 'required|string|max:500',
            'date_reception'        => 'required|date',
            'details'               => 'nullable|string',
            'id_statut_reclamation' => 'required|exists:statut_reclamations,id',
            // Mise à jour du réclamant
            'telephone_reclamant'   => 'nullable|regex:/^(\+212|00212|0)(5|6|7)[0-9]{8}$/',
            'email_reclamant'       => 'nullable|email|max:255',
            'adresse_reclamant'     => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $reclamation) {
            // Mettre à jour les coordonnées du réclamant
            $reclamation->reclamant->update([
                'telephone' => $validated['telephone_reclamant'] ?? $reclamation->reclamant->telephone,
                'email'     => $validated['email_reclamant']     ?? $reclamation->reclamant->email,
                'adresse'   => $validated['adresse_reclamant']   ?? $reclamation->reclamant->adresse,
            ]);

            $reclamation->update([
                'objet'                 => $validated['objet'],
                'date_reception'        => $validated['date_reception'],
                'details'               => $validated['details'],
                'id_statut_reclamation' => $validated['id_statut_reclamation'],
            ]);
        });

        return redirect()
            ->route('reclamations.show', $reclamation)
            ->with('success', 'Réclamation mise à jour.');
    }

    // ─────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────
    public function destroy(Reclamation $reclamation): RedirectResponse
    {
        $objet = $reclamation->objet;
        $reclamation->delete(); // SoftDelete

        return redirect()
            ->route('reclamations.index')
            ->with('success', "Réclamation « {$objet} » supprimée.");
    }

    // ─────────────────────────────────────────
    // ADD ACTION (suivi)
    // ─────────────────────────────────────────
    public function addAction(Request $request, Reclamation $reclamation): RedirectResponse
    {
        $validated = $request->validate([
            'id_type_action'  => 'required|exists:type_actions,id',
            'date_action'     => 'required|date',
            'id_structure'    => 'nullable|exists:structures,id',
            'commentaire'     => 'nullable|string|max:2000',
            'reponse'         => 'nullable|string|max:5000',
            'document_action' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        DB::transaction(function () use ($validated, $request, $reclamation) {
            // Fusionner commentaire + réponse en un seul commentaire si les deux sont présents
            $commentaireFinal = $validated['commentaire'] ?? null;
            if (!empty($validated['reponse'])) {
                $reponseFormatee = "**Réponse :** " . $validated['reponse'];
                $commentaireFinal = $commentaireFinal
                    ? $commentaireFinal . "\n\n" . $reponseFormatee
                    : $reponseFormatee;
            }

            $action = ActionReclamation::create([
                'id_reclamation' => $reclamation->id,
                'id_type_action' => $validated['id_type_action'],
                'statut_action'  => 'Traitée', // valeur fixe interne
                'date_action'    => $validated['date_action'],
                'id_structure'   => $validated['id_structure'] ?? null,
                'commentaire'    => $commentaireFinal,
                'created_by'     => Auth::id(),
            ]);

            // Document joint à l'action
            if ($request->hasFile('document_action')) {
                $file = $request->file('document_action');
                $path = $file->storeAs(
                    "reclamations/{$reclamation->id}/actions/{$action->id}",
                    time() . '_' . $file->getClientOriginalName(),
                    'local'
                );
                $idTypeDoc = \App\Models\TypeDocument::first()?->id;
                Document::create([
                    'id_reclamation'   => $reclamation->id,
                    'id_action'        => $action->id,
                    'titre_document'   => $file->getClientOriginalName(),
                    'date_depot'       => now()->toDateString(),
                    'fichier_path'     => $path,
                    'id_type_document' => $idTypeDoc,
                ]);
            }

            // Mise à jour automatique du statut → "تمت المعالجة"
            $statutTraite = StatutReclamation::where('statut_reclamation', 'تمت المعالجة')->first()
                ?? StatutReclamation::where('statut_reclamation', 'Clôturée')->first();

            if ($statutTraite) {
                $reclamation->update(['id_statut_reclamation' => $statutTraite->id]);
            }
        });

        return redirect()
            ->route('reclamations.show', $reclamation)
            ->with('success', 'Action de suivi enregistrée.');
    }

    // ─────────────────────────────────────────
    // CHANGER STATUT (AJAX-friendly)
    // ─────────────────────────────────────────
    public function changerStatut(Request $request, Reclamation $reclamation): RedirectResponse
    {
        $request->validate([
            'id_statut_reclamation' => 'required|exists:statut_reclamations,id',
        ]);

        $reclamation->update(['id_statut_reclamation' => $request->id_statut_reclamation]);

        return back()->with('success', 'Statut mis à jour.');
    }
}