<?php

use Monotek\Dependency\Inject;
use Monotek\Dependency\Dependency;

class SimpleClassTest extends Inject
{
	public $inject = array("database");
	function get() {
		$db = $this->getDatabase();
		return $db->title;
	}
}

class DatabaseMock {
	public $title = false;
	function __construct($title = false) {
		if ($title === false) {
			$title = "Default title";
		}
		$this->title = $title;
	}
	function get() {
		return $this->title;
	}
}


class DependencyTest extends PHPUnit_Framework_TestCase
{
	public function testInjection()
	{
		Dependency::set("database", function($title = false) {
			return new DatabaseMock($title);
		});

		$count = $seq = 1;
		Dependency::set("sequenceSingleton", Dependency::share( function() use (&$count) { return "test #" . $count++; }));
		Dependency::set("sequence", function() use (&$seq) { return new DatabaseMock("test #" . $seq++); } );

		Dependency::set("test", Dependency::singleton( new DatabaseMock("testing singleton") ) );

		$test1 = new SimpleClassTest();
		$this->assertEquals("Default title", $test1->get());
		$this->assertEquals("Default title", $test1->getDatabase()->get());

		$db_instantiator = Dependency::get("database");

		$this->assertTrue(is_callable($db_instantiator));

		$test1->setDatabase($db_instantiator("testing"));
		$this->assertEquals("testing", $test1->get());
		$this->assertEquals("testing", $test1->getDatabase()->get());

		$test1->addInject("sequence");
		$test1->addInject("sequenceSingleton");

		$this->assertEquals("test #1", $test1->getSequenceSingleton(), "singleton 1 test");
		$this->assertEquals("test #1", $test1->getSequenceSingleton(), "singleton 2 test");
		$this->assertEquals("test #1", $test1->getSequenceSingleton(), "singleton 3 test");

		$this->assertEquals("test #1", $test1->getSequence()->get(), "seq 1 test");
		$this->assertEquals("test #2", $test1->getSequence()->get(), "seq 2 test");
		$this->assertEquals("test #3", Dependency::resolve("sequence", true)->get(), "seq 3 test");

		// injection not defined
		try {
			$test1->getNaN();
			$this->assertFalse(true);
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}

		// unknown call...
		try {
			$test1->xxxNaN();
			$this->assertFalse(true);
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}

		// dependency not defined
		$test1->addInject("unknown");
		try {
			$test1->xxxUnknown();
			$this->assertFalse(true);
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}

		// dependency not defined
		try {
			Dependency::get("NaN");
			$this->assertFalse(true);
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}

		$list = Dependency::getList();
		$this->assertEquals(array("database", "sequencesingleton", "sequence", "test"), array_keys($list));

		$db = Dependency::get("test", true);
		$this->assertEquals("testing singleton", $db->get());
	}

	function testStaticInjection() {
		Inject::set("database", function($title = false) {
			return new DatabaseMock($title);
		});

		Dependency::set("title", "test");
		Inject::setTitle2("test2");

		$db = Inject::getDatabase("test");
		$this->assertTrue(is_object($db));
		$this->assertTrue($db->title === "test");

		$this->assertTrue(Inject::getTitle() === "test");
		$this->assertTrue(Inject::getTitle2() === "test2");

		try {
			$a = Inject::getTitle3();
			$this->assertFalse(true);
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}

		try {
			$a = Inject::putTitle();
			$this->assertFalse(true);
		} catch (\Exception $e) {
			$this->assertTrue(true);
		}
	}
}
