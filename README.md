# SimpleDTO

[![TravisCI](https://travis-ci.com/phpexpertsinc/SimpleDTO.svg?branch=master)](https://travis-ci.com/phpexpertsinc/SimpleDTO)
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
 * @property-read Carbon $date
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

### WriteOnce DTOs

Sometimes, you may need to initialize one or more values of a DTO after it has been created. This is particularly
common for stateful DTOs via multiple round-trips in certain APIs (particularly Zuora's).

To overcome the stateless nature of traditional Data Type Objects, you can use the `WriteOnce` trait.

This will enable you to initialize a DTO with *null* and *uninitialized* properties, and set them *once* and only.

Also, you must set every property before you can serialize or `json_encode()` the object, send it to `toArray()`, etc.

```php
/**
* @property string $name
*/
class CityDTO extends SimpleDTO
{
    use WriteOnce;

    protected int $population;
}

$cityDTO = new CityDTO(['name' => 'Dubai']);
dd($cityDTO);
```

### Ignore certain protected properties.

If you are using PHP 8.0 and above, you can have SimpleDTO ignore any particular `protected` property (PHP will treat it
like any regular protected property) using the `#[IgnoreAsDTO]` Attribute:

```php
$testDTO = new class(['name' => 'Sofia', 'birthYear' => 2010]) extends SimpleDTO {
    #[IgnoreAsDTO]
    protected int $age;

    protected string $name;
    protected int $birthYear;

    public function calcAge(): int
    {
        $this->age = date('Y') - $this->birthYear;

        return $this->age;
    }
};
```

### NestedDTOs

You can nest DTOs inside of each other. 

```php
    $myDTO = new MyTestDTO([
        'name' => 'PHP Experts, Inc.',
        'age'  => 7.01,
        'year' => 2019,
    ]);

    /**
     * @property MyTestDTO $myDTO
     */
    $dto = new class(['myDTO' => $myDTO]) extends NestedDTO
    {
    };
    
    /*
    PHPExperts\SimpleDTO\NestedDTO@anonymous {
      -dataTypeRules: array:1 [
        "myDTO" => "?MyTestDTO"
      ]
      -data: array:1 [
        "myDTO" => PHPExperts\SimpleDTO\Tests\MyTestDTO {#355
          -dataTypeRules: array:3 [
            "name" => "?string"
            "age" => "?float"
            "year" => "?int"
          ]
          -data: array:3 [
            "name" => "PHP Experts, Inc."
            "age" => 7.01
            "year" => 2019
          ]
        }
      ]
    }
    */
```

# Use cases
PHPExperts\SimpleDTO\SimpleDTO  
 ✔ Properties are set via the constructor  
 ✔ Properties are accessed as public properties  
 ✔ Constructor assigns default values of typed properties  
 ✔ Public, private and static protected properties will be ignored  
 ✔ Each DTO is immutable  
 ✔ Setting any property returns an exception  
 ✔ Concrete properties can be used to set default values  
 ✔ Properties with the type carbon become carbon dates  
 ✔ Can easily output to array  
 ✔ Can easily be JSON encoded  
 ✔ Can easily be JSON decoded  
 ✔ Nullable properties are allowed  
 ✔ Every property is nullable with permissive mode  
 ✔ Can be serialized  
 ✔ Can be unserialized  
 ✔ Extra validation can be added  
 ✔ Can get the internal data  
 ✔ Can identify if it is permissive or not  
 ✔ Can ignore protected properties with the #[IgnoreDTO] Attribute.

PHPExperts\SimpleDTO\NestedDTO  
 ✔ Will construct nested DTOs  
 ✔ Can construct arrays of nested DTOs  
 ✔ Can retrieve the stored DTOs.  
 ✔ Will convert array data into the appropriate Nested DTOs  
 ✔ Will convert stdClasses into the appropriate Nested DTOs  
 ✔ Nested DTOs use Loose typing  
 ✔ Nested DTOs can be built using Typed Properties  
 ✔ Nested DTOs with Typed Properties use Strict typing  
 ✔ All registered Nested DTOs are required  
 ✔ Optional, unregistered, Nested DTOs are handled gracefully  
 ✔ Can be serialized  
 ✔ Can be unserialized  
 ✔ Can validate the DTO manually  
 ✔ Can get the internal data

PHPExperts\SimpleDTO\WriteOnceTrait  
 ✔ Can accept null values  
 ✔ Can be serialized  
 ✔ Will validate on serialize  
 ✔ Will validate on to array  
 ✔ Can write each null value once  
 ✔ Write-Once values must validate  

SimpleDTO Sad Paths  
 ✔ Cannot initialize with a nonexisting property  
 ✔ Accessing a nonexisting property throws an error  
 ✔ A DTO must have class property docblocks -or- typehint for each concrete property  
 ✔ Carbon date strings must be parsable dates  
 ✔ Properties must match their data types  
 ✔ Will not unserialize DTOs with invalid data  
 ✔ Cannot overwrite a non-existing property  

## Testing

```bash
phpunit --testdox
```

# Contributors

[Theodore R. Smith](https://www.phpexperts.pro/]) <theodore@phpexperts.pro>  
GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690  
CEO: PHP Experts, Inc.

## License

MIT license. Please see the [license file](LICENSE) for more information.

