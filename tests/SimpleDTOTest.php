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

use Carbon\Carbon;
use Error;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\SimpleDTO\SimpleDTO;
use PHPUnit\Framework\TestCase;

/** @testdox PHPExperts\SimpleDTO\SimpleDTO */
final class SimpleDTOTest extends TestCase
{
    /** @var SimpleDTO */
    private $dto;

    protected function setUp(): void
    {
        $this->dto = new MyTestDTO([
            'name' => 'World',
            'age'  => 4.51 * 1000000000,
        ]);

        parent::setUp();
    }

    public function testPropertiesAreSetViaTheConstructor()
    {
        self::assertInstanceOf(SimpleDTO::class, $this->dto);
        self::assertInstanceOf(MyTestDTO::class, $this->dto);
    }

    public function testPropertiesAreAccessedAsPublicProperties()
    {
        self::assertEquals('World', $this->dto->name);
    }

    /** @testdox Public, private and static protected properties will be ignored  */
    public function testPublicStaticAndPrivatePropertiesWillBeIgnored()
    {
        /**
         * Every public and private property is ignored, as are static protected ones.
         *
         * @property string $name
         */
        $dto = new class(['name' => 'Bharti Kothiyal']) extends SimpleDTO
        {
            protected $name;

            private $age = 27;

            public $country = 'India';

            protected static $employer = 'N/A';
        };

        $expected = [
            'name' => 'Bharti Kothiyal',
        ];

        self::assertSame($expected, $dto->toArray());
    }

    /** @testdox Each DTO is immutable */
    public function testEachDTOIsImmutable()
    {
        $this->testSettingAnyPropertyReturnsAnException();
    }

    public function testSettingAnyPropertyReturnsAnException()
    {
        try {
            $this->dto->name = 'asdf';
            $this->fail('Setting a property did not throw an error.');
        } catch (Error $e) {
            self::assertEquals(
                'SimpleDTOs are immutable. Create a new one to set a new value.',
                $e->getMessage()
            );
        }
    }

    private function buildDateDTO(array $values = ['remember' => '2001-09-11 8:46 EST']): SimpleDTO
    {
        /**
         * @property string $name
         * @property Carbon $remember
         */
        return new class($values) extends SimpleDTO
        {
            /** @var string */
            protected $name = '9/11';

            /** @var Carbon */
            protected $remember;
        };
    }

    public function testConcretePropertiesCanBeUsedToSetDefaultValues()
    {
        $dateDTO = $this->buildDateDTO();

        self::assertEquals('9/11', $dateDTO->name);
    }

    public function testPropertiesWithTheTypeCarbonBecomeCarbonDates()
    {
        $dateDTO = $this->buildDateDTO();

        self::assertInstanceOf(Carbon::class, $dateDTO->remember);
        self::assertEquals('September 11th, 2001', $dateDTO->remember->format('F jS, Y'));
        self::assertIsString($dateDTO->name);
        self::assertEquals('9/11', $dateDTO->name);
    }

    public function testCanEasilyOutputToArray()
    {
        $expected = [
            'name'     => 'Challenger Disaster',
            'remember' => Carbon::createFromDate('January 28 1986 11:39 EST'),
        ];

        $dateDTO = $this->buildDateDTO($expected);

        $actual = $dateDTO->toArray();
        self::assertIsArray($actual);
        self::assertEquals($expected, $actual);
    }

    public function testCanEasilyBeJsonEncoded()
    {
        $expected = '{"name":"9\/11","remember":"2001-09-11T13:46:00.000000Z"}';
        $dateDTO = $this->buildDateDTO();

        self::assertEquals($expected, json_encode($dateDTO));
    }

    public function testCanEasilyBeJsonDecoded()
    {
        $json = '{"name":"9\/11","remember":"2001-09-11T13:46:00.000000Z"}';
        $dateDTO = $this->buildDateDTO(json_decode($json, true));

        self::assertInstanceOf(Carbon::class, $dateDTO->remember);
        self::assertEquals('September 11th, 2001', $dateDTO->remember->format('F jS, Y'));
        self::assertIsString($dateDTO->name);
        self::assertEquals('9/11', $dateDTO->name);
    }

    public function testNullablePropertiesAreAllowed()
    {
        try {
            /**
             * Every public and private property is ignored, as are static protected ones.
             *
             * @property string $firstName
             * @property ?int $age
             * @property null|int $year
             * @property null|string $lastName
             * @property ?float $height
             */
            new class(['firstName' => 'Cheyenne', 'lastName' => 3, 'height' => 'asdf']) extends SimpleDTO
            {
            };

            $this->fail('A DTO was created with invalid nullable properties.');
        } catch (InvalidDataTypeException $e) {
            $expected = [
                'lastName' => 'lastName is not a valid string',
                'height'   => 'height is not a valid float',
            ];

            self::assertSame($expected, $e->getReasons());
        }
    }

    /** @test Every property is nullable with Permissive Move */
    public function testEveryPropertyIsNullableWithPermissiveMode()
    {
        $info = ['firstName' => 'Cheyenne', 'lastName' => null, 'age' => null, 'height' => null];

        /**
         * Every public and private property is ignored, as are static protected ones.
         *
         * @property string $firstName
         * @property string $lastName
         * @property int $age
         * @property float $height
         */
        $dto = new class($info, [SimpleDTO::PERMISSIVE]) extends SimpleDTO
        {
        };

        $expected = [
            'firstName' => 'Cheyenne',
            'lastName'  => null,
            'age'       => null,
            'height'    => null,
        ];

        self::assertSame($expected, $dto->toArray());
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
        "age": "float",
        "year": "int"
    },
    "data": {
        "year": 2019,
        "name": "World",
        "age": "4510000000"
    }
}
JSON;

        return $expectedJSON;
    }

    public function testCanBeSerialized()
    {
        $dto = new MyTestDTO([
            'name' => 'World',
            'age'  => (string) (4.51 * 1000000000),
        ], [SimpleDTO::PERMISSIVE]);

        $expectedJSON = $this->getSerializedDTO();
        $serializedJson = sprintf(
            "%s$expectedJSON}",
            'C:36:"PHPExperts\SimpleDTO\Tests\MyTestDTO":294:{'
        );

        self::assertSame($expectedJSON, $dto->serialize());
        self::assertSame($serializedJson, serialize($dto));

        return $dto;
    }

    /**
     * @depends testCanBeSerialized
     */
    public function testCanBeUnserialized(SimpleDTO $origDTO)
    {
        $serializedJSON = sprintf(
            '%s%s}',
            'C:36:"PHPExperts\SimpleDTO\Tests\MyTestDTO":294:{',
            $this->getSerializedDTO()
        );

        $awokenDTO = unserialize($serializedJSON);

        self::assertEquals($origDTO->toArray(), $awokenDTO->toArray());
    }

    public function testExtraValidationCanBeAdded()
    {
        try {
            /**
             * @property string $name
             * @property ?float $age
             */
            new class(['name' => 'Theodore R. Smith']) extends SimpleDTO
            {
                protected function extraValidation(array $input)
                {
                    $ifThisThenThat = [$this, 'ifThisThenThat'];
                    $ifThisThenThat($input, 'name', 'Theodore R. Smith', 'age');
                }
            };
            $this->fail('A DTO with invalid extra validation was created.');
        } catch (InvalidDataTypeException $e) {
            self::assertStringContainsString('$age must be set when self::$name is ', $e->getMessage());
        }

        /**
         * @property string $name
         * @property ?float $age
         */
        $dto = new class(['name' => 'Theodore R. Smith', 'age' => 37.426]) extends SimpleDTO
        {
            protected function extraValidation(array $input)
            {
                $ifThisThenThat = [$this, 'ifThisThenThat'];
                $ifThisThenThat($input, 'name', 'Theodore R. Smith', 'age');
            }
        };

        $expected = [
            'name' => 'Theodore R. Smith',
            'age'  => 37.426,
        ];

        self::assertInstanceOf(SimpleDTO::class, $dto);
        self::assertSame(37.426, $dto->age);
        self::assertSame($expected, $dto->toArray());
    }
}
