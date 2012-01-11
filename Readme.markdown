# Zit

Zit is a simple dependency injector based heavily on Pimple.  It aims to provide the
same simplicity as Pimple while offering a slightly more robust object interface.

## Usage

Zit is simple to use.  Just include it and create a new container:

```php
<?php
require_once '/path/to/lib/Zit/Container.php';
$container = new \Zit\Container();
?>
```

## Defining Objects

Like Pimple, objects are defined through anonymous functions that return an instance
of the object:

```php
<?php
$container->set('auth', function() {
	return new Auth();
});
?>
```
	
All instantiation functions are passed the container as the first argument, making 
dependency injection possible:

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

By default, all objects are shared in Zit.  That is, once an object is created, that same
exact object is returned for each additional get().  If you need a fresh object, you can
do so with:

```php
<?php
$container->fresh('auth'); // Or:
$container->freshAuth(); // Or:
$container->fresh_auth(); // Or:
$container->newAuth(); // Or:
$container->new_auth();
?>
```
	
Note the because the 'new' keyword is reserved, you can only use it if you're using
the magic methods.

## Constructor Parameters

Sometimes you need to pass parameters to the constructor of an object, while still also
injecting dependencies.  Zit automatically passes all parameters on to your instantiation
function:

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
	
## Storing Non-Objects

You can also use Zit to store non-objects (anything from a string to an anonymous function).
Just use the setParam() method:

```php
<?php
$container->setParam('api_key', 'abcd1234567890');
$key = $container->get('api_key');
?>
```

## Custom Container

Most projects will benefit from a custom container that sets up its own injection rules.  This
is as simple as extending Zit:

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


