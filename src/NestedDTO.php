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

    public function validate(): void
    {
        $errors = [];
        $errorCount = 0;
        try {
            parent::validate();
        } catch (InvalidDataTypeException $e) {
            $errors = $e->getReasons();
            $errorCount += count($errors);
        }

        foreach ($this->DTOs as $property => $dtoClass) {
            try {
                if ($this->data[$property] instanceof SimpleDTO) {
                    $this->data[$property]->validate();
                }
            } catch (InvalidDataTypeException $e) {
                $errors[$property] = $e->getReasons();
                $errorCount += count($e->getReasons());
            }
        }

        if (!empty($errors)) {
            $wasWere = $errorCount > 1 ? 'were' : 'was';
            $errorErrors = $errorCount > 1 ? 's' : '';
            throw new InvalidDataTypeException("There $wasWere $errorCount error$errorErrors.", $errors);
        }
    }

    /**
     * @return false|string
     */
    private function convertPropertiesToDTOs(array $input, ?array $options): array
    {
        foreach ($this->DTOs as $property => $dtoClass) {
            if (substr($property, -2) === '[]' || (!empty($input[$property]) && is_array($input[$property]))) {
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
        $newProperty = substr($property, -2) === '[]' ? substr($property, 0, -2) : $property;

        if (!is_array($input[$property] ?? null) && !is_array($input[$newProperty] ?? null)) {
            $self = get_class($this);

            throw new InvalidDataTypeException("$self::\$$property must be an array of $property");
        }

        $foundDTOArray = $input[$newProperty] ?? $input[$property];

        foreach ($foundDTOArray as $index => $value) {
            if (isset($input[$property]) && $foundDTOArray === $input[$property]) {
                unset($input[$property]);
            }

            if (is_array($dtoClass)) {
                if (empty($dtoClass[0]) || !is_object($dtoClass[0]) && !is_string($dtoClass[0])) {
                    throw new InvalidDataTypeException('A malformed DTO class was passed.');
                }

                $dtoClass = $dtoClass[0];
            }

            if (!($value instanceof $dtoClass) && !is_array($value) && !($value instanceof \stdClass)) {
                $input[$newProperty] = $this->convertToDTO($dtoClass, $foundDTOArray, $options, $property);

                break;
            }

            $input[$newProperty][$index] = $this->convertToDTO($dtoClass, $value, $options, $property);
        }
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
    public function __construct(array $input, array $DTOs, array $options = null, DataTypeValidator $validator = null)
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
