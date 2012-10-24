jQuery(document).ready(function() {
	application.server = location.protocol + '//' + location.host.replace('frontend-', 'backend-');
	application.init();
});
