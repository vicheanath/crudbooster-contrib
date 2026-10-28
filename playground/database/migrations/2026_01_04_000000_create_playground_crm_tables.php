<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The CRM example: companies, contacts with tags, deals and activities. Managed by the
 * resources in app/Admin/Resources/Crm. Owners are CRUDBooster admins (cms_users).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->unsignedInteger('employees')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_company_id')->nullable()->constrained('crm_companies')->nullOnDelete();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->boolean('is_subscribed')->default(false);
            $table->timestamps();
        });

        Schema::create('crm_contact_tag', function (Blueprint $table) {
            $table->foreignId('crm_contact_id')->constrained('crm_contacts')->cascadeOnDelete();
            $table->foreignId('crm_tag_id')->constrained('crm_tags')->cascadeOnDelete();
            $table->primary(['crm_contact_id', 'crm_tag_id']);
        });

        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_company_id')->constrained('crm_companies');
            $table->foreignId('crm_contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('title');
            $table->string('stage')->default('lead');
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('probability')->default(10);
            $table->date('expected_close_on')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_deal_id')->nullable()->constrained('crm_deals')->cascadeOnDelete();
            $table->foreignId('crm_contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
            $table->string('type')->default('call');
            $table->string('subject');
            $table->dateTime('due_at')->nullable();
            $table->boolean('is_done')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_deals');
        Schema::dropIfExists('crm_contact_tag');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_tags');
        Schema::dropIfExists('crm_companies');
    }
};
