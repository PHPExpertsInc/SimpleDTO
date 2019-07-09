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

use Error;

trait WriteOnce
{
    /** @var array */
    private $myData = [];

    abstract protected function overwrite($property, $value): void;

    public function __set(string $property, $value): void
    {
        if (array_key_exists($property, $this->myData) === false || $this->$property === null) {
            $this->overwrite($property, $value);
            $this->myData[$property] = $value;

            return;
        }

        throw new Error('SimpleDTOs are immutable. Create a new one to set a new value.');
    }
}
