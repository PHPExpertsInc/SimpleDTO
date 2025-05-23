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

use DateTime;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\SimpleDTO\NestedDTO;
use PHPExperts\SimpleDTO\SimpleDTO;
use PHPExperts\SimpleDTO\SimpleDTOContract;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

/** @testdox PHPExperts\SimpleDTO\NestedDTO */
#[TestDox('PHPExperts\SimpleDTO\NestedDTO')]
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
    #[TestDox('Will construct nested DTOs')]
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
    #[TestDox('Can construct arrays of nested DTOs')]
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
            self::assertEquals('PHPExperts\SimpleDTO\Tests\MyNestedTestDTO::$key must be a string, but it is actually an integer.', $e->getMessage());
        }
    }

    /** @testdox Can retrieve the stored DTOs. */
    #[TestDox('Can retrieve the stored DTOs.')]
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
    #[TestDox('Will convert array data into the appropriate Nested DTOs')]
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
    #[TestDox('Will convert stdClasses into the appropriate Nested DTOs')]
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
    #[TestDox('Nested DTOs use Loose typing')]
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
    #[TestDox('Nested DTOs can be built using Typed Properties')]
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
        } catch (InvalidDataTypeException $e) {
            dd($e->getReasons());
        }


        $actual = unserialize(serialize($myTypedPropertyDTO));
        self::assertEquals($myTypedPropertyDTO, $actual);
    }

    /** @testdox Nested DTOs with Typed Properties use Strict typing */
    #[TestDox('Nested DTOs with Typed Properties use Strict typing')]
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
            self::assertEquals('There were 3 validation errors.', $e->getMessage());
            self::assertEquals([
                    'extra' => "'extra' is not a configured DTO property",
                    'age'   => 'age is not a valid float',
                    'year'  => 'year is not a valid int'
                ],
                $e->getReasons()
            );
        }
    }

    /** @testdox Nested DTOs with extra properties will work with Permissive mode */
    #[TestDox('Nested DTOs with extra properties will work with Permissive mode')]
    public function testNestedDTOsWithExtraPropertiesWillWorkWithPermissiveMode()
    {
        // Test with Loose Types
        try {
            $myDTOInfo = [
                'name'  => 'PHP Experts, Inc.',
                'age'   => null,
                'year'  => '2019',
                'extra' => true,
            ];
            $myTypedPropertyDTO = new MyTypedPropertyTestDTO($myDTOInfo, [SimpleDTO::PERMISSIVE]);
            self::assertInstanceOf(SimpleDTOContract::class, $myTypedPropertyDTO);
            self::assertInstanceOf(SimpleDTO::class, $myTypedPropertyDTO);

            /**
             * @property MyTypedPropertyTestDTO $myDTO
             */
            $nestedDTO = new class(
                ['myDTO' => $myTypedPropertyDTO],
                ['myDTO' => MyTypedPropertyTestDTO::class],
                [SimpleDTO::PERMISSIVE]
            ) extends NestedDTO {
            };

            self::assertInstanceOf(SimpleDTOContract::class, $nestedDTO);
            self::assertInstanceOf(SimpleDTO::class, $nestedDTO);
            self::assertInstanceOf(NestedDTO::class, $nestedDTO);
        } catch (InvalidDataTypeException $e) {
            dd($e->getReasons());
        }
    }

    /** @testdox All registered Nested DTOs are required */
    #[TestDox('All registered Nested DTOs are required')]
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
    #[TestDox('Optional, unregistered, Nested DTOs are handled gracefully')]
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
        return 'O:42:"PHPExperts\SimpleDTO\Tests\MyNestedTestDTO":4:{s:3:"isA";s:45:"PHPExperts\DataTypeValidator\IsAFuzzyDataType";s:7:"options";a:1:{i:0;i:101;}s:9:"dataRules";a:2:{s:4:"name";s:6:"string";s:5:"myDTO";s:22:"MyTypedPropertyTestDTO";}s:4:"data";a:2:{s:4:"name";s:6:"Nested";s:5:"myDTO";O:49:"PHPExperts\SimpleDTO\Tests\MyTypedPropertyTestDTO":4:{s:3:"isA";s:46:"PHPExperts\DataTypeValidator\IsAStrictDataType";s:7:"options";a:0:{}s:9:"dataRules";a:3:{s:4:"name";s:6:"string";s:3:"age";s:5:"float";s:4:"year";s:3:"int";}s:4:"data";a:3:{s:4:"name";s:17:"PHP Experts, Inc.";s:3:"age";d:7.01;s:4:"year";i:2019;}}}}';
    }

    public function testCanBeSerialized()
    {
        $nestedDTO = $this->buildNestedDTO();
        $expectedJSON = $this->getSerializedDTO();
        //dd($nestedDTO->toArray());
        $actualJSON = serialize($nestedDTO);

//        file_put_contents("/tmp/expected.json", $expectedJSON);
//        file_put_contents("/tmp/actual.json", $actualJSON);

        self::assertSame($expectedJSON, $actualJSON);

        return $nestedDTO;
    }

    /** @depends testCanBeSerialized */
    #[Depends('testCanBeSerialized')]
    public function testCanBeUnserialized(SimpleDTO $origDTO)
    {
        $serialized = $this->getSerializedDTO();

        try {
            $awokenDTO = unserialize($serialized);
        } catch (InvalidDataTypeException $e) {
            dd($e->getReasons());
        }

        self::assertEquals(serialize($origDTO), serialize($awokenDTO));
    }

    /** @testdox Can validate the DTO manually */
    #[TestDox('Can validate the DTO manually')]
    public function testCanValidateTheDTOManually()
    {
        $nestedDTO = $this->buildNestedDTO();

        try {
            $nestedDTO->validate();
            self::assertTrue(true, 'Validated a nested DTO successfully.');
        } catch (InvalidDataTypeException $e) {
            self::fail("Failed to validate the nested DTO because: \n* " . implode("\n* ", $e->getReasons()));
        }

        $buildInvalidNestedDTO = function () {
            $myDTO = new MyTypedPropertyTestDTO([
                'name' => 'PHP Experts, Inc.',
                'age'  => 7.01,
                'year' => 2019,
            ]);

            try {
                /**
                 * @property MyTypedPropertyTestDTO $myDTO
                 * @property string $name
                 */
                $nestedDTO = new class(['myDTO' => $myDTO, 'name' => 123], ['myDTO' => MyTypedPropertyTestDTO::class]) extends MyNestedTestDTO
                {

                };
                self::fail("Failed to invalidate an invalid NestedDTO.");
            } catch (InvalidDataTypeException $e) {
                self::assertEquals(['name' => 'name is not a valid string'], $e->getReasons());
            }

            try {
                $myDTO = new BirthdayDTO([
                    'name' => 'Vivek Ramaswamy',
                    'date' => '1985-08-09',
                ]);
                /**
                 * @property MyTypedPropertyTestDTO $myDTO
                 * @property string $name
                 */
                $nestedDTO = new class(['myDTO' => $myDTO, 'name' => 'Vivek']) extends MyNestedTestDTO
                {

                };
                self::fail("Failed to invalidate an invalid NestedDTO.");
            } catch (InvalidDataTypeException $e) {
                self::assertEquals(['myDTO' => 'myDTO is not a valid MyTypedPropertyTestDTO'], $e->getReasons());
            }
        };

        $buildInvalidNestedDTO();
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

    /** @testdox Throws an InvalidDataTypeException for invalid array properties */
    #[TestDox('Throws an InvalidDataTypeException for invalid array properties')]
    public function testInvalidDataTypeExceptionForInvalidArrayProperties()
    {
        $input = ['property' => 'not_an_array', 'newProperty' => 'also_not_an_array'];
        $nestedDTO = new class([]) extends NestedDTO {};
        $reflection = new ReflectionClass($nestedDTO);

        $method = $reflection->getMethod('processDTOArray'); // Replace with actual method name
        $method->setAccessible(true);

        try {
            $method->invokeArgs($nestedDTO, [&$input, 'property', 'newProperty', null]);
            self::fail('Worked when it should not have.');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('::$property must be an array of property', $e->getMessage());
        }
    }

    /** @testdox Throws an InvalidDataTypeException for empty DTO classes */
    #[TestDox('Throws an InvalidDataTypeException for empty DTO classes')]
    public function testThrowsInvalidDataTypeExceptionForEmptyDTOArray()
    {
        $input = [
            'dtoClass' => []
        ];
        $property = 'dtoClass';

        $nestedDTO = new class([]) extends NestedDTO {};
        $reflection = new ReflectionClass($nestedDTO);

        $method = $reflection->getMethod('processDTOArray'); // Replace with actual method name
        $method->setAccessible(true);

        try {
            $method->invokeArgs($nestedDTO, [&$input, $property, $input['dtoClass'], null]);
            self::fail('Expected InvalidDataTypeException not thrown');
        } catch (InvalidDataTypeException $exception) {
            //dd($exception->getMessage());
            self::assertStringContainsString('No DTOs could be found in the NestedDTO.', $exception->getMessage());
        }

    }

    /** @testdox Throws an InvalidDataTypeException for malformed DTO classes */
    #[TestDox('Throws an InvalidDataTypeException for malformed DTO classes')]
    public function testThrowsInvalidDataTypeExceptionForMalformedDTOClasses()
    {
        // Setup a proper array input structure for processDTOArray
        $input = [
            'items' => ['some_item'] // An array with at least one element
        ];

        // This is the malformed DTO class definition - an array without a valid class in position 0
        $malformedDTOClass = [null]; // or [] or [42] - anything that's not a valid class

        // Create an anonymous NestedDTO instance for testing
        $nestedDTO = new class([]) extends NestedDTO {};

        // Use reflection to access the private method
        $reflection = new ReflectionClass($nestedDTO);
        $method = $reflection->getMethod('processDTOArray');
        $method->setAccessible(true);

        // Now test the exception is thrown properly
        $this->expectException(InvalidDataTypeException::class);
        $this->expectExceptionMessage('A malformed DTO class was passed.');

        // Call the method with a reference to input (as it modifies the array)
        $method->invokeArgs($nestedDTO, [&$input, 'items', $malformedDTOClass, null]);
    }
}
