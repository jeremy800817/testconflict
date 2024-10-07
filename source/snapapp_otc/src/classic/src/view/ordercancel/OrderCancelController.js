Ext.define('snap.view.ordercancel.OrderCancelController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.ordercancel-ordercancel',



    /*onPreLoadViewDetail: function(record, displayCallback) {
    	snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'detailview', id: record.data.id, status_text: record.data.status_text,})
    	.then(function(data){
    		if(data.success) {
    			displayCallback(data.record);
    		}
    	})
        return false;
	}*/

    onPreLoadViewDetail: function(record, displayCallback) {
    snap.getApplication().sendRequest({ hdl: 'ordercancel', action: 'detailview', id: record.data.id})
    .then(function(data){
        if(data.success) {
            displayCallback(data.record);
        }
    })
    return false;
},

});
