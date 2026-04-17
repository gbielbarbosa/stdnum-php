# stdnum-php

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE.md)

**stdnum-php** is a modern, framework-agnostic PHP 8.1+ library designed for parsing, formatting, and validating standard numbers and alphanumeric identifiers. 
This project is an architectural port inspired by the robust `python-stdnum` library, featuring an object-oriented and extensible design structure natively built for the PHP ecosystem.

## Features

- **Extensive Coverage**: Supports 106 international identification formats spanning across 71 countries.
- **Fluent & Static Resolution**: Simple dot-notation to dynamically map specific validators (e.g. `br.cpf`, `us.ssn`, `cn.uscc`).
- **Laravel Integration Ready**: Includes a native generic rule `StdNumRule` that tightly integrates into Laravel's FormRequests and validators.
- **Standardized Interfaces**: All validators rigorously adhere to a single `DocumentInterface` contract implementing `validate()`, `isValid()`, `format()`, and `compact()`.

## Installation

You can install the package via composer:

```bash
composer require gbielbarbosa/stdnum-php
```

## Basic Usage

The library acts through a central `StdNum` factory or you can instance the classes independently.

### Pure PHP

You can quickly evaluate identifiers using the dot notation format `[iso-code].[identifier-type]`.

```php
use StdNum\StdNum;

// Returns boolean indicating if the identifier is valid
$isValid = StdNum::isValid('br.cpf', '390.533.447-05'); // true

// Returns a rich ValidationResult Object
$result = StdNum::validate('ch.uid', 'CHE-100.155.213');

if (! $result->isValid) {
    echo $result->error; // "Invalid checksum for Swiss UID"
}
```

### Laravel Usage

The package comes bundled with `StdNumRule` for seamless Laravel integration:

```php
use StdNum\Laravel\Rules\StdNumRule;
use Illuminate\Http\Request;

public function store(Request $request) 
{
    $request->validate([
        'cuit' => ['required', new StdNumRule('ar.cuit')],
        'nif'  => ['required', new StdNumRule('es.nif')],
    ]);
}
```

### Formatting Identifiers

In addition to checks, each format provides natively tailored presentation routines:

```php
$validator = StdNum::make('cl.rut');
echo $validator->format('125319092'); // Outputs: "12.531.909-2"
echo $validator->compact('12.531.909-2'); // Outputs: "125319092"
```

## Supported Formats

Currently supported and rigorously tested identifier types include:

* 🇦🇩 **Andorra**: NRT
* 🇦🇱 **Albania**: NIPT
* 🇦🇷 **Argentina**: CUIT, DNI, CBU
* 🇦🇹 **Austria**: UID, TIN
* 🇦🇺 **Australia**: ABN, ACN, TFN
* 🇦🇿 **Azerbaijan**: VOEN
* 🇧🇪 **Belgium**: NN, VAT
* 🇧🇬 **Bulgaria**: EGN, PNF, VAT
* 🇧🇾 **Belarus**: UNP
* 🇧🇷 **Brazil**: CPF, CNPJ
* 🇨🇦 **Canada**: SIN, BN
* 🇨🇭 **Switzerland**: SSN, UID, VAT
* 🇨🇱 **Chile**: RUT
* 🇨🇳 **China**: RIC, USCC
* 🇨🇴 **Colombia**: NIT
* 🇨🇷 **Costa Rica**: CPF, CPJ, CR
* 🇨🇺 **Cuba**: NI
* 🇨🇾 **Cyprus**: VAT
* 🇨🇿 **Czech Republic**: DIC, RC
* 🇩🇪 **Germany**: STNR, VAT
* 🇩🇰 **Denmark**: CPR, CVR
* 🇩🇴 **Dominican Republic**: CEDULA, RNC
* 🇪🇨 **Ecuador**: CI, RUC
* 🇪🇪 **Estonia**: IK, KMKR, REGISTRIKOOD
* 🇪🇸 **Spain**: CIF, DNI, NIE, NIF
* 🇫🇮 **Finland**: ALV, HETU, YTUNNUS
* 🇫🇷 **France**: SIREN, SIRET, TVA
* 🇬🇧 **United Kingdom**: NINO, UTR, VAT
* 🇬🇷 **Greece**: AMKA, VAT
* 🇬🇹 **Guatemala**: NIT
* 🇮🇩 **Indonesia**: NIK, NPWP
* 🇮🇪 **Ireland**: PPS, VAT
* 🇮🇱 **Israel**: HP, IDNR
* 🇮🇳 **India**: AADHAAR, EPIC, PAN
* 🇮🇸 **Iceland**: KENNITALA, VSK
* 🇮🇹 **Italy**: CodiceFiscale, IVA
* 🇯🇵 **Japan**: CN
* 🇰🇷 **South Korea**: BRN, RRN
* 🇱🇹 **Lithuania**: ASMENS, PVM
* 🇱🇺 **Luxembourg**: TVA
* 🇱🇻 **Latvia**: PVN
* 🇲🇨 **Monaco**: TVA
* 🇲🇩 **Moldova**: IDNO
* 🇲🇪 **Montenegro**: PIB
* 🇲🇰 **North Macedonia**: EDB
* 🇲🇹 **Malta**: VAT
* 🇲🇺 **Mauritius**: NID
* 🇲🇽 **Mexico**: CURP, RFC
* 🇲🇾 **Malaysia**: NRIC
* 🇳🇱 **Netherlands**: BRIN, BSN, BTW, Identiteitskaartnummer, Onderwijsnummer, Postcode
* 🇳🇴 **Norway**: Fodselsnummer, KontoNr, MVA, OrgNr
* 🇳🇿 **New Zealand**: BankAccount, IRD
* 🇵🇪 **Peru**: CUI, RUC
* 🇵🇱 **Poland**: NIP, PESEL, REGON
* 🇵🇹 **Portugal**: CC, NIF
* 🇵🇾 **Paraguay**: RUC
* 🇷🇴 **Romania**: CF, CNP, CUI, ONRC
* 🇷🇸 **Serbia**: PIB
* 🇷🇺 **Russia**: INN, OGRN
* 🇸🇪 **Sweden**: Orgnr, Personnummer, VAT
* 🇸🇬 **Singapore**: UEN
* 🇸🇮 **Slovenia**: DDV, EMSO
* 🇸🇰 **Slovakia**: DPH, RC
* 🇸🇲 **San Marino**: COE
* 🇸🇻 **El Salvador**: NIT
* 🇹🇷 **Turkey**: TCKimlik, VKN
* 🇺🇦 **Ukraine**: EDRPOU
* 🇺🇸 **United States**: ATIN, EIN, ITIN, PTIN, RTN, SSN
* 🇺🇾 **Uruguay**: RUT
* 🇻🇪 **Venezuela**: RIF
* 🇿🇦 **South Africa**: ID, TIN

## Testing

The project incorporates PHPUnit. All formulas and checksums are deeply tested over boundary conditions spanning hundreds of assertions mirroring their native environments.

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
