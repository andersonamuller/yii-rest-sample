Handlebars.registerHelper('eachProperty', function(context, options) {
	var buffer = '', property;

	for (property in context) {
		if (context.hasOwnProperty(property)) {
			buffer += options.fn({
				property: property,
				value: context[property]
			});
		}
	}

	return buffer;
});

Handlebars.registerPartial('options-partial', jQuery('#options-template').html());

jQuery(document).ready(function() {
	application.server = location.protocol + '//' + location.host.replace('frontend-', 'backend-');
	application.init();
});
