<?php

namespace App\Console\Commands;

use App\Models\DegreeJuridiction;
use App\Models\Tribunal;
use App\Models\TypeRecours;
use Illuminate\Console\Command;

class DiagnosticRecours extends Command
{
    protected $signature = 'diagnostic:recours';

    protected $description = 'Vérifie les données nécessaires à la transition automatique 1er degré → appel → cassation';

    public function handle(): int
    {
        $this->info('── Degrés de juridiction (degre_juridictions) ──');
        $degres = DegreeJuridiction::all(['id', 'degre_juridiction']);

        if ($degres->isEmpty()) {
            $this->error('Table degre_juridictions VIDE. C\'est la cause : aucune transition ne peut se faire.');
        } else {
            $this->table(['id', 'degre_juridiction'], $degres->map(fn($d) => [$d->id, $d->degre_juridiction])->toArray());
        }

        $appel = DegreeJuridiction::whereRaw("LOWER(degre_juridiction) LIKE '%استئناف%'")->first();
        $cassation = DegreeJuridiction::whereRaw("LOWER(degre_juridiction) LIKE '%نقض%'")->first();

        $this->line('Degré "استئناف" trouvé : ' . ($appel ? "OUI (id={$appel->id}, '{$appel->degre_juridiction}')" : 'NON ← BUG ICI'));
        $this->line('Degré "نقض" trouvé : ' . ($cassation ? "OUI (id={$cassation->id}, '{$cassation->degre_juridiction}')" : 'NON ← BUG ICI'));

        $this->newLine();
        $this->info('── Types de recours (type_recours) ──');
        $types = TypeRecours::all(['id', 'type_recours', 'delai_legal_jours']);

        if ($types->isEmpty()) {
            $this->error('Table type_recours VIDE. Aucun recours ne peut être classifié.');
        } else {
            $this->table(
                ['id', 'type_recours', 'delai_legal_jours', 'classé comme'],
                $types->map(function ($t) {
                    $nom = strtolower($t->type_recours);
                    $classe = match (true) {
                        str_contains($nom, 'rejet') => 'cassation-rejet',
                        str_contains($nom, 'renvoi') => 'cassation-renvoi',
                        (str_contains($nom, 'pourvoi') || str_contains($nom, 'نقض'))
                            && !str_contains($nom, 'rejet') && !str_contains($nom, 'renvoi') => 'pourvoi (cassation)',
                        (str_contains($nom, 'استئناف') || str_contains($nom, 'appel'))
                            && !str_contains($nom, 'نقض') && !str_contains($nom, 'cassation') => 'appel',
                        str_contains($nom, 'تعرض') || str_contains($nom, 'opposition') => 'opposition',
                        str_contains($nom, 'إعادة النظر') || str_contains($nom, 'revision') => 'revision',
                        default => '⚠️ AUCUNE TRANSITION (type inconnu)',
                    };
                    return [$t->id, $t->type_recours, $t->delai_legal_jours, $classe];
                })->toArray()
            );
        }

        $this->newLine();
        $this->info('── Tribunaux par degré ──');
        $parDegre = Tribunal::selectRaw('id_degre, count(*) as total')->groupBy('id_degre')->get();
        $this->table(['id_degre', 'nombre de tribunaux'], $parDegre->map(fn($r) => [$r->id_degre, $r->total])->toArray());

        return self::SUCCESS;
    }
}