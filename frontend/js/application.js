var application = {
	proxy: 'proxy.php',
	server: '',

	init: function() {
		var spinnerOptions = {
			lines: 9,
			length: 1,
			width: 4,
			radius: 6,
			corners: 1.0,
			rotate: 0,
			trail: 100,
			speed: 1,
			hwaccel: true,
			top: '0px',
			left: '0px'
		};

		var spinner = new Spinner(spinnerOptions);
		jQuery("#loading").ajaxStart(function() {
			spinner.spin(this);
			jQuery(this).show();
		});

		jQuery("#loading").ajaxStop(function() {
			jQuery(this).hide();
			spinner.stop();
		});

		jQuery(document).on('click', '#login', function() {
			application.login(jQuery('#server').val(), jQuery('#username').val(), jQuery('#password').val());
		});

		jQuery(document).on('click', '#logout', function() {
			application.logout();
		});

		jQuery(document).on('click', '.option', function(event) {
			event.stopImmediatePropagation();
			var action = jQuery(this).attr('href');
			var verb = jQuery(this).data('verb');

			var canDo = true;
			if (verb == 'DELETE') {
				canDo = confirm('Are you sure you want to delete this item?');
			}

			if (canDo) {
				var data = {};
				if ((verb == 'POST') || (verb == 'PUT') || (verb == 'DELETE')) {
					data = jQuery(this).closest('form').serializeArray();
				}

				application.perform(action, verb, data);
			}

			return false;
		});

		jQuery.ajax({
			url: application.proxy
		}).done(function(response) {
			application.run();
		}).fail(function(response) {
			if (response.status == 401) {
				application.authenticate(application.server);
			} else {
				application.fail(response.status);
				application.render('logout');
			}
		});
	},

	authenticate: function(server, username) {
		jQuery('#logout').remove();

		application.render('authenticate', {
			server: server,
			username: username
		});
	},

	login: function(server, username, password) {
		jQuery.ajax({
			url: application.proxy,
			type: 'POST',
			data: {
				server: server,
				username: username,
				password: password
			}
		}).done(function() {
			jQuery('#alert').empty();
			application.run(server);
		}).fail(function(response) {
			application.authenticate(server, username);
			application.fail(response.status);
		});
	},

	logout: function() {
		jQuery.ajax({
			url: application.proxy,
			type: 'POST',
			data: {
				logout: true
			}
		}).done(function() {
			jQuery('#alert').empty();
			application.authenticate(application.server);
		});
	},

	run: function(server) {
		application.server = server;

		application.render('logout', {}, '.nav-collapse', 'append');

		jQuery.ajax({
			url: application.proxy,
			type: 'GET',
			accepts: 'application/json',
			dataType: 'json',
			headers: {
				Forward: server
			}
		}).done(function(response) {
			application.process(response);
		}).fail(function(response) {
			application.authenticate(server);
			application.fail(response.status);
		});
	},

	perform: function(action, verb, data) {
		jQuery('#alert').empty();

		jQuery.ajax({
			url: application.proxy,
			type: verb,
			accepts: 'application/json',
			dataType: 'json',
			data: data,
			headers: {
				Forward: action
			}
		}).done(function(response) {
			application.success(verb);
			application.process(response);
		}).fail(function(response) {
			application.fail(response.status);
		});
	},

	process: function(response) {
		if (response != null) {
			jQuery('#content').empty();
			// Options
			if (response.options != null) {
				application.render('options', {
					options: response.options
				}, '#content', 'append');
			}
			// Users
			if (response.users != null) {
				application.render('users', {
					users: response.users
				}, '#content', 'append');
			}
			if (response.user != null) {
				response.user.isNew = function() {
					return this.id == null;
				};
				application.render('user', {
					user: response.user
				}, '#content', 'append');
			}
			// Friends
			if (response.friends != null) {
				application.render('friends', {
					friends: response.friends
				}, '#content', 'append');
			}
			if (response.friend != null) {
				response.friend.isNew = function() {
					return this.id == null;
				};
				application.render('friend', {
					friend: response.friend
				}, '#content', 'append');
			}
		} else {
			application.render('welcome');
		}
	},

	success: function(verb) {
		if (verb == 'POST') {
			application.alert('Created!', 'success');
		} else if (verb == 'PUT') {
			application.alert('Updated!', 'success');
		} else if (verb == 'DELETE') {
			application.alert('Deleted!', 'success');
		}
	},

	fail: function(status) {
		if (status == 400) {
			application.alert('Invalid request', 'error');
		} else if (status == 401) {
			application.alert('Wrong credentials', 'warning');
		} else if (status == 404) {
			application.alert('The requested URL was not found', 'error');
		} else if (status == 500) {
			application.alert('The server encountered an error while processing your request', 'error');
		} else if (status == 501) {
			application.alert('The requested method is not implemented', 'error');
		}
	},

	alert: function(message, type) {
		application.render('alert', {
			label: type.charAt(0).toUpperCase() + type.slice(1),
			message: message,
			type: type
		}, '#alert');
	},

	/**
	 * Render a template file with the data and place the output in a target
	 * within the document using a method as replace, append, prepend or return
	 */
	render: function(template, data, target, method) {
		if (data == null) {
			data = {};
		}

		if (target == null) {
			target = '#content';
		}

		if (method == null) {
			method = 'replace';
		}

		var source = jQuery('#' + template + '-template').html();
		var template = Handlebars.compile(source);
		var output = template(data);

		if (method == 'return') {
			return output;
		} else if (method == 'replace') {
			jQuery(target).html(output);
		} else if (method == 'append') {
			jQuery(target).append(output);
		} else if (method == 'prepend') {
			jQuery(target).prepend(output);
		}
	}
};