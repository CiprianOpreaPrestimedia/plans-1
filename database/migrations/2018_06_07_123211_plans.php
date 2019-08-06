<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Plans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->text('description')->nullable();

            $table->float('price', 8, 2);
            $table->string('currency');

            $table->integer('duration')->default(30);

            $table->timestamps();
        });

        Schema::create('plans_features', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('plan_id');

            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();

            $table->enum('type', ['feature', 'limit'])->default('feature');
            $table->integer('limit')->default(0);

            $table->timestamps();
        });

        Schema::create('plans_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('plan_id');

            $table->integer('model_id');
            $table->string('model_type');

            $table->enum('payment_method', ['stripe'])->nullable()->default(null);
            $table->boolean('is_paid')->default(false);

            $table->float('charging_price', 8, 2)->nullable();
            $table->string('charging_currency')->nullable();

            $table->boolean('is_recurring')->default(true);
            $table->integer('recurring_each_days')->default(30);

            $table->timestamp('starts_on')->nullable();
            $table->timestamp('expires_on')->nullable();
            $table->timestamp('cancelled_on')->nullable();

            $table->timestamps();
        });

        Schema::create('plans_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subscription_id');

            $table->string('code');
            $table->float('used', 9, 2)->default(0);

            $table->timestamps();
        });

        Schema::create('stripe_customers', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('model_id');
            $table->string('model_type');

            $table->string('customer_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('plans_features');
        Schema::dropIfExists('plans_subscriptions');
        Schema::dropIfExists('plans_usages');
        Schema::dropIfExists('stripe_customers');
    }
}
