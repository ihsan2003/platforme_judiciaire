<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Avocat;

class AvocatController extends Controller
{
 

    
    public function index()
    {
        $avocats = Avocat::orderBy('nom_avocat')->paginate(10);
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
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|unique:avocats,email'
        ]);

        Avocat::create($validated);

        return redirect()->route('avocats.index')->with('success', 'Avocat créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $avocat = Avocat::with('dossierParties')->findOrFail($id);
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
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|unique:avocats,email,'.$id
        ]);

        $avocat = Avocat::findOrFail($id);
        $avocat->update($validated);

        return redirect()->route('avocats.index')->with('success', 'Avocat mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $avocat = Avocat::findOrFail($id);

        if($avocat->dossierParties()->count() > 0){
            return redirect()->route('avocats.index')->with('error', 'Cet avocat ne peut pas être supprimé car il est associé à des dossiers.');
        }

        $avocat->delete();

        return redirect()->route('avocats.index')->with('success', 'Avocat supprimé avec succès.');
    }
}
