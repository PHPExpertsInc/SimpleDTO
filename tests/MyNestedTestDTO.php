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

namespace PHPExperts\SimpleDTO\Tests;

use PHPExperts\SimpleDTO\NestedDTO;

/**
 * @property string    $name
 * @property MyTestDTO $myDTO
 */
class MyNestedTestDTO extends NestedDTO
{
    /** @var string */
    protected $name = 'Nested';
}
