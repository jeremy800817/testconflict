//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.UserType', {
    extend: 'Ext.data.ArrayStore',
    // autoLoad: true,

    model: 'snap.model.UserType',
    alias: 'store.UserType',
    data: [
        ['Operator', 'Operator'], 
        ['Trader', 'Trader'], 
        ['Customer', 'Customer'],
        ['Sale', 'Sale'], 
        ['Referral', 'Referral'],
        ['Agent', 'Agent']
    ]

});
