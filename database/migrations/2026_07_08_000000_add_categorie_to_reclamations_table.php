<?php
// database/migrations/2026_07_08_000000_add_categorie_to_reclamations_table.php
//
// Le modèle de rapport demande une répartition des شكايات par "نوع الشكاية"
// (شكايات قضائية / شكايات موارد بشرية / شكايات مرتفقين / أخرى).
// Cette information n'existe pas dans le schéma actuel (seul "objet" en texte
// libre est disponible), donc on ajoute une colonne de catégorie.
// Si vous préférez classer via "objet" par mots-clés plutôt que par une vraie
// colonne, vous pouvez ignorer cette migration : le service fournit un
// classement heuristique de secours basé sur "objet".

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->enum('categorie', [
                'قضائية',
                'موارد_بشرية',
                'مرتفقين',
                'أخرى',
            ])->nullable()->after('id_statut_reclamation');
        });
    }

    public function down()
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->dropColumn('categorie');
        });
    }
};
