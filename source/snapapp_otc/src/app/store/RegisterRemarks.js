//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.RegisterRemarks', {
    extend: 'snap.store.Base',
    model: 'snap.model.RegisterRemarks',
    alias: 'store.RegisterRemarks',
    autoload: true,
    proxy: {
        type: 'ajax',
        url: 'index.php?hdl=otcregisterremarkshandler&action=list&partnercode='+PROJECTBASE,
        reader: {
            type: 'json',
            rootProperty: 'records',
        }
    },
});
