<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestForm extends Model
{
    protected $fillable = [
        'quotation_form_id', 'requestor', 'product_type', 'customer', 'status', 'remarks', 'requested_by', 'created_by', 'updated_by'
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

    public function requestedBy(){
        $this->belongsTo(User::class, 'requested_by', 'id');
    }
}
