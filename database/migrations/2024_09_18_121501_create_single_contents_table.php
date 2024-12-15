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
        Schema::create('single_contents', function (Blueprint $table) {
            $table->id();
            $table->json('main_page')->nullable();
            $table->json('products')->nullable();
            $table->json('credit_assessment')->nullable();
            $table->json('blogs')->nullable();
            $table->json('webinars')->nullable();
            $table->json('faqs')->nullable();
            $table->json('about_us')->nullable();
            $table->json('careers')->nullable();
            $table->json('contact_us')->nullable();
            $table->json('refer_earn')->nullable();
            $table->json('become_partner')->nullable();
            $table->json('after_login')->nullable();
            $table->json('consultation')->nullable();
            $table->json('complete_kyc')->nullable();
            $table->json('schemes')->nullable();
            $table->json('scheme_details1')->nullable();
            $table->json('scheme_details2')->nullable();
            $table->json('dashboard')->nullable();
            $table->json('profile')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('single_contents');
    }
};
