<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Avocat;
use App\Rules\Telephone;


class AvocatController extends Controller
{
 

    
    public function index(Request $request)
    {
        $query = Avocat::query();

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
            'email' => 'required|email|unique:avocats,email'
        ]);

        Avocat::create($validated);

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
        return view('avocats.edit', compact('avocat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nom_avocat' => 'required|string|max:255',
            'telephone'  => ['required', new Telephone],
            'email' => 'required|email|unique:avocats,email,'.$id
        ]);

        $avocat = Avocat::findOrFail($id);
        $avocat->update($validated);

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
