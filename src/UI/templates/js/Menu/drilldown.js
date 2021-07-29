il = il || {};
il.UI = il.UI || {};
il.UI.menu = il.UI.menu || {};

il.UI.menu.drilldown = {

	classes : {
		MENU: 'il-drilldown',
		BUTTON: 'menulevel',
		ACTIVE: 'engaged'
	},

	init : function (component_id) {
		var i,
			dd = document.getElementById(component_id),
			buttons = dd.getElementsByClassName(this.classes.BUTTON);
		
		for (i = 0; i < buttons.length; i = i + 1) { 
			buttons[i].addEventListener('click', this.menulevelOnClick);
		}
	},
	
	menulevelOnClick : function(event) {
		var i,
			classes = il.UI.menu.drilldown.classes,
			current = event.currentTarget,
			dd = current.closest('.' + classes.MENU),
			buttons = dd.getElementsByClassName(classes.BUTTON);

		for (i = 0; i < buttons.length; i = i + 1) { 
			buttons[i].classList.remove(classes.ACTIVE);
		}
		current.classList.add(classes.ACTIVE);
	}
};
