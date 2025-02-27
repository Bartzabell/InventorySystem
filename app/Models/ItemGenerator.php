<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemGenerator extends Model
{
    protected $fillable = [
        'request_form_id', 'item_code', 'item_description', 'category', 'type', 'first_dimension',
        'first_unit', 'second_dimension', 'second_unit', 'third_dimension', 'third_unit', 'fourth_dimension',
        'fourth_unit', 'weight', 'weight_unit', 'laminate', 'color', 'uom', 'part_no', 'part_name', 'status',
        'created_by', 'updated_by'
    ];

    public function requestForm()
    {
        $this->belongsTo(RequestForm::class, 'request_form_id', 'id');
    }

    public function createdBy(){
        $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(){
        $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
