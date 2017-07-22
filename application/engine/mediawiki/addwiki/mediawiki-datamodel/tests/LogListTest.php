<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\Log;
use Mediawiki\DataModel\LogList;
use Mediawiki\DataModel\PageIdentifier;

/**
 * @covers \Mediawiki\DataModel\LogList
 * @author Addshore
 */
class LogListTest extends \PHPUnit_Framework_TestCase {

	public function testJsonRoundTrip() {
		$logList = new LogList( array(
			new Log( 1, 'ty', 'ac', '2014', 'Addshore', new PageIdentifier( null, 22 ), 'comment', array() ),
			new Log( 2, 'ty2', 'ac2', '2015', 'Addbot', new PageIdentifier( null, 33 ), 'comment2', array() ),
		) );
		$json = $logList->jsonSerialize();
		$json = json_decode( json_encode( $json ), true );
		$this->assertEquals( $logList, LogList::jsonDeserialize( $json ) );
	}

}
