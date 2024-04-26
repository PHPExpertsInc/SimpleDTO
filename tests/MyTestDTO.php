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

namespace PHPExperts\SimpleDTO\Tests;

use PHPExperts\SimpleDTO\SimpleDTO;

/**
 * @property string $name
 * @property float  $age
 * @property int    $year
 */
class MyTestDTO extends SimpleDTO
{
    /** @var string */
    protected $name;

    /** @var float */
    protected $age;

    /** @var int */
    protected $year = 2019;

    public function overwiteTest()
    {
        $this->overwrite('doesntExist', true);
    }
}
