//
Ext.define('snap.view.event.EventmessageController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.eventmessage-eventmessage',

    init: function(view) {
        if (view instanceof snap.view.gridpanel.Base) {
            //Somehow have to bind the store this way......
           var pagingTool = this.lookupReference('gridPagingToolbar');
           if (pagingTool) pagingTool.setStore(view.getStore());
        }

        var height = Ext.Element.getViewportHeight() - 164;
        view.height = 600;
    },
	 
    replaceListViewReady: function(obj) {
        //Loading of the column levels info and the actual price list records
    	obj.store.loadData(this.getViewModel().get('replacelist'));
    },

    onReplaceListValidate: function(editor, e) {
        // var replacelistEditor = editor.editor.form;
        // var code = parseInt(replacelistEditor.findField('code').getValue());
        // var subject = parseInt(replacelistEditor.findField('subject').getValue());
        // var contentfull = parseInt(replacelistEditor.findField('contentfull').getValue());

        // if (code == "") {
        //     Ext.Msg.alert('Error', 'The name cannot be empty');
        //     e.cancel = true;
        // }
        // else if (subject == "") {
        //     Ext.Msg.alert('Error', 'The subject cannot be empty');
        //     e.cancel = true;
        // }
        // else if (contentfull == "") {
        //     Ext.Msg.alert('Error', 'The body cannot be empty');
        //     e.cancel = true;
        // }
        // else {
        //     e.cancel = false;
        // }
    },

    onEditReplaceList: function(editor, e) {
    	// e.record.commit();
    },

    replaceListSelectionChanged: function(view, records) {
        
    },

    doMappingClicked: function() {
     	var re = /##(.*?)##/g;
        var mappings = source = {};
        var replaceName;
        var replacelist = this.getViewModel().get('replacelist');
        for(i=0; i<replacelist.length; i++) {
        	replaceName = replacelist[i].name.replace(/##/g, "");

        	if (replaceName != "") {
        		mappings[replaceName] = replacelist[i];
        	}
        }

        var content = this.lookupReference('content').getValue();
        var match = re.exec(content);
        while (match != null) {
            // console.log(mappings[match[1]] + ' = ' + match[1]);
            if (mappings[match[1]]) source[match[1]] = mappings[match[1]];
            else source[match[1]] = "";
            match = re.exec(content);
        }
        
        var subject = this.lookupReference('subject').getValue();
        match = re.exec(subject);
        while (match != null) {
            // console.log(mappings[match[1]] + ' = ' + match[1]);
            if (mappings[match[1]]) source[match[1]] = mappings[match[1]];
            else source[match[1]] = "";
            match = re.exec(subject);
        }

        var grid = this.lookupReference('replaceListGrid'),
    	    plugin = grid.getPlugin('rowEditPlugin');

	    // remove all grid data
	    var sm = grid.getSelectionModel(); //this.lookupReference is not a function
        sm.getStore().removeAll();

        // reinsert grid data
        plugin.completeEdit();
        var sourceName = sourceValue = null;
        var i = 0;
        for (var key in source) {
        	if (source[key] != "") {
        		sourceName = source[key].name;
        		sourceValue = source[key].value;
        	} else {
        		sourceName = "##" + key + "##";
        		sourceValue = '';
        	}

	        grid.getStore().insert(i, {
	            name: sourceName,
	            value: sourceValue,
	        });
	        i++;
	    }
    },

    onPreLoadForm: function( formView, form, record, asyncLoadCallback) {
    	// var me = this;
        snap.getApplication().sendRequest({
            hdl: 'eventmessage', action: 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
            	//1. For items that can not be populated immediately, keep it in the view model first.
	            // formView.getViewModel().set('subject', data.record.subject);
            	// formView.getViewModel().set('contentfull', data.record.contentfull);
            	formView.getViewModel().set('replacelist', data.record.replacelist);
            	
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
    	this.onPreLoadForm(formView, form, Ext.create('snap.model.Eventmessage', {id: 0}), null);
    },

    onPreAddEditSubmit: function(formAction, formView, formObject) {
        //Populate the hidden field (replacelist) with the grid data to send to server
        var replacelist = formView.down('#replaceListGrid').getStore().data;
        var replaceText = '';
        var i = 0;

        replacelist.each(function(replace) {
            if (i > 0) replaceText = replaceText + ',';
            replaceText = replaceText + replace.get('name') + '||' + replace.get('value');
            i++;
        });

        var fieldValues = formObject.getFieldValues();
        fieldValues.replace = replaceText;
        formObject.setValues(fieldValues);

    	return true;
    },

    onPreLoadViewDetail: function(record, displayCallback) {
    	snap.getApplication().sendRequest({ hdl: 'eventmessage', action: 'detailview', id: record.data.id})
    	.then(function(data){
    		if(data.success) {
    			displayCallback(data.record);
    		}
    	})
        return false;
    }
});
