(function($, undefined) {

$.nette.ext('spinner', {
	init: function () {
		this.spinner = this.createSpinner();
                this.spinner.appendTo('body');
	},
	start: function () {
		this.counter++;
		if (this.counter === 1) {
                        this.spinner.show(this.speed);
		}
	},
	complete: function () {
		this.counter--;
		if (this.counter <= 0) {
                        this.spinner.hide(this.speed);
		}
	}
}, {
	createSpinner: function () {
                return $('<div>', {
                        class: 'glyphicon glyphicon-refresh glyphicon-spin',
			id: 'ajax-spinner',
			css: {
				display: 'none',
                                marginLeft: $(window).width()/2,
                                marginTop: $(window).height()/2,
                                zIndex: '9999'
			}
		});
	},
	spinner: null,
	speed: undefined,
	counter: 0
});

})(jQuery);
