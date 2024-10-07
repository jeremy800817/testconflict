
Ext.define('snap.view.collection.KoponasCollection',{
    extend: 'snap.view.collection.SharedCollection',
    xtype: 'koponascollectionview',
    permissionRoot: '/root/koponas/collection',
    //store: { type: 'Collection' },
    partnercode: 'KOPONAS',
    store:{
        type: 'Collection', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=collection&action=list&partnercode=KOPONAS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },
    enableFilter: true,
   
});
