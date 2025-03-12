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

namespace PHPExperts\SimpleDTO;

use Exception;
use PHPExperts\DataTypeValidator\DataTypeValidator;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;

abstract class NestedDTO extends SimpleDTO implements SimpleDTOContract
{
    /** @var SimpleDTOContract[] */
    private $DTOs = [];

    /** @var mixed[] */
    private $data;

    public function getDTOs(): array
    {
        return $this->DTOs;
    }

    /**
     * @return false|string
     */
    private function convertPropertiesToDTOs(array $input, ?array $options): array
    {
        foreach ($this->DTOs as $property => $dtoClass) {
            if (substr($property, -2) === '[]' || (array_key_exists($property, $input) && is_array($input[$property]))) {
                $this->processDTOArray($input, $property, $dtoClass, $options);

                continue;
            }

            $input[$property] = $this->convertToDTO($dtoClass, $input[$property] ?? null, $options, $property);
        }

        return $input;
    }

    /**
     * @param mixed[]        $input
     * @param string         $property
     * @param object|mixed[] $dtoClass
     * @param mixed[]|null   $options
     */
    private function processDTOArray(array &$input, string $property, $dtoClass, ?array $options): void
    {
        // Extract the base property name without array suffix
        $baseProperty = substr($property, -2) === '[]' ? substr($property, 0, -2) : $property;

        // Validate array input
        if (!is_array($input[$property] ?? null) && !is_array($input[$baseProperty] ?? null)) {
            $self = get_class($this);
            throw new InvalidDataTypeException("$self::\$$property must be an array of $property");
        }

        // Find the array to process
        $foundDTOArray = $input[$baseProperty] ?? $input[$property] ?? null;

        if (empty($foundDTOArray)) {
            throw new InvalidDataTypeException('No DTOs could be found in the NestedDTO.');
        }

        // Normalize the DTO class
        if (is_array($dtoClass)) {
            if (empty($dtoClass[0]) || !is_object($dtoClass[0]) && !is_string($dtoClass[0])) {
                throw new InvalidDataTypeException('A malformed DTO class was passed.');
            }

            $dtoClass = $dtoClass[0];
        }

        // Clean up input if needed
        if (isset($input[$property]) && $foundDTOArray === $input[$property]) {
            unset($input[$property]);
        }

        // Process the array
        foreach ($foundDTOArray as $index => $value) {
            // Special case: If value is a scalar (not an array, not a DTO, not an stdClass)
            // then convert the entire array at once
            if (!($value instanceof $dtoClass) && !is_array($value) && !($value instanceof \stdClass)) {
                $input[$baseProperty] = $this->convertToDTO($dtoClass, $foundDTOArray, $options, $property);
                break; // Important: stop after first item, as in original code
            }

            // Normal case: convert each value individually
            $input[$baseProperty][$index] = $this->convertToDTO($dtoClass, $value, $options, $property);
        }
    }
    private function normalizeDTOClass($dtoClass): string
    {
        if (is_array($dtoClass)) {
            if (empty($dtoClass[0]) || !is_object($dtoClass[0]) && !is_string($dtoClass[0])) {
                throw new InvalidDataTypeException('A malformed DTO class was passed.');
            }
            return $dtoClass[0];
        }

        return $dtoClass;
    }

    /**
     * @param string|object $dtoClass
     * @param mixed[]       $value
     * @param array|null    $options
     * @param string        $property
     * @return SimpleDTO
     */
    private function convertToDTO($dtoClass, $value, ?array $options, string $property): SimpleDTO
    {
        if ($value instanceof $dtoClass && $value instanceof SimpleDTO) {
            return $value;
        }

        $newValue = $this->convertValueToArray($value) ?? $value;

        $newDTO = new $dtoClass($newValue, $options ?? [SimpleDTO::PERMISSIVE]);

        return $newDTO;
    }

    /**
     * @param mixed[]                $input
     * @param SimpleDTOContract[]    $DTOs
     * @param mixed[]|null           $options
     * @param DataTypeValidator|null $validator
     */
    public function __construct(array $input, array $DTOs = [], ?array $options = null, ?DataTypeValidator $validator = null)
    {
        $filterArraySymbol = function (array $DTOs): array {
            $results = [];
            foreach ($DTOs as $key => $value) {
                if (!is_string($key)) {
                    $self = get_class($this);
                    throw new InvalidDataTypeException(
                        "$self::\$key must be a string, but it is actually an integer."
                    );
                }
                $key = substr($key, -2) === '[]' ? substr($key, 0, -2) : $key;

                $results[$key] = $value;
            }

            return $results;
        };

        if (!empty(array_diff_key($filterArraySymbol($DTOs), $input))) {
            throw new InvalidDataTypeException('Missing critical DTO input(s).', array_diff_key($DTOs, $input));
        }

        $options = $options ?? [self::PERMISSIVE];

        foreach ($input as $propertyName => $value) {
            if ($value instanceof SimpleDTOContract) {
                $this->DTOs[$propertyName] = get_class($value);
            }
        }

        $this->DTOs = $DTOs;
        $input = $this->convertPropertiesToDTOs($input, $options);

        try {
            parent::__construct($input, $options, $validator);
        } catch (InvalidDataTypeException $e) {
            throw new InvalidDataTypeException(
                $e->getMessage() . ' ' . implode(', ', $e->getReasons()),
                $e->getReasons(),
                0,
                $e
            );
        }

        $this->data = $input;
    }
}
