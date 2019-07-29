<?php declare(strict_types=1);

/**
 * This file is part of SimpleDTO, a PHP Experts, Inc., Project.
 *
 * Copyright © 2019 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *  GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *  https://www.phpexperts.pro/
 *  https://github.com/phpexpertsinc/SimpleDTO
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\SimpleDTO\Tests;

use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\SimpleDTO\NestedDTO;
use PHPExperts\SimpleDTO\SimpleDTO;
use PHPUnit\Framework\TestCase;

/** @testdox PHPExperts\SimpleDTO\NestedDTO */
final class NestedDTOTest extends TestCase
{
    private function buildNestedDTO(): NestedDTO
    {
        $myDTO = new MyTestDTO([
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.01,
            'year' => 2019,
        ]);

        try {
            /**
             * @property MyTestDTO $myDTO
             */
            $nestedDTO = new MyNestedTestDTO(['myDTO' => $myDTO], ['myDTO' => MyTestDTO::class]);
        }
        catch (InvalidDataTypeException $e) {
            dd([$e->getReasons(), $e->getTraceAsString()]);
        }

        return $nestedDTO;
    }

    /** @testdox Will construct nested DTOs */
    public function testWillConstructNestedDTOs()
    {
        $nestedDTO = $this->buildNestedDTO();

        $expected = [
            'name'  => 'Nested',
            'myDTO' => [
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.01,
                'year' => 2019,
            ],
        ];

        self::assertSame($expected, $nestedDTO->toArray());
    }

    /** @testdox Can construct arrays of nested DTOs */
    public function testCanConstructArraysOfNestedDTOs()
    {
        $myDTOs = [
            new MyTestDTO([
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.01,
                'year' => 2019,
            ]),
            new MyTestDTO([
                'name' => 'Cheyenne Novosad',
                'age'  => 22.472,
                'year' => 1996,
            ])
        ];

        /**
         * @property MyTestDTO[] $myDTOs
         */
        $nestedDTO = new class(['myDTOs' => $myDTOs], ['myDTOs[]' => MyTestDTO::class]) extends NestedDTO
        {
        };

        self::assertInstanceOf(NestedDTO::class, $nestedDTO);
        self::assertSame($myDTOs[0], $nestedDTO->myDTOs[0]);
        self::assertSame($myDTOs[1], $nestedDTO->myDTOs[1]);

        try {
            /**
             * @property MyNestedTestDTO[] $myDTOs
             */
            $nestedDto = new class(['myDTOs' => ['asdf']], ['myDTOs' => MyNestedTestDTO::class]) extends NestedDTO
            {
            };
            $this->fail('Created an invalid nested DTO.');
        } catch (InvalidDataTypeException $e) {
        }
    }

    /** @testdox Will convert array data into the appropriate Nested DTOs */
    public function testWillConvertArrayDataIntoTheAppropriateNestedDTOs()
    {
        try {
            $myDTO = [
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.2,
                'year' => 2012,
            ];

            /**
             * @property MyTestDTO $myDTO
             */
            $nestedDTO = new class(['myDTO' => $myDTO], ['myDTO' => MyTestDTO::class]) extends NestedDTO
            {
            };
        } catch (InvalidDataTypeException $e) {
            dd($e->getReasons());
        }

        $expected = [
            'myDTO' => [
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.2,
                'year' => 2012,
            ],
        ];

        self::assertSame($expected, $nestedDTO->toArray());
    }

    /** @testdox Will convert stdClasses into the appropriate Nested DTOs */
    public function testWillConvertStdClassesIntoTheAppropriateNestedDTOs()
    {
        try {
            $myDTO = (object) [
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.2,
                'year' => 2012,
            ];
        } catch (InvalidDataTypeException $e) {
            dd($e->getReasons());
        }

        /**
         * @property MyTestDTO $myDTO
         */
        $nestedDTO = new class(['myDTO' => $myDTO], ['myDTO' => MyTestDTO::class]) extends NestedDTO
        {
        };

        $expected = [
            'myDTO' => [
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.2,
                'year' => 2012,
            ],
        ];

        self::assertSame($expected, $nestedDTO->toArray());
    }

    /** @testdox Nested DTOs use Loose typing */
    public function testNestedDTOsUseLooseTyping()
    {
        try {
            $myDTOInfo = [
                'name'  => 'PHP Experts, Inc.',
                'age'   => null,
                'year'  => '2019',
                'extra' => true,
            ];
        } catch (InvalidDataTypeException $e) {
            dd($e->getReasons());
        }

        /**
         * @property MyTestDTO $myDTO
         */
        $nestedDTO = new class(['myDTO' => $myDTOInfo], ['myDTO' => MyTestDTO::class]) extends NestedDTO
        {
        };

        $expected = [
            'myDTO' => [
                'name'  => 'PHP Experts, Inc.',
                'age'   => null,
                'year'  => '2019',
                'extra' => true,
            ],
        ];

        self::assertSame($expected, $nestedDTO->toArray());
    }

    /** @testdox All registered Nested DTOs are required */
    public function testAllRegisteredNestedDTOsAreRequired()
    {
        $myDTO = new MyTestDTO([
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.01,
            'year' => 2019,
        ]);

        try {
            /**
             * @property MyTestDTO $myDTO
             */
            $dto = new class(['myDTO' => $myDTO], ['myDTO' => MyTestDTO::class, 'missing' => MyTestDTO::class]) extends NestedDTO
            {
            };

            $this->fail('A nested DTO was created without all of the required DTOs.');
        } catch (InvalidDataTypeException $e) {
            self::assertSame('Missing critical DTO input(s).', $e->getMessage());
            self::assertSame(['missing' => MyTestDTO::class], $e->getReasons());
        }
    }

    /** @testdox Optional, unregistered, Nested DTOs are handled gracefully */
    public function testOptionalUnregisteredNestedDTOsAreHandledGracefully()
    {
        $myDTO = (object) [
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.01,
            'year' => 2019,
        ];

        /**
         * @property MyTestDTO $myDTO
         */
        $dto = new class(['myDTO' => $myDTO, 'extra' => $myDTO], ['myDTO' => MyTestDTO::class]) extends NestedDTO
        {
        };

        $expectedArray = [
            'myDTO' => [
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.01,
                'year' => 2019,
            ],
            'extra' => [
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.01,
                'year' => 2019,
            ],
        ];

        $expectedObject = (object) [
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.01,
            'year' => 2019,
        ];

        self::assertSame($expectedArray, $dto->toArray());
        self::assertInstanceOf(MyTestDTO::class, $dto->myDTO);
        self::assertInstanceOf('\stdClass', $dto->extra);
        self::assertEquals($expectedObject, $dto->extra);
    }

    private function getSerializedDTO(): string
    {
        $expectedJSON = <<<'JSON'
{
    "isA": "PHPExperts\\DataTypeValidator\\IsAFuzzyDataType",
    "options": [
        101
    ],
    "dataRules": {
        "name": "string",
        "myDTO": "MyTestDTO"
    },
    "data": {
        "name": "Nested",
        "myDTO": {
            "name": "PHP Experts, Inc.",
            "age": 7.01,
            "year": 2019
        }
    },
    "DTOs": {
        "myDTO": "PHPExperts\\SimpleDTO\\Tests\\MyTestDTO"
    }
}
JSON;

        return $expectedJSON;
    }

    public function testCanBeSerialized()
    {
        $nestedDTO = $this->buildNestedDTO();
        $expectedJSON = $this->getSerializedDTO();
        $serializedJson = sprintf(
            "%s$expectedJSON}",
            'C:42:"PHPExperts\SimpleDTO\Tests\MyNestedTestDTO":428:{'
        );

        self::assertSame($expectedJSON, $nestedDTO->serialize());

        self::assertSame($serializedJson, serialize($nestedDTO));

        return $nestedDTO;
    }

    /**
     * @param SimpleDTO $origDTO
     * @depends testCanBeSerialized
     */
    public function testCanBeUnserialized(SimpleDTO $origDTO)
    {
        $serializedJSON = sprintf(
            "%s%s}",
            'C:42:"PHPExperts\SimpleDTO\Tests\MyNestedTestDTO":428:{',
            $this->getSerializedDTO()
        );

        $awokenDTO = unserialize($serializedJSON);

        self::assertEquals(serialize($origDTO), serialize($awokenDTO));
    }
}
