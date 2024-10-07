Ext.define('snap.view.mypepmatchdata.MyPepMatchData', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'mypepmatchdataview',
    reference: 'mypepmatchdataview',
    requires: [
        'snap.store.MyPepMatchData',
        'snap.model.MyPepMatchData',
        'snap.view.mypepmatchdata.MyPepMatchDataController',
        'snap.view.mypepmatchdata.MyPepMatchDataModel'
    ],
    permissionRoot: '/root/bmmb/approval',
    store: { type: 'MyPepMatchData' },
    controller: 'mypepmatchdata-mypepmatchdata',

    viewModel: {
        type: 'mypepmatchdata-mypepmatchdata'
    },

    detailViewWindowHeight: 400,

    enableFilter: true,

    rowexpander: {
        ptype: 'rowexpander',
        rowBodyTpl: '{detail}'
    },


    toolbarItems: [
        'filter',
        { reference: 'printPEP', text: 'Print PEP', itemId: 'printPEP', tooltip: 'Print PEP', iconCls: 'x-fa fa-print', handler: 'printPEP', validSelection: 'single' }
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'score',
                direction: 'DESC'
            }]);
        }
    },
    columns: [
        { text: 'Score', dataIndex: 'score', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Name', dataIndex: 'name', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'Date of Birth', dataIndex: 'dateofbirth', renderer: Ext.util.Format.dateRenderer('d M Y'), filter: { type: 'string' }, minWidth: 130, flex: 1 },
        {
            text: 'Is PEP', xtype: 'booleancolumn',
            trueText: 'Yes',
            falseText: 'No', dataIndex: 'ispep', minWidth: 130, flex: 1
        },
        { text: 'PEP Level', dataIndex: 'peplevel', minWidth: 130, flex: 1 },
    ],
    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    // enableDetailView: true,
    // detailViewWindowHeight: 500,
    // detailViewWindowWidth: 500,
    // style: 'word-wrap: normal',
    // detailViewSections: { default: 'Properties' },
    // detailViewUseRawData: true,

    formConfig: {

    },

});
