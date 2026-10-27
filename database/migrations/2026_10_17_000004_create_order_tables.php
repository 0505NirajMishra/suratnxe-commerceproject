<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_no')->unique();                       // SNX-77291
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // customer + address SNAPSHOT (order history must not change if profile changes)
            $t->string('name');
            $t->string('email')->index();
            $t->string('phone', 20);
            $t->json('address');
            // payment
            $t->string('payment_method', 20);                       // UPI | Card | COD | Net Banking
            $t->string('payment_status', 10)->default('Pending');   // Pending | Paid | COD | Failed | Refunded
            $t->string('txn_id')->nullable();
            $t->string('gateway_order_id')->nullable();             // PayU transaction id
            // amounts
            $t->decimal('subtotal', 10, 2);
            $t->decimal('discount', 10, 2)->default(0);
            $t->string('coupon_code')->nullable();
            $t->decimal('shipping', 10, 2)->default(0);
            $t->decimal('total', 10, 2);
            $t->boolean('express')->default(false);
            // fulfilment
            $t->string('status', 20)->default('Pending')->index();
            $t->string('courier')->nullable();
            $t->string('tracking_no')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('sku');
            $t->decimal('price', 10, 2);
            $t->unsignedInteger('qty');                              // metres
            $t->json('art')->nullable();
            $t->string('image')->nullable();
        });

        Schema::create('order_timelines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('status', 20);
            $t->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_timelines');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
