Ext.define('snap.view.orderdashboard.UnfulfillPODashboardController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.unfulfillpodashboard-unfulfillpodashboard',

    summaryAction: function (elemnt) {
		var form = elemnt.lookupController().lookupReference('transactionlisting-form').getForm();
        transactionlisting = form.getFieldValues();
		summaryfromdate = Date.parse(transactionlisting.fromdate);				
        summarytodate = Date.parse(transactionlisting.todate);
		summarytype = transactionlisting.type;
		partnerid = transactionlisting.gtpcustomernametransactionlisting;
		//if (form.validate()) {			
		//debugger;
		//transactionlisting.gtpcustomernametransactionlisting
		if(transactionlisting.usertypefortransactionlisting == "Operator" || transactionlisting.usertypefortransactionlisting == "Sale"){
			
			if (form.isValid()) {
				// Compare fromdate with todate
				if(summarytodate >= summaryfromdate){
					if (transactionlisting.gtpcustomernametransactionlisting != null) {
						var url = 'index.php?hdl=order&action=getOrderStatementsForCustomer&summaryfromdate='+summaryfromdate+'&summarytodate='+summarytodate+'&summarytype='+summarytype+'&partnerid='+partnerid;
						Ext.Ajax.request({
							url: url,
							method: 'get',
							waitMsg: 'Processing',
							//params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
							autoAbort: false,
							success: function (result) {
								var win = window.open('');
									win.location = url;
									win.focus();
							},
							failure: function () {
								
								Ext.MessageBox.show({
									title: 'Error Message',
									msg: 'Failed to retrieve data',
									buttons: Ext.MessageBox.OK,
									icon: Ext.MessageBox.ERROR
								});
							}
						});
					} else {
						 Ext.MessageBox.show({
							title: "ERROR-A1001",
							msg: "Please Select GTP Customer",
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.WARNING
						});
					}
					/*
					var url = 'index.php?hdl=order&action=getOrderStatementsForCustomer&summaryfromdate='+summaryfromdate+'&summarytodate='+summarytodate+'&summarytype='+summarytype;
					Ext.Ajax.request({
						url: url,
						method: 'get',
						waitMsg: 'Processing',
						//params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
						autoAbort: false,
						success: function (result) {
							var win = window.open('');
								win.location = url;
								win.focus();
						},
						failure: function () {
							
							Ext.MessageBox.show({
								title: 'Error Message',
								msg: 'Failed to retrieve data',
								buttons: Ext.MessageBox.OK,
								icon: Ext.MessageBox.ERROR
							});
						}
					});*/
				}else {
					Ext.MessageBox.show({
						title: "ERROR-A1001",
						msg: "Please Select Valid Date Range",
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.WARNING
					});
				}
				
			} else {
				 Ext.MessageBox.show({
					title: "ERROR-A1001",
					msg: "Please fill the required fields correctly.",
					buttons: Ext.MessageBox.OK,
					icon: Ext.MessageBox.WARNING
				});
			}
			
		}else {
			if (form.isValid()) {
				// Compare fromdate with todate
				if(summarytodate >= summaryfromdate){
					var url = 'index.php?hdl=order&action=getOrderStatements&summaryfromdate='+summaryfromdate+'&summarytodate='+summarytodate+'&summarytype='+summarytype;
					Ext.Ajax.request({
						url: url,
						method: 'get',
						waitMsg: 'Processing',
						//params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
						autoAbort: false,
						success: function (result) {
							var win = window.open('');
								win.location = url;
								win.focus();
						},
						failure: function () {
							
							Ext.MessageBox.show({
								title: 'Error Message',
								msg: 'Failed to retrieve data',
								buttons: Ext.MessageBox.OK,
								icon: Ext.MessageBox.ERROR
							});
						}
					});
				}else{
					Ext.MessageBox.show({
						title: "ERROR-A1001",
						msg: "Please Select Valid Date Range",
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.WARNING
					});
				}
			
			} else {
				 Ext.MessageBox.show({
					title: "ERROR-A1001",
					msg: "Please fill the required fields correctly.",
					buttons: Ext.MessageBox.OK,
					icon: Ext.MessageBox.WARNING
				});
			}
			
		}
			
		//} else {
		//	Ext.toast('Form is invalid, please correct the errors.');
		//}
	},

    fetchPOListForCustomer: function (elemnt) {
		var form = elemnt.lookupController().lookupReference('unfulfilledpolisting-form').getForm();
        unfulfilledpolisting = form.getFieldValues();
		partnerid = unfulfilledpolisting.gtpcustomernamepurchaseorder

		// Make sure Customer is selected
		if (partnerid != null) {
			
			// If form not present, enable form
			if(Ext.getCmp('unfulfilledjlistpo').isHidden() == true){
				// Clear init form
				Ext.getCmp('unfulfilledjlistpo').getStore().removeAll();
				Ext.getCmp('unfulfilledjlistpo').setHidden(false);
	
			}//'index.php?hdl=order&action=getUnfulfilledStatementsForCustomer&partnerid='+partnerid
			
			// Replace proxy URL with selection
			Ext.getCmp('unfulfilledjlistpo').getStore().proxy.url = 'index.php?hdl=order&action=getUnfulfilledStatementsForCustomer&partnerid='+partnerid;
			Ext.getCmp('unfulfilledjlistpo').getStore().reload();
			
		} else {
			 Ext.MessageBox.show({
				title: "ERROR-A1001",
				msg: "Please Select GTP Customer",
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.WARNING
			});
		}
		
		//} else {
		//	Ext.toast('Form is invalid, please correct the errors.');
		//}
	} 
});
