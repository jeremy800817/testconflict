Ext.define('snap.view.redemption.RedemptionTreeController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-redemptiontreecontroller',
    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.redemption', { id: 0, }), null);     
    },
    /*paramAddClick: function () {
        var grid = this.lookupReference('partnerservice'),
            plugin = grid.getPlugin('editedRow1');
        plugin.completeEdit();
        grid.getStore().insert(0, {
            id: "",
            partnersapgroup: "",
            productid: "",
            pricesourcetypeid: "",
            refineryfee: "",
            premiumfee: "",
            includefeeinprice: "",
            canbuy: "",
            cansell: "",
            canqueue: "",
            canredeem: "",
            clickminxau: "",
            clickmaxxau: "",
            dailybuylimitxau: "",
            dailyselllimitxau: "",
        });
        plugin.startEdit(0, 0);
    },
    paramDelClick: function () {
        var grid = this.lookupReference('partnerservice'),
            plugin = grid.getPlugin('editedRow1');
        plugin.cancelEdit();
        var sm = grid.getSelectionModel();
        var recordId = sm.getSelection()[0].data.id;
        var store = this.lookupReference('partnerservice');
        Ext.MessageBox.confirm('Confirm', 'Confirm Delete?', function (id) {
            if (id == 'yes') {
                sm.getStore().remove(sm.getSelection());
                sm.select(0);
                this.setServicesParamsFormData(store);
            }
        }, this);
    },
    paramsSelectionChange: function (view, records) {
        this.lookupReference('partnerservice').down('#removerec').setDisabled(true);
        if (records.length > 0) this.lookupReference('partnerservice').down('#removerec').setDisabled(false);
    },
    checkChange: function (checkbox, rowIndex, checked, eOpts) {
        if (checked == true) {
        }
    },
    partnerServiceViewReady: function (obj) {
        console.log("ready");
        obj.on('edit', function (editor, e) {
            var me = this;
            var store = this.lookupReference('serviceparams');
            me.setServicesParamsFormData(store);
        }, this);
        obj.on('validateedit', function (editor, e) {
            var paramsEditor = editor.editor.form;
            var partnersapgroup = paramsEditor.findField('partnersapgroup').getValue();
            var productid = paramsEditor.findField('productid').getValue();
            var refineryfee = paramsEditor.findField('refineryfee').getValue();
            var premiumfee = paramsEditor.findField('premiumfee').getValue();
            var includefeeinprice = paramsEditor.findField('includefeeinprice').getValue() ? '1' : '0';
            var canbuy = paramsEditor.findField('canbuy').getValue() ? '1' : '0';
            var cansell = paramsEditor.findField('cansell').getValue() ? '1' : '0';
            var canqueue = paramsEditor.findField('canqueue').getValue() ? '1' : '0';
            var canredeem = paramsEditor.findField('canredeem').getValue() ? '1' : '0';
            var clickminxau = paramsEditor.findField('clickminxau').getValue();
            var clickmaxxau = paramsEditor.findField('clickmaxxau').getValue();
            var dailybuylimitxau = paramsEditor.findField('dailybuylimitxau').getValue();
            var dailyselllimitxau = paramsEditor.findField('dailyselllimitxau').getValue();
        });
    },
    chkchange: function (dv, record, item, index, e) {
        var me = this;
        var store = this.lookupReference('serviceparams');
        me.setServicesParamsFormData(store);
    },
    setServicesParamsFormData: function (store) {
        var me = this;
        var grid = this.lookupReference("partnerservice").getStore();
        var paramsFormData = new Array();
        var dataStored = "";
        grid.each(function (item, index, totalItems) {
            var paramsFormItemData = {
                id: item.get('id'),
                partnersapgroup: item.get('partnersapgroup'),
                productid: item.get('productid'),
                refineryfee: item.get('refineryfee'),
                premiumfee: item.get('premiumfee'),
                includefeeinprice: item.get('includefeeinprice') ? '1' : '0',
                canbuy: item.get('canbuy') ? '1' : '0',
                cansell: item.get('cansell') ? '1' : '0',
                canqueue: item.get('canqueue') ? '1' : '0',
                canredeem: item.get('canredeem') ? '1' : '0',
                clickminxau: item.get('clickminxau'),
                clickmaxxau: item.get('clickmaxxau'),
                dailybuylimitxau: item.get('dailybuylimitxau'),
                dailyselllimitxau: item.get('dailyselllimitxau'),
            };
            paramsFormData.push(Ext.JSON.encode(paramsFormItemData));
        });
        if (paramsFormData.length > 0) dataStored = "[" + paramsFormData.join() + "]";
        console.log(dataStored);
        store.setValue(dataStored);
    },
    paramAddBranchClick: function () {
        var grid = this.lookupReference('partnerbranch'),
            plugin = grid.getPlugin('editedRow2');
        plugin.completeEdit();
        grid.getStore().insert(0, {
            id: "",
            code: "",
            name: "",
            sapcode: "",
            address: "",
            postcode: "",
            city: "",
            contactno: "",
            status: "",           
        });
        plugin.startEdit(0, 0);
    },
    paramDelBranchClick: function () {
        var grid = this.lookupReference('partnerbranch'),
            plugin = grid.getPlugin('editedRow2');
        plugin.cancelEdit();
        var sm = grid.getSelectionModel();
        var recordId = sm.getSelection()[0].data.id;
        var store = this.lookupReference('partnerbranch');
        Ext.MessageBox.confirm('Confirm', 'Confirm Delete?', function (id) {
            if (id == 'yes') {
                sm.getStore().remove(sm.getSelection());
                sm.select(0);
                this.setBranchesParamsFormData(store);
            }
        }, this);
    },
    partnerBranchviewReady: function (obj) {
        obj.on('edit', function (editor, e) {
            var me = this;
            var store = this.lookupReference('branchparams');
            me.setBranchesParamsFormData(store);
        }, this);
        obj.on('validateedit', function (editor, e) {
        });
    },
    chkbranchstatuschange: function (dv, record, item, index, e) {
        var me = this;
        var store = this.lookupReference('branchparams');
        me.setBranchesParamsFormData(store);
    },
    paramsBranchSelectionChange: function (view, records) {
        this.lookupReference('partnerbranch').down('#removebranch').setDisabled(true);
        if (records.length > 0) this.lookupReference('partnerbranch').down('#removebranch').setDisabled(false);
    },
    setBranchesParamsFormData: function (store) {
        var me = this;
        var grid = this.lookupReference("partnerbranch").getStore();
        var paramsFormData = new Array();
        var dataStored = "";
        grid.each(function (item, index, totalItems) {
            var paramsFormItemData = {
                id: item.get('id'),
                code: item.get('code'),
                name: item.get('name'),
                sapcode: item.get('sapcode'),
                address: item.get('address'),
                postcode: item.get('postcode'),
                city: item.get('city'),
                contactno: item.get('contactno'),
                status: item.get('status') ? '1' : '0',                
            };
            paramsFormData.push(Ext.JSON.encode(paramsFormItemData));
        });
        if (paramsFormData.length > 0) dataStored = "[" + paramsFormData.join() + "]";
        console.log(dataStored);
        store.setValue(dataStored);
    }, */   
});
