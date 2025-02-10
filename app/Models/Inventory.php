<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'item_code', 'item_description', 'item_qty', 'item_price', 'created_by', 'updated_by', 'deleted_by'
    ];

}
