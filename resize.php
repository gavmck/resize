<?php
# ========================================================================#
#
#  Author:    Gavyn McKenzie
#  Version:   1.0
#  Date:      10-Oct-13
#  Purpose:   Take an array of images and convert to the cached correct sized version, return as JSON
#  Requires : Requires PHP5, GD library, resize-class.php
#
# ========================================================================#

// @TODO Don't resize if cached

require_once __DIR__ . '/php/lib/resize-class.php';

// Return JSON
header('Content-Type: application/json');

// Potential image sizes
// Could be set evenly as a tolerance
// or to your common image display sizes
// Example: Round to nearest 100px 
$sizes = array(
	'100',
	'150',
  '200',
  '240'
    
);

$cache = 'img/cache/';

// Get the closest value to the number in array (as long as it's not bigger than original)
function closest($search, $arr) {
    $closest = null;
    foreach($arr as $item) {
        // distance from image width -> current closest entry is greater than distance from  
        if ($closest == null || abs($search - $closest) > abs($item - $search)) {
            $closest = $item;
        }
    }
    $closest = ($closest == null) ? $closest = $search : $closest;
    return $closest;
}

$images = array();

if (isset($_GET['image'])) {
    foreach ($_GET['image'] as $key => $src) {

        // Error suppression
        if ($src != "" && $src != "/") {
      	    // get image width
      	    $width = (int) $_GET['width'][$key];

            // Get the full path for the image
            if (substr($src,0,7) == "http://") {
                $image = $src;
            } else {
                $base_path = str_replace(basename(__FILE__),"",__FILE__);
                $image = $base_path.str_replace("/",DIRECTORY_SEPARATOR,$src);
            }

      	    // get the optimum width from the available options
      	    $width = closest($width, $sizes);

      	    // Initialise image
      	    $crispy = new resize($image,$width,$cache);

            $newSrc = $crispy->resizeImage();
      	
          	$images[] =  array('og_src' => $src, 'src' => $newSrc);
        } else {
            $images[] =  array('og_src' => $src, 'src' => '', 'fail' => true);
        }
    }
}

echo json_encode($images);

?>