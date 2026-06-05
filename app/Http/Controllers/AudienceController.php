<?php

namespace App\Http\Controllers;

use App\Http\Requests\Audiences\StoreAudienceRequest;
use App\Http\Requests\Audiences\UpdateAudienceRequest;
use App\Models\Audience;
use App\Models\DossierTribunal;
use App\Models\DossierJudiciaire;
use App\Models\TypeAudience;
use App\Models\Juge;

class AudienceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────
    public function index()
    {
        $audiences = Audience::with([
                'dossierTribunal.dossier',
                'dossierTribunal.tribunal',
                'dossierTribunal.degre',
                'typeAudience',
                'juge',
            ])
            ->when(request('juge'),    fn($q, $v) => $q->where('id_juge', $v))
            ->when(request('type'),    fn($q, $v) => $q->where('id_type_audience', $v))
            ->when(request('dossier'), fn($q, $v) => $q->whereHas(
                'dossierTribunal', fn($q) => $q->where('id_dossier', $v)
            ))
            ->latest('date_audience')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'aujourd_hui'        => Audience::whereDate('date_audience', today())->count(),
            'cette_semaine'      => Audience::whereBetween('date_audience', [today(), today()->addDays(7)])->count(),
            'passees_sans_suite' => Audience::whereDate('date_audience', '<', today())
                ->whereNull('resultat_audience')
                ->count(),
        ];

        $juges        = Juge::orderBy('nom_complet')->get();
        $typesAudience = TypeAudience::orderBy('type_audience')->get();

        return view('audiences.index', compact('audiences', 'stats', 'juges', 'typesAudience'));
    }

    // ─────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────
    public function create()
    {
        $dossierId         = request('dossier_id');
        $dossierTribunalId = request('dossier_tribunal_id'); // optionnel : pré-sélectionner une instance

        if (! $dossierId) {
            return redirect()
                ->route('audiences.index')
                ->with('error', 'لم يتم تحديد الملف.');
        }

        // Charger le dossier + parties
        $dossier = DossierJudiciaire::with('parties')->findOrFail($dossierId);

        // RG — Minimum 2 parties (المدعي et المدعى عليه)
        if (! $dossier->peutAvoirAudience()) {
            $manquants = implode('" et "', $dossier->typesPartiesManquants());

            return redirect()
                ->route('dossiers.show', $dossier->id)
                ->with('error', "يتعذر إنشاء جلسة: الصفة \"{$manquants}\" غير موجودة في هذا الملف.");
        }

        // RG — Seules les instances OUVERTES peuvent recevoir de nouvelles audiences
        // Une instance clôturée (date_fin renseignée) ne peut plus recevoir d'audiences
        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal', 'degre'])
            ->where('id_dossier', $dossierId)
            ->whereNull('date_fin') // uniquement les instances ouvertes
            ->get();

        if ($dossierTribunaux->isEmpty()) {
            return redirect()
                ->route('dossiers.show', $dossier->id)
                ->with('error', 'لا توجد أي درجة قضائية مفتوحة لهذا الملف. يرجى أولاً تعيين المحكمة.');
        }

        // RG — Une instance qui a déjà une audience الحكم ne peut plus recevoir d'audiences
        // (le cycle est terminé pour cette instance — on attend le jugement)
        $dossierTribunaux = $dossierTribunaux->filter(function ($dt) {
            // Bloquer si l'audience الحكم existe déjà ET un jugement existe aussi
            // (si الحكم existe mais pas de jugement, on bloque aussi les nouvelles audiences)
            return $dt->audienceHoukm() === null;
        });

        if ($dossierTribunaux->isEmpty()) {
            return redirect()
                ->route('dossiers.show', $dossier->id)
                ->with('error', 'تم تسجيل جلسة من نوع "الحكم" لجميع الدرجات القضائية المفتوحة. يرجى تسجيل الحكم.');
        }

        $typesAudience = TypeAudience::orderBy('type_audience')->get();
        $juges         = Juge::with('tribunal')->orderBy('nom_complet')->get();

        // Valeur par défaut : date de la prochaine audience depuis la dernière audience de l'instance
        $dateAudienceParDefaut = null;

        // Si une instance spécifique est pré-sélectionnée, chercher dans cette instance uniquement
        $instanceCible = $dossierTribunalId
            ? $dossierTribunaux->firstWhere('id', $dossierTribunalId)
            : $dossierTribunaux->first();

        if ($instanceCible) {
            $derniereAudience = Audience::where('id_dossier_tribunal', $instanceCible->id)
                ->latest('date_audience')
                ->first();

            if ($derniereAudience?->date_prochaine_audience) {
                $dateAudienceParDefaut = $derniereAudience->date_prochaine_audience->format('Y-m-d');
            }
        }

        return view('audiences.create', compact(
            'dossierTribunaux',
            'typesAudience',
            'juges',
            'dateAudienceParDefaut',
            'dossierTribunalId'
        ));
    }

    // ─────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────
    public function store(StoreAudienceRequest $request)
    {
        $dossierTribunal = DossierTribunal::with(['dossier.parties', 'audiences.typeAudience', 'degre'])
            ->findOrFail($request->id_dossier_tribunal);

        // RG1 — Sécurité backend : vérifier les parties
        if (! $dossierTribunal->dossier->peutAvoirAudience()) {
            $manquants = implode('" et "', $dossierTribunal->dossier->typesPartiesManquants());

            return back()->withErrors([
                'id_dossier_tribunal' => "لا يمكن لهذا الملف أن يتوفر على جلسة: الصفة \"{$manquants}\" غير موجودة."
            ]);
        }

        // RG2 — L'instance doit être ouverte
        if (! $dossierTribunal->estOuverte()) {
            return back()->withErrors([
                'id_dossier_tribunal' => "هذه الدرجة القضائية مغلقة. لا يمكن إضافة جلسة إليها."
            ]);
        }

        // RG3 — Si une audience الحكم existe déjà dans CETTE instance, on ne peut plus en ajouter
        if ($dossierTribunal->audienceHoukm() !== null) {
            return back()->withErrors([
                'id_dossier_tribunal' => "تم تسجيل جلسة من نوع \"الحكم\" مسبقاً لهذه الدرجة القضائية. يرجى تسجيل الحكم."
            ]);
        }

        // RG4 — Vérifier le type de la nouvelle audience
        $typeAudience = TypeAudience::find($request->id_type_audience);

        if ($typeAudience && $typeAudience->type_audience === 'الحكم') {

            // Vérifier qu'il n'y a pas déjà un jugement dans cette instance
            if ($dossierTribunal->aUnJugement()) {
                return back()->withErrors([
                    'id_type_audience' => "يوجد حكم مسجل مسبقاً لهذه الدرجة القضائية. لا يمكن إضافة جلسة من نوع \"الحكم\"."
                ]);
            }
        }

        $audience = Audience::create($request->validated());

        return redirect()
            ->route('audiences.show', $audience)
            ->with(
                'success',
                'تم إنشاء الجلسة بتاريخ ' . $audience->date_audience->format('d/m/Y') . ' بنجاح.'
            );
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

        // Autres audiences DE LA MÊME INSTANCE (même degré, même tribunal)
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
        // En édition, on propose uniquement l'instance à laquelle appartient cette audience
        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal', 'degre'])
            ->where('id', $audience->id_dossier_tribunal)
            ->get();

        $typesAudience = TypeAudience::orderBy('type_audience')->get();
        $juges         = Juge::with('tribunal')->orderBy('nom_complet')->get();

        return view('audiences.edit', compact('audience', 'dossierTribunaux', 'typesAudience', 'juges'));
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────
    public function update(UpdateAudienceRequest $request, Audience $audience)
    {
        $dossierTribunal = $audience->dossierTribunal;

        // RG — Si on change le type vers الحكم, vérifier qu'il n'y en a pas déjà une autre
        $typeAudience = TypeAudience::find($request->id_type_audience);

        if ($typeAudience && $typeAudience->type_audience === 'الحكم') {

            $autreHoukm = Audience::where('id_dossier_tribunal', $audience->id_dossier_tribunal)
                ->where('id', '!=', $audience->id)
                ->whereHas('typeAudience', fn($q) => $q->where('type_audience', 'الحكم'))
                ->exists();

            if ($autreHoukm) {
                return back()->withErrors([
                    'id_type_audience' => 'توجد بالفعل جلسة أخرى من نوع "الحكم" ضمن هذه الدرجة القضائية.'
                ]);
            }
        }

        $audience->update($request->validated());

        return redirect()
            ->route('audiences.show', $audience)
            ->with('success', 'تم تحيين بيانات الجلسة بنجاح.');
    }

    // ─────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────
    public function destroy(Audience $audience)
    {
        // RG — Impossible de supprimer l'audience الحكم si un jugement existe déjà
        $typeAudience = $audience->typeAudience;

        if ($typeAudience?->type_audience === 'الحكم') {
            if ($audience->dossierTribunal->aUnJugement()) {
                return redirect()
                    ->route('audiences.show', $audience)
                    ->with('error', 'يتعذر حذف جلسة "الحكم" لأنه تم تسجيل حكم مسبقاً لهذه الدرجة القضائية.');
            }
        }

        $date = $audience->date_audience->format('d/m/Y');
        $audience->delete();

        return redirect()
            ->route('audiences.index')
            ->with('success', "تم حذف الجلسة بتاريخ {$date} بنجاح.");
    }
}