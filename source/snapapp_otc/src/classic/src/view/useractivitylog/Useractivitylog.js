Ext.define("snap.view.useractivitylog.Useractivitylog", {
    extend: "snap.view.gridpanel.Base",
    xtype: "userlogsview",

    requires: [
        'snap.store.UserActivityLog',
        'snap.model.UserActivityLog',
        'snap.view.useractivitylog.UseractivitylogController',
        'snap.view.useractivitylog.UseractivitylogModel',
    ],
    //permissionRoot: '/root/bmmb/profile',
    store: { type: 'useractivitylog' },
    controller: 'useractivitylog-useractivitylog',

    viewModel: {
        type: 'useractivitylog-useractivitylog'
    },
    detailViewWindowHeight: 500,

    enableFilter: true,
    toolbarItems: [
        'filter',
    ],

    columns: [
        { text: 'ID', dataIndex: 'id', hidden: true, filter: { type: 'string' }, minWidth: 50, flex: 1 },
        { text: 'USR ID', dataIndex: 'usrid', hidden: true, filter: { type: 'string' }, maxWidth: 100, flex: 1},
        { text: 'User Name', dataIndex: 'username', exportdecimal:2, filter: { type: 'string' }, maxWidth: 100, flex: 1},
        { text: 'Module', dataIndex: 'module', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Action', dataIndex: 'action', filter: { type: 'string' }, minWidth: 100, flex: 1 },
        { text: 'Activity Detail', dataIndex: 'activitydetail', filter: { type: 'string' }, minWidth: 130, flex: 1 },
        { text: 'IP Address', dataIndex: 'ip', filter: { type: 'string' }, maxWidth: 130, flex: 1 },
        { text: 'Browser', dataIndex: 'browser', filter: { type: 'string' }, minWidth: 300, flex: 1 },
        { text: 'Activity Time', dataIndex: 'activitytime', filter: { type: 'string' }, minWidth: 130, flex: 1 },
    ],
});
