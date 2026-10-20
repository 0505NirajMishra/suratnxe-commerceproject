<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('name');
            $t->string('code', 10)->unique();      // SKU prefix e.g. ITA
            $t->text('description')->nullable();
            $t->string('image')->nullable();        // uploaded image path (optional)
            $t->json('art')->nullable();            // generated placeholder pattern {p,c,s}
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('sku')->unique();
            $t->decimal('price', 10, 2);            // per metre
            $t->decimal('mrp', 10, 2)->nullable();
            $t->unsignedInteger('stock')->default(0);
            $t->string('width', 30)->nullable();
            $t->string('fabric')->nullable();
            $t->string('usage')->nullable();
            $t->string('care')->nullable();
            $t->string('colors')->nullable();
            $t->text('description')->nullable();
            $t->json('tags')->nullable();           // ["new","trending","best"]
            $t->json('art')->nullable();
            $t->string('status', 10)->default('active')->index(); // active | draft
            $t->decimal('rating', 2, 1)->default(0);
            $t->unsignedInteger('reviews_count')->default(0);
            $t->timestamps();
            $t->index(['category_id', 'status']);
        });

        Schema::create('product_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('type', 10);                 // percent | flat
            $t->decimal('value', 10, 2);
            $t->decimal('max_discount', 10, 2)->default(0);
            $t->decimal('min_order', 10, 2)->default(0);
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
