<?php

namespace App\Support;

use App\Models\DegreeJuridiction;
use Illuminate\Http\Request;

/**
 * Formats des numéros de dossier selon la juridiction.
 *
 *  STANDARD  (1re instance, appel, opposition, révision) : année/code/ordre
 *            ex. 2024/7120/1994
 *  CASSATION : année/رمز الهيئة (1-13)/الغرفة (1-7)/ordre
 *            ex. 2025/1/4/6327
 *
 * Les valeurs sont stockées sous forme canonique : segments séparés par "/",
 * sans espaces, chiffres latins.
 */
final class NumeroDossier
{
    public const STANDARD  = 'standard';
    public const CASSATION = 'cassation';

    private const REGEX = [
        self::STANDARD  => '#^\d{4}/\d{1,4}/\d{1,6}$#',
        self::CASSATION => '#^\d{4}/(?:1[0-3]|[1-9])/[1-7]/\d{1,5}$#',
    ];

    private const SEGMENTS = [
        self::STANDARD  => ['annee', 'code', 'ordre'],
        self::CASSATION => ['annee', 'hay_a', 'chambre', 'ordre'],
    ];

    private const EXEMPLES = [
        self::STANDARD  => '2024/7120/1994',
        self::CASSATION => '2025/1/4/6327',
    ];

    public const HAY_A_MAX   = 13; // رمز الهيئة : 1 à 13
    public const CHAMBRE_MAX = 7;  // الغرفة : 1 à 7

    public static function formats(): array
    {
        return array_keys(self::REGEX);
    }

    public static function exemple(string $format): string
    {
        return self::EXEMPLES[$format] ?? self::EXEMPLES[self::STANDARD];
    }

    /** Cassation = degré d'ordre 3 ou libellé contenant « نقض » (même critère que RecoursController). */
    public static function formatPourDegre(?DegreeJuridiction $degre): string
    {
        if ($degre && ((int) $degre->ordre === 3 || str_contains((string) $degre->degre_juridiction, 'نقض'))) {
            return self::CASSATION;
        }

        return self::STANDARD;
    }

    /**
     * Format du numéro que portera l'instance ouverte par un recours.
     * Même classification que RecoursController::store().
     */
    public static function formatPourTypeRecours(?string $typeRecours, ?DegreeJuridiction $degreOrigine = null): string
    {
        $nom = mb_strtolower((string) $typeRecours);

        $pourvoi = (str_contains($nom, 'pourvoi') || str_contains($nom, 'نقض'))
            && ! str_contains($nom, 'rejet') && ! str_contains($nom, 'رفض')
            && ! str_contains($nom, 'renvoi') && ! str_contains($nom, 'إحالة');

        if ($pourvoi) {
            return self::CASSATION;
        }

        $appel = (str_contains($nom, 'استئناف') || str_contains($nom, 'appel'))
            && ! str_contains($nom, 'نقض') && ! str_contains($nom, 'cassation');

        $memeDegre = str_contains($nom, 'تعرض') || str_contains($nom, 'opposition')
            || str_contains($nom, 'إعادة النظر') || str_contains($nom, 'revision');

        if ($memeDegre && ! $appel) {
            return self::formatPourDegre($degreOrigine);
        }

        return self::STANDARD;
    }

    /** Forme canonique : chiffres latins, sans espaces ni marques bidirectionnelles. */
    public static function normaliser(?string $numero): ?string
    {
        if ($numero === null) {
            return null;
        }

        $numero = strtr($numero, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '\\' => '/',
        ]);

        $numero = preg_replace('/[\s\x{200E}\x{200F}\x{061C}\x{202A}-\x{202E}]+/u', '', $numero);

        return $numero === '' ? null : $numero;
    }

    public static function estValide(?string $numero, string $format): bool
    {
        $numero = self::normaliser($numero);

        return $numero !== null
            && preg_match(self::REGEX[$format] ?? self::REGEX[self::STANDARD], $numero) === 1;
    }

    /** Assemble les segments saisis ; null si tous sont vides. */
    public static function assembler(string $format, array $segments): ?string
    {
        $valeurs = [];
        foreach (self::SEGMENTS[$format] ?? self::SEGMENTS[self::STANDARD] as $cle) {
            $valeurs[] = self::normaliser((string) ($segments[$cle] ?? '')) ?? '';
        }

        return implode('', $valeurs) === '' ? null : implode('/', $valeurs);
    }

    /**
     * Découpe un numéro stocké pour pré-remplir le formulaire.
     *
     * @return array{format:string, annee:?string, code:?string, hay_a:?string, chambre:?string, ordre:?string}
     */
    public static function decomposer(?string $numero): array
    {
        $vide = ['format' => self::STANDARD, 'annee' => null, 'code' => null, 'hay_a' => null, 'chambre' => null, 'ordre' => null];
        $parts = explode('/', self::normaliser($numero) ?? '');

        if (count($parts) === 4) {
            return array_merge($vide, [
                'format' => self::CASSATION,
                'annee' => $parts[0], 'hay_a' => $parts[1], 'chambre' => $parts[2], 'ordre' => $parts[3],
            ]);
        }

        if (count($parts) === 3) {
            return array_merge($vide, ['annee' => $parts[0], 'code' => $parts[1], 'ordre' => $parts[2]]);
        }

        return $vide;
    }

    /**
     * Construit `numero_dossier_tribunal` dans la requête à partir des segments
     * `numero[...]` du composant de saisie (ou normalise une valeur brute).
     */
    public static function fusionner(Request $request, string $format): void
    {
        $segments = $request->input('numero');

        if (is_array($segments)) {
            $request->merge(['numero_dossier_tribunal' => self::assembler($format, $segments)]);
        } elseif ($request->has('numero_dossier_tribunal')) {
            $request->merge(['numero_dossier_tribunal' => self::normaliser($request->input('numero_dossier_tribunal'))]);
        }
    }
}
