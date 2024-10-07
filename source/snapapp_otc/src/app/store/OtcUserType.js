//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.OtcUserType', {
    extend: 'Ext.data.ArrayStore',
    // autoLoad: true,

    model: 'snap.model.UserType',
    alias: 'store.OtcUserType',
    data: [
        ['Branch', 'Branch'], 
        ['HQ', 'HQ'], 
        ['Regional', 'Regional'],
    ]

});
