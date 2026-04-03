<?php
// database/migrations/2024_01_01_000002_create_main_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

return new class extends Migration
{
    public function up()
    {
        // Régions
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('region')->unique();
            $table->timestamps();
        });

        // Provinces
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('province');
            $table->foreignId('id_region')->constrained('regions');
            $table->timestamps();
        });

        // Tribunaux
        Schema::create('tribunaux', function (Blueprint $table) {
            $table->id();
            $table->string('nom_tribunal');
            $table->foreignId('id_type_tribunal')->constrained('type_tribunaux');
            $table->foreignId('id_province')->constrained('provinces');
            $table->timestamps();
        });

        // Avocats
        Schema::create('avocats', function (Blueprint $table) {
            $table->id();
            $table->string('nom_avocat');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // Parties
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('nom_partie');
            $table->enum('type_personne', ['Physique', 'Morale']);
            $table->string('identifiant_unique');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->timestamps();
        });

        // Juges
        Schema::create('juges', function (Blueprint $table) {
            $table->id();
            $table->string('nom_complet');
            $table->string('grade')->nullable();
            $table->string('specialisation')->nullable();
            $table->foreignId('id_tribunal')->constrained('tribunaux');
            $table->timestamps();
        });

        // Structures organisationnelles
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('id_type_structure')->constrained('type_structures');
            $table->foreignId('id_parent')->nullable()->constrained('structures');
            $table->timestamps();
        });

        // Dossiers judiciaires
        Schema::create('dossier_judiciaires', function (Blueprint $table) {
            $table->id();
            $table->string('numero_dossier_interne')->unique();
            $table->string('numero_dossier_tribunal')->nullable();
            $table->foreignId('id_type_affaire')->constrained('type_affaires');
            $table->foreignId('id_statut_dossier')->constrained('statut_dossiers');
            $table->date('date_ouverture');
            $table->date('date_cloture')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes(); // crée la colonne deleted_at nullable
        });

        // Relation Dossier-Partie
        Schema::create('dossier_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier')->constrained('dossier_judiciaires');
            $table->foreignId('id_partie')->constrained('parties');
            $table->foreignId('id_type_partie')->constrained('type_parties');
            $table->foreignId('id_avocat')->nullable()->constrained('avocats');
            $table->boolean('est_institution')->default(false);
            $table->date('date_entree');
            $table->timestamps();
        });

        // Relation Dossier-Tribunal
        Schema::create('dossier_tribunaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier')->constrained('dossier_judiciaires');
            $table->foreignId('id_tribunal')->constrained('tribunaux');
            $table->foreignId('id_degre')->constrained('degre_juridictions');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->timestamps();
        });

        // Audiences
        Schema::create('audiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier_tribunal')->constrained('dossier_tribunaux');
            $table->foreignId('id_type_audience')->constrained('type_audiences');
            $table->foreignId('id_juge')->constrained('juges');
            $table->boolean('presence_demandeur');
            $table->boolean('presence_defendeur');
            $table->date('date_audience');
            $table->date('date_prochaine_audience')->nullable();
            $table->text('resultat_audience')->nullable();
            $table->text('actions_demandees')->nullable();
            $table->timestamps();
        });

        // Jugements
        Schema::create('jugements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier_tribunal')->constrained('dossier_tribunaux');
            $table->foreignId('id_juge')->constrained('juges');
            $table->date('date_jugement');
            $table->text('contenu_dispositif');
            $table->boolean('est_definitif')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Jugement-Partie
        Schema::create('jugement_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jugement')->constrained('jugements');
            $table->foreignId('id_partie')->constrained('parties');
            $table->foreignId('id_position_institution')->nullable()->constrained('position_institutions');
            $table->decimal('montant_condamne', 15, 2)->nullable();
            $table->timestamps();
        });

        // Recours
        Schema::create('recours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jugement')->constrained('jugements');
            $table->foreignId('id_dossier_tribunal')->constrained('dossier_tribunaux');
            $table->foreignId('id_dossier_recours')->constrained('dossier_judiciaires');
            $table->foreignId('id_type_recours')->constrained('type_recours');
            $table->date('date_recours');
            $table->text('motifs')->nullable();
            $table->timestamps();
        });

        // Exécutions
        Schema::create('executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jugement')->constrained('jugements');
            $table->string('numero_dossier_execution')->nullable();
            $table->date('date_notification')->nullable();
            $table->foreignId('statut_execution')->constrained('statut_executions');
            $table->date('date_execution')->nullable();
            $table->foreignId('responsable_id')->constrained('users');
            $table->timestamps();
        });

        // Finance
        Schema::create('finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jugement')->constrained('jugements');
            $table->decimal('montant_reclame_demandeur', 15, 2)->nullable();
            $table->decimal('montant_reclame_defendeur', 15, 2)->nullable();
            $table->decimal('montant_condamne', 15, 2)->nullable();
            $table->decimal('montant_paye', 15, 2)->default(0);
            $table->date('date_paiement')->nullable();
            $table->enum('statut_paiement', ['En attente', 'Partiel', 'Complet']);
            $table->timestamps();
        });

        // Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier')->constrained('dossier_judiciaires');
            $table->foreignId('id_type_document')->constrained('type_documents');
            $table->foreignId('id_partie')->nullable()->constrained('parties');
            $table->string('titre_document');
            $table->date('date_depot');
            $table->string('fichier_path')->nullable();
            $table->timestamps();
        });

        // Réclamants
        Schema::create('reclamants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('id_type_reclamant')->constrained('type_reclamants');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->timestamps();
        });

        // Réclamations
        Schema::create('reclamations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reclamant')->constrained('reclamants');
            $table->string('objet');
            $table->date('date_reception');
            $table->foreignId('id_statut_reclamation')->constrained('statut_reclamations');
            $table->text('details')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });

        // Actions réclamations
        Schema::create('action_reclamations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reclamation')->constrained('reclamations');
            $table->foreignId('id_type_action')->constrained('type_actions');
            $table->string('statut_action')->nullable();
            $table->date('date_action');
            $table->foreignId('id_structure')->nullable()->constrained('structures');
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Laravel standard
            $table->string('type'); // type de notification
            $table->morphs('notifiable'); // crée notifiable_id et notifiable_type
            $table->text('data'); // JSON ou texte de la notification
            $table->timestamp('read_at')->nullable(); // quand la notification a été lue
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('action_reclamations');
        Schema::dropIfExists('reclamations');
        Schema::dropIfExists('reclamants');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('finances');
        Schema::dropIfExists('executions');
        Schema::dropIfExists('recours');
        Schema::dropIfExists('jugement_parties');
        Schema::dropIfExists('jugements');
        Schema::dropIfExists('audiences');
        Schema::dropIfExists('dossier_tribunaux');
        Schema::dropIfExists('dossier_parties');
        Schema::dropIfExists('dossier_judiciaires');
        Schema::dropIfExists('structures');
        Schema::dropIfExists('juges');
        Schema::dropIfExists('parties');
        Schema::dropIfExists('avocats');
        Schema::dropIfExists('tribunaux');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};