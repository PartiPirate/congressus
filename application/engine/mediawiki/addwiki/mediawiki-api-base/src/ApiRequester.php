<?php

namespace Mediawiki\Api;

/**
 * @since 2.2
 * @licence GNU GPL v2+
 * @author Addshore
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface ApiRequester {

	/**
	 * @since 2.2
	 *
	 * @param Request $request
	 *
	 * @return mixed Normally an array
	 */
	public function getRequest( Request $request );

	/**
	 * @since 2.2
	 *
	 * @param Request $request
	 *
	 * @return mixed Normally an array
	 */
	public function postRequest( Request $request );

}
