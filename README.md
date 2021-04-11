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

## lexik/jwt-authentication-bundle

### Revoke JWT token

The JWT is stateful token. We don't need to store them. This property create problem, when we need to revoke (invalidate) a token.
Some resources: [How to change a token to be invalid status?](https://github.com/lexik/LexikJWTAuthenticationBundle/issues/643) or
[Invalidating JSON Web Tokens](https://stackoverflow.com/questions/21978658/invalidating-json-web-tokens/23089839#23089839)

In a file `config/packages/cache.yaml` we register a new pool `cache.jwt`. In this example as adapter we use Redis.
```yaml
cache.jwt:
    adapter: cache.adapter.redis
```

In a file `config/routes.yaml` we add a router:
```yaml
api_logout:
    path: /api/sessions
    controller: 'mmo\sf\JWTAuthenticationBundle\Controller\LogoutAction'
    methods: DELETE
```

Finally, we need to register services in `config/services.yaml` file.
We create alias for interface `JitGeneratorInterface` to `RamseyUuid4JitGenerator` and configure listeners.
For `CheckRevokeListener` we need pass correct arguments for cache pool (we create custom pool - `cache.jwt` in `config/packages/cache.yaml`) and router name `api_logout`, which we add in a file `config/routes.yaml`
```yaml
mmo\sf\JWTAuthenticationBundle\Controller\LogoutAction:
  tags: ['controller.service_arguments']
  mmo\sf\JWTAuthenticationBundle\JitGenerator\RamseyUuid4JitGenerator: ~
  mmo\sf\JWTAuthenticationBundle\JitGenerator\JitGeneratorInterface:
    alias: mmo\sf\JWTAuthenticationBundle\JitGenerator\RamseyUuid4JitGenerator

  mmo\sf\JWTAuthenticationBundle\Listener\CheckRevokeListener:
    arguments:
      - '@request_stack'
      - '@cache.jwt'
      - 'api_logout'
      - 'key_prefix_in_cache.'
    tags:
      - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_decoded, method: onJWTDecoded }
  mmo\sf\JWTAuthenticationBundle\Listener\AddJitClaimListener:
    tags:
      - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_created, method: onJWTCreated }
```

## Util

### Transliterator

Class `Transliterator` contains one static method `transliterate` to returns transliterated version of a string.
[Based on yii2 Inflector](https://github.com/yiisoft/yii2/blob/054e25986123dafd052d1bbd6a35525412f4b4a1/framework/helpers/BaseInflector.php#L512)

```php
<?php

require_once './vendor/autoload.php';

use mmo\sf\Util\Transliterator;

Transliterator::transliterate('¿Español?'); // Espanol?
Transliterator::transliterate('Українська: ґанок, європа', Transliterator::TRANSLITERATE_STRICT); // Ukraí̈nsʹka: g̀anok, êvropa
```

### EntityTestHelper

The class `EntityTestHelper` helps set a value for a private field e.g. `id`.

```php
<?php

require_once './vendor/autoload.php';

use mmo\sf\Util\EntityTestHelper;

EntityTestHelper::setPrivateProperty($entity, 12);
EntityTestHelper::setPrivateProperty($entity, 12, 'fieldName');
```

## Commands

### S3CreateBucketCommand

Command `mmo:s3:create-bucket` creates a S3 bucket.
To use this command you must register two services.
In `config/services.yaml` register a service `s3client` and `mmo\sf\Command\S3CreateBucketCommand`.

```yaml
services:
  # ...
  s3client:
    class: Aws\S3\S3Client
    arguments:
      - version: '2006-03-01' # or 'latest'
        endpoint: '%env(AWS_S3_ENDPOINT)%'
        use_path_style_endpoint: true
        region: "us-east-1" # 'eu-central-1' for example
        credentials:
          key: '%env(AWS_S3_KEY)%'
          secret: '%env(AWS_S3_SECRET)%'
  mmo\sf\Command\S3CreateBucketCommand:
    arguments:
      $s3Client: '@s3client'
```
