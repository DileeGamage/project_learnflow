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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('subject_area')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->json('tags')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->string('pdf_path')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->boolean('is_pdf_note')->default(false);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
