# RESTSpeaker

[![TravisCI](https://travis-ci.org/phpexpertsinc/SimpleDTO.svg?branch=master)](https://travis-ci.org/phpexpertsinc/RESTSpeaker)
[![Maintainability](https://api.codeclimate.com/v1/badges/ba05b5ebfa6bb211619e/maintainability)](https://codeclimate.com/github/phpexpertsinc/RESTSpeaker/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ba05b5ebfa6bb211619e/test_coverage)](https://codeclimate.com/github/phpexpertsinc/RESTSpeaker/test_coverage)

SimpleDTO is a PHP Experts, Inc., Project meant to facilitate easy Data Transfer Objects.

Basically, any protected property on the DTO can be set as an array element passed in to
the __constructor and/or as a default value on the property itself.

The DTOs are immutable: Once created, they cannot be changed. Create a new object instead.



## Installation

Via Composer

```bash
composer require phpexperts/simple-dto
```

## Usage

```php
use Carbon\Carbon;
use PHPExperts\SimpleDTO\SimpleDTO;

/**
 * @property-read string $name
 * @property-read Carbon $name
 */
class BirthdayDTO extends SimpleDTO
{
    /** @var string */
    protected $name;
    
    /** @var Carbon */
    protected $date;
    
    protected static $DATES = ['date'];
}

$birthdayDTO = new BirthdayDTO([
    'name' => 'Donald J. Trump',
    'date' => '1946-06-14',
]);

// Access as a property:
echo $birthday->name; // Donald J. Trump

// Properties in the $DATES property are auto-converted to Carbon.
echo $birthday->date->format('F jS, Y'); // June 14th, 1946

// Easily output as an array:
$birthday->toArray();

// Copy from one to another:
$newDTO = new BirthdayDTO($birthdayDTO->toArray());

// Easily output as JSON:
echo json_encode($birthdayDTO);
/* Output: 
{
    "name": "Donald J. Trump",
    "date": "1946-06-14T00:00:00.000000Z"
}
*/
```

# Use cases

PHPExperts\SimpleDTO\Tests\SimpleDTO
 ✔ Properties are set via the constructor
 ✔ Cannot initialize with a nonexisting property
 ✔ Properties are accessed as public properties
 ✔ Accessing a nonexisting property throws an error
 ✔ Each DTO is immutable
 ✔ Setting any property returns an exception
 ✔ Properties in the dates static property become carbon dates
 ✔ Can easily output to array
 ✔ Can easily be json encoded
 ✔ Can easily be json decoded

## Testing

```bash
phpunit
```

# Contributors

[Theodore R. Smith](https://www.phpexperts.pro/]) <theodore@phpexperts.pro>  
GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690  
CEO: PHP Experts, Inc.

## License

MIT license. Please see the [license file](LICENSE) for more information.

