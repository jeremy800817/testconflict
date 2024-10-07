Ext.define('snap.view.pricedelay.PriceDelayController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.pricedelay-pricedelay',


    config: {
    },

    onPostLoadEmptyForm: function( formView, form) {
        snap.getApplication().sendRequest({
            hdl: 'pricedelay', 'action': 'prefillform',
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {

                    formView.getController().lookupReference('pricecombo').getStore().loadData(data.priceproviders);
                    
                }
            });
    },

    // onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm, btn) {
    //     _this = this;
    //     Ext.MessageBox.confirm('Confirm', 'This will change your current data.', function(id) {
    //         console.log(id,theGridFormPanel);
    //         if (id == 'yes') {
    //             btn = theGridFormPanel.down('button');
    //             _this._onSaveGridForm(btn)
    //         }else{
    //             return false;
    //         }
    //     })
    // },
});
