# Conception de la table de correspondance des tribunaux d'appel

## Problématique actuelle
Le système actuel détermine la juridiction d'appel en se basant sur des heuristiques liées à la province et à la région du tribunal de première instance. Cette approche est insuffisante pour refléter la complexité et la spécificité des ressorts judiciaires marocains, où une cour d'appel peut couvrir des tribunaux de première instance situés dans différentes provinces, voire régions, et où la nature du dossier (administratif, commercial, général) détermine la cour d'appel compétente.

## Solution proposée : Table de correspondance explicite
Pour résoudre ce problème, nous proposons d'introduire une nouvelle table de base de données qui établira une correspondance explicite entre les tribunaux de première instance et leurs cours d'appel respectives, en tenant compte du type de tribunal (administratif, commercial, général).

### Nouvelle table : `tribunal_appel_relations`
Cette table permettra de mapper directement un tribunal de premier degré à son tribunal d'appel pour un type de juridiction donné.

| Colonne | Type de données | Description | Contraintes |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | Identifiant unique de la relation | Clé primaire, Auto-incrémenté |
| `tribunal_premier_degre_id` | `BIGINT UNSIGNED` | ID du tribunal de première instance | Clé étrangère vers `tribunaux.id` |
| `tribunal_appel_id` | `BIGINT UNSIGNED` | ID du tribunal d'appel correspondant | Clé étrangère vers `tribunaux.id` |
| `type_tribunal_id` | `BIGINT UNSIGNED` | ID du type de tribunal (simple, administratif, commercial) pour lequel cette relation s'applique | Clé étrangère vers `type_tribunaux.id` |

### Modèle Eloquent : `TribunalAppelRelation`
Un nouveau modèle Eloquent sera créé pour interagir avec cette table.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TribunalAppelRelation extends Model
{
    use HasFactory;

    protected $table = 'tribunal_appel_relations';

    protected $fillable = [
        'tribunal_premier_degre_id',
        'tribunal_appel_id',
        'type_tribunal_id',
    ];

    public function tribunalPremierDegre()
    {
        return $this->belongsTo(Tribunal::class, 'tribunal_premier_degre_id');
    }

    public function tribunalAppel()
    {
        return $this->belongsTo(Tribunal::class, 'tribunal_appel_id');
    }

    public function typeTribunal()
    {
        return $this->belongsTo(TypeTribunal::class, 'type_tribunal_id');
    }
}
```

## Impact sur la logique existante
La méthode `trouverTribunalSuivant` dans `RecoursController` sera modifiée pour utiliser cette nouvelle table de correspondance. Au lieu de chercher par province/région, elle interrogera `tribunal_appel_relations` en utilisant `tribunal_premier_degre_id` et `type_tribunal_id` pour trouver directement le `tribunal_appel_id`.

## Vérification des IDs des types de tribunaux
Avant d'implémenter la migration et le seeder pour cette nouvelle table, il est crucial de s'assurer de la cohérence des IDs des `type_tribunaux`. D'après l'analyse, le `DataSeeder` définit les types de tribunaux avec les IDs suivants (basé sur l'ordre d'insertion):

| ID | Type de Tribunal (Arabe) | Type de Tribunal (Français) |
|---|---|---|
| 1 | المحكمة الابتدائية | Tribunal de Première Instance |
| 2 | محكمة الاستئناف | Cour d'Appel |
| 3 | محكمة النقض | Cour de Cassation |
| 4 | المحكمة الإدارية | Tribunal Administratif |
| 5 | محكمة الاستئناف الإدارية | Cour d'Appel Administrative |
| 6 | المحكمة التجارية | Tribunal de Commerce |
| 7 | محكمة الاستئناف التجارية | Cour d'Appel Commerciale |

Ces IDs seront utilisés pour la colonne `type_tribunal_id` dans la nouvelle table `tribunal_appel_relations`.
