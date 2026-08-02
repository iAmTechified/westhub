<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent migration that ensures every column defined in the original
 * create_* migrations actually exists. Safe to run multiple times.
 * No ->after() calls — columns are appended if missing.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── users ────────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
        });

        // ── join_requests ────────────────────────────────────────────────────
        Schema::table('join_requests', function (Blueprint $table) {
            foreach ([
                'phone'          => fn() => $table->string('phone')->nullable(),
                'profession'     => fn() => $table->string('profession')->nullable()->index(),
                'about'          => fn() => $table->text('about')->nullable(),
                'qualifications' => fn() => $table->json('qualifications')->nullable(),
                'resume_path'    => fn() => $table->string('resume_path')->nullable(),
                'internal_notes' => fn() => $table->text('internal_notes')->nullable(),
                'status'         => fn() => $table->string('status', 32)->default('new')->index(),
                'reviewed_by'    => fn() => $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(),
                'reviewed_at'    => fn() => $table->timestamp('reviewed_at')->nullable(),
                'decided_at'     => fn() => $table->timestamp('decided_at')->nullable(),
            ] as $col => $add) {
                if (! Schema::hasColumn('join_requests', $col)) { $add(); }
            }
        });

        // ── outbound_messages ────────────────────────────────────────────────
        Schema::table('outbound_messages', function (Blueprint $table) {
            foreach ([
                'join_request_id'   => fn() => $table->foreignId('join_request_id')->nullable()->constrained()->nullOnDelete(),
                'recipient_email'   => fn() => $table->string('recipient_email')->index(),
                'template_key'      => fn() => $table->string('template_key'),
                'provider'          => fn() => $table->string('provider')->nullable(),
                'status'            => fn() => $table->string('status', 32)->default('queued')->index(),
                'subject'           => fn() => $table->string('subject')->nullable(),
                'body'              => fn() => $table->longText('body')->nullable(),
                'meta'              => fn() => $table->json('meta')->nullable(),
                'provider_response' => fn() => $table->json('provider_response')->nullable(),
                'sent_at'           => fn() => $table->timestamp('sent_at')->nullable(),
                'failed_at'         => fn() => $table->timestamp('failed_at')->nullable(),
            ] as $col => $add) {
                if (! Schema::hasColumn('outbound_messages', $col)) { $add(); }
            }
        });

        // ── gallery_categories ───────────────────────────────────────────────
        Schema::table('gallery_categories', function (Blueprint $table) {
            foreach ([
                'description' => fn() => $table->text('description')->nullable(),
                'is_active'   => fn() => $table->boolean('is_active')->default(true)->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('gallery_categories', $col)) { $add(); }
            }
        });

        // ── gallery_items ────────────────────────────────────────────────────
        Schema::table('gallery_items', function (Blueprint $table) {
            foreach ([
                'gallery_category_id' => fn() => $table->foreignId('gallery_category_id')->nullable()->constrained()->nullOnDelete(),
                'slug'                => fn() => $table->string('slug')->unique(),
                'alt_text'            => fn() => $table->string('alt_text')->nullable(),
                'caption'             => fn() => $table->text('caption')->nullable(),
                'visibility'          => fn() => $table->string('visibility', 32)->default('private')->index(),
                'status'              => fn() => $table->string('status', 32)->default('draft')->index(),
                'sort_order'          => fn() => $table->unsignedInteger('sort_order')->default(0)->index(),
                'published_at'        => fn() => $table->timestamp('published_at')->nullable(),
            ] as $col => $add) {
                if (! Schema::hasColumn('gallery_items', $col)) { $add(); }
            }
        });

        // ── counties ─────────────────────────────────────────────────────────
        Schema::table('counties', function (Blueprint $table) {
            foreach ([
                'slug'             => fn() => $table->string('slug')->unique(),
                'description'      => fn() => $table->text('description')->nullable(),
                'meta_title'       => fn() => $table->string('meta_title')->nullable(),
                'meta_description' => fn() => $table->text('meta_description')->nullable(),
                'is_active'        => fn() => $table->boolean('is_active')->default(true)->index(),
                'sort_order'       => fn() => $table->unsignedInteger('sort_order')->default(0)->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('counties', $col)) { $add(); }
            }
        });

        // ── townships ────────────────────────────────────────────────────────
        Schema::table('townships', function (Blueprint $table) {
            foreach ([
                'slug'             => fn() => $table->string('slug'),
                'content'          => fn() => $table->json('content')->nullable(),
                'meta_title'       => fn() => $table->string('meta_title')->nullable(),
                'meta_description' => fn() => $table->text('meta_description')->nullable(),
                'is_active'        => fn() => $table->boolean('is_active')->default(true)->index(),
                'sort_order'       => fn() => $table->unsignedInteger('sort_order')->default(0)->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('townships', $col)) { $add(); }
            }
        });

        // ── services ─────────────────────────────────────────────────────────
        Schema::table('services', function (Blueprint $table) {
            foreach ([
                'slug'             => fn() => $table->string('slug')->unique(),
                'tagline'          => fn() => $table->string('tagline')->nullable(),
                'description'      => fn() => $table->longText('description')->nullable(),
                'icon'             => fn() => $table->string('icon')->nullable(),
                'status'           => fn() => $table->string('status', 32)->default('draft')->index(),
                'meta_title'       => fn() => $table->string('meta_title')->nullable(),
                'meta_description' => fn() => $table->text('meta_description')->nullable(),
                'is_active'        => fn() => $table->boolean('is_active')->default(true)->index(),
                'sort_order'       => fn() => $table->unsignedInteger('sort_order')->default(0)->index(),
                'published_at'     => fn() => $table->timestamp('published_at')->nullable(),
            ] as $col => $add) {
                if (! Schema::hasColumn('services', $col)) { $add(); }
            }
        });

        // ── settings ─────────────────────────────────────────────────────────
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'group'        => fn() => $table->string('group', 64)->index(),
                'key'          => fn() => $table->string('key', 100),
                'value'        => fn() => $table->longText('value')->nullable(),
                'type'         => fn() => $table->string('type')->default('string'),
                'is_encrypted' => fn() => $table->boolean('is_encrypted')->default(false),
                'updated_by'   => fn() => $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(),
            ] as $col => $add) {
                if (! Schema::hasColumn('settings', $col)) { $add(); }
            }
        });

        // ── setting_audits ───────────────────────────────────────────────────
        Schema::table('setting_audits', function (Blueprint $table) {
            foreach ([
                'setting_id' => fn() => $table->foreignId('setting_id')->constrained()->cascadeOnDelete(),
                'actor_id'   => fn() => $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(),
                'action'     => fn() => $table->string('action')->default('updated'),
                'old_value'  => fn() => $table->longText('old_value')->nullable(),
                'new_value'  => fn() => $table->longText('new_value')->nullable(),
                'changed_at' => fn() => $table->timestamp('changed_at')->useCurrent()->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('setting_audits', $col)) { $add(); }
            }
        });

        // ── seo_metrics ──────────────────────────────────────────────────────
        Schema::table('seo_metrics', function (Blueprint $table) {
            foreach ([
                'entity_type' => fn() => $table->string('entity_type', 64)->index(),
                'entity_id'   => fn() => $table->unsignedBigInteger('entity_id')->index(),
                'metric_date' => fn() => $table->date('metric_date')->index(),
                'clicks'      => fn() => $table->unsignedInteger('clicks')->default(0),
                'impressions' => fn() => $table->unsignedInteger('impressions')->default(0),
                'ctr'         => fn() => $table->decimal('ctr', 5, 2)->default(0),
                'position'    => fn() => $table->decimal('position', 8, 2)->nullable(),
                'page_url'    => fn() => $table->string('page_url')->nullable(),
                'top_queries' => fn() => $table->json('top_queries')->nullable(),
                'source'      => fn() => $table->string('source', 32)->default('ga4'),
            ] as $col => $add) {
                if (! Schema::hasColumn('seo_metrics', $col)) { $add(); }
            }
        });

        // ── subscribers ──────────────────────────────────────────────────────
        Schema::table('subscribers', function (Blueprint $table) {
            foreach ([
                'full_name'       => fn() => $table->string('full_name')->nullable(),
                'source'          => fn() => $table->string('source', 32)->default('website')->index(),
                'status'          => fn() => $table->string('status', 32)->default('subscribed')->index(),
                'subscribed_at'   => fn() => $table->timestamp('subscribed_at')->nullable()->index(),
                'unsubscribed_at' => fn() => $table->timestamp('unsubscribed_at')->nullable()->index(),
                'meta'            => fn() => $table->json('meta')->nullable(),
            ] as $col => $add) {
                if (! Schema::hasColumn('subscribers', $col)) { $add(); }
            }
        });

        // ── care_service_groups ──────────────────────────────────────────────
        Schema::table('care_service_groups', function (Blueprint $table) {
            foreach ([
                'slug'         => fn() => $table->string('slug')->unique(),
                'description'  => fn() => $table->text('description')->nullable(),
                'status'       => fn() => $table->string('status', 32)->default('draft')->index(),
                'is_active'    => fn() => $table->boolean('is_active')->default(true)->index(),
                'sort_order'   => fn() => $table->unsignedInteger('sort_order')->default(0)->index(),
                'published_at' => fn() => $table->timestamp('published_at')->nullable()->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('care_service_groups', $col)) { $add(); }
            }
        });

        // ── care_service_items ───────────────────────────────────────────────
        Schema::table('care_service_items', function (Blueprint $table) {
            foreach ([
                'care_service_group_id' => fn() => $table->foreignId('care_service_group_id')->constrained()->cascadeOnDelete(),
                'service_id'            => fn() => $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete(),
                'slug'                  => fn() => $table->string('slug')->unique(),
                'subtitle'              => fn() => $table->string('subtitle')->nullable(),
                'description'           => fn() => $table->longText('description')->nullable(),
                'icon'                  => fn() => $table->string('icon')->nullable(),
                'status'                => fn() => $table->string('status', 32)->default('draft')->index(),
                'is_active'             => fn() => $table->boolean('is_active')->default(true)->index(),
                'sort_order'            => fn() => $table->unsignedInteger('sort_order')->default(0)->index(),
                'published_at'          => fn() => $table->timestamp('published_at')->nullable()->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('care_service_items', $col)) { $add(); }
            }
        });

        // ── appointments ─────────────────────────────────────────────────────
        Schema::table('appointments', function (Blueprint $table) {
            foreach ([
                'phone'          => fn() => $table->string('phone')->nullable(),
                'county_id'      => fn() => $table->foreignId('county_id')->nullable()->constrained()->nullOnDelete(),
                'township_id'    => fn() => $table->foreignId('township_id')->nullable()->constrained()->nullOnDelete(),
                'service_id'     => fn() => $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete(),
                'preferred_date' => fn() => $table->date('preferred_date')->nullable()->index(),
                'preferred_time' => fn() => $table->string('preferred_time')->nullable(),
                'message'        => fn() => $table->text('message')->nullable(),
                'source'         => fn() => $table->string('source', 32)->default('website')->index(),
                'status'         => fn() => $table->string('status', 32)->default('new')->index(),
                'scheduled_at'   => fn() => $table->timestamp('scheduled_at')->nullable()->index(),
                'assigned_to'    => fn() => $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(),
                'resolved_at'    => fn() => $table->timestamp('resolved_at')->nullable(),
                'meta'           => fn() => $table->json('meta')->nullable(),
            ] as $col => $add) {
                if (! Schema::hasColumn('appointments', $col)) { $add(); }
            }
        });

        // ── testimonials ─────────────────────────────────────────────────────
        Schema::table('testimonials', function (Blueprint $table) {
            foreach ([
                'author_role'  => fn() => $table->string('author_role')->nullable(),
                'quote'        => fn() => $table->text('quote'),
                'rating'       => fn() => $table->unsignedTinyInteger('rating')->default(5),
                'avatar_path'  => fn() => $table->string('avatar_path')->nullable(),
                'status'       => fn() => $table->string('status', 32)->default('draft')->index(),
                'is_featured'  => fn() => $table->boolean('is_featured')->default(false)->index(),
                'sort_order'   => fn() => $table->unsignedInteger('sort_order')->default(0)->index(),
                'published_at' => fn() => $table->timestamp('published_at')->nullable()->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('testimonials', $col)) { $add(); }
            }
        });

        // ── analytics_events ─────────────────────────────────────────────────
        Schema::table('analytics_events', function (Blueprint $table) {
            foreach ([
                'event_group' => fn() => $table->string('event_group')->nullable()->index(),
                'entity_type' => fn() => $table->string('entity_type')->nullable()->index(),
                'entity_id'   => fn() => $table->unsignedBigInteger('entity_id')->nullable()->index(),
                'user_id'     => fn() => $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(),
                'session_id'  => fn() => $table->string('session_id')->nullable()->index(),
                'ip_address'  => fn() => $table->ipAddress('ip_address')->nullable(),
                'user_agent'  => fn() => $table->text('user_agent')->nullable(),
                'payload'     => fn() => $table->json('payload')->nullable(),
                'occurred_at' => fn() => $table->timestamp('occurred_at')->useCurrent()->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('analytics_events', $col)) { $add(); }
            }
        });

        // ── appointment_events ───────────────────────────────────────────────
        Schema::table('appointment_events', function (Blueprint $table) {
            foreach ([
                'appointment_id' => fn() => $table->foreignId('appointment_id')->constrained()->cascadeOnDelete(),
                'actor_id'       => fn() => $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(),
                'event_type'     => fn() => $table->string('event_type'),
                'old_status'     => fn() => $table->string('old_status')->nullable(),
                'new_status'     => fn() => $table->string('new_status')->nullable(),
                'note'           => fn() => $table->text('note')->nullable(),
                'meta'           => fn() => $table->json('meta')->nullable(),
                'event_at'       => fn() => $table->timestamp('event_at')->useCurrent()->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('appointment_events', $col)) { $add(); }
            }
        });

        // ── join_request_events ──────────────────────────────────────────────
        Schema::table('join_request_events', function (Blueprint $table) {
            foreach ([
                'join_request_id' => fn() => $table->foreignId('join_request_id')->constrained()->cascadeOnDelete(),
                'actor_id'        => fn() => $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(),
                'event_type'      => fn() => $table->string('event_type'),
                'old_status'      => fn() => $table->string('old_status')->nullable(),
                'new_status'      => fn() => $table->string('new_status')->nullable(),
                'note'            => fn() => $table->text('note')->nullable(),
                'meta'            => fn() => $table->json('meta')->nullable(),
                'event_at'        => fn() => $table->timestamp('event_at')->useCurrent()->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('join_request_events', $col)) { $add(); }
            }
        });

        // ── article_categories ───────────────────────────────────────────────
        Schema::table('article_categories', function (Blueprint $table) {
            foreach ([
                'slug'        => fn() => $table->string('slug')->unique(),
                'description' => fn() => $table->text('description')->nullable(),
                'is_active'   => fn() => $table->boolean('is_active')->default(true)->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('article_categories', $col)) { $add(); }
            }
        });

        // ── article_revisions ────────────────────────────────────────────────
        Schema::table('article_revisions', function (Blueprint $table) {
            foreach ([
                'article_id'   => fn() => $table->unsignedBigInteger('article_id')->index(),
                'saved_by'     => fn() => $table->foreignId('saved_by')->nullable()->constrained('users')->nullOnDelete(),
                'version'      => fn() => $table->unsignedInteger('version'),
                'payload_json' => fn() => $table->json('payload_json'),
                'saved_at'     => fn() => $table->timestamp('saved_at')->useCurrent()->index(),
            ] as $col => $add) {
                if (! Schema::hasColumn('article_revisions', $col)) { $add(); }
            }
        });

        // ── admin_preferences ────────────────────────────────────────────────
        Schema::table('admin_preferences', function (Blueprint $table) {
            foreach ([
                'user_id'     => fn() => $table->foreignId('user_id')->constrained()->cascadeOnDelete(),
                'module'      => fn() => $table->string('module')->index(),
                'preferences' => fn() => $table->json('preferences'),
            ] as $col => $add) {
                if (! Schema::hasColumn('admin_preferences', $col)) { $add(); }
            }
        });
    }

    public function down(): void
    {
        // Intentionally left blank — column removal is destructive and
        // should be handled manually when rolling back a fresh install.
    }
};
