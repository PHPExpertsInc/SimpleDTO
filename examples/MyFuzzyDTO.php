<?php declare(strict_types=1);

namespace PHPExperts\SimpleDTO\Examples;

require_once __DIR__ . '/../vendor/autoload.php';

use PHPExperts\DataTypeValidator\DataTypeValidator;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\DataTypeValidator\IsAFuzzyDataType;
use PHPExperts\SimpleDTO\SimpleDTO;

/**
 * @property int $daysAlive
 * @property float $age
 * @property bool $isHappy
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
