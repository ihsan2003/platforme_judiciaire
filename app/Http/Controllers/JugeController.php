<?php

namespace App\Http\Controllers;

use App\Models\Juge;
use App\Models\Tribunal;
use Illuminate\Http\Request;

class JugeController extends Controller
{
    public function index()
    {
        $juges = Juge::with('tribunal')
            ->when(request('search'), fn($q, $v) =>
                $q->where(fn($q) =>
                    $q->where('nom_complet', 'like', "%{$v}%")
                      ->orWhere('grade', 'like', "%{$v}%")
                      ->orWhere('specialisation', 'like', "%{$v}%")
                )
            )
            ->when(request('tribunal'), fn($q, $v) =>
                $q->where('id_tribunal', $v)
            )
            ->orderBy('nom_complet')
            ->paginate(10)
            ->withQueryString();

        return view('juges.index', compact('juges'));
    }

    public function create()
    {
        $tribunaux = Tribunal::orderBy('nom_tribunal')->get();

        return view('juges.create', compact('tribunaux'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_complet'    => 'required|string|max:255',
            'grade'          => 'required|string|max:255',
            'specialisation' => 'nullable|string|max:255',
            'id_tribunal'    => 'required|exists:tribunaux,id',
        ]);

        $juge = Juge::create($request->only([
            'nom_complet',
            'grade',
            'specialisation',
            'id_tribunal',
        ]));

        return redirect()->route('juges.show', $juge)
            ->with('success', 'Juge ajouté avec succès.');
    }

    public function show(Juge $juge)
    {
        $juge->load([
            'tribunal',
            'audiences.typeAudience',
            'audiences.dossierTribunal.dossier',
            'jugements.dossierTribunal.dossier',
        ]);

        return view('juges.show', compact('juge'));
    }

    public function edit(Juge $juge)
    {
        $tribunaux = Tribunal::orderBy('nom_tribunal')->get();

        return view('juges.edit', compact('juge', 'tribunaux'));
    }

    public function update(Request $request, Juge $juge)
    {
        $request->validate([
            'nom_complet'    => 'required|string|max:255',
            'grade'          => 'required|string|max:255',
            'specialisation' => 'nullable|string|max:255',
            'id_tribunal'    => 'required|exists:tribunaux,id',
        ]);

        $juge->update($request->only([
            'nom_complet',
            'grade',
            'specialisation',
            'id_tribunal',
        ]));

        return redirect()->route('juges.show', $juge)
            ->with('success', 'Juge modifié avec succès.');
    }

    public function destroy(Juge $juge)
    {
        $juge->delete();

        return redirect()->route('juges.index')
            ->with('success', 'Juge supprimé.');
    }
}