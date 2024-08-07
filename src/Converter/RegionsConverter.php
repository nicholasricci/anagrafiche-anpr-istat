<?php

namespace nicholasricci\AnagraficheANPRISTAT\Converter;

use PhpOffice\PhpSpreadsheet;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Serializer;

class RegionsConverter extends AbstractConverter
{
    /**
     * @var array
     */
    protected const COLUMN_MAPPING = [
        'NUTS1_2010' => 'eurostat_code_ripartizione_2010',
        'NUTS1_2021' => 'eurostat_code_ripartizione_2021',
        'COD_RIP ' => 'istat_code_ripartizione',
        "DEN_RIP\n(Maiuscolo)" => 'istat_denominazione_ripartizione_maiuscolo',
        'DEN_RIP' => 'istat_denominazione_ripartizione_camel',
        'NUTS2_2010' => 'eurostat_code_regione_2010',
        'NUTS2_2021' => 'eurostat_code_regione_2021',
        'COD_REG' => 'istat_code_reg',
        "DEN_REG\n(Maiuscolo)" => 'istat_denominazione_regione_maiuscolo',
        "DEN_REG" => 'istat_denominazione_regione_camel',
        "TIPO_REG" => 'istat_regione_tipo',
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
    protected $regions;

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
        $this->regions = $this->setKeys($header, $rawData);

        $this->regions = $this->convertValues($this->regions);
    }

    /**
     * Rename the columns
     */
    public function convertColumns(array $regions): array
    {
        foreach ($regions as $key => $value) {
            if (in_array($value, array_keys(self::COLUMN_MAPPING))) {
                $regions[$key] = self::COLUMN_MAPPING[$value] ?? $value;
            }else{
                unset($regions[$key]);
            }
        }

        return $regions;
    }

    /**
     * Convert all values using the converted columns
     */
    public function convertValues(array $regions): array
    {
        foreach ($regions as $regionKey => $regionValue) {
            foreach (self::VALUE_MAPPING as $column => $values) {
                foreach ($values as $oldValue => $newValue) {
                    if ($regions[$regionKey][$column] == $oldValue) {
                        $regions[$regionKey][$column] = $newValue;
                        break;
                    }
                }
            }
            // Remove empty keys due to a possible bug in phpspreadsheet
            $regions[$regionKey] = array_filter($regions[$regionKey], function($value) {
                return !is_null($value) && $value !== '';
            }, ARRAY_FILTER_USE_KEY);
        }

        return $regions;
    }


    public function setKeys(array $header, array $data): array
    {
        $lettersHeader = array_keys($header);
        foreach ($data as $position => $region) {
            foreach ($region as $letter => $value) {
                if (!in_array($letter, $lettersHeader)) {
                    unset($region[$letter]);
                }
            }
            $data[$position] = array_combine($header, $region);
        }

        return $data;
    }

    public function getData(): array
    {
        return $this->regions;
    }
}
