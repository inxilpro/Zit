# Zit

[![Build Status](https://travis-ci.org/inxilpro/Zit.svg)](https://travis-ci.org/inxilpro/Zit)
[![Packagist Version](https://img.shields.io/packagist/v/inxilpro/zit.svg)](https://packagist.org/packages/inxilpro/zit)
[![GitHub Stars](https://img.shields.io/github/stars/inxilpro/Zit.svg)](https://github.com/inxilpro/Zit/stargazers)

Zit is a simple dependency injector based heavily on Pimple.  It aims to provide the same simplicity as Pimple while offering a slightly more robust object interface.

## Usage

Zit is simple to use.  Just include it and create a new container:

```php
<?php
require_once '/path/to/lib/Zit/Container.php';
$container = new \Zit\Container();
?>
```

## Defining Objects

Like Pimple, objects are defined through anonymous functions that return an instance of the object:

```php
<?php
$container->set('auth', function() {
	return new Auth();
});
?>
```
	
All instantiation functions are passed the container as the first argument, making dependency injection possible:

```php
<?php
$container->set('auth', function($container) {
	return new Auth($container->get('db'));
});
?>
```
	
Zit also provides convenient magic methods for setting instantiation functions:

```php
<?php
$container->setAuth(function() { // ... }); // Or:
$container->set_auth(function() { // ... });
?>
```
	
## Getting Objects

Getting objects are as simple as:

```php
<?php
$container->get('auth');
?>
```
	
Or, if you prefer the shorthand:

```php
<?php
$container->getAuth(); // Or:
$container->get_auth();
?>
```
	
## Getting Fresh Objects

By default, all objects are shared in Zit.  That is, once an object is created, that same exact object is returned for each additional get().  If you need a fresh object, you can do so with:

```php
<?php
$container->fresh('auth'); // Or:
$container->freshAuth(); // Or:
$container->fresh_auth(); // Or:
$container->newAuth(); // Or:
$container->new_auth();
?>
```
	
Note the because the 'new' keyword is reserved, you can only use it if you're using the magic methods.

## Constructor Parameters

Sometimes you need to pass parameters to the constructor of an object, while still also injecting dependencies.  Zit automatically passes all parameters on to your instantiation function:

```php
<?php
$container->setUser(function($c, $id)) {
	$user = new User($id);
	$user->setDb($c->getDb());
	return $user;
});

$user = $container->newUser(1);
?>
```
	
## Storing Non-Generators

You can also use Zit to store non-generators (strings/instantiated objects/etc). Just pass it instead of a generator:

```php
<?php
$container->set('api_key', 'abcd1234567890');
$key = $container->get('api_key');
?>
```

**Please note:** You must wrap closures with a generator, if you want to closure returned rather than the return value of the closure.

## Custom Container

Most projects will benefit from a custom container that sets up its own injection rules.  This is as simple as extending Zit:

```php
<?php
namespace MyApp\Di;

class Container extends \Zit\Container
{
	public function __construct()
	{
		$this->setAuth(function() { // ... });
		$this->setUser(function() { // ... });
	}
}
?>
```

## Change Log

### Version 3.0.0

  - Implemented [Container Interoperability](https://github.com/container-interop/container-interop). Zit was already
    `container-interop` compatible, but it now implements the interface and throws an exception that implements
    `Interop\Container\Exception\NotFoundException` when a item is not found. This exception extends
    `\InvalidArgumentException`, so 3.0.0 should be nearly 100% backwards-compatible, but I'm bumping the major version
    just in case.
  - Removed deprecated function `setParam`
  - Fixed a typo in the exception message thrown from `__call` if a method does not exist
  - `set()` is now fluent (returns the container for chaining)
  - Switched to md4 hashing for speed improvements (we don't need the security of md5)
  - Added DocBlocks throughout the code

### Version 2.0

  - Removed the `setParam` method in favor of checking whether the parameter passed to `set` is callable.
  - Added the "Factory" variant.  This could cause backwards compatibility issues if you have set up objects that end with the word "factory".
  - Updated the `delete` method so that it clears out objects, callbacks, and factories, which could have some abnormal BC issues as well.


