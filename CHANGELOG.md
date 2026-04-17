# Changelog

All notable changes to `stdnum-php` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), 
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-04-17

### Added
- Mathematical algorithms support including `Verhoeff` and pure math implementations.
- Expanded the library by implementing 82 additional identifier formats across 47 new and existing countries:
  - **CU**: NI
  - **CY**: VAT
  - **CZ**: DIC, RC
  - **DK**: CPR, CVR
  - **DO**: CEDULA, RNC
  - **EC**: CI, RUC
  - **EE**: IK, KMKR, REGISTRIKOOD
  - **FI**: ALV, HETU, YTUNNUS
  - **GR**: AMKA, VAT
  - **GT**: NIT
  - **ID**: NIK, NPWP
  - **IE**: PPS, VAT
  - **IL**: HP, IDNR
  - **IN**: AADHAAR, EPIC, PAN
  - **IS**: KENNITALA, VSK
  - **JP**: CN
  - **KR**: BRN, RRN
  - **LT**: ASMENS, PVM
  - **LU**: TVA
  - **LV**: PVN
  - **MC**: TVA
  - **MD**: IDNO
  - **ME**: PIB
  - **MK**: EDB
  - **MT**: VAT
  - **MU**: NID
  - **MX**: CURP, RFC
  - **MY**: NRIC
  - **NL**: BRIN, Identiteitskaartnummer, Onderwijsnummer, Postcode
  - **NO**: Fodselsnummer, KontoNr, MVA, OrgNr
  - **NZ**: BankAccount, IRD
  - **PE**: CUI, RUC
  - **PL**: NIP, PESEL, REGON
  - **PT**: CC
  - **PY**: RUC
  - **RO**: CF, CNP, CUI, ONRC
  - **RS**: PIB
  - **RU**: INN, OGRN
  - **SE**: Orgnr, Personnummer, VAT
  - **SG**: UEN
  - **SI**: DDV, EMSO
  - **SK**: DPH, RC
  - **SM**: COE
  - **SV**: NIT
  - **TR**: TCKimlik, VKN
  - **UA**: EDRPOU
  - **US**: ATIN, ITIN, PTIN, RTN
  - **UY**: RUT
  - **VE**: RIF
  - **ZA**: ID, TIN

### Fixed
- Addressed memory safety and integer overflow with algorithms scaling dynamically using `bcmath` operations where appropriate.

## [1.0.0] - 2026-04-16

### Added
- Created the core architecture implementing `DocumentInterface` and `ValidationResult`.
- Central registry `StdNum` supporting concise dot-notation mapping (`make`, `isValid`, `validate`).
- Trait-based toolkit helpers ensuring minimal verbosity (`Cleanable`, `LuhnChecksum`, `Mod97_10`).
- Seamless standard Laravel integration mapping rule `StdNumRule`.
- Centralized extensive `tests/StdNumTest.php` suite covering thousands of conditional checks across robust assertions.
- Implemented and ported native identifier validators targeting 24 distinct countries crossing the Americas, Europe and East Asia:
  - **AD**: NRT
  - **AL**: NIPT
  - **AR**: CUIT, DNI, CBU
  - **AT**: UID, TIN
  - **AU**: ABN, ACN, TFN
  - **AZ**: VOEN
  - **BE**: NN, VAT
  - **BG**: EGN, PNF, VAT
  - **BR**: CPF, CNPJ
  - **BY**: UNP
  - **CA**: SIN, BN
  - **CH**: SSN, UID, VAT
  - **CL**: RUT
  - **CN**: RIC, USCC
  - **CO**: NIT
  - **CR**: CPF, CPJ, CR 
  - **DE**: VAT, STNR
  - **ES**: DNI, NIE, CIF, NIF
  - **FR**: SIREN, SIRET, TVA
  - **GB**: NINO, UTR, VAT
  - **IT**: Codice Fiscale, IVA
  - **NL**: BSN, BTW
  - **PT**: NIF
  - **US**: SSN, EIN
