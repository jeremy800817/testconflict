Ext.define('snap.view.priceadjuster.PriceAdjusterTreeController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-priceadjustertreecontroller',

    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.priceadjuster', { id: 0, }), null);
        // this.onPreLoadForm(formView, form, Ext.create('snap.model.CollectionServices', { id: 0, }), null);
    },
   
    setBranchesParamsFormData: function (store) {
        var me = this;
        var grid = this.lookupReference("collectionbranch").getStore();
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

    onclickpriceprovider: function(record){
        // console.log(this, "OOOOOTHIS")

        this.lookupReference("hourscombo").setSelection(this.lookupReference("hourscombo").getSelection())
        this.lookupReference("hourscombo").fireEvent('select', this.lookupReference("hourscombo"));
        // priceCombo = this.lookupReference("hourscombo").getSelection();
        // this.lookupReference("hourscombo").selection(priceCombo.id)
    },

    onclickprice: function(record) {
        // x = record.value;
        // console.log(record,x,'record.value;');
        
        tier = record.getSelection().data.tier.toString();
        // time = record.getSelection().data.time.toString();
        // timeend = record.getSelection().data.timeend.toString();
        
        // Do check if default
        priceproviderid = this.lookupReference("pricecombo").selection.id

        // return;
        
        snap.getApplication().sendRequest({
            hdl: 'priceadjuster', action: 'getLatestData',  tier: ((record && record.value) ? tier : 0), providerid: priceproviderid,
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                //alert("aaa");
                console.log(data.data)
                record.up('form').getForm().setValues(data.data);
            }
        });
        // snap.getApplication().sendRequest({
        //     hdl: 'priceadjuster', action: 'getLatestData',  time: ((record && record.value) ? time : 0), timeend: ((record && record.value) ? timeend : 0), providerid: priceproviderid,
        // }, 'Fetching data from server....').then(
        // //Received data from server already
        // function(data){
        //     if(data.success){
        //         //alert("aaa");
        //         console.log(data.data)
        //         record.up('form').getForm().setValues(data.data)
        //     }
        // });
    },

    
});
