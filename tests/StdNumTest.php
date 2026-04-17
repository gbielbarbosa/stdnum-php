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
        $this->assertTrue(StdNum::isValid('us.atin', '123-45-6789'));
        $this->assertFalse(StdNum::isValid('us.atin', '1234-56789'));

        $this->assertTrue(StdNum::isValid('us.ein', '91-1144442'));
        $this->assertFalse(StdNum::isValid('us.ein', '911-14-4442'));

        $this->assertTrue(StdNum::isValid('us.itin', '912-90-3456'));
        $this->assertFalse(StdNum::isValid('us.itin', '123-45-6789'));
        $this->assertFalse(StdNum::isValid('us.itin', '912-93-4567'));

        $this->assertTrue(StdNum::isValid('us.ptin', 'P-00634642'));
        $this->assertTrue(StdNum::isValid('us.ptin', 'P01594846'));
        $this->assertFalse(StdNum::isValid('us.ptin', '00634642'));

        $this->assertTrue(StdNum::isValid('us.rtn', '111000025'));
        $this->assertFalse(StdNum::isValid('us.rtn', '112000025'));

        $this->assertTrue(StdNum::isValid('us.ssn', '536-90-4399'));
        $this->assertFalse(StdNum::isValid('us.ssn', '1112-23333'));
        $this->assertFalse(StdNum::isValid('us.ssn', '666-00-0000'));
        $this->assertFalse(StdNum::isValid('us.ssn', '078-05-1120'));
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
        $this->assertTrue(StdNum::isValid('pt.cc', '00000000 0 ZZ4'));
        $this->assertTrue(StdNum::isValid('pt.cc', '000000000ZZ4'));
        $this->assertFalse(StdNum::isValid('pt.cc', '00000000 0 ZZ3'));
        $this->assertFalse(StdNum::isValid('pt.cc', '00000000 A ZZ4'));

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

        $this->assertTrue(StdNum::isValid('nl.brin', '05 KO'));
        $this->assertTrue(StdNum::isValid('nl.brin', '07NU 00'));
        $this->assertFalse(StdNum::isValid('nl.brin', '12KB1'));

        $this->assertTrue(StdNum::isValid('nl.identiteitskaartnummer', 'EM0000000'));
        $this->assertTrue(StdNum::isValid('nl.identiteitskaartnummer', 'XR1001R58'));
        $this->assertFalse(StdNum::isValid('nl.identiteitskaartnummer', 'XR1O01R58'));

        $this->assertTrue(StdNum::isValid('nl.onderwijsnummer', '1012.22.331'));
        $this->assertFalse(StdNum::isValid('nl.onderwijsnummer', '100252333'));

        $this->assertTrue(StdNum::isValid('nl.postcode', '2601 DC'));
        $this->assertFalse(StdNum::isValid('nl.postcode', '2611 SS'));
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

    public function testCu()
    {
        $this->assertTrue(StdNum::isValid('cu.ni', '91021027775'));
        $this->assertFalse(StdNum::isValid('cu.ni', '9102102777A'));
        $this->assertFalse(StdNum::isValid('cu.ni', '02023061531'));
    }

    public function testCy()
    {
        $this->assertTrue(StdNum::isValid('cy.vat', 'CY-10259033P '));
        $this->assertFalse(StdNum::isValid('cy.vat', 'CY-10259033Z'));
    }

    public function testCz()
    {
        $this->assertTrue(StdNum::isValid('cz.rc', '710319/2745'));
        $this->assertTrue(StdNum::isValid('cz.rc', '991231123'));
        $this->assertFalse(StdNum::isValid('cz.rc', '7103192746'));
        $this->assertFalse(StdNum::isValid('cz.rc', '1103492745'));
        $this->assertFalse(StdNum::isValid('cz.rc', '590312/123'));

        $this->assertTrue(StdNum::isValid('cz.dic', '25123891'));
        $this->assertFalse(StdNum::isValid('cz.dic', '25123890'));
        $this->assertTrue(StdNum::isValid('cz.dic', '7103192745'));
        $this->assertTrue(StdNum::isValid('cz.dic', '640903926'));
    }

    public function testDk()
    {
        $this->assertTrue(StdNum::isValid('dk.cpr', '211062-5629'));
        $this->assertFalse(StdNum::isValid('dk.cpr', '511062-5629'));

        $this->assertTrue(StdNum::isValid('dk.cvr', 'DK 13585628'));
        $this->assertFalse(StdNum::isValid('dk.cvr', 'DK 13585627'));
    }

    public function testDo()
    {
        $this->assertTrue(StdNum::isValid('do.rnc', '1-01-85004-3'));
        $this->assertFalse(StdNum::isValid('do.rnc', '1018A0043'));
        $this->assertFalse(StdNum::isValid('do.rnc', '101850042'));

        $this->assertTrue(StdNum::isValid('do.cedula', '00113918205'));
        $this->assertFalse(StdNum::isValid('do.cedula', '00113918204'));
        $this->assertFalse(StdNum::isValid('do.cedula', '0011391820A'));
    }

    public function testEc()
    {
        $this->assertTrue(StdNum::isValid('ec.ci', '171430710-3'));
        $this->assertFalse(StdNum::isValid('ec.ci', '1714307104'));
        $this->assertFalse(StdNum::isValid('ec.ci', '171430710'));

        $this->assertTrue(StdNum::isValid('ec.ruc', '1792060346-001'));
        $this->assertFalse(StdNum::isValid('ec.ruc', '1763154690001'));
        $this->assertFalse(StdNum::isValid('ec.ruc', '179206034601'));
    }

    public function testEe()
    {
        $this->assertTrue(StdNum::isValid('ee.ik', '36805280109'));
        $this->assertFalse(StdNum::isValid('ee.ik', '36805280108'));

        $this->assertTrue(StdNum::isValid('ee.kmkr', 'EE 100 931 558'));
        $this->assertTrue(StdNum::isValid('ee.kmkr', '100594102'));
        $this->assertFalse(StdNum::isValid('ee.kmkr', '100594103'));

        $this->assertTrue(StdNum::isValid('ee.registrikood', '12345678'));
        $this->assertFalse(StdNum::isValid('ee.registrikood', '12345679'));
        $this->assertFalse(StdNum::isValid('ee.registrikood', '32345674'));
    }

    public function testFi()
    {
        $this->assertTrue(StdNum::isValid('fi.alv', 'FI 20774740'));
        $this->assertFalse(StdNum::isValid('fi.alv', 'FI 20774741'));

        $this->assertTrue(StdNum::isValid('fi.hetu', '131052-308T'));
        $this->assertFalse(StdNum::isValid('fi.hetu', '131052-308U'));
        $this->assertFalse(StdNum::isValid('fi.hetu', '310252-308Y'));

        $this->assertTrue(StdNum::isValid('fi.ytunnus', '2077474-0'));
        $this->assertFalse(StdNum::isValid('fi.ytunnus', '2077474-1'));
    }

    public function testGr()
    {
        $this->assertTrue(StdNum::isValid('gr.amka', '01013099997'));
        $this->assertFalse(StdNum::isValid('gr.amka', '01013099999'));

        $this->assertTrue(StdNum::isValid('gr.vat', 'EL 094259216 '));
        $this->assertFalse(StdNum::isValid('gr.vat', 'EL 123456781'));
    }

    public function testGt()
    {
        $this->assertTrue(StdNum::isValid('gt.nit', '576937-K'));
        $this->assertTrue(StdNum::isValid('gt.nit', '7108-0'));
        $this->assertFalse(StdNum::isValid('gt.nit', '8977112-0'));
        $this->assertFalse(StdNum::isValid('gt.nit', '1234567890123'));
    }

    public function testId()
    {
        $this->assertTrue(StdNum::isValid('id.nik', '3171011708450001'));
        $this->assertFalse(StdNum::isValid('id.nik', '31710117084500'));
        $this->assertTrue(StdNum::isValid('id.npwp', '01.312.166.0-091.000'));
        $this->assertTrue(StdNum::isValid('id.npwp', '016090524017000'));
        $this->assertFalse(StdNum::isValid('id.npwp', '123456789'));
    }

    public function testIe()
    {
        $this->assertTrue(StdNum::isValid('ie.pps', '6433435F'));
        $this->assertTrue(StdNum::isValid('ie.pps', '6433435FT'));
        $this->assertTrue(StdNum::isValid('ie.pps', '6433435OA'));
        $this->assertFalse(StdNum::isValid('ie.pps', '6433435E'));

        $this->assertTrue(StdNum::isValid('ie.vat', 'IE 6433435F'));
        $this->assertTrue(StdNum::isValid('ie.vat', 'IE 6433435OA'));
        $this->assertTrue(StdNum::isValid('ie.vat', '8D79739I'));
        $this->assertFalse(StdNum::isValid('ie.vat', '6433435E'));
        $this->assertFalse(StdNum::isValid('ie.vat', '8?79739J'));
    }

    public function testIl()
    {
        $this->assertTrue(StdNum::isValid('il.hp', '516179157'));
        $this->assertFalse(StdNum::isValid('il.hp', '516179150'));
        $this->assertFalse(StdNum::isValid('il.hp', '490154203237518'));

        $this->assertTrue(StdNum::isValid('il.idnr', '3933742-3'));
        $this->assertFalse(StdNum::isValid('il.idnr', '3933742-2'));
        $this->assertFalse(StdNum::isValid('il.idnr', '490154203237518'));
    }

    public function testIn()
    {
        $this->assertTrue(StdNum::isValid('in.aadhaar', '234123412346'));
        $this->assertFalse(StdNum::isValid('in.aadhaar', '234123412347'));
        $this->assertFalse(StdNum::isValid('in.aadhaar', '123412341234'));
        $this->assertFalse(StdNum::isValid('in.aadhaar', '643343121'));

        $this->assertTrue(StdNum::isValid('in.epic', 'WKH1186253'));
        $this->assertFalse(StdNum::isValid('in.epic', 'WKH118624'));
        $this->assertFalse(StdNum::isValid('in.epic', '1231186253'));

        $this->assertTrue(StdNum::isValid('in.pan', 'ACUPA7085R'));
        $this->assertFalse(StdNum::isValid('in.pan', 'ACUPA7085RR'));
        $this->assertFalse(StdNum::isValid('in.pan', 'ABMPA32111'));
        $this->assertFalse(StdNum::isValid('in.pan', 'ABMXA3211G'));
    }

    public function testIs()
    {
        $this->assertTrue(StdNum::isValid('is.kennitala', '450401-3150'));
        $this->assertTrue(StdNum::isValid('is.kennitala', '120174-3399'));
        $this->assertFalse(StdNum::isValid('is.kennitala', '530575-0299'));
        $this->assertFalse(StdNum::isValid('is.kennitala', '320174-3399'));

        $this->assertTrue(StdNum::isValid('is.vsk', 'IS 00621'));
        $this->assertFalse(StdNum::isValid('is.vsk', 'IS 0062199'));
    }

    public function testIt()
    {
        $this->assertTrue(StdNum::isValid('it.codicefiscale', 'RCCMNL83S18D969H'));
        $this->assertTrue(StdNum::isValid('it.codicefiscale', '00743110157'));
        $this->assertFalse(StdNum::isValid('it.codicefiscale', 'RCCMNL83S18D969'));
        $this->assertFalse(StdNum::isValid('it.codicefiscale', '00743110158'));

        $this->assertTrue(StdNum::isValid('it.iva', 'IT 00743110157'));
        $this->assertFalse(StdNum::isValid('it.iva', '00743110158'));
    }

    public function testJp()
    {
        $this->assertTrue(StdNum::isValid('jp.cn', '5-8356-7825-6246'));
        $this->assertFalse(StdNum::isValid('jp.cn', '2-8356-7825-6246'));
    }

    public function testKr()
    {
        $this->assertTrue(StdNum::isValid('kr.brn', '116-82-00276'));
        $this->assertTrue(StdNum::isValid('kr.brn', ' 116 - 82 - 00276  '));
        $this->assertFalse(StdNum::isValid('kr.brn', '123456789'));

        $this->assertTrue(StdNum::isValid('kr.rrn', '971013-9019902'));
        $this->assertFalse(StdNum::isValid('kr.rrn', '971013-9019903'));
    }

    public function testLt()
    {
        $this->assertTrue(StdNum::isValid('lt.pvm', '119511515'));
        $this->assertTrue(StdNum::isValid('lt.pvm', 'LT 100001919017'));
        $this->assertTrue(StdNum::isValid('lt.pvm', '100004801610'));
        $this->assertFalse(StdNum::isValid('lt.pvm', '100001919018'));

        $this->assertTrue(StdNum::isValid('lt.asmens', '33309240064'));
        $this->assertFalse(StdNum::isValid('lt.asmens', '33309240164'));
    }

    public function testLu()
    {
        $this->assertTrue(StdNum::isValid('lu.tva', 'LU 150 274 42'));
        $this->assertFalse(StdNum::isValid('lu.tva', '150 274 43'));
    }

    public function testLv()
    {
        $this->assertTrue(StdNum::isValid('lv.pvn', 'LV 4000 3521 600'));
        $this->assertFalse(StdNum::isValid('lv.pvn', '40003521601'));
        
        $this->assertTrue(StdNum::isValid('lv.pvn', '161175-19997'));
        $this->assertFalse(StdNum::isValid('lv.pvn', '161375-19997'));

        $this->assertTrue(StdNum::isValid('lv.pvn', '328673-00679'));
        $this->assertFalse(StdNum::isValid('lv.pvn', '328673-00677'));
    }

    public function testMc()
    {
        $this->assertTrue(StdNum::isValid('mc.tva', '53 0000 04605'));
        $this->assertFalse(StdNum::isValid('mc.tva', 'FR 61 954 506 077'));
    }

    public function testMd()
    {
        $this->assertTrue(StdNum::isValid('md.idno', '1008600038413'));
        $this->assertFalse(StdNum::isValid('md.idno', '1008600038412'));
    }

    public function testMe()
    {
        $this->assertTrue(StdNum::isValid('me.pib', '02655284'));
        $this->assertFalse(StdNum::isValid('me.pib', '02655283'));
    }

    public function testMk()
    {
        $this->assertTrue(StdNum::isValid('mk.edb', '4030000375897'));
        $this->assertTrue(StdNum::isValid('mk.edb', 'МК 4020990116747'));
        $this->assertTrue(StdNum::isValid('mk.edb', 'MK4057009501106'));
        $this->assertFalse(StdNum::isValid('mk.edb', '4030000375890'));
    }

    public function testMt()
    {
        $this->assertTrue(StdNum::isValid('mt.vat', 'MT 1167-9112'));
        $this->assertFalse(StdNum::isValid('mt.vat', '1167-9113'));
    }

    public function testMu()
    {
        $this->assertTrue(StdNum::isValid('mu.nid', 'A0101011234568'));
        $this->assertFalse(StdNum::isValid('mu.nid', 'A0101011234569'));
    }

    public function testMx()
    {
        $this->assertTrue(StdNum::isValid('mx.curp', 'BOXW310820HNERXN09'));
        $this->assertFalse(StdNum::isValid('mx.curp', 'BOXW310820HNERXN08'));

        $this->assertTrue(StdNum::isValid('mx.rfc', 'GODE 561231 GR8'));
        $this->assertTrue(StdNum::isValid('mx.rfc', 'MAB-930714-8T4'));
        $this->assertTrue(StdNum::isValid('mx.rfc', 'COMG-600703'));
        $this->assertTrue(StdNum::isValid('mx.rfc', 'VACE-460910-SX6'));
        $this->assertFalse(StdNum::isValid('mx.rfc', 'PUTO-460910-SX6')); // Blacklist word
    }

    public function testMy()
    {
        $this->assertTrue(StdNum::isValid('my.nric', '770305-02-1234'));
        $this->assertFalse(StdNum::isValid('my.nric', '771305-02-1234'));
        $this->assertFalse(StdNum::isValid('my.nric', '770305-17-1234'));
    }

    public function testNo()
    {
        $this->assertTrue(StdNum::isValid('no.fodselsnummer', '151086 95088'));
        $this->assertFalse(StdNum::isValid('no.fodselsnummer', '15108695077'));

        $this->assertTrue(StdNum::isValid('no.kontonr', '8601 11 17947'));
        $this->assertTrue(StdNum::isValid('no.kontonr', '0000.4090403'));
        $this->assertFalse(StdNum::isValid('no.kontonr', '8601 11 17949'));

        $this->assertTrue(StdNum::isValid('no.mva', 'NO 995 525 828 MVA'));
        $this->assertFalse(StdNum::isValid('no.mva', 'NO 995 525 829 MVA'));

        $this->assertTrue(StdNum::isValid('no.orgnr', '988 077 917'));
        $this->assertFalse(StdNum::isValid('no.orgnr', '988 077 918'));
    }

    public function testNz()
    {
        $this->assertTrue(StdNum::isValid('nz.bankaccount', '01-0242-0100194-00'));
        $this->assertFalse(StdNum::isValid('nz.bankaccount', '01-0242-0100195-00'));

        $this->assertTrue(StdNum::isValid('nz.ird', '4909185-0'));
        $this->assertTrue(StdNum::isValid('nz.ird', 'NZ 49-098-576'));
        $this->assertFalse(StdNum::isValid('nz.ird', '136410133'));
    }

    public function testPe()
    {
        $this->assertTrue(StdNum::isValid('pe.cui', '10117410'));
        $this->assertTrue(StdNum::isValid('pe.cui', '10117410-2'));
        $this->assertFalse(StdNum::isValid('pe.cui', '10117410-3'));

        $this->assertTrue(StdNum::isValid('pe.ruc', '20512333797'));
        $this->assertFalse(StdNum::isValid('pe.ruc', '20512333798'));
    }

    public function testPl()
    {
        $this->assertTrue(StdNum::isValid('pl.nip', 'PL 8567346215'));
        $this->assertFalse(StdNum::isValid('pl.nip', 'PL 8567346216'));

        $this->assertTrue(StdNum::isValid('pl.pesel', '44051401359'));
        $this->assertFalse(StdNum::isValid('pl.pesel', '44051401358'));

        $this->assertTrue(StdNum::isValid('pl.regon', '192598184'));
        $this->assertTrue(StdNum::isValid('pl.regon', '12345678512347'));
        $this->assertFalse(StdNum::isValid('pl.regon', '192598183'));
    }



    public function testPy()
    {
        $this->assertTrue(StdNum::isValid('py.ruc', '80028061-0'));
        $this->assertTrue(StdNum::isValid('py.ruc', '9991603'));
        $this->assertTrue(StdNum::isValid('py.ruc', '2660-3'));
        $this->assertFalse(StdNum::isValid('py.ruc', '800532492'));
    }

    public function testRo()
    {
        $this->assertTrue(StdNum::isValid('ro.cf', 'RO 185 472 90'));
        $this->assertTrue(StdNum::isValid('ro.cf', '1630615123457'));

        $this->assertTrue(StdNum::isValid('ro.cnp', '1630615123457'));
        $this->assertFalse(StdNum::isValid('ro.cnp', '1630615123458'));
        $this->assertFalse(StdNum::isValid('ro.cnp', '0800101221142')); // invalid first digit
        $this->assertFalse(StdNum::isValid('ro.cnp', '1632215123457')); // invalid date

        $this->assertTrue(StdNum::isValid('ro.cui', '185 472 90'));
        $this->assertFalse(StdNum::isValid('ro.cui', '185 472 91'));

        $this->assertTrue(StdNum::isValid('ro.onrc', 'J52/750/2012'));
        $this->assertTrue(StdNum::isValid('ro.onrc', 'J2012000750528'));
        $this->assertFalse(StdNum::isValid('ro.onrc', 'X52/750/2012'));
        $this->assertFalse(StdNum::isValid('ro.onrc', 'J2012000750529'));
    }

    public function testRs()
    {
        $this->assertTrue(StdNum::isValid('rs.pib', '101134702'));
        $this->assertFalse(StdNum::isValid('rs.pib', '101134703'));
    }

    public function testRu()
    {
        $this->assertTrue(StdNum::isValid('ru.inn', '123456789047'));
        $this->assertTrue(StdNum::isValid('ru.inn', '1234567894'));
        $this->assertFalse(StdNum::isValid('ru.inn', '123456789037'));
        $this->assertFalse(StdNum::isValid('ru.inn', '1234567895'));

        $this->assertTrue(StdNum::isValid('ru.ogrn', '1022200525819'));
        $this->assertTrue(StdNum::isValid('ru.ogrn', '385768585948949'));
        $this->assertFalse(StdNum::isValid('ru.ogrn', '1022500001328'));
    }

    public function testSe()
    {
        $this->assertTrue(StdNum::isValid('se.orgnr', '1234567897'));
        $this->assertFalse(StdNum::isValid('se.orgnr', '1234567891'));

        $this->assertTrue(StdNum::isValid('se.personnummer', '880320-0016'));
        $this->assertTrue(StdNum::isValid('se.personnummer', '8803200016'));
        $this->assertTrue(StdNum::isValid('se.personnummer', '890102-3286'));
        $this->assertFalse(StdNum::isValid('se.personnummer', '880320-0018'));

        $this->assertTrue(StdNum::isValid('se.vat', 'SE 123456789701'));
        $this->assertFalse(StdNum::isValid('se.vat', '123456789101'));
    }

    public function testSg()
    {
        $this->assertTrue(StdNum::isValid('sg.uen', '00192200M'));
        $this->assertTrue(StdNum::isValid('sg.uen', '197401143C'));
        $this->assertTrue(StdNum::isValid('sg.uen', 'S16FC0121D'));
        $this->assertTrue(StdNum::isValid('sg.uen', 'T01FC6132D'));
        $this->assertFalse(StdNum::isValid('sg.uen', '123456'));
    }

    public function testSi()
    {
        $this->assertTrue(StdNum::isValid('si.ddv', 'SI 5022 3054'));
        $this->assertFalse(StdNum::isValid('si.ddv', 'SI 50223055'));

        $this->assertTrue(StdNum::isValid('si.emso', '0101006500006'));
        $this->assertFalse(StdNum::isValid('si.emso', '0101006500007'));
    }

    public function testSk()
    {
        $this->assertTrue(StdNum::isValid('sk.dph', 'SK 202 274 96 19'));
        $this->assertFalse(StdNum::isValid('sk.dph', 'SK 202 274 96 18'));

        $this->assertTrue(StdNum::isValid('sk.rc', '710319/2745'));
        $this->assertTrue(StdNum::isValid('sk.rc', '991231123'));
        $this->assertFalse(StdNum::isValid('sk.rc', '7103192746'));
    }

    public function testSm()
    {
        $this->assertTrue(StdNum::isValid('sm.coe', '51'));
        $this->assertTrue(StdNum::isValid('sm.coe', '024165'));
        $this->assertFalse(StdNum::isValid('sm.coe', '2416A'));
        $this->assertFalse(StdNum::isValid('sm.coe', '1124165'));
    }

    public function testSv()
    {
        $this->assertTrue(StdNum::isValid('sv.nit', '0614-050707-104-8'));
        $this->assertTrue(StdNum::isValid('sv.nit', 'SV 0614-050707-104-8'));
        $this->assertFalse(StdNum::isValid('sv.nit', '0614-050707-104-0'));
    }

    public function testTr()
    {
        $this->assertTrue(StdNum::isValid('tr.tckimlik', '17291716060'));
        $this->assertFalse(StdNum::isValid('tr.tckimlik', '17291716050'));
        $this->assertFalse(StdNum::isValid('tr.tckimlik', '07291716092'));

        $this->assertTrue(StdNum::isValid('tr.vkn', '4540536920'));
        $this->assertFalse(StdNum::isValid('tr.vkn', '4540536921'));
    }

    public function testUa()
    {
        $this->assertTrue(StdNum::isValid('ua.edrpou', '32855961'));
        $this->assertFalse(StdNum::isValid('ua.edrpou', '32855968'));
    }

    public function testUy()
    {
        $this->assertTrue(StdNum::isValid('uy.rut', '21-100342-001-7'));
        $this->assertTrue(StdNum::isValid('uy.rut', 'UY 21 140634 001 1'));
        $this->assertFalse(StdNum::isValid('uy.rut', '210303670014'));
    }

    public function testVe()
    {
        $this->assertTrue(StdNum::isValid('ve.rif', 'V-11470283-4'));
        $this->assertFalse(StdNum::isValid('ve.rif', 'V-11470283-3'));
    }

    public function testZa()
    {
        $this->assertTrue(StdNum::isValid('za.id', '7503305044089'));
        $this->assertFalse(StdNum::isValid('za.id', '8503305044089'));

        $id = new \StdNum\Countries\ZA\ID();
        $this->assertEquals('1975-03-30', $id->getBirthDate('7503305044089')->format('Y-m-d'));
        $this->assertEquals('M', $id->getGender('7503305044089'));
        $this->assertEquals('citizen', $id->getCitizenship('7503305044089'));

        $this->assertTrue(StdNum::isValid('za.tin', '0001339050'));
        $this->assertFalse(StdNum::isValid('za.tin', '2449/494/16/0'));
    }
}
