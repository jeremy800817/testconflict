Ext.define('snap.view.order.OrderController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.order-order',


    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'order', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },

    onRecord: function(grid, info) {
        _this = this;
        var tabPanel = grid;
        tabPanel.setMasked({
                   xtype: 'loadmask',
                   message: 'Loading...',
                   indicator:true
        });
        snap.getApplication().sendRequest({ hdl: 'order', action: 'detailviewmobile', id: info.record.data.id}, false, 'GET', true)
        .then(function(data){
            tabPanel.setMasked(false);
            if(data.success) {
                // displayCallback(data.record);
                _this.showDetails(data.record.default, info.record.data.id)
            }else{
                Ext.Msg.alert('Error', data.errorMessage);
            }
        })
        return false;
    },

    showDetails: function(data, orderid){
        var propWin = new Ext.Window({
            title: 'Details' + ' ...',
            //layout: 'fit',
            bodyPadding: 0,
            modal: true,
            width: '95%',
            height: 550,
            // padding: '0 15px',
            // margin: '0 15px',
            closeAction: 'close',
            // bodyPadding: 0,
            // bodyBorder: false,
            // bodyStyle: {
            //     background: '#FFFFFF'
            // },
            maximizable: true,
            plain: false,
            scrollable: 'vertical',
            
            html: this.formatDetails(data),
            buttons: [{
                text: 'Receipt',
                buttonAlign: 'left',
                handler: function() {
                    var url = 'index.php?hdl=order&action=printSpotOrder&orderid='+orderid;
                    var win = window.open('');
                        win.location = url;
                        win.focus();
                        return;
                    Ext.Ajax.request({
                        url: url,
                        method: 'get',
                        waitMsg: 'Processing',
                        //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
                        autoAbort: false,
                        success: function (result) {
                            var win = window.open('');
                                win.location = url;
                                win.focus();
                        },
                        failure: function () {
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: 'Failed to retrieve data',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                    });
                }
            },{
                text: 'Close',
                buttonAlign: 'right',
                handler: function() {
                    propWin.destroy();
                }
            }]
        });
        propWin.show();
    },

    formatDetails: function(data){
        table = `
        <table class="detailtable" style="width: 100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th colspan="1">Value</th>
                </tr>
            </thead>
            <tbody>
            `;
        for (const [key, value] of Object.entries(data)) {
            console.log(`${key}: ${value}`);
            table += `
                <tr>
                    <td>${key}</td>
                    <td>${value}</td>
                </tr>
            `;
        }
        table += `
            </tbody>
        </table>
        `;
        return table
    },

    formatDetailsMobile: function(data){
        table = `
        <table class="detailtable" style="width: 100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th colspan="1">Value</th>
                </tr>
            </thead>
            <tbody>
            `;
        for (const [key, value] of Object.entries(data)) {
            console.log(`${key}: ${value}`);
            table += `
                <tr>
                    <td>${key}</td>
                    <td>${value}</td>
                </tr>
            `;
        }
        table += `
            </tbody>
        </table>
        `;
        return table
    },
});
