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
use PHPExperts\DataTypeValidator\DataTypeValidator;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\DataTypeValidator\IsAFuzzyDataType;
use PHPExperts\DataTypeValidator\IsAStrictDataType;
use ReflectionClass;

abstract class SimpleDTO implements JsonSerializable
{
    public const PERMISSIVE = 101;

    /** @var array */
    private $options;

    /** @var DataTypeValidator */
    private $validator;

    /** @var array */
    private $dataTypeRules = [];

    /** @var array */
    private $data = [];

    public function __construct(array $input, array $options = [], DataTypeValidator $validator = null)
    {
        $this->options = $options;

        if (!$validator) {
            $isA = in_array(self::PERMISSIVE, $this->options) ? new IsAFuzzyDataType() : new IsAStrictDataType();
            $validator = new DataTypeValidator($isA);
        }
        $this->validator = $validator;

        $this->loadConcreteProperties();

        // Add in default values if they're missing.
        $this->spliceInDefaultValues($input);

        $this->loadDynamicProperties($input);
    }

    private function loadConcreteProperties(): void
    {
        $properties = (new ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            // Store the properties' default values.
            $propertyName = $property->getName();
            $this->data[$propertyName] = $this->$propertyName;

            // Unset the property to mitigate shenanigans.
            unset($this->$propertyName);
        }
    }

    private function spliceInDefaultValues(array &$input): void
    {
        $new = [];
        $inputDiff = array_diff_key($this->data, $input);
        if (!empty($inputDiff)) {
            foreach ($inputDiff as $key => $diff) {
                if ($diff !== null) {
                    $new[$key] = $diff;
                }
            }
        }

        $new += $input;
        $input = $new;
    }

    private function loadDynamicProperties(array $input): void
    {
        $this->loadDynamicDTORules();

        $rulesDiff = array_diff_key($this->data, $this->dataTypeRules);
        if (!empty($rulesDiff)) {
            throw new \LogicException('You need class-level docblocks for $' . implode(', $', array_keys($rulesDiff)) . '.');
        }

        // Handle any string Carbon objects.
        $this->processCarbonProperties($input);

        $this->validator->validate($input, $this->dataTypeRules);

        $inputDiff = array_diff_key($input, $this->dataTypeRules);
        if (!in_array(self::PERMISSIVE, $this->options) && !empty($inputDiff)) {
            $self = static::class;
            $property = key($inputDiff);
            throw new Error("Undefined property: {$self}::\${$property}.");
        }

        $this->data = $input;
    }

    private function loadDynamicDTORules(): void
    {
        $properties = (new ReflectionClass($this))->getDocComment();
        if (!$properties) {
            throw new \LogicException('No DTO class property docblocks have been added.');
        }

        preg_match_all('/@property(-read)* (.*?)\n/s', $properties, $annotations);

        if (empty($annotations[2])) {
            throw new \LogicException('No DTO class property docblocks have been added.');
        }

        foreach ($annotations[2] as $annotation) {
            // Strip out extraneous white space.
            $annotation = preg_replace('/ {2,}/', ' ', $annotation);
            $prop = explode(' ', $annotation);
            if (empty($prop[0]) || empty($prop[1])) {
                throw new InvalidDataTypeException('A class data type docblock is malformed.');
            }

            $this->dataTypeRules[substr($prop[1], 1)] = $prop[0];
        }
    }

    private function processCarbonProperties(array &$input): void
    {
        $isPermissive = in_array(self::PERMISSIVE, $this->options);
        foreach ($this->dataTypeRules as $property => &$type) {
            // Make every property nullable if in PERMISSIVE mode.
            if ($isPermissive) {
                $type = $type[0] !== '?' && strpos($type, 'null|') !== 0 ? "?$type" : $type;
            }

            if (in_array($type, ['Carbon', Carbon::class, '\\' . Carbon::class])) {
                if (is_string($input[$property])) {
                    try {
                        $input[$property] = Carbon::parse($input[$property]);
                    } catch (\Exception $e) {
                        throw new InvalidDataTypeException("$property is not a parsable date: '{$input[$property]}'.");
                    }
                }
            }
        }
    }

    public function __isset(string $property): bool
    {
        return array_key_exists($property, $this->data);
    }

    public function __get(string $property)
    {
        if (!$this->__isset($property)) {
            $self = static::class;
            throw new Error("Undefined property: {$self}::\$$property.");
        }

        return $this->data[$property];
    }

    public function __set(string $property, $value): void
    {
        throw new Error('SimpleDTOs are immutable. Create a new one to set a new value.');
    }

    public function toArray(): array
    {
        foreach ($this->data as &$value) {
            if (is_object($value))
            {
                if (is_callable([$value, 'toArray']))
                {
                    $toArray = $value->toArray();
                    $value = $toArray;
                    $toArray = null;

                    continue;
                }

                $value = (array) $value;
            }
        }

        return $this->data;
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
