<?php

namespace App\Http\Controllers\Dossiers;

use App\Http\Controllers\Controller;
use App\Models\DossierJudiciaire;
use App\Models\Document;
use App\Models\TypeDocument;
use App\Models\Partie;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Upload d'un document et rattachement au dossier.
     */
    public function store(Request $request, DossierJudiciaire $dossier): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'fichier'          => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'titre_document'   => ['required', 'string', 'max:255'],
            'id_type_document' => ['required', 'exists:type_documents,id'],
            'id_partie'        => ['nullable', 'exists:parties,id'],
            'date_depot'       => ['required', 'date'],
        ]);

        $fichier = $request->file('fichier');
        $path    = $fichier->storeAs(
            "dossiers/{$dossier->id}/documents",
            time() . '_' . $fichier->getClientOriginalName(),
            'private'
        );

        $dossier->documents()->create([
            'id_type_document' => $request->id_type_document,
            'id_partie'        => $request->id_partie,
            'titre_document'   => $request->titre_document,
            'date_depot'       => $request->date_depot,
            'fichier_path'     => $path,
        ]);

        return back()->with('success', 'Document « ' . $request->titre_document . ' » ajouté.');
    }

    /**
     * Téléchargement sécurisé (vérifie l'autorisation via la policy du dossier).
     */
    public function download(DossierJudiciaire $dossier, Document $document): StreamedResponse
    {
        $this->authorize('view', $dossier);

        abort_unless($document->id_dossier === $dossier->id, 403);
        abort_unless(Storage::disk('private')->exists($document->fichier_path), 404);

        return Storage::disk('private')->download(
            $document->fichier_path,
            $document->titre_document . '.' . pathinfo($document->fichier_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Suppression d'un document (fichier + enregistrement BDD).
     */
    public function destroy(DossierJudiciaire $dossier, Document $document): RedirectResponse
    {
        $this->authorize('update', $dossier);

        abort_unless($document->id_dossier === $dossier->id, 403);

        Storage::disk('private')->delete($document->fichier_path);
        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }
}
