<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $table = 'shipments';
    public $incrementing = false; // vì id không phải auto-increment
    protected $keyType = 'string'; // id là string

    protected $fillable = [
        'id',
        'status',
    ];

    public function document()
    {
        return $this->hasMany(Document::class, 'shipment_id', 'id');
    }

    public function codeProduct()
    {
        return $this->hasManyThrough(CodeProduct::class, Document::class, 'shipment_id', 'document_id', 'id', 'id');
    }

    public function codeProductTemp()
    {
        return $this->hasManyThrough(CodeProductTemp::class, Document::class, 'shipment_id', 'document_id', 'id', 'id');
    }
}
