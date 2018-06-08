((Joomla) => {
	if (!Joomla) {
		throw new Error('Joomla API is not properly initiated');
	}

	document.addEventListener('DOMContentLoaded', function (event) {

		var buttonSelect = document.getElementsByClassName("button-select-agosm");

		var myFunctionButtonSelect = function () {
			this.parentNode.parentNode.parentNode.querySelector('[role="dialog"]').open();
		};

		for (var i = 0; i < buttonSelect.length; i++) {
			buttonSelect[i].addEventListener('click', myFunctionButtonSelect, false);
		}



		var buttonSaveSelect = document.getElementsByClassName("button-agosm-save-selected");

		var myFunctionButtonSaveSelect = function (e) {
			e.preventDefault();
			e.stopPropagation();
			this.parentNode.parentNode.parentNode.parentNode.parentNode.querySelector('[role="input"]').value = localStorage.getItem('cordsTextFieldValue');
			Joomla.Modal.getCurrent().close();
		};

		for (var i = 0; i < buttonSaveSelect.length; i++) {
			buttonSaveSelect[i].addEventListener('click', myFunctionButtonSaveSelect, false);
		}


	});


})(Joomla);
