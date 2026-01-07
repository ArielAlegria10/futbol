<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Temporada extends Model
{
    protected $table = 'temporadas';
    
    protected $fillable = [
        'nombre',
        'anio',
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'anio' => 'integer'
    ];

    // Relaciones
    public function jornadas(): HasMany
    {
        return $this->hasMany(Jornada::class);
    }

    public function partidos(): HasMany
    {
        return $this->hasMany(Partido::class);
    }

    public function clasificaciones(): HasMany
    {
        return $this->hasMany(Clasificacion::class);
    }

    public function enfrentamientosDirectos(): HasMany
    {
        return $this->hasMany(EnfrentamientoDirecto::class);
    }

    // Métodos útiles
    public function getTemporadaCompletaAttribute(): string
    {
        return "{$this->nombre} ({$this->anio})";
    }

    public function getDuracionAttribute(): int
    {
        return $this->fecha_inicio->diffInDays($this->fecha_fin);
    }

    public function getEstaEnCursoAttribute(): bool
    {
        return $this->estado === 'En Curso';
    }

    public function getEstaFinalizadaAttribute(): bool
    {
        return $this->estado === 'Finalizada';
    }

    // Scopes
    public function scopeEnCurso($query)
    {
        return $query->where('estado', 'En Curso');
    }

    public function scopeFinalizadas($query)
    {
        return $query->where('estado', 'Finalizada');
    }

    public function scopePorAnio($query, $anio)
    {
        return $query->where('anio', $anio);
    }

    // Relación con la temporada actual
    public static function actual(): ?self
    {
        return static::enCurso()->first();
    }
}