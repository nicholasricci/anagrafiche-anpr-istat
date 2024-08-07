<?php

namespace nicholasricci\AnagraficheANPRISTAT;

use ErrorException;
use nicholasricci\AnagraficheANPRISTAT\Converter\CitiesConverter;
use nicholasricci\AnagraficheANPRISTAT\Converter\CountriesConverter;
use nicholasricci\AnagraficheANPRISTAT\Converter\CountriesSuppressedConverter;
use nicholasricci\AnagraficheANPRISTAT\Converter\ProvincesConverter;
use nicholasricci\AnagraficheANPRISTAT\Converter\RegionsConverter;
use nicholasricci\AnagraficheANPRISTAT\Exception\UpdaterException;
use Devnix\ZipException\ZipException;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use ZipArchive;

final class Updater
{
    /**
     * @var string
     *
     * @link https://developers.italia.it/en/anpr
     * @license cc-by-3.0
     */
    protected const CITIES = 'https://www.anagrafenazionale.interno.it/wp-content/uploads/ANPR_archivio_comuni.csv';

    /**
     * @var string
     * @link https://www.istat.it
     * @license cc-by-4.0
     */
    protected const REGIONS_AND_PROVINCES = 'https://www.istat.it/storage/codici-unita-amministrative/Codici-statistici-e-denominazioni-delle-ripartizioni-sovracomunali.zip';

    /**
     * @var string
     * @link https://www.istat.it
     * @license cc-by-4.0
     */
    protected const COUNTRIES = 'https://www.istat.it/wp-content/uploads/2024/03/Elenco-codici-e-denominazioni-unita-territoriali-estere.zip';

    /**
     * @var string
     * @link https://www.istat.it
     * @license cc-by-4.0
     */
    protected const COUNTRIES_SUPPRESSED = 'https://www.istat.it/it/files//2011/01/Elenco-Paesi-esteri-cessati.zip';

    /**
     * @var string
     */
    protected const DESTINATION = __DIR__.'/../dist';

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $citiesPath;

    /**
     * @var string
     */
    protected $provincesPath;

    /**
     * @var string
     */
    protected $regionsPath;

    /**
     * @var string
     */
    protected $countriesPath;

    /**
     * @var string
     */
    protected $countriesSuppressedPath;

    /**
     * @throws ZipException
     * @throws ErrorException
     */
    public function __construct()
    {
        $this->serializer = new Serializer([], [new CsvEncoder(), new XmlEncoder(), new JsonEncoder()]);

        $this->citiesPath = $this->downloadTmpCities();
        $this->provincesPath = $this->downloadTmpProvinces();
        $this->regionsPath = $this->downloadTmpRegions();
        $this->countriesPath = $this->downloadTmpCountries();
        $this->countriesSuppressedPath = $this->downloadTmpCountriesSuppressed();
    }

    public function __destruct()
    {
        $this->removeTmpCities();
        $this->removeTmpProvinces();
        $this->removeTmpRegions();
        $this->removeTmpCountries();
        $this->removeTmpCountriesSuppressed();
    }

    /**
     * Fetch and update all the data
     */
    public function generateCities(): void
    {
        $this->createDistDirectory();

        $citiesConverter = new CitiesConverter($this->citiesPath);

        file_put_contents(self::DESTINATION.'/cities.csv', $citiesConverter->getCsv());
        file_put_contents(self::DESTINATION.'/cities.xml', $citiesConverter->getXml());
        file_put_contents(self::DESTINATION.'/cities.json', $citiesConverter->getJson());
        file_put_contents(self::DESTINATION.'/cities.yaml', $citiesConverter->getYaml());
    }

    /**
     * Fetch and update all the data
     */
    public function generateProvinces(): void
    {
        $this->createDistDirectory();

        $provincesConverter = new ProvincesConverter($this->provincesPath);

        file_put_contents(self::DESTINATION.'/provinces.csv', $provincesConverter->getCsv());
        file_put_contents(self::DESTINATION.'/provinces.xml', $provincesConverter->getXml());
        file_put_contents(self::DESTINATION.'/provinces.json', $provincesConverter->getJson());
        file_put_contents(self::DESTINATION.'/provinces.yaml', $provincesConverter->getYaml());
    }

    /**
     * Fetch and update all the data
     */
    public function generateRegions(): void
    {
        $this->createDistDirectory();

        $regionsConverter = new RegionsConverter($this->regionsPath);

        file_put_contents(self::DESTINATION.'/regions.csv', $regionsConverter->getCsv());
        file_put_contents(self::DESTINATION.'/regions.xml', $regionsConverter->getXml());
        file_put_contents(self::DESTINATION.'/regions.json', $regionsConverter->getJson());
        file_put_contents(self::DESTINATION.'/regions.yaml', $regionsConverter->getYaml());
    }

    public function generateCountries()
    {
        $this->createDistDirectory();

        $countriesConverter = new CountriesConverter($this->countriesPath);

        file_put_contents(self::DESTINATION.'/countries.csv', $countriesConverter->getCsv());
        file_put_contents(self::DESTINATION.'/countries.xml', $countriesConverter->getXml());
        file_put_contents(self::DESTINATION.'/countries.json', $countriesConverter->getJson());
        file_put_contents(self::DESTINATION.'/countries.yaml', $countriesConverter->getYaml());
    }

    public function generateCountriesSuppressed()
    {
        $this->createDistDirectory();

        $countriesConverter = new CountriesSuppressedConverter($this->countriesSuppressedPath);

        file_put_contents(self::DESTINATION.'/countries_suppressed.csv', $countriesConverter->getCsv());
        file_put_contents(self::DESTINATION.'/countries_suppressed.xml', $countriesConverter->getXml());
        file_put_contents(self::DESTINATION.'/countries_suppressed.json', $countriesConverter->getJson());
        file_put_contents(self::DESTINATION.'/countries_suppressed.yaml', $countriesConverter->getYaml());
    }

    protected function createDistDirectory(): void
    {
        if (!is_dir(self::DESTINATION)) {
            ErrorHandler::call('mkdir', self::DESTINATION);
        }
    }

    protected function downloadTmpCities(): string
    {
        return ErrorHandler::call(static function () {
            $tmpPath = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-cities.csv.');

            file_put_contents($tmpPath, file_get_contents(self::CITIES));

            return $tmpPath;
        });
    }

    /**
     * @throws ZipException
     * @throws ErrorException
     */
    protected function downloadTmpProvinces(): string
    {
        return ErrorHandler::call(static function () {
            $tmpZip = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-provinces.zip.');
            $tmpPath = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-provinces.xlsx.');

            file_put_contents($tmpZip, file_get_contents(self::REGIONS_AND_PROVINCES));

            $zip = new ZipArchive();

            if (!$zipStatus = $zip->open($tmpZip)) {
                throw new ZipException($zipStatus);
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ('xls' === (pathinfo($zip->statIndex($i)['name'])['extension'] ?? null)) {
                    file_put_contents($tmpPath, $zip->getFromName($zip->statIndex($i)['name']));
                    return $tmpPath;
                }
            }

            throw new UpdaterException('Could not find the xlsx file inside of the downloaded provinces zip');
        });
    }

    /**
     * @throws ZipException
     * @throws ErrorException
     */
    protected function downloadTmpRegions(): string
    {
        return ErrorHandler::call(static function () {
            $tmpZip = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-regions.zip.');
            $tmpPath = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-regions.xlsx.');

            file_put_contents($tmpZip, file_get_contents(self::REGIONS_AND_PROVINCES));

            $zip = new ZipArchive();

            if (!$zipStatus = $zip->open($tmpZip)) {
                throw new ZipException($zipStatus);
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ('xls' === (pathinfo($zip->statIndex($i)['name'])['extension'] ?? null)) {
                    file_put_contents($tmpPath, $zip->getFromName($zip->statIndex($i)['name']));
                    return $tmpPath;
                }
            }

            throw new UpdaterException('Could not find the xlsx file inside of the downloaded regions zip');
        });
    }

    /**
     * @throws ZipException
     * @throws ErrorException
     */
    protected function downloadTmpCountries(): string
    {
        return ErrorHandler::call(static function () {
            $tmpZip = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-countries.zip.');
            $tmpPath = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-countries.xlsx.');

            file_put_contents($tmpZip, file_get_contents(self::COUNTRIES));

            $zip = new ZipArchive();

            if (!$zipStatus = $zip->open($tmpZip)) {
                throw new ZipException($zipStatus);
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ('xlsx' === (pathinfo($zip->statIndex($i)['name'])['extension'] ?? null)) {
                    file_put_contents($tmpPath, $zip->getFromName($zip->statIndex($i)['name']));
                    return $tmpPath;
                }
            }

            throw new UpdaterException('Could not find the xlsx file inside of the downloaded countries zip');
        });
    }

    /**
     * @throws ZipException
     * @throws ErrorException
     */
    protected function downloadTmpCountriesSuppressed(): string
    {
        return ErrorHandler::call(static function () {
            $tmpZip = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-countries_suppressed.zip.');
            $tmpPath = tempnam(sys_get_temp_dir(), 'nicholasricci-anagrafiche-anpr-istat-countries_suppressed.xlsx.');

            file_put_contents($tmpZip, file_get_contents(self::COUNTRIES_SUPPRESSED));

            $zip = new ZipArchive();

            if (!$zipStatus = $zip->open($tmpZip)) {
                throw new ZipException($zipStatus);
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ('xlsx' === (pathinfo($zip->statIndex($i)['name'])['extension'] ?? null)) {
                    file_put_contents($tmpPath, $zip->getFromName($zip->statIndex($i)['name']));
                    return $tmpPath;
                }
            }

            throw new UpdaterException('Could not find the xlsx file inside of the downloaded countries suppressed zip');
        });
    }

    protected function removeTmpCities(): void
    {
        if (empty($this->citiesPath)) {
            return;
        }

        try {
            ErrorHandler::call('unlink', $this->citiesPath);
        } catch (\Exception $e) {};

        unset($this->citiesPath);
    }

    protected function removeTmpProvinces(): void
    {
        if (empty($this->provincesPath)) {
            return;
        }

        try {
            ErrorHandler::call('unlink', $this->provincesPath);
        } catch (\Exception $e) {};

        unset($this->provincesPath);
    }

    protected function removeTmpRegions(): void
    {
        if (empty($this->regionsPath)) {
            return;
        }

        try {
            ErrorHandler::call('unlink', $this->regionsPath);
        } catch (\Exception $e) {};

        unset($this->regionsPath);
    }

    protected function removeTmpCountries(): void
    {
        if (empty($this->countriesPath)) {
            return;
        }

        try {
            ErrorHandler::call('unlink', $this->countriesPath);
        } catch (\Exception $e) {};

        unset($this->countriesPath);
    }

    protected function removeTmpCountriesSuppressed(): void
    {
        if (empty($this->countriesSuppressedPath)) {
            return;
        }

        try {
            ErrorHandler::call('unlink', $this->countriesSuppressedPath);
        } catch (\Exception $e) {};

        unset($this->countriesSuppressedPath);
    }
}
