<?php

namespace Tests\Unit;

use App\Models\DegreeJuridiction;
use App\Rules\NumeroDossierValide;
use App\Support\NumeroDossier;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NumeroDossierTest extends TestCase
{
    #[DataProvider('numerosValides')]
    public function test_numeros_valides(string $numero, string $format): void
    {
        $this->assertTrue(NumeroDossier::estValide($numero, $format));
    }

    public static function numerosValides(): array
    {
        return [
            '1re instance'          => ['2024/7120/1994', NumeroDossier::STANDARD],
            'appel'                 => ['2025/7205/698',  NumeroDossier::STANDARD],
            'avec espaces (ancien)' => ['2024 / 7120 / 1994', NumeroDossier::STANDARD],
            'chiffres arabes'       => ['٢٠٢٤/٧١٢٠/١٩٩٤', NumeroDossier::STANDARD],
            'cassation'             => ['2025/1/4/6327', NumeroDossier::CASSATION],
            'cassation hay\'a 13'   => ['2025/13/7/1', NumeroDossier::CASSATION],
            'cassation 5 chiffres'  => ['2025/10/1/12345', NumeroDossier::CASSATION],
        ];
    }

    #[DataProvider('numerosInvalides')]
    public function test_numeros_invalides(?string $numero, string $format): void
    {
        $this->assertFalse(NumeroDossier::estValide($numero, $format));
    }

    public static function numerosInvalides(): array
    {
        return [
            'cassation donné au format standard' => ['2025/1/4/6327', NumeroDossier::STANDARD],
            'standard donné au format cassation' => ['2024/7120/1994', NumeroDossier::CASSATION],
            'hay\'a 14'                          => ['2025/14/4/6327', NumeroDossier::CASSATION],
            'hay\'a 0'                           => ['2025/0/4/6327', NumeroDossier::CASSATION],
            'chambre 8'                          => ['2025/1/8/6327', NumeroDossier::CASSATION],
            'chambre 0'                          => ['2025/1/0/6327', NumeroDossier::CASSATION],
            'ordre cassation 6 chiffres'         => ['2025/1/4/123456', NumeroDossier::CASSATION],
            'année sur 2 chiffres'               => ['24/7120/1994', NumeroDossier::STANDARD],
            'code sur 5 chiffres'                => ['2024/71200/1994', NumeroDossier::STANDARD],
            'segment vide'                       => ['2024//1994', NumeroDossier::STANDARD],
            'vide'                               => ['', NumeroDossier::STANDARD],
            'null'                               => [null, NumeroDossier::STANDARD],
        ];
    }

    public function test_normaliser_retire_espaces_et_marques_bidi(): void
    {
        $this->assertSame('2024/7120/1994', NumeroDossier::normaliser("2024 / 7120 / 1994"));
        $this->assertSame('2024/7120/1994', NumeroDossier::normaliser("\u{200F}2024/7120/1994\u{200E}"));
        $this->assertSame('2024/7120/1994', NumeroDossier::normaliser('2024\\7120\\1994'));
        $this->assertNull(NumeroDossier::normaliser('   '));
    }

    public function test_assembler_et_decomposer_sont_inverses(): void
    {
        $std = NumeroDossier::assembler(NumeroDossier::STANDARD, ['annee' => '2024', 'code' => '7120', 'ordre' => '1994']);
        $this->assertSame('2024/7120/1994', $std);
        $this->assertSame(
            ['format' => 'standard', 'annee' => '2024', 'code' => '7120', 'hay_a' => null, 'chambre' => null, 'ordre' => '1994'],
            NumeroDossier::decomposer($std)
        );

        $cas = NumeroDossier::assembler(NumeroDossier::CASSATION, ['annee' => '2025', 'hay_a' => '1', 'chambre' => '4', 'ordre' => '6327']);
        $this->assertSame('2025/1/4/6327', $cas);
        $this->assertSame(
            ['format' => 'cassation', 'annee' => '2025', 'code' => null, 'hay_a' => '1', 'chambre' => '4', 'ordre' => '6327'],
            NumeroDossier::decomposer($cas)
        );
    }

    public function test_assembler_renvoie_null_si_tout_est_vide(): void
    {
        $this->assertNull(NumeroDossier::assembler(NumeroDossier::STANDARD, ['annee' => '', 'code' => '', 'ordre' => '']));
        $this->assertNull(NumeroDossier::assembler(NumeroDossier::CASSATION, []));
    }

    public function test_numero_partiel_est_assemble_puis_rejete_par_la_validation(): void
    {
        // L'année est pré-remplie : un numéro à moitié saisi ne doit pas passer.
        $partiel = NumeroDossier::assembler(NumeroDossier::STANDARD, ['annee' => '2024', 'code' => '', 'ordre' => '']);
        $this->assertSame('2024//', $partiel);
        $this->assertFalse(NumeroDossier::estValide($partiel, NumeroDossier::STANDARD));
    }

    public function test_format_selon_le_degre(): void
    {
        $this->assertSame(NumeroDossier::STANDARD, NumeroDossier::formatPourDegre(null));
        $this->assertSame(NumeroDossier::STANDARD, NumeroDossier::formatPourDegre(new DegreeJuridiction(['degre_juridiction' => 'الدرجة الأولى', 'ordre' => 1])));
        $this->assertSame(NumeroDossier::STANDARD, NumeroDossier::formatPourDegre(new DegreeJuridiction(['degre_juridiction' => 'الإستئناف', 'ordre' => 2])));
        $this->assertSame(NumeroDossier::CASSATION, NumeroDossier::formatPourDegre(new DegreeJuridiction(['degre_juridiction' => 'النقض', 'ordre' => 3])));
        // repli sur le libellé quand l'ordre n'est pas renseigné
        $this->assertSame(NumeroDossier::CASSATION, NumeroDossier::formatPourDegre(new DegreeJuridiction(['degre_juridiction' => 'النقض', 'ordre' => 1])));
    }

    public function test_format_selon_le_type_de_recours(): void
    {
        $appel = new DegreeJuridiction(['degre_juridiction' => 'الإستئناف', 'ordre' => 2]);

        $this->assertSame(NumeroDossier::CASSATION, NumeroDossier::formatPourTypeRecours('الطعن بالنقض', $appel));
        $this->assertSame(NumeroDossier::CASSATION, NumeroDossier::formatPourTypeRecours('Pourvoi en cassation', $appel));
        $this->assertSame(NumeroDossier::STANDARD, NumeroDossier::formatPourTypeRecours('استئناف', null));
        // opposition / révision : même degré que l'instance d'origine
        $this->assertSame(NumeroDossier::STANDARD, NumeroDossier::formatPourTypeRecours('تعرض', $appel));
        $cassation = new DegreeJuridiction(['degre_juridiction' => 'النقض', 'ordre' => 3]);
        $this->assertSame(NumeroDossier::CASSATION, NumeroDossier::formatPourTypeRecours('إعادة النظر', $cassation));
    }

    public function test_fusionner_assemble_les_segments_de_la_requete(): void
    {
        $request = Request::create('/', 'POST', ['numero' => ['annee' => '2025', 'hay_a' => '1', 'chambre' => '4', 'ordre' => '6327']]);
        NumeroDossier::fusionner($request, NumeroDossier::CASSATION);
        $this->assertSame('2025/1/4/6327', $request->input('numero_dossier_tribunal'));

        // valeur brute collée par l'utilisateur
        $request = Request::create('/', 'POST', ['numero_dossier_tribunal' => '2024 / 7120 / 1994']);
        NumeroDossier::fusionner($request, NumeroDossier::STANDARD);
        $this->assertSame('2024/7120/1994', $request->input('numero_dossier_tribunal'));
    }

    public function test_la_regle_signale_un_mauvais_format(): void
    {
        $messages = [];
        $fail = function (string $m) use (&$messages) { $messages[] = $m; };

        (new NumeroDossierValide(NumeroDossier::CASSATION))->validate('numero_dossier_tribunal', '2024/7120/1994', $fail);

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('محكمة النقض', $messages[0]);
    }

    public function test_la_regle_accepte_un_bon_format_sans_tribunal(): void
    {
        $messages = [];
        $fail = function (string $m) use (&$messages) { $messages[] = $m; };

        (new NumeroDossierValide(NumeroDossier::STANDARD))->validate('numero_dossier_tribunal', '2024/7120/1994', $fail);

        $this->assertSame([], $messages);
    }
}
