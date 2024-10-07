Ext.define('snap.view.order.GoOrderController', {
    extend: 'snap.view.order.MyOrderController',
    alias: 'controller.goorder-goorder',

    getTransactionReport: function(btn){
        var myView = this.getView(),
        // grid header data
        header = [];
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
       

       const reportingFields = [
            ['Date', ['ordbookingon', 0]], 
            ['Transaction Ref No', ['refno', 0]],
            
        ];
        //{ key1 : [val1, val2, val3] } 
        
        for (let [key, value] of reportingFields) {
            //alert(key + " = " + value);
            columnleft = {
                // [_key]: _value
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                columnleft.decimal = value[1];
            }
            header.push(columnleft);
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
                if('ordpartnername' == column.dataIndex || 'refno' == column.dataIndex || 'ordstatus' == column.dataIndex || 'ordstatus' == column.dataIndex){
                    // dont push header if its status
                }else {
                    header.push(columnlist);
                }
              
            }
        });

        // Add a transaction header 
        
        
        startDate = this.getView().getReferences().startDate.getValue();
        endDate = this.getView().getReferences().endDate.getValue();

        if(!startDate || !endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Please select date range within 2 months',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
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
            if (rangeLimit >= intervalDays){
                // Check if day exceeds 63 days 
                
                startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
                endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
                daterange = {
                    startDate: startDate,
                    endDate: endDate,
                }
            }else{
                Ext.MessageBox.show({
                    title: 'Filter Date',
                    msg: 'Please select date range within 2 months',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
                return
            }
            // End check

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

        partnerCode = myView.partnercode;
        //url = '?hdl=bmmborder&action=exportExcel&header='+header+'&daterange='+daterange+'&type='+type;'
        url = '?hdl=myorder&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
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
