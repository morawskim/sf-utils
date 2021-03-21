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
