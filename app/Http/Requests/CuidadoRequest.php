<?php

namespace App\Http\Requests;

use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use App\Models\Cuidado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CuidadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Aceita temperatura com vírgula: 36,5
        if (is_string($this->input('temperatura'))) {
            $this->merge(['temperatura' => str_replace(',', '.', $this->input('temperatura'))]);
        }
    }

    public function rules(): array
    {
        return [
            'idoso_id' => ['required', 'integer', Rule::exists('idosos', 'id')],
            'tipo' => ['required', Rule::enum(TipoCuidado::class)],
            'status' => ['required', Rule::enum(StatusCuidado::class)],
            'data_hora' => ['required', 'date'],
            'responsavel' => ['required', 'string', 'max:150'],
            'descricao' => ['required', 'string', 'max:2000'],

            'medicamento' => ['nullable', 'required_if:tipo,'.TipoCuidado::Medicacao->value, 'string', 'max:150'],
            'dosagem' => ['nullable', 'string', 'max:100'],

            'pressao_sistolica' => ['nullable', 'integer', 'between:50,260', 'required_with:pressao_diastolica'],
            'pressao_diastolica' => ['nullable', 'integer', 'between:30,160', 'required_with:pressao_sistolica'],
            'frequencia_cardiaca' => ['nullable', 'integer', 'between:20,250'],
            'temperatura' => ['nullable', 'numeric', 'between:30,45'],
            'glicemia' => ['nullable', 'integer', 'between:20,600'],
            'saturacao' => ['nullable', 'integer', 'between:50,100'],

            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $tipo = $this->input('tipo');
            $status = $this->input('status');

            if ($tipo === TipoCuidado::SinaisVitais->value
                && $status === StatusCuidado::Realizado->value
                && ! $this->anyFilled(Cuidado::SINAIS_VITAIS)) {
                $validator->errors()->add('pressao_sistolica', 'Informe pelo menos um sinal vital aferido.');
            }
        });
    }

    /** Dados validados, com a data já convertida. */
    public function dados(): array
    {
        $dados = $this->validated();
        $dados['data_hora'] = Carbon::parse($dados['data_hora']);

        return $dados;
    }

    public function attributes(): array
    {
        return [
            'idoso_id' => 'idoso',
            'data_hora' => 'data e hora',
            'responsavel' => 'quem cuidou',
            'descricao' => 'descrição',
            'pressao_sistolica' => 'pressão máxima',
            'pressao_diastolica' => 'pressão mínima',
            'frequencia_cardiaca' => 'batimentos',
            'medicamento' => 'medicamento',
            'dosagem' => 'dose',
            'saturacao' => 'saturação',
            'observacoes' => 'observações',
        ];
    }

    public function messages(): array
    {
        return [
            'medicamento.required_if' => 'Informe qual medicamento foi dado.',
            'idoso_id.required' => 'Escolha o idoso.',
            'tipo.required' => 'Escolha o tipo de cuidado.',
            'pressao_sistolica.required_with' => 'Informe também a pressão máxima.',
            'pressao_diastolica.required_with' => 'Informe também a pressão mínima.',
        ];
    }
}
