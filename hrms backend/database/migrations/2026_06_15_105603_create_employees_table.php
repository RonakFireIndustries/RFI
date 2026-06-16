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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('emp_id')->unique();
            $table->string('full_name');
            $table->string('photo_path')->nullable();
            
            // Dates
            $table->date('interview_date')->nullable();
            $table->date('joining_date');
            $table->date('probation_end_date')->nullable();
            $table->date('dob')->nullable();
            
            // Work Info
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('designation_id')->nullable()->constrained()->onDelete('set null');
            $table->string('employment_type')->default('Full-Time'); // Full-Time, Contract, etc.
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->onDelete('set null');
            
            // Personal Info
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('marital_status')->nullable();
            $table->string('government_id_type')->nullable(); // Aadhaar, PAN, etc.
            $table->text('address')->nullable();
            
            // Contact
            $table->string('contact_number')->nullable();
            $table->string('personal_number')->nullable();
            $table->string('emergency_contact')->nullable();
            
            // Qualifications & Statuses
            $table->string('qualification')->nullable();
            $table->boolean('employment_bond_status')->default(false);
            $table->boolean('previous_termination_status')->default(false);
            $table->boolean('legal_proceedings_status')->default(false);
            
            // Document Paths
            $table->string('resume_path')->nullable();
            $table->string('aadhaar_path')->nullable();
            $table->string('pan_path')->nullable();
            $table->string('offer_letter_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
