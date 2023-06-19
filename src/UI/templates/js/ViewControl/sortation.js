il = il || {};
il.UI = il.UI || {};
il.UI.viewcontrol = il.UI.viewcontrol || {};

(function($, viewcontrol) {
	viewcontrol.sortation = (function($) {
		var onInternalSelect = function(event, signalData, signal, component_id) {
			let triggerer = signalData.triggerer[0]; 			//the shy-button
			let param = triggerer.getAttribute('data-action'); 	//the actual value
			let sortation = $('#' + component_id);					//the component itself
			let sigdata = {
				'id' : signal,
				'event' : 'sort',
				'triggerer' : sortation,
				'options' : {
					'sortation': param
				}
			};
			let dd = sortation.find('.dropdown-toggle');		//the dropdown-toggle
			let label = signalData.triggerer.contents()[0].data;

console.log(param);
console.log(label);

			//close dropdown and set current value
			dd.dropdown('toggle');
			dd.contents()[0].data = 
				signalData.options.label_prefix
				+ ' '
				+ label
				+ ' ';
			dd.parent().find('li').each(
				function (idx, li) {
					if(li.getElementsByTagName('button')[0].innerHTML === label) {
						li.className = 'selected';
					} else {
						li.className = '';
					}
				} 
			);


			sortation.trigger(signal, sigdata);
		};

		return {
			onInternalSelect: onInternalSelect
		}

	})($);
})($, il.UI.viewcontrol);
