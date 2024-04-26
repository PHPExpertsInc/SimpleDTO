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

use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\SimpleDTO\NestedDTO;
use PHPExperts\SimpleDTO\SimpleDTO;
use PHPUnit\Framework\TestCase;

/** @testdox PHPExperts\SimpleDTO\NestedDTO */
final class NestedDTOTest extends TestCase
{
    private function buildNestedDTO(): NestedDTO
    {
        $myDTO = new MyTypedPropertyTestDTO([
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.01,
            'year' => 2019,
        ]);

        try {
            /**
             * @property MyTypedPropertyTestDTO $myDTO
             */
            $nestedDTO = new MyNestedTestDTO(['myDTO' => $myDTO], ['myDTO' => MyTypedPropertyTestDTO::class]);
        } catch (InvalidDataTypeException $e) {
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
            new MyTypedPropertyTestDTO([
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.01,
                'year' => 2019,
            ]),
            new MyTypedPropertyTestDTO([
                'name' => 'Cheyenne Novosad',
                'age'  => 22.472,
                'year' => 1996,
            ]),
        ];

        /**
         * @property MyTypedPropertyTestDTO[] $myDTOs
         */
        $nestedDTO = new class(['myDTOs' => $myDTOs], ['myDTOs[]' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
        };

        self::assertInstanceOf(NestedDTO::class, $nestedDTO);
        self::assertSame($myDTOs[0], $nestedDTO->myDTOs[0]);
        self::assertSame($myDTOs[1], $nestedDTO->myDTOs[1]);

        try {
            /**
             * @property MyNestedTestDTO[] $myDTOs
             */
            $nestedDto = new class(['myDTOs' => ['asdf']], ['myDTOs' => MyNestedTestDTO::class]) extends NestedDTO {
            };
            $this->fail('Created an invalid nested DTO.');
        } catch (InvalidDataTypeException $e) {
        }
    }

    /** @testdox Can retrieve the stored DTOs. */
    public function testCanRetrieveTheDTOs()
    {
        $myDTOs = [
            new MyTypedPropertyTestDTO([
                'name' => 'PHP Experts, Inc.',
                'age'  => 8.01,
                'year' => 2020,
            ]),
        ];

        /**
         * @property MyTypedPropertyTestDTO[] $myDTOs
         */
        $nestedDTO = new class(['myDTOs' => $myDTOs], ['myDTOs[]' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
        };

        $expected = ['myDTOs[]' => MyTypedPropertyTestDTO::class];

        self::assertSame($expected, $nestedDTO->getDTOs());
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
             * @property MyTypedPropertyTestDTO $myDTO
             */
            $nestedDTO = new class(['myDTO' => $myDTO], ['myDTO' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
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
        $myDTO = (object) [
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.2,
            'year' => 2012,
        ];

        /**
         * @property MyTypedPropertyTestDTO $myDTO
         */
        $nestedDTO = new class(['myDTO' => $myDTO], ['myDTO' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
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
        $myDTOInfo = [
            'name'  => 'PHP Experts, Inc.',
            'age'   => null,
            'year'  => '2019',
            'extra' => true,
        ];

        /**
         * @property MyTypedPropertyTestDTO $myDTO
         */
        $nestedDTO = new class(['myDTO' => $myDTOInfo], ['myDTO' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
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

    /** @testdox Nested DTOs can be built using Typed Properties */
    public function testNestedDTOsCanBeBuiltUsingTypedProperties()
    {
        if (version_compare(phpversion(), '7.4.0', '<')) {
            self::markTestSkipped('This functionality requires PHP 7.4 or higher.');
        }

        $myDTOInfo = [
            'name'  => 'PHP Experts, Inc.',
            'age'   => 41.58,
            'year'  => 2019,
        ];
        $myTypedPropertyDTO = new MyTypedPropertyTestDTO($myDTOInfo);

        /**
         * @property MyTypedPropertyTestDTO $myDTO
         */
        $nestedDTO = new class(['myDTO' => $myTypedPropertyDTO], ['myDTO' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
        };

        $expected = [
            'myDTO' => [
                'name'  => 'PHP Experts, Inc.',
                'age'   => 41.58,
                'year'  => 2019,
            ],
        ];

        self::assertSame($expected, $nestedDTO->toArray());

        try {
            $serialized = serialize($myTypedPropertyDTO);
            $actual = unserialize($serialized);
//            dd([
//                'serialized'   => $serialized,
//                'unserialized' => $actual
//            ]);
        } catch (InvalidDataTypeException $e) {
            dd($e->getReasons());
        }


        $actual = unserialize(serialize($myTypedPropertyDTO));
        self::assertEquals($myTypedPropertyDTO, $actual);
    }

    /** @testdox Nested DTOs with Typed Properties use Strict typing */
    public function testNestedDTOsWithTypedPropertiesUseStrictTyping()
    {
        if (version_compare(phpversion(), '7.4.0', '<')) {
            self::markTestSkipped('This functionality requires PHP 7.4 or higher.');
        }

        // Test with Loose Types
        try {
            $myDTOInfo = [
                'name'  => 'PHP Experts, Inc.',
                'age'   => null,
                'year'  => '2019',
                'extra' => true,
            ];
            $myTypedPropertyDTO = new MyTypedPropertyTestDTO($myDTOInfo);

            /**
             * @property MyTypedPropertyTestDTO $myDTO
             */
            $nestedDTO = new class(['myDTO' => $myTypedPropertyDTO], ['myDTO' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
            };

            self::fail('NestedDTO was built with loose types.');
        } catch (InvalidDataTypeException $e) {
            self::assertEquals('There were 2 validation errors.', $e->getMessage());
            self::assertEquals([
                    'age'  => 'age is not a valid float',
                    'year' => 'year is not a valid int',
                ],
                $e->getReasons()
            );
        }
    }

    /** @testdox All registered Nested DTOs are required */
    public function testAllRegisteredNestedDTOsAreRequired()
    {
        $myDTO = new MyTypedPropertyTestDTO([
            'name' => 'PHP Experts, Inc.',
            'age'  => 7.01,
            'year' => 2019,
        ]);

        try {
            /**
             * @property MyTypedPropertyTestDTO $myDTO
             */
            $dto = new class(['myDTO' => $myDTO], ['myDTO' => MyTypedPropertyTestDTO::class, 'missing' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
            };

            $this->fail('A nested DTO was created without all of the required DTOs.');
        } catch (InvalidDataTypeException $e) {
            self::assertSame('Missing critical DTO input(s).', $e->getMessage());
            self::assertSame(['missing' => MyTypedPropertyTestDTO::class], $e->getReasons());
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
         * @property MyTypedPropertyTestDTO $myDTO
         */
        $dto = new class(['myDTO' => $myDTO, 'extra' => $myDTO], ['myDTO' => MyTypedPropertyTestDTO::class]) extends NestedDTO {
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
        self::assertInstanceOf(MyTypedPropertyTestDTO::class, $dto->myDTO);
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
     * @depends testCanBeSerialized
     */
    public function testCanBeUnserialized(SimpleDTO $origDTO)
    {
        $serializedJSON = sprintf(
            '%s%s}',
            'C:42:"PHPExperts\SimpleDTO\Tests\MyNestedTestDTO":428:{',
            $this->getSerializedDTO()
        );

        $awokenDTO = unserialize($serializedJSON);

        self::assertEquals(serialize($origDTO), serialize($awokenDTO));
    }

    /** @testdox Can validate the DTO manually */
    public function testCanValidateTheDTOManually()
    {
        $nestedDTO = $this->buildNestedDTO();

        try {
            $nestedDTO->validate();
            self::assertTrue(true, 'Validated a nested DTO successfully.');
        } catch (InvalidDataTypeException $e) {
            self::fail("Failed to validate the nested DTO because: \n* " . implode("\n* ", $e->getReasons()));
        }
    }

    public function testCanGetTheInternalData()
    {
        $nestedDTO = $this->buildNestedDTO();
        $expected = [
            'name'  => 'Nested',
            'myDTO' => new MyTypedPropertyTestDTO([
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.01,
                'year' => 2019,
            ])
        ];

        self::assertEquals($expected, $nestedDTO->getData());
    }
}
