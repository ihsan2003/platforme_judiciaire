<?php

namespace App\Http\Controllers;

use App\Models\DossierJudiciaire;
use App\Models\DossierPartie;
use App\Models\DossierTribunal;
use App\Models\TypeAffaire;
use App\Models\StatutDossier;
use App\Models\Tribunal;
use App\Models\TypePartie;
use App\Models\Avocat;
use App\Models\Partie;
use App\Models\DegreeJuridiction;
use App\Models\Jugement;
use App\Models\Region;
use App\Models\TypeDocument;
use App\Http\Requests\Dossiers\StoreDossierRequest;
use App\Http\Requests\Dossiers\UpdateDossierRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DossierJudiciaireController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(DossierJudiciaire::class, 'dossier');
    }

    // ================= INDEX =================
    public function index(Request $request)
    {
        $dossiers = DossierJudiciaire::query()
            ->with([
                'typeAffaire',
                'statut',
                'dossierTribunaux.tribunal.province.region',
            ])
            ->when($request->type, fn($q, $v) => $q->parType($v))
            ->when($request->statut, fn($q, $v) => $q->whereHas(
                'statut', fn($q) => $q->where('id', $v)
            ))
            ->when($request->search, fn($q, $v) =>
                $q->where(fn($q) =>
                    $q->where('numero_dossier_tribunal', 'like', "%{$v}%")
                      ->orWhere('id', 'like', "%{$v}%")
                )
            )
            ->when($request->date_debut, fn($q, $v) => $q->where('date_ouverture', '>=', $v))
            ->when($request->date_fin, fn($q, $v) => $q->where('date_ouverture', '<=', $v))
            ->sortable([
                'id' => 'id',
                'numero' => 'numero_dossier_tribunal',
                'type' => fn($q, $dir) => $q->orderBy(
                    TypeAffaire::select('affaire')
                        ->whereColumn('type_affaires.id', 'dossier_judiciaires.id_type_affaire'),
                    $dir
                ),
                'statut' => fn($q, $dir) => $q->orderBy(
                    StatutDossier::select('statut_dossier')
                        ->whereColumn('statut_dossiers.id', 'dossier_judiciaires.id_statut_dossier'),
                    $dir
                ),
                'date' => 'date_ouverture',
                'region' => fn($q, $dir) => $q->orderBy(
                    Region::select('region')
                        ->join('provinces', 'provinces.id_region', '=', 'regions.id')
                        ->join('tribunaux', 'tribunaux.id_province', '=', 'provinces.id')
                        ->join('dossier_tribunaux', 'dossier_tribunaux.id_tribunal', '=', 'tribunaux.id')
                        ->whereColumn('dossier_tribunaux.id_dossier', 'dossier_judiciaires.id')
                        ->limit(1),
                    $dir
                ),
            ], 'id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $typesAffaire = TypeAffaire::orderBy('affaire')->get();
        $statutDossiers = StatutDossier::orderBy('statut_dossier')->get();

        $stats = [
            'total' => DossierJudiciaire::count(),
            'actifs' => DossierJudiciaire::actifs()->count(),
            'ce_mois' => DossierJudiciaire::whereMonth('created_at', now()->month)->count(),
        ];

        return view('dossiers.index', compact('dossiers', 'typesAffaire', 'statutDossiers', 'stats'));
    }

    // ================= CREATE =================
    public function create()
    {
        $typesAffaire = TypeAffaire::orderBy('affaire')->get();
        return view('dossiers.create', compact('typesAffaire'));
    }

    // ================= STORE =================
    public function store(StoreDossierRequest $request): RedirectResponse
    {
        // Assemblage du numéro Mahakim à partir des 3 champs
        $numero_mahakim = $request->annee_mahakim . ' / ' . $request->code_mahakim . ' / ' . $request->ordre_mahakim;

        DB::transaction(function () use ($request, $numero_mahakim) {
            DossierJudiciaire::create([
                'numero_dossier_tribunal' => $numero_mahakim,
                'id_type_affaire'         => $request->id_type_affaire,
                'date_ouverture'          => $request->date_ouverture,
                'date_cloture'            => $request->date_cloture,
                'created_by'              => Auth::id(),
            ]);
        });

        return redirect()->route('dossiers.index')->with('success', 'تم إنشاء الملف بنجاح.');
    }

    // ================= SHOW =================
    public function show(DossierJudiciaire $dossier)
    {
        $dossier->load([
            'typeAffaire',
            'statut',
            'createdBy:id,name',
            'dossierTribunaux.tribunal.typeTribunal',
            'dossierTribunaux.tribunal.province.region',
            'dossierTribunaux.degre',
            'dossierTribunaux.audiences.typeAudience',
            'dossierTribunaux.audiences.juge',
            'dossierTribunaux.jugements.juge',
            'dossierTribunaux.jugements.recours.typeRecours',
            'dossierTribunaux.jugements.executions.statut',
            'dossierTribunaux.jugements.executions.responsable',
            'dossierTribunaux.jugements.finance',
            'dossierTribunaux.jugements.parties',
            'documents.typeDocument',
            'documents.partie',
        ]);

        $dossierParties = DossierPartie::with([
            'partie.avocat',
            'typePartie',
        ])
        ->where('id_dossier', $dossier->id)
        ->get();

        // ✅ IMPORTANT: variables conservées (NE PAS SUPPRIMER)
        $tribunaux = Tribunal::with('typeTribunal')->orderBy('nom_tribunal')->get();
        $typesPartie = TypePartie::orderBy('type_partie')->get();
        $avocats = Avocat::orderBy('nom_avocat')->get(); // 🔥 CORRIGÉ
        $degresJuridiction = DegreeJuridiction::orderBy('degre_juridiction')->get();
        $parties = Partie::orderBy('nom_partie')->get();

        $stats = [
            'nb_audiences' => $dossier->dossierTribunaux->flatMap->audiences->count(),
            'nb_jugements' => $dossier->dossierTribunaux->flatMap->jugements->count(),
            'nb_parties' => $dossierParties->count(),
            'nb_tribunaux' => $dossier->dossierTribunaux->count(),
            'nb_documents' => $dossier->documents->count(),
        ];

        $typesDocuments = TypeDocument::all();
        $regions = Region::orderBy('region')->get();

        // Chargé une seule fois pour afficher "الصفة" dans les onglets
        // "الأحكام" et "الوضعية المالية", à partir de
        // $partie->pivot->id_position_institution.
        $positionsInstitution = \App\Models\PositionInstitution::pluck('position', 'id');

        return view('dossiers.show', compact(
            'dossier',
            'dossierParties',
            'tribunaux',
            'typesPartie',
            'avocats', 
            'degresJuridiction',
            'parties',
            'stats',
            'typesDocuments',
            'regions',
            'positionsInstitution'
        ));
    }

    // ================= EDIT =================
    public function edit(DossierJudiciaire $dossier)
    {
        $typesAffaire = TypeAffaire::orderBy('affaire')->get();
        $statutDossiers = StatutDossier::orderBy('statut_dossier')->get();

        $dossier->load([
            'typeAffaire',
            'statut',
            'createdBy:id,name',
            'dossierTribunaux.tribunal',
            'parties',
        ]);

        return view('dossiers.edit', compact('dossier', 'typesAffaire', 'statutDossiers'));
    }

    // ================= UPDATE =================
    public function update(UpdateDossierRequest $request, DossierJudiciaire $dossier): RedirectResponse
    {
        // Assemblage du numéro Mahakim à partir des 3 champs
        $numero_mahakim = $request->annee_mahakim . ' / ' . $request->code_mahakim . ' / ' . $request->ordre_mahakim;

        $dossier->update([
            'numero_dossier_tribunal' => $numero_mahakim,
            'id_type_affaire'         => $request->id_type_affaire,
            'id_statut_dossier'       => $request->id_statut_dossier,
            'date_ouverture'          => $request->date_ouverture,
            'date_cloture'            => $request->date_cloture,
        ]);

        return redirect()->route('dossiers.show', $dossier)->with('success', 'تم تحديث الملف بنجاح.');
    }

    // ================= DESTROY =================
    public function destroy(DossierJudiciaire $dossier): RedirectResponse
    {
        $executionEnCours = $dossier->jugements()
            ->whereHas('executions', fn($q) =>
                $q->whereHas('statut', fn($q) =>
                    $q->where('statut_execution', 'En cours')
                )
            )
            ->exists();

        if ($executionEnCours) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->with('error', 'لا يمكن أرشفة هذا الملف لأن هناك تنفيذًا قيد التنفيذ.');
        }

        $numero = $dossier->numero_dossier_interne;
        $dossier->delete();

        return redirect()
            ->route('dossiers.index')
            ->with('success', "تم أرشفة الملف « {$numero} » بنجاح.");
    }

    // ================= EXPORT PDF =================
    public function exportPdf(DossierJudiciaire $dossier): Response
    {
        $this->authorize('view', $dossier);

        $dossier->load([
            'typeAffaire',
            'statut',
            'createdBy:id,name',
            'dossierTribunaux.tribunal',
            'dossierTribunaux.degre',
            'dossierTribunaux.jugements.juge',
            'dossierTribunaux.jugements.finance',
            'dossierTribunaux.jugements.parties',
            'documents.typeDocument',
            'documents.partie',
        ]);

        $dossier->dossierParties = DossierPartie::with(['partie', 'typePartie', 'avocat'])
            ->where('id_dossier', $dossier->id)
            ->get();

        $pdf = Pdf::loadView('dossiers.pdf', compact('dossier'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("dossier-{$dossier->numero_dossier_interne}.pdf");
    }
}