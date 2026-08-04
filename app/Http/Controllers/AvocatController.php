<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Avocat;
use App\Models\Partie;
use App\Rules\Telephone;


class AvocatController extends Controller
{
 

    
    public function index(Request $request)
    {
        $query = Avocat::withCount('partiesAssociees');

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
        $parties = Partie::orderBy('nom_partie')->get();

        return view('avocats.create', compact('parties'));
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
            'parties' => ['nullable', 'array'],
            'parties.*' => ['integer', 'exists:parties,id'],
        ]);

        $avocat = Avocat::create([
            'nom_avocat' => $validated['nom_avocat'],
            'telephone'  => $validated['telephone'],
            'email'      => $validated['email'],
        ]);

        // Associer les parties sélectionnées à ce nouveau محامٍ
        if (!empty($validated['parties'])) {
            Partie::whereIn('id', $validated['parties'])->update(['id_avocat' => $avocat->id]);
        }

        return redirect()
            ->route('avocats.index')
            ->with('success', 'تم إنشاء المحامي بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $avocat = Avocat::with(['parties' => function ($q) {
            $q->distinct('parties.id');
        }])->findOrFail($id);
        return view('avocats.show', compact('avocat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $avocat = Avocat::findOrFail($id);
        $parties = Partie::orderBy('nom_partie')->get();
        $selectedPartyIds = Partie::where('id_avocat', $avocat->id)->pluck('id')->toArray();

        return view('avocats.edit', compact('avocat', 'parties', 'selectedPartyIds'));
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
            'parties' => ['nullable', 'array'],
            'parties.*' => ['integer', 'exists:parties,id'],
        ]);

        $avocat = Avocat::findOrFail($id);
        $avocat->update([
            'nom_avocat' => $validated['nom_avocat'],
            'telephone'  => $validated['telephone'],
            'email'      => $validated['email'],
        ]);

        $selectedIds = $validated['parties'] ?? [];

        // Détacher les parties qui ne sont plus sélectionnées
        Partie::where('id_avocat', $avocat->id)
            ->whereNotIn('id', $selectedIds)
            ->update(['id_avocat' => null]);

        // Associer les parties sélectionnées à ce محامٍ
        if (!empty($selectedIds)) {
            Partie::whereIn('id', $selectedIds)->update(['id_avocat' => $avocat->id]);
        }

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

        if($avocat->dossierParties()->count() > 0){
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