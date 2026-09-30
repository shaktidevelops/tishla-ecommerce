<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_values', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('master_type', 40);
            $t->string('name', 120);
            $t->string('slug', 140);
            $t->text('description')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['master_type', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_values');
    }
};