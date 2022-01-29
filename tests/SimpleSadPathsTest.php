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

namespace PHPExperts\SimpleDTO\Tests;

use Error;
use LogicException;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\SimpleDTO\SimpleDTO;
use PHPUnit\Framework\TestCase;

/** @testdox SimpleDTO Sad Paths */
final class SimpleSadPathsTest extends TestCase
{
    public function testCannotInitializeWithANonexistingProperty()
    {
        try {
            new MyTestDTO([
                'name'        => 'Sibi',
                'age'         => 25.2,
                'nonexistant' => true,
            ]);
            $this->fail('A DTO with an undefined property was created.');
        } catch (Error $e) {
            self::assertEquals('Undefined property: PHPExperts\SimpleDTO\Tests\MyTestDTO::$nonexistant.', $e->getMessage());
        }
    }

    public function testAccessingANonexistingPropertyThrowsAnError()
    {
        try {
            $dto = new MyTestDTO([
                'name'        => 'Sibi',
                'age'         => 25.2,
            ]);

            $dto->doesntExist;
            $this->fail('A non-existing property was accessed.');
        } catch (Error $e) {
            self::assertEquals('Undefined property: PHPExperts\SimpleDTO\Tests\MyTestDTO::$doesntExist.', $e->getMessage());
        }
    }

    /** @testdox A DTO must have class property docblocks for each concrete property */
    public function testADTOMustHaveClassPropertyDocblocksForEachConcreteProperty()
    {
        try {
            new class(['name' => 'Rishi Ramawat']) extends SimpleDTO
            {
                protected $name;
            };

            $this->fail('A DTO with no class docblock was created.');
        } catch (LogicException $e) {
            self::assertEquals('No DTO class property docblocks have been added.', $e->getMessage());
        }

        try {
            /**
             * This is a comment, but not a property docblock.
             * @author Theodore R. Smith
             */
            new class(['name' => 'Smijo Thekuddan']) extends SimpleDTO
            {
                protected $name;
            };

            $this->fail('A DTO with no class property docblocks was created.');
        } catch (LogicException $e) {
            self::assertEquals('No DTO class property docblocks have been added.', $e->getMessage());
        }

        try {
            /**
             * What about malformed property docblocks?
             *
             * @property $name
             */
            new class(['name' => 'Anuradha Polakonda']) extends SimpleDTO
            {
                protected $name;
            };

            $this->fail('A DTO with a malformed class property docblock was created.');
        } catch (LogicException $e) {
            self::assertEquals('A class data type docblock is malformed.', $e->getMessage());
        }

        try {
            /**
             * What about when there's some but not every property docblock?
             *
             * @property string $name
             */
            new class(['name' => 'Harshi Srivasta']) extends SimpleDTO
            {
                protected $name;

                protected $age;
            };

            $this->fail('A DTO with a missing class property docblock was created.');
        } catch (LogicException $e) {
            self::assertEquals('You need class-level docblocks for $age.', $e->getMessage());
        }
    }

    public function testCarbonDateStringsMustBeParsableDates()
    {
        try {
            /**
             * Here is a non-parsable Carbon date.
             *
             * @property \Carbon\Carbon $date
             */
            new class(['date' => 'Gowtham Swaroop']) extends SimpleDTO
            {
            };

            $this->fail('A DTO with a malformed class property docblock was created.');
        } catch (InvalidDataTypeException $e) {
            $expected = "date is not a parsable date: 'Gowtham Swaroop'.";

            self::assertSame($expected, $e->getMessage());
        }
    }

    public function testPropertiesMustMatchTheirDataTypes()
    {
        try {
            /**
             * Every public and private property is ignored, as are static protected ones.
             *
             * @property int $age
             */
            new class([]) extends SimpleDTO
            {
            };

            self::fail('It worked without a required data type.');
        } catch (InvalidDataTypeException $e) {
            $expected = [
                'age' => 'age is not a valid int',
            ];

            self::assertSame($expected, $e->getReasons());
        }
    }

    /** @testdox Will not unserialize DTOs with invalid data */
    public function testWillNotUnserializeDTOsWithInvalidData()
    {
        $serializedJSON = <<<'JSON'
C:36:"PHPExperts\SimpleDTO\Tests\MyTestDTO":291:{{
    "isA": "PHPExperts\\DataTypeValidator\\IsAFuzzyDataType",
    "options": [
        101
    ],
    "dataRules": {
        "name": "?string",
        "age": "?floam",
        "year": "?int"
    },
    "data": {
        "year": 2019,
        "name": 1,
        "age": "4510000000"
    }
}}
JSON;
        $expected = [
            'name' => 'name is not a valid string',
            'age'  => 'age is not a valid floam',
        ];

        try {
            unserialize($serializedJSON);
            $this->fail('Unserialized a DTO with invalid data.');
        } catch (InvalidDataTypeException $e) {
            self::assertSame('There were 2 validation errors.', $e->getMessage());
            self::assertSame($expected, $e->getReasons());
        }
    }

    /** @testdox Cannot overwrite a non-existing property */
    public function testCannotOverwiteANonExistingProperty()
    {
        $overwriteDTO = new MyTestDTO([
            'name'        => 'Sibi',
            'age'         => 25.2,
        ]);

        try {
            $overwriteDTO->overwiteTest();
        } catch (Error $e) {
            self::assertEquals('Undefined property: PHPExperts\SimpleDTO\Tests\MyTestDTO::$doesntExist.', $e->getMessage());
        }
    }
}
