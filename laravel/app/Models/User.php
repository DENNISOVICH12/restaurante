<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'correo',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public $timestamps = false;

    public function getEmailAttribute(): ?string
    {
        return $this->correo;
    }

    public function setEmailAttribute(?string $value): void
    {
        $this->correo = $value;
    }

    public function getNameAttribute(): ?string
    {
        return $this->nombre;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->nombre = $value;
    }
}
