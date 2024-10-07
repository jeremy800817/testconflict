Ext.define('snap.store.MyFeeCalculationType', {
    extend: 'Ext.data.Store',
    alias: 'store.MyFeeCalculationType',   
    model: 'snap.model.MyFeeCalculationType',
    storeId:'MyFeeCalculationType', 
    data : [
        {code:'Fixed', id:'FIXED'},
        {code:'Float', id:'FLOAT'},
    ]
});
