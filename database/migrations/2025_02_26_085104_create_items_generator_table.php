<?php

use App\Models\RequestForm;
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
        Schema::create('items_generator', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RequestForm::class, 'request_form_id')->nullable();
            $table->string('item_code');
            $table->string('item_description');
            $table->string('category');
            $table->string('type');
            $table->integer('first_dimention')->nullable();
            $table->string('first_unit')->nullable();
            $table->integer('second_dimention')->nullable();
            $table->string('second_unit')->nullable();
            $table->integer('third_dimention')->nullable();
            $table->string('third_unit')->nullable();
            $table->integer('fourth_dimention')->nullable();
            $table->string('fourth_unit')->nullable();
            $table->integer('weight')->nullable();
            $table->string('weight_unit')->nullable();
            $table->string('laminate')->nullable();
            $table->string('color')->nullable();
            $table->string('uom');
            $table->integer('part_no')->nullable();
            $table->string('part_name')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items_generator');
    }
};
