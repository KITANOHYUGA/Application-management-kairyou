<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();;
            $table->bigInteger('user_id')->unsigned()->index();
            $table->string('name', 100);
            $table->integer('price')->nullable();
            $table->integer('stock');
            $table->text('comment')->nullable();
            $table->timestamps();

            // 外部キー制約を追加
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');

    }
};
