<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partido extends Model
{
    protected $table = 'partidos';
    
    protected $fillable = [
        'temporada_id',
        'jornada_id',
        'equipo_local_id',
        'equipo_visitante_id',
        'fecha_hora',
        'estadio',
        'arbitro',
        'estado',
        'goles_local',
        'goles_visitante',
        'penales_local',
        'penales_visitante',
        'observaciones'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'goles_local' => 'integer',
        'goles_visitante' => 'integer',
        'penales_local' => 'integer',
        'penales_visitante' => 'integer'
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

    public function equipoLocal(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_local_id');
    }

    public function equipoVisitante(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_visitante_id');
    }

    public function resultadosDetalle(): HasMany
    {
        return $this->hasMany(ResultadoDetalle::class);
    }

    // Métodos útiles
    public function getResultadoAttribute(): string
    {
        if ($this->estado !== 'Finalizado') {
            return 'Por jugar';
        }
        
        if ($this->penales_local !== null && $this->penales_visitante !== null) {
            return "{$this->goles_local}-{$this->goles_visitante} ({$this->penales_local}-{$this->penales_visitante} pen.)";
        }
        
        return "{$this->goles_local}-{$this->goles_visitante}";
    }

    public function getGanadorAttribute(): ?Equipo
    {
        if ($this->estado !== 'Finalizado') {
            return null;
        }

        if ($this->goles_local > $this->goles_visitante) {
            return $this->equipoLocal;
        } elseif ($this->goles_local < $this->goles_visitante) {
            return $this->equipoVisitante;
        } elseif ($this->penales_local > $this->penales_visitante) {
            return $this->equipoLocal;
        } elseif ($this->penales_local < $this->penales_visitante) {
            return $this->equipoVisitante;
        }
        
        return null; // Empate en penales o sin penales
    }

    public function getPerdedorAttribute(): ?Equipo
    {
        if ($this->estado !== 'Finalizado') {
            return null;
        }

        if ($this->goles_local < $this->goles_visitante) {
            return $this->equipoLocal;
        } elseif ($this->goles_local > $this->goles_visitante) {
            return $this->equipoVisitante;
        } elseif ($this->penales_local < $this->penales_visitante) {
            return $this->equipoLocal;
        } elseif ($this->penales_local > $this->penales_visitante) {
            return $this->equipoVisitante;
        }
        
        return null; // Empate
    }

    public function getEmpateAttribute(): bool
    {
        return $this->estado === 'Finalizado' && 
               $this->goles_local === $this->goles_visitante &&
               ($this->penales_local === null || $this->penales_local === $this->penales_visitante);
    }

    public function getPuntosLocalAttribute(): int
    {
        if ($this->estado !== 'Finalizado') return 0;
        
        if ($this->goles_local > $this->goles_visitante) return 3;
        if ($this->goles_local === $this->goles_visitante) return 1;
        return 0;
    }

    public function getPuntosVisitanteAttribute(): int
    {
        if ($this->estado !== 'Finalizado') return 0;
        
        if ($this->goles_visitante > $this->goles_local) return 3;
        if ($this->goles_visitante === $this->goles_local) return 1;
        return 0;
    }

    // Scopes
    public function scopeFinalizados($query)
    {
        return $query->where('estado', 'Finalizado');
    }

    public function scopeProgramados($query)
    {
        return $query->where('estado', 'Programado');
    }

    public function scopePorEquipo($query, $equipoId)
    {
        return $query->where(function($q) use ($equipoId) {
            $q->where('equipo_local_id', $equipoId)
              ->orWhere('equipo_visitante_id', $equipoId);
        });
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_hora', $fecha);
    }

    public function scopeEntreFechas($query, $inicio, $fin)
    {
        return $query->whereBetween('fecha_hora', [$inicio, $fin]);
    }
}