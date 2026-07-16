<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

use App\Observers\JugementObserver;
use App\Observers\ExecutionObserver;

use App\Models\Jugement;
use App\Models\Execution;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Jugement::observe(JugementObserver::class);
        Execution::observe(ExecutionObserver::class);

        $this->registerSortableMacro();
    }

    /**
     * Macro générique de tri des colonnes, utilisable sur toutes les listes
     * (ex: Model::query()->sortable([...])).
     *
     * Chaque entrée du tableau $columns associe une "clé de tri" (celle
     * utilisée dans l'URL ?sort=cle&direction=asc|desc) soit :
     *   - à un nom de colonne réel de la table   => 'nom' => 'nom_colonne'
     *   - à une closure personnalisée            => 'nom' => fn($q, $dir) => $q->orderBy(...)
     *     (utile pour trier sur une colonne d'une relation via une sous-requête)
     */
    protected function registerSortableMacro(): void
    {
        Builder::macro('sortable', function (array $columns, ?string $default = null, string $defaultDirection = 'desc') {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $sort = request('sort');
            $direction = request('direction') === 'asc' ? 'asc' : 'desc';

            if (!$sort || !array_key_exists($sort, $columns)) {
                $sort = $default;
                $direction = $default ? $defaultDirection : $direction;
            }

            if ($sort && array_key_exists($sort, $columns)) {
                $column = $columns[$sort];

                return $column instanceof \Closure
                    ? $column($this, $direction)
                    : $this->orderBy($column, $direction);
            }

            return $this;
        });
    }
}