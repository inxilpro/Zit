# Zit

[![Build Status](https://travis-ci.org/inxilpro/Zit.svg)](https://travis-ci.org/inxilpro/Zit)
[![Packagist Version](https://img.shields.io/packagist/v/inxilpro/zit.svg)](https://packagist.org/packages/inxilpro/zit)
[![GitHub Stars](https://img.shields.io/github/stars/inxilpro/Zit.svg)](https://github.com/inxilpro/Zit/stargazers)
[![PHP 5.4+](https://img.shields.io/badge/php-%3E%3D5.4-yellowgreen.svg)](https://secure.php.net/releases/5_4_0.php)

Zit is a simple dependency injector based heavily on Pimple.  It aims to provide the same simplicity as Pimple while offering a slightly more robust object interface.

## ⚠️ Use `syberisle/zit` Instead

This package has been updated and replaced by [`syberisle/zit`](https://github.com/SyberIsle/Zit). Please upgrade to that to get the latest updates and improvements.



# Original README

## Installation

Zit is available via Composer:

```bash
composer require inxilpro/zit
```

## Usage

Zit is simple to use.  Like Pimple, objects are defined through anonymous functions that return an 
instance of the object:

```php
$container = new \Zit\Container();
$container->set('auth', function() {
	return new Auth();
});
```
	
All instantiation functions are passed the container as the first argument, making dependency injection possible:

```php
$container->set('auth', function($container) {
	return new Auth($container->get('db'));
});
```
	
Zit also provides convenient magic methods for setting instantiation functions:

```php
$container->setAuth(function() { /* ... */ }); // Or:
$container->set_auth(function() { /* ... */ });
```
	
## Getting Objects

Getting objects are as simple as:

```php
$container->get('auth');
```
	
Or, if you prefer the shorthand:

```php
$container->getAuth(); // Or:
$container->get_auth();
```
	
## Getting Fresh Objects

By default, all objects are shared in Zit.  That is, once an object is created, that same exact object is 
returned for each additional `get()`.  If you need a fresh object, you can do so with:

```php
$container->fresh('auth'); // Or:
$container->freshAuth(); // Or:
$container->fresh_auth(); // Or:
$container->newAuth(); // Or:
$container->new_auth();
```
	
> Note that because the 'new' keyword is reserved, you can only use it if you're using the magic methods.

## Constructor Parameters

Sometimes you need to pass parameters to the constructor of an object, while still also injecting 
dependencies.  Zit automatically passes all parameters on to your instantiation function:

```php
$container->setUser(function($c, $id)) {
	$user = new User($id);
	$user->setDb($c->getDb());
	return $user;
});

$user = $container->newUser(1);

// Parameters are taken into account when caching results:
$user2 = $container->getUser(1); // $user2 === $user;
```
	
## Storing Static Content

You can also use Zit to store strings/objects/etc. Just pass it instead of a generator:

```php
$container->set('api_key', 'abcd1234567890');
$key = $container->get('api_key');
```

**Please note:** You must wrap callables with an instantiation function if you want the callable 
returned rather than the return value of the callable.

## Custom Container

Most projects will benefit from a custom container that sets up its own injection rules.  This is as simple 
as extending Zit:

```php
namespace MyApp\Di;

class Container extends \Zit\Container
{
	public function __construct()
	{
		$this->setAuth(function() { /* ... */ });
		$this->setUser(function() { /* ... */ });
	}
}
```

## Change Log

### Version 3.0.0

  - Implemented [Container Interoperability](https://github.com/container-interop/container-interop). Zit was already
    `container-interop` compatible, but it now implements the interface and throws an exception that implements
    `Interop\Container\Exception\NotFoundException` when a item is not found. This exception extends
    `\InvalidArgumentException`, so 3.0.0 should be nearly 100% backwards-compatible, but I'm bumping the major version
    just in case.
  - Dropped support for PHP 5.3
  - Removed deprecated function `setParam`
  - `setFactory()` now accepts any `callable` instead of specifically a `Closure`
  - Fixed a typo in the exception message thrown from `__call` if a method does not exist
  - `set()` is now fluent (returns the container for chaining)
  - Switched to md4 hashing for speed improvements (we don't need the security of md5)
  - Added DocBlocks throughout the code

### Version 2.0

  - Removed the `setParam` method in favor of checking whether the parameter passed to `set` is callable.
  - Added the "Factory" variant.  This could cause backwards compatibility issues if you have set up objects that end with the word "factory".
  - Updated the `delete` method so that it clears out objects, callbacks, and factories, which could have some abnormal BC issues as well.


