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

if (!$css || true) {
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

echo $css;

?>