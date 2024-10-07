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

    getPartnerExport: function(btn){
        var myView = this.getView(),
        // grid header data
        header = [];
        // partnerCode = myView.partnercode;
        //debugger;
        // Check if buy or sell based on button reference
        /*
        if('dailytransactionsell' == btn.reference){
            // filter by companysell
            
        }else if('dailytransactionbuy' == btn.reference){
            // filter by companybuy
        }
        */
       type = btn.reference;
       

    //    const reportingFields = [
    //         ['Date', ['createdon', 0]], 
    //         ['Transaction Ref No', ['refno', 0]],
            
    //     ];
    //     //{ key1 : [val1, val2, val3] } 
        
    //     for (let [key, value] of reportingFields) {
    //         //alert(key + " = " + value);
    //         columnleft = {
    //             // [_key]: _value
    //             text: key,
    //             index: value[0]
    //         }
            
    //         if (value[0] !== 0){
    //             columnleft.decimal = value[1];
    //         }
    //         header.push(columnleft);
    //     }
        
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
                if('ordpartnername' == column.dataIndex || 'ordstatus' == column.dataIndex){
                    // dont push header if its status
                }else {
                    header.push(columnlist);
                }
              
            }
        });

        // Add a transaction header 
        
        
        startDate = this.getView().getReferences().startDate.getValue();
        endDate = this.getView().getReferences().endDate.getValue();

        // Alter this check if partner is BMMB
        if(!startDate || !endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Please select date range for export',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }

        if(startDate > endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date cannot be later than End date',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
        
        // Do a daterange checker
        // If date exceeds 2 months, reject
        // Init date values
        var msecPerMinute = 1000 * 60;
        var msecPerHour = msecPerMinute * 60;
        var msecPerDay = msecPerHour * 24;

        // Calculate date interval 
        var interval = endDate - startDate;

        var intervalDays = Math.floor(interval / msecPerDay );

        // Get days of months
        // Startdate
        startMonth = new Date(startDate.getYear(), startDate.getMonth(), 0).getDate();

        endMonth = new Date(endDate.getYear(), endDate.getMonth(), 0).getDate();

        // Get 2 months range limit for filter
        rangeLimit = startMonth + endMonth;

        if (startDate && endDate){
            // Check if day exceeds 63 days 
            // Skip this check if partner is BMMB
            // if(partnerCode != 'BMMB'){
            //     if (rangeLimit >= intervalDays){
            //         // Check if day exceeds 63 days 
                    
            //         startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //         endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //         daterange = {
            //             startDate: startDate,
            //             endDate: endDate,
            //         }
            //     }else{
            //         Ext.MessageBox.show({
            //             title: 'Filter Date',
            //             msg: 'Please select date range within 2 months',
            //             buttons: Ext.MessageBox.OK,
            //             icon: Ext.MessageBox.ERROR
            //         });
            //         return
            //     }
            //     // End check
            // }else{
            //     // for bmmb no limit imposed
            //     startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //     endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //     daterange = {
            //         startDate: startDate,
            //         endDate: endDate,
            //     }
            // }
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

        type = encodeURI(JSON.stringify(type));

        // partnerCode = myView.partnercode;
        //url = '?hdl=bmmborder&action=exportExcel&header='+header+'&daterange='+daterange+'&type='+type;'
        url = '?hdl=partner&action=exportExcel&header='+header+'&daterange='+daterange;
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

    getDateRange: function(){ 

        // _this = this;
        vm = this.getViewModel();

        startDate = this.getView().getReferences().startDate.getValue()
        endDate = this.getView().getReferences().endDate.getValue()

        if (startDate && endDate){
            startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
        }else{
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
        this.getView().getStore().addFilter(
            {
                property: "createdon", type: "date", operator: "BETWEEN", value: [startDate, endDate]
            },
        )
    },

});
