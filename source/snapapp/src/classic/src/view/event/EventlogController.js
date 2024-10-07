Ext.define('snap.view.event.EventlogController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.eventlog-eventlog',

    requires: [
        'snap.view.gridpanel.BaseController'
    ],
    
    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'eventlog', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    }

});
