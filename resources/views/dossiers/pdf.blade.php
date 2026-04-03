{{-- resources/views/dossiers/pdf.blade.php --}}
{{-- Utilisé par DomPDF — pas de @extends, CSS inline uniquement --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier {{ $dossier->numero_dossier_interne }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.6; }

        /* En-tête */
        .header { background: #1e3a5f; color: #fff; padding: 18px 24px; margin-bottom: 20px; }
        .header .title { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .header .numero { font-size: 11px; opacity: .8; font-family: Courier, monospace; }
        .header .meta { font-size: 9px; opacity: .7; margin-top: 6px; }

        /* Sections */
        .section { margin-bottom: 18px; page-break-inside: avoid; }
        .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase;
            letter-spacing: .08em; color: #64748b; border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 4px; margin-bottom: 10px; }

        /* Grid infos */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 4px 8px; font-size: 9.5px; border: 1px solid #f0f4f8; }
        .info-table td:first-child { font-weight: bold; color: #64748b; width: 35%;
            background: #f8fafc; }

        /* Tableau parties */
        .data-table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .data-table th { background: #1e3a5f; color: #fff; padding: 5px 8px;
            text-align: left; font-weight: 600; }
        .data-table td { padding: 4px 8px; border-bottom: 1px solid #f0f4f8; }
        .data-table tr:nth-child(even) td { background: #f8fafc; }

        /* Dispositif */
        .dispositif { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #1e3a5f;
            padding: 10px; font-size: 9.5px; white-space: pre-wrap; line-height: 1.7; }

        /* Badge */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px;
            font-size: 8.5px; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }

        /* Pied de page */
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center;
            font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0;
            padding: 5px 20px; background: #fff; }

        .page-break { page-break-after: always; }
        .mt-8 { margin-top: 8px; }
    </style>
</head>
<body>

<div class="footer">
    Dossier {{ $dossier->numero_dossier_interne }} — Généré le {{ now()->format('d/m/Y à H:i') }} — CONFIDENTIEL
</div>

{{-- En-tête --}}
<div class="header">
    <div class="title">FICHE DOSSIER JUDICIAIRE</div>
    <div class="numero">{{ $dossier->numero_dossier_interne }}</div>
    <div class="meta">
        Type : {{ $dossier->typeAffaire->affaire ?? '—' }} |
        Statut : {{ $dossier->statutDossier->statut_dossier ?? '—' }} |
        Ouverture : {{ $dossier->date_ouverture->format('d/m/Y') }}
    </div>
</div>

{{-- Informations générales --}}
<div class="section">
    <div class="section-title">Informations générales</div>
    <table class="info-table">
        <tr>
            <td>N° interne</td>
            <td>{{ $dossier->numero_dossier_interne }}</td>
            <td>N° tribunal</td>
            <td>{{ $dossier->numero_dossier_tribunal ?? '—' }}</td>
        </tr>
        <tr>
            <td>Type d'affaire</td>
            <td>{{ $dossier->typeAffaire->affaire ?? '—' }}</td>
            <td>Statut</td>
            <td>{{ $dossier->statutDossier->statut_dossier ?? '—' }}</td>
        </tr>
        <tr>
            <td>Date d'ouverture</td>
            <td>{{ $dossier->date_ouverture->format('d/m/Y') }}</td>
            <td>Date de clôture</td>
            <td>{{ $dossier->date_cloture?->format('d/m/Y') ?? 'En cours' }}</td>
        </tr>
        <tr>
            <td>Créé par</td>
            <td>{{ $dossier->createdBy->name ?? '—' }}</td>
            <td>Date création</td>
            <td>{{ $dossier->created_at->format('d/m/Y') }}</td>
        </tr>
    </table>
</div>

{{-- Parties --}}
<div class="section">
    <div class="section-title">Parties</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Rôle</th>
                <th>Type</th>
                <th>Identifiant</th>
                <th>Avocat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dossier->dossierParties as $dp)
            <tr>
                <td>{{ $dp->partie->nom_partie }}</td>
                <td>{{ $dp->typePartie->type_partie ?? '—' }}</td>
                <td>{{ $dp->partie->type_personne }}</td>
                <td>{{ $dp->partie->identifiant_unique }}</td>
                <td>{{ $dp->avocat ? 'Me. '.$dp->avocat->nom_avocat : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#94a3b8;">Aucune partie</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Tribunaux --}}
<div class="section">
    <div class="section-title">Tribunaux</div>
    <table class="data-table">
        <thead>
            <tr><th>Tribunal</th><th>Degré</th><th>Début</th><th>Fin</th><th>Audiences</th></tr>
        </thead>
        <tbody>
            @forelse($dossier->dossierTribunaux as $dt)
            <tr>
                <td>{{ $dt->tribunal->nom_tribunal }}</td>
                <td>{{ $dt->degre->degre_juridiction }}</td>
                <td>{{ $dt->date_debut->format('d/m/Y') }}</td>
                <td>{{ $dt->date_fin?->format('d/m/Y') ?? 'En cours' }}</td>
                <td>{{ $dt->audiences->count() }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#94a3b8;">Aucun tribunal</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Jugements --}}
@php $tousJugements = $dossier->dossierTribunaux->flatMap->jugements; @endphp
@if($tousJugements->isNotEmpty())
<div class="section">
    <div class="section-title">Jugements</div>
    @foreach($tousJugements as $jugement)
    <div class="mt-8">
        <table class="info-table" style="margin-bottom:6px;">
            <tr>
                <td>Date</td>
                <td>{{ $jugement->date_jugement->format('d/m/Y') }}</td>
                <td>Juge</td>
                <td>{{ $jugement->juge->nom_complet ?? '—' }}</td>
            </tr>
            <tr>
                <td>Caractère</td>
                <td>
                    <span class="badge {{ $jugement->est_definitif ? 'badge-success' : 'badge-warning' }}">
                        {{ $jugement->est_definitif ? 'Définitif' : 'Susceptible de recours' }}
                    </span>
                </td>
                @if($jugement->finance)
                <td>Montant condamné</td>
                <td>{{ number_format($jugement->finance->montant_condamne, 2, ',', ' ') }} MAD</td>
                @else
                <td colspan="2"></td>
                @endif
            </tr>
        </table>
        <div class="dispositif">{{ $jugement->contenu_dispositif }}</div>
    </div>
    @endforeach
</div>
@endif

{{-- Documents --}}
@if($dossier->documents->isNotEmpty())
<div class="section">
    <div class="section-title">Documents déposés</div>
    <table class="data-table">
        <thead>
            <tr><th>Titre</th><th>Type</th><th>Date dépôt</th><th>Partie</th></tr>
        </thead>
        <tbody>
            @foreach($dossier->documents as $doc)
            <tr>
                <td>{{ $doc->titre_document }}</td>
                <td>{{ $doc->typeDocument->type_document ?? '—' }}</td>
                <td>{{ $doc->date_depot->format('d/m/Y') }}</td>
                <td>{{ $doc->partie?->nom_partie ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

</body>
</html>
