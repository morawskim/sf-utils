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
