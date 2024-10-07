//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.UserActivityLog', {
    extend: 'snap.store.Base',
    model: 'snap.model.UserActivityLog',
    alias: 'store.useractivitylog',
    autoload: true,
    proxy: {
        type: 'ajax',
        url: 'index.php?hdl=useractivitylog&action=list&partnercode='+PROJECTBASE,
        reader: {
            type: 'json',
            rootProperty: 'records',
        }
    },
});
