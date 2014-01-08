<?php

   # ========================================================================#
   #
   #  Author:    Gavyn McKenzie
   #  Version:	 1.0
   #  Date:      16 Oct 2013
   #  Purpose:   Resizes and saves image
   #  Requires : Requires PHP5, GD library.
   #  Usage Example:
   #                     include("classes/resize_class.php");
   #                     $crispy = new resize('images/cars/large/input.jpg',700,'/img/cache/');
   #                     $src = $crispy -> resizeImage();
   #
   #
   # ========================================================================#


		Class resize {

			// *** Class variables
			private $image;
		    private $width;
		    private $height;
		    private $newWidth;
			private $imageResized;
			private $imagetype;
			private $src;
            private $cache;
			private $path;

			function __construct($fileName, $width, $cache) {
				
				$this->src = $fileName;
                $this->newWidth = $width;
                $this->cache = $cache;
				$this->path = $this->setPath($width);
				               
				$this->imageType = exif_imagetype($fileName);

				switch($this->imageType)
				{
					case IMAGETYPE_JPEG:
						$this->path .= '.jpg';
						break;

					case IMAGETYPE_GIF:
						$this->path .= '.gif';
						break;

					case IMAGETYPE_PNG:
						$this->path .= '.png';
						break;

					default:
						// *** Not recognised
						break;
				}

			}

			## --------------------------------------------------------

			private function openImage($file) {

				switch($this->imageType) {
                    case IMAGETYPE_JPEG:
						$img = @imagecreatefromjpeg($file);
						break;
					case IMAGETYPE_GIF:
						$img = @imagecreatefromgif($file);
						break;
					case IMAGETYPE_PNG:
						$img = @imagecreatefrompng($file);
						break;
					default:
						$img = false;
						break;
				}
				return $img;
			}

			## --------------------------------------------------------

			private function setPath($width) {
				// build new filename
		        $info = pathinfo($this->src);
		        $extension = isset($info['extension']) ? $info['extension'] : '';

		    	$path = $this->cache.$width.'_'.$this->imageHash().'_'.basename($this->src,'.'.$extension);

				return $path;
			}

			## --------------------------------------------------------

			private function imageHash() {
				$image = $this->src;

		    	$this->imageType = exif_imagetype($image);
		    	
		    	if (substr($image,0,7) == "http://") {
		    		$h = get_headers($image, 1);

		    		$dt = NULL;
		    		if (!($h || strstr($h[0], '200') === FALSE)) {
		    		    $dt = new \DateTime($h['Last-Modified']);//php 5.3
		    		}

		    		$modified = $dt;
		    	} else {
		    		$modified = filemtime($image);
		    	}
		    	// Mix in the modified with the full image name for a unique ID MD5
		    	return md5($modified.$image);
			}

			## --------------------------------------------------------

			public function resizeImage() {
				if (!file_exists($this->path)) {
					$this->image = $this->openImage($this->src);
					
					// *** Get width and height
				    if ($this->image) {
					    $this->width  = imagesx($this->image);
					    $this->height = imagesy($this->image);
					}

					$ratio = $this->height/$this->width;
					$newHeight = $this->newWidth*$ratio;

					// *** Resample - create image canvas of x, y size
					$this->imageResized = imagecreatetruecolor($this->newWidth, $newHeight);
					imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $this->newWidth, $newHeight, $this->width, $this->height);
				
					return $this->saveImage($this->newWidth);
				} else {
					return $this->path;
				}

			}

			## --------------------------------------------------------

			public function saveImage($newWidth) {
				
				switch($this->imageType) {
					case IMAGETYPE_JPEG:
						if (imagetypes() & IMG_JPG) {
							imagejpeg($this->imageResized, $this->path, 100);
						}
						break;

					case IMAGETYPE_GIF:
						if (imagetypes() & IMG_GIF) {
							imagegif($this->imageResized, $this->path);
						}
						break;

					case IMAGETYPE_PNG:
						if (imagetypes() & IMG_PNG) {
							 imagepng($this->imageResized, $this->path, 0);
						}
						break;

					default:
						// *** Not recognised
						break;
				}

				imagedestroy($this->imageResized);

				return $this->path;
			}


			## --------------------------------------------------------

		}
?>
