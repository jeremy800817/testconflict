Ext.define('snap.view.partner.PartnerController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.partner-partner',
    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {
        var mask = Ext.getBody().mask('Loading...');
        mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);
        snap.getApplication().sendRequest({
            hdl: 'partner', 'action': 'fillform', id: record.data.id
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {

                    //alert("aa");
                    Ext.getBody().unmask();
                    var partnerserviceStore = Ext.getStore("partnerserviceStore");
                    partnerserviceStore.removeAll();
                    partnerserviceStore.add(data.servicerecord);
                    var partnerbranchStore = Ext.getStore("partnerbranchStore");
                    partnerbranchStore.removeAll();
                    partnerbranchStore.add(data.branchrecord);

                    //form.down('radiogroup').setValue({ status: data.servicerecord.status });
                    formView.getController().lookupReference('sapcompanysellcode1').getStore().loadData(data.apicodescustomer);
                    formView.getController().lookupReference('sapcompanybuycode1').getStore().loadData(data.apicodesvendor);

                    // Add status for ktp partner group
                    formView.getController().lookupReference('parent').getStore().loadData(data.parent);
                    formView.getController().lookupReference('parent').setValue(data.parentid);
                    //formView.getController().lookupReference('sapcompanybuycode2').getStore().loadData(data.apicodesvendor);
                    //getSapVendorCodes(record);
                    // Populate Combo Boxes with existing api data
                    //formView.getController().lookupReference('cardiodoc').getStore().loadData(data.cardCode);

                    // Check if have group value
                    // If there is partner group, means ktp enabled
                    if(data.group == true){
                        formView.getController().lookupReference('isktp').setValue(true);
                    }


                    Ext.Object.each(data.settings, function (key, value) {
                        if (formView.getController().lookupReference(key) !== null) {
                            formView.getController().lookupReference(key).setValue(value);
                        }                        
                    });
                    
                    var sapsettingsStore = Ext.getStore("sapsettingsStore");
                    sapsettingsStore.removeAll();
                    sapsettingsStore.add(data.sapsettingsrecord);

                    formView.sapbpcodes = data.sapbpcodes;
                }
            });



        return true;
    },

    onPostLoadEmptyForm: function( formView, form) {
        snap.getApplication().sendRequest({
            hdl: 'partner', 'action': 'prefillform',
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {

                    formView.getController().lookupReference('sapcompanysellcode1').getStore().loadData(data.apicodescustomer);
                    formView.getController().lookupReference('sapcompanybuycode1').getStore().loadData(data.apicodesvendor);
                    formView.getController().lookupReference('parent').getStore().loadData(data.parent);
                    //formView.getController().lookupReference('sapcompanysellcode2').getStore().loadData(data.apicodescustomer);
                    //formView.getController().lookupReference('sapcompanybuycode2').getStore().loadData(data.apicodesvendor);

                }
            });
    },


    // *************************************************************************************
    // Get SAP VENDOR
    // *************************************************************************************

    getSapVendorCodes: function(record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();

        snap.getApplication().sendRequest({
            hdl: 'partner', action: 'getSapVendorCodes', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                //alert("aaa");

            }

        });
    },

});
