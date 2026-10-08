<?php

namespace Database\Seeders;

use App\Enums\Sexo;
use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use App\Models\Cuidado;
use App\Models\Idoso;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Database\Seeder;

class DadosExemploSeeder extends Seeder
{
    /**
     * Quatro idosos e seis dias de registros de exemplo, para conhecer o sistema.
     * Não faz nada se já houver idosos cadastrados.
     */
    public function run(): void
    {
        if (Idoso::query()->exists()) {
            return;
        }

        $admin = User::query()->where('email', UsuarioInicialSeeder::EMAIL)->first() ?? User::query()->first();

        if (! $admin) {
            $this->call(UsuarioInicialSeeder::class);
            $admin = User::query()->where('email', UsuarioInicialSeeder::EMAIL)->firstOrFail();
        }

        $maria = Idoso::create([
            'nome' => 'Maria das Graças Souza',
            'data_nascimento' => '1942-03-18',
            'sexo' => Sexo::Feminino,
            'cpf' => Cpf::gerar(),
            'tipo_sanguineo' => 'O+',
            'telefone' => '(91) 3222-1040',
            'endereco' => 'Travessa das Mangueiras, 120 - apto 302',
            'contato_emergencia_nome' => 'Raimunda Souza (filha)',
            'contato_emergencia_telefone' => '(91) 98811-2040',
            'condicoes_saude' => "Hipertensão arterial\nOsteoporose",
            'alergias' => 'Dipirona',
            'medicamentos_continuos' => "Losartana 50 mg - 8h e 20h\nCálcio + vitamina D - no almoço",
            'observacoes' => 'Usa óculos para leitura. Prefere tomar banho pela manhã.',
        ]);

        $jose = Idoso::create([
            'nome' => 'José Ribamar Costa',
            'data_nascimento' => '1947-11-02',
            'sexo' => Sexo::Masculino,
            'cpf' => Cpf::gerar(),
            'tipo_sanguineo' => 'A+',
            'telefone' => '(91) 99120-5566',
            'endereco' => 'Rua Boaventura da Silva, 845',
            'contato_emergencia_nome' => 'Carlos Costa (filho)',
            'contato_emergencia_telefone' => '(91) 98402-7781',
            'condicoes_saude' => "Diabetes tipo 2\nHipertensão arterial",
            'alergias' => null,
            'medicamentos_continuos' => "Metformina 850 mg - café da manhã e jantar\nEnalapril 10 mg - 8h",
            'observacoes' => 'Controlar doces. Gosta de caminhar no fim da tarde.',
        ]);

        $benedita = Idoso::create([
            'nome' => 'Benedita Lima Ferreira',
            'data_nascimento' => '1934-06-25',
            'sexo' => Sexo::Feminino,
            'cpf' => null,
            'tipo_sanguineo' => 'B+',
            'telefone' => null,
            'endereco' => 'Passagem Santa Luzia, 58',
            'contato_emergencia_nome' => 'Lúcia Ferreira (neta)',
            'contato_emergencia_telefone' => '(91) 98250-3317',
            'condicoes_saude' => "Doença de Alzheimer, fase moderada\nMobilidade reduzida, usa cadeira de rodas",
            'alergias' => 'Penicilina',
            'medicamentos_continuos' => "Sertralina 50 mg - 8h\nDonepezila 10 mg - 21h",
            'observacoes' => 'Precisa de ajuda para se alimentar. Fica mais calma ouvindo rádio pela manhã. Mudar de posição na cama a cada 2 horas à noite.',
        ]);

        Idoso::create([
            'nome' => 'Antônio Pereira Neto',
            'data_nascimento' => '1939-01-09',
            'sexo' => Sexo::Masculino,
            'cpf' => null,
            'tipo_sanguineo' => null,
            'condicoes_saude' => 'Insuficiência cardíaca',
            'observacoes' => 'Mudou-se para a casa da filha em outra cidade.',
            'ativo' => false,
        ]);

        mt_srand(2026);

        $rotinas = [
            [$maria, [
                ['07:30', TipoCuidado::Higiene, 'Banho de chuveiro com cadeira de banho e hidratante nas pernas.'],
                ['08:00', TipoCuidado::Medicacao, 'Tomou com água, em jejum.', 'Losartana', '50 mg'],
                ['08:30', TipoCuidado::Alimentacao, 'Café da manhã: tapioca com queijo, mamão e café com leite. Comeu tudo.'],
                ['09:00', TipoCuidado::SinaisVitais, 'Aferição de rotina, sentada.'],
                ['12:30', TipoCuidado::Alimentacao, 'Almoço: arroz, feijão, peixe assado e salada. Comeu metade.'],
                ['12:30', TipoCuidado::Medicacao, 'Junto com o almoço.', 'Cálcio + vitamina D', '1 comprimido'],
                ['15:00', TipoCuidado::Hidratacao, 'Água de coco e 2 copos de água.'],
                ['20:00', TipoCuidado::Medicacao, 'Tomou depois do jantar.', 'Losartana', '50 mg'],
            ]],
            [$jose, [
                ['07:00', TipoCuidado::Sono, 'Dormiu bem, acordou uma vez de madrugada para ir ao banheiro.'],
                ['07:40', TipoCuidado::SinaisVitais, 'Glicemia em jejum e pressão.'],
                ['08:00', TipoCuidado::Medicacao, 'Com o café da manhã.', 'Metformina', '850 mg'],
                ['08:00', TipoCuidado::Medicacao, 'Com água.', 'Enalapril', '10 mg'],
                ['12:00', TipoCuidado::Alimentacao, 'Almoço: arroz integral, frango cozido e legumes. Sem sobremesa.'],
                ['17:30', TipoCuidado::Mobilidade, 'Caminhada de 20 minutos na praça, sem queixas.'],
                ['19:30', TipoCuidado::Medicacao, 'Com o jantar.', 'Metformina', '850 mg'],
            ]],
            [$benedita, [
                ['06:00', TipoCuidado::Mobilidade, 'Mudança de decúbito: virada para o lado direito.'],
                ['08:00', TipoCuidado::Medicacao, 'Comprimido amassado no mingau.', 'Sertralina', '50 mg'],
                ['08:15', TipoCuidado::Alimentacao, 'Mingau de aveia, com ajuda. Aceitou bem.'],
                ['09:30', TipoCuidado::Higiene, 'Banho no leito e troca de fralda. Pele íntegra.'],
                ['10:00', TipoCuidado::SinaisVitais, 'Aferição de rotina.'],
                ['14:00', TipoCuidado::Hidratacao, 'Suco de acerola de canudinho, 200 ml.'],
                ['16:00', TipoCuidado::Curativo, 'Hidratação da pele nos calcanhares e cotovelos. Sem vermelhidão.'],
                ['21:00', TipoCuidado::Medicacao, 'Tomou com água.', 'Donepezila', '10 mg'],
            ]],
        ];

        $responsaveis = ['Ana Paula', 'Raimunda', 'Carlos'];
        $agora = now();

        foreach (range(5, 0) as $diasAtras) {
            $dia = today()->subDays($diasAtras);

            foreach ($rotinas as $indice => [$idoso, $eventos]) {
                foreach ($eventos as $evento) {
                    [$hora, $tipo, $descricao] = $evento;
                    $dataHora = $dia->copy()->setTimeFromTimeString($hora);
                    $feito = $dataHora->lte($agora);

                    $dados = [
                        'idoso_id' => $idoso->id,
                        'tipo' => $tipo,
                        'status' => $feito ? StatusCuidado::Realizado : StatusCuidado::Pendente,
                        'data_hora' => $dataHora,
                        'responsavel' => $responsaveis[($indice + $diasAtras + (int) substr($hora, 0, 2)) % 3],
                        'descricao' => $descricao,
                        'medicamento' => $evento[3] ?? null,
                        'dosagem' => $evento[4] ?? null,
                    ];

                    if ($tipo === TipoCuidado::SinaisVitais) {
                        $dados = $feito
                            ? array_merge($dados, $this->sinaisVitais($indice, $diasAtras))
                            : array_merge($dados, ['descricao' => 'Aferir pressão, temperatura e saturação.']);
                    }

                    // Um exemplo de cuidado que estava previsto e não aconteceu.
                    if ($diasAtras === 2 && $idoso->is($jose) && $tipo === TipoCuidado::Mobilidade) {
                        $dados['status'] = StatusCuidado::NaoRealizado;
                        $dados['descricao'] = 'Caminhada não feita: choveu forte a tarde toda.';
                        $dados['observacoes'] = 'Fez 10 minutos de alongamento sentado no lugar.';
                    }

                    $this->registrar($dados, $admin);
                }
            }
        }

        // Uma consulta agendada para amanhã.
        $this->registrar([
            'idoso_id' => $maria->id,
            'tipo' => TipoCuidado::Consulta,
            'status' => StatusCuidado::Pendente,
            'data_hora' => today()->addDay()->setTime(9, 30),
            'responsavel' => 'Raimunda',
            'descricao' => 'Consulta com a cardiologista. Levar os últimos exames e o caderno de pressão.',
        ], $admin);
    }

    /** @param  array<string, mixed>  $dados */
    private function registrar(array $dados, User $usuario): void
    {
        $cuidado = new Cuidado($dados);
        $cuidado->user_id = $usuario->id;
        $cuidado->save();
    }

    /**
     * Valores de exemplo com alguma variação; de vez em quando saem da faixa
     * usual para mostrar os destaques do sistema.
     *
     * @return array<string, int|float>
     */
    private function sinaisVitais(int $indice, int $diasAtras): array
    {
        return match ($indice) {
            // Maria: pressão às vezes alta
            0 => [
                'pressao_sistolica' => mt_rand(126, 152),
                'pressao_diastolica' => mt_rand(78, 94),
                'frequencia_cardiaca' => mt_rand(68, 84),
                'temperatura' => mt_rand(358, 368) / 10,
                'saturacao' => mt_rand(95, 98),
            ],
            // José: glicemia em jejum oscilando
            1 => [
                'pressao_sistolica' => mt_rand(118, 136),
                'pressao_diastolica' => mt_rand(74, 86),
                'frequencia_cardiaca' => mt_rand(64, 80),
                'glicemia' => $diasAtras === 3 ? 214 : mt_rand(108, 168),
                'saturacao' => mt_rand(95, 98),
            ],
            // Benedita: febre em um dos dias
            default => [
                'pressao_sistolica' => mt_rand(108, 128),
                'pressao_diastolica' => mt_rand(66, 80),
                'frequencia_cardiaca' => mt_rand(70, 88),
                'temperatura' => $diasAtras === 1 ? 37.9 : mt_rand(359, 368) / 10,
                'saturacao' => mt_rand(93, 97),
            ],
        };
    }
}
