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

use PHPExperts\DataTypeValidator\DataTypeValidator;
use PHPExperts\DataTypeValidator\InvalidDataTypeException;

abstract class NestedDTO extends SimpleDTO
{
    /** @var array */
    private $DTOs;

    public function __construct(array $input, array $DTOs, array $options = null, DataTypeValidator $validator = null)
    {
        if (!empty(array_diff_key($DTOs, $input))) {
            throw new InvalidDataTypeException('Missing critical DTO input(s).', array_diff_key($DTOs, $input));
        }

        $this->DTOs = $DTOs;
        $input = $this->convertPropertiesToDTOs($input, $options);

        parent::__construct($input, $options ?? [SimpleDTO::PERMISSIVE], $validator);
    }

    private function convertPropertiesToDTOs(array $input, ?array $options): array
    {
        foreach ($this->DTOs as $property => $dtoClass) {
            if (!is_string($property) || substr($property, -2) === '[]') {
                $this->processDTOArray($input, $property, $dtoClass, $options);

                continue;
            }

            $input[$property] = $this->convertToDTO($dtoClass, $input[$property] ?? null, $options);
        }

        return $input;
    }

    private function convertToDTO($dtoClass, $value, ?array $options): ?SimpleDTO
    {
        if ($value instanceof $dtoClass || !$value) {
            return $value;
        }

        $newValue = $this->convertValueToArray($value) ?? $value;
        $newDTO = new $dtoClass($newValue, $options ?? [SimpleDTO::PERMISSIVE]);

        return $newDTO;
    }

    private function processDTOArray(&$input, $property, $dtoClass, ?array $options)
    {
        if (!is_array($input[$property])) {
            $self = get_class($this);

            throw new InvalidDataTypeException("$self::\$$property must be an array of $property");
        }

        $newProperty = substr($property, 0, -2);

        foreach ($input[$property] as $index => $value) {
            $input[$newProperty][$index] = $this->convertToDTO($dtoClass, $value, $options);
            unset($input[$property]);
        }
    }

    public function serialize()
    {
        $output = json_decode(parent::serialize(), true);
        $output['DTOs'] = $this->DTOs;

        return json_encode($output, JSON_PRETTY_PRINT);
    }

    public function unserialize($serialized): void
    {
        $decoded = json_decode($serialized, true);
        $this->DTOs = $decoded['DTOs'];
        $decoded['data'] = $this->convertPropertiesToDTOs($decoded['data'], $decoded['options']);

        $validator = new DataTypeValidator(new $decoded['isA']());
        $this->__construct($decoded['data'], $decoded['DTOs'], $decoded['options'], $validator);
    }
}
