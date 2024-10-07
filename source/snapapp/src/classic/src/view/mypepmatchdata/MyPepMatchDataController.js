Ext.define('snap.view.mypepmatchdata.MyPepMatchDataController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.mypepmatchdata-mypepmatchdata',


    printPEP: function(btn)  {
        var myView = this.getView();
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();     
        var record = selectedRecords[0].data;

        var url = 'index.php?hdl=mypepsearchresult&action=printPepPdf&personid=' + record.personid + '&accountholderid='+record.accountholderid;
        
        var mask = Ext.getBody().mask('Loading...');
        mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);

        Ext.Ajax.request({
            url: url,
            method: 'get',
            waitMsg: 'Processing',
            autoAbort: false,
            success: function (data) {
                Ext.getBody().unmask();
                var blob = new Blob([data.responseText], { type: 'application/pdf' });
                if (window.navigator && window.navigator.msSaveOrOpenBlob) {
                    window.navigator.msSaveOrOpenBlob(blob); // for IE
                } else {
                    var fileURL = URL.createObjectURL(blob);
                    var newWin = window.open(fileURL);
                    newWin.focus();
                }                
            },
            failure: function (error) {
                Ext.getBody().unmask();
                response = Ext.util.JSON.decode(error.responseText)
                errmsg   = response.errorMessage
                Ext.MessageBox.show({
                    title: 'Error',
                    msg: errmsg,
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        });
    }

});
