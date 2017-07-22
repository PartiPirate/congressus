<?php

namespace Mediawiki\Api\Test\Unit;

use Mediawiki\Api\UsageException;
use PHPUnit_Framework_TestCase;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Api\UsageException
 */
class UsageExceptionTest extends PHPUnit_Framework_TestCase {

	public function testUsageExceptionWithNoParams() {
		$e = new UsageException();
		$this->assertSame(
			'Code: ' . PHP_EOL .
			'Message: ' . PHP_EOL .
			'Result: []',
			$e->getMessage()
		);
		$this->assertSame( '', $e->getApiCode() );
		$this->assertEquals( [], $e->getApiResult() );
	}

	public function testUsageExceptionWithParams() {
		$e = new UsageException( 'imacode', 'imamsg', [ 'foo' => 'bar' ] );
		$this->assertSame( 'imacode', $e->getApiCode() );
		$this->assertSame(
			'Code: imacode' . PHP_EOL .
			'Message: imamsg' . PHP_EOL .
			'Result: {"foo":"bar"}',
			$e->getMessage()
		);
		$this->assertEquals( [ 'foo' => 'bar' ], $e->getApiResult() );
	}

}
