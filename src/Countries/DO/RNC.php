<?php
namespace StdNum\Countries\DO;

use StdNum\Contracts\DocumentInterface;
use StdNum\Models\ValidationResult;
use StdNum\Traits\Cleanable;

class RNC implements DocumentInterface
{
    use Cleanable;

    private $whitelist = [
        '101581601', '101582245', '101595422', '101595785', '10233317', '131188691', '401007374',
        '501341601', '501378067', '501620371', '501651319', '501651823', '501651845', '501651926',
        '501656006', '501658167', '501670785', '501676936', '501680158', '504654542', '504680029',
        '504681442', '505038691'
    ];

    private function calcCheckDigit(string $number): string
    {
        $weights = [7, 9, 8, 6, 5, 4, 3, 2];
        $check = 0;
        for ($i = 0; $i < 8; $i++) {
            $check += $weights[$i] * (int)$number[$i];
        }
        $check = $check % 11;
        
        $mod = (10 - $check) % 9;
        if ($mod < 0) {
            $mod += 9;
        }
        return (string)($mod + 1);
    }

    public function validate(string $number): ValidationResult
    {
        $cleaned = $this->compact($number);

        if (!ctype_digit($cleaned)) {
            return ValidationResult::failure('Invalid format for RNC');
        }

        if (in_array($cleaned, $this->whitelist)) {
            return ValidationResult::success();
        }

        if (strlen($cleaned) !== 9) {
            return ValidationResult::failure('Invalid length for RNC');
        }

        if ($cleaned[8] !== $this->calcCheckDigit(substr($cleaned, 0, 8))) {
            return ValidationResult::failure('Invalid checksum for RNC');
        }

        return ValidationResult::success();
    }

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid;
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);
        if (strlen($compact) >= 9) {
            return substr($compact, 0, 1) . '-' . substr($compact, 1, 2) . '-' . substr($compact, 3, 5) . '-' . substr($compact, 8);
        }
        return $number;
    }

    public function compact(string $number): string
    {
        return trim(strtoupper(str_replace([' ', '-'], '', $number)));
    }

    public const DGII_WSDL = 'https://www.dgii.gov.do/wsMovilDGII/WSMovilDGII.asmx?WSDL';

    private function convertResult(string $result): array
    {
        $translation = [
            'RGE_RUC' => 'rnc',
            'RGE_NOMBRE' => 'name',
            'NOMBRE_COMERCIAL' => 'commercial_name',
            'CATEGORIA' => 'category',
            'REGIMEN_PAGOS' => 'payment_regime',
            'ESTATUS' => 'status',
            'RNUM' => 'result_number',
        ];
        
        $jsonStr = str_replace(["\n", "\t"], ["\\n", "\\t"], $result);
        $data = json_decode($jsonStr, true);
        if (!is_array($data)) {
            return [];
        }
        
        $out = [];
        foreach ($data as $key => $value) {
            $mappedKey = $translation[$key] ?? $key;
            $out[$mappedKey] = $value;
        }
        return $out;
    }

    public function checkDgii(string $number, array $options = []): ?array
    {
        if (!class_exists('\SoapClient')) {
            throw new \RuntimeException('The SOAP extension is required for DGII lookups.');
        }

        $number = $this->compact($number);
        
        $soapOptions = [
            'connection_timeout' => $options['timeout'] ?? 30,
        ];
        
        if (isset($options['verify']) && !$options['verify']) {
            $soapOptions['stream_context'] = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
        }
        
        try {
            $client = new \SoapClient(self::DGII_WSDL, $soapOptions);
            $result = $client->GetContribuyentes([
                'value' => $number,
                'patronBusqueda' => 0,
                'inicioFilas' => 1,
                'filaFilas' => 1,
                'IMEI' => ''
            ]);
            
            if (isset($result->GetContribuyentesResult)) {
                $resStr = $result->GetContribuyentesResult;
                if ($resStr === '0' || empty($resStr)) {
                    return null;
                }
                
                $parts = explode('@@@', $resStr);
                return $this->convertResult($parts[0]);
            }
        } catch (\SoapFault|\Exception $e) {
            return null;
        }
        return null;
    }

    public function searchDgii(string $keyword, int $startAt = 1, int $endAt = 10, array $options = []): array
    {
        if (!class_exists('\SoapClient')) {
            throw new \RuntimeException('The SOAP extension is required for DGII lookups.');
        }

        $soapOptions = [
            'connection_timeout' => $options['timeout'] ?? 30,
        ];
        
        if (isset($options['verify']) && !$options['verify']) {
            $soapOptions['stream_context'] = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
        }
        
        try {
            $client = new \SoapClient(self::DGII_WSDL, $soapOptions);
            $result = $client->GetContribuyentes([
                'value' => $keyword,
                'patronBusqueda' => 1,
                'inicioFilas' => $startAt,
                'filaFilas' => $endAt,
                'IMEI' => ''
            ]);
            
            if (isset($result->GetContribuyentesResult)) {
                $resStr = $result->GetContribuyentesResult;
                if ($resStr === '0' || empty($resStr)) {
                    return [];
                }
                
                $parts = explode('@@@', $resStr);
                $out = [];
                foreach ($parts as $part) {
                    if (!empty($part)) {
                        $out[] = $this->convertResult($part);
                    }
                }
                return $out;
            }
        } catch (\SoapFault|\Exception $e) {
            return [];
        }
        return [];
    }
}
