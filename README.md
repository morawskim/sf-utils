# Symfony extensions

## Validators

### ITIN validator

**ITIN** - Individual Taxpayer Identification Number

```php
<?php

require_once './vendor/autoload.php';

use mmo\sf\Validator\Constraints\Itin;
use Symfony\Component\Validator\Validation;

$validator = Validation::createValidatorBuilder()->getValidator();
$validator->validate('foo', new Itin()); // NOT VALID
$validator->validate('918-97-5273', new Itin()); // VALID
```

### Birthday

```php
require_once './vendor/autoload.php';

use mmo\sf\Validator\Constraints\Birthday;
use Symfony\Component\Validator\Validation;

$validator = Validation::createValidatorBuilder()->getValidator();
$validator->validate((new DateTimeImmutable('now'))->modify('-5 years'), new Birthday(['minAge' => 18])); // NOT VALID
$validator->validate((new DateTimeImmutable('now'))->modify('-120 years'), new Birthday()); // NOT VALID
$validator->validate((new DateTimeImmutable('now'))->modify('-5 years'), new Birthday()); // VALID
```

### BankRoutingNumber

```php
require_once './vendor/autoload.php';

use mmo\sf\Validator\Constraints\BankRoutingNumber;
use Symfony\Component\Validator\Validation;

$validator = Validation::createValidatorBuilder()->getValidator();
$validator->validate('1234567890', new BankRoutingNumber()); // NOT VALID
$validator->validate('275332587', new BankRoutingNumber()); // VALID
```

### Utf8Letters

Only UTF-8 letters and dashes.

```php
require_once './vendor/autoload.php';

use mmo\sf\Validator\Constraints\Utf8Letters;
use Symfony\Component\Validator\Validation;

$validator = Validation::createValidatorBuilder()->getValidator();
$validator->validate('foo.bar', new Utf8Letters()); // NOT VALID
$validator->validate('Zażółć', new Utf8Letters()); // VALID
```

### Utf8Words

Only UTF-8 letters, dashes, and spaces are allowed. Useful to validate the full name of a person.

```php
require_once './vendor/autoload.php';

use mmo\sf\Validator\Constraints\Utf8Words;
use Symfony\Component\Validator\Validation;

$validator = Validation::createValidatorBuilder()->getValidator();
$validator->validate('foo.bar', new Utf8Words()); // NOT VALID
$validator->validate('Zażółć gęślą', new Utf8Words()); // VALID
```

### ArrayConstraintValidatorFactory

Validators which don't follow a convention of naming a Constraint and ConstraintValidator,
will not be found by the default implementation of ConstraintValidatorFactory.

The class `ArrayConstraintValidatorFactory` resolve this problem, you can map ConstraintValidator to object.

```php
use Symfony\Component\Validator\Validation;
use mmo\sf\Validator\ArrayConstraintValidatorFactory;
use Kiczort\PolishValidatorBundle\Validator\Constraints\NipValidator;

// ....
$validatorFactory = new ArrayConstraintValidatorFactory(['kiczort.validator.nip' => new NipValidator()]);
$validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory($validatorFactory)
            ->getValidator();
// ...
```

## Translator

### FakeTranslator

The `FakeTranslator` class can be used in a unit tests instead of using a stub.
At the moment only `id` and `locale` arguments are supported.

```php
<?php

require_once './vendor/autoload.php';

use mmo\sf\Translation\FakeTranslator;

$translator = new FakeTranslator('en');
$translator->trans('foo'); // en-foo
$translator->trans('foo', [], null, 'us'); // us-foo
```

## Security

### FakePasswordEncoder

`FakePasswordEncoder` does not do any encoding but is useful in testing environments.

The main difference to PlaintextPasswordEncoder is prefix a password with a string.
So in tests, we know whether sut encode a password or not.

### AlwaysTheSameEncoderFactory

`AlwaysTheSameEncoderFactory`  is useful in integration tests with combination of `UserPasswordEncoder`. No matter which implementation of UserInterface you pass,
will always be used the same password encoder injected via constructor.

```php
<?php

require_once './vendor/autoload.php';

use mmo\sf\Security\Test\AlwaysTheSameEncoderFactory;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

$factory = new AlwaysTheSameEncoderFactory(new PlaintextPasswordEncoder());
$encoder = new UserPasswordEncoder($factory);
// now you can pass $encoder to your service, which expect `UserPasswordEncoderInterface`
```

### MemoryUserProvider

`MemoryUserProvider` is a simple non persistent user provider for tests.

This provider compares to InMemoryUserProvider allows for store any user objects,
which implement the UserInterface interface instead of only the internal Symfony User class.

```php
<?php

require_once './vendor/autoload.php';

use mmo\sf\Security\Test\MemoryUserProvider;
use Symfony\Component\Security\Core\User\User;

$provider = new MemoryUserProvider(User::class, []);
$provider->createUser(new User('test', 'foo'));
$provider->loadUserByUsername('test');
```

## Form

### RamseyUuidToStringTransformer

Transforms between a UUID string, and a UUID object.
Symfony 5.3 include an own `UuidToStringTransformer` transformer, but you need also use a symfony/uuid component.
This transformer works with a `ramsey/uuid` library.
