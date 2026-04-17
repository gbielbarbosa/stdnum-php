<?php

namespace StdNum;

use StdNum\Contracts\DocumentInterface;
use StdNum\Exceptions\UnsupportedDocumentTypeException;

class StdNum
{
    /**
     * Map of dot notation types to fully qualified class names.
     */
    protected static array $map = [
        'br.cpf' => \StdNum\Countries\BR\CPF::class,
        'br.cnpj' => \StdNum\Countries\BR\CNPJ::class,
        'gb.nino' => \StdNum\Countries\GB\NINO::class,
        'gb.utr' => \StdNum\Countries\GB\UTR::class,
        'gb.vat' => \StdNum\Countries\GB\VAT::class,
        'es.dni' => \StdNum\Countries\ES\DNI::class,
        'es.nie' => \StdNum\Countries\ES\NIE::class,
        'es.cif' => \StdNum\Countries\ES\CIF::class,
        'es.nif' => \StdNum\Countries\ES\NIF::class,
        'us.ssn' => \StdNum\Countries\US\SSN::class,
        'us.ein' => \StdNum\Countries\US\EIN::class,
        'it.codicefiscale' => \StdNum\Countries\IT\CodiceFiscale::class,
        'it.iva' => \StdNum\Countries\IT\IVA::class,
        'fr.siren' => \StdNum\Countries\FR\SIREN::class,
        'fr.siret' => \StdNum\Countries\FR\SIRET::class,
        'fr.tva' => \StdNum\Countries\FR\TVA::class,
        'ca.sin' => \StdNum\Countries\CA\SIN::class,
        'ca.bn' => \StdNum\Countries\CA\BN::class,
        'pt.nif' => \StdNum\Countries\PT\NIF::class,
        'de.vat' => \StdNum\Countries\DE\VAT::class,
        'de.stnr' => \StdNum\Countries\DE\STNR::class,
        'au.abn' => \StdNum\Countries\AU\ABN::class,
        'au.acn' => \StdNum\Countries\AU\ACN::class,
        'au.tfn' => \StdNum\Countries\AU\TFN::class,
        'nl.brin' => \StdNum\Countries\NL\BRIN::class,
        'nl.bsn' => \StdNum\Countries\NL\BSN::class,
        'nl.btw' => \StdNum\Countries\NL\BTW::class,
        'nl.identiteitskaartnummer' => \StdNum\Countries\NL\Identiteitskaartnummer::class,
        'nl.onderwijsnummer' => \StdNum\Countries\NL\Onderwijsnummer::class,
        'nl.postcode' => \StdNum\Countries\NL\Postcode::class,
        'no.fodselsnummer' => \StdNum\Countries\NO\Fodselsnummer::class,
        'no.kontonr' => \StdNum\Countries\NO\KontoNr::class,
        'no.mva' => \StdNum\Countries\NO\MVA::class,
        'no.orgnr' => \StdNum\Countries\NO\OrgNr::class,
        'nz.bankaccount' => \StdNum\Countries\NZ\BankAccount::class,
        'nz.ird' => \StdNum\Countries\NZ\IRD::class,
        'pe.cui' => \StdNum\Countries\PE\CUI::class,
        'pe.ruc' => \StdNum\Countries\PE\RUC::class,
        'pl.nip' => \StdNum\Countries\PL\NIP::class,
        'pl.pesel' => \StdNum\Countries\PL\PESEL::class,
        'pl.regon' => \StdNum\Countries\PL\REGON::class,
        'pt.cc' => \StdNum\Countries\PT\CC::class,
        'pt.nif' => \StdNum\Countries\PT\NIF::class,
        'py.ruc' => \StdNum\Countries\PY\RUC::class,
        'ro.cf' => \StdNum\Countries\RO\CF::class,
        'ro.cnp' => \StdNum\Countries\RO\CNP::class,
        'ro.cui' => \StdNum\Countries\RO\CUI::class,
        'ro.onrc' => \StdNum\Countries\RO\ONRC::class,
        'rs.pib' => \StdNum\Countries\RS\PIB::class,
        'ru.inn' => \StdNum\Countries\RU\INN::class,
        'ru.ogrn' => \StdNum\Countries\RU\OGRN::class,
        'se.orgnr' => \StdNum\Countries\SE\Orgnr::class,
        'se.personnummer' => \StdNum\Countries\SE\Personnummer::class,
        'se.vat' => \StdNum\Countries\SE\VAT::class,
        'sg.uen' => \StdNum\Countries\SG\UEN::class,
        'si.ddv' => \StdNum\Countries\SI\DDV::class,
        'si.emso' => \StdNum\Countries\SI\EMSO::class,
        'sk.dph' => \StdNum\Countries\SK\DPH::class,
        'sk.rc' => \StdNum\Countries\SK\RC::class,
        'sm.coe' => \StdNum\Countries\SM\COE::class,
        'sv.nit' => \StdNum\Countries\SV\NIT::class,
        'tr.tckimlik' => \StdNum\Countries\TR\TCKimlik::class,
        'tr.vkn' => \StdNum\Countries\TR\VKN::class,
        'ua.edrpou' => \StdNum\Countries\UA\EDRPOU::class,
        'us.atin' => \StdNum\Countries\US\ATIN::class,
        'us.ein' => \StdNum\Countries\US\EIN::class,
        'us.itin' => \StdNum\Countries\US\ITIN::class,
        'us.ptin' => \StdNum\Countries\US\PTIN::class,
        'us.rtn' => \StdNum\Countries\US\RTN::class,
        'us.ssn' => \StdNum\Countries\US\SSN::class,
        'uy.rut' => \StdNum\Countries\UY\RUT::class,
        've.rif' => \StdNum\Countries\VE\RIF::class,
        'za.id' => \StdNum\Countries\ZA\ID::class,
        'za.tin' => \StdNum\Countries\ZA\TIN::class,
        'be.nn' => \StdNum\Countries\BE\NN::class,
        'be.vat' => \StdNum\Countries\BE\VAT::class,
        'ad.nrt' => \StdNum\Countries\AD\NRT::class,
        'al.nipt' => \StdNum\Countries\AL\NIPT::class,
        'ar.cuit' => \StdNum\Countries\AR\CUIT::class,
        'ar.dni' => \StdNum\Countries\AR\DNI::class,
        'ar.cbu' => \StdNum\Countries\AR\CBU::class,
        'at.uid' => \StdNum\Countries\AT\UID::class,
        'at.tin' => \StdNum\Countries\AT\TIN::class,
        'az.voen' => \StdNum\Countries\AZ\VOEN::class,
        'bg.egn' => \StdNum\Countries\BG\EGN::class,
        'bg.pnf' => \StdNum\Countries\BG\PNF::class,
        'bg.vat' => \StdNum\Countries\BG\VAT::class,
        'by.unp' => \StdNum\Countries\BY\UNP::class,
        'ch.ssn' => \StdNum\Countries\CH\SSN::class,
        'ch.uid' => \StdNum\Countries\CH\UID::class,
        'ch.vat' => \StdNum\Countries\CH\VAT::class,
        'cl.rut' => \StdNum\Countries\CL\RUT::class,
        'cn.ric' => \StdNum\Countries\CN\RIC::class,
        'cn.uscc' => \StdNum\Countries\CN\USCC::class,
        'co.nit' => \StdNum\Countries\CO\NIT::class,
        'cr.cpf' => \StdNum\Countries\CR\CPF::class,
        'cr.cpj' => \StdNum\Countries\CR\CPJ::class,
        'cr.cr' => \StdNum\Countries\CR\CR::class,
        'cu.ni' => \StdNum\Countries\CU\NI::class,
        'cy.vat' => \StdNum\Countries\CY\VAT::class,
        'cz.rc' => \StdNum\Countries\CZ\RC::class,
        'cz.dic' => \StdNum\Countries\CZ\DIC::class,
        'dk.cpr' => \StdNum\Countries\DK\CPR::class,
        'dk.cvr' => \StdNum\Countries\DK\CVR::class,
        'do.rnc' => \StdNum\Countries\DO\RNC::class,
        'do.cedula' => \StdNum\Countries\DO\CEDULA::class,
        'ec.ci' => \StdNum\Countries\EC\CI::class,
        'ec.ruc' => \StdNum\Countries\EC\RUC::class,
        'ee.ik' => \StdNum\Countries\EE\IK::class,
        'ee.kmkr' => \StdNum\Countries\EE\KMKR::class,
        'ee.registrikood' => \StdNum\Countries\EE\REGISTRIKOOD::class,
        'fi.alv' => \StdNum\Countries\FI\ALV::class,
        'fi.hetu' => \StdNum\Countries\FI\HETU::class,
        'fi.ytunnus' => \StdNum\Countries\FI\YTUNNUS::class,
        'gr.amka' => \StdNum\Countries\GR\AMKA::class,
        'gr.vat' => \StdNum\Countries\GR\VAT::class,
        'gt.nit' => \StdNum\Countries\GT\NIT::class,
        'id.nik' => \StdNum\Countries\ID\NIK::class,
        'id.npwp' => \StdNum\Countries\ID\NPWP::class,
        'ie.pps' => \StdNum\Countries\IE\PPS::class,
        'ie.vat' => \StdNum\Countries\IE\VAT::class,
        'il.hp' => \StdNum\Countries\IL\HP::class,
        'il.idnr' => \StdNum\Countries\IL\IDNR::class,
        'in.aadhaar' => \StdNum\Countries\IN\AADHAAR::class,
        'in.epic' => \StdNum\Countries\IN\EPIC::class,
        'in.pan' => \StdNum\Countries\IN\PAN::class,
        'is.kennitala' => \StdNum\Countries\IS\KENNITALA::class,
        'is.vsk' => \StdNum\Countries\IS\VSK::class,
        'it.codicefiscale' => \StdNum\Countries\IT\CODICEFISCALE::class,
        'it.iva' => \StdNum\Countries\IT\IVA::class,
        'jp.cn' => \StdNum\Countries\JP\CN::class,
        'kr.brn' => \StdNum\Countries\KR\BRN::class,
        'kr.rrn' => \StdNum\Countries\KR\RRN::class,
        'lt.pvm' => \StdNum\Countries\LT\PVM::class,
        'lt.asmens' => \StdNum\Countries\LT\ASMENS::class,
        'lu.tva' => \StdNum\Countries\LU\TVA::class,
        'lv.pvn' => \StdNum\Countries\LV\PVN::class,
        'mc.tva' => \StdNum\Countries\MC\TVA::class,
        'md.idno' => \StdNum\Countries\MD\IDNO::class,
        'me.pib' => \StdNum\Countries\ME\PIB::class,
        'mk.edb' => \StdNum\Countries\MK\EDB::class,
        'mt.vat' => \StdNum\Countries\MT\VAT::class,
        'mu.nid' => \StdNum\Countries\MU\NID::class,
        'mx.curp' => \StdNum\Countries\MX\CURP::class,
        'mx.rfc' => \StdNum\Countries\MX\RFC::class,
        'my.nric' => \StdNum\Countries\MY\NRIC::class,
    ];

    /**
     * Retrieves an instance of a specific document validator.
     *
     * @param string $type The document type (e.g. 'br.cpf')
     * @return DocumentInterface
     * @throws UnsupportedDocumentTypeException
     */
    public static function make(string $type): DocumentInterface
    {
        $type = strtolower(trim($type));

        if (!isset(self::$map[$type])) {
            throw new UnsupportedDocumentTypeException($type);
        }

        $class = self::$map[$type];
        return new $class();
    }

    /**
     * Shortcut to quickly validate and get a result.
     */
    public static function validate(string $type, string $number): Models\ValidationResult
    {
        return self::make($type)->validate($number);
    }

    /**
     * Shortcut to quickly check if a number is valid.
     */
    public static function isValid(string $type, string $number): bool
    {
        return self::make($type)->isValid($number);
    }

    /**
     * Allow registering new validators via macro or at runtime.
     */
    public static function register(string $type, string $className): void
    {
        self::$map[strtolower(trim($type))] = $className;
    }
}
