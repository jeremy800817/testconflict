//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.ManagementFeeDeduction', {
    extend: 'snap.store.Base',
    model: 'snap.model.ManagementFeeDeduction',
    alias: 'store.ManagementFeeDeduction',
    autoload: true,
    proxy: {
        type: 'ajax',
        url: 'index.php?hdl=otcoutstandingstoragefeejob&action=list&partnercode='+PROJECTBASE,
        reader: {
            type: 'json',
            rootProperty: 'records',
        }
    },
});
