(function($) {

    // here we go!
    $.MultilangInput = function(element, options) {

        var plugin = this;

        var $element = $(element),  // reference to the jQuery version of DOM element the plugin is attached to
             element = element;        // reference to the actual DOM element

        plugin.init = function() {
			var input = $(element).children('input[type="text"]');
        	var container = input.parent();
			
		}

        plugin.init();

    }

    // add the plugin to the jQuery.fn object
    $.fn.MultilangInput = function(options) {

        return this.each(function() {

            // if plugin has not already been attached to the element
            if (undefined == $(this).data('MultilangInput')) {

                // create a new instance of the plugin
                // pass the DOM element and the user-provided options as arguments
                var plugin = new $.MultilangInput(this, options);

                // in the jQuery version of the element
                // store a reference to the plugin object
                // you can later access the plugin and its methods and properties like
                // element.data('MultilangInput').publicMethod(arg1, arg2, ... argn) or
                // element.data('MultilangInput').settings.propertyName
                $(this).data('MultilangInput', plugin);

            }

        });

    }

})(jQuery);