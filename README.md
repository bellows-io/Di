# Dependency Injection

## example usage:


```php
use Di\Injector;
use Di\ServiceRegistry;

class Foo {

	protected $serviceA;
	public function __construct($serviceA) {
		$this->serviceA = $serviceA;
	}

	public function doThings($serviceB) {
		printf("%s, %s", $this->serviceA, $serviceB);
	}
}

$registry = new ServiceRegistry();
$registry->set('serviceA', 'config');
$registry->lazySet('serviceC', function() {
	return "database";
});

$injector = new Injector($registry);
$injector->aliasClassDependencies("Foo", ["serviceB" => "serviceC"]);

$foo = $injector->invokeConstructor("Foo");
$injector->invokeMethod($foo, "doThings");
```