<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date');
            $table->unsignedInteger('quota')->default(0);
            $table->unsignedInteger('sold')->default(0);
            $table->timestamps();

            $table->unique(['ticket_type_id', 'visit_date']);
            $table->index('visit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_availability');
    }
};
