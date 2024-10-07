Ext.define('snap.reader.Default',{
    extend: 'Ext.data.reader.Json',
    alias: 'reader.snapdefault',
    config: {
        idProperty: 'id',
        rootProperty: 'records',
        totalProperty: 'totalRecords'
    }
});

Ext.define('snap.store.Base', {
    extend: 'Ext.data.Store',
    pageSize: 50,
    remoteSort: true,
    autoLoad: false,
    listeners: {
        exception: function(proxy, response, operation) {
            Ext.Msg.show({ title:'Error!', msg: response.responseText,
            icon: Ext.Msg.ERROR, buttons: Ext.Msg.OK});
        },
        beforeload: function(store, operation, eOpts) { 
        	//Added by Devon on 2017/5/12 as a hack to fix unable to load reader properly when defined in schema of the snap.model.Base. 
        	if(!store.hasSetReader){
        		store.hasSetReader = 1;
        		store.getProxy().setReader({type : 'snapdefault' });
        	}
            if (USERACTIVITYLOG) {
                store.getProxy().setExtraParams({otcuseractivitylog : true});
            }
        	//End Add by Devon on 2017/5/12
        }
    }

});
