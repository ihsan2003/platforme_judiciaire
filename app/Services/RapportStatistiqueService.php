<?php

namespace App\Services;

use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
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

 
    public function lignesRegion(): array
    {
        // Nombre de dossiers par région et par degré de juridiction
        $rows = DossierTribunal::query()
            ->whereBetween('date_debut', [$this->debut, $this->fin])
            ->join('tribunaux', 'tribunaux.id', '=', 'dossier_tribunaux.id_tribunal')
            ->join('provinces', 'provinces.id', '=', 'tribunaux.id_province')
            ->join('regions', 'regions.id', '=', 'provinces.id_region')
            ->join('degre_juridictions', 'degre_juridictions.id', '=', 'dossier_tribunaux.id_degre')
            ->select(
                'regions.region as region',
                'degre_juridictions.ordre as ordre',
                DB::raw('COUNT(DISTINCT dossier_tribunaux.id_dossier) as nombre')
            )
            ->groupBy('regions.region', 'degre_juridictions.ordre')
            ->get();

        // Nombre total de dossiers distincts par région
        $totauxRegion = DossierTribunal::query()
            ->whereBetween('date_debut', [$this->debut, $this->fin])
            ->join('tribunaux', 'tribunaux.id', '=', 'dossier_tribunaux.id_tribunal')
            ->join('provinces', 'provinces.id', '=', 'tribunaux.id_province')
            ->join('regions', 'regions.id', '=', 'provinces.id_region')
            ->select(
                'regions.region',
                DB::raw('COUNT(DISTINCT dossier_tribunaux.id_dossier) as total')
            )
            ->groupBy('regions.region')
            ->pluck('total', 'region');

        $parRegion = [];

        foreach ($rows as $r) {

            $parRegion[$r->region] ??= [
                'ibtidai' => 0,
                'istinaf' => 0,
                'naqd' => 0,
            ];

            $cle = match ((int) $r->ordre) {
                1 => 'ibtidai',
                2 => 'istinaf',
                3 => 'naqd',
                default => null,
            };

            if ($cle === null) {
                continue;
            }


            $parRegion[$r->region][$cle] += $r->nombre;
        }

        $lignes = [];

        foreach ($parRegion as $region => $c) {

            $lignes[] = [
                'reg_nom'     => $region,
                'reg_ibtidai' => (string) $c['ibtidai'],
                'reg_istinaf' => (string) $c['istinaf'],
                'reg_naqd'    => (string) $c['naqd'],
                'reg_total'   => (string) ($totauxRegion[$region] ?? 0),
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

        $nouveaux = (clone $base)
            ->whereDate('date_ouverture', $this->fin)
            ->count();

   
        $enCours = (clone $base)->whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', ['جاري', 'في طور الاستئناف', 'في طور النقض']))->count();

        // Un dossier "jugé" a dépassé la phase de litige : qu'il attende
        // encore l'exécution, soit en cours d'exécution, ou totalement
        // exécuté, un jugement a bien été rendu. Ne compter que "تم الحكم"
        // sous-estimait donc les dossiers déjà passés en exécution.
        $juges = (clone $base)->whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', ['تم الحكم', 'تم التنفيذ', 'قيد التنفيذ']))->count();

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
            ->select('type_tribunaux.tribunal as type', DB::raw('COUNT(DISTINCT dossier_tribunaux.id_dossier) as nombre'))
            ->groupBy('type_tribunaux.tribunal')
            ->pluck('nombre', 'type');

        $get = fn (string $libelle) => (string) ($counts[$libelle] ?? 0);

        $ibtidai      = $get('المحكمة الابتدائية');
        $idari        = $get('المحكمة الإدارية');
        $tijari       = $get('المحكمة التجارية');
        $istinaf      = $get('محكمة الإستئناف');
        $istinafIdari = $get('محكمة الإستئناف الإدارية');
        $istinafTijari = $get('محكمة الإستئناف التجارية');
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
        // Tous les dossiers ayant au moins une instance commencée
        // avant ou à la fin de la période.
        $dossiers = DossierJudiciaire::whereHas('dossierTribunaux', function ($q) {
                $q->whereDate('date_debut', '<=', $this->fin);
            })
            ->with([
                'dossierTribunaux' => function ($q) {
                    $q->whereDate('date_debut', '<=', $this->fin)
                        ->orderByDesc('date_debut')
                        ->with('degre');
                }
            ])
            ->get();

        $counts = [
            'ibtidai'  => 0,
            'istinafi' => 0,
            'naqd'     => 0,
        ];

        foreach ($dossiers as $dossier) {

            /*
            * On récupère l'instance représentant la situation
            * du dossier à la date de fin du rapport.
            *
            * Si plusieurs instances ont la même date_debut,
            * on privilégie celle qui est encore ouverte.
            */
            $instanceActuelle = $dossier->dossierTribunaux
                ->sortByDesc(function ($dt) {
                    return [
                        $dt->date_debut,
                        $dt->date_fin === null ? 1 : 0,
                    ];
                })
                ->first();

            if (!$instanceActuelle || !$instanceActuelle->degre) {
                continue;
            }

            switch ((int) $instanceActuelle->degre->ordre) {

                case 1:
                    $counts['ibtidai']++;
                    break;

                case 2:
                    $counts['istinafi']++;
                    break;

                case 3:
                    $counts['naqd']++;
                    break;
            }
        }

        // Recours en révision (إعادة النظر)
        $iaadaNadar = DB::table('recours')
            ->join('type_recours', 'type_recours.id', '=', 'recours.id_type_recours')
            ->whereBetween('recours.date_recours', [$this->debut, $this->fin])
            ->where('type_recours.type_recours', 'إعادة النظر')
            ->count();

        $total = $counts['ibtidai']
                + $counts['istinafi']
                + $counts['naqd']
                + $iaadaNadar;

        return [
            'nb_degre_ibtidai'     => (string) $counts['ibtidai'],
            'nb_degre_istinafi'    => (string) $counts['istinafi'],
            'nb_degre_iaada_nadar' => (string) $iaadaNadar,
            'nb_degre_naqd'        => (string) $counts['naqd'],
            'nb_degre_total'       => (string) $total,
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
    // Formatage RTL des montants
    //
    // Dans un paragraphe arabe (RTL), un nombre LTR (chiffres, point
    // décimal, séparateur de milliers) inséré tel quel via setValue()
    // peut s'afficher dans le mauvais ordre : Word applique l'algorithme
    // bidi au texte du run, et sans marque de direction explicite, la
    // ponctuation/l'ordre des groupes de chiffres peut se retrouver
    // inversée au milieu du texte arabe qui l'entoure.
    //
    // On entoure donc la chaîne formatée de deux marques invisibles
    // "Left-to-Right Mark" (U+200E), qui forcent Word à garder le
    // nombre dans le bon ordre sans changer le sens de lecture RTL du
    // reste de la phrase (ex : "...المحكوم بها: ‎19 000.00‎ درهم.").
    // ─────────────────────────────────────────────────────────────
    protected function formatMontant(float $montant): string
    {
        $formatted = number_format($montant, 2, '.', ' ');

        return "\u{200E}{$formatted}\u{200E}";
    }

    // ─────────────────────────────────────────────────────────────
    // Sous-requête partagée : un seul jugement par dossier
    // ─────────────────────────────────────────────────────────────
    /**
     * Un dossier peut traverser plusieurs instances (1ère instance, appel,
     * cassation), chacune produisant son propre jugement. Le jugement le
     * plus récent annule et remplace les précédents : c'est LUI, et lui
     * seul, qui représente l'état actuel du dossier (montant, issue
     * gagné/perdu, etc.) — jamais la somme/le compte de toutes les
     * instances d'un même dossier.
     *
     * Retourne une sous-requête exposant, pour CHAQUE jugement :
     * id, date_jugement, id_dossier, et rn (rang par dossier, rn = 1 =
     * jugement le plus récent). Le filtre "rn = 1" doit être appliqué par
     * l'appelant après ->fromSub().
     *
     * Nécessite ROW_NUMBER() : MySQL 8+ / MariaDB 10.2+ / SQLite 3.25+.
     */
    protected function dernierJugementParDossierQuery()
    {
        return DB::table('jugements')
            ->join(
                'dossier_tribunaux',
                'dossier_tribunaux.id',
                '=',
                'jugements.id_dossier_tribunal'
            )
            ->whereBetween('jugements.date_jugement', [$this->debut, $this->fin])
            ->select([
                'jugements.id',
                'jugements.date_jugement',
                'dossier_tribunaux.id_dossier',
                DB::raw('ROW_NUMBER() OVER (
                    PARTITION BY dossier_tribunaux.id_dossier
                    ORDER BY jugements.date_jugement DESC, jugements.id DESC
                ) AS rn'),
            ]);

    }

    // ─────────────────────────────────────────────────────────────
    // 7) المبالغ المالية المحكوم بها
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesFinancieres(): array
    {
        // Un seul jugement (le plus récent) par dossier, filtré sur la
        // période via sa date_jugement, puis rattaché à sa ligne "finances".
        $finances = DB::query()
            ->fromSub($this->dernierJugementParDossierQuery(), 'dj')
            ->where('dj.rn', 1)
            ->whereBetween('dj.date_jugement', [$this->debut, $this->fin])
            ->join('finances', 'finances.id_jugement', '=', 'dj.id')
            ->select('finances.*', 'dj.id_dossier', 'dj.id as id_jugement');

        // pour l'institution : le dossier (via son jugement retenu) où la
        // partie gagnante est l'institution
        $montantPour = (clone $finances)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'dj.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'مع')
            ->sum('finances.montant_condamne');

        $nbPour = (clone $finances)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'dj.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'مع')
            ->distinct('dj.id_dossier')
            ->count('dj.id_dossier');

        $montantContre = (clone $finances)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'dj.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'ضد')
            ->sum('finances.montant_condamne');

        $nbContre = (clone $finances)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'dj.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'ضد')
            ->distinct('dj.id_dossier')
            ->count('dj.id_dossier');

        $montantExecute = (clone $finances)->where('finances.statut_paiement', 'مكتمل')->sum('finances.montant_paye');
        $nbExecute = (clone $finances)->where('finances.statut_paiement', 'مكتمل')->count();

        $montantEnCours = (clone $finances)->where('finances.statut_paiement', 'جزئي')->sum('finances.montant_paye');
        $nbEnCours = (clone $finances)->whereIn('finances.statut_paiement', ['جزئي', 'في الانتظار'])->count();

        $montantTotal = (clone $finances)->sum('finances.montant_condamne');
        $nbTotal = (clone $finances)->count();

        return [
            'montant_pour'     => $this->formatMontant((float) $montantPour),
            'nb_pour'          => (string) $nbPour,
            'montant_contre'   => $this->formatMontant((float) $montantContre),
            'nb_contre'        => (string) $nbContre,
            'montant_execute'  => $this->formatMontant((float) $montantExecute),
            'nb_execute'       => (string) $nbExecute,
            'montant_en_cours' => $this->formatMontant((float) $montantEnCours),
            'nb_en_cours'      => (string) $nbEnCours,
            'montant_total'    => $this->formatMontant((float) $montantTotal),
            'nb_total'         => (string) $nbTotal,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // 8) أهم المؤشرات (dossiers)
    //
    // Bloc autonome : calcule lui-même les 7 indicateurs affichés dans
    // le template (${dossiers_total}, ${pct_dossiers_juges},
    // ${pct_dossiers_encours}, ${pct_dossiers_perdus}, ${montant_total},
    // ${nb_tribunal_naqd}, ${type_affaire_top}/${type_affaire_top_nb}),
    // sans dépendre de l'ordre d'exécution des autres méthodes dans
    // genererStatistiques(). Les définitions et le périmètre (cohortes)
    // restent volontairement identiques à ceux des sections détaillées
    // correspondantes, pour que les chiffres affichés dans "أهم
    // المؤشرات" concordent avec ceux des tableaux qui les précèdent.
    // ─────────────────────────────────────────────────────────────
    protected function indicateursDossiers(): array
    {
        // 1) إجمالي الملفات القضائية
        // Même cohorte que statistiquesDossiersGlobales() : dossiers
        // ouverts (date_ouverture) pendant la période.
        $baseDossiers = DossierJudiciaire::whereBetween('date_ouverture', [$this->debut, $this->fin]);
        $totalDossiers = (clone $baseDossiers)->count();

        // Voir la note dans statistiquesDossiersGlobales() : un dossier
        // "جاري البت" recouvre aussi bien "تم الحكم" que les étapes
        // d'exécution qui suivent (le jugement a bien été rendu).
        $dossiersJuges = (clone $baseDossiers)
            ->whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', ['تم الحكم', 'تم التنفيذ', 'قيد التنفيذ']))
            ->count();

        // 2) نسبة الملفات التي تم البث فيها
        $pctDossiersJuges = round(($dossiersJuges / max(1, $totalDossiers)) * 100, 1);

        // Un seul jugement (le plus récent) par dossier, filtré sur la
        // période via sa date_jugement — même cohorte que dans
        // statistiquesFinancieres(), pour que "نسبة الملفات... ضد
        // المؤسسة" et "إجمالي المبالغ المالية" portent tous sur
        // exactement le même ensemble de jugements.
        $dernierJugement = DB::query()
            ->fromSub($this->dernierJugementParDossierQuery(), 'dj')
            ->where('dj.rn', 1)
            ->whereBetween('dj.date_jugement', [$this->debut, $this->fin]);

        $dossiersContreInstitution = (clone $dernierJugement)
            ->join('jugement_parties', 'jugement_parties.id_jugement', '=', 'dj.id')
            ->join('position_institutions', 'position_institutions.id', '=', 'jugement_parties.id_position_institution')
            ->where('position_institutions.position', 'ضد')
            ->distinct('dj.id_dossier')
            ->count('dj.id_dossier');

        // 3) نسبة الملفات الجارية (en cours)
        // Statut du dossier lui-même (pas du jugement) : جاري, في طور
        // الاستئناف, ou في طور النقض. Même définition et même cohorte
        // (date_ouverture dans la période) que "$enCours" dans
        // statistiquesDossiersGlobales(), rapportée à $totalDossiers.
        $dossiersEnCours = (clone $baseDossiers)
            ->whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', ['جاري', 'في طور الاستئناف', 'في طور النقض']))
            ->count();

        $pctDossiersEnCours = round(($dossiersEnCours / max(1, $totalDossiers)) * 100, 1);

        // 4) نسبة الملفات التي صدرت ضد المؤسسة
        // Rapportée au total des dossiers de la période ($totalDossiers),
        // et non au seul nombre de dossiers jugés.
        $pctDossiersContreInstitution = round(($dossiersContreInstitution / max(1, $totalDossiers)) * 100, 1);

        // 5) إجمالي المبالغ المالية المحكوم بها
        // Même cohorte de jugements que ci-dessus (dernier jugement par
        // dossier, sur la période) — même périmètre que le "المجموع" du
        // tableau des montants financiers.
        $montantTotal = (clone $dernierJugement)
            ->join('finances', 'finances.id_jugement', '=', 'dj.id')
            ->sum('finances.montant_condamne');

        // 6) عدد الملفات المعروضة أمام محكمة النقض
        // Dossiers distincts ayant une instance ouverte devant محكمة
        // النقض (date_debut de l'instance dans la période) — même
        // définition que ${nb_tribunal_naqd} dans statistiquesParTypeTribunal().
        $dossiersNaqd = DossierTribunal::whereBetween('date_debut', [$this->debut, $this->fin])
            ->join('tribunaux', 'tribunaux.id', '=', 'dossier_tribunaux.id_tribunal')
            ->join('type_tribunaux', 'type_tribunaux.id', '=', 'tribunaux.id_type_tribunal')
            ->where('type_tribunaux.tribunal', 'محكمة النقض')
            ->distinct('dossier_tribunaux.id_dossier')
            ->count('dossier_tribunaux.id_dossier');

        // 7) أكثر أنواع المنازعات تسجيلاً
        $topType = TypeAffaire::withCount(['dossiers' => function ($q) {
            $q->whereBetween('date_ouverture', [$this->debut, $this->fin]);
        }])->orderByDesc('dossiers_count')->first();

        return [
            // Repris ici pour que le bloc "أهم المؤشرات" soit
            // auto-suffisant ; identiques aux valeurs déjà exposées par
            // statistiquesDossiersGlobales() / statistiquesFinancieres()
            // / statistiquesParTypeTribunal() pour les mêmes clés.
            'dossiers_total'       => (string) $totalDossiers,
            'montant_total'        => $this->formatMontant((float) $montantTotal),
            'nb_tribunal_naqd'     => (string) $dossiersNaqd,

            'pct_dossiers_juges'   => (string) $pctDossiersJuges,
            'pct_dossiers_encours' => (string) $pctDossiersEnCours,
            'pct_dossiers_perdus'  => (string) $pctDossiersContreInstitution,

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
    // Classée d'après le type du réclamant lui-même
    // (reclamations.id_reclamant -> reclamants.id_type_reclamant ->
    // type_reclamants.type_reclamant), et non plus d'après la
    // structure de traitement de la réclamation. Chaque réclamation
    // n'a qu'un seul réclamant, donc pas besoin de distinct().
    //
    // Libellés seedés dans DataSeeder (table type_reclamants) :
    // 'شركة', 'مقاولة', 'مرفق عمومي', 'مستخدم', 'مرتفق',
    // 'مؤسسة الوسيط', 'مديرية جهوية', 'مديرية إقليمية', 'آخر'.
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesReclamationsParJiha(): array
    {
        $rows = DB::table('reclamations')
            ->join('reclamants', 'reclamants.id', '=', 'reclamations.id_reclamant')
            ->join('type_reclamants', 'type_reclamants.id', '=', 'reclamants.id_type_reclamant')
            ->whereBetween('reclamations.date_reception', [$this->debut, $this->fin])
            ->select('type_reclamants.type_reclamant as type')
            ->get();

        $c = ['wasit' => 0, 'regionale' => 0, 'provinciale' => 0, 'usagers' => 0, 'autres' => 0];

        foreach ($rows as $r) {
            $type = $r->type;
            match (true) {
                str_contains($type, 'الوسيط')      => $c['wasit']++,
                str_contains($type, 'جهوية')       => $c['regionale']++,
                str_contains($type, 'إقليمية'), str_contains($type, 'اقليمية') => $c['provinciale']++,
                str_contains($type, 'مرتفق'), str_contains($type, 'مستخدم') => $c['usagers']++,
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
    // Basé sur la relation `reclamations.id_type_reclamation` → table
    // `type_reclamations` (voir TypeReclamation::class). Les 4 libellés
    // sont ceux insérés par la migration create_type_reclamations_table.
    // ─────────────────────────────────────────────────────────────
    protected function statistiquesReclamationsParType(): array
    {
        $rows = Reclamation::whereBetween('date_reception', [$this->debut, $this->fin])
            ->join('type_reclamations', 'type_reclamations.id', '=', 'reclamations.id_type_reclamation')
            ->select('type_reclamations.type_reclamation', DB::raw('count(*) as nombre'))
            ->groupBy('type_reclamations.type_reclamation')
            ->pluck('nombre', 'type_reclamation');

        $judiciaire = (int) ($rows['شكايات مرتبطة بالمنازعات القضائية'] ?? 0);
        $rh         = (int) ($rows['شكايات الموارد البشرية'] ?? 0);
        $usagers    = (int) ($rows['شكايات المرتفقين'] ?? 0);
        $autres     = (int) ($rows['شكايات أخرى'] ?? 0);

        // Réclamations sans type assigné (id_type_reclamation null) : comptées dans "autres"
        $sansType = Reclamation::whereBetween('date_reception', [$this->debut, $this->fin])
            ->whereNull('id_type_reclamation')
            ->count();
        $autres += $sansType;

        $total = $judiciaire + $rh + $usagers + $autres;

        $pct = function (int $nombre) use ($total): string {
            if ($total === 0) {
                return '0';
            }

            return (string) round(($nombre / $total) * 100, 1);
        };


        return [
            'nb_recl_type_judiciaire'  => (string) $judiciaire,
            'pct_recl_type_judiciaire' => $pct($judiciaire),
            'nb_recl_type_rh'          => (string) $rh,
            'pct_recl_type_rh'         => $pct($rh),
            'nb_recl_type_usagers'     => (string) $usagers,
            'pct_recl_type_usagers'    => $pct($usagers),
            'nb_recl_type_autres'      => (string) $autres,
            'pct_recl_type_autres'     => $pct($autres),
            'nb_recl_type_total' => (string) $total,
            'pct_recl_type_total' => $total === 0 ? '0' : '100',

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

        $topReclamant = DB::table('reclamations')
            ->join('reclamants', 'reclamants.id', '=', 'reclamations.id_reclamant')
            ->whereBetween('reclamations.date_reception', [$this->debut, $this->fin])
            ->select('reclamants.nom', DB::raw('count(distinct reclamations.id) as nombre'))
            ->groupBy('reclamants.nom')
            ->orderByDesc('nombre')
            ->first();

        return [
            'pct_recl_traitees'  => (string) $pctTraitees,
            'top_jiha_recl'      => $topReclamant->nom ?? '—',
            'top_jiha_recl_nb'   => (string) ($topReclamant->nombre ?? 0),
        ];
    }
}