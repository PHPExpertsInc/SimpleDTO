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
            $value = $this->convertValueToArray($input[$property]) ?? $input[$property];
            $input[$property] = new $dtoClass($value, $options ?? [SimpleDTO::PERMISSIVE]);
        }

        return $input;
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

        $validator = new DataTypeValidator(new $decoded['isA']);
        $this->__construct($decoded['data'], $decoded['DTOs'], $decoded['options'], $validator);
    }
}
