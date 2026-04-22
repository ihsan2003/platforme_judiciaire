<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DossierJudiciaire;
use App\Models\Document;
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

        $validated = $request->validate([
            'fichier'          => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'titre_document'   => ['required', 'string', 'max:255'],
            'id_type_document' => ['required', 'exists:type_documents,id'],
            'id_partie'        => ['nullable', 'exists:parties,id'],
            'date_depot'       => ['required', 'date'],
        ]);

        $file = $request->file('fichier');

        $path = $file->storeAs(
            "dossiers/{$dossier->id}/documents",
            time() . '_' . $file->getClientOriginalName(),
            'local'
        );

        $dossier->documents()->create([
            'id_type_document' => $validated['id_type_document'],
            'id_partie'        => $validated['id_partie'] ?? null,
            'titre_document'   => $validated['titre_document'],
            'date_depot'       => $validated['date_depot'],
            'fichier_path'     => $path,
        ]);

        return back()->with('success', "Document « {$validated['titre_document']} » ajouté.");
    }

    /**
     * Téléchargement sécurisé.
     */
    public function download(DossierJudiciaire $dossier, Document $document): StreamedResponse
    {
        $this->authorize('view', $dossier);

        abort_unless($document->id_dossier === $dossier->id, 403);
        abort_unless(Storage::disk('local')->exists($document->fichier_path), 404);

        $extension = pathinfo($document->fichier_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download(
            $document->fichier_path,
            $document->titre_document . '.' . $extension
        );
    }

    /**
     * Suppression d'un document.
     */
    public function destroy(DossierJudiciaire $dossier, Document $document): RedirectResponse
    {
        $this->authorize('update', $dossier);

        abort_unless($document->id_dossier === $dossier->id, 403);

        if (Storage::disk('local')->exists($document->fichier_path)) {
            Storage::disk('local')->delete($document->fichier_path);
        }

        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }
}