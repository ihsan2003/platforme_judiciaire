<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tribunal;
use App\Models\TypeTribunal;
use App\Models\Province;

class TribunalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tribunaux = Tribunal::with(['typeTribunal', 'province'])->paginate(10);

        return view('tribunaux.index', compact('tribunaux'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = TypeTribunal::all();
        $provinces = Province::all();

        return view('tribunaux.create', compact('types', 'provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_tribunal' => 'required|string|max:255',
            'id_type_tribunal' => 'required|exists:type_tribunaux,id',
            'id_province' => 'required|exists:provinces,id',
        ]);

        Tribunal::create($request->only([
            'nom_tribunal',
            'id_type_tribunal',
            'id_province'
        ]));

        return redirect()->route('tribunaux.index')
                        ->with('success', 'Tribunal créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tribunal = Tribunal::findOrFail($id);
        return view('tribunaux.show', compact('tribunal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tribunal $tribunal)
    {
        $types = TypeTribunal::all();
        $provinces = Province::all();

        return view('tribunaux.edit', compact('tribunal', 'types', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, Tribunal $tribunal)
    {
        $request->validate([
            'nom_tribunal' => 'required|string|max:255',
            'id_type_tribunal' => 'required|exists:type_tribunaux,id',
            'id_province' => 'required|exists:provinces,id',
        ]);

        $tribunal->update($request->only([
            'nom_tribunal',
            'id_type_tribunal',
            'id_province'
        ]));

        return redirect()->route('tribunaux.index')
                        ->with('success', 'Tribunal mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tribunal $tribunal)
    {
        $tribunal->delete();

        return redirect()->route('tribunaux.index')
                        ->with('success', 'Tribunal supprimé.');
    }
}
