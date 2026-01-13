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
        Schema::create('reservation_update_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained()
                ->cascadeOnDelete();

            // التواريخ الجديدة
            $table->date('new_check_in');
            $table->date('new_check_out');

            // التفاصيل الجديدة
            $table->unsignedTinyInteger('new_adults_count');
            $table->unsignedTinyInteger('new_children_count')->default(0);
            $table->unsignedInteger('new_days_count');
            $table->decimal('new_total_cost', 10, 2);

            // حالة الطلب
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_update_requests');
    }
};
