<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PlansMetadata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->mediumText('metadata')->after('duration')->nullable();
        });

        Schema::table('plans_features', function (Blueprint $table) {
            $table->mediumText('metadata')->after('limit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });

        Schema::table('plans_features', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
}
