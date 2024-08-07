<?php

namespace nicholasricci\AnagraficheANPRISTAT\Converter;

use PhpOffice\PhpSpreadsheet;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Serializer;

class ProvincesConverter extends AbstractConverter
{
    /**
     * @var array
     */
    protected const COLUMN_MAPPING = [
        'NUTS3_2010' => 'eurostat_code_2010',
        'NUTS3_2021' => 'eurostat_code_2021',
        'COD_UTS_AM' => 'istat_code_provincia_attuale',
        'COD_UTS_ST' => 'istat_code_provincia_statistico',
        'DEN_UTS' => 'istat_nome',
        'TIPO_UTS' => 'istat_tipo',
        'Sigla automobilistica' => 'motorizzazione_sigla_automobilistica',
        'COD_PROV_Storico' => 'istat_code_provincia_storico',
        'DEN_RIP' => 'ripartizione_geografica',
        'COD_REG' => 'istat_code_regione',
    ];

    /**
     * @var array
     */
    protected const VALUE_MAPPING = [
//        'region_type' => [
//            'S' => 'state',
//            'T' => 'territory',
//        ],
    ];

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $provinces;

    public function __construct(string $path)
    {
        parent::__construct();

        $this->path = $path;

        $xlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $xlsxReader->setReadDataOnly(true)->setReadEmptyCells(false);
        $spreadsheet = $xlsxReader->load($this->path);

        $rawData = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

        // two times because active sheet has 2 header rows
        array_shift($rawData);
        $header = array_shift($rawData);

        $header = $this->convertColumns($header);
        $this->provinces = $this->setKeys($header, $rawData);

        $this->provinces = $this->convertValues($this->provinces);
    }

    /**
     * Rename the columns
     */
    public function convertColumns(array $provinces): array
    {
        foreach ($provinces as $key => $value) {
            if (in_array($value, array_keys(self::COLUMN_MAPPING))) {
                $provinces[$key] = self::COLUMN_MAPPING[$value] ?? $value;
            }else{
                unset($provinces[$key]);
            }
        }

        return $provinces;
    }

    /**
     * Convert all values using the converted columns
     */
    public function convertValues(array $provinces): array
    {
        foreach ($provinces as $provinceKey => $provinceValue) {
            foreach (self::VALUE_MAPPING as $column => $values) {
                foreach ($values as $oldValue => $newValue) {
                    if ($provinces[$provinceKey][$column] == $oldValue) {
                        $provinces[$provinceKey][$column] = $newValue;
                        break;
                    }
                }
            }
            // Remove empty keys due to a possible bug in phpspreadsheet
            $provinces[$provinceKey] = array_filter($provinces[$provinceKey], function($value) {
                return !is_null($value) && $value !== '';
            }, ARRAY_FILTER_USE_KEY);
        }

        return $provinces;
    }


    public function setKeys(array $header, array $data): array
    {
        $lettersHeader = array_keys($header);
        foreach ($data as $position => $province) {
            foreach ($province as $letter => $value) {
                if (!in_array($letter, $lettersHeader)) {
                    unset($province[$letter]);
                }
            }
            $data[$position] = array_combine($header, $province);
        }

        return $data;
    }

    public function getData(): array
    {
        return $this->provinces;
    }
}
