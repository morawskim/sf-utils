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

## Translator

### FakeTranslator

The `FakeTranslator` class can be used in a unit tests to check whether the service used a translator to translate some messages.

```php
<?php

require_once './vendor/autoload.php';

use mmo\sf\Translation\FakeTranslator;

$translator = new FakeTranslator('en');
$translator->trans('foo'); // en-foo
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
