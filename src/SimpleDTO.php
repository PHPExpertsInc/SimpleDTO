<?php declare(strict_types=1);

/**
 * This file is part of SimpleDTO, a PHP Experts, Inc., Project.
 *
 * Copyright © 2019-2024 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/SimpleDTO
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\SimpleDTO;

use Carbon\Carbon;
use Error;
use PHPExperts\DataTypeValidator\DataTypeValidator;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\DataTypeValidator\IsAFuzzyDataType;
use PHPExperts\DataTypeValidator\IsAStrictDataType;
use ReflectionClass;

abstract class SimpleDTO implements SimpleDTOContract
{
    public const PERMISSIVE = 101;
    public const ALLOW_NULL = 102;
    public const ALLOW_EXTRA = 103;

    /** @var mixed[] */
    private $options;

    /** @var DataTypeValidator */
    private $validator;

    /** @var mixed[] */
    private $dataTypeRules = [];

    /** @var mixed[] */
    private $origDataTypeRules = [];

    /** @var mixed[] */
    private $data = [];

    /**
     * @param mixed[]                $input
     * @param mixed[]                $options
     * @param DataTypeValidator|null $validator
     */
    public function __construct(array $input, array $options = [], DataTypeValidator $validator = null)
    {
        $this->options = $options;

        if (!$validator) {
            $isA = in_array(self::PERMISSIVE, $this->options) ? new IsAFuzzyDataType() : new IsAStrictDataType();
            $validator = new DataTypeValidator($isA);
        }
        $this->validator = $validator;

        // WriteOnce trait needs to allow nullables.
        if (in_array(WriteOnce::class, $this->class_uses_recursive($this))) {
            $this->options[] = self::ALLOW_NULL;
        }

        $this->loadConcreteProperties($input);

        // Add in default values if they're missing.
        $this->spliceInDefaultValues($input);

        $this->loadDynamicProperties($input);
    }

    public function isPermissive(): bool
    {
        return in_array(self::PERMISSIVE, $this->options);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function validate(): void
    {
        $this->validator->validate($this->data, $this->origDataTypeRules);
        $this->extraValidation($this->data);
    }

    /**
     * @param mixed[] $input
     * @param string  $ifThis
     * @param mixed   $specialValue
     * @param string  $thenThat
     */
    protected function ifThisThenThat(array $input, string $ifThis, $specialValue, string $thenThat): void
    {
        if (($input[$ifThis] ?? '') === $specialValue && empty($input[$thenThat])) {
            $self = get_class($this);

            throw new InvalidDataTypeException(
                "$self::\$$thenThat must be set when self::\$$ifThis is '$specialValue'."
            );
        }
    }

    /**
     * @param mixed[] $input
     */
    private function loadConcreteProperties(array $input): void
    {
        $properties = (new ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();
            // Check for the #[IgnoreAsDTO] attribute
            if (method_exists($property, 'getAttributes')) {
                $attributes = $property->getAttributes(IgnoreAsDTO::class);
                if (!empty($attributes)) {
                    continue;
                }
            }

            $propertyName = $property->getName();

            if (empty($input[$propertyName])) {
                if (method_exists($property, 'hasDefaultValue')) {
                    if ($property->hasDefaultValue()) {
                        // Store the properties' default values.
                        $this->data[$propertyName] = $this->$propertyName;
                    }
                } else {
                    // @codeCoverageIgnoreStart
                    // This is needed for PHP 7.4 and earlier support.
                    if (property_exists($this, $propertyName)) {
                        if (isset($this->$propertyName)) {
                            // This is only run in PHP v8.0 and earlier.
                            $this->data[$propertyName] = $this->$propertyName;
                        } else {
                            // This is only run in PHP 7.4.
                            $this->data[$propertyName] = null;
                        }
                    }
                    // @codeCoverageIgnoreEnd
                }
            } else {
                $this->data[$propertyName] = $input[$propertyName];
            }

            // Needed for PHP 7.2 support.
            if (method_exists($property, 'hasType') && $property->hasType()) {
                if (method_exists($property, 'getType') && $property->getType() instanceof \ReflectionNamedType) {
                    $isNullable = $property->getType()->allowsNull() ? '?' : '';
                    $this->dataTypeRules[$propertyName] = $isNullable . $property->getType()->getName();
                }
            }

            // Unset the property to mitigate shenanigans.
            unset($this->$propertyName);
        }
    }

    /**
     * @param mixed[] $input
     */
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

    /**
     * @param mixed[] $input
     * @return void
     * @throws \ReflectionException
     */
    private function loadDynamicProperties(array $input): void
    {
        $this->loadDynamicDTORules();

        $rulesDiff = array_diff_key($this->data, $this->dataTypeRules);
        if (!empty($rulesDiff)) {
            throw new \LogicException(
                'There must be either a class-level docblock or typehint for $' . implode(', $', array_keys($rulesDiff)) . '.'
            );
        }

        // Backup the data type rules.
        $this->origDataTypeRules = $this->dataTypeRules;

        // Handle any string Carbon objects.
        $this->processCarbonProperties($input);

        $this->validateInputs($input);

        $inputDiff = array_diff_key($input, $this->dataTypeRules);
        if (
            !(in_array(self::PERMISSIVE, $this->options)
            || in_array(self::ALLOW_EXTRA, $this->options)) && !empty($inputDiff)
        ) {
            $self = static::class;
            $property = key($inputDiff);
            throw new Error("Undefined property: {$self}::\${$property}.");
        }

        $this->data = $input;
    }

    /**
     * This is just a placeholder so that child classes can override it.
     *
     * @param mixed[] $input
     * @return void
     */
    protected function extraValidation(array $input)
    {
    }

    /**
     * @param mixed[] $input
     * @return void
     */
    private function validateInputs(array $input): void
    {
        $this->validator->validate($input, $this->dataTypeRules);
        $this->extraValidation($input);
    }

    /**
     * Loads the dynamic properties of the DTO and builds a data type rule set from them.
     *
     * @throws \ReflectionException
     */
    private function loadDynamicDTORules(): void
    {
        $properties = (new ReflectionClass($this))->getDocComment();
        if (!$properties) {
            //throw new \LogicException('No DTO class property docblocks have been added.');
            return;
        }

        preg_match_all('/@property(-read)* (.*?)\r?\n/s', $properties, $annotations);

        if (empty($annotations[2])) {
            throw new \LogicException('No DTO class property docblocks nor typehints have been added.');
        }

        /** @var string $annotation */
        foreach ($annotations[2] as $annotation) {
            // Strip out extraneous white space.
            $annotation = preg_replace('/ {2,}/', ' ', $annotation) ?? '';
            $prop = explode(' ', $annotation);
            if (empty($prop[0]) || empty($prop[1])) {
                throw new InvalidDataTypeException('A class data type docblock is malformed.');
            }

            $this->dataTypeRules[substr($prop[1], 1)] = $prop[0];
        }
    }

    private function extractNullableProperty(string $expectedType): string
    {
        if ($expectedType[0] === '?' || substr($expectedType, 0, 5) === 'null|') {
            $nullTokenPos = $expectedType[0] === '?' ? 1 : 5;

            // Then strip it out of the expected type.
            $expectedType = substr($expectedType, $nullTokenPos);
        }

        return $expectedType;
    }

    /**
     * @param mixed[] $input
     * @return void
     */
    private function processCarbonProperties(array &$input): void
    {
        foreach ($this->dataTypeRules as $property => &$expectedType) {
            // Make every property nullable if in PERMISSIVE mode.
            $this->handlePermissiveMode($expectedType);

            $nonNullableType = $this->extractNullableProperty($expectedType);
            if (in_array($nonNullableType, ['Carbon', Carbon::class, '\\' . Carbon::class])) {
                if (!empty($input[$property]) && is_string($input[$property])) {
                    try {
                        $input[$property] = Carbon::parse($input[$property]);
                    } catch (\Exception $e) {
                        throw new InvalidDataTypeException("$property is not a parsable date: '{$input[$property]}'.");
                    }
                }
            }
        }
    }

    private function handlePermissiveMode(string &$expectedType): void
    {
        $isPermissive = in_array(self::PERMISSIVE, $this->options) || in_array(self::ALLOW_NULL, $this->options);
        if ($isPermissive) {
            $expectedType =
                $expectedType[0] !== '?' && strpos($expectedType, 'null|') !== 0
                    ? "?$expectedType"
                    : $expectedType;
        }
    }

    public function __isset(string $property): bool
    {
        return array_key_exists($property, $this->data);
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        if (!$this->__isset($property)) {
            $self = static::class;
            throw new Error("Undefined property: {$self}::\$$property.");
        }

        return $this->data[$property];
    }

    /**
     * @param string $property
     * @param mixed  $value
     */
    public function __set(string $property, $value): void
    {
        throw new Error('SimpleDTOs are immutable. Create a new DTO to set a new value.');
    }

    /**
     * @internal DO NOT USE THIS METHOD.
     * @param string $property
     * @param mixed $value
     */
    protected function overwrite($property, $value): void
    {
        if (!isset($this->dataTypeRules[$property])) {
            $self = static::class;
            throw new Error("Undefined property: {$self}::\${$property}.");
        }

        $this->data[$property] = $value;
        $this->validator->validate([$property => $value], $this->dataTypeRules);
    }

    /**
     * Recursively converts every NestedDTO (or any other object) to an array.
     * Even arrays of objects.
     *
     * @param mixed $value
     * @return mixed[]|null
     */
    protected function convertValueToArray($value): ?array
    {
        // Recurse into array values.
        $recurseIntoArray = function (array &$input): ?array {
            $newArray = [];
            foreach ($input as $key => $value) {
                $newArray[$key] = $this->convertValueToArray($value) ?? $value;
            }

            if ($input === $newArray) {
                return null;
            }

            return $newArray;
        };

        if (is_object($value)) {
            if (method_exists($value, 'toArray') && !($value instanceof Carbon)) {
                return $value->toArray();
            }

            if ($value instanceof \stdClass) {
                return (array) $value;
            }
        }

        if (!is_array($value)) {
            return null;
        }

        $value = $recurseIntoArray($value);

        return $value;
    }

    public function toArray(): array
    {
        $output = [];
        foreach ($this->data as $key => $value) {
            $output[$key] = $this->convertValueToArray($value) ?? $value;
        }

        return $output;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return false|string
     */
        /**
         * Returns all traits used by a class, its parent classes and trait of their traits.
         * Copyright (c) 2018 Taylor Otwell
         *
         * @param  object|string  $class
         * @return mixed[]
         *
         * @codeCoverageIgnore
         */
        private function class_uses_recursive($class): array
        {
            if (is_object($class)) {
                $class = get_class($class);
            }

            $results = [];

            $parentClasses = class_parents($class);
            if ($parentClasses === false) {
                $parentClasses = [];
            }

            foreach (array_reverse($parentClasses) + [$class => $class] as $class) {
                $results += $this->trait_uses_recursive($class);
            }

            return array_unique($results);
        }

        /**
         * Returns all traits used by a trait and its traits.
         * Copyright (c) 2018 Taylor Otwell
         *
         * @param  string  $trait
         * @return mixed[]
         *
         * @codeCoverageIgnore
         */
        private function trait_uses_recursive($trait): array
        {
            $traits = class_uses($trait);
            if ($traits === false) {
                $traits = [];
            }

            foreach ($traits as $trait) {
                $traits += $this->trait_uses_recursive($trait);
            }

            return $traits;
        }

    public function __serialize(): array
    {
        return [
            'isA'       => $this->validator->getValidationType(),
            'options'   => $this->options,
            'dataRules' => $this->origDataTypeRules,
            'data'      => $this->data,
        ];
    }

    public function __unserialize(array $input): void
    {
        $this->validator = new DataTypeValidator(new $input['isA']());
        $this->options = $input['options'];
        $this->validator->validate($input['data'], $input['dataRules']);
        $this->dataTypeRules = $input['dataRules'];
        $this->origDataTypeRules = $this->dataTypeRules;
        $this->loadConcreteProperties($input);
        $this->data = $input['data'];
    }

}
