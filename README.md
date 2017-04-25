# Introduction
This package lets you save some config items dynamically into the database. It is useful when you want to make an interface to let your users change mail settings, or other types of configuration on the fly.

# Installation
```bash
composer require vynatu/database-config
```

Then, add the service provider to `app.php`:

```php
<?php 

'providers' => [
    ...
    Vynatu\DatabaseConfig\ConfigServiceProvider::class,
]
```

```bash
php artisan migrate
```

>  Vynatu/Database-Config does not require an alias. It replaces the default `config()` or `\Config::class` bindings.


# Usage
```php
<?php

// To permanently save items in the database
config()->set('mail.driver', 'mailgun', true); // The last argument sets wether or not to make this change permanent.
```