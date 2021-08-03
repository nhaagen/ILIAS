il = il || {};
il.UI = il.UI || {};
il.UI.menu = il.UI.menu || {};

il.UI.menu.drilldown = {

	classes : {
		MENU: 'il-drilldown',
		BUTTON: 'menulevel',
		ACTIVE: 'engaged'
	},

	init : function (component_id, back_signal) {
		var i,
			dd = document.getElementById(component_id),
			parts = this.getMenuParts(dd);
		
		$(document).on(back_signal, this.menuOnUplevel);
		for (i = 0; i < parts.buttons.length; i = i + 1) { 
			parts.buttons[i].addEventListener('click', this.menulevelOnClick);
		}
		
		parts.buttons[0].classList.add(classes.ACTIVE);
		parts.title.innerHTML = parts.buttons[0].innerHTML;
	},
	
	getMenuParts : function(menu_inner_element) {
		var classes = il.UI.menu.drilldown.classes,
			dd = menu_inner_element.closest('.' + classes.MENU),
			parts = {
				title : dd.getElementsByTagName('h2')[0],
				buttons : dd.getElementsByClassName(classes.BUTTON),
				active : dd.getElementsByClassName(classes.ACTIVE).item(0),
				upper : null
			};

			if(parts.active) {
				parts.upper = parts.active.closest('ul').parentElement.getElementsByClassName(classes.BUTTON).item(0);
			}
			return parts;
	},

	menulevelOnClick : function(event) {
		var classes = il.UI.menu.drilldown.classes,
			parts = il.UI.menu.drilldown.getMenuParts(event.currentTarget);
			
		for (i = 0; i < parts.buttons.length; i = i + 1) { 
			parts.buttons[i].classList.remove(classes.ACTIVE);
		}
		event.currentTarget.classList.add(classes.ACTIVE);
		parts.title.innerHTML = event.currentTarget.innerHTML;
	},

	menuOnUplevel : function(event) {
		var classes = il.UI.menu.drilldown.classes,
			parts = il.UI.menu.drilldown.getMenuParts(event.target);

		for (i = 0; i < parts.buttons.length; i = i + 1) { 
			parts.buttons[i].classList.remove(classes.ACTIVE);
		}
		if(parts.upper) {
			parts.upper.classList.add(classes.ACTIVE);
			parts.title.innerHTML = parts.upper.innerHTML;
		}
	}
};
