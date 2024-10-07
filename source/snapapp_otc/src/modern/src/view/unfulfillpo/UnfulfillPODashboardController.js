Ext.define('snap.view.unfulfillpo.UnfulfillPODashboardController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.unfulfillpodashboard-unfulfillpodashboard',    
    summaryAction: function (elemnt) {
        var form =elemnt.up('formpanel');     
        transactionlisting = form.getValues();
		summaryfromdate = Date.parse(transactionlisting.fromdate);				
        summarytodate = Date.parse(transactionlisting.todate);
		summarytype = transactionlisting.type;
        partnerid = transactionlisting.gtpcustomernametransactionlisting;    
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
							autoAbort: false,
							success: function (result) {
								var win = window.open('');
									win.location = url;
									win.focus();
							},
							failure: function () {
								Ext.Msg.alert('Error Message', 'Failed to retrieve data', Ext.emptyFn);							
							}
						});
					} else {
						Ext.Msg.alert('ERROR-A1001', 'Please Select GTP Customer', Ext.emptyFn);						
					}	
				}else {
					Ext.Msg.alert('ERROR-A1001', 'Please Select Valid Date Range', Ext.emptyFn);
				}
							
			} else {
                Ext.Msg.alert('ERROR-A1001', 'Please fill the required fields correctly.', Ext.emptyFn);					
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
							Ext.Msg.alert('Error Message', 'Failed to retrieve data', Ext.emptyFn);						
						}
					});
				}else{
					Ext.Msg.alert('ERROR-A1001', 'Please Select Valid Date Range', Ext.emptyFn);
				}
				
			} else {
                Ext.Msg.alert('ERROR-A1001', 'Please fill the required fields correctly.', Ext.emptyFn);				
			}
			
		}
			
		//} else {
		//	Ext.toast('Form is invalid, please correct the errors.');
		//}
	},

    fetchPOListForCustomer: function (elemnt) {
        //var form = elemnt.lookupController().lookupReference('unfulfilledpolisting-form');
        var form =elemnt.up('formpanel');
        unfulfilledpolisting = form.getValues();        
		partnerid = unfulfilledpolisting.gtpcustomernamepurchaseorder;
		// Make sure Customer is selected
		if (partnerid != null) {			
			// If form not present, enable form
			if(elmnt.lookupReference('unfulfilledjlistpo').isHidden() == true){
				// Clear init form
                elmnt.lookupReference('unfulfilledjlistpo').getStore().removeAll();
               
				elmnt.lookupReference('unfulfilledjlistpo').setHidden(false);
	
			}//'index.php?hdl=order&action=getUnfulfilledStatementsForCustomer&partnerid='+partnerid
			
			// Replace proxy URL with selection
			elmnt.lookupReference('unfulfilledjlistpo').getStore().proxy.url = 'index.php?hdl=order&action=getUnfulfilledStatementsForCustomer&partnerid='+partnerid;
			elmnt.lookupReference('unfulfilledjlistpo').getStore().reload();
			
		} else {
            Ext.Msg.alert('ERROR-A1001', 'Please Select GTP Customer', Ext.emptyFn);			
		}	
		
	} 
});
