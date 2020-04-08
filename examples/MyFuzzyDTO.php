<?php declare(strict_types=1);

/**
 * This file is part of SimpleDTO, a PHP Experts, Inc., Project.
 *
 * Copyright © 2019-2020 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/SimpleDTO
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\SimpleDTO\Examples;

require_once __DIR__ . '/../vendor/autoload.php';

use PHPExperts\DataTypeValidator\DataTypeValidator;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\DataTypeValidator\IsAFuzzyDataType;
use PHPExperts\SimpleDTO\SimpleDTO;

/**
 * @property int   $daysAlive
 * @property float $age
 * @property bool  $isHappy
 */
class MyFuzzyDTO extends SimpleDTO
{
    public function __construct(array $input)
    {
        parent::__construct($input, new DataTypeValidator(new IsAFuzzyDataType()));
    }
}

try {
    $person = new MyFuzzyDTO([
        'daysAlive' => '5000',
        'age'       => '13.689',
        'isHappy'   => 1,
    ]);
} catch (InvalidDataTypeException $e) {
    dd($e->getReasons());
}

echo json_encode($person, JSON_PRETTY_PRINT);
