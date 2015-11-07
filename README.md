NOT MAINTAINED
==============
I recommend you use a suggested solution from http://responsiveimages.org/ as this technique is outdated.

Crispy resize
=============
Crispy resize is a responsive images plugin that uses PHP-GD and ajax to lazy load the correct image size for the display width.

### Requirements:

* A Server
* PHP
* GD
* jQuery
* [smartresize](http://www.paulirish.com/2009/throttled-smartresize-jquery-event-handler/)

How to use
----------

Add the resize-class.php in php/lib/ to your php libs folder.
Add resize.php in the root to your web root.

Set your potential image display sizes in the array at the top of resize.php and the path to the cache folder.

Include the resize.js plugin in /js/ on your page.

Set the breakpoints at which you want to refresh the image in resize.js (or resize.coffee if you want to compile the plugin.)

Replace your `<img>` tags with the following html:

	<div data-src="[put your image src here]" data-alt="crispy" class="img-wrap js-crispy">
        <noscript><img src="[put your image src here]" alt="Crispy"></noscript>
    </div>

Add some CSS to size your img-wrap element and make sure the image within it fills it.

	.img-wrap {
	  display: inline-block;
	  width: 10em;
	}
	.img-wrap img {
	  max-width: 100%;
	  display: block;
	  width: 100%;
	}

Done!




