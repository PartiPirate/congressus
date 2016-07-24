<?php
header ( 'Content-type: text/javascript' );

include("../../config/config.php");
include("../../config/memcache.php");
include("../../engine/utils/JShrink.php");

$memcache = openMemcacheConnection();
$memcacheKey = "min.js";

$js = $memcache->get($memcacheKey);

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
	include ('bootstrap-treeview.js');
	include ('strings.js');
	include ('user.js');
	include ('window.js');

//	include ('pagination.js');

	include ('editor.js');
	include ('search.js');

	$js = ob_get_clean();
	$js = \JShrink\Minifier::minify($js, array('flaggedComments' => false));
	
	if (!$memcache->replace($memcacheKey, $js, MEMCACHE_COMPRESSED, 3600)) {
		$memcache->set($memcacheKey, $js, MEMCACHE_COMPRESSED, 3600);
	}
}
else {
	$js = "// from cache\n" . $js;
//	header("HTTP/1.1 304 Not Modified");
}

echo $js;


?>