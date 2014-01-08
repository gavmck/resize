/*
    Resize plugin.
    Grabs appropriately sized image from the server, with noscript fallback and error handling
    Author: Gavyn McKenzie @ Etch Apps
    Requires: jQuery, smartresize

    - Find the images in the dom
    - Get their size and src
    - Post them to the server
    - Populate the image tags on reply
*/


(function() {
  var crispy,
    __slice = [].slice;

  crispy = {
    els: ".js-crispy",
    images: [],
    breakpoints: ["32em", "48em", "62em", "76em"],
    debugMode: true,
    debug: function() {
      var vars;
      vars = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
      if (this.debugMode && (typeof console !== "undefined" && console !== null)) {
        if (console.log.apply != null) {
          return console.log.apply(console, vars);
        } else {
          return console.log(vars);
        }
      }
    },
    init: function() {
      this.debug("Initialising crispy");
      this.currentBreakpoint = this.getCurrentBreakpoint();
      this.gather();
      return this.bind();
    },
    bind: function() {
      var _this = this;
      return $(window).smartresize(function() {
        return _this.refreshImages();
      });
    },
    windowWidth: function() {
      return $(window).width() / Number($("body").css("font-size"));
    },
    getCurrentBreakpoint: function() {
      var bp, breakpoint, _fn, _i, _len, _ref,
        _this = this;
      bp = this.breakpoints[0];
      _ref = this.breakpoints;
      _fn = function(breakpoint) {
        if (window.matchMedia && window.matchMedia("all and (min-width: " + breakpoint + ")").matches) {
          return bp = breakpoint;
        }
      };
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        breakpoint = _ref[_i];
        _fn(breakpoint);
      }
      this.debug(bp);
      return bp;
    },
    testBreakpointChange: function() {
      var bp;
      bp = this.getCurrentBreakpoint();
      if (this.currentBreakpoint === bp) {
        return false;
      } else {
        this.currentBreakpoint = bp;
        return true;
      }
    },
    refreshImages: function() {
      if (this.testBreakpointChange()) {
        this.debug("Breakpoint change");
        return this.gather();
      }
    },
    gather: function() {
      var el, els, _i, _len;
      els = $(this.els);
      this.debug(els.length, "images found");
      this.images = [];
      for (_i = 0, _len = els.length; _i < _len; _i++) {
        el = els[_i];
        this.add(el);
      }
      return this.grabFromServer();
    },
    add: function(image) {
      image = $(image);
      return this.images.push({
        src: image.attr("data-src"),
        width: image.width()
      });
    },
    buildQuery: function() {
      var addText, data, img, _i, _len, _ref,
        _this = this;
      addText = function(img, text) {
        return text += "image[]=" + img.src + "&width[]=" + img.width + "&";
      };
      data = "";
      _ref = this.images;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        img = _ref[_i];
        data = addText(img, data);
      }
      return data = data.slice(0, -1);
    },
    grabFromServer: function() {
      var data,
        _this = this;
      this.debug("Grabbing images from server");
      data = this.buildQuery();
      return $.ajax({
        type: "POST",
        url: "resize.php",
        data: data,
        success: function(data) {
          var image, _i, _len;
          for (_i = 0, _len = data.length; _i < _len; _i++) {
            image = data[_i];
            _this.loadImage(image);
          }
          return true;
        }
      });
    },
    loadImage: function(image) {
      var el, img,
        _this = this;
      this.debug("Loading image");
      el = $("[data-src='" + image.og_src + "']");
      img = $("<img />");
      img.attr("src", image.src).attr("alt", el.attr("data-alt"));
      if (el.children("img").length) {
        this.debug("Already have an img");
        return el.children("img").attr("src", image.src);
      } else {
        return img.load(function() {
          _this.debug("Adding img to page");
          el.append(img);
          return el.addClass('img-loaded');
        });
      }
    }
  };

  $(document).ready(function() {
    $(".wrap").removeClass("no-js").addClass("js");
    return crispy.init();
  });

}).call(this);
