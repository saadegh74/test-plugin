(function ($) {

	"use strict";

	function widgetBrands($scope) {

		var $target = $scope.find('.brands-list--slider'),
			settings = $target.data('slider_options') || {};

		if (!$target.length) {
			return;
		}

		settings.slide = '.brands-list__item';

		JetElements.initCarousel($target, settings);
	}

	window.widgetBrands = widgetBrands;

})(jQuery);
