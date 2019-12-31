# Configuration

To use the adapter, you will need to provide the following configuration:

- A valid mezzio-authentication `UserRepositoryInterface` service in
  your DI container. This service will perform the actual work of validating the
  supplied credentials.

- An HTTP Basic **realm**. This may be an arbitrary value, but is [required by
  the specification](https://tools.ietf.org/html/rfc7617#section-2).

- A response factory. If you are using Mezzio, this is already configured
  for you.

As an example of configuration:

```php
// config/autoload/authentication.global.php

use Mezzio\Authentication\AdapterInterface;
use Mezzio\Authentication\Basic\BasicAccess;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Authentication\UserRepository\PdoDatabase;

return [
    'dependencies' => [
        'aliases' => [
            // Use the default PdoDatabase user repository. This assumes
            // you have configured that service correctly.
            UserRepositoryInterface::class => PdoDatabase::class,

            // Tell mezzio-authentication to use the BasicAccess
            // adapter:
            AdapterInterface::class => BasicAccess::class,
        ],
    ],
    'authentication' => [
        'realm' => 'api',
    ],
];
```
