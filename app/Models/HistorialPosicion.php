<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPosicion extends Model
{
    protected $table = 'historial_posiciones';
    
    protected $fillable = [
        'temporada_id',
        'jornada_id',
        'equipo_id',
        'posicion',
        'puntos',
        'partidos_jugados'
    ];

    protected $casts = [
        'posicion' => 'integer',
        'puntos' => 'integer',
        'partidos_jugados' => 'integer'
    ];

    // Relaciones
    public function temporada(): BelongsTo
    {
        return $this->belongsTo(Temporada::class);
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    // Métodos útiles
    public function getVariacionAttribute($jornadaAnterior): int
    {
        $posicionAnterior = static::where('equipo_id', $this->equipo_id)
            ->where('temporada_id', $this->temporada_id)
            ->where('jornada_id', $jornadaAnterior)
            ->value('posicion');
        
        if ($posicionAnterior === null) return 0;
        
        return $posicionAnterior - $this->posicion; // Positivo = subió, Negativo = bajó
    }

    // Scopes
    public function scopePorTemporadaJornada($query, $temporadaId, $jornadaId)
    {
        return $query->where('temporada_id', $temporadaId)
                    ->where('jornada_id', $jornadaId);
    }

    public function scopePorEquipoTemporada($query, $equipoId, $temporadaId)
    {
        return $query->where('equipo_id', $equipoId)
                    ->where('temporada_id', $temporadaId)
                    ->orderBy('jornada_id');
    }
}