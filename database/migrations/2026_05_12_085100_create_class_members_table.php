<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('member_role');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'user_id', 'member_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_members');
    }
};
