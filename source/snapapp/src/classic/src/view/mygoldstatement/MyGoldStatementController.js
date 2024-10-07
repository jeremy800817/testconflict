Ext.define('snap.view.mygoldstatement.MyGoldStatementController', {
  extend: 'snap.view.gridpanel.BaseController',
  alias: 'controller.mygoldstatement-mygoldstatement',

  getPrintReport: function (btn) {

    var accountHolderId = this.getView().accountHolderId;
    // grid header data
    header = []
    btn.up('grid').getColumns().map(column => {
      if (column.isVisible() && column.dataIndex != null && column.exportcolumn == null) {
        _key = column.text
        _value = column.dataIndex
        columnlist = {
          // [_key]: _value
          text: _key,
          index: _value
        }
        if (column.exportdecimal !== null) {
          _decimal = column.exportdecimal;
          columnlist.decimal = _decimal;
        }
        header.push(columnlist);
      } else if (column.isVisible() && column.dataIndex != null && column.exportcolumn != null) {
        columnlist = {
          text: column.text,
          index: column.dataIndex,
          calculate: column.exportcolumn
        }
        header.push(columnlist)
      }
    });

    startDate = this.getView().getReferences().startDate.getValue()
    endDate = this.getView().getReferences().endDate.getValue()

    if (startDate && endDate) {

      if (this.checkDateRangeExceedLimit(startDate, endDate)) {
        Ext.MessageBox.show({
          title: 'Filter Date',
          msg: 'Please select date range within 2 months',
          buttons: Ext.MessageBox.OK,
          icon: Ext.MessageBox.ERROR
        });

        return;
      }

      startDate = Ext.Date.format(startDate, 'Y-m-d 00:00:00');
      endDate = Ext.Date.format(endDate, 'Y-m-d 23:59:59');
      daterange = {
        startDate: startDate,
        endDate: endDate,
      }
    } else {
      Ext.MessageBox.show({
        title: 'Filter Date',
        msg: 'Start date and End date required.',
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.ERROR
      });
      return
    }

    

    header = encodeURIComponent(JSON.stringify(header));
    daterange = encodeURIComponent(JSON.stringify(daterange));

    url = '?hdl=mygoldstatement&action=exportExcel&accountholderid=' + accountHolderId + '&header=' + header + '&daterange=' + daterange;
    // url = Ext.urlEncode(url);

    Ext.DomHelper.append(document.body, {
      tag: 'iframe',
      id: 'downloadIframe',
      frameBorder: 0,
      width: 0,
      height: 0,
      css: 'display:none;visibility:hidden;height: 0px;',
      src: url
    });

  },

  getDateRange: function () {

    // _this = this;
    vm = this.getViewModel();

    startDate = this.getView().getReferences().startDate.getValue()
    endDate = this.getView().getReferences().endDate.getValue()

    if (startDate && endDate) {
      if (this.checkDateRangeExceedLimit(startDate, endDate)) {
        Ext.MessageBox.show({
          title: 'Filter Date',
          msg: 'Please select date range within 2 months',
          buttons: Ext.MessageBox.OK,
          icon: Ext.MessageBox.ERROR
        });

        return;
      }

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
        property: "transactiondate", type: "date", operator: "BETWEEN", value: [startDate, endDate]
      },
    )
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

  checkDateRangeExceedLimit: function (startDate, endDate) {

    // Do a daterange checker
    // If date exceeds 2 months, reject
    // Init date values
    var msecPerMinute = 1000 * 60;
    var msecPerHour = msecPerMinute * 60;
    var msecPerDay = msecPerHour * 24;

    // Calculate date interval 
    var interval = endDate - startDate;
    var intervalDays = Math.floor(interval / msecPerDay);


    // Get 2 months range limit for filter
    rangeLimit = 61;
    return intervalDays > rangeLimit;

  }

});
