<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeProduct extends Model
{
    use HasFactory;

    protected $table = 'code_products';
    public $incrementing = false; // vì id không phải auto-increment
    protected $keyType = 'string'; // id là string
    public $timestamps = false;

    protected $fillable = [
        'id',
        'shipment_id',
        'document_id',
        'scan',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id', 'id');
    }
}
