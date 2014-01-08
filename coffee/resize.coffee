###
    Resize plugin.
    Grabs appropriately sized image from the server, with noscript fallback and error handling
    Author: Gavyn McKenzie @ Etch Apps
    Requires: jQuery, smartresize

    - Find the images in the dom
    - Get their size and src
    - Post them to the server
    - Populate the image tags on reply
###

crispy = 
    els: ".js-crispy"
    images: []
    breakpoints: [
        "32em"
        "48em"
        "62em"
        "76em"
    ]
    debugMode: true

    # Console debugging
    debug: (vars...) ->
        if @debugMode and console?
            if console.log.apply? then  console.log.apply(console, vars) else console.log vars

    # Init crispy resize
    init: () ->
        @debug "Initialising crispy"
        @currentBreakpoint = @getCurrentBreakpoint()

        @gather()

        @bind()
        
    # Bind resize events
    bind: () ->

        $(window).smartresize =>
            @refreshImages()

    # Returns window width in em values
    windowWidth: () ->
        return $(window).width()/Number($("body").css("font-size"))

    # Return the current breakpoint
    getCurrentBreakpoint: () ->
        bp = @breakpoints[0]

        # for each breakpoint, test if window width is wider than that
        for breakpoint in @breakpoints then do (breakpoint) =>
            if window.matchMedia and window.matchMedia("all and (min-width: "+breakpoint+")").matches
                bp = breakpoint

        @debug bp
        return bp

    # Test if the breakpoint has changed
    testBreakpointChange: () ->
        bp = @getCurrentBreakpoint()
        if @currentBreakpoint == bp
            return false
        else
            @currentBreakpoint = bp
            return true

    # Refresh the images if the breakpoint has changed
    refreshImages: () ->
        if @testBreakpointChange()
            @debug "Breakpoint change"
            @gather()

    # gather the images from the DOM
    gather: () ->
        els = $(@els)

        @debug els.length, "images found"

        # Reset images array
        @images = []
        
        @add el for el in els

        @grabFromServer()

    # Adds images and dimensions to @images array
    add: (image) ->
        image = $ image
        @images.push
            src: image.attr("data-src")
            width: image.width()

    # Build the url post params
    buildQuery: () ->
        addText = (img,text) =>
            text += "image[]="+img.src+"&width[]="+img.width+"&";

        data = ""

        data = addText img, data for img in @images

        # Remove the last &
        data = data.slice(0,-1)

    # Grab the image paths from the server
    grabFromServer: () ->
        @debug "Grabbing images from server"

        data = @buildQuery()

        $.ajax
            type: "POST"
            url: "resize.php"
            data: data
            success: (data) =>
                # Received array of new img srcs
                # Load the images into the DOM

                @loadImage(image) for image in data
                true

    # Load images into DOM
    loadImage: (image) ->
        @debug "Loading image"
        # Get the image to replace
        el = $ "[data-src='"+image.og_src+"']"

        img = $ "<img />"
        
        img.attr("src",image.src).attr("alt", el.attr("data-alt"))

        if el.children("img").length
            @debug "Already have an img"
            el.children("img").attr "src", image.src
        else
            img.load => 
                @debug "Adding img to page"
                el.append img
                
                # Set as loaded
                el.addClass('img-loaded')

# Initialise crispy on doc ready
$(document).ready ->
    $(".wrap").removeClass("no-js").addClass("js")
    crispy.init()
   