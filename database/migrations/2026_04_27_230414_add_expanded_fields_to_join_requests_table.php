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
        Schema::table('join_requests', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('first_name')->nullable()->after('title');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('middle_initial', 10)->nullable()->after('last_name');
            $table->string('professional_type')->nullable()->after('phone'); // skilled/non-skilled
            $table->text('home_address')->nullable()->after('professional_type');
            $table->string('position_applied_for')->nullable()->after('home_address');
            $table->date('date_available')->nullable()->after('position_applied_for');
            $table->string('desired_salary')->nullable()->after('date_available');
            
            // Eligibility & Work Status
            $table->boolean('is_citizen')->default(false)->after('desired_salary');
            $table->boolean('has_felony')->default(false)->after('is_citizen');
            $table->text('felony_explanation')->nullable()->after('has_felony');
            $table->boolean('is_authorized')->default(false)->after('felony_explanation');
            $table->boolean('worked_here_before')->default(false)->after('is_authorized');
            $table->string('worked_here_before_when')->nullable()->after('worked_here_before');
            $table->string('ssn_tin')->nullable()->after('worked_here_before_when');
            $table->date('dob')->nullable()->after('ssn_tin');

            // Complex data
            $table->json('education')->nullable()->after('dob');
            $table->json('military_service')->nullable()->after('education');
            $table->json('work_experience')->nullable()->after('military_service');
            $table->json('references')->nullable()->after('work_experience');
            
            // Credentials
            $table->string('signature_path')->nullable()->after('resume_path');
            $table->boolean('disclaimer_accepted')->default(false)->after('signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('join_requests', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'first_name', 'last_name', 'middle_initial',
                'professional_type', 'home_address', 'position_applied_for',
                'date_available', 'desired_salary',
                'is_citizen', 'has_felony', 'felony_explanation',
                'is_authorized', 'worked_here_before', 'worked_here_before_when',
                'ssn_tin', 'dob',
                'education', 'military_service', 'work_experience', 'references',
                'signature_path', 'disclaimer_accepted'
            ]);
        });
    }
};
