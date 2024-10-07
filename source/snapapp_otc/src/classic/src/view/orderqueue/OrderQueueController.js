Ext.define('snap.view.orderqueue.OrderQueueController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.orderqueue-orderqueue',



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
        snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },

    cancelOrders: function(btn, formAction) {
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
        if (selectedRecords.length == 1) {
            for(var i = 0; i < selectedRecords.length; i++) {
                selectedID = selectedRecords[i].get('id');
                selectedRecord = selectedRecords[i];
                break;
            }
        } else if('add' != formAction) {
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Select a record first'});
            return;
        }

        snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'cancelFutureOrder', 
                                            id: selectedRecord.data.id,
                                            partnerid: selectedRecord.data.partnerid,
                                            apiversion: selectedRecord.data.apiversion,
                                            refid: selectedRecord.data.partnerrefid,
                                            notifyurl: selectedRecord.data.notifyurl,
                                            reference: selectedRecord.data.remarks,
                                            timestamp: selectedRecord.data.createdon,
                                        })
    	.then(function(data){
    		if(data.success) {
                Ext.MessageBox.show({
                    title: 'Notification', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
                    msg: 'Successfully cancelled future order'});
            }
            if(!data.success) {
                Ext.MessageBox.show({
                    title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                    msg: 'Unable to cancel order'});
    		}
    	})
    }

});
