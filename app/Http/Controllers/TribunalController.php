<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tribunal;
use App\Models\TypeTribunal;
use App\Models\Province;

class TribunalController extends Controller
{
    public function index()
    {
        $tribunaux = Tribunal::with(['typeTribunal', 'province.region'])
            ->when(request('search'), fn($q, $v) =>
                $q->where('nom_tribunal', 'like', "%{$v}%")
            )
            ->when(request('type'), fn($q, $v) =>
                $q->where('id_type_tribunal', $v)
            )
            ->orderBy('nom_tribunal')
            ->paginate(10);

        return view('tribunaux.index', compact('tribunaux'));
    }

    public function create()
    {
        $types     = TypeTribunal::orderBy('tribunal')->get();
        $provinces = Province::with('region')->orderBy('province')->get();

        return view('tribunaux.create', compact('types', 'provinces'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_tribunal'     => 'required|string|max:255',
            'id_type_tribunal' => 'required|exists:type_tribunaux,id',
            'id_province'      => 'required|exists:provinces,id',
        ]);

        Tribunal::create($request->only([
            'nom_tribunal',
            'id_type_tribunal',
            'id_province',
        ]));

        return redirect()->route('tribunaux.index')
            ->with('success', 'Tribunal créé avec succès.');
    }

    public function show(Tribunal $tribunal)
    {
        $tribunal->load([
            'typeTribunal',
            'province.region',
            'juges',
        ]);

        return view('tribunaux.show', compact('tribunal'));
    }

    public function edit(Tribunal $tribunal)
    {
        $types     = TypeTribunal::orderBy('tribunal')->get();
        $provinces = Province::with('region')->orderBy('province')->get();

        return view('tribunaux.edit', compact('tribunal', 'types', 'provinces'));
    }

    public function update(Request $request, Tribunal $tribunal)
    {
        $request->validate([
            'nom_tribunal'     => 'required|string|max:255',
            'id_type_tribunal' => 'required|exists:type_tribunaux,id',
            'id_province'      => 'required|exists:provinces,id',
        ]);

        $tribunal->update($request->only([
            'nom_tribunal',
            'id_type_tribunal',
            'id_province',
        ]));

        return redirect()->route('tribunaux.show', $tribunal)
            ->with('success', 'Tribunal mis à jour.');
    }

    public function destroy(Tribunal $tribunal)
    {
        $tribunal->delete();

        return redirect()->route('tribunaux.index')
            ->with('success', 'Tribunal supprimé.');
    }
}