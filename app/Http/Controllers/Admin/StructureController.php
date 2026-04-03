<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Models\TypeStructure;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function index()
    {
        $structures = Structure::with(['typeStructure', 'parent'])
            ->whereNull('id_parent')
            ->with('enfants.typeStructure')
            ->orderBy('nom')
            ->get();
        return view('admin.structures.index', compact('structures'));
    }

    public function create()
    {
        $typesStructure = TypeStructure::all();
        $parents        = Structure::orderBy('nom')->get();
        return view('admin.structures.create', compact('typesStructure', 'parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'id_type_structure' => 'required|exists:type_structures,id',
            'id_parent'         => 'nullable|exists:structures,id',
        ]);
        Structure::create($validated);
        return redirect()->route('admin.structures.index')->with('success', 'Structure créée.');
    }

    public function show(Structure $structure)
    {
        $structure->load(['typeStructure', 'parent', 'enfants.typeStructure']);
        return view('admin.structures.show', compact('structure'));
    }

    public function edit(Structure $structure)
    {
        $typesStructure = TypeStructure::all();
        $parents        = Structure::where('id', '!=', $structure->id)->orderBy('nom')->get();
        return view('admin.structures.edit', compact('structure', 'typesStructure', 'parents'));
    }

    public function update(Request $request, Structure $structure)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'id_type_structure' => 'required|exists:type_structures,id',
            'id_parent'         => 'nullable|exists:structures,id',
        ]);
        $structure->update($validated);
        return redirect()->route('admin.structures.index')->with('success', 'Structure mise à jour.');
    }

    public function destroy(Structure $structure)
    {
        $structure->delete();
        return redirect()->route('admin.structures.index')->with('success', 'Structure supprimée.');
    }
}
