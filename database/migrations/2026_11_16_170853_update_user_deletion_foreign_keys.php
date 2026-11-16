<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('hero_mission', function (Blueprint $table) {
            $table->dropForeign(['hero_id']);

            $table->foreign('hero_id')
                ->references('id')
                ->on('heroes')
                ->cascadeOnDelete();
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->change();
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('quests')->whereNull('created_by')->exists()) {
            throw new \RuntimeException(
                'Cannot roll back this migration after quest creators have been deleted.'
            );
        }

        Schema::table('heroes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('hero_mission', function (Blueprint $table) {
            $table->dropForeign(['hero_id']);

            $table->foreign('hero_id')
                ->references('id')
                ->on('heroes')
                ->restrictOnDelete();
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')
                ->nullable(false)
                ->change();
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};