<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationProduct extends Model
{
    protected $fillable = [
    'quotation_form_id', 'product_name', 'product_description', 'product_qty', 'price', 'remarks', 'created_by', 'updated_by'
    ];

    public function quotationForm(){
        $this->belongsTo(QuotationForm::class, 'quotation_form_id', 'id');
    }

    public function createdBy(){
        $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(){
        $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
