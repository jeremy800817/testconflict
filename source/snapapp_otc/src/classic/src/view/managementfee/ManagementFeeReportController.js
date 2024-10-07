Ext.define('snap.view.managementfee.ManagementFeeReportController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.managementfeereport-managementfeereport',
 
    getDateRangeManagementFee: function () {
        vm = this.getViewModel();
        startDate = this.getView().getReferences().startDate.getValue()
        endDate = this.getView().getReferences().endDate.getValue()
        if (startDate && endDate) {
            startDate = Ext.Date.format(startDate, 'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate, 'Y-m-d 23:59:59');
        } else {
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

    getPrintReportManagementFee: function(btn){

        header = [];
        const reportingFields = [['Date', ['createdon', 0]]];
        
        for (let [key, value] of reportingFields) {
            columnlist = {
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                
                if (value[1] === 'string'){
                    columnlist.convert = value[1];
                    columnlist.decimal = 0;
                }else{
                    columnlist.decimal = value[1];
                }
            }

            header.push(columnlist);
        }

        btn.up('grid').getColumns().map(column => {
            if (column.isVisible() && column.dataIndex !== null){
                _key = column.text
                _value = column.dataIndex
                columnlist = {
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

        url = '?hdl=mymanagementfeereport&action=exportExcelManagementFee&header='+header+'&daterange='+daterange+'&partnercode='+PROJECTBASE;

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
  
    clearDateRange: function () {
      startDate = this.getView().getReferences().startDate.setValue('')
      endDate = this.getView().getReferences().endDate.setValue('')
      filter = this.getView().getStore().getFilters().items[0];
      if (filter) {
        this.getView().getStore().removeFilter(filter)
      } else {
        Ext.MessageBox.show({
          title: 'Clear Filter',
          msg: 'No Filter.',
          buttons: Ext.MessageBox.OK,
          icon: Ext.MessageBox.ERROR
        });
        return
      }
    },

    getPrintReportManagementFeeStatus: function(btn){

        var myView = this.getView();
        var viewname = myView.routeId;

        header = [];
        const reportingFields = [['Date', ['createdon', 0]]];
        
        for (let [key, value] of reportingFields) {
            columnlist = {
                text: key,
                index: value[0]
            }
            
            if (value[0] !== 0){
                
                if (value[1] === 'string'){
                    columnlist.convert = value[1];
                    columnlist.decimal = 0;
                }else{
                    columnlist.decimal = value[1];
                }
            }

            header.push(columnlist);
        }

        btn.up('grid').getColumns().map(column => {
            if (column.isVisible() && column.dataIndex !== null){
                _key = column.text
                _value = column.dataIndex
                columnlist = {
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

        url = '?hdl=otcoutstandingstoragefeejob&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+PROJECTBASE;
		if (viewname == 'managementfeedeductionsuccess') url += '&status=success';
		if (viewname == 'managementfeedeductionfailed') url += '&status=failed';

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
