<?php

namespace App\Models;

use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Cuidado extends Model
{
    use HasFactory;

    protected $table = 'cuidados';

    public const SINAIS_VITAIS = [
        'pressao_sistolica',
        'pressao_diastolica',
        'frequencia_cardiaca',
        'temperatura',
        'glicemia',
        'saturacao',
    ];

    protected $fillable = [
        'idoso_id',
        'tipo',
        'status',
        'data_hora',
        'responsavel',
        'descricao',
        'medicamento',
        'dosagem',
        'pressao_sistolica',
        'pressao_diastolica',
        'frequencia_cardiaca',
        'temperatura',
        'glicemia',
        'saturacao',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoCuidado::class,
            'status' => StatusCuidado::class,
            'data_hora' => 'datetime',
            'pressao_sistolica' => 'integer',
            'pressao_diastolica' => 'integer',
            'frequencia_cardiaca' => 'integer',
            'temperatura' => 'decimal:1',
            'glicemia' => 'integer',
            'saturacao' => 'integer',
        ];
    }

    public function idoso(): BelongsTo
    {
        return $this->belongsTo(Idoso::class);
    }

    /** Usuário do sistema que fez o registro. */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function pressaoArterial(): Attribute
    {
        return Attribute::get(function () {
            if ($this->pressao_sistolica === null || $this->pressao_diastolica === null) {
                return null;
            }

            return $this->pressao_sistolica.'/'.$this->pressao_diastolica;
        });
    }

    public function temSinaisVitais(): bool
    {
        foreach (self::SINAIS_VITAIS as $campo) {
            if ($this->{$campo} !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sinais vitais preenchidos, já formatados para exibição.
     *
     * @return array<string, array{rotulo: string, valor: string, fora: bool}>
     */
    public function sinaisVitais(): array
    {
        $fora = $this->camposForaDaFaixa();
        $sinais = [];

        if ($this->pressao_arterial !== null) {
            $sinais['pressao'] = [
                'rotulo' => 'Pressão',
                'valor' => $this->pressao_arterial.' mmHg',
                'fora' => isset($fora['pressao_sistolica']) || isset($fora['pressao_diastolica']),
            ];
        }

        $outros = [
            'frequencia_cardiaca' => ['Batimentos', ' bpm'],
            'temperatura' => ['Temperatura', ' °C'],
            'glicemia' => ['Glicemia', ' mg/dL'],
            'saturacao' => ['Saturação', '%'],
        ];

        foreach ($outros as $campo => [$rotulo, $unidade]) {
            if ($this->{$campo} === null) {
                continue;
            }

            $valor = $campo === 'temperatura'
                ? number_format((float) $this->temperatura, 1, ',', '')
                : (string) $this->{$campo};

            $sinais[$campo] = [
                'rotulo' => $rotulo,
                'valor' => $valor.$unidade,
                'fora' => isset($fora[$campo]),
            ];
        }

        return $sinais;
    }

    /**
     * Mensagens para os sinais vitais fora das faixas definidas em config/cuidados.php.
     *
     * @return list<string>
     */
    public function alertas(): array
    {
        return array_values($this->camposForaDaFaixa());
    }

    /** @return array<string, string> campo => mensagem */
    private function camposForaDaFaixa(): array
    {
        $alertas = [];

        foreach (config('cuidados.faixas', []) as $campo => $faixa) {
            $valor = $this->{$campo};

            if ($valor === null) {
                continue;
            }

            $valor = (float) $valor;
            $exibir = $campo === 'temperatura' ? number_format($valor, 1, ',', '') : (string) (int) $valor;

            if ($valor < $faixa['min']) {
                $alertas[$campo] = "{$faixa['nome']} abaixo da faixa usual ({$exibir} {$faixa['unidade']})";
            } elseif ($valor > $faixa['max']) {
                $alertas[$campo] = "{$faixa['nome']} acima da faixa usual ({$exibir} {$faixa['unidade']})";
            }
        }

        return $alertas;
    }

    public function estaAtrasado(): bool
    {
        return $this->status === StatusCuidado::Pendente && $this->data_hora->isPast();
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', StatusCuidado::Pendente->value);
    }

    public function scopeDoDia(Builder $query, Carbon $dia): Builder
    {
        return $query->whereBetween('data_hora', [$dia->copy()->startOfDay(), $dia->copy()->endOfDay()]);
    }

    /**
     * Filtros vindos da tela de listagem.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            ->when($filtros['idoso'] ?? null, fn (Builder $q, $id) => $q->where('idoso_id', $id))
            ->when($filtros['tipo'] ?? null, fn (Builder $q, $tipo) => $q->where('tipo', $tipo))
            ->when($filtros['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filtros['de'] ?? null, fn (Builder $q, $de) => $q->where('data_hora', '>=', Carbon::parse($de)->startOfDay()))
            ->when($filtros['ate'] ?? null, fn (Builder $q, $ate) => $q->where('data_hora', '<=', Carbon::parse($ate)->endOfDay()))
            ->when($filtros['q'] ?? null, function (Builder $q, $termo) {
                $q->where(function (Builder $q) use ($termo) {
                    $q->where('descricao', 'like', "%{$termo}%")
                        ->orWhere('responsavel', 'like', "%{$termo}%")
                        ->orWhere('medicamento', 'like', "%{$termo}%")
                        ->orWhere('observacoes', 'like', "%{$termo}%");
                });
            });
    }
}
