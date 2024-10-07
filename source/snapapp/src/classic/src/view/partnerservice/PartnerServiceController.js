Ext.define('snap.view.partnerservice.PartnerServiceController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.partnerservice-partnerservice',
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
    // Get Report
    // *************************************************************************************
    getPrintReport: function(btn){

        var myView = this.getView(),
        // grid header data
        header = [];
        additionalHeader = [];
        partnerCode = myView.partnerCode;

        const reportingFields = [
            // ['Date', ['createdon', 0]], 
            
        ];

        const additionalFields = [
            ['Buy Balance', ['buybalance', 3]],
            ['Sell Balance', ['sellbalance', 3]],
        ];
        //{ key1 : [val1, val2, val3] } 
        
        for (let [key, value] of reportingFields) {
            //alert(key + " = " + value);
            columnlist = {
                // [_key]: _value
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                
                // Do check to convert string
                if (value[1] === 'string'){
                    columnlist.convert = value[1];
                    columnlist.decimal = 0;
                }else{
                    columnlist.decimal = value[1];
                }
            }

            header.push(columnlist);
        }

        for (let [key, value] of additionalFields) {
            //alert(key + " = " + value);
            columnlist = {
                // [_key]: _value
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                
                // Do check to convert string
                if (value[1] === 'string'){
                    columnlist.convert = value[1];
                    columnlist.decimal = 0;
                }else{
                    columnlist.decimal = value[1];
                }
            }

            additionalHeader.push(columnlist);
        }

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
                    if('buybalance' == column.dataIndex || 'sellbalance' == column.dataIndex || 'createdon' == column.dataIndex){
                        // dont push header if its status
                    }else {
                        header.push(columnlist);
                    }
                }
            });

        // startDate = this.getView().getReferences().startDate.getValue()
        // endDate = this.getView().getReferences().endDate.getValue()
        startDate = new Date(null);
        endDate = new Date();
      
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
        additionalHeader = encodeURI(JSON.stringify(additionalHeader));
        daterange = encodeURI(JSON.stringify(daterange));

        url = '?hdl=partnerservicehandler&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode+'&additionalheader='+additionalHeader;
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
