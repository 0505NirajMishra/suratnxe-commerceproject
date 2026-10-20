<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('phone', 20);
            $t->string('line1');
            $t->string('line2')->nullable();
            $t->string('landmark')->nullable();
            $t->string('city');
            $t->string('state');
            $t->string('pin', 10);
            $t->string('type', 20)->default('Home');
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'product_id']);
        });

        Schema::create('enquiries', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('phone', 20)->nullable();
            $t->string('subject');
            $t->text('message');
            $t->string('status', 10)->default('New')->index(); // New | Replied
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('addresses');
    }
};
