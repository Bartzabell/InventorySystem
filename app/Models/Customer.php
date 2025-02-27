<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_name', 'contact_person', 'phone_number', 'email', 'tin_no', 'address', 'created_by', 'updated_by'
    ];

    public function createdBy(){
        $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(){
        $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
