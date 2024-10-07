Ext.define('snap.view.tender.PosTenderController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.postender-postender',

	summaryAction: function (elemnt) {
		//alert('testing');
		//debugger;
		var previewgoldprice = elemnt.lookupController().lookupReference('transactionlisting-form').getForm().getFieldValues().goldprice;
		Ext.Msg.show({
    title:'Tender Import',
    message: 'Would you like to proceed the tender import with the gold price with MYR '+parseFloat(previewgoldprice).toFixed(2)+'?',
    buttons: Ext.Msg.YESNOCANCEL,
    icon: Ext.Msg.QUESTION,
    fn: function(btn) {
        if (btn === 'yes') {
          	var form = elemnt.lookupController().lookupReference('transactionlisting-form').getForm();
			  console.log(form,'form');
		// debugger;
		transactionlisting = form.getFieldValues();
		summaryfromdate = Date.parse(transactionlisting.fromdate);
		referenceid = transactionlisting.referenceid;
		goldprice = parseFloat(transactionlisting.goldprice).toFixed(2);
		
       // summarytype = transactionlisting.type;
		//fileaddr = transactionlisting.tenderlist;
	
		//if (form.validate()) {			
		//debugger;
		//transactionlisting.gtpcustomernametransactionlisting
		
			
			if (form.isValid()) {
				if ( transactionlisting.tenderlist != null) {
				form.submit({
                    url: 'index.php?hdl=tender&action=uploadTenderFile',
                    waitMsg: 'Uploading your tender list...',
                    success: function(fp, o) {
						if (o.result.exception && !o.result.preview){
							Ext.Msg.alert('Exception', o.result.exception);
							return;
						}
						if (o.result.preview){
							Ext.Msg.alert('Preview', o.result.preview);
							return;
						}
						if (o.result.success){
							Ext.Msg.alert('Success', 'Your tender list has converted to Buyback Order.');
							return;
						}
                    }
                });


				} else {
					 Ext.MessageBox.show({
						title: "ERROR-A1001",
						msg: "Choose correct .csv file",
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
			} else {
				 Ext.MessageBox.show({
					title: "ERROR-A1001",
					msg: "Please fill the required fields correctly.",
					buttons: Ext.MessageBox.OK,
					icon: Ext.MessageBox.WARNING
				});
			}
        } else if (btn === 'no') {
            console.log('Cancel the import');
        } else {
            console.log('Cancel');
        }
    }
});

	
			
		
			
		//} else {
		//	Ext.toast('Form is invalid, please correct the errors.');
		//}
	},

   
});
