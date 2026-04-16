# stdnum-php

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE.md)

**stdnum-php** is a modern, framework-agnostic PHP 8.1+ library designed for parsing, formatting, and validating standard numbers and alphanumeric identifiers. 
This project is an architectural port inspired by the robust `python-stdnum` library, featuring an object-oriented and extensible design structure natively built for the PHP ecosystem.

## Features

- **Extensive Coverage**: Supports over 50 international identification formats spanning more than 24 countries.
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
* 🇨🇷 **Costa Rica**: CPF, CPJ, CR (DIMEX)
* 🇩🇪 **Germany**: VAT, STNR
* 🇪🇸 **Spain**: DNI, NIE, CIF, NIF
* 🇫🇷 **France**: SIREN, SIRET, TVA
* 🇬🇧 **United Kingdom**: NINO, UTR, VAT
* 🇮🇹 **Italy**: Codice Fiscale, IVA
* 🇳🇱 **Netherlands**: BSN, BTW
* 🇵🇹 **Portugal**: NIF
* 🇺🇸 **United States**: SSN, EIN

## Testing

The project incorporates PHPUnit. All formulas and checksums are deeply tested over boundary conditions spanning hundreds of assertions mirroring their native environments.

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
