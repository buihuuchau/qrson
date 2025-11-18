<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'shipments';

    public function documents()
    {
        return $this->hasMany(Document::class, 'shipment_id', 'id');
    }

    public function codeProducts()
    {
        return $this->hasManyThrough(CodeProduct::class, Document::class, 'shipment_id', 'document_id', 'id', 'id');
    }
}
