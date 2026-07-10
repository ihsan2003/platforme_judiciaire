<?php

namespace App\Services;

use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\Finance;
use App\Models\Jugement;
use App\Models\Reclamation;
use App\Models\Region;
use App\Models\TypeAffaire;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RapportStatistiqueService
{
    protected Carbon $debut;
    protected Carbon $fin;

    public function __construct(Carbon $debut, Carbon $fin)
    {
        $this->debut = $debut->copy()->startOfDay();
        $this->fin = $fin->copy()->endOfDay();
    }

    /**
     * Point d'entrée unique : retourne un tableau plat "clé => valeur",
     * dont les clés correspondent 1 à 1 aux ${...} du template Word,
     * sauf pour les tableaux dynamiques ('type_affaires' et 'regions')
     * qui sont retournés à part pour être injectés via cloneRow().
     */
    public function genererStatistiques(): array
    {
        return array_merge(
            [
                'date_debut' => $this->debut->format('d/m/Y'),
                'date_fin'   => $this->fin->format('d/m/Y'),
            ],
            $this->statistiquesDossiersGlobales(),
            $this->statistiquesParTypeTribunal(),
            $this->statistiquesParDegre(),
            $this->statistiquesParAnnee(),
            $this->statistiquesFinancieres(),
            $this->indicateursDossiers(),
            $this->statistiquesReclamationsGlobales(),
            $this->statistiquesReclamationsParJiha(),
            $this->statistiquesReclamationsParType(),
            $this->indicateursReclamations(),
        );
    }

    /**
     * Lignes dynamiques : répartition des dossiers par "طبيعة النزاع"
     * (une ligne par type_affaire réellement utilisé sur la période).
     * Retourne un tableau de ['type_libelle' => ..., 'type_nombre' => ...]
     */
    public function lignesTypeAffaire(): array
    {
        return DossierJudiciaire::whereBetween('date_ouverture', [$this->debut, $this->fin])
            ->join('type_affaires', 'type_affaires.id', '=', 'dossier_judiciaires.id_type_affaire')
            ->select('type_affaires.affaire as libelle', DB::raw('count(*) as nombre'))
            ->groupBy('type_affaires.affaire')
            ->orderByDesc('nombre')
            ->get()
            ->map(fn ($r) => [
                'type_libelle' => $r->libelle,
                'type_nombre'  => (string) $r->nombre,
            ])
            ->toArray();
    }

    /**
     * Lignes dynamiques : répartition des dossiers par "الجهة القضائية"
     * (région), ventilée par degré (ابتدائي / استئناف / نقض) + total.
     * Une ligne par région ayant au moins un dossier sur la période.
     */
    public function lignesRegion(): array
    {
        // ordre des degrés : 1 = ابتدائي, 2 = استئناف, 3 = نقض (voir DegreeJuridiction.ordre)
        $rows = DossierTribunal::query()
            ->whereBetween('date_debut', [$this->debut, $this->fin])
            ->join('tribunaux', 'tribunaux.id', '=', 'dossier_tribunaux.id_tribunal')
            ->join('provinces', 'provinces.id', '=', 'tribunaux.id_province')
            ->join('regions', 'regions.id', '=', 'provinces.id_region')
            ->join('degre_juridictions', 'degre_juridictions.id', '=', 'dossier_tribunaux.id_degre')
            ->select(
                'regions.region as region',
                'degre_juridictions.ordre as ordre',
                DB::raw('count(*) as nombre')
            )
            ->groupBy('regions.region', 'degre_juridictions.ordre')
            ->get();

        $parRegion = [];
        foreach ($rows as $r) {
            $parRegion[$r->region] ??= ['ibtidai' => 0, 'istinaf' => 0, 'naqd' => 0];
            $parRegion[$r->region][match ((int) $r->ordre) {
                1 => 'ibtidai',
                2 => 'istinaf',
                3 => 'naqd',
                default => 'ibtidai',
            }] += $r->nombre;
        }

        $lignes = [];
        foreach ($parRegion as $region => $c) {
            $total = $c['ibtidai'] + $c['istinaf'] + $c['naqd'];
            $lignes[] = [
                'reg_nom'     => $region,
                'reg_ibtidai' => (string) $c['ibtidai'],
                'reg_istinaf' => (string) $c['istinaf'],
                'reg_naqd'    => (string) $c['naqd'],
                'reg_total'   => (string) $total,
            ];
        }

        usort($lignes, fn ($a, $b) => (int) $b['reg_total'] <=> (int) $a['reg_total']);

        return $lignes;
    }

    // ─────────────────────────────────────────────────────────────
    // 1) الحصيلة الإجمالية للملفات القضائية
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesDossiersGlobales(): array
    {
        $base = DossierJudiciaire::whereBetween('date_ouverture', [$this->debut, $this->fin]);

        $total = (clone $base)->count();

        $nouveaux = (clone $base)->count(); // ouverts pendant la période = "nouveaux" par définition ici

        // NB : les libellés exacts dans `statut_dossiers` varient selon les seeders
        // (ex: 'جاري' vs 'موقوف'), on matche donc par mot-clé (comme le fait déjà
        // DossierJudiciaire::getEstActifAttribute() dans le modèle existant).
        $enCours = (clone $base)->whereHas('statut', fn ($q) => $q->where('statut_dossier', 'جاري'))->count();

        $juges = (clone $base)->whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', ['تم الحكم', 'تم التنفيذ', 'مغلق']))->count();

        $executes = (clone $base)->whereHas('statut', fn ($q) => $q->where('statut_dossier', 'تم التنفيذ'))->count();

        // "قيد التنفيذ" = un jugement existe avec une exécution non terminée (date_execution NULL)
        $enExecution = (clone $base)->whereHas('dossierTribunaux.jugements.executions', function ($q) {
            $q->whereNull('date_execution');
        })->count();

        return [
            'dossiers_total'         => (string) $total,
            'dossiers_nouveaux'      => (string) $nouveaux,
            'dossiers_en_cours'      => (string) $enCours,
            'dossiers_juges'         => (string) $juges,
            'dossiers_executes'      => (string) $executes,
            'dossiers_en_execution'  => (string) $enExecution,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // 2) توزيع الملفات حسب نوع المحكمة
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesParTypeTribunal(): array
    {
        $counts = DossierTribunal::whereBetween('date_debut', [$this->debut, $this->fin])
            ->join('tribunaux', 'tribunaux.id', '=', 'dossier_tribunaux.id_tribunal')
            ->join('type_tribunaux', 'type_tribunaux.id', '=', 'tribunaux.id_type_tribunal')
            ->select('type_tribunaux.tribunal as type', DB::raw('count(*) as nombre'))
            ->groupBy('type_tribunaux.tribunal')
            ->pluck('nombre', 'type');

        $get = fn (string $libelle) => (string) ($counts[$libelle] ?? 0);

        $ibtidai      = $get('المحكمة الابتدائية');
        $idari        = $get('المحكمة الإدارية');
        $tijari       = $get('المحكمة التجارية');
        $istinaf      = $get('محكمة الاستئناف');
        $istinafIdari = $get('محكمة الاستئناف الإدارية');
        $istinafTijari = $get('محكمة الاستئناف التجارية');
        $naqd         = $get('محكمة النقض');

        $total = array_sum([$ibtidai, $idari, $tijari, $istinaf, $istinafIdari, $istinafTijari, $naqd]);

        return [
            'nb_tribunal_ibtidai'        => $ibtidai,
            'nb_tribunal_idari'          => $idari,
            'nb_tribunal_tijari'         => $tijari,
            'nb_tribunal_istinaf'        => $istinaf,
            'nb_tribunal_istinaf_idari'  => $istinafIdari,
            'nb_tribunal_istinaf_tijari' => $istinafTijari,
            'nb_tribunal_naqd'           => $naqd,
            'nb_tribunal_total'          => (string) $total,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // 5) توزيع الملفات حسب درجات التقاضي
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesParDegre(): array
    {
        $counts = DossierTribunal::whereBetween('date_debut', [$this->debut, $this->fin])
            ->join('degre_juridictions', 'degre_juridictions.id', '=', 'dossier_tribunaux.id_degre')
            ->select('degre_juridictions.ordre as ordre', DB::raw('count(*) as nombre'))
            ->groupBy('degre_juridictions.ordre')
            ->pluck('nombre', 'ordre');

        // Le seeder ne définit que 3 degrés (الدرجة الأولى / الاستئناف / النقض).
        // "إعادة النظر" n'existe pas encore comme degré : compté ici via les
        // recours de type "إعادة النظر" (voir type_recours), à 0 sinon.
        $ibtidai   = (int) ($counts[1] ?? 0);
        $istinafi  = (int) ($counts[2] ?? 0);
        $naqd      = (int) ($counts[3] ?? 0);

        $iaadaNadar = DB::table('recours')
            ->join('type_recours', 'type_recours.id', '=', 'recours.id_type_recours')
            ->whereBetween('recours.date_recours', [$this->debut, $this->fin])
            ->where('type_recours.type_recours', 'إعادة النظر')
            ->count();

        $total = $ibtidai + $istinafi + $iaadaNadar + $naqd;

        return [
            'nb_degre_ibtidai'      => (string) $ibtidai,
            'nb_degre_istinafi'     => (string) $istinafi,
            'nb_degre_iaada_nadar'  => (string) $iaadaNadar,
            'nb_degre_naqd'         => (string) $naqd,
            'nb_degre_total'        => (string) $total,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // 6) توزيع الملفات حسب سنة تسجيل الدعوى
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesParAnnee(): array
    {
        $counts = DossierJudiciaire::whereBetween('date_ouverture', [$this->debut, $this->fin])
            ->select(DB::raw('YEAR(date_ouverture) as annee'), DB::raw('count(*) as nombre'))
            ->groupBy('annee')
            ->pluck('nombre', 'annee');

        $c2024 = (int) ($counts[2024] ?? 0);
        $c2025 = (int) ($counts[2025] ?? 0);
        $c2026 = (int) ($counts[2026] ?? 0);

        return [
            'nb_annee_2024' => (string) $c2024,
            'nb_annee_2025' => (string) $c2025,
            'nb_annee_2026' => (string) $c2026,
            'nb_annee_total' => (string) ($c2024 + $c2025 + $c2026),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // 7) المبالغ المالية المحكوم بها
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesFinancieres(): array
    {
        $finances = Finance::join('jugements', 'jugements.id', '=', 'finances.id_jugement')
            ->whereBetween('jugements.date_jugement', [$this->debut, $this->fin])
            ->select('finances.*');

        // pour l'institution : montant condamné où la partie gagnante est l'institution
        $montantPour = (clone $finances)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'jugements.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'مع')
            ->sum('finances.montant_condamne');

        $nbPour = (clone $finances)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'jugements.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'مع')
            ->distinct('jugements.id')
            ->count('jugements.id');

        $montantContre = (clone $finances)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'jugements.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'ضد')
            ->sum('finances.montant_condamne');

        $nbContre = (clone $finances)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'jugements.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'ضد')
            ->distinct('jugements.id')
            ->count('jugements.id');

        $montantExecute = (clone $finances)->where('statut_paiement', 'Complet')->sum('montant_paye');
        $nbExecute = (clone $finances)->where('statut_paiement', 'Complet')->count();

        $montantEnCours = (clone $finances)->where('statut_paiement', 'Partiel')->sum('montant_paye');
        $nbEnCours = (clone $finances)->whereIn('statut_paiement', ['Partiel', 'En attente'])->count();

        $montantTotal = (clone $finances)->sum('montant_condamne');
        $nbTotal = (clone $finances)->count();

        return [
            'montant_pour'     => number_format((float) $montantPour, 2, '.', ' '),
            'nb_pour'          => (string) $nbPour,
            'montant_contre'   => number_format((float) $montantContre, 2, '.', ' '),
            'nb_contre'        => (string) $nbContre,
            'montant_execute'  => number_format((float) $montantExecute, 2, '.', ' '),
            'nb_execute'       => (string) $nbExecute,
            'montant_en_cours' => number_format((float) $montantEnCours, 2, '.', ' '),
            'nb_en_cours'      => (string) $nbEnCours,
            'montant_total'    => number_format((float) $montantTotal, 2, '.', ' '),
            'nb_total'         => (string) $nbTotal,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // 8) أهم المؤشرات (dossiers)
    // ─────────────────────────────────────────────────────────────
    protected function indicateursDossiers(): array
    {
        $globales = $this->statistiquesDossiersGlobales();
        $total = max(1, (int) $globales['dossiers_juges']); // évite division par zéro

        $jugementsGagnes = DB::table('jugement_parties')
            ->join('jugements', 'jugements.id', '=', 'jugement_parties.id_jugement')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->whereBetween('jugements.date_jugement', [$this->debut, $this->fin])
            ->where('position_institutions.position', 'مع')
            ->distinct('jugements.id')
            ->count('jugements.id');

        $jugementsPerdus = DB::table('jugement_parties')
            ->join('jugements', 'jugements.id', '=', 'jugement_parties.id_jugement')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->whereBetween('jugements.date_jugement', [$this->debut, $this->fin])
            ->where('position_institutions.position', 'ضد')
            ->distinct('jugements.id')
            ->count('jugements.id');

        $topType = TypeAffaire::withCount(['dossiers' => function ($q) {
            $q->whereBetween('date_ouverture', [$this->debut, $this->fin]);
        }])->orderByDesc('dossiers_count')->first();

        return [
            'pct_dossiers_juges'   => (string) round(((int) $globales['dossiers_juges'] / max(1, (int) $globales['dossiers_total'])) * 100, 1),
            'pct_dossiers_gagnes'  => (string) round(($jugementsGagnes / $total) * 100, 1),
            'pct_dossiers_perdus'  => (string) round(($jugementsPerdus / $total) * 100, 1),
            'type_affaire_top'     => $topType?->affaire ?? '—',
            'type_affaire_top_nb'  => (string) ($topType?->dossiers_count ?? 0),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // الحصيلة الإجمالية للشكايات
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesReclamationsGlobales(): array
    {
        $base = Reclamation::whereBetween('date_reception', [$this->debut, $this->fin]);

        $total = (clone $base)->count();
        $nouvelles = (clone $base)->whereHas('statut', fn ($q) => $q->where('statut_reclamation', 'قيد المعالجة'))->count();
        $traitees = (clone $base)->whereHas('statut', fn ($q) => $q->where('statut_reclamation', 'تمت المعالجة'))->count();
        $enCours = $nouvelles; // "قيد المعالجة" couvre à la fois nouvelles et en cours dans le schéma actuel
        $archivees = (clone $base)->whereHas('statut', fn ($q) => $q->where('statut_reclamation', 'مغلقة'))->count();

        return [
            'recl_total'      => (string) $total,
            'recl_nouvelles'  => (string) $nouvelles,
            'recl_traitees'   => (string) $traitees,
            'recl_en_cours'   => (string) $enCours,
            'recl_archivees'  => (string) $archivees,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // توزيع الشكايات حسب الجهة المحيلة
    //
    // ASSOMPTION : le schéma actuel ne contient pas de champ dédié "جهة محيلة".
    // On utilise ici la structure rattachée à la première action de traitement
    // (action_reclamations.id_structure -> structures.nom), en la classant par
    // mot-clé sur le nom de la structure. À ajuster si votre organigramme
    // (table `structures`) utilise d'autres libellés.
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesReclamationsParJiha(): array
    {
        $rows = DB::table('reclamations')
            ->join('action_reclamations', 'action_reclamations.id_reclamation', '=', 'reclamations.id')
            ->join('structures', 'structures.id', '=', 'action_reclamations.id_structure')
            ->whereBetween('reclamations.date_reception', [$this->debut, $this->fin])
            ->select('structures.nom')
            ->distinct('reclamations.id')
            ->get();

        $c = ['wasit' => 0, 'regionale' => 0, 'provinciale' => 0, 'usagers' => 0, 'autres' => 0];

        foreach ($rows as $r) {
            $nom = $r->nom;
            match (true) {
                str_contains($nom, 'الوسيط')      => $c['wasit']++,
                str_contains($nom, 'الجهوية')     => $c['regionale']++,
                str_contains($nom, 'الإقليمية'), str_contains($nom, 'الاقليمية') => $c['provinciale']++,
                str_contains($nom, 'مرتفق'), str_contains($nom, 'مستخدم') => $c['usagers']++,
                default => $c['autres']++,
            };
        }

        $total = array_sum($c);

        return [
            'recl_jiha_wasit'       => (string) $c['wasit'],
            'recl_jiha_regionale'   => (string) $c['regionale'],
            'recl_jiha_provinciale' => (string) $c['provinciale'],
            'recl_jiha_usagers'     => (string) $c['usagers'],
            'recl_jiha_autres'      => (string) $c['autres'],
            'recl_jiha_total'       => (string) $total,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // توزيع الشكايات حسب النوع
    //
    // ASSOMPTION : nécessite la colonne `reclamations.categorie` ajoutée par
    // la migration fournie. Si vous ne voulez pas ajouter de colonne, un
    // classement heuristique de secours par mots-clés sur `objet` est utilisé.
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesReclamationsParType(): array
    {
        $base = Reclamation::whereBetween('date_reception', [$this->debut, $this->fin]);
        $hasColonne = \Illuminate\Support\Facades\Schema::hasColumn('reclamations', 'categorie');

        if ($hasColonne) {
            $judiciaire = (clone $base)->where('categorie', 'قضائية')->count();
            $rh         = (clone $base)->where('categorie', 'موارد_بشرية')->count();
            $usagers    = (clone $base)->where('categorie', 'مرتفقين')->count();
            $autres     = (clone $base)->where('categorie', 'أخرى')->count();
        } else {
            $judiciaire = (clone $base)->where('objet', 'like', '%قضائ%')->count();
            $rh         = (clone $base)->where('objet', 'like', '%موارد بشرية%')->count();
            $usagers    = (clone $base)->where('objet', 'like', '%مرتفق%')->count();
            $autres     = (clone $base)->count() - $judiciaire - $rh - $usagers;
        }

        $total = max(1, $judiciaire + $rh + $usagers + $autres);
        $pct = fn ($n) => (string) round(($n / $total) * 100, 1);

        return [
            'nb_recl_type_judiciaire'  => (string) $judiciaire,
            'pct_recl_type_judiciaire' => $pct($judiciaire),
            'nb_recl_type_rh'          => (string) $rh,
            'pct_recl_type_rh'         => $pct($rh),
            'nb_recl_type_usagers'     => (string) $usagers,
            'pct_recl_type_usagers'    => $pct($usagers),
            'nb_recl_type_autres'      => (string) $autres,
            'pct_recl_type_autres'     => $pct($autres),
            'nb_recl_type_total'       => (string) $total,
            'pct_recl_type_total'      => '100',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // أهم المؤشرات (شكايات)
    // ─────────────────────────────────────────────────────────────
    protected function indicateursReclamations(): array
    {
        $g = $this->statistiquesReclamationsGlobales();
        $total = max(1, (int) $g['recl_total']);

        $pctTraitees = round(((int) $g['recl_traitees'] / $total) * 100, 1);

        $topJiha = DB::table('reclamations')
            ->join('action_reclamations', 'action_reclamations.id_reclamation', '=', 'reclamations.id')
            ->join('structures', 'structures.id', '=', 'action_reclamations.id_structure')
            ->whereBetween('reclamations.date_reception', [$this->debut, $this->fin])
            ->select('structures.nom', DB::raw('count(distinct reclamations.id) as nombre'))
            ->groupBy('structures.nom')
            ->orderByDesc('nombre')
            ->first();

        return [
            'pct_recl_traitees'  => (string) $pctTraitees,
            'top_jiha_recl'      => $topJiha->nom ?? '—',
            'top_jiha_recl_nb'   => (string) ($topJiha->nombre ?? 0),
        ];
    }
}
