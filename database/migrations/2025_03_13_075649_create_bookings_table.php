<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_id')->unique()->nullable();
            $table->date('date');
            $table->string('service');
            $table->integer('session');
            $table->integer('total_price');
            $table->string('status')->default('pending');
            $table->string('snap_token')->nullable();
            $table->timestamp('snap_token_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};