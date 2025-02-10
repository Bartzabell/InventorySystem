<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'item_qty', 'amount_sold', 'customer', 'created_by', 'updated_by', 'deleted_by'
    ];

    public function createdBy(){
        return $this->belongsTo(Inventory::class, 'item_id', 'id');
    }
}
