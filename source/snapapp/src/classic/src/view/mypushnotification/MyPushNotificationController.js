Ext.define('snap.view.mypushnotification.MyPushNotificationController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.mypushnotification-mypushnotification',


    onPreLoadForm: function( formView, form, record, asyncLoadCallback) {
        var me = this;
        snap.getApplication().sendRequest({
            hdl: 'mypushnotification', 'action': 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        function(data) {
            if(data.success) {

                formView.getController().lookupReference('event').getStore().loadData(data.events);
                //formView.getController().lookupReference('type').getStore().loadData(data.type);
                //formView.getController().lookupReference('attachmentPicture').setValue(data.picture);
                /*
                

                record.data.cardio = false;
                record.data.gp = false;

                if(record.data.type == data.patienttype[2].code) {
                    record.data.gp = true;
                    record.data.cardio = true;
                }
                else if(record.data.type == data.patienttype[0].code) {
                    record.data.gp = true;
                }
                else if(record.data.type == data.patienttype[1].code) {
                    record.data.cardio = true;
                }
            */
            }
            
            if(Ext.isFunction(asyncLoadCallback)) asyncLoadCallback(record);
            else {
                record = Ext.apply(record, data.record);
                form.loadRecord(record);
            }
        });
        return false;
    },

    onPostLoadEmptyForm: function( formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.MyPushNotification', {id: 0}), null);
    },


    onContentAddPressed: function (item, evt, eOpts) {
        let grid = this.lookup('contentgrid');

        let plugin = grid.getPlugin('contentgrid-rowEditPlugin');
        plugin.completeEdit();

        grid.getStore().insert(0, {
            language: '',
            title: '',
            body: ''
        });

        plugin.startEdit(0, 0);
    },

    onContentRemovePressed: function (item, evt, eOpts) {
        let grid = this.lookup('contentgrid');

        let plugin = grid.getPlugin('contentgrid-rowEditPlugin');
        plugin.cancelEdit();

        let sm = grid.getSelectionModel();
        let selection = sm.getSelection();
        let store = grid.getStore();

        Ext.MessageBox.confirm('Confirm', 'Confirm Delete?', function (id) {
            if (id == 'yes') {
                store.remove(selection);
                sm.select(0);
            }
        }, this);

    },

    onContentSelectionChanged : function (sm, selected) {
        this.getView().lookup('removeBtn').setDisabled(0 >= selected.length);
    },

    onContentViewReady: function(grid) {
        let formPanel = grid.lookupReferenceHolder().getView().gridFormPanel;
        let record = formPanel.getRecord();
        
        // Editing a record, load the existing contents
        if (0 != record.id) {
            let params = {
                hdl: 'mypushnotification',
                action: 'listcontent',
                id: record.id
            };

            snap.getApplication().sendRequest(params, "Fetching data...")
            .then(function (data) {
                if (data.success) {
                    grid.getStore().loadData(data.contents);
                }
            });

        }

    },

    onPreAddEditSubmit: function(formAction, formView, formObject) {
        let contentgrid = formView.lookup('contentgrid');
        let items = contentgrid.getStore().getData().items;
        let contents = [];
        
        formObject.setValues({contentparam: ''});
        for (let item of items) {
            if (0 < item.data.language.length) {
                contents.push({
                    // id: item.data._id,
                    title: item.data.title,
                    body: item.data.body,
                    language: item.data.language
                });
            }
        }

        if (0 < contents.length) {
            formObject.setValues({contentparam: JSON.stringify(contents)});
        }

        return true;
    }

});