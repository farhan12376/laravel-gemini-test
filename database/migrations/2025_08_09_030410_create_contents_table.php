<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 50); // instagram_caption, facebook_post, etc
            $table->string('product_name', 100);
            $table->string('target_audience', 100);
            $table->string('tone', 30); // casual, professional, friendly
            $table->string('product_category', 50)->default('default');
            $table->text('generated_content');
            $table->integer('word_count')->default(0);
            $table->integer('char_count')->default(0);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['content_type', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contents');
    }
};