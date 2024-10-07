Ext.define('snap.view.event.EventtriggerController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.eventtrigger-eventtrigger',

    requires: [
        'snap.view.gridpanel.BaseController'
    ],

    onPreLoadForm: function( formView, form, record, asyncLoadCallback) {
        var me = this;
        snap.getApplication().sendRequest({
            hdl: 'eventtrigger', action: 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        function(data) {
            if(data.success) {
                // formView.getController().lookupReference('grouptypeid').getStore().loadData(data.grouptypemap);
                // formView.getController().lookupReference('actionid').getStore().loadData(data.actionmap);
                // formView.getController().lookupReference('moduleid').getStore().loadData(data.modulemap);
                // formView.getController().lookupReference('processorclass').getStore().loadData(data.processorclass);
                formView.getController().lookupReference('objectclass').getStore().loadData(data.objectclass);
                formView.getController().lookupReference('observableclass').getStore().loadData(data.managerclass);
                formView.getController().lookupReference('messageid').getStore().loadData(data.eventmessage);
                formView.getController().lookupReference('matcherclass').getStore().loadData(data.matcherclass);
            }

            if(Ext.isFunction(asyncLoadCallback)) asyncLoadCallback(record);
            else {
                record = Ext.apply(record, data.record);
                form.loadRecord(record);
            }
        });
        return false;
    },

    onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm) {
        var isEditMode = (theGridForm.findField('id').getValue().length > 0) ? true : false;
        if(isEditMode) {
            theGridForm.setValues({action: 'edit'});
        } else {
            theGridForm.setValues({action: 'add'});
        }
        return true;
    },
    onPostLoadEmptyForm: function( formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.Eventtrigger', {id: 0, storetolog: 1}), null);
    },

    _loadRemoteEventConstants: function(view, componentReference, storeReference) {
            var eventConstStore = Ext.data.StoreManager.lookup(storeReference);
            if(! eventConstStore) {
                eventConstStore = Ext.create('Ext.data.Store', {
                    model: storeReference, 
                    storeId: storeReference}
                );
                eventConstStore.load({
                    callback: function(records, operation, success) {
                        records = records[0].data.records;
                        var map = [];
                        for(var i =  0; i < records.length; i++) {
                            map.push([records[i].id, records[i].desc]);
                        }
                        view.lookupReference(componentReference).filter.store = map;
                        view.getFilterBar().resetup.call(view);
                    }
                });
            } else {
                records = eventConstStore.getData().items[0].data;
                var map = [];
                for(var i =  0; i < records.length; i++) {
                    map.push([records[i].id, records[i].desc]);
                    alert(records[i].id + records[i].desc)
                }
                view.lookupReference(componentReference).filter.store = map;
                view.getFilterBar().resetup.call(view);
            }
    },

    init: function(view) {
        var me = this;
        var theView = me.getView();

        if (view instanceof snap.view.gridpanel.Base) {
            this._loadRemoteEventConstants(view, 'gridGroupType', 'EventGroupType');
            this._loadRemoteEventConstants(view, 'gridActionType', 'EventActionType');
            this._loadRemoteEventConstants(view, 'gridModuleType', 'EventModuleType');
            this._loadRemoteEventConstants(view, 'gridProcessorType', 'EventProcessorType');

            // Somehow have to bind the store this way......
           var pagingTool = this.lookupReference('gridPagingToolbar');
           if (pagingTool) pagingTool.setStore(view.getStore());
        }
    }
});
