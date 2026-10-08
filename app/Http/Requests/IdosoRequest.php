<?php

namespace App\Http\Requests;

use App\Enums\Sexo;
use App\Models\Idoso;
use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IdosoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cpf = preg_replace('/\D/', '', (string) $this->input('cpf'));

        $this->merge([
            'cpf' => $cpf === '' ? null : $cpf,
            'ativo' => $this->boolean('ativo'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'data_nascimento' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'sexo' => ['nullable', Rule::enum(Sexo::class)],
            'cpf' => [
                'nullable',
                'string',
                new Cpf,
                Rule::unique('idosos', 'cpf')->ignore($this->route('idoso')),
            ],
            'tipo_sanguineo' => ['nullable', Rule::in(Idoso::TIPOS_SANGUINEOS)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'contato_emergencia_nome' => ['nullable', 'string', 'max:150'],
            'contato_emergencia_telefone' => ['nullable', 'string', 'max:20'],
            'condicoes_saude' => ['nullable', 'string', 'max:5000'],
            'alergias' => ['nullable', 'string', 'max:5000'],
            'medicamentos_continuos' => ['nullable', 'string', 'max:5000'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            'ativo' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'data_nascimento' => 'data de nascimento',
            'cpf' => 'CPF',
            'tipo_sanguineo' => 'tipo sanguíneo',
            'endereco' => 'endereço',
            'contato_emergencia_nome' => 'nome do contato de emergência',
            'contato_emergencia_telefone' => 'telefone do contato de emergência',
            'condicoes_saude' => 'condições de saúde',
            'medicamentos_continuos' => 'medicamentos de uso contínuo',
            'observacoes' => 'observações',
        ];
    }

    public function messages(): array
    {
        return [
            'data_nascimento.before' => 'A data de nascimento precisa ser anterior a hoje.',
            'cpf.unique' => 'Já existe um idoso cadastrado com este CPF.',
        ];
    }
}
