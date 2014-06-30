## Basset for Laravel 4

[![Build Status](https://secure.travis-ci.org/etrepat/basset.png)](http://travis-ci.org/etrepat/basset)

Basset is a better asset management package for the Laravel framework. Basset
shares the same philosophy as Laravel. Development should be an enjoyable and
fulfilling experience. When it comes to managing your assets it can become quite
complex and a pain in the backside. These days developers are able to use a range
of pre-processors such as Sass, Less, and CoffeeScript. Basset is able to handle
the processing of these assets instead of relying on a number of individual tools.

### Please NOTE:

* This is a fork of the original but, unfortunately, no longer maintained
[Basset](https://github.com/jasonlewis/basset) asset management package for the
[Laravel 4 framework](http://laravel.com/). All credits and great thanks to
@jasonlewis for developing this great package.
* The main reason for this fork to exist is to make the package compatible
with the latest version of the [Laravel 4 framework](http://laravel.com/), fix
some bugs, and allow me to upgrade some applications which rely on this package
to the latest [Laravel 4 framework](http://laravel.com/).

Status:
- **Working** without major issues with *[Laravel 4.1](http://laravel.com)*.
- Should work with *[Laravel 4.2](http://laravel.com)* (more feedback needed).

Please, take a look at the [CHANGELOG.md](https://github.com/etrepat/basset/blob/master/CHANGELOG.md),
or browse the commits to know what has changed from the original.

### Installation

- [Basset on Packagist](https://packagist.org/packages/etrepat/basset)
- [Basset on GitHub](https://github.com/etrepat/basset)

To get the latest version of Basset simply require it in your `composer.json`
file:

    "etrepat/basset": "~4.0.1"

You'll then need to run `composer install` to download it and have the autoloader
updated.

Once Basset is installed you need to register the service provider with the
application. Open up `app/config/app.php` and find the `providers` key.

```php
'providers' => array(

    'Basset\BassetServiceProvider'

)
```

Basset also ships with a facade which provides the static syntax for creating
collections. You can register the facade in the `aliases` key of
your `app/config/app.php` file.

```php
'aliases' => array(

    'Basset' => 'Basset\Facade'

)
```

### Documentation

[View the official documentation](http://jasonlewis.me/code/basset/4.0).

### Changes

Please see the [CHANGELOG.md](https://github.com/etrepat/basset/blob/master/CHANGELOG.md) file.
