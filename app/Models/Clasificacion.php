<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clasificacion extends Model
{
    protected $table = 'clasificacion';
    
    protected $fillable = [
        'temporada_id',
        'equipo_id',
        'partidos_jugados',
        'partidos_ganados',
        'partidos_empatados',
        'partidos_perdidos',
        'goles_a_favor',
        'goles_en_contra',
        'diferencia_goles',
        'puntos',
        'posicion'
    ];

    protected $casts = [
        'partidos_jugados' => 'integer',
        'partidos_ganados' => 'integer',
        'partidos_empatados' => 'integer',
        'partidos_perdidos' => 'integer',
        'goles_a_favor' => 'integer',
        'goles_en_contra' => 'integer',
        'diferencia_goles' => 'integer',
        'puntos' => 'integer',
        'posicion' => 'integer'
    ];

    // Relaciones
    public function temporada(): BelongsTo
    {
        return $this->belongsTo(Temporada::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    // Métodos útiles
    public function getRendimientoAttribute(): float
    {
        if ($this->partidos_jugados === 0) return 0.0;
        return round(($this->puntos / ($this->partidos_jugados * 3)) * 100, 2);
    }

    public function getPromedioGolesFavorAttribute(): float
    {
        if ($this->partidos_jugados === 0) return 0.0;
        return round($this->goles_a_favor / $this->partidos_jugados, 2);
    }

    public function getPromedioGolesContraAttribute(): float
    {
        if ($this->partidos_jugados === 0) return 0.0;
        return round($this->goles_en_contra / $this->partidos_jugados, 2);
    }

    public function getPartidosSinGanarAttribute(): int
    {
        return $this->partidos_empatados + $this->partidos_perdidos;
    }

    // Scopes
    public function scopePorTemporada($query, $temporadaId)
    {
        return $query->where('temporada_id', $temporadaId);
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('puntos', 'DESC')
                    ->orderBy('diferencia_goles', 'DESC')
                    ->orderBy('goles_a_favor', 'DESC')
                    ->orderBy('goles_en_contra', 'ASC');
    }

    public function scopeTop($query, $limit = 5)
    {
        return $query->ordenado()->limit($limit);
    }
}