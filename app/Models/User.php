<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone',
        'password',
        'role',
    ];

    public function codeProductTemps()
    {
        return $this->hasMany(CodeProductTemp::class, 'user_id', 'id');
    }

    public function codeProducts()
    {
        return $this->hasMany(CodeProduct::class, 'user_id', 'id');
    }
}
