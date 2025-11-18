<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';
    public $incrementing = false; // vì id không phải auto-increment
    protected $keyType = 'string'; // id là string

    protected $fillable = [
        'id',
        'shipment_id',
        'total_current',
        'total',
        'status',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id', 'id');
    }

    public function codeProducts()
    {
        return $this->hasMany(CodeProduct::class, 'document_id', 'id');
    }
}
