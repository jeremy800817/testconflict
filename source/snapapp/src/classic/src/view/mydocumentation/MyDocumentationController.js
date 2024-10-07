Ext.define('snap.view.mydocumentation.MyDocumentationController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.mydocumentation-mydocumentation',

    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {
        var me = this;
        snap.getApplication().sendRequest({
            hdl: 'mydocumentation', 'action': 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
            function (data) {
                if (data.success && formView.getController()) {                    


                    var myDocumentationTranslationStore = Ext.getStore("myDocumentationTranslationStore");
                    myDocumentationTranslationStore.removeAll();
                    myDocumentationTranslationStore.add(data.translations);
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
        this.onPreLoadForm(formView, form, Ext.create('snap.model.MyDocumentation', { id: 0 }), null);
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
        var grid = this.lookupReference('myDocumentationTranslation'),
            plugin = grid.getPlugin('editedRow1');
        plugin.completeEdit();
        grid.getStore().insert(0, {
            language: "",
            filename: "",
            filecontent: "",
        });
        plugin.startEdit(0, 0);
    },
    paramDelClick: function () {
        var grid = this.lookupReference('myDocumentationTranslation'),
            plugin = grid.getPlugin('editedRow1');
        plugin.cancelEdit();
        var sm = grid.getSelectionModel();
        var recordId = sm.getSelection()[0].data.id;
        var store = this.lookupReference('myDocumentationTranslationParams');
        Ext.MessageBox.confirm('Confirm', 'Confirm Delete?', function (id) {
            if (id == 'yes') {
                sm.getStore().remove(sm.getSelection());
                sm.select(0);
                this.setMyDocumentationTranslationParamsFormData(store);
            }
        }, this);
    },
    setMyDocumentationTranslationParamsFormData: function (store) {
        var me = this;
        var grid = this.lookupReference("myDocumentationTranslation").getStore();
        var paramsFormData = new Array();
        var dataStored = "";
        grid.each(function (item, index, totalItems) {
            var paramsFormItemData = {
                filename: item.get('locfilename'),
                filecontent: item.get('locfilecontent'),
                language: item.get('loclanguage'),
            };
            paramsFormData.push(Ext.JSON.encode(paramsFormItemData));
        });
        if (paramsFormData.length > 0) dataStored = "[" + paramsFormData.join() + "]";
        store.setValue(dataStored);
    },
    myDocumentationTranslationViewReady: function (obj) {
        obj.on('edit', function (editor, e) {
            var me = this;
            var store = this.lookupReference('myDocumentationTranslationParams');
            me.setMyDocumentationTranslationParamsFormData(store);
        }, this);
    },
    paramsSelectionChange: function (view, records) {
        this.lookupReference('myDocumentationTranslation').down('#removerec').setDisabled(true);
        if (records.length > 0) this.lookupReference('myDocumentationTranslation').down('#removerec').setDisabled(false);
    },
});
