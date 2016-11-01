<?php
header ( 'Content-type: text/css' );

include("../../config/config.php");
include("../../config/memcache.php");

$memcache = openMemcacheConnection();
$memcacheKey = "min.css";

$css = $memcache->get($memcacheKey);
$ts = $memcache->get($memcacheKey . ".ts");

function compress($buffer) {
	/* remove comments */
	$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
	/* remove tabs, spaces, newlines, etc. */
	$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

	return $buffer;
}

if (!$css) {
	ob_start();
	
	/* css files for combining */
	
	// <!-- Bootstrap -->
	include ('bootstrap.min.css');
	include ('bootstrap-datetimepicker.min.css');
	include ('ekko-lightbox.min.css');
	include ('jquery.template.css');
	include ('jquery-ui.min.css');
	include ('opentweetbar.css');
	include ('calendar.min.css');
	include ('flags.css');
	include ('social.css');
	include ('style.css');
	include ('font-awesome.min.css');

	$css = ob_get_clean();
	
	$css = compress($css);

	$timestamp = time();
	if (!$memcache->replace($memcacheKey . ".ts", $timestamp, MEMCACHE_COMPRESSED, 3600)) {
		$memcache->set($memcacheKey . ".ts", $timestamp, MEMCACHE_COMPRESSED, 3600);
	}
	
	if (!$memcache->replace($memcacheKey, $css, MEMCACHE_COMPRESSED, 3600)) {
		$memcache->set($memcacheKey, $css, MEMCACHE_COMPRESSED, 3600);
	}
}
else {
//	header("HTTP/1.1 304 Not Modified");	
	$tsstring = gmdate('D, d M Y H:i:s ', $ts) . 'GMT';
	$etag = md5($ts . $memcacheKey);

	$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
	$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;

	if ((($if_none_match && $if_none_match == $etag) || (!$if_none_match)) &&
			($if_modified_since && $if_modified_since == $tsstring))
	{
		header('HTTP/1.1 304 Not Modified');
		exit();
	}
	else
	{
		header("Last-Modified: $tsstring");
		header("ETag: \"{$etag}\"");
	}	
}

echo $css;

?>