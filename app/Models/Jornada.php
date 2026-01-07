<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jornada extends Model
{
    protected $table = 'jornadas';
    
    protected $fillable = [
        'temporada_id',
        'numero',
        'nombre',
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'numero' => 'integer'
    ];

    // Relaciones
    public function temporada(): BelongsTo
    {
        return $this->belongsTo(Temporada::class);
    }

    public function partidos(): HasMany
    {
        return $this->hasMany(Partido::class);
    }

    public function historialPosiciones(): HasMany
    {
        return $this->hasMany(HistorialPosicion::class);
    }

    // Métodos útiles
    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre ?: "Jornada {$this->numero}";
    }

    public function getPartidosJugadosAttribute()
    {
        return $this->partidos()->where('estado', 'Finalizado')->count();
    }

    public function getPartidosPendientesAttribute()
    {
        return $this->partidos()->where('estado', 'Programado')->count();
    }

    public function getEstaCompletaAttribute(): bool
    {
        return $this->partidos()->where('estado', '!=', 'Finalizado')->count() === 0;
    }

    // Scopes
    public function scopePorTemporada($query, $temporadaId)
    {
        return $query->where('temporada_id', $temporadaId);
    }

    public function scopePorNumero($query, $numero)
    {
        return $query->where('numero', $numero);
    }

    public function scopeCompletas($query)
    {
        return $query->whereHas('partidos', function($q) {
            $q->where('estado', 'Finalizado');
        }, '=', \DB::raw('(SELECT COUNT(*) FROM partidos WHERE jornada_id = jornadas.id)'));
    }
}