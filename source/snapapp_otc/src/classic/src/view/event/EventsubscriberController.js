//
Ext.define('snap.view.event.EventsubscriberController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.eventsubscriber-eventsubscriber',

    init: function(view) {
        if (view instanceof snap.view.gridpanel.Base) {
            //Somehow have to bind the store this way......
           var pagingTool = this.lookupReference('gridPagingToolbar');
           if (pagingTool) pagingTool.setStore(view.getStore());
        }

        var height = Ext.Element.getViewportHeight() - 164;
        view.height = 300;
    },

    // eventSubscriberViewReady: function(obj) {
    //     //Loading of the column levels info and the actual price list records
    //     obj.store.loadData(this.getViewModel().get('eventsubscriber'));
    // },

    onPreLoadForm: function( formView, form, record, asyncLoadCallback) {
    	// var me = this;
        snap.getApplication().sendRequest({
            hdl: 'eventsubscriber', 
            action: 'fillform', 
            id: ((record && record.data) ? record.data.id : 0),
            trigger_id: record.data.trigger_id,
            action_desc: record.data.action_desc, 
            action_id: record.data.action_id, 
            branch_code: record.data.branch_code, 
            branch_id: record.data.branch_id, 
            branch_name: record.data.branch_name, 
            createdby: record.data.createdby, 
            createdon: record.data.createdon, 
            modifiedby: record.data.modifiedby, 
            modifiedon: record.data.modifiedon, 
            module_desc: record.data.module_desc, 
            module_id: record.data.module_id, 
            receiver: record.data.receiver, 
            status: record.data.status, 
            object_id: ((record && record.data) ? record.data.object_id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
            	//1. For items that can not be populated immediately, keep it in the view model first.
            	formView.getViewModel().set('eventsubscriber', data);
            	
            	//2.  Override the model object with our new fields data.
            	record = Ext.apply(record, data.record);
            	//We have to call this method because there are some custom fields that needs to be loaded.
            	form.setValues(data.record);
            }
            //Call the callback method to continue with form showing.
            if(Ext.isFunction(asyncLoadCallback)) asyncLoadCallback(record);
            else {
            	record = Ext.apply(record, data.record);
            	form.loadRecord(record);
            }
        });
        return false;
    },
    
    onPostLoadEmptyForm: function( formView, form) {
    	this.onPreLoadForm(formView, form, Ext.create('snap.model.Eventsubscriber', {id: 0}), null);
    },

    onPreAddEditSubmit: function(formAction, formView, formObject) {
        var fieldValues = formObject.getFieldValues();
        formObject.setValues(fieldValues);

    	return true;
    },

    onPreLoadViewDetail: function(record, displayCallback) {
    	snap.getApplication().sendRequest({ 
            hdl: 'eventsubscriber', 
            action: 'detailview', 
            id: record.data.id, 
            action_desc: record.data.action_desc, 
            action_id: record.data.action_id, 
            branch_code: record.data.branch_code, 
            branch_id: record.data.branch_id, 
            branch_name: record.data.branch_name, 
            createdby: record.data.createdby, 
            createdon: record.data.createdon, 
            modifiedby: record.data.modifiedby, 
            modifiedon: record.data.modifiedon, 
            module_desc: record.data.module_desc, 
            module_id: record.data.module_id, 
            receiver: record.data.receiver, 
            status: record.data.status, 
            object_id: record.data.object_id
        }).then(function(data){
    		if(data.success) {
    			displayCallback(data.record);
    		}
    	})
        return false;
    }
});
