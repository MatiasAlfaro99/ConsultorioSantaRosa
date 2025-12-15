<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rut',
        'cargo',
        'telefono',
        'jefe_id',
        'role',
        'tipo_contrato',
        'departamento',
        'lugar_trabajo',
        'profile_photo_path',
        'is_active',
        // Nuevos campos para Feriado Legal y Administrativos
        'vacaciones_total',
        'vacaciones_usadas',
        'dias_admin_usados',
        'region_sur',
        'jornada_sabado'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'region_sur' => 'boolean',
        'jornada_sabado' => 'boolean',
    ];

    // Relación: Un usuario tiene un jefe
    public function jefe()
    {
        return $this->belongsTo(User::class, 'jefe_id');
    }

    // Relación: Un usuario (jefe) tiene varios subordinados
    public function subordinados()
    {
        return $this->hasMany(User::class, 'jefe_id');
    }

    // Accessor para la URL de la foto
    protected $appends = ['profile_photo_url', 'dias_vacaciones_restantes', 'dias_admin_restantes'];

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    public function getDiasVacacionesRestantesAttribute()
    {
        // Si no se ha definido un total, asumimos el legal base de 15, o 20 si es zona extrema
        $total = $this->vacaciones_total ?? ($this->region_sur ? 20 : 15);
        return max(0, $total - ($this->vacaciones_usadas ?? 0));
    }

    public function getDiasAdminRestantesAttribute()
    {
        // Legalmente son 6 días administrativos
        $total = 6; 
        return max(0, $total - ($this->dias_admin_usados ?? 0));
    }
}
