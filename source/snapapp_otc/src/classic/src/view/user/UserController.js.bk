Ext.define('snap.view.user.UserController', {
	extend: 'snap.view.gridpanel.BaseController',
	alias: 'controller.user-user',

	requires: [
		'snap.view.gridpanel.BaseController'
	],

	getRoleGrid: function(theGridFormPanel) {
		var accessFieldSet = theGridFormPanel.down('form').getComponent('user_column_main').getComponent('user_column_2').getComponent('user_access_fieldset');
		var roleGrid = accessFieldSet.getComponent('user_userrole');
		return roleGrid;
	},

	onPostLoadForm: function(theGridFormPanel, theGridForm, theRecord) {
		theGridForm.setValues({
			password: ''
		});
		var roleGrid = this.getRoleGrid(theGridFormPanel);
		roleGrid.setEmptyText('Loading Roles...');
		snap.getApplication().sendRequest({
			hdl: 'user', action: 'getuserrole', id: theGridForm.findField('id').getValue()
		}, 'Fetching data from server....').then(
		function(data) {
			if (data.success) {
				var roleStore = roleGrid.getStore();
				roleStore.removeAll();
				roleStore.add(data.roledata);
			}
		});
	},

	onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm) {
		var isEditMode = (theGridForm.findField('id').getValue().length > 0) ? true : false;

		var password = theGridForm.findField('userpassword').getValue();
		if (password != '' || password != null || password !== undefined) {
			var confirmpassword = theGridForm.findField('confirmpassword').getValue();
			if (password != confirmpassword) {
				theGridForm.findField('confirmpassword').markInvalid('Password do not match');
				return false;
			}
		}
		if(isEditMode) {
			theGridForm.setValues({action: 'edit'});
		} else {
			theGridForm.setValues({action: 'add'});
		}

		var roleGrid = this.getRoleGrid(theGridFormPanel);
		var roleStore = roleGrid.getStore();
		var roleCount = roleStore.count();
		if (roleCount > 0) {
			var tmpRoles = [];
			roleStore.each(function(rec) {
				tmpRoles.push(rec.get('id'));
			});
			var selectedroles = tmpRoles.join("||");
			theGridForm.setValues({selectedroles: selectedroles});
		} else {
			Ext.MessageBox.show({
				title: 'Error Message',
				msg: 'Select roles',
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
			return false;
		}

		return true;
	}
});
