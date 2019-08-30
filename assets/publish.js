/**
* Entry Deep Duplicator
*
* @author Deux Huit Huit
*/
(function ($, Sym, undefined) {

	'use strict';

	var sels = {
		btn: '.js-entry-deep-duplicator-btn',
		actions: '#contents-actions ul.actions'
	};

	var onBtnClick = function (event) {

	};

	var init = function () {
		var btn = $('<a />').addClass('js-entry-deep-duplicator-btn').attr({
			href: Sym.Context.get('symphony') + '/extension/entry_deep_duplicator/clone?entry=' + Sym.Context.get('env').entry_id, // jshint ignore:line
			title: 'Duplicate this entry'
		}).append('<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 4C14 3.44853 14 3.16439 14 2.99899C14 1.89442 13.1046 1 12 1H4C2.89543 1 2 1.89543 2 3V13C2 14.1046 2.89543 15 4 15H5" stroke="currentColor" stroke-width="2"/><rect x="6" y="5" width="12" height="14" rx="2" stroke="currentColor" stroke-width="2"/></svg>');

		Sym.Elements.body.find(sels.actions).prepend(btn);
		Sym.Elements.body.on('click', sels.btn, onBtnClick);
	};

	$(init);
	
})(window.jQuery, window.Symphony);
