Ext.define('snap.view.vaultitem.MyVaultItemBorderListMcash', {
    extend:'snap.view.vaultitem.MyVaultItemBorderList',
    permissionRoot: '/root/mcash/vault',
    xtype: 'mymcashvaultitem-border', 
    type: 'mcash',
    partnerCode : 'MCASH',
});