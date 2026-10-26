<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\DegreeJuridiction;
use App\Models\Juge;
use App\Models\Partie;
use App\Models\Province;
use App\Models\Region;
use App\Models\Reclamation;
use App\Models\Reclamant;
use App\Models\TypeJugement;
use App\Models\StatutReclamation;
use App\Models\TypeReclamant;
use App\Models\TypeReclamation;
use App\Models\TypeAction;
use App\Models\TypeAffaire;
use App\Models\Tribunal;
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
use Illuminate\Validation\Rule;

class ReclamationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->only('destroy');
    }

    // ─────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $reclamations = Reclamation::with([
                'reclamant.typeReclamant',
                'typeReclamation',
                'statut',
                'actions' => fn($q) => $q->latest()->limit(1),
            ])
            ->when($request->statut,   fn($q, $v) => $q->whereHas('statut', fn($q) => $q->where('id', $v)))
            ->when($request->type_reclamant, fn($q, $v) =>
                $q->whereHas('reclamant', fn($q) => $q->where('id_type_reclamant', $v))
            )
            ->when($request->type_reclamation, fn($q, $v) => $q->where('id_type_reclamation', $v))
            ->when($request->search, fn($q, $v) =>
                $q->where(fn($q) =>
                    $q->where('objet', 'like', "%{$v}%")
                      ->orWhereHas('reclamant', fn($q) => $q->where('nom', 'like', "%{$v}%"))
                      ->orWhereHas('reclamant', fn($q) => $q->where('email', 'like', "%{$v}%"))
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
            ->sortable([
                'objet' => 'objet',
                'date' => 'date_reception',
                'statut' => fn($q, $dir) => $q->orderBy(
                    StatutReclamation::select('statut_reclamation')
                        ->whereColumn('statut_reclamations.id', 'reclamations.id_statut_reclamation'),
                    $dir
                ),
                'type_reclamation' => fn($q, $dir) => $q->orderBy(
                    TypeReclamation::select('type_reclamation')
                        ->whereColumn('type_reclamations.id', 'reclamations.id_type_reclamation'),
                    $dir
                ),
                'reclamant' => fn($q, $dir) => $q->orderBy(
                    Reclamant::select('nom')
                        ->whereColumn('reclamants.id', 'reclamations.id_reclamant'),
                    $dir
                ),
                'type' => fn($q, $dir) => $q->orderBy(
                    TypeReclamant::select('type_reclamant')
                        ->join('reclamants', 'reclamants.id_type_reclamant', '=', 'type_reclamants.id')
                        ->whereColumn('reclamants.id', 'reclamations.id_reclamant'),
                    $dir
                ),
            ], 'date', 'desc')
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
            'en_cours'   => $statsStatuts->get('قيد المعالجة', 0),
            'cloturees'  => $statsStatuts->get('مغلقة', 0),
            'traitees'   => $statsStatuts->get('تمت المعالجة', 0),
            'ce_mois'    => Reclamation::whereMonth('date_reception', now()->month)->count(),
        ];

        $statuts        = StatutReclamation::orderBy('statut_reclamation')->get();
        $typesReclamant = TypeReclamant::orderBy('type_reclamant')->get();
        $typesReclamation = TypeReclamation::orderBy('type_reclamation')->get();

        return view('reclamations.index', compact(
            'reclamations', 'stats', 'statuts', 'typesReclamant', 'typesReclamation'
        ));
    }

    // ─────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────
    public function create()
    {
        $typesReclamant   = TypeReclamant::orderBy('type_reclamant')->get();
        $statuts          = StatutReclamation::orderBy('statut_reclamation')->get();
        $typesReclamation = TypeReclamation::orderBy('type_reclamation')->get();
        $reclamants       = Reclamant::orderBy('nom')->get();
        $typesDocuments   = TypeDocument::orderBy('type_document')->get();

        return view('reclamations.create', $this->donneesLocalisation() + $this->donneesDossier() + compact(
            'typesReclamant', 'statuts', 'typesReclamation', 'reclamants', 'typesDocuments'
        ));
    }

    // ─────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Réclamant (existant si id_reclamant fourni, sinon nouveau via nom_reclamant)
            'id_reclamant'        => 'nullable|exists:reclamants,id',
            'nom_reclamant'       => 'required_without:id_reclamant|nullable|string|max:255',
            'id_type_reclamant'   => 'required_without:id_reclamant|nullable|exists:type_reclamants,id',
            'telephone_reclamant' => ['nullable', 'regex:/^(\+212|00212|0)(5|6|7)[0-9]{8}$/'],
            'email_reclamant'     => 'nullable|email|max:255',
            'adresse_reclamant'   => 'nullable|string|max:500',
            // Réclamation
            'objet'              => 'required|string|max:500',
            'id_type_reclamation'=> 'required|exists:type_reclamations,id',
            'date_reception'     => 'required|date',
            'details'            => 'nullable|string',

            'document'           => 'nullable|file|max:10240',
            'id_type_document'   => 'nullable|exists:type_documents,id',
        ] + $this->reglesLocalisation($request) + $this->reglesDossier());

        DB::transaction(function () use ($validated, $request) {

            // Réclamant existant sélectionné, ou nouveau réclamant à créer
            if (!empty($validated['id_reclamant'])) {
                $reclamant = Reclamant::findOrFail($validated['id_reclamant']);
            } else {
                $reclamant = Reclamant::create([
                    'nom'               => $validated['nom_reclamant'],
                    'id_type_reclamant' => $validated['id_type_reclamant'],
                    'telephone'         => $validated['telephone_reclamant'] ?? null,
                    'email'             => $validated['email_reclamant'] ?? null,
                    'adresse'           => $validated['adresse_reclamant'] ?? null,
                ]);
            }

            // Statut automatique = "قيد المعالجة" (En traitement)
            $statutId = StatutReclamation::where('statut_reclamation', 'قيد المعالجة')->first()?->id
                ?? StatutReclamation::where('statut_reclamation', 'Reçue')->first()?->id
                ?? StatutReclamation::first()?->id;

            $reclamation = Reclamation::create([
                'id_reclamant'          => $reclamant->id,
                'id_type_reclamation'   => $validated['id_type_reclamation'],
                'objet'                 => $validated['objet'],
                'date_reception'        => $validated['date_reception'],
                'id_statut_reclamation' => $statutId,
                'details'               => $validated['details'] ?? null,
                'id_province'           => $validated['id_province'] ?? null,
                'id_commune'            => $validated['id_commune'] ?? null,
            ] + $this->champsDossier($validated, $request, $reclamant));

            // Upload document joint (optionnel)
            if ($request->hasFile('document')) {
                $file = $request->file('document');

                $path = $file->storeAs(
                    "reclamations/{$reclamation->id}",
                    time() . '_' . $file->getClientOriginalName(),
                    'local'
                );

                $idTypeDoc = $validated['id_type_document']
                    ?? TypeDocument::where('type_document', 'وثيقة')->first()?->id
                    ?? TypeDocument::first()?->id; 


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
            ->with('success', 'تم تسجيل الشكاية بنجاح.');
    }

    // ─────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────
    public function show(Reclamation $reclamation)
    {
        $reclamation->load([
            'reclamant.typeReclamant',
            'typeReclamation',
            'statut',
            'province.region',
            'commune',
            'degre', 'tribunal', 'typeAffaire', 'demandeur', 'defendeur',
            'actions' => fn($q) => $q->with(['typeAction', 'structure', 'createdBy'])->latest(),
            'documents.typeDocument',
            'jugements.juge', 'jugements.typeJugement',
        ]);

        $typesJugement = TypeJugement::orderBy('id')->get();
        // Une réclamation n'est pas liée à un tribunal : on propose tous les juges.
        $juges = Juge::with('tribunal:id,nom_tribunal')
            ->orderBy('nom_complet')
            ->get(['id', 'nom_complet', 'grade', 'id_tribunal']);

        $typesAction    = TypeAction::orderBy('type_action')->get();
        $structures = Structure::with(['typeStructure', 'enfants.enfants'])
                ->whereNull('id_parent')
                ->get();
        $statuts        = StatutReclamation::orderBy('statut_reclamation')->get();
        $typesDocuments = TypeDocument::all();

        return view('reclamations.show', compact(
            'reclamation', 'typesAction', 'structures', 'statuts', 'typesDocuments',
            'typesJugement', 'juges'
        ));
    }

    // ─────────────────────────────────────────
    // DOWNLOAD DOCUMENT
    // ─────────────────────────────────────────
    public function downloadDocument(Reclamation $reclamation, Document $document)
    {
        // Sécurité : le document doit bien appartenir à cette réclamation
        abort_unless($document->id_reclamation === $reclamation->id, 404);

        abort_unless(
            Storage::disk('local')->exists($document->fichier_path),
            404,
            'Fichier introuvable.'
        );

        return Storage::disk('local')->download(
            $document->fichier_path,
            $document->titre_document
        );
    }

    // ─────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────
    public function edit(Reclamation $reclamation)
    {
        // Charger les relations nécessaires
        $reclamation->load([
            'reclamant.typeReclamant',
            'typeReclamation',
            'statut',
            'province',
        ]);

        // Tous les réclamants avec leur type (nécessaire pour TomSelect)
        $reclamants = Reclamant::with('typeReclamant')
            ->orderBy('nom')
            ->get();

        $typesReclamant = TypeReclamant::orderBy('type_reclamant')->get();

        $statuts = StatutReclamation::orderBy('statut_reclamation')->get();

        $typesReclamation = TypeReclamation::orderBy('type_reclamation')->get();


        return view('reclamations.edit', $this->donneesLocalisation($reclamation) + $this->donneesDossier() + compact(
            'reclamation',
            'reclamants',
            'typesReclamant',
            'statuts',
            'typesReclamation'
        ));
    }


    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────
    public function update(Request $request, Reclamation $reclamation): RedirectResponse
    {
        $validated = $request->validate([

            // Réclamation
            'objet'                 => 'required|string|max:500',
            'id_type_reclamation'   => 'required|exists:type_reclamations,id',
            'date_reception'        => 'required|date',
            'details'               => 'nullable|string',
            'id_statut_reclamation' => 'required|exists:statut_reclamations,id',

            // Réclamant
            'id_reclamant'          => 'required|exists:reclamants,id',

        ] + $this->reglesLocalisation($request) + $this->reglesDossier());


        DB::transaction(function () use ($validated, $reclamation, $request) {

            $reclamant = Reclamant::findOrFail($validated['id_reclamant']);


            // Mise à jour de la réclamation
            $reclamation->update([

                'id_reclamant'          => $validated['id_reclamant'],

                'objet'                 => $validated['objet'],

                'id_type_reclamation'   => $validated['id_type_reclamation'],

                'date_reception'        => $validated['date_reception'],

                'details'               => $validated['details'] ?? null,

                'id_statut_reclamation' => $validated['id_statut_reclamation'],

                'id_province'           => $validated['id_province'] ?? null,

                'id_commune'            => $validated['id_commune'] ?? null,

            ] + $this->champsDossier($validated, $request, $reclamant, modification: true));


        });


        return redirect()

            ->route('reclamations.show', $reclamation)

            ->with('success', 'تم تحديث الشكاية بنجاح.');
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
            ->with('success', "تم حذف الشكاية « {$objet} » بنجاح.");
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
                $reponseFormatee = "رد : " . $validated['reponse'];

                $commentaireFinal = $commentaireFinal
                    ? $commentaireFinal . "\n\n" . $reponseFormatee
                    : $reponseFormatee;
            }

            $action = ActionReclamation::create([
                'id_reclamation' => $reclamation->id,
                'id_type_action' => $validated['id_type_action'],
                'statut_action'  => 'تمت المعالجة',
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
            ->with('success', 'تم تسجيل إجراء المتابعة بنجاح.');
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

        return back()->with('success', 'تم تحديث حالة الشكاية بنجاح.');
    }

    // ─────────────────────────────────────────
    // DOSSIER : réclamations « شكايات مرتبطة بالمنازعات القضائية »
    // ─────────────────────────────────────────

    /**
     * Listes du bloc « dossier » (درجة التقاضي، المحكمة، الشعبة، المدعي، المدعى عليه).
     * La sélection courante est gérée par la vue (old() ou valeurs de la réclamation).
     */
    private function donneesDossier(): array
    {
        return [
            'idTypeJudiciaire' => TypeReclamation::idJudiciaire(),
            'degres'           => DegreeJuridiction::orderBy('ordre')->get(['id', 'degre_juridiction']),
            'tribunaux'        => Tribunal::orderBy('nom_tribunal')->get(['id', 'nom_tribunal', 'id_degre']),
            'typesAffaire'     => TypeAffaire::orderBy('affaire')->get(['id', 'affaire']),
            'parties'          => Partie::orderBy('nom_partie')->get(['id', 'nom_partie']),
            // المدعي par défaut : l'institution (partie est_entraide = true)
            'idDemandeurDefaut' => $this->idInstitution(),
        ];
    }

    /**
     * Règles de validation du bloc « dossier ». Tous les champs sont facultatifs
     * (les anciennes réclamations n'en ont pas) : ils ne sont pris en compte que
     * pour le type judiciaire (voir champsDossier()).
     */
    private function reglesDossier(): array
    {
        return [
            'id_degre'        => 'nullable|exists:degre_juridictions,id',
            'id_tribunal'     => 'nullable|exists:tribunaux,id',
            'id_type_affaire' => 'nullable|exists:type_affaires,id',
            'id_demandeur'    => 'nullable|exists:parties,id',
            'id_defendeur'    => 'nullable|exists:parties,id|different:id_demandeur',
        ];
    }

    /**
     * Champs « dossier » à enregistrer avec la réclamation.
     *
     *  - type ≠ judiciaire : tous remis à null (la réclamation n'est pas un dossier) ;
     *  - type judiciaire   : valeurs saisies, avec par défaut
     *      المدعي      = l'institution (partie est_entraide = true),
     *      المدعى عليه = le réclamant, converti en Partie (créée ou retrouvée par son nom) ;
     *  - modification sans aucun de ces champs dans la requête (ex. changement rapide
     *    de statut depuis la fiche) : rien n'est modifié.
     */
    private function champsDossier(array $validated, Request $request, Reclamant $reclamant, bool $modification = false): array
    {
        $cles = ['id_degre', 'id_tribunal', 'id_type_affaire', 'id_demandeur', 'id_defendeur'];

        $estJudiciaire = (int) $validated['id_type_reclamation'] === TypeReclamation::idJudiciaire();

        if (! $estJudiciaire) {
            return array_fill_keys($cles, null);
        }

        if ($modification && ! $request->hasAny($cles)) {
            return [];
        }

        return [
            'id_degre'        => $validated['id_degre'] ?? null,
            'id_tribunal'     => $validated['id_tribunal'] ?? null,
            'id_type_affaire' => $validated['id_type_affaire'] ?? null,
            'id_demandeur'    => $validated['id_demandeur'] ?? $this->idInstitution(),
            'id_defendeur'    => $validated['id_defendeur'] ?? $this->partieDepuisReclamant($reclamant)->id,
        ];
    }

    /** Id de la partie « institution » (parties.est_entraide = true), s'il y en a une. */
    private function idInstitution(): ?int
    {
        return Partie::where('est_entraide', true)->value('id');
    }

    /**
     * Partie correspondant au réclamant : celle qui porte déjà son nom si elle existe
     * (l'institution est exclue), sinon une nouvelle Partie créée à partir de ses coordonnées.
     */
    private function partieDepuisReclamant(Reclamant $reclamant): Partie
    {
        $existante = Partie::where('nom_partie', $reclamant->nom)
            ->where('est_entraide', false)
            ->first();

        if ($existante) {
            return $existante;
        }

        $typesPersonneMorale = ['شركة', 'مقاولة', 'مرفق عمومي', 'مؤسسة الوسيط', 'مديرية جهوية', 'مديرية إقليمية'];

        return Partie::create([
            'nom_partie'         => $reclamant->nom,
            'type_personne'      => in_array($reclamant->typeReclamant?->type_reclamant, $typesPersonneMorale, true)
                                        ? 'اعتباري'
                                        : 'ذاتي',
            'identifiant_unique' => null,
            'telephone'          => $reclamant->telephone,
            'email'              => $reclamant->email,
            'adresse'            => $reclamant->adresse,
        ]);
    }

    // ─────────────────────────────────────────
    // LOCALISATION : الجهة > الإقليم / العمالة > الجماعة
    // ─────────────────────────────────────────

    /**
     * Données de la cascade Région > Province > Commune.
     * Pré-remplies côté serveur à l'édition, ou au retour d'une erreur de validation.
     * Seuls id_province / id_commune sont stockés : la région se déduit de la province.
     */
    private function donneesLocalisation(?Reclamation $reclamation = null): array
    {
        $idProvince = old('id_province', $reclamation?->id_province);
        $idCommune  = old('id_commune', $reclamation?->id_commune);
        $idRegion   = old('id_region', $reclamation?->province?->id_region);

        if (! $idRegion && $idProvince) {
            $idRegion = Province::whereKey($idProvince)->value('id_region');
        }

        return [
            'regions'       => Region::orderBy('region')->get(),
            'provinces'     => $idRegion
                ? Province::where('id_region', $idRegion)->orderBy('province')->get(['id', 'province'])
                : collect(),
            'communes'      => $idProvince
                ? Commune::where('id_province', $idProvince)->orderBy('commune')->get(['id', 'commune'])
                : collect(),
            'idRegion'      => $idRegion,
            'idProvinceSel' => $idProvince,
            'idCommuneSel'  => $idCommune,
        ];
    }

    /**
     * Règles de validation de la localisation (tous les champs sont facultatifs,
     * les anciennes réclamations n'en ont pas) avec cohérence entre les niveaux.
     */
    private function reglesLocalisation(Request $request): array
    {
        return [
            'id_region' => 'nullable|exists:regions,id',

            // L'إقليم doit appartenir à la جهة choisie (si elle est renseignée)
            'id_province' => [
                'nullable',
                $request->filled('id_region')
                    ? Rule::exists('provinces', 'id')->where('id_region', $request->input('id_region'))
                    : 'exists:provinces,id',
            ],

            // La جماعة doit appartenir à l'إقليم choisi
            'id_commune' => [
                'nullable',
                Rule::exists('communes', 'id')->where('id_province', $request->input('id_province')),
            ],
        ];
    }
}