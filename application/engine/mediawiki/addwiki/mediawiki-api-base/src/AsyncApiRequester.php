<?php

namespace Mediawiki\Api;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * @since 2.2
 * @licence GNU GPL v2+
 * @author Addshore
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface AsyncApiRequester {

	/**
	 * @since 2.2
	 *
	 * @param Request $request
	 *
	 * @return PromiseInterface
	 *         Normally promising an array, though can be mixed (json_decode result)
	 *         Can throw UsageExceptions or RejectionExceptions
	 */
	public function getRequestAsync( Request $request );

	/**
	 * @since 2.2
	 *
	 * @param Request $request
	 *
	 * @return PromiseInterface
	 *         Normally promising an array, though can be mixed (json_decode result)
	 *         Can throw UsageExceptions or RejectionExceptions
	 */
	public function postRequestAsync( Request $request );

}
