<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoDetalle extends Model
{
    protected $table = 'resultados_detalle';
    
    protected $fillable = [
        'partido_id',
        'equipo_id',
        'tiros',
        'tiros_a_puerta',
        'posesion',
        'corners',
        'faltas',
        'offsides',
        'tarjetas_amarillas',
        'tarjetas_rojas'
    ];

    protected $casts = [
        'tiros' => 'integer',
        'tiros_a_puerta' => 'integer',
        'posesion' => 'decimal:2',
        'corners' => 'integer',
        'faltas' => 'integer',
        'offsides' => 'integer',
        'tarjetas_amarillas' => 'integer',
        'tarjetas_rojas' => 'integer'
    ];

    // Relaciones
    public function partido(): BelongsTo
    {
        return $this->belongsTo(Partido::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    // Métodos útiles
    public function getEfectividadTirosAttribute(): float
    {
        if ($this->tiros === 0) return 0.0;
        return round(($this->tiros_a_puerta / $this->tiros) * 100, 2);
    }

    // Scopes
    public function scopePorPartido($query, $partidoId)
    {
        return $query->where('partido_id', $partidoId);
    }

    public function scopePorEquipo($query, $equipoId)
    {
        return $query->where('equipo_id', $equipoId);
    }
}