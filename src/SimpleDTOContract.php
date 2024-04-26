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

use JsonSerializable;

interface SimpleDTOContract extends JsonSerializable
{
    public function isPermissive(): bool;

    /** @return mixed[] */
    public function getData(): array;
    public function validate(): void;
    public function __isset(string $property): bool;

    /**
     * @param string $property
     * @return mixed
     */
    public function __get(string $property);

    /**
     * @param string $property
     * @param mixed  $value
     */
    public function __set(string $property, $value): void;

    /** @return mixed[] */
    public function toArray(): array;

    /** @return mixed[] */
    public function jsonSerialize(): array;

    /**
     * @return false|string
     */
    public function __serialize(): array;

    public function __unserialize(array $serialized): void;
}
