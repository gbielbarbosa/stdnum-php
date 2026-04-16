<?php
use PHPUnit\Framework\TestCase;
use StdNum\StdNum;

class StdNumTest extends TestCase
{
    public function testValidCpf()
    {
        // 11 zeros is invalid, let's use a real one or generate one for test.
        // A valid CPF: 00000000000 is invalid sequence.
        // Let's use 390.533.447-05 which is mentioned in stdnum-js README
        $this->assertTrue(StdNum::isValid('br.cpf', '390.533.447-05'));
        $this->assertFalse(StdNum::isValid('br.cpf', '111.111.111-11'));
        $this->assertFalse(StdNum::isValid('br.cpf', '390.533.447-00'));
    }

    public function testGb()
    {
        $this->assertTrue(StdNum::isValid('gb.vat', 'GB 980 7806 84'));
        $this->assertFalse(StdNum::isValid('gb.vat', '802311781'));
    }

    public function testEs()
    {
        // Resident
        $this->assertTrue(StdNum::isValid('es.nif', '54362315K'));
        // Foreign person
        $this->assertTrue(StdNum::isValid('es.nif', 'X-5253868-R'));
        // Valid NIF but from CIF
        $this->assertTrue(StdNum::isValid('es.nif', 'B64717838'));
    }

    public function testLaravelRule()
    {
        $rule = new \StdNum\Laravel\Rules\StdNumRule('br.cpf');
        
        $failed = false;
        $failClosure = function (string $msg) use (&$failed) {
            $failed = true;
            return new class { public function translate() {} };
        };

        $rule->validate('document', '390.533.447-05', $failClosure);
        $this->assertFalse($failed);

        $rule->validate('document', '111.111.111-11', $failClosure);
        $this->assertTrue($failed);
    }

    public function testUs()
    {
        $this->assertTrue(StdNum::isValid('us.ssn', '536-90-4399'));
        $this->assertFalse(StdNum::isValid('us.ssn', '666-00-0000'));
        $this->assertFalse(StdNum::isValid('us.ssn', '078-05-1120')); // Blacklisted
    }

    public function testIt()
    {
        $this->assertTrue(StdNum::isValid('it.codicefiscale', 'RCCMNL83S18D969H'));
        $this->assertFalse(StdNum::isValid('it.codicefiscale', 'RCCMNL83S18D969')); // Short

        $this->assertTrue(StdNum::isValid('it.iva', '00743110157'));
        // Company number tested as Codice Fiscale as well
        $this->assertTrue(StdNum::isValid('it.codicefiscale', '00743110157'));
    }

    public function testFr()
    {
        $this->assertTrue(StdNum::isValid('fr.tva', 'FR 40 303 265 045'));
        $this->assertTrue(StdNum::isValid('fr.tva', '23334175221'));
        $this->assertFalse(StdNum::isValid('fr.tva', '84 323 140 391'));

        $this->assertTrue(StdNum::isValid('fr.siren', '303265045'));
    }

    public function testCa()
    {
        $this->assertTrue(StdNum::isValid('ca.sin', '123-456-782'));
        $this->assertFalse(StdNum::isValid('ca.sin', '999-999-999'));
        
        $this->assertTrue(StdNum::isValid('ca.bn', '12302 6635'));
        $this->assertTrue(StdNum::isValid('ca.bn', '12302 6635 RC 0001'));
        $this->assertFalse(StdNum::isValid('ca.bn', '123456783'));
    }

    public function testPt()
    {
        $this->assertTrue(StdNum::isValid('pt.nif', 'PT 501 964 843'));
        $this->assertFalse(StdNum::isValid('pt.nif', 'PT 501 964 842'));
    }

    public function testDe()
    {
        $this->assertTrue(StdNum::isValid('de.vat', 'DE 136,695 976'));
        $this->assertTrue(StdNum::isValid('de.vat', 'DE136695976'));
        $this->assertFalse(StdNum::isValid('de.vat', '136695978'));

        $this->assertTrue(StdNum::isValid('de.stnr', ' 181/815/0815 5')); // 11FF0BBBUUUUP
        $this->assertTrue(StdNum::isValid('de.stnr', '201/123/12340')); // 32FF0BBBUUUUP / Sachsen
        $this->assertTrue(StdNum::isValid('de.stnr', '4151081508156')); // 41FF0BBBUUUUP
        $this->assertFalse(StdNum::isValid('de.stnr', '4151181508156')); // invalid check 
    }

    public function testAu()
    {
        $this->assertTrue(StdNum::isValid('au.abn', '83 914 571 673'));
        $this->assertFalse(StdNum::isValid('au.abn', '99 999 999 999'));

        $this->assertTrue(StdNum::isValid('au.acn', '004 085 616'));
        $this->assertTrue(StdNum::isValid('au.acn', '010 499 966'));
        $this->assertFalse(StdNum::isValid('au.acn', '999 999 999'));

        $this->assertTrue(StdNum::isValid('au.tfn', '123 456 782'));
    }

    public function testNl()
    {
        $this->assertTrue(StdNum::isValid('nl.bsn', '1112.22.333'));
        $this->assertFalse(StdNum::isValid('nl.bsn', '1112.52.333'));

        $this->assertTrue(StdNum::isValid('nl.btw', 'NL4495445B01'));
        $this->assertTrue(StdNum::isValid('nl.btw', 'NL002455799B11'));
        $this->assertFalse(StdNum::isValid('nl.btw', '123456789B90'));
    }

    public function testBe()
    {
        $this->assertTrue(StdNum::isValid('be.nn', '85.07.30-033 28'));
        $this->assertTrue(StdNum::isValid('be.nn', '17 07 30 033 84'));
        $this->assertFalse(StdNum::isValid('be.nn', '12345678901'));

        $this->assertTrue(StdNum::isValid('be.vat', 'BE403019261'));
        $this->assertTrue(StdNum::isValid('be.vat', 'BE 428759497'));
        $this->assertFalse(StdNum::isValid('be.vat', 'BE431150351'));
    }

    public function testAd()
    {
        $this->assertTrue(StdNum::isValid('ad.nrt', 'U-132950-X'));
        $this->assertFalse(StdNum::isValid('ad.nrt', 'A123B'));
        $this->assertFalse(StdNum::isValid('ad.nrt', 'I 706193 G'));
    }

    public function testAl()
    {
        $this->assertTrue(StdNum::isValid('al.nipt', 'AL J 91402501 L'));
        $this->assertTrue(StdNum::isValid('al.nipt', 'K22218003V'));
        $this->assertFalse(StdNum::isValid('al.nipt', '(AL) J 91402501'));
        $this->assertFalse(StdNum::isValid('al.nipt', 'Z 22218003 V'));
    }

    public function testAr()
    {
        // CUIT
        $this->assertTrue(StdNum::isValid('ar.cuit', '20-05536168-2'));
        $this->assertFalse(StdNum::isValid('ar.cuit', '20267565392'));

        // DNI
        $this->assertTrue(StdNum::isValid('ar.dni', '20.123.456'));
        $this->assertFalse(StdNum::isValid('ar.dni', '2012345699'));

        // CBU
        $this->assertTrue(StdNum::isValid('ar.cbu', '2850590940090418135201'));
        $this->assertFalse(StdNum::isValid('ar.cbu', '2810590940090418135201'));
    }

    public function testAt()
    {
        // UID
        $this->assertTrue(StdNum::isValid('at.uid', 'AT U13585627'));
        $this->assertFalse(StdNum::isValid('at.uid', 'U13585626'));

        // TIN
        $this->assertTrue(StdNum::isValid('at.tin', '59-119/9013'));
        $this->assertFalse(StdNum::isValid('at.tin', '591199014'));
    }

    public function testAz()
    {
        $this->assertTrue(StdNum::isValid('az.voen', '140 155 5071'));
        $this->assertFalse(StdNum::isValid('az.voen', '140 155 5081'));
        $this->assertFalse(StdNum::isValid('az.voen', '1400057424'));
    }

    public function testBg()
    {
        $this->assertTrue(StdNum::isValid('bg.egn', '752316 926 3'));
        $this->assertTrue(StdNum::isValid('bg.egn', '8032056031'));
        $this->assertFalse(StdNum::isValid('bg.egn', '7552A10004'));
        $this->assertFalse(StdNum::isValid('bg.egn', '8019010008'));

        $this->assertTrue(StdNum::isValid('bg.pnf', '7111 042 925'));
        $this->assertFalse(StdNum::isValid('bg.pnf', '7111042922'));
        $this->assertFalse(StdNum::isValid('bg.pnf', '71110A2922'));

        $this->assertTrue(StdNum::isValid('bg.vat', 'BG 175 074 752'));
        $this->assertTrue(StdNum::isValid('bg.vat', '175074752'));
        $this->assertFalse(StdNum::isValid('bg.vat', '175074751'));
    }

    public function testBy()
    {
        $this->assertTrue(StdNum::isValid('by.unp', '200988541'));
        $this->assertTrue(StdNum::isValid('by.unp', 'УНП MA1953684'));
        $this->assertFalse(StdNum::isValid('by.unp', '200988542'));
    }

    public function testCh()
    {
        $this->assertTrue(StdNum::isValid('ch.ssn', '7569217076985'));
        $this->assertTrue(StdNum::isValid('ch.ssn', '756.9217.0769.85'));
        $this->assertFalse(StdNum::isValid('ch.ssn', '756.9217.0769.84'));
        $this->assertFalse(StdNum::isValid('ch.ssn', '123.4567.8910.19'));

        $this->assertTrue(StdNum::isValid('ch.uid', 'CHE-100.155.212'));
        $this->assertTrue(StdNum::isValid('ch.uid', '100.155.212'));
        $this->assertFalse(StdNum::isValid('ch.uid', 'CHE-100.155.213'));

        $this->assertTrue(StdNum::isValid('ch.vat', 'CHE-107.787.577 IVA'));
        $this->assertFalse(StdNum::isValid('ch.vat', 'CHE-107.787.578 IVA'));
    }

    public function testCl()
    {
        $this->assertTrue(StdNum::isValid('cl.rut', '76086428-5'));
        $this->assertTrue(StdNum::isValid('cl.rut', 'CL 12531909-2'));
        $this->assertFalse(StdNum::isValid('cl.rut', '12531909-3'));
        $this->assertFalse(StdNum::isValid('cl.rut', '76086A28-5'));
    }

    public function testCn()
    {
        $this->assertTrue(StdNum::isValid('cn.ric', '360426199101010071'));
        $this->assertFalse(StdNum::isValid('cn.ric', '360426199101010070'));

        $this->assertTrue(StdNum::isValid('cn.uscc', '91110000600037341L'));
        $this->assertFalse(StdNum::isValid('cn.uscc', 'A1110000600037341L'));
        $this->assertFalse(StdNum::isValid('cn.uscc', '12345'));
    }

    public function testCo()
    {
        $this->assertTrue(StdNum::isValid('co.nit', '213.123.432-1'));
        $this->assertFalse(StdNum::isValid('co.nit', '2131234325'));
        $this->assertFalse(StdNum::isValid('co.nit', '2131'));
    }

    public function testCr()
    {
        $this->assertTrue(StdNum::isValid('cr.cpf', '3-0455-0175'));
        $this->assertFalse(StdNum::isValid('cr.cpf', '30-1234-1234'));
        $this->assertFalse(StdNum::isValid('cr.cpf', '12345678'));

        $this->assertTrue(StdNum::isValid('cr.cpj', '3-101-999999'));
        $this->assertFalse(StdNum::isValid('cr.cpj', '3-534-123559'));
        $this->assertFalse(StdNum::isValid('cr.cpj', '310132541'));

        $this->assertTrue(StdNum::isValid('cr.cr', '155812994816'));
        $this->assertFalse(StdNum::isValid('cr.cr', '30123456789'));
        $this->assertFalse(StdNum::isValid('cr.cr', '12345678'));
    }
}
