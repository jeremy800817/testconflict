
Ext.define('snap.view.collection.SahabatCollection',{
    extend: 'snap.view.collection.SharedCollection',
    xtype: 'sahabatcollectionview',
    permissionRoot: '/root/sahabat/collection',
    //store: { type: 'Collection' },
    partnercode: 'SAHABAT',
    store:{
        type: 'Collection', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=collection&action=list&partnercode=SAHABAT',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    enableFilter: true,
   
});
