<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('service_id');
            $table->string('service_name');
            $table->integer('price_at_booking');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('session');
            $table->integer('total_price');
            $table->integer('units');
            $table->enum('status', ['pending', 'paid', 'processing', 'canceled', 'finished'])->default('pending');
            $table->string('snap_token')->nullable();
            $table->timestamp('snap_token_expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

    }
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};