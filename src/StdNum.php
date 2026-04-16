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
        'nl.bsn' => \StdNum\Countries\NL\BSN::class,
        'nl.btw' => \StdNum\Countries\NL\BTW::class,
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
