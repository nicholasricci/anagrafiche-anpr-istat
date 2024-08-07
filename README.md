# nicholasricci/anagrafiche-anpr-istat

Official Italian Belfiore code list (cadastral code) which represents a 
[comune](https://en.wikipedia.org/wiki/Comune).

## Update

To update the database, you have to clone the project and install the
`dev-dependencies`. Then, the console will be available to download and dump 
the normalized datasets:

```
composer install
php -d memory_limit=-1 bin/console update
```

and you are golden.

# Usage

## Installation

### PHP

```
composer require nicholasricci/anagrafiche-anpr-istat
```

## Serialized data

You can get the up to date serialized database of comunes and
foreign regions in CSV, JSON, XML and YAML inside the `dist/` folder in any
language.

## API

### PHP

There is a `nicholasricci\AnagraficheANPRISTAT\Collection\ComuneCollection` and a 
`nicholasricci\AnagraficheANPRISTAT\BelfioreCode\Collection\RegionCollection` to get an `ArrayCollection`
filled with both databases. This enables you to directly iterate through them
like an array, or even perform queries of columns.

#### Querying

You can fetch a comune by its `registry_code` (also know as  *cadastral code* or
*belfiore code*).

```php
<?php

use nicholasricci\AnagraficheANPRISTAT\Collection\ComuneCollection;
use nicholasricci\AnagraficheANPRISTAT\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

$comunes = new ComuneCollection();

$criteria = new Criteria();
$criteria
    ->where(new Comparison('registry_code', Comparison::IS, 'A001'))
;

var_dump($comunes->matching($criteria)));
```

This would get you a new collection with the matching registry codes. As you can
see there is a comune called "ABANO" discontinued in 1924-11-13, and an active
comune called "ABANO TERME":
```
object(nicholasricci\AnagraficheANPRISTAT\Collection\ComuneCollection)#49 (1) {
  ["elements":"Doctrine\Common\Collections\ArrayCollection":private]=>
  array(2) {
    [0]=>
    array(18) {
      ["id"]=>
      string(5) "12560"
      ["institution_date"]=>
      string(10) "1866-11-19"
      ["end_date"]=>
      string(10) "1924-11-13"
      ["istat_id"]=>
      string(6) "028001"
      ["registry_code"]=>
      string(4) "A001"
      ["name_it"]=>
      string(5) "ABANO"
      ["name_transliterated"]=>
      string(5) "ABANO"
      ["alternative_name"]=>
      string(0) ""
      ["alternative_name_transliterated"]=>
      string(0) ""
      ["anpr_id"]=>
      string(2) "28"
      ["istat_province_id"]=>
      string(3) "028"
      ["istat_region_id"]=>
      string(2) "05"
      ["istat_prefecture_id"]=>
      string(0) ""
      ["status"]=>
      string(12) "discontinued"
      ["provincial_code"]=>
      string(2) "PD"
      ["source"]=>
      string(0) ""
      ["last_update"]=>
      string(10) "2016-06-17"
      ["istat_discontinued_code"]=>
      string(6) "028500"
    }
    [1]=>
    array(18) {
      ["id"]=>
      string(1) "1"
      ["institution_date"]=>
      string(10) "1924-11-14"
      ["end_date"]=>
      string(10) "9999-12-31"
      ["istat_id"]=>
      string(6) "028001"
      ["registry_code"]=>
      string(4) "A001"
      ["name_it"]=>
      string(11) "ABANO TERME"
      ["name_transliterated"]=>
      string(11) "ABANO TERME"
      ["alternative_name"]=>
      string(0) ""
      ["alternative_name_transliterated"]=>
      string(0) ""
      ["anpr_id"]=>
      string(2) "28"
      ["istat_province_id"]=>
      string(3) "028"
      ["istat_region_id"]=>
      string(2) "05"
      ["istat_prefecture_id"]=>
      string(2) "PD"
      ["status"]=>
      string(6) "active"
      ["provincial_code"]=>
      string(2) "PD"
      ["source"]=>
      string(0) ""
      ["last_update"]=>
      string(10) "2016-06-17"
      ["istat_discontinued_code"]=>
      string(0) ""
    }
  }
}
```

You may want to find an active comune by his `registry_code`. To archieve this,
just play with the
[Doctrine Collections docs](https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/index.html#introduction)

```php
<?php

use nicholasricci\AnagraficheANPRISTAT\Collection\ComuneCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

$comunes = new ComuneCollection();

$criteria = new Criteria();
$criteria
    ->where(new Comparison('registry_code', Comparison::IS, 'A001'))
    ->andWhere(new Comparison('status', Comparison::IS, 'active'))
;

var_dump($comunes->matching($criteria)));
```

and it will grab for you your desired criteria:

```
object(nicholasricci\AnagraficheANPRISTAT\Collection\ComuneCollection)#55 (1) {
  ["elements":"Doctrine\Common\Collections\ArrayCollection":private]=>
  array(1) {
    [1]=>
    array(18) {
      ["id"]=>
      string(1) "1"
      ["institution_date"]=>
      string(10) "1924-11-14"
      ["end_date"]=>
      string(10) "9999-12-31"
      ["istat_id"]=>
      string(6) "028001"
      ["registry_code"]=>
      string(4) "A001"
      ["name_it"]=>
      string(11) "ABANO TERME"
      ["name_transliterated"]=>
      string(11) "ABANO TERME"
      ["alternative_name"]=>
      string(0) ""
      ["alternative_name_transliterated"]=>
      string(0) ""
      ["anpr_id"]=>
      string(2) "28"
      ["istat_province_id"]=>
      string(3) "028"
      ["istat_region_id"]=>
      string(2) "05"
      ["istat_prefecture_id"]=>
      string(2) "PD"
      ["status"]=>
      string(6) "active"
      ["provincial_code"]=>
      string(2) "PD"
      ["source"]=>
      string(0) ""
      ["last_update"]=>
      string(10) "2016-06-17"
      ["istat_discontinued_code"]=>
      string(0) ""
    }
  }
}
```

#### Ordering

Ordering can be done too through Doctrine Collections. Please refer to their 
[docs](https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/expressions.html#expressions)
to see the available API:

```php
<?php

use nicholasricci\AnagraficheANPRISTAT\Collection\ComuneCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

$comunes = new ComuneCollection();

$criteria = new Criteria();
$criteria->orderBy(['last_update' => Criteria::ASC]);

var_dump($comunes->matching($criteria)));
```

# Attribution

Forked by [devnix/belfiore-code](https://github.com/devnix/belfiore-code), 
