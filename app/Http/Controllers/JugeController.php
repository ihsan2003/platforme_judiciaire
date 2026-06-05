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

            // ══ Recherche nom juge + tribunal ══
            ->when(request('search'), function ($q, $v) {

                $q->where(function ($query) use ($v) {

                    $query->where('nom_complet', 'like', "%{$v}%")

                        ->orWhereHas('tribunal', function ($tribunal) use ($v) {
                            $tribunal->where('nom_tribunal', 'like', "%{$v}%");
                        });

                });

            })

            // ══ Filtre spécialité ══
            ->when(request('specialisation'), function ($q, $v) {
                $q->where('specialisation', $v);
            })

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
            ->with('success', 'تمت إضافة القاضي بنجاح.');
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
            ->with('success', 'تم تعديل القاضي بنجاح.');
    }

    public function destroy(Juge $juge)
    {
        $juge->delete();

        return redirect()->route('juges.index')
            ->with('success', 'تم حذف القاضي بنجاح.');
    }
}