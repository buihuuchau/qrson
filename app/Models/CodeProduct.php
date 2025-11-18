<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'code_products';

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id', 'id');
    }
}
