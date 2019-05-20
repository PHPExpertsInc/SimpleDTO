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
    public function __construct(array $input, array $DTOs, array $options = null, DataTypeValidator $validator = null)
    {
        if (!empty(array_diff_key($DTOs, $input))) {
            throw new InvalidDataTypeException('Missing critical DTO inputs.', array_diff_key($DTOs, array_keys($input)));
        }

        foreach ($DTOs as $property => $dtoClass) {
            $input[$property] = new $dtoClass((array) $input[$property], $options ?? [SimpleDTO::PERMISSIVE]);
        }

        parent::__construct($input, $options ?? [SimpleDTO::PERMISSIVE], $validator);
    }
}
