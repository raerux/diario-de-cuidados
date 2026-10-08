<?php

namespace Tests\Unit;

use App\Rules\Cpf;
use PHPUnit\Framework\TestCase;

class CpfTest extends TestCase
{
    public function test_valida_digitos_verificadores(): void
    {
        $this->assertTrue(Cpf::valido('529.982.247-25'));
        $this->assertTrue(Cpf::valido('52998224725'));
        $this->assertFalse(Cpf::valido('529.982.247-24'));
        $this->assertFalse(Cpf::valido('111.111.111-11'));
        $this->assertFalse(Cpf::valido('123'));
    }

    public function test_cpfs_gerados_sao_validos(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $this->assertTrue(Cpf::valido(Cpf::gerar()));
        }
    }

    public function test_formata(): void
    {
        $this->assertSame('529.982.247-25', Cpf::formatar('52998224725'));
        $this->assertNull(Cpf::formatar(null));
    }
}
