<?php
// database/migrations/2024_01_01_000001_create_type_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Types d'affaires
        Schema::create('type_affaires', function (Blueprint $table) {
            $table->id();
            $table->string('affaire')->unique();
            $table->timestamps();
        });

        // Statuts dossier
        Schema::create('statut_dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('statut_dossier')->unique();
            $table->timestamps();
        });

        // Types de parties
        Schema::create('type_parties', function (Blueprint $table) {
            $table->id();
            $table->string('type_partie');
            $table->timestamps();
        });

        // Types de tribunaux
        Schema::create('type_tribunaux', function (Blueprint $table) {
            $table->id();
            $table->string('tribunal');
            $table->timestamps();
        });

        // Degrés de juridiction
        Schema::create('degre_juridictions', function (Blueprint $table) {
            $table->id();
            $table->string('degre_juridiction');
            $table->timestamps();
        });

        // Types d'audience
        Schema::create('type_audiences', function (Blueprint $table) {
            $table->id();
            $table->string('type_audience');
            $table->timestamps();
        });

        // Positions institution
        Schema::create('position_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('position');
            $table->timestamps();
        });

        // Types de recours
        Schema::create('type_recours', function (Blueprint $table) {
            $table->id();
            $table->string('type_recours');
            $table->integer('delai_legal_jours');
            $table->timestamps();
        });

        // Statuts exécution
        Schema::create('statut_executions', function (Blueprint $table) {
            $table->id();
            $table->string('statut_execution');
            $table->timestamps();
        });

        // Types documents
        Schema::create('type_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type_document');
            $table->timestamps();
        });

        // Statuts réclamation
        Schema::create('statut_reclamations', function (Blueprint $table) {
            $table->id();
            $table->string('statut_reclamation');
            $table->timestamps();
        });

        // Types réclamant
        Schema::create('type_reclamants', function (Blueprint $table) {
            $table->id();
            $table->string('type_reclamant');
            $table->timestamps();
        });

        // Types actions
        Schema::create('type_actions', function (Blueprint $table) {
            $table->id();
            $table->string('type_action');
            $table->timestamps();
        });

        // Types structures
        Schema::create('type_structures', function (Blueprint $table) {
            $table->id();
            $table->string('type_structure');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('type_affaires');
        Schema::dropIfExists('statut_dossiers');
        Schema::dropIfExists('type_parties');
        Schema::dropIfExists('type_tribunaux');
        Schema::dropIfExists('degre_juridictions');
        Schema::dropIfExists('type_audiences');
        Schema::dropIfExists('position_institutions');
        Schema::dropIfExists('type_recours');
        Schema::dropIfExists('statut_executions');
        Schema::dropIfExists('type_documents');
        Schema::dropIfExists('statut_reclamations');
        Schema::dropIfExists('type_reclamants');
        Schema::dropIfExists('type_actions');
        Schema::dropIfExists('type_structures');
    }
};