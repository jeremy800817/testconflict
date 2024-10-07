Ext.define('snap.view.collection.SharedCollectionTreeController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-sharedcollectiontreecontroller',

    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.sharedcollection', { id: 0, }), null);
        this.onPreLoadForm(formView, form, Ext.create('snap.model.SharedCollectionServices', { id: 0, }), null);
    },

    cmdc: function(a){
        console.log(a,'cmdc')

        console.log(this,'cmdcthis')

        total_purity = 0;
        total_inputweight = 0;
        total_xauweight = 0;
        total_weight = 0;

        // eg: total_weight = 100;
        // total_weight = this.getViewModel().data.total_poweight

        lists = this.getView().getReferences().ratecardgird.getStore().data.items
        polists = this.getView().getReferences().pocontainer.getStore().data.items
        list_length = this.getView().getReferences().ratecardgird.getStore().data.items.length
        lists.map(function(value, index){
            total_purity += parseFloat(value.data.u_purity)
            total_inputweight += parseFloat(value.data.gtp_inputweight)
            total_xauweight += parseFloat(value.data.gtp_xauweight)
        })
        polists.map(function(value, index){
            // total_weight += parseFloat(value.data.docTotalAmt)
            total_weight += parseFloat(value.data.quantity)
        })
        this.getViewModel().data.total_poweight = total_weight.toFixed(2)


        final_purity = total_purity / list_length;
        final_balance = total_weight - total_inputweight;

        console.log(total_xauweight,final_purity,final_balance)

        this.getViewModel().data.total_purity = final_purity.toFixed(2)
        this.getViewModel().data.total_xauweight = total_xauweight.toFixed(2)
        this.getViewModel().data.total_balanceweight = final_balance.toFixed(2)

        this.getView().lookupReference('display_weight').setValue(total_weight.toFixed(2))
        this.getView().lookupReference('display_balanceweight').setValue(final_balance.toFixed(2))
        this.getView().lookupReference('display_purity').setValue(final_purity.toFixed(2))
        this.getView().lookupReference('display_xauweight').setValue(total_xauweight.toFixed(2))
        // temp1.getViewModel().data.input_...
    },
    cmdd: function(purity, xauweight, grossweight, list_length){


        total_purity = 0;
        total_inputweight = 0;
        total_xauweight = 0;
        total_weight = 0;

        // eg: total_weight = 100;
        // total_weight = this.getViewModel().data.total_poweight

        // lists = this.getView().getReferences().ratecardgird.getStore().data.items
        polists = this.getView().getReferences().pocontainer.getStore().data.items
        // list_length = this.getView().getReferences().ratecardgird.getStore().data.items.length
        // lists.map(function(value, index){
        //     total_purity += parseFloat(value.data.u_purity)
        //     total_inputweight += parseFloat(value.data.gtp_inputweight)
        //     total_xauweight += parseFloat(value.data.gtp_xauweight)
        // })
        polists.map(function(value, index){
            // total_weight += parseFloat(value.data.docTotalAmt)
            total_weight += parseFloat(value.data.quantity)
        })
        this.getViewModel().data.total_poweight = total_weight.toFixed(2)


        purity, xauweight, grossweight

        final_purity = purity / list_length;
        final_balance = total_weight - grossweight;

        this.getViewModel().data.total_purity = final_purity.toFixed(2)
        this.getViewModel().data.total_xauweight = xauweight.toFixed(2)
        this.getViewModel().data.total_balanceweight = final_balance.toFixed(2)

        this.getView().lookupReference('display_weight').setValue(total_weight.toFixed(2))
        this.getView().lookupReference('display_balanceweight').setValue(final_balance.toFixed(2))
        this.getView().lookupReference('display_purity').setValue(final_purity.toFixed(2))
        this.getView().lookupReference('display_xauweight').setValue(xauweight.toFixed(2))
        // temp1.getViewModel().data.input_...
    },

    customerComboSelected: function(a,b,c){

        url = 'index.php?hdl=collection&action=getPODetail' + '&query=' + a.value
        if (this.getView().lookupReference('searchpocontainer') && this.getView().lookupReference('searchpocontainer').getSearchStore()){
            this.getView().lookupReference('searchpocontainer').getSearchStore().getProxy().setUrl(url)
            this.getView().lookupReference('pocontainer').getStore().removeAll()
            this.getView().lookupReference('searchpocontainer').getSearchStore().removeAll()
            this.getView().lookupReference('searchpocontainer').getSearchStore().load()

        }

        if (this.getView().lookupReference('searchitemscontainer') && this.getView().lookupReference('searchitemscontainer').getSearchStore()){
            code = a.value;
            u_GTPREFNO_s = [];
            polists = this.getView().lookupReference('pocontainer').getStore().data.items;
            polists.map(function(value, index){
                u_GTPREFNO_s.push(value.data.u_GTPREFNO);
            });

            url = 'index.php?hdl=collection&action=getPreDraftGrnItemList' + '&code=' + code + '&u_GTPREFNO_s=' + JSON.stringify(u_GTPREFNO_s);

            this.getView().lookupReference('searchitemscontainer').getSearchStore().getProxy().setUrl(url)
            this.getView().lookupReference('itemsgird').getStore().removeAll()
            this.getView().lookupReference('searchitemscontainer').getSearchStore().removeAll()
            this.getView().lookupReference('searchitemscontainer').getSearchStore().load()
        }
        return


        this.getView().lookupReference('pocontainer').getStore().removeAll()


        this.getView().lookupReference('searchpocontainer').store.proxy.url += '&query=' + a.value;
        if (this.getView().lookupReference('searchpocontainer').getSearchStore()){
            this.getView().lookupReference('searchpocontainer').getSearchStore().load()
        }

        return;

        this.getView().lookupReference('searchpocontainer').getSearchStore().load();
        url = this.getView().lookupReference('searchpocontainer').getSearchStore().getProxy().url;
        url += '&query=' + a.value;
        this.getView().lookupReference('searchpocontainer').getSearchStore().getProxy().setUrl(url);
        this.getView().lookupReference('searchpocontainer').getSearchStore().load();
        console.log(this)
        console.log(a,b,c)
    },

    saveSingleDraftInput: function(btn, asd){
        // data_userinputweight = btn.lookupReferenceHolder().lookupReference('inputweight').getValue();
        // console.log(btn,'BTN')
        if (btn.lookupReferenceHolder().lookupReference('itemsgird').getSelection()){
            selected_item = btn.lookupReferenceHolder().lookupReference('itemsgird').getSelection()[0].data

            data = JSON.stringify(selected_item);

            // data_referenceno = btn.lookupReferenceHolder().lookupReference('itemsgird').getSelection()[0].data.referenceno;
        }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Empty Item.'});
            return;
        }
        snap.getApplication().sendRequest({
            hdl: 'collection', 
            action: 'saveSingleItemDraft', 
            data: data,
        }, 'Fetching data from server....')
            .then(
            //Received data from server already
            function(data){
                if(data.success){
                    Ext.MessageBox.show({
                        title: 'Successful', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.SUCCESS,
                        msg: 'Input Saved.'});
                    return;
                }
            });
    },

    branchComboSelected: function(_this, elemnt){
        code = this.getView().lookupReference('customercombox').value
        u_GTPREFNO_s = [];
        polists = this.getView().lookupReference('pocontainer').getStore().data.items;
        polists.map(function(value, index){
            u_GTPREFNO_s.push(value.data.u_GTPREFNO);
        });
        url = 'index.php?hdl=collection&action=getPreDraftGrnItemList' + '&code=' + code + '&u_GTPREFNO_s=' + JSON.stringify(u_GTPREFNO_s) + '&query=' + _this.value;
        // url = 'index.php?hdl=collection&action=getPreDraftGrnItemList' + '&query=' + _this.value
        if (this.getView().lookupReference('searchitemscontainer').getSearchStore()){
            this.getView().lookupReference('searchitemscontainer').getSearchStore().getProxy().setUrl(url)
            this.getView().lookupReference('itemsgird').getStore().removeAll()
            this.getView().lookupReference('searchitemscontainer').getSearchStore().removeAll()
            this.getView().lookupReference('searchitemscontainer').getSearchStore().load()

        }
        return
    }
});


// temp1 = store
// temp1.collect('type') // distinct column
// temp1.queryBy(function(x){ return (x.data.type == 'CompanyBuy')  })