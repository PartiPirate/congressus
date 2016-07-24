<?php
header ( 'Content-type: text/css' );

include("../../config/config.php");
include("../../config/memcache.php");

$memcache = openMemcacheConnection();
$memcacheKey = "min.css";

$css = $memcache->get($memcacheKey);

if (!$css) {
	ob_start("compress", 0, PHP_OUTPUT_HANDLER_CLEANABLE);
	
	function compress($buffer) {
		/* remove comments */
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

        return $buffer;
    }
	
	/* css files for combining */
	
	// <!-- Bootstrap -->
	include ('bootstrap.min.css');
	include ('bootstrap-datetimepicker.min.css');
	include ('ekko-lightbox.min.css');
	include ('jquery.template.css');
	include ('opentweetbar.css');
	include ('calendar.min.css');
	include ('flags.css');
	include ('social.css');
	include ('style.css');
	include ('font-awesome.min.css');

	$css = ob_get_clean();
	
	if (!$memcache->replace($memcacheKey, $css, MEMCACHE_COMPRESSED, 3600)) {
		$memcache->set($memcacheKey, $css, MEMCACHE_COMPRESSED, 3600);
	}
}
else {
//	header("HTTP/1.1 304 Not Modified");	
}

echo $css;

?>