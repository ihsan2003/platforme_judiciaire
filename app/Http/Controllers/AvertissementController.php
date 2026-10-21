<?php

namespace App\Http\Controllers;

use App\Http\Requests\Avertissements\AvertissementRequest;
use App\Models\Avertissement;
use App\Models\Juge;
use App\Models\Partie;
use App\Models\Province;
use App\Models\Region;
use App\Models\StatutAvertissement;
use App\Models\Tribunal;
use App\Models\TypeAffaire;
use App\Models\TypeJugement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AvertissementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Avertissement::class, 'avertissement');
    }

    // ================= INDEX =================
    public function index(Request $request)
    {
        $avertissements = Avertissement::query()
            ->with(['typeAffaire', 'statut', 'tribunal', 'province.region', 'demandeur', 'defendeur'])
            ->withCount('jugements')
            ->when($request->type, fn($q, $v) => $q->where('id_type_affaire', $v))
            ->when($request->statut, fn($q, $v) => $q->where('id_statut_avertissement', $v))
            ->when($request->tribunal, fn($q, $v) => $q->where('id_tribunal', $v))
            ->when($request->jugement === 'avec', fn($q) => $q->has('jugements'))
            ->when($request->jugement === 'sans', fn($q) => $q->doesntHave('jugements'))
            ->when($request->search, function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('objet_litige', 'like', "%{$v}%")
                      ->orWhere('action_service', 'like', "%{$v}%")
                      ->orWhereHas('demandeur', fn($p) => $p->where('nom_partie', 'like', "%{$v}%"))
                      ->orWhereHas('defendeur', fn($p) => $p->where('nom_partie', 'like', "%{$v}%"));
                });
            })
            ->sortable([
                'id'       => 'id',
                'type'     => fn($q, $dir) => $q->orderBy(
                    TypeAffaire::select('affaire')
                        ->whereColumn('type_affaires.id', 'avertissements.id_type_affaire'),
                    $dir
                ),
                'statut'   => fn($q, $dir) => $q->orderBy(
                    StatutAvertissement::select('statut_avertissement')
                        ->whereColumn('statut_avertissements.id', 'avertissements.id_statut_avertissement'),
                    $dir
                ),
                'tribunal' => fn($q, $dir) => $q->orderBy(
                    Tribunal::select('nom_tribunal')
                        ->whereColumn('tribunaux.id', 'avertissements.id_tribunal'),
                    $dir
                ),
                'ville'    => fn($q, $dir) => $q->orderBy(
                    Province::select('province')
                        ->whereColumn('provinces.id', 'avertissements.id_province'),
                    $dir
                ),
                'region'   => fn($q, $dir) => $q->orderBy(
                    Region::select('region')
                        ->join('provinces', 'provinces.id_region', '=', 'regions.id')
                        ->whereColumn('provinces.id', 'avertissements.id_province')
                        ->limit(1),
                    $dir
                ),
                'jugements' => fn($q, $dir) => $q->orderBy('jugements_count', $dir),
                'date_avertissement' => 'date_avertissement',
                'date'     => 'created_at',
            ], 'id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $typesAffaire = TypeAffaire::orderBy('affaire')->get();
        $tribunaux    = Tribunal::orderBy('nom_tribunal')->get();
        $statuts      = StatutAvertissement::orderBy('id')->get();

        $total = Avertissement::count();
        $avecJugement = Avertissement::has('jugements')->count();

        $stats = [
            'total'         => $total,
            'avec_jugement' => $avecJugement,
            'sans_jugement' => $total - $avecJugement,
            'ce_mois'       => Avertissement::whereYear('created_at', now()->year)
                                    ->whereMonth('created_at', now()->month)
                                    ->count(),
        ];

        return view('avertissements.index', compact('avertissements', 'typesAffaire', 'tribunaux', 'statuts', 'stats'));
    }

    // ================= CREATE =================
    public function create()
    {
        return view('avertissements.create', $this->formData());
    }

    // ================= STORE =================
    public function store(AvertissementRequest $request): RedirectResponse
    {
        $avertissement = DB::transaction(function () use ($request) {
            return Avertissement::create(
                $this->donneesValidees($request) + ['created_by' => Auth::id()]
            );
        });

        return redirect()
            ->route('avertissements.show', $avertissement)
            ->with('success', 'تم إنشاء الإنذار بنجاح.');
    }

    // ================= SHOW =================
    public function show(Avertissement $avertissement)
    {
        $avertissement->load([
            'typeAffaire', 'statut', 'tribunal', 'province.region',
            'demandeur', 'defendeur', 'createdBy:id,name',
            'jugements.juge', 'jugements.typeJugement',
        ]);

        $typesJugement = TypeJugement::orderBy('id')->get();
        $juges = Juge::where('id_tribunal', $avertissement->id_tribunal)
            ->orderBy('nom_complet')
            ->get(['id', 'nom_complet', 'grade']);

        return view('avertissements.show', compact('avertissement', 'typesJugement', 'juges'));
    }

    // ================= EDIT =================
    public function edit(Avertissement $avertissement)
    {
        $avertissement->load('province');

        return view('avertissements.edit', $this->formData($avertissement) + compact('avertissement'));
    }

    // ================= UPDATE =================
    public function update(AvertissementRequest $request, Avertissement $avertissement): RedirectResponse
    {
        $data = $this->donneesValidees($request);

        // RG — Jugement final définitif : le statut reste « حفظ », toute valeur soumise est ignorée.
        if ($avertissement->statutVerrouille()) {
            unset($data['id_statut_avertissement']);
        }

        DB::transaction(fn() => $avertissement->update($data));

        return redirect()
            ->route('avertissements.show', $avertissement)
            ->with('success', 'تم تحديث الإنذار بنجاح.');
    }

    // ================= DESTROY =================
    public function destroy(Avertissement $avertissement): RedirectResponse
    {
        $avertissement->delete();

        return redirect()
            ->route('avertissements.index')
            ->with('success', 'تم حذف الإنذار.');
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    /**
     * Données prêtes à enregistrer.
     */
    private function donneesValidees(AvertissementRequest $request): array
    {
        $data = $request->validated();

        // Statut non fourni : création => défaut du modèle (في طور الإجراء), modification => inchangé
        if (empty($data['id_statut_avertissement'])) {
            unset($data['id_statut_avertissement']);
        }

        return $data;
    }

    /**
     * Données du formulaire. Région > Province sont
     * pré-remplies côté serveur (édition, ou retour après erreur de validation) ; la محكمة est une liste complète.
     */
    private function formData(?Avertissement $avertissement = null): array
    {
        $idProvince = old('id_province', $avertissement?->id_province);
        $idRegion   = old('id_region', $avertissement?->province?->id_region);

        if (! $idRegion && $idProvince) {
            $idRegion = Province::whereKey($idProvince)->value('id_region');
        }

        return [
            'typesAffaire' => TypeAffaire::orderBy('affaire')->get(),
            // « حفظ » n'est pas sélectionnable : il est appliqué automatiquement par un jugement
            // final définitif. On le garde uniquement s'il est déjà le statut de cet avertissement
            // (sinon le select afficherait à tort une autre valeur).
            'statuts'      => StatutAvertissement::orderBy('id')
                ->when(
                    $avertissement?->statut?->statut_avertissement !== StatutAvertissement::CLASSEMENT,
                    fn ($q) => $q->where('statut_avertissement', '!=', StatutAvertissement::CLASSEMENT)
                )
                ->get(),
            'idStatutDefaut' => StatutAvertissement::idDefaut(),
            'regions'      => Region::orderBy('region')->get(),
            'provinces'    => $idRegion
                ? Province::where('id_region', $idRegion)->orderBy('province')->get(['id', 'province'])
                : collect(),
            // Liste complète : la محكمة ne dépend pas de l'إقليم choisi
            'tribunaux'    => Tribunal::orderBy('nom_tribunal')->get(['id', 'nom_tribunal']),
            'idRegion'     => $idRegion,
            'parties'      => Partie::orderBy('nom_partie')->get(['id', 'nom_partie']),
            // Valeur par défaut du المدعي à la création : l'institution (partie est_entraide)
            'idDemandeurDefaut' => Partie::where('est_entraide', true)->value('id'),
            'idDefendeurDefaut' => null,
        ];
    }
}
