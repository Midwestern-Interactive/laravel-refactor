# MWI Laravel Refactor
Package to help refactor data in staging and production environments.

# Installation
```shell
composer require mwi/laravel-refactor
php artisan migrate
```

## Service Provider
If you're on laravel 5.5 or later the service provider will be automatically loaded and you can skip this step, if not, add to your `config/app.php` providers
```php
'providers' => [
    // ...
    MWI\LaravelRefactor\ServiceProvider::class,
    // ...
],
```

# Usage
Once you have the package setup and migrations ran
```shell script
php artisan make:refactor convert_relationship_to_many_to_many
```

Instruct the up and down methods
```php
<?php

class ConvertRelationshipToManyToMany
{
    /**
     * Run the refactor.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the refactor.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
```

Run the refactor command
```shell script
php artisan refactor
```

## Rollback
You can roll all migrations back
```shell script
php artisan refactor --rollback
```

## Steps
You can also run a certain number of steps
```shell script
php artisan refactor --steps=2
php artisan refactor --rollback --steps=1
```