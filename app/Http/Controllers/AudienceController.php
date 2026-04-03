<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audience;
use App\Models\DossierTribunal;
use App\Models\TypeAudience;
use App\Models\Juge;

class AudienceController extends Controller
{
    public function index()
    {
        $audiences = Audience::with(['dossierTribunal', 'typeAudience', 'juge'])
            ->latest('date_audience')
            ->paginate(10);

        return view('audiences.index', compact('audiences'));
    }

    public function create()
    {
        $dossiers = DossierTribunal::all();
        $types = TypeAudience::all();
        $juges = Juge::all();

        return view('audiences.create', compact('dossiers', 'types', 'juges'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_dossier_tribunal' => 'required|exists:dossier_tribunaux,id',
            'id_type_audience' => 'required|exists:type_audiences,id',
            'id_juge' => 'required|exists:juges,id',
            'date_audience' => 'required|date',
            'date_prochaine_audience' => 'nullable|date|after_or_equal:date_audience',
            'presence_demandeur' => 'boolean',
            'presence_defendeur' => 'boolean',
            'resultat_audience' => 'nullable|string',
            'actions_demandees' => 'nullable|string',
        ]);

        Audience::create($data);

        return redirect()->route('audiences.index')
            ->with('success', 'Audience créée avec succès');
    }

    public function show(Audience $audience)
    {
        return view('audiences.show', compact('audience'));
    }

    public function edit(Audience $audience)
    {
        $dossiers = DossierTribunal::all();
        $types = TypeAudience::all();
        $juges = Juge::all();

        return view('audiences.edit', compact('audience', 'dossiers', 'types', 'juges'));
    }

    public function update(Request $request, Audience $audience)
    {
        $data = $request->validate([
            'id_dossier_tribunal' => 'required|exists:dossier_tribunals,id',
            'id_type_audience' => 'required|exists:type_audiences,id',
            'id_juge' => 'required|exists:juges,id',
            'date_audience' => 'required|date',
            'date_prochaine_audience' => 'nullable|date|after_or_equal:date_audience',
            'presence_demandeur' => 'boolean',
            'presence_defendeur' => 'boolean',
            'resultat_audience' => 'nullable|string',
            'actions_demandees' => 'nullable|string',
        ]);

        $audience->update($data);

        return redirect()->route('audiences.index')
            ->with('success', 'Audience mise à jour avec succès');
    }

    public function destroy(Audience $audience)
    {
        $audience->delete();

        return redirect()->route('audiences.index')
            ->with('success', 'Audience supprimée avec succès');
    }
}