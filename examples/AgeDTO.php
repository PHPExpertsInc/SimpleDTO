<?php declare(strict_types=1);

/**
 * This file is part of SimpleDTO, a PHP Experts, Inc., Project.
 *
 * Copyright © 2019-2020 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/SimpleDTO
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\SimpleDTO\Examples;

require_once __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;
use PHPExperts\SimpleDTO\SimpleDTO;

/**
 * @property-read string $name
 * @property-read ?float $age
 * @property-read ?int   $days
 * @property-read Carbon $birthdate
 */
class AgeDTO extends SimpleDTO
{
    public function __construct(array $input)
    {
        $calcDays = function ($birthdate): int {
            $days = Carbon::now()->diffInDays(Carbon::parse($birthdate));

            return $days;
        };

        $input['days'] = $input['days'] ?? $calcDays($input['birthdate']);
        $input['age'] = $input['age'] ?? $input['days'] / 365.25;

        parent::__construct($input);
    }
}

$dto = new AgeDTO(['name' => 'USA', 'birthdate' => '1776-07-04']);
echo json_encode($dto, JSON_PRETTY_PRINT);
/** Output:
    {
        "name": "USA",
        "birthdate": "1776-07-04T00:00:00.000000Z",
        "days": 88714,
        "age": 242.88569472963724
    }
*/