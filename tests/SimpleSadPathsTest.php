<?php declare(strict_types=1);

/**
 * This file is part of SimpleDTO, a PHP Experts, Inc., Project.
 *
 * Copyright © 2019-2025 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/SimpleDTO
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\SimpleDTO\Tests;

use Error;
use InvalidArgumentException;
use LogicException;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\SimpleDTO\SimpleDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Throwable;

/** @testdox SimpleDTO Sad Paths */
#[TestDox('SimpleDTO Sad Paths')]
final class SimpleSadPathsTest extends TestCase
{
    public function testCannotInitializeWithANonexistingProperty()
    {
        try {
            new MyTypedPropertyTestDTO([
                'year'        => 1988,
                'name'        => 'Sibi',
                'age'         => 25.2,
                'nonexistant' => true,
            ]);
            $this->fail('A DTO with an undefined property was created.');
        } catch (InvalidDataTypeException $e) {
            self::assertEquals('There was 1 validation error.', $e->getMessage());
            self::assertEquals(['nonexistant' => "'nonexistant' is not a configured DTO property"], $e->getReasons());
        }
    }

    public function testAccessingANonexistingPropertyThrowsAnError()
    {
        try {
            $dto = new MyTypedPropertyTestDTO([
                'year'  => 2005,
                'name'  => 'Sibi',
                'age'   => 25.2,
            ]);

            $dto->doesntExist;
            $this->fail('A non-existing property was accessed.');
        } catch (Error $e) {
            self::assertEquals('Undefined property: PHPExperts\SimpleDTO\Tests\MyTypedPropertyTestDTO::$doesntExist.', $e->getMessage());
        }
    }

    /** @testdox A DTO must have class property docblocks -or- typehint for each concrete property */
    #[TestDox('A DTO must have class property docblocks -or- typehint for each concrete property')]
    public function testADTOMustHaveClassPropertyDocblocksForEachConcreteProperty()
    {
        try {
            new class(['name' => 'Rishi Ramawat']) extends SimpleDTO
            {
                protected $name;
            };

            $this->fail('A DTO with no class docblock nor typehint was created.');
        } catch (LogicException $e) {
            self::assertEquals('There must be either a class-level docblock or typehint for $name.', $e->getMessage());
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
            self::assertEquals('No DTO class property docblocks nor typehints have been added.', $e->getMessage());
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
            self::assertEquals('There must be either a class-level docblock or typehint for $age.', $e->getMessage());
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
    #[TestDox('Will not unserialize DTOs with invalid data')]
    public function testWillNotUnserializeDTOsWithInvalidData()
    {
        // To build, also comment out lines 229-230 in SimpleDTO.php.
        // $dto = new MyTestDTO([
        //     'name' => 1,
        //     'age'  => (string)(4.51 * 1000000000),
        //     'year' => 1981,
        // ]);
        // dd(serialize($dto));

        $serialized = <<<TXT
O:36:"PHPExperts\SimpleDTO\Tests\MyTestDTO":4:{s:3:"isA";s:46:"PHPExperts\DataTypeValidator\IsAStrictDataType";s:7:"options";a:0:{}s:9:"dataRules";a:3:{s:4:"name";s:6:"string";s:3:"age";s:5:"float";s:4:"year";s:3:"int";}s:4:"data";a:3:{s:4:"name";i:1;s:3:"age";s:10:"4510000000";s:4:"year";i:1981;}}
TXT;

        $expected = [
            'name' => 'name is not a valid string',
            'age'  => 'age is not a valid float',
        ];

        try {
            $data = unserialize($serialized);
            dump($data);
            $this->fail('Unserialized a DTO with invalid data.');
        } catch (InvalidDataTypeException $e) {
            self::assertSame('There were 2 validation errors.', $e->getMessage());
            self::assertSame($expected, $e->getReasons());
        }
    }

    /** @testdox Cannot overwrite a non-existing property */
    #[TestDox('Cannot overwrite a non-existing property')]
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
