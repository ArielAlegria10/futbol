<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{
    protected $table = 'equipos';
    
    protected $fillable = [
        'nombre',
        'abreviacion',
        'ciudad',
        'estadio',
        'fundacion',
        'escudo_url',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fundacion' => 'integer'
    ];

    // Relaciones
    public function partidosLocal(): HasMany
    {
        return $this->hasMany(Partido::class, 'equipo_local_id');
    }

    public function partidosVisitante(): HasMany
    {
        return $this->hasMany(Partido::class, 'equipo_visitante_id');
    }

    public function clasificaciones(): HasMany
    {
        return $this->hasMany(Clasificacion::class);
    }

    public function resultadosDetalle(): HasMany
    {
        return $this->hasMany(ResultadoDetalle::class);
    }

    public function historialPosiciones(): HasMany
    {
        return $this->hasMany(HistorialPosicion::class);
    }

    // Métodos útiles
    public function getPartidosAttribute()
    {
        return $this->partidosLocal->merge($this->partidosVisitante);
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre . ($this->abreviacion ? " ({$this->abreviacion})" : '');
    }

    public function getEstadioCompletoAttribute(): string
    {
        return $this->estadio ? "{$this->estadio}, {$this->ciudad}" : $this->ciudad;
    }

    public function getEdadAttribute(): int
    {
        return date('Y') - $this->fundacion;
    }

    // Scope para equipos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCiudad($query, $ciudad)
    {
        return $query->where('ciudad', $ciudad);
    }
}