//
Ext.define('snap.view.tag.TagController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.tag-tag',

    onPreLoadForm: function( formView, form, record, asyncLoadCallback) {
    	var me = this;
        snap.getApplication().sendRequest({
            hdl: 'tag', action: 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                //1. Populate all the controls with tag information
                formView.getController().lookupReference('tag_category').getStore().loadData(data.category);
                
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
        this.onPreLoadForm(formView, form, Ext.create('snap.model.Tag', {id: 0}), null);
	},
	
    onPreLoadViewDetail: function(record, displayCallback) {
    	snap.getApplication().sendRequest({ hdl: 'tag', action: 'detailview', id: record.data.id, status_text: record.data.status_text})
    	.then(function(data){
    		if(data.success) {
    			displayCallback(data.record);
    		}
    	})
        return false;
	}

});
