<?php

namespace Mediawiki\Api\Test\Generator;

use Mediawiki\Api\Generator\FluentGenerator;

/**
 * @author Addshore
 *
 * @covers \Mediawiki\Api\Generator\FluentGenerator
 */
class FluentGeneratorTest extends \PHPUnit_Framework_TestCase {

	public function testConstructionWithNoGPrefix() {
		$generator = new FluentGenerator( 'name' );
		$generator->set( 'foo', 'bar' );

		$this->assertEquals(
			[
				'generator' => 'name',
				'gfoo' => 'bar',
			],
			$generator->getParams()
		);
	}

	public function testConstructionWithGPrefix() {
		$generator = new FluentGenerator( 'name' );
		$generator->set( 'gfoo', 'bar' );

		$this->assertEquals(
			[
				'generator' => 'name',
				'gfoo' => 'bar',
			],
			$generator->getParams()
		);
	}

	public function testFluidity() {
		$generator = FluentGenerator::factory( 'name' )
			->set( 'foo', 'bar' )
			->set( 'gcat', 'meow' );

		$this->assertEquals(
			[
				'generator' => 'name',
				'gfoo' => 'bar',
				'gcat' => 'meow',
			],
			$generator->getParams()
		);
	}

}
