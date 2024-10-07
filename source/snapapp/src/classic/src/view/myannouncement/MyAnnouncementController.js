Ext.define('snap.view.myannouncement.MyAnnouncementController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.myannouncement-myannouncement',


    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {
        var me = this;
        snap.getApplication().sendRequest({
            hdl: 'myannouncement', 'action': 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
            function (data) {
                if (data.success && formView.getController()) {                    
                    formView.getController().lookupReference('type').getStore().loadData(data.type);


                    var myAnnouncementTranslationStore = Ext.getStore("myAnnouncementTranslationStore");
                    myAnnouncementTranslationStore.removeAll();
                    myAnnouncementTranslationStore.add(data.translations);
                }

                if (Ext.isFunction(asyncLoadCallback)) asyncLoadCallback(record);
                else {
                    if (data.record) {
                        record = Ext.apply(record, data.record);
                        form.loadRecord(record);
                    }
                }
            });
        return false;
    },

    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.MyAnnouncement', { id: 0 }), null);
    },

    init: function (view) {
        var me = this;
        var theView = me.getView();

        if (view instanceof snap.view.gridpanel.Base) {

            // Somehow have to bind the store this way......
            var pagingTool = this.lookupReference('gridPagingToolbar');
            if (pagingTool) pagingTool.setStore(view.getStore());
        }
    },

    paramAddClick: function () {
        var grid = this.lookupReference('myAnnouncementTranslation'),
            plugin = grid.getPlugin('editedRow1');
        plugin.completeEdit();
        grid.getStore().insert(0, {
            language: "",
            title: "",
            content: "",
        });
        plugin.startEdit(0, 0);
    },
    paramDelClick: function () {
        var grid = this.lookupReference('myAnnouncementTranslation'),
            plugin = grid.getPlugin('editedRow1');
        plugin.cancelEdit();
        var sm = grid.getSelectionModel();
        var recordId = sm.getSelection()[0].data.id;
        var store = this.lookupReference('myAnnouncementTranslationParams');
        Ext.MessageBox.confirm('Confirm', 'Confirm Delete?', function (id) {
            if (id == 'yes') {
                sm.getStore().remove(sm.getSelection());
                sm.select(0);
                this.setMyAnnouncementTranslationParamsFormData(store);
            }
        }, this);
    },
    setMyAnnouncementTranslationParamsFormData: function (store) {
        var me = this;
        var grid = this.lookupReference("myAnnouncementTranslation").getStore();
        var paramsFormData = new Array();
        var dataStored = "";
        grid.each(function (item, index, totalItems) {
            var paramsFormItemData = {
                // id: item.get('id'),
                title: item.get('title'),
                content: item.get('content'),
                language: item.get('language'),
            };
            paramsFormData.push(Ext.JSON.encode(paramsFormItemData));
        });
        if (paramsFormData.length > 0) dataStored = "[" + paramsFormData.join() + "]";
        console.log(dataStored);
        store.setValue(dataStored);
    },
    myAnnouncementTranslationViewReady: function (obj) {
        obj.on('edit', function (editor, e) {
            var me = this;
            var store = this.lookupReference('myAnnouncementTranslationParams');
            me.setMyAnnouncementTranslationParamsFormData(store);
        }, this);
    },
    paramsSelectionChange: function (view, records) {
        this.lookupReference('myAnnouncementTranslation').down('#removerec').setDisabled(true);
        if (records.length > 0) this.lookupReference('myAnnouncementTranslation').down('#removerec').setDisabled(false);
    },

    approveAnnouncement: function (record) {
        var myView = this.getView();
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
        Ext.MessageBox.confirm(
            'Confirm', 'Are you sure you want to approve ?', function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'myannouncement', 'action': 'approveannouncement', id: selectedRecords[0].data.id
                    }, 'Sending request....').then(
                        function (data) {

                            if (data.success) {
                                myView.getSelectionModel().deselectAll();
                                myView.getStore().reload();
                            } else {
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errorMessage,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });
                }
            });

    },

    disableAnnouncement: function (record) {
        var myView = this.getView();
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();
        Ext.MessageBox.confirm(
            'Confirm', 'Are you sure you want to disable ?', function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'myannouncement', 'action': 'disableannouncement', id: selectedRecords[0].data.id
                    }, 'Sending request....').then(
                        function (data) {

                            if (data.success) {
                                myView.getSelectionModel().deselectAll();
                                myView.getStore().reload();
                            } else {
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errorMessage,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });
                }
            });

    },
});