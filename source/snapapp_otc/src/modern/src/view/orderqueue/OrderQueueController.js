Ext.define('snap.view.orderqueue.OrderQueueController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.orderqueue-orderqueue',



    /*onPreLoadViewDetail: function(record, displayCallback) {
    	snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'detailview', id: record.data.id, status_text: record.data.status_text,})
    	.then(function(data){
    		if(data.success) {
    			displayCallback(data.record);
    		}
    	})
        return false;
	}*/

    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'detailview', id: record.data.id})
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
        snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'detailviewmobile', id: info.record.data.id}, false, 'GET', true)
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
            bodyPadding: 0,
            modal: true,
            width: '95%',
            height: 550,
            closeAction: 'close',
            maximizable: true,
            plain: false,
            scrollable: 'vertical',
            
            html: this.formatDetails(data),
            buttons: [{
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
