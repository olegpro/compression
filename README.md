compression
===========

JS and CSS files compress


==========================
require_once MODX_BASE_PATH . "assets/plugins/compression/compression.php"; 

$js_dirs      = array('assets/templates/aqwella/js/', 'assets/templates/aqwella/js/fancybox/fancybox/');
$cs_dirs      = array('assets/templates/aqwella/css/', 'assets/css/', 'assets/templates/aqwella/js/fancybox/fancybox/');
$merged_dir   = 'assets/merged/';
$home_dir     = MODX_BASE_PATH;

compression::compress($js_dirs, $cs_dirs, $merged_dir, $home_dir);