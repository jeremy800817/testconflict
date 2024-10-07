Ext.define('snap.view.pricedelay.PriceDelayTreeController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-pricedelaytreecontroller',

    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.pricedelay', { id: 0, }), null);
        // this.onPreLoadForm(formView, form, Ext.create('snap.model.CollectionServices', { id: 0, }), null);
    },
   
 //   setBranchesParamsFormData: function (store) {
 //       var me = this;
  //      var grid = this.lookupReference("collectionbranch").getStore();
   //     var paramsFormData = new Array();
    //    var dataStored = "";
   //     grid.each(function (item, index, totalItems) {
   //         var paramsFormItemData = {
   //             id: item.get('id'),
   //             code: item.get('code'),
   //             name: item.get('name'),
   //             sapcode: item.get('sapcode'),
  //              address: item.get('address'),
    //            postcode: item.get('postcode'),
     //           city: item.get('city'),
    //            contactno: item.get('contactno'),
     //           status: item.get('status') ? '1' : '0',                
      //      };
     //       paramsFormData.push(Ext.JSON.encode(paramsFormItemData));
      //  });
      //  if (paramsFormData.length > 0) dataStored = "[" + paramsFormData.join() + "]";
     //   console.log(dataStored);
     //   store.setValue(dataStored);
  //  },    


    onclickprice: function(record) {

        x = record.value;
        console.log(record,x,'record.value;');

     //    return;
        
        snap.getApplication().sendRequest({
            hdl: 'priceaddelay', action: 'getLatestData',  id: ((record && record.value) ? record.value : 0)
        }, 'Fetching data from server....').then(
       //  get data from server
        function(data){
            if(data.success){
                //alert("aaa");
                console.log(data.data) //if success
                record.up('form').getForm().setValues(data.data)
            }
        });
    },

    
});
