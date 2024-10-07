Ext.define('snap.view.futureorder.FutureOrderController', {
	extend: 'Ext.app.ViewController',
	alias: 'controller.futureorder-futureorder',
	futureOrderAction: function (btn) {
		var view = this.getView(),
			model = Ext.create('snap.view.futureorder.FormModel', view.getValues());
		var errors = { foproductitem: true, foamount: true, foweight: true, fobuyprice: true, fosellprice: true };
		if (view.getValues().foproductitem == null) {
			errors.foproductitem = "Product is required";
		}
		if (view.getValues().foamount == null && view.getValues().foweight == null) {
			errors.foamount = "Total Value is required";
		}
		if (view.getValues().fobuyprice == null && view.getValues().fosellprice == null) {
			errors.fobuyprice = "Buy price is required";
		}
		var validationerror = 0;
		for (var err in errors) {
			if (errors[err] != true) {
				validationerror++;
			}
		}	
		if (validationerror != 0) {			
			Object.keys(errors).forEach(function (f) {				
				var field = view.getFields(f);
				if (field && errors[f] !== true) {
					field.markInvalid(errors[f]);
				}
			});
			return false;
		}
		//var errors = { foproductitem: true, foamount: true, foweight: true, fobuyprice: true, fosellprice: true };	
		var form = this.getView();		
		form.submit({
			submitEmptyText: false,
			url: 'index.php',
			method: 'POST',
			params: { hdl: 'order', action: 'doFutureOrder' },
			waitMsg: 'Processing',
			success: function (form, action) { //success				
				Ext.Msg.alert('Success', 'Submitted Successfully !', Ext.emptyFn);
			},
			failure: function (form, action) {	
				Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
			}
		});
	}

});