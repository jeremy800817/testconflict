Ext.define('snap.view.summary.SummaryController', {
	extend: 'Ext.app.ViewController',
	alias: 'controller.summary-summary',
	summaryAction: function (btn) {
		var form = this.getView();
		summaryfromdate = Date.parse(form.getValues().summaryfromdate);				
		summarytodate = Date.parse(form.getValues().summarytodate);
		summarytype = form.getValues().summarytype;
		if (form.validate()) {				
			Ext.Viewport.mask({
				xtype: 'loadmask',
				message: "Getting summary.."
			});			
			var url = 'index.php?hdl=order&action=getOrderStatements&summaryfromdate='+summaryfromdate+'&summarytodate='+summarytodate+'&summarytype='+summarytype;
			Ext.Ajax.request({
				url: url,
				method: 'get',
				waitMsg: 'Processing',
				//params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
				autoAbort: false,
				success: function (result) {
					Ext.Viewport.unmask();
					if (result.status == 204) {
						Ext.Msg.alert('Empty Report', 'There is no data');
					} else if (result.status == 200) {
						var win = window.open('');
						win.location = url;
						win.focus();
					}
				},
				failure: function () {
					Ext.Viewport.unmask();
					Ext.Msg.alert('Error', "Error occured", Ext.emptyFn);
				}
			});
		} else {
			Ext.toast('Form is invalid, please correct the errors.');
		}
	}
});