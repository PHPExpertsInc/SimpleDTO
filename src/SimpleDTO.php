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

namespace PHPExperts\SimpleDTO;

use Carbon\Carbon;
use Error;
use JsonSerializable;
use PHPExperts\DataTypeValidator\DataTypeValidator;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;
use PHPExperts\DataTypeValidator\IsAFuzzyDataType;
use PHPExperts\DataTypeValidator\IsAStrictDataType;
use ReflectionClass;
use Serializable;

abstract class SimpleDTO implements JsonSerializable, Serializable
{
    public const PERMISSIVE = 101;
    public const ALLOW_NULL = 102;

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

        // WriteOnce trait needs to allow nullables.
        if (in_array(WriteOnce::class, class_uses($this))) {
            $this->options[] = self::ALLOW_NULL;
        }

        $this->loadConcreteProperties();

        // Add in default values if they're missing.
        $this->spliceInDefaultValues($input);

        $this->loadDynamicProperties($input);
    }

    protected function ifThisThenThat(array $input, $ifThis, $specialValue, $thenThat)
    {
        if (($input[$ifThis] ?? '') === $specialValue && empty($input[$thenThat])) {
            $self = get_class($this);

            throw new InvalidDataTypeException(
                "$self::\$$thenThat must be set when self::\$$ifThis is '$specialValue'."
            );
        }
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
            throw new \LogicException(
                'You need class-level docblocks for $' . implode(', $', array_keys($rulesDiff)) . '.'
            );
        }

        // Handle any string Carbon objects.
        $this->processCarbonProperties($input);

        $this->validateInputs($input);

        $inputDiff = array_diff_key($input, $this->dataTypeRules);
        if (!in_array(self::PERMISSIVE, $this->options) && !empty($inputDiff)) {
            $self = static::class;
            $property = key($inputDiff);
            throw new Error("Undefined property: {$self}::\${$property}.");
        }

        $this->data = $input;
    }

    /**
     * This is just a placeholder so that child classes can override it.
     *
     * @param array $input
     */
    protected function extraValidation(array $input)
    {
    }

    private function validateInputs(array $input)
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
            throw new \LogicException('No DTO class property docblocks have been added.');
        }

        preg_match_all('/@property(-read)* (.*?)\n/s', $properties, $annotations);

        if (empty($annotations[2])) {
            throw new \LogicException('No DTO class property docblocks have been added.');
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

    private function processCarbonProperties(array &$input): void
    {
        foreach ($this->dataTypeRules as $property => &$expectedType) {
            // Make every property nullable if in PERMISSIVE mode.
            $this->handlePermissiveMode($expectedType);

            if (in_array($expectedType, ['Carbon', Carbon::class, '\\' . Carbon::class])) {
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

    private function handlePermissiveMode(&$expectedType)
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
//        $this->validator->assertIsType($value, $this->dataTypeRules[$property]);
    }

    protected function convertValueToArray($value): ?array
    {
        if (is_object($value)) {
            // Hack to make phpstan work, because it apparently doesn't understand `is_callable()`.
            if (method_exists($value, 'toArray') && !($value instanceof Carbon)) {
                return $value->toArray();
            }

            if ($value instanceof \stdClass) {
                return (array) $value;
            }
        }

        return null;
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

    public function serialize()
    {
        $output = [
            'isA'       => $this->validator->getValidationType(),
            'options'   => $this->options,
            'dataRules' => $this->dataTypeRules,
            'data'      => $this->toArray(),
        ];

        return json_encode($output, JSON_PRETTY_PRINT);
    }

    public function unserialize($serialized): void
    {
        $input = json_decode($serialized, true);

        $this->validator = new DataTypeValidator(new $input['isA']());
        $this->options = $input['options'];
        $this->validator->validate($input['data'], $input['dataRules']);
        $this->dataTypeRules = $input['dataRules'];
        $this->loadConcreteProperties();
        $this->data = $input['data'];
    }
}
