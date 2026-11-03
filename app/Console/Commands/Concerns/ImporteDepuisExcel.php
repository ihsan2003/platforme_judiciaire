<?php

namespace App\Console\Commands\Concerns;

use App\Models\Commune;
use App\Models\Partie;
use App\Models\Province;
use App\Models\Tribunal;
use App\Models\TypeAffaire;
use App\Models\TypeJugement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

/**
 * Socle commun des commandes d'import Excel/CSV :
 *   import:dossiers, import:avertissements, import:reclamations.
 *
 * Fournit : lecture du fichier (.xlsx / .csv / .tsv), boucle d'import transactionnelle
 * (avec mode simulation), rapport CSV, et résolution des référentiels
 * (tribunal, type d'affaire, commune, parties).
 *
 * La commande qui l'utilise doit déclarer les options : --feuille, --dry-run, --user,
 * --institution. L'option --creer-types est facultative.
 */
trait ImporteDepuisExcel
{
    /** Fragments qui signalent une personne morale. */
    private function motsPersonneMorale(): array
    {
        return ['مؤسسة', 'مركز', 'مديرية', 'وزارة', 'جماعة', 'شركة', 'مكتب', 'وكالة', 'جمعية', 'بنك', 'مجلس', 'الوطنية', 'الوطني'];
    }

    private array $rapport = [];
    private array $stats = [];
    private array $typesInconnus = [];
    private ?User $createur = null;
    private $typesAffaire;
    private $tribunaux;

    // ───────────────────────────────────────────────────────────────
    // Boucle d'import
    // ───────────────────────────────────────────────────────────────

    /**
     * @param  string[]  $colonnesRequises  en-têtes qui doivent exister dans le fichier
     * @param  callable  $nature   fn(array $row): string  — nature de la ligne ('vide' = ignorée)
     * @param  callable  $traiter  fn(string $nature, array $row): array{0:string,1:string}  — [statut, message]
     * @param  array<string,string>  $libelles  nature => libellé affiché dans le tableau final
     * @param  string  $cleNumero  en-tête repris dans la colonne « numero » du rapport ('' = aucune)
     */
    private function importerFichier(
        array $colonnesRequises,
        callable $nature,
        callable $traiter,
        array $libelles,
        string $prefixeRapport,
        string $cleNumero = ''
    ): int {
        $chemin = $this->argument('fichier');
        if (! is_file($chemin)) {
            $this->error("Fichier introuvable : {$chemin}");
            return 1;
        }

        $this->createur = $this->option('user')
            ? User::where('email', $this->option('user'))->first()
            : User::orderBy('id')->first();
        if (! $this->createur) {
            $this->error("Aucun utilisateur trouvé (created_by est obligatoire). Créez un utilisateur ou passez --user=email.");
            return 1;
        }

        try {
            $lignes = $this->lireFichier($chemin, $this->option('feuille'));
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return 1;
        }

        if (count($lignes) < 2) {
            $this->error('Le fichier ne contient aucune ligne de données.');
            return 1;
        }

        $entetes = array_map(fn ($h) => $this->nettoyer($h), array_shift($lignes));
        $colonnes = array_flip(array_filter($entetes, fn ($h) => $h !== ''));
        foreach ($colonnesRequises as $requise) {
            if (! isset($colonnes[$requise])) {
                $this->error("Colonne obligatoire absente : {$requise}");
                return 1;
            }
        }

        $this->typesAffaire = TypeAffaire::all();
        $this->tribunaux = Tribunal::all();
        $dry = (bool) $this->option('dry-run');

        $this->info(($dry ? '[SIMULATION] ' : '') . count($lignes) . ' ligne(s) à traiter.');
        $barre = $this->output->createProgressBar(count($lignes));

        // Transaction englobante : en simulation, tout est annulé à la fin, mais les
        // lignes voient les écritures des lignes précédentes (doublons, regroupements).
        DB::beginTransaction();

        foreach ($lignes as $i => $brut) {
            $numLigne = $i + 2; // en-tête = ligne 1
            $row = [];
            foreach ($colonnes as $nom => $idx) {
                $row[$nom] = $this->nettoyer($brut[$idx] ?? '');
            }

            if (implode('', $row) === '') {
                $barre->advance();
                continue;
            }

            $natureLigne = $nature($row);

            if ($natureLigne === 'vide') {
                [$statut, $message] = ['ignore', 'Sans numéro de dossier : ligne ignorée'];
            } else {
                DB::beginTransaction();
                try {
                    [$statut, $message] = $traiter($natureLigne, $row);
                    DB::commit(); // libère le point de sauvegarde de la ligne
                } catch (\Throwable $e) {
                    DB::rollBack();
                    [$statut, $message] = ['rejete', $e->getMessage()];
                }
            }

            $this->stats[$natureLigne][$statut] = ($this->stats[$natureLigne][$statut] ?? 0) + 1;
            $this->rapport[] = [$numLigne, $natureLigne, $statut, $message, $cleNumero !== '' ? ($row[$cleNumero] ?? '') : ''];
            $barre->advance();
        }

        $dry ? DB::rollBack() : DB::commit();

        $barre->finish();
        $this->newLine(2);

        $this->ecrireRapport($prefixeRapport);

        $lignesTable = [];
        foreach ($libelles as $natureCle => $libelle) {
            if (isset($this->stats[$natureCle])) {
                $st = $this->stats[$natureCle];
                $lignesTable[] = [$libelle, $st['importe'] ?? 0, $st['ignore'] ?? 0, $st['rejete'] ?? 0];
            }
        }
        $this->table(['Nature', 'Importés', 'Ignorés (doublon / hors périmètre)', 'Rejetés'], $lignesTable);

        if ($this->typesInconnus) {
            $this->warn('Types d\'affaire inconnus (relancez avec --creer-types ou corrigez le fichier) :');
            foreach (array_count_values($this->typesInconnus) as $t => $n) {
                $this->line("  - {$t} ({$n})");
            }
        }
        if ($dry) {
            $this->comment('Simulation : aucune donnée n\'a été écrite.');
        }

        return 0;
    }

    // ───────────────────────────────────────────────────────────────
    // Éléments communs d'une ligne
    // ───────────────────────────────────────────────────────────────

    private function dateDeLigne(array $row): Carbon
    {
        $annee = (int) $this->chiffresLatins($row['السنة'] ?? '');
        if ($annee < 1990 || $annee > ((int) date('Y') + 1)) {
            throw new RuntimeException("Année invalide : « " . ($row['السنة'] ?? '') . " »");
        }
        return Carbon::create($annee, 1, 1)->startOfDay();
    }

    /**
     * Parties de la ligne : [المدعي, المدعى عليه] (null si vide).
     * @return array{0:?Partie,1:?Partie}
     */
    private function partiesDeLigne(array $row): array
    {
        $demandeur = $this->vide($row['المدعي'] ?? '');
        $defendeur = $this->vide($row['المدعى عليه'] ?? '');

        return [
            $demandeur ? $this->trouverOuCreerPartie($demandeur) : null,
            $defendeur ? $this->trouverOuCreerPartie($defendeur) : null,
        ];
    }

    private function typeJugementAuFond(): ?int
    {
        return TypeJugement::where('type_jugement', TypeJugement::AU_FOND)->value('id');
    }

    /** @return array{0:Carbon,1:?string} date du jugement + éventuel avertissement */
    private function dateJugement(string $texte, Carbon $repli): array
    {
        $date = $this->dateDansTexte($texte);
        return $date
            ? [$date, null]
            : [$repli->copy(), "Date du jugement non lisible dans le texte : date d'ouverture utilisée"];
    }

    // ───────────────────────────────────────────────────────────────
    // Résolution des référentiels
    // ───────────────────────────────────────────────────────────────

    /** @return array{0:Tribunal,1:?string} */
    private function trouverTribunal(array $row): array
    {
        $note = null;
        $parId = ($id = (int) $this->chiffresLatins($row['id_tribunal'] ?? '')) ? $this->tribunaux->firstWhere('id', $id) : null;

        $nomExcel = $this->cle($row['nom_tribunal_corrigé'] ?? '');
        $parNom = $nomExcel !== ''
            ? $this->tribunaux->first(fn ($t) => $this->cle($t->nom_tribunal) === $nomExcel)
            : null;

        if ($parId && $parNom && $parId->id !== $parNom->id) {
            $note = "id_tribunal={$parId->id} (« {$parId->nom_tribunal} ») ≠ nom « {$row['nom_tribunal_corrigé']} » : id retenu";
        }

        $tribunal = $parId ?? $parNom;
        if (! $tribunal) {
            throw new RuntimeException("Tribunal introuvable (id_tribunal=" . ($row['id_tribunal'] ?? '') . ", « " . ($row['nom_tribunal_corrigé'] ?? '') . " »)");
        }

        return [$tribunal, $note];
    }

    private function trouverTypeAffaire(string $libelle, bool $obligatoire = true): ?TypeAffaire
    {
        $cle = $this->cleSansOrdre($libelle);
        $type = $cle === '' ? null : $this->typesAffaire->first(fn ($t) => $this->cleSansOrdre($t->affaire) === $cle);

        if ($type) {
            return $type;
        }

        if (! $obligatoire) {
            return null;
        }

        if ($libelle !== '' && $this->hasOption('creer-types') && $this->option('creer-types')) {
            $type = TypeAffaire::create(['affaire' => $libelle, 'code' => null]);
            $this->typesAffaire->push($type);
            return $type;
        }

        $this->typesInconnus[] = $libelle === '' ? '(vide)' : $libelle;
        throw new RuntimeException("Type d'affaire inconnu : « {$libelle} »");
    }

    private function trouverCommune(array $row, Tribunal $tribunal): ?Commune
    {
        $nom = $this->cle($row['nom_commune'] ?? '');
        if ($nom === '') {
            return null;
        }

        $idProvince = (int) $this->chiffresLatins($row['id_province'] ?? '') ?: null;
        if ($idProvince && ! Province::whereKey($idProvince)->exists()) {
            $idProvince = null;
        }
        if (! $idProvince && ($row['nom_province_corrigé'] ?? '') !== '') {
            $cleP = $this->cle($row['nom_province_corrigé']);
            $idProvince = Province::all()->first(fn ($p) => $this->cle($p->province) === $cleP)?->id;
        }
        $idProvince ??= $tribunal->id_province;

        return Commune::where('id_province', $idProvince)->get()
            ->first(fn ($c) => $this->cle($c->commune) === $nom);
    }

    /**
     * Retrouve un libellé de référentiel (statut, type…) par comparaison normalisée
     * (« في طور الاجراء » = « في طور الإجراء »).
     *
     * @param  iterable<int,object>  $modeles
     */
    private function trouverParLibelle(iterable $modeles, string $colonne, string $libelle): mixed
    {
        $cle = $this->cle($libelle);
        if ($cle === '') {
            return null;
        }
        foreach ($modeles as $modele) {
            if ($this->cle((string) $modele->{$colonne}) === $cle) {
                return $modele;
            }
        }
        return null;
    }

    /** Fragments d'identification de l'institution (défaut : التعاون الوطني). */
    private function fragmentsInstitution(): array
    {
        $f = array_filter((array) $this->option('institution'));
        return $f ?: ['التعاون الوطني'];
    }

    private function estInstitution(string $nom): bool
    {
        foreach ($this->fragmentsInstitution() as $fragment) {
            if (str_contains($this->cle($nom), $this->cle($fragment))) {
                return true;
            }
        }
        return false;
    }

    private function trouverOuCreerPartie(string $nom): Partie
    {
        $existante = Partie::where('nom_partie', $nom)->first();
        if ($existante) {
            if (! $existante->est_entraide && $this->estInstitution($nom)) {
                $existante->update(['est_entraide' => true]);
            }
            return $existante;
        }

        $institution = false;
        foreach ($this->fragmentsInstitution() as $fragment) {
            if ($fragment !== '' && str_contains($this->cle($nom), $this->cle($fragment))) {
                $institution = true;
            }
        }

        $morale = $institution;
        foreach ($this->motsPersonneMorale() as $mot) {
            if (str_contains($nom, $mot)) {
                $morale = true;
            }
        }

        return Partie::create([
            'nom_partie'         => $nom,
            'type_personne'      => $morale ? 'اعتباري' : 'ذاتي',
            'identifiant_unique' => null,
            'est_entraide'       => $institution,
        ]);
    }

    // ───────────────────────────────────────────────────────────────
    // Utilitaires texte
    // ───────────────────────────────────────────────────────────────

    private function nettoyer($v): string
    {
        $v = (string) $v;
        $v = preg_replace('/[\x{200E}\x{200F}\x{061C}\x{202A}-\x{202E}\x{00A0}]/u', ' ', $v);
        return trim(preg_replace('/\s+/u', ' ', $v));
    }

    private function vide(string $v): ?string
    {
        return $v === '' ? null : $v;
    }

    private function chiffresLatins(string $s): string
    {
        return strtr($s, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /** Clé de comparaison : sans diacritiques/tatweel, alef/ya/ta marbouta unifiés. */
    private function cle(string $s): string
    {
        $s = $this->nettoyer($s);
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0640}]/u', '', $s);
        $s = strtr($s, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ي', 'ة' => 'ه']);
        return mb_strtolower($s);
    }

    /** Comme cle() mais insensible à l'ordre des mots (« عقاري مدني » = « مدني عقاري »). */
    private function cleSansOrdre(string $s): string
    {
        $mots = array_filter(array_map(
            fn ($m) => mb_strlen($m) > 3 && str_starts_with($m, 'ال') ? mb_substr($m, 2) : $m,
            explode(' ', $this->cle($s))
        ));
        sort($mots);
        return implode(' ', $mots);
    }

    private function dateDansTexte(string $texte): ?Carbon
    {
        $texte = $this->chiffresLatins($texte);
        if (preg_match_all('#(\d{1,2})\s*[/\-.]\s*(\d{1,2})\s*[/\-.]\s*(\d{4})#', $texte, $m, PREG_SET_ORDER)) {
            foreach ($m as [$_, $j, $mo, $a]) {
                if (checkdate((int) $mo, (int) $j, (int) $a)) {
                    return Carbon::create((int) $a, (int) $mo, (int) $j)->startOfDay();
                }
            }
        }
        return null;
    }

    // ───────────────────────────────────────────────────────────────
    // Lecture du fichier
    // ───────────────────────────────────────────────────────────────

    /** @return array<int,array<int,string>> */
    private function lireFichier(string $chemin, ?string $feuille): array
    {
        $ext = strtolower(pathinfo($chemin, PATHINFO_EXTENSION));

        if (in_array($ext, ['csv', 'tsv', 'txt'], true)) {
            $contenu = file_get_contents($chemin);
            $contenu = preg_replace('/^\xEF\xBB\xBF/', '', $contenu);
            $premiere = strtok($contenu, "\n");
            $sep = substr_count($premiere, "\t") >= substr_count($premiere, ',') ? "\t" : ',';
            $h = fopen('php://temp', 'r+');
            fwrite($h, $contenu);
            rewind($h);
            $lignes = [];
            while (($r = fgetcsv($h, 0, $sep)) !== false) {
                $lignes[] = $r;
            }
            return $lignes;
        }

        if ($ext !== 'xlsx') {
            throw new RuntimeException('Format non pris en charge (utilisez .xlsx, .csv ou .tsv).');
        }

        $zip = new ZipArchive();
        if ($zip->open($chemin) !== true) {
            throw new RuntimeException('Impossible d\'ouvrir le fichier xlsx.');
        }

        $partages = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->si as $si) {
                $t = '';
                if (isset($si->t)) {
                    $t = (string) $si->t;
                } else {
                    foreach ($si->r as $r) {
                        $t .= (string) $r->t;
                    }
                }
                $partages[] = $t;
            }
        }

        // Feuille cible
        $wb = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $cible = null;
        foreach ($wb->sheets->sheet as $s) {
            if ($feuille === null || (string) $s['name'] === $feuille) {
                $rid = (string) $s->attributes('r', true)['id'];
                foreach ($rels->Relationship as $rel) {
                    if ((string) $rel['Id'] === $rid) {
                        $cible = 'xl/' . ltrim(preg_replace('#^/?(xl/)?#', '', (string) $rel['Target']), '/');
                    }
                }
                break;
            }
        }
        if (! $cible || ($xml = $zip->getFromName($cible)) === false) {
            throw new RuntimeException('Feuille introuvable' . ($feuille ? " : {$feuille}" : '.'));
        }

        $sheet = simplexml_load_string($xml);
        $lignes = [];
        foreach ($sheet->sheetData->row as $row) {
            $ligne = [];
            foreach ($row->c as $c) {
                $idx = $this->indexColonne((string) $c['r']);
                $type = (string) $c['t'];
                if ($type === 's') {
                    $val = $partages[(int) $c->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = (string) $c->is->t;
                } else {
                    $val = (string) $c->v;
                    if ($type === '' && preg_match('/^-?\d+\.0+$/', $val)) {
                        $val = (string) (int) $val;
                    }
                }
                $ligne[$idx] = $val;
            }
            $max = $ligne ? max(array_keys($ligne)) : -1;
            $plein = [];
            for ($i = 0; $i <= $max; $i++) {
                $plein[$i] = $ligne[$i] ?? '';
            }
            $lignes[] = $plein;
        }
        $zip->close();

        return $lignes;
    }

    private function indexColonne(string $ref): int
    {
        preg_match('/^([A-Z]+)/', $ref, $m);
        $n = 0;
        foreach (str_split($m[1]) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }
        return $n - 1;
    }

    private function ecrireRapport(string $prefixe): void
    {
        $dossier = storage_path('app');
        if (! is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }
        $fichier = $dossier . '/' . $prefixe . '_' . date('Ymd_His') . '.csv';
        $h = fopen($fichier, 'w');
        fwrite($h, "\xEF\xBB\xBF");
        fputcsv($h, ['ligne', 'nature', 'statut', 'message', 'numero']);
        foreach ($this->rapport as $r) {
            fputcsv($h, $r);
        }
        fclose($h);
        $this->info("Rapport détaillé : {$fichier}");
    }
}
