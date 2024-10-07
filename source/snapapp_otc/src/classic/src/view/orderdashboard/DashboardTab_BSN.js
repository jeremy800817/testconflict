Ext.define('snap.view.orderdashboard.DashboardTab_BSN',{
    extend: 'Ext.panel.Panel',
    xtype: 'dashboardtabview_BSN',
    permissionRoot: '/root/gtp/cust',
    //store: { type: 'Order' },
    store: 'orderPriceStream',	
    controller: 'orderdashboard-orderdashboard',
    // formDialogWidth: 950,
    layout: 'fit',
    // width: 500,
    // height: 400,
    // cls: Ext.baseCSSPrefix + 'shadow',

    //bodyPadding: 25,
    cls: 'otc-main',
    bodyCls: 'otc-main-body',
    items: [
        {
            xtype: 'tabpanel',
            width: '99%',
            // flex: 1,
            // reference: 'goldstatementtab',
            items: [
                    {
                        title: getText('dashboard'),
                        scrollable: true,
                        bodyCls: 'otc-main-tabpanel-body',
                        items: [
                            { width: '99%', reference: 'dashboard', ui: 'dashboardview',  xtype: 'dashboardview_'+PROJECTBASE, type: 'go'},
                        ]
                    },
                    {
                        title: getText('search'),
                        scrollable: true,
                        bodyCls: 'otc-main-tabpanel-body',
                        plugins: {
                            ptype: 'lazyitems',
                            items: [
                                { width: '99%', reference: 'orderdashboardview', ui: 'orderdashboardview',  xtype: 'orderdashboardview_'+PROJECTBASE, type: 'go'},
                            ]
                        }
                    },
                ]
            }
    ]


});
