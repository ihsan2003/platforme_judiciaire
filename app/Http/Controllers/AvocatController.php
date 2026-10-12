<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Avocat;
use Illuminate\Support\Facades\DB;
use App\Rules\Telephone;


class AvocatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->only('destroy');
    }

    public function index(Request $request)
    {
        // Nombre de dossiers où l'avocat est explicitement affecté
        $query = Avocat::select('avocats.*')->addSelect([
            'nb_dossiers' => DB::table('dossier_partie_avocats as dpa')
                ->join('dossier_parties as dp', 'dp.id', '=', 'dpa.id_dossier_partie')
                ->whereColumn('dpa.id_avocat', 'avocats.id')
                ->selectRaw('count(distinct dp.id_dossier)'),
        ]);

        // ══ Recherche ══
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nom_avocat', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        // ══ Tri ══
        $query->sortable([
            'nom' => 'nom_avocat',
            'telephone' => 'telephone',
            'email' => 'email',
        ], 'nom', 'asc');

        // ══ Pagination ══
        $avocats = $query->paginate(10)->withQueryString();

        return view('avocats.index', compact('avocats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('avocats.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_avocat' => 'required|string|max:255',
            'telephone'  => ['required', new Telephone],
            'email' => 'required|email|unique:avocats,email',
        ]);

        $avocat = Avocat::create([
            'nom_avocat' => $validated['nom_avocat'],
            'telephone'  => $validated['telephone'],
            'email'      => $validated['email'],
        ]);

        return redirect()
            ->route('avocats.index')
            ->with('success', 'تم إنشاء المحامي بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $avocat = Avocat::findOrFail($id);

        // Affectations explicites : partie × dossier × degré
        $affectations = $avocat->affectations()
            ->with(['degre', 'dossierPartie.partie', 'dossierPartie.dossier'])
            ->get()
            ->sortBy(fn($a) => [$a->dossierPartie?->id_dossier, $a->id_degre ?? 0])
            ->values();

        $nbParties = $affectations->pluck('dossierPartie.id_partie')->filter()->unique()->count();

        $dossiers = $avocat->dossiers()
            ->with(['typeAffaire', 'statutDossier', 'dossierTribunaux.tribunal'])
            ->latest('date_ouverture')
            ->get();

        return view('avocats.show', compact('avocat', 'dossiers', 'affectations', 'nbParties'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $avocat = Avocat::findOrFail($id);

        $affectations = $avocat->affectations()
            ->with(['degre', 'dossierPartie.partie', 'dossierPartie.dossier'])
            ->get();

        return view('avocats.edit', compact('avocat', 'affectations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nom_avocat' => 'required|string|max:255',
            'telephone'  => ['required', new Telephone],
            'email' => 'required|email|unique:avocats,email,'.$id,
        ]);

        $avocat = Avocat::findOrFail($id);
        $avocat->update([
            'nom_avocat' => $validated['nom_avocat'],
            'telephone'  => $validated['telephone'],
            'email'      => $validated['email'],
        ]);

        return redirect()
            ->route('avocats.index')
            ->with('success', 'تم تحيين بيانات المحامي بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $avocat = Avocat::findOrFail($id);

        if($avocat->affectations()->exists()){
            return redirect()
                ->route('avocats.index')
                ->with('error', 'لا يمكن حذف هذا المحامي لأنه مرتبط بملفات قضائية.');
        }

        $avocat->delete();

        return redirect()
            ->route('avocats.index')
            ->with('success', 'تم حذف المحامي بنجاح.');
    }
}