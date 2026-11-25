<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La table "users" existe déjà dans la base de production (comptes admin,
 * pros immobilier, visiteurs). Cette migration ne la crée que sur une base
 * vierge et se contente sinon d'ajouter les colonnes manquantes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->integer('role_id')->nullable();
                $table->integer('entreprise_id')->nullable();
                $table->string('workstation', 100)->nullable();
                $table->string('CATEGORIE', 100)->nullable();
                $table->string('agent_tel', 50)->nullable();
                $table->string('agent_cel', 50)->nullable();
                $table->string('agent_facebook', 1000)->nullable();
                $table->string('agent_twitter', 1000)->nullable();
                $table->string('agent_instagram', 1000)->nullable();
                $table->string('agent_youtube', 1000)->nullable();
                $table->integer('active')->nullable();
                $table->string('titre_menu', 100)->nullable();
                $table->string('image', 100)->nullable();
                $table->date('date_menu')->nullable();
                $table->string('user_created', 25)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down(): void
    {
        // On ne supprime jamais la table "users" : elle contient les comptes de production.
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
