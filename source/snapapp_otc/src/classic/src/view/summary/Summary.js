Ext.define("snap.view.summary.Summary", {
    extend: "Ext.grid.Panel",
    xtype: "summary",
    partnercode: PROJECTBASE,
    controller: "Summary",

    store: {
        type: "Summary",
        pageSize: 25,
        proxy: {
            type: 'ajax',
            url: 'index.php?hdl=summary&action=list&partnercode='+PROJECTBASE,
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
    },

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

    dockedItems: [{
            xtype: "pagingtoolbar",
            dock: "bottom",
            displayInfo: true,
        },
        {
            xtype: "toolbar",
            dock: "top",
            items: [{
                    xtype: "datefield",
                    fieldLabel: "Start Date",
                    name: "start_date",
                    reference: "startDate",
                    format: "d/m/Y",
                },
                {
                    xtype: "datefield",
                    fieldLabel: "End Date",
                    name: "end_date",
                    reference: "endDate",
                    format: "d/m/Y",
                },
                {
                    iconCls: 'x-fa fa-redo-alt',
                    xtype: "button",
                    text: "Filter Date",
                    handler: "getdatefilter",
                    handlerOptions: {
                        scope: this,
                        updateSummaryAlrajhi: true
                    }
                }, 
                {
                    iconCls: 'x-fa fa-times-circle', 
                    xtype: "button",
                    text: "Clear Date",
                    handler: "cleardatefilter",
                },
                {
                    iconCls: 'x-fa fa-download',
                    xtype: "button",
                    text: "Download",
                    handler: "getinventoryreport",
                },
            ],
        },
    ],

   
});