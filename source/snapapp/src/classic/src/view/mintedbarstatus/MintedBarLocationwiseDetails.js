Ext.define('snap.view.mintedbarstatus.MintedBarLocationwiseDetails', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mintedbarlocationwise',
    requires: [
        'snap.store.Logistic',
        'snap.model.Logistic',
        'snap.view.logistics.LogisticController',
        'snap.view.logistics.LogisticModel',
        'Ext.ProgressBarWidget',
    ],
    height: 390,
    permissionRoot: '/root/system/mintedbar',
    controller: 'mintedbarstatus-mintedbarstatus',
    viewModel: {
        type: 'mintedbarstatus-mintedbarstatus',
    },
    store: { type: 'MintedGoldbarInventory' },
    enableFilter: true,
    toolbarItems: ['filter'],
    disableSelection: true,
    listeners: {
        afterrender: function (grid) {
            //grid.getView().headerCt.getGridColumns()[0].hide();
            this.store.sorters.clear();
            grid.headerCt.items.getAt(0).hide();
        }
        
    },
    columns: [
        { text: 'Store/Bin', flex: 2, align: 'center',dataIndex: 'bin' },
        { text: 'Branch Name', flex: 2, align: 'center',dataIndex: 'branch' },
        { text: '0.5 Grams', flex: 1, align: 'center',dataIndex: '1_gram' },
        { text: '1 Grams', flex: 1, align: 'center',dataIndex: '1_gram' },
        { text: '5 Grams', flex: 1, align: 'center',dataIndex: '5_gram' },
        { text: '10 Grams', flex: 1, align: 'center',dataIndex: '10_gram' },
        { text: '50 Grams', flex: 1, align: 'center',dataIndex: '50_gram' },
        { text: '100 Grams', flex: 1, align: 'center',dataIndex: '100_gram' },
        { text: '1000 Grams', flex: 1, align: 'center',dataIndex: '1000_gram' },
        { text: 'Total Grams', flex: 1, align: 'center',dataIndex: 'total' },
    ],
});



