<?php declare(strict_types=1);

/**
 * This file is part of SimpleDTO, a PHP Experts, Inc., Project.
 *
 * Copyright © 2024 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/SimpleDTO
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\SimpleDTO\Tests;

use Carbon\Carbon;
use Error;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\SimpleDTO\NestedDTO;
use PHPExperts\SimpleDTO\SimpleDTO;
use PHPExperts\SimpleDTO\WriteOnce;
use PHPUnit\Framework\TestCase;

/**
 * @property string $name
 */
class CityDTO extends SimpleDTO
{
    use WriteOnce;
    protected int $population;
}

/** @testdox PHPExperts\SimpleDTO\WriteOnceTrait */
final class ReadMeTest extends TestCase
{
    public function testCanRunTheReadMeCodeSuccessfully()
    {
        $birthdayDTO = new BirthdayDTO([
            'name' => 'Donald J. Trump',
            'date' => '1946-06-14',
        ]);

        // Access as a property:
        self::assertEquals('Donald J. Trump', $birthdayDTO->name);

        // Properties with the data type of "Carbon" or "Carbon\Carbon"
        // are automagically converted to Carbon objects.
        self::assertEquals('June 14th, 1946', $birthdayDTO->date->format('F jS, Y'));

        // Easily output as an array:
        $expected = [
            'isPresident' => false,
            'name' => 'Donald J. Trump',
            'date' => Carbon::createFromDate('1946-06-14'),
        ];
        self::assertEquals($expected, $birthdayDTO->toArray());


        // Copy from one to another:
        $newDTO = new BirthdayDTO($birthdayDTO->toArray());
        self::assertNotSame($birthdayDTO, $newDTO);
        self::assertEquals($birthdayDTO, $newDTO);

        // Copy from one to another, with new properties:
        $origDTO = $newDTO;
        self::assertSame($origDTO, $newDTO);
        $newDTO = new BirthdayDTO([
            'date' => '2024-04-28',
        ] + $birthdayDTO->toArray());
        self::assertNotSame($origDTO, $newDTO);
        self::assertEquals('28 April 2024', $newDTO->date->format('j F Y'));

        // Easily output as JSON:
        $expectedJSON = '{"isPresident":false,"name":"Donald J. Trump","date":"1946-06-14T00:00:00.000000Z"}';
        self::assertEquals($expectedJSON, json_encode($birthdayDTO));


        $cityDTO = new CityDTO(['name' => 'Dubai']);
        self::assertEquals('Dubai', $cityDTO->name);
        try {
            dd($cityDTO->toArray());
        } catch (InvalidDataTypeException $e) {
            self::assertEquals(['population' => 'population is not a valid int'], $e->getReasons());
        }

        $cityDTO->population = 3_625_223;
        self::assertEquals(3_625_223, $cityDTO->population);

        /**
         * @property string $name
         */
        $normalCityDTO = new class(['name' => 'Dubai', 'population' => 3_625_223]) extends SimpleDTO {
            protected int $population;
        };
        self::assertNotSame($cityDTO, $normalCityDTO);
        self::assertEquals($cityDTO->toArray(), $normalCityDTO->toArray());

        try {
            $cityDTO->population = 4_000_000;
            $this->fail('Wrote to the same DTO property twice without failing.');
        } catch (Error $e) {
            self::assertEquals('SimpleDTOs are immutable. Create a new DTO to set a new value.', $e->getMessage());
        }

        $myDTO = new MyTestDTO([
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.01,
            'year' => 2019,
        ]);

        /**
         * @property MyTestDTO $myDTO
         */
        $dto = new class(['myDTO' => $myDTO]) extends NestedDTO
        {
        };

        $expected = [
            "myDTO" => [
                "name" => "PHP Experts, Inc.",
                "age"  => 7.01,
                "year" => 2019
            ]
        ];

        self::assertEquals($expected, $dto->toArray());
    }
}
