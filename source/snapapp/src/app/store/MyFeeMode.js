Ext.define('snap.store.MyFeeMode', {
    extend: 'Ext.data.Store',
    alias: 'store.MyFeeMode',   
    model: 'snap.model.MyFeeMode',
    storeId:'MyFeeMode', 
    data : [
        {code:'XAU', id:'XAU'},
        {code:'MYR', id:'MYR'},
    ]
});
