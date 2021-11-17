jQuery( function( $ ) {

	$(document).ready(function(e) {

	// if is Page or Post continue...
	if( $('body').hasClass('post-php') && typeof YoastSEO !== "undefined" && typeof SCFYoastSEOAnalysis == "undefined" ){

		/**
         * Set up the SCF Yoast SEO Analysis plugin
         */
        var SCFYoastSEOAnalysis = function() {

			console.log('SCFYoastSEOAnalysis');

            YoastSEO.app.registerPlugin('SCFYoastSEOAnalysis', {status: 'ready'});

			/**
			 * @param modification 	{string} 	The name of the filter
			 * @param callable 		{function} 	The callable
			 * @param pluginName 	    {string} 	The plugin that is registering the modification.
			 * @param priority 		{number} 	(optional) Used to specify the order in which the callables
			 * 									associated with a particular filter are called. Lower numbers
			 * 									correspond with earlier execution.
			 */
            YoastSEO.app.registerModification('content', this.addScfFieldsToContent, 'SCFYoastSEOAnalysis', 5);

            this.analysisTimeout = 0;
            this.bindListeners();

            // Re-analyse SEO score
            $('.btn-add-repeat-group, .btn-remove-image').on('click', this.bindListeners);
			if (wp.media) wp.media.view.Modal.prototype.on('close', function() {
				window.setTimeout( function() { YoastSEO.app.pluginReloaded('SCFYoastSEOAnalysis'); }, 200 );
			});

        };

        /**
         * Bind listeners to text fields (input, textarea and wysiwyg)
         */
        SCFYoastSEOAnalysis.prototype.bindListeners = function() {

			console.log('bindListeners');

			SCFYoastSEOAnalysis.analysisTimeout = window.setTimeout( function() { YoastSEO.app.pluginReloaded('SCFYoastSEOAnalysis'); }, 200 );

			$('#post-body, #edittag').find('input[type="text"][name^="smart-custom-fields"], textarea[name^="smart-custom-fields"], .smart-cf-field-type-wysiwyg iframe body#tinymce').on('keyup paste cut blur focus change', function() {

				if ( SCFYoastSEOAnalysis.analysisTimeout ) {
					window.clearTimeout(SCFYoastSEOAnalysis.analysisTimeout);
				}
			});

        };

		/**
		 * Adds some text to the data...
		 *
		 * @param data The data to modify
		 */
        SCFYoastSEOAnalysis.prototype.addScfFieldsToContent = function(data) {
            console.log('addScfFieldsToContent');

			var scf_content = ' ';

			$('#post-body, #edittag').find('input[type="text"][name^="smart-custom-fields"], textarea[name^="smart-custom-fields"]').each(function() {
                scf_content += ' ' + $(this).val();
            });

			$(".smart-cf-field-type-wysiwyg iframe").contents().find("body#tinymce").each(function() {
                scf_content += ' ' + $(this).html();
            });

			$('#post-body, #edittag').find('.smart-cf-upload-image img').each(function() {
                scf_content += '<img src="' + $(this).attr('src') + '" alt="' + $(this).attr('alt') + '" />';
            });

			data = data + scf_content;

            return data.trim();
        };

        new SCFYoastSEOAnalysis();
	}

    });

});
