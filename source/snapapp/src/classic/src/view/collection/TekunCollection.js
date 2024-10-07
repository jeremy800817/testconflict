
Ext.define('snap.view.collection.TekunCollection',{
    extend: 'snap.view.collection.SharedCollection',
    xtype: 'tekuncollectionview',
    permissionRoot: '/root/tekun/collection',
    //store: { type: 'Collection' },
    partnercode: 'TEKUN',
    store:{
        type: 'Collection', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=collection&action=list&partnercode=TEKUN',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    enableFilter: true,
   
});
