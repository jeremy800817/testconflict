Ext.define('snap.view.spotorder.SpotOrderController', {
	extend: 'Ext.app.ViewController',
	alias: 'controller.spotorder-spotorder',
	spotOrderAction: function (btn) {
		if (btn.id == 'sellbtn') {
			Ext.getCmp('sellorbuy').setValue('sell');
		} else if (btn.id == 'buybtn') {
			Ext.getCmp('sellorbuy').setValue('buy');
		}
		var view = this.getView(),
			model = Ext.create('snap.view.spotorder.FormModel', view.getValues());
		var errors = { productitem: true, amount: true, weight: true, id: true, uuid:true };		
		if (view.getValues().uuid == null) {
			errors.uuid = "Sorry Your Order Cannot Be Process, Our ACE Connection Currently Offline";
			Ext.toast(errors.uuid,4000);
		}
		if (view.getValues().productitem == null) {
			errors.productitem = "Product is required";
		}
		if (view.getValues().amount == "" && view.getValues().weight == "") {
			errors.amount = "This field is required";
		}
		var regex = /^[0-9]*\.?[0-9]*$/;
		if (view.getValues().amount != "" && !regex.test(view.getValues().amount)) {
			errors.amount = "Enter valid number";
		}
		var validationerror = 0;
		for (var err in errors) {
			if (errors[err] != true) {
				validationerror++;
			}
		}		
		if (validationerror != 0) {
			//var errors = model.getValidation().getData();
			Object.keys(errors).forEach(function (f) {
				//console.log(view);
				var field = view.getFields(f);
				if (field && errors[f] !== true) {
					field.markInvalid(errors[f]);
				}
			});
			return false;
		}
		var form = this.getView();
		form.submit({
			submitEmptyText: false,
			url: 'index.php',
			method: 'POST',
			params: { hdl: 'spotorder', action: 'makeOrder' },
			waitMsg: 'Processing',
			success: function (frm, action) { //success
				Ext.Msg.alert('Success', 'Submitted Successfully !', Ext.emptyFn);
			},
			failure: function (frm, action) {
				Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
			}
		});
	}
});