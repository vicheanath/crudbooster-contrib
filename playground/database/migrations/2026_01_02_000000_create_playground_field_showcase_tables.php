<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables for the Field Showcase module, which puts every CRUDBooster form field type on one form.
 * Lookups (select, select2, checkbox, datamodal) reuse the e-commerce tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_showcases', function (Blueprint $table) {
            $table->id();
            // Text inputs
            $table->string('title');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('aliases')->nullable();
            $table->string('reference')->nullable();
            // Date and time
            $table->date('birth_date')->nullable();
            $table->dateTime('meeting_at')->nullable();
            $table->time('alarm_time')->nullable();
            // Long text
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->longText('settings')->nullable();
            // Choices
            $table->string('status')->nullable();
            $table->unsignedTinyInteger('priority')->nullable();
            $table->string('color')->nullable();
            $table->foreignId('shop_category_id')->nullable()->constrained('shop_categories');
            $table->unsignedBigInteger('query_category_id')->nullable();
            $table->string('level')->nullable();
            $table->foreignId('shop_customer_id')->nullable()->constrained('shop_customers');
            $table->foreignId('shop_product_id')->nullable()->constrained('shop_products');
            $table->string('platforms')->nullable();
            $table->string('category_names')->nullable();
            // Files
            $table->string('cover_image')->nullable();
            $table->string('attachment')->nullable();
            // Location
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            // Custom HTML
            $table->string('rating')->nullable();
            $table->timestamps();
        });

        // Many-to-many pivots. CRUDBooster names pivot columns "{table}_id".
        Schema::create('field_showcase_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_showcases_id')->constrained('field_showcases')->cascadeOnDelete();
            $table->foreignId('shop_categories_id')->constrained('shop_categories')->cascadeOnDelete();
        });

        Schema::create('field_showcase_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_showcases_id')->constrained('field_showcases')->cascadeOnDelete();
            $table->foreignId('shop_customers_id')->constrained('shop_customers')->cascadeOnDelete();
        });

        // Rows of the "child" field.
        Schema::create('field_showcase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_showcase_id')->constrained('field_showcases')->cascadeOnDelete();
            $table->string('name');
            $table->text('note')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('size')->nullable();
            $table->string('kind')->nullable();
            $table->foreignId('shop_product_id')->nullable()->constrained('shop_products');
            $table->foreignId('shop_category_id')->nullable()->constrained('shop_categories');
            $table->string('photo')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_showcase_items');
        Schema::dropIfExists('field_showcase_customers');
        Schema::dropIfExists('field_showcase_categories');
        Schema::dropIfExists('field_showcases');
    }
};
