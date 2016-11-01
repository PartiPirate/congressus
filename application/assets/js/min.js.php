<?php
header ( 'Content-type: text/javascript' );

include("../../config/config.php");
include("../../config/memcache.php");
include("../../engine/utils/JShrink.php");

$memcache = openMemcacheConnection();
$memcacheKey = "min.js";

$js = $memcache->get($memcacheKey);
$ts = $memcache->get($memcacheKey . ".ts");

if (!$js) {
	ob_start();
	
	// <!-- Bootstrap -->
	
	include ('bootstrap.min.js');
	include ('underscore-min.js');
	include ('calendar.min.js');
	include ('language/fr-FR.js');
	include ('bootbox.min.js');
	include ('moment-with-locales.js');
	include ('bootstrap-datetimepicker.js');
	include ('jquery.timer.js');
	include ('jquery.scrollTo.min.js');
	include ('jquery.template.js');
	include ('jquery-ui.min.js');
	include ('bootstrap-treeview.js');
	include ('strings.js');
	include ('user.js');
	include ('window.js');

//	include ('pagination.js');

	include ('editor.js');
	include ('search.js');

	$js = ob_get_clean();
	$js = \JShrink\Minifier::minify($js, array('flaggedComments' => false));

	$timestamp = time();
	if (!$memcache->replace($memcacheKey . ".ts", $timestamp, MEMCACHE_COMPRESSED, 3600)) {
		$memcache->set($memcacheKey . ".ts", $timestamp, MEMCACHE_COMPRESSED, 3600);
	}

	if (!$memcache->replace($memcacheKey, $js, MEMCACHE_COMPRESSED, 3600)) {
		$memcache->set($memcacheKey, $js, MEMCACHE_COMPRESSED, 3600);
	}
}
else {
	$js = "// from cache\n" . $js;
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

echo $js;


?>