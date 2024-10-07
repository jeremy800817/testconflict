Ext.define('snap.view.partner.PartnerTreeController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-partnertreecontroller',
    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.partner', { id: 0, }), null);
        this.onPreLoadForm(formView, form, Ext.create('snap.model.PartnerServices', { id: 0, }), null);
    },
    paramAddClick: function () {
        var grid = this.lookupReference('partnerservice'),
            plugin = grid.getPlugin('editedRow1');
        plugin.completeEdit();
        grid.getStore().insert(0, {
            id: "",
            partnersapgroup: "",
            productid: "",
            pricesourcetypeid: "",
            refineryfee: 0,
            premiumfee: 0,
            redemptionpremiumfee: 0,
            redemptioninsurancefee: 0,
            redemptioncommission: 0,
            redemptionhandlingfee: 0,
            includefeeinprice: 1,
            canbuy: "",
            cansell: "",
            canqueue: "",
            canredeem: "",
            buyclickminxau: 0,
            buyclickmaxxau: 0,
            sellclickminxau: 0,
            sellclickmaxxau: 0,
            dailybuylimitxau: 0,
            dailyselllimitxau: 0,
            specialpricetype: "NONE",
            specialpricecondition: 0,
            specialpricecompanybuyoffset: 0,
            specialpricecompanyselloffset: 0,
        });
        plugin.startEdit(0, 0);
    },
    paramDelClick: function () {
        var grid = this.lookupReference('partnerservice'),
            plugin = grid.getPlugin('editedRow1');
        plugin.cancelEdit();
        var sm = grid.getSelectionModel();
        var recordId = sm.getSelection()[0].data.id;
        var store = this.lookupReference('serviceparams');
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
            //var canqueue = paramsEditor.findField('canqueue').getValue() ? '1' : '0';
            var canredeem = paramsEditor.findField('canredeem').getValue() ? '1' : '0';
            var buyclickminxau = paramsEditor.findField('buyclickminxau').getValue();
            var buyclickmaxxau = paramsEditor.findField('buyclickmaxxau').getValue();
            var sellclickminxau = paramsEditor.findField('sellclickminxau').getValue();
            var sellclickmaxxau = paramsEditor.findField('sellclickmaxxau').getValue();
            var dailybuylimitxau = paramsEditor.findField('dailybuylimitxau').getValue();
            var dailyselllimitxau = paramsEditor.findField('dailyselllimitxau').getValue();
            var specialpricetype = paramsEditor.findField('specialpricetype').getValue();
            var specialpricecondition = paramsEditor.findField('specialpricecondition').getValue();
            var specialpricecompanybuyoffset = paramsEditor.findField('specialpricecompanybuyoffset').getValue();
            var specialpricecompanyselloffset = paramsEditor.findField('specialpricecompanyselloffset').getValue();
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
                buyclickminxau: item.get('buyclickminxau'),
                buyclickmaxxau: item.get('buyclickmaxxau'),
                sellclickminxau: item.get('sellclickminxau'),
                sellclickmaxxau: item.get('sellclickmaxxau'),
                dailybuylimitxau: item.get('dailybuylimitxau'),
                dailyselllimitxau: item.get('dailyselllimitxau'),
                redemptionpremiumfee: item.get('redemptionpremiumfee'),
                redemptioncommission: item.get('redemptioncommission'),
                redemptioninsurancefee: item.get('redemptioninsurancefee'),
                redemptionhandlingfee: item.get('redemptionhandlingfee'),
                specialpricetype: item.get('specialpricetype'),
                specialpricecondition: item.get('specialpricecondition'),
                specialpricecompanybuyoffset: item.get('specialpricecompanybuyoffset'),
                specialpricecompanyselloffset: item.get('specialpricecompanyselloffset'),
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
        var store = this.lookupReference('branchparams');
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
    },

    onPartnerSapViewReady: function (grid) {
        var me = this;
        grid.bpcodeeditor = Ext.create({xtype: 'editor', updateEl: true, ignoreNoChange: true});
        grid.bpcodeeditor.on('complete', function(editor, curVal, startVal) {
            // console.log("in complete")
            if (curVal == '') {
                editor.boundEl.setText(startVal);
            } else {
                // console.log("in complete edited")
                editor.boundEl.setText(curVal);
                let reference = editor.boundEl.component.reference.substr(7); // Remove "header_"
                me.lookupReference(reference).setValue(curVal);
            }
            return true;
        });
        // grid.bpcodeeditor.on('focusleave', function(cmp, event) {
        //     console.log("in focusleave")
        // })

        // Header editing
        for (let ref_ of ['header_tradebp_v', 'header_tradebp_c', 'header_nontradebp_v', 'header_nontradebp_c']) {
            grid.lookupReference(ref_).textEl.on('dblclick', function(e, t) {
                grid.bpcodeeditor.startEdit(t);
            });
        }

        // To save grid row data into form params
        grid.on('edit', function (editor, ctx) {
            me.updateSapParamsData(me);
        });

        // Initialize header values
        if (me.getView().sapbpcodes) {
            let bpcodes = me.getView().sapbpcodes;
            for (let key of ['tradebp_v', 'tradebp_c', 'nontradebp_v', 'nontradebp_c']) {
                let header  = "header_" + key;
                if (bpcodes[key] && 0 < bpcodes[key].length) {
                    me.lookupReference(key).setValue(bpcodes[key]);    // Value to be sent to server
                    grid.lookupReference(header).textEl.setText(bpcodes[key]);
                }
            }
        }

    },

    updateSapParamsData: function(controller) {
        let rows = [];
        let store = controller.lookupReference('sapsettingsgrid').store;
        let value = "";

        store.each(function(item) {
            rows.push({
                id: item.get('sapsettingid'),
                transactiontype: item.get('transactiontype'),
                itemcode: item.get('itemcode'),
                tradebpvendor: item.get('header_tradebp_v'),
                tradebpcus: item.get('header_tradebp_c'),
                nontradebpvendor: item.get('header_nontradebp_v'),
                nontradebpcus: item.get('header_nontradebp_c'),
                action: item.get('action'),
                gtprefno: item.get('gtprefno')
            });
        });

        if (0 < rows.length) {
            value = Ext.JSON.encode(rows);
        }
        controller.lookupReference('sapsettingsparams').setValue(value);
    }
});
