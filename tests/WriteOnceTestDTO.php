<?php declare(strict_types=1);

/**
 * This file is part of SimpleDTO, a PHP Experts, Inc., Project.
 *
 * Copyright © 2024 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/SimpleDTO
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\SimpleDTO\Tests;

use PHPExperts\SimpleDTO\SimpleDTO;
use PHPExperts\SimpleDTO\WriteOnce;

/**
 * @property string $name
 * @property float  $age
 * @property int    $year
 */
class WriteOnceTestDTO extends SimpleDTO
{
    use WriteOnce;
}
