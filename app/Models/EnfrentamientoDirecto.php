<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnfrentamientoDirecto extends Model
{
    protected $table = 'enfrentamientos_directos';
    
    protected $fillable = [
        'equipo1_id',
        'equipo2_id',
        'temporada_id',
        'partidos_jugados',
        'victorias_equipo1',
        'victorias_equipo2',
        'empates',
        'goles_equipo1',
        'goles_equipo2'
    ];

    protected $casts = [
        'partidos_jugados' => 'integer',
        'victorias_equipo1' => 'integer',
        'victorias_equipo2' => 'integer',
        'empates' => 'integer',
        'goles_equipo1' => 'integer',
        'goles_equipo2' => 'integer'
    ];

    // Relaciones
    public function equipo1(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo1_id');
    }

    public function equipo2(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo2_id');
    }

    public function temporada(): BelongsTo
    {
        return $this->belongsTo(Temporada::class);
    }

    // Métodos útiles
    public function getDiferenciaGolesAttribute(): int
    {
        return $this->goles_equipo1 - $this->goles_equipo2;
    }

    public function getDominioEquipo1Attribute(): float
    {
        if ($this->partidos_jugados === 0) return 0.0;
        return round(($this->victorias_equipo1 / $this->partidos_jugados) * 100, 2);
    }

    public function getDominioEquipo2Attribute(): float
    {
        if ($this->partidos_jugados === 0) return 0.0;
        return round(($this->victorias_equipo2 / $this->partidos_jugados) * 100, 2);
    }

    public function getPorcentajeEmpatesAttribute(): float
    {
        if ($this->partidos_jugados === 0) return 0.0;
        return round(($this->empates / $this->partidos_jugados) * 100, 2);
    }
}