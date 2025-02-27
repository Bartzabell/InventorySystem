<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationForm extends Model
{
    protected $fillable = [
        'customer_id', 'reference_no', 'date', 'payment_term', 'created_by', 'updated_by'
    ];

    public function customer(){
        $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function createdBy(){
        $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(){
        $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
