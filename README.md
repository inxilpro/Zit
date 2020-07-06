# Zit

[![Build Status](https://travis-ci.org/inxilpro/Zit.svg)](https://travis-ci.org/inxilpro/Zit)
[![Packagist Version](https://img.shields.io/packagist/v/inxilpro/zit.svg)](https://packagist.org/packages/inxilpro/zit)
[![GitHub Stars](https://img.shields.io/github/stars/inxilpro/Zit.svg)](https://github.com/inxilpro/Zit/stargazers)
[![PHP 5.4+](https://img.shields.io/badge/php-%3E%3D5.4-yellowgreen.svg)](https://secure.php.net/releases/5_4_0.php)

Zit is a simple dependency injector based heavily on Pimple.  It aims to provide the same simplicity as Pimple while offering a slightly more robust object interface.

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

## Auto-wiring

Zit has an an auto-wiring resolver, via `register($id, $class)` you will receive a `Definition` object that contains
the constructor parameters and any method calls.

```php
$container->register(MyClass::class);
$container->get(MyClass::class); // Instance of MyClass
```

### Modifying parameters

Constructor parameters can be set manually by calling `setParameter($key, $value)`

```php
// new MyClass('something');
$container->register(MyClass::class)->setParameter('arg1', 'something');
```

### Method calls

You may also set method calls on the definition

```php
// (new MyClass)->setSomething('something');
$container->register(MyClass::class)->addMethodCall('setSomething', ['arg1' => 'something']);
```

### Factories

While the main container supports factories, you may also use definitions to set factories.

This is different from using the method calls, as this will return the results of the factory method as if it were
a regular instantiation.

```php
// MyFactory::build();
$container->register(MyFactory::class)
          ->setFactoryMethod('build');

// MyFactory::build('now');
$container->register(MyFactory::class)
          ->setFactoryMethod('build')
          ->setParameter('arg1', 'now');

// ($container->get(MyFactory::class))->build();
$container->register(Resolver::reference(MyFactory::class))
          ->setFactoryMethod('build')
          ->setParameter('arg1', 'now');
```

### Referencing other container definitions

You can set references to values in the container by using `Resolver::reference($id)`.

```php
$container->register(MyClass::class);
$container->register(AnotherClass::class)->setParameter('test', Resolver::Reference(MyClass::class));
```

This is not limited to just auto-wired definitions, any key in the container can be referenced.

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
$container->setUser(function($c, $id) {
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