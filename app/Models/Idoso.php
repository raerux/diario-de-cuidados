<?php

namespace App\Models;

use App\Enums\Sexo;
use App\Rules\Cpf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Idoso extends Model
{
    use HasFactory;

    protected $table = 'idosos';

    public const TIPOS_SANGUINEOS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    protected $fillable = [
        'nome',
        'data_nascimento',
        'sexo',
        'cpf',
        'tipo_sanguineo',
        'telefone',
        'endereco',
        'contato_emergencia_nome',
        'contato_emergencia_telefone',
        'condicoes_saude',
        'alergias',
        'medicamentos_continuos',
        'observacoes',
        'ativo',
    ];

    protected $attributes = [
        'ativo' => true,
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'sexo' => Sexo::class,
            'ativo' => 'boolean',
        ];
    }

    public function cuidados(): HasMany
    {
        return $this->hasMany(Cuidado::class);
    }

    protected function idade(): Attribute
    {
        return Attribute::get(fn () => $this->data_nascimento?->age);
    }

    protected function cpfFormatado(): Attribute
    {
        return Attribute::get(fn () => Cpf::formatar($this->cpf));
    }

    /** Primeiro nome + último sobrenome, para listas compactas. */
    protected function nomeCurto(): Attribute
    {
        return Attribute::get(function () {
            $partes = preg_split('/\s+/', trim((string) $this->nome));

            return count($partes) > 1 ? $partes[0].' '.end($partes) : $partes[0];
        });
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeBusca(Builder $query, ?string $termo): Builder
    {
        $termo = trim((string) $termo);

        if ($termo === '') {
            return $query;
        }

        $cpf = preg_replace('/\D/', '', $termo);

        return $query->where(function (Builder $q) use ($termo, $cpf) {
            $q->where('nome', 'like', "%{$termo}%");

            if ($cpf !== '') {
                $q->orWhere('cpf', 'like', "%{$cpf}%");
            }
        });
    }
}
