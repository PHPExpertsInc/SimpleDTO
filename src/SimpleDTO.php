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

namespace PHPExperts\SimpleDTO;

use Carbon\Carbon;
use Error;
use JsonSerializable;
use ReflectionClass;
use Serializable;

abstract class SimpleDTO implements JsonSerializable
{
    /** @var array Dates that should be converted to Carbon instances. */
    protected static $DATES = [];

    private function assertPropertyIsDefined(string $property)
    {
        if (!property_exists($this, $property)) {
            $self = static::class;
            throw new Error("Undefined property: {$self}::$property.");
        }
    }

    public function __construct(array $input)
    {
        foreach ($input as $property => $value) {
            $this->assertPropertyIsDefined($property);

            if (in_array($property, static::$DATES) && !($value instanceof Carbon)) {
                $value = Carbon::createFromDate($value);
            }

            $this->$property = $value;
        }
    }

    public function __get(string $property)
    {
        $this->assertPropertyIsDefined($property);

        return $this->$property;
    }

    public function __set(string $property, $value)
    {
        throw new Error('SimpleDTOs are immutable. Create a new one to set a new value.');
    }

    public function toArray(): array
    {
        $properties = (new ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PROTECTED);

        $results = [];
        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);
            $results[$property->getName()] = $property->getValue($this);
        }

        return $results;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

//    public function serialize()
//    {
//        return serialize($this->toArray());
//    }
//
//    public function unserialize($serialized)
//    {
//        // TODO: Implement unserialize() method.
//    }
}