Ext.define('snap.view.collection.PosCollectionController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.collection-poscollection',


    config: {
        companyid: 4,
    },
    
    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'collection', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },

    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {
        var mask = Ext.getBody().mask('Loading...');
        mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);
        snap.getApplication().sendRequest({
            hdl: 'collection', 'action': 'fillform', id: record.data.id
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {

                    //alert("aa");
                    Ext.getBody().unmask();
                    var collectionserviceStore = Ext.getStore("collectionserviceStore");
                    collectionserviceStore.removeAll();
                    collectionserviceStore.add(data.servicerecord);
                    var collectionbranchStore = Ext.getStore("collectionbranchStore");
                    collectionbranchStore.removeAll();
                    collectionbranchStore.add(data.branchrecord);
                    //form.down('radiogroup').setValue({ status: data.servicerecord.status });
                    formView.getController().lookupReference('sapcompanysellcode1').getStore().loadData(data.apicodescustomer);
                    formView.getController().lookupReference('sapcompanybuycode1').getStore().loadData(data.apicodesvendor);
                    //formView.getController().lookupReference('sapcompanysellcode2').getStore().loadData(data.apicodescustomer);
                    //formView.getController().lookupReference('sapcompanybuycode2').getStore().loadData(data.apicodesvendor);
                    //getSapVendorCodes(record);
                    // Populate Combo Boxes with existing api data
                    //formView.getController().lookupReference('cardiodoc').getStore().loadData(data.cardCode);

                }
            });



        return true;
    },

    onPostLoadEmptyForm: function( formView, form) {
        snap.getApplication().sendRequest({
            hdl: 'collection', 'action': 'prefillform',
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {

                    // formView.getController().lookupReference('companycombo').getStore().loadData(data.companies);
                    formView.getController().lookupReference('salespersoninput').setValue(data.salesperson);
                    // formView.getController().lookupReference('salespersoncombo').getStore().loadData(data.salesperson);

                }
            });
        // formView.getController().lookupReference('salespersoninput').setValue(sanp.salesperson);
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
            hdl: 'collection', action: 'getSapVendorCodes', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                //alert("aaa");

            }

        });
    },

    onclickcompany: function(record) {

        x = record.lookupController().getView().controller.getCompanyid();
        console.log(x);
        return;
        
        snap.getApplication().sendRequest({
            hdl: 'collection', action: 'getCustomerList',  id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                //alert("aaa");
                console.log(data)
            }

        });
    },

    inventorycatComboSelected: function(combo, record) {
        var vm = this.getViewModel(),
            map = vm.get('inventoryMap');
        map[record.id] = record.data.name;
        console.log(record.data, "invcat selected");
        console.log(vm.get('inventoryMap'), "GET");
        vm.set('inventoryMap', map);
        // var itemRecord = combo.up('editor').context.record;
    },


    onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm) {
        console.log(formAction, theGridFormPanel, theGridForm,'onPreAddEditSubmit');
        console.log(theGridFormPanel,'theGridFormPanel')
        // var isEditMode = (theGridForm.findField('id').getValue().length > 0) ? true : false;
        // if(isEditMode) {
        //     theGridForm.setValues({action: 'edit'});
        // } else {
        //     theGridForm.setValues({action: 'add'});
        // }
        // return true;

        polists = theGridFormPanel.getController().lookupReference('pocontainer').getStore().data.items
        ratecardlists = theGridFormPanel.getController().lookupReference('itemsgird').getStore().data.items

        console.log(ratecardlists,'ratecardlistsratecardlistsratecardlistsratecardlists')

        total_expected = theGridFormPanel.getController().lookupReference('display_weight').getValue()
        total_gross_weight = parseFloat(total_expected) - parseFloat(theGridFormPanel.getController().lookupReference('display_balanceweight').getValue())
        total_xau_collected = theGridFormPanel.getController().lookupReference('display_xauweight').getValue()
        vatsum = theGridFormPanel.getController().lookupReference('display_balanceweight').getValue()

        // if (parseFloat(vatsum) < 0){
        //     Ext.MessageBox.show({
        //         title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
        //         msg: 'Balance is negative.'});
        //     return;
        // }
        summary = {
            "total_expected_xau": total_expected,
            "total_gross_weight": total_gross_weight,
            "total_xau_collected": total_xau_collected,
            "vatsum": 0,
        }
        console.log(ratecardlists)
        
        trans_ratecard = [];
        // listing = ratecardlists.data.items
        x = 0;
        ratecardlists.map((list)=>{
            list.data.details.map((detail)=>{
                x++
                purity += detail.u_purity
                xauweight += detail.gtp_xauweight
                grossweight += detail.gtp_inputweight
                ratecard = {
                    "u_itemcode": detail.u_itemcode,
                    "u_purity": detail.u_purity,
                    "u_inputweight": detail.gtp_inputweight,
                    "u_xauweight": detail.gtp_xauweight // not in use, output need recalculate
                }
                trans_ratecard.push(ratecard);
            })
        })
        list_length = x
        // ratecardlists.map(function(value, index){
        //     ratecard = {
        //         "u_itemcode": value.data.u_itemcode,
        //         "u_purity": value.data.u_purity,
        //         "u_inputweight": value.data.gtp_inputweight,
        //         "u_xauweight": value.data.gtp_xauweight // not in use, output need recalculate
        //     }
        //     trans_ratecard.push(ratecard);

        //     // total_purity += parseFloat(value.data.u_purity)
        //     // total_inputweight += parseFloat(value.data.gtp_inputweight)
        //     // total_grossweight += parseFloat(value.data.gtp_grossweight)
        // })
        trans_po = []
        polists.map(function(value, index){
            po = {
                "docEntry": value.data.docEntry,
                "docNum": value.data.docNum,
                "u_GTPREFNO": value.data.u_GTPREFNO
            }
            trans_po.push(po);

            // total_weight += parseFloat(value.data.docTotalAmt)
        })

        customer = theGridFormPanel.getController().lookupReference('customercombox').value

        req = {
            'po': trans_po,
            'ratecard': trans_ratecard,
            'customer': customer,
            'summary': summary
        }
        console.log(req)

        snap.getApplication().sendRequest({
            hdl: 'collection', 
            action: 'addgrn', 
            data: JSON. stringify(req)
        }, 'Fetching data from server....')
            .then(
            //Received data from server already
            function(data){
                if(data.success){
                    Ext.MessageBox.show({
                        title: 'Successful', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.SUCCESS,
                        msg: 'Submit to SAP success.'});
                        theGridFormPanel.close()
                    return;
                }
            });
    },


    uploadcollectionpos: function(btn, formAction) {
        var me = this, selectedRecord,
            myView = this.getView();
        var sm = myView.getSelectionModel();
        

        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.uploadcollectionposform ? myView.uploadcollectionposform : {}, {
            formDialogButtons: [{
                xtype:'panel',
                flex:3
            },
            {
                text: 'Upload File',
                flex: 2,
                handler: function(btn) {
                    me._uploadFile(btn);
                }
            },{
                text: 'Close',
                flex: 1,
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            },{
                xtype:'panel',
                flex: 2,
            }]
        }));


        this.gridFormView = gridFormView;
        this._formAction = "edit";

        var addEditForm = this.gridFormView.down('form').getForm();

        gridFormView.title = 'Update ' + gridFormView.title + '...';
        // var sm = this.getView().getSelectionModel();
        // var selectedRecords = sm.getSelection();
        // var selectedRecord = selectedRecords[0];
        // if(Ext.isFunction(me['onPreLoadForm'])) {
        //     if(! this.onPreLoadForm( gridFormView, addEditForm, selectedRecord, function(updatedRecord){
        //         addEditForm.loadRecord(updatedRecord);
        //         if(Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm( gridFormView, addEditForm, updatedRecord);
        //         me.gridFormView.show();
        //       })) return;
        // }
        // addEditForm.loadRecord(selectedRecord);
        // if(Ext.isFunction(me['onPostLoadForm'])) this.onPostLoadForm( gridFormView, addEditForm, selectedRecord);

        this.gridFormView.show();
    },
    _uploadFile: function(btn) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this,
        myView = this.getView();
        // console.log('form',btn);return
        form = btn.lookupController().lookupReference('grnposlist-form').getForm();
        transactionlisting = form.getFieldValues();
        if (form.isValid()) {
            if ( transactionlisting.grnposlist != null) {
                form.submit({
                    url: 'index.php?hdl=collection&action=uploadCollectionPOS',
                    // url: 'index.php?hdl=tender&action=uploadTenderFile',
                    dataType: "json",
                    waitMsg: 'Uploading your POS GRN list...',
                    success: function(fp, o) {
                        if (o.result.error){
                            Ext.Msg.alert('Exception', o.result.message);
                            return;
                        }
                        if (o.result.success){
                            Ext.Msg.alert('Success', 'Your excel list has converted to GRN.');
                            return;
                        }
                    },
                    
                });
            } else {
                    Ext.MessageBox.show({
                    title: "ERROR-A1001",
                    msg: "Choose correct .xlsx file",
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.WARNING
                });
            }
        } else {
                Ext.MessageBox.show({
                title: "ERROR-A1001",
                msg: "Please fill the required fields correctly.",
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.WARNING
            });
        }
    },

    getPrintReport: function(btn){

        var myView = this.getView();
        // grid header data
        header = []
        btn.up('grid').getColumns().map(column => {
            if (column.isVisible() && column.dataIndex !== null){
                _key = column.text
                _value = column.dataIndex
                columnlist = {
                    // [_key]: _value
                    text: _key,
                    index: _value
                }
                if (column.exportdecimal !== null){
                    _decimal = column.exportdecimal;
                    columnlist.decimal = _decimal;
                }
                header.push(columnlist);
            }
        });

        startDate = this.getView().getReferences().startDate.getValue()
        endDate = this.getView().getReferences().endDate.getValue()

        if (startDate && endDate){
            startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            daterange = {
                startDate: startDate,
                endDate: endDate,
            }
        }else{
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }

        header = encodeURI(JSON.stringify(header));
        daterange = encodeURI(JSON.stringify(daterange));

        url = '?hdl=poscollection&action=exportExcel&header='+header+'&daterange='+daterange;
        // url = Ext.urlEncode(url);

        Ext.DomHelper.append(document.body, {
            tag: 'iframe',
            id:'downloadIframe',
            frameBorder: 0,
            width: 0,
            height: 0,
            css: 'display:none;visibility:hidden;height: 0px;',
            src: url
          });
    },
});
