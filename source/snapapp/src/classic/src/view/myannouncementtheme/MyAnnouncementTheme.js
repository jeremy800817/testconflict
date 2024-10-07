//
Ext.define('snap.view.myannouncementtheme.MyAnnouncementTheme', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'myannouncementthemeview',

    requires: [
        'snap.store.MyAnnouncementTheme',
        'snap.model.MyAnnouncementTheme',
        'snap.view.myannouncementtheme.MyAnnouncementThemeController',
        'snap.view.myannouncementtheme.MyAnnouncementThemeModel'
    ],
    permissionRoot: '/root/system/myannouncementtheme',
    store: { type: 'MyAnnouncementTheme' },

    controller: 'myannouncementtheme-myannouncementtheme',

    viewModel: {
        type: 'myannouncementtheme-myannouncementtheme'
    },

    enableFilter: true,
    // gridSelectionModel:'checkboxmodel',
    sortableColumns: false,
    columns: [
        { text: 'ID', dataIndex: 'id', hidden: true, filter: { type: 'int' }, flex: 1 },
        { text: 'Name', dataIndex: 'name', filter: { type: 'string' }, flex: 1 },
        { text: 'Template', dataIndex: 'shorten_template', filter: { type: 'string' }, flex: 2 },
        { text: 'Start On', dataIndex: 'displaystarton', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 0 },
        { text: 'End On', dataIndex: 'displayendon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 0 },
        { text: 'Valid From', dataIndex: 'validfrom', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 0 },
        { text: 'Valid To', dataIndex: 'validto', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 0 },
        { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 0 },
        { text: 'Modified On', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y/m/d H:i:s', filter: { type: 'date' }, flex: 0 },
        { text: 'Created By', dataIndex: 'createdbyname', filter: { type: 'string' }, hidden: true, flex: 1 },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: { type: 'string' }, hidden: true, flex: 1 },
        {
            text: 'Status', dataIndex: 'status', flex: 1,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],
                    ['1', 'Active'],
                ],

            },
            renderer: function (value, rec) {
                if (value == '0') return 'Inactive';
                if (value == '1') return 'Active';
                else return 'Inactive';
            },
        },
    ],
    listeners: {
        afterrender: function (grid) {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns = this.query('gridcolumn');
            columns.find(obj => obj.text === 'ID').setVisible(false);
        }
    },

    // View properties settings
    enableDetailView: true,
    detailViewWindowHeight: 500,
    detailViewWindowWidth: 800,
    detailViewUseRawData: true,

    // Add/edit form settings
    formClass: 'snap.view.myannouncementtheme.MyAnnouncementThemeGridForm',
});
