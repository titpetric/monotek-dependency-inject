Dependency injection with PHP
=============================

Simply, this package consists of two classes. Class `Dependency` handles the
definition of dependencies and resources. Class `Inject` handles auto injection
of defined dependencies when instantiating objects.

Defining a dependency is as simple as:

~~~
Dependency::set("database", function($param = false) {
	return new Database();
});
~~~

Using a dependency is as simple as:

~~~
class UsesDatabase extends Inject
{
	public $inject = array("database");
	public function process()
	{
		$db = $this->getDatabase("param");
	}
}
~~~

The defined dependencies will create instances only when they are actually used.
If the dependency is not used it will not be instantiated, keeping your overhead
when executing PHP code as minimal as possible.

Passing arguments to the dependencies is enabled. This way you can have a utility
dependency instantiator, which would return data depending on the arguments.

If you want to override a dependency in an instance:
~~~
$object->setDatabase($object_or_callback);
~~~

Every next call to `getDatabase` will return the result of the call or the value
which was passed to `setDatabase`.

Please see the unit tests for more advanced examples of using this package.

2014 (c) Tit Petrič, Monotek d.o.o.
