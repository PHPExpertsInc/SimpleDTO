# SimpleDTO

[![TravisCI](https://travis-ci.org/phpexpertsinc/SimpleDTO.svg?branch=master)](https://travis-ci.org/phpexpertsinc/SimpleDTO)
[![Maintainability](https://api.codeclimate.com/v1/badges/503cba0c53eb262c947a/maintainability)](https://codeclimate.com/github/phpexpertsinc/SimpleDTO/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/503cba0c53eb262c947a/test_coverage)](https://codeclimate.com/github/phpexpertsinc/SimpleDTO/test_coverage)

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

As of version 2, you *must* define class-level @property docblocks for each one of your properties.

You also must define the data type.

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
}

$birthdayDTO = new BirthdayDTO([
    'name' => 'Donald J. Trump',
    'date' => '1946-06-14',
]);

// Access as a property:
echo $birthday->name; // Donald J. Trump

// Properties with the data type of "Carbon" or "Carbon\Carbon" 
// are automagically converted to Carbon objects.
echo $birthday->date->format('F jS, Y'); // June 14th, 1946

// Easily output as an array:
$birthday->toArray();

// Copy from one to another:
$newDTO = new BirthdayDTO($birthdayDTO->toArray());

// Copy from one to another, with new properties:
$newDTO = new BirthdayDTO($birthdayDTO->toArray() + [
    'date' => '2020-11-03',
]);

// Easily output as JSON:
echo json_encode($birthdayDTO);
/* Output: 
{
    "name": "Donald J. Trump",
    "date": "1946-06-14T00:00:00.000000Z"
}
*/
```

### Fuzzy Data Types

But what if you aren't ready / able to dive into strict PHP data types yet?

Well, just instantiate the parent class like this:

```php
    use PHPExperts\DataTypeValidator\DataTypeValidator;
    use PHPExperts\DataTypeValidator\IsAFuzzyDataType;
    
    /**
     * @property int   $daysAlive
     * @property float $age
     * @property bool  $isHappy
     */
    class MyFuzzyDTO extends SimpleDTO
    {
        public function __construct(array $input)
        {
            parent::__construct($input, new DataTypeValidator(new IsAFuzzyDataType());
        }
    }

    $person = new MyFuzzyDTO([
        'daysAlive' => '5000',
        'age'       => '13.689',
        'isHappy'   => 1,
    ]);

    echo json_encode($person, JSON_PRETTY_PRINT);
    /*
    {
        "daysAlive": "5000",
        "age": "13.689",
        "isHappy": 1
    }
    */
```

# Use cases
PHPExperts\SimpleDTO\SimpleDTO  
 ✔ Properties are set via the constructor  
 ✔ Properties are accessed as public properties  
 ✔ Public, private and static protected properties will be ignored  
 ✔ Accessing a nonexisting property throws an error  
 ✔ Each DTO is immutable  
 ✔ Setting any property returns an exception  
 ✔ Concrete properties can be used to set default values  
 ✔ Properties with the type carbon become carbon dates  
 ✔ Can easily output to array  
 ✔ Can easily be json encoded  
 ✔ Can easily be json decoded

SimpleDTO Sad Paths  
 ✔ Cannot initialize with a nonexisting property  
 ✔ A DTO must have class property docblocks for each concrete property  
 ✔ Carbon date strings must be parsable dates

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

