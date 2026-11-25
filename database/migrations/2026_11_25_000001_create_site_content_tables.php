<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Content for the public site. Translatable columns are JSON objects keyed by
 * locale: {"es": "...", "en": "..."}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        Schema::create('site_texts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->index();
            $table->string('label');
            $table->json('value');
            $table->boolean('multiline')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('work'); // work | education
            $table->string('organization');
            $table->json('role');
            $table->json('description')->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->boolean('is_current')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('icon');
            $table->json('title');
            $table->json('summary');
            $table->json('items')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->index();
            $table->string('logo_path')->nullable();
            $table->string('brand_color', 7)->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->json('label');
            $table->string('target'); // a section key (profile, services, ...) or an absolute URL
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->json('label');
            $table->string('url');
            $table->string('icon')->default('link');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->json('subject');
            $table->json('body');
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_label');
            $table->text('message');
            $table->string('locale', 2);
            $table->string('status')->default('new')->index();
            $table->text('notes')->nullable();
            $table->decimal('recaptcha_score', 3, 2)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent')->nullable();
            // Each email is tracked on its own, so a retry only resends what failed.
            $table->string('ack_status')->default('pending');
            $table->timestamp('ack_sent_at')->nullable();
            $table->text('ack_error')->nullable();
            $table->string('notify_status')->default('pending');
            $table->timestamp('notify_sent_at')->nullable();
            $table->text('notify_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['leads', 'mail_templates', 'links', 'nav_items', 'technologies', 'services', 'experiences', 'site_texts', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
