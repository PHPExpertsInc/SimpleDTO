<?php declare(strict_types=1);

/**
 * This file is part of SimpleDTO, a PHP Experts, Inc., Project.
 *
 * Copyright © 2019 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *  GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *  https://www.phpexperts.pro/
 *  https://github.com/phpexpertsinc/Zuora-API-Client
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\SimpleDTO\Tests;

use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\SimpleDTO\SimpleDTO;
use PHPExperts\SimpleDTO\WriteOnce;
use PHPUnit\Framework\TestCase;

/** @testdox PHPExperts\SimpleDTO\WriteOnceTrait */
final class WriteOnceTest extends TestCase
{
    private function buildWriteOnceDTO(): SimpleDTO
    {
        $info = [
            'name' => 'PHP Experts, Inc.',
            'age'  => null,
            'year' => null,
        ];

        /**
         * @property string $name
         * @property float $age
         * @property int $year
         */
        $writeOnceDTO = new class($info) extends SimpleDTO
        {
            use WriteOnce;
        };

        return $writeOnceDTO;
    }

    public function testCanAcceptNullValues()
    {
        $writeOnceDTO = $this->buildWriteOnceDTO();

        $expected = [
            'name' => 'PHP Experts, Inc.',
            'age'  => null,
            'year' => null,
        ];

        self::assertSame($expected, $writeOnceDTO->toArray());
    }

    public function testCanWriteEachNullValueOnce()
    {
        $writeOnceDTO = $this->buildWriteOnceDTO();
        $writeOnceDTO->age = 7.3;
        $writeOnceDTO->year = 2012;

        $expected = [
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.3,
            'year' => 2012,
        ];

        self::assertSame($expected, $writeOnceDTO->toArray());

        try {
            $writeOnceDTO->age = 8.0;
        } catch (\Error $e) {
            self::assertEquals('SimpleDTOs are immutable. Create a new one to set a new value.', $e->getMessage());
        }
    }

    /** @testdox Write-Once values must validate */
    public function testWriteOnceValuesMustValidate()
    {
        $writeOnceDTO = $this->buildWriteOnceDTO();

        try {
            $writeOnceDTO->age = 7;
            $this->fail('Created a WriteOnce DTO with an invalid data type.');
        } catch (InvalidDataTypeException $e) {
            $expected = [
                'age' => 'age is not a valid float',
            ];

            self::assertEquals('There was 1 validation error.', $e->getMessage());
            self::assertEquals($expected, $e->getReasons());
        }
    }
}
