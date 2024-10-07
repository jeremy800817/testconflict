
Ext.define('snap.store.NavigationTree', {
    extend: 'Ext.data.TreeStore',

    storeId: 'NavigationTree',

    fields: [{
        name: 'text'
    }],

    root: {
        expanded: false,
        children: [
            {
                text: 'Home',
                iconCls: 'fa fa-home',
                rowCls: 'nav-tree-badge nav-tree-badge-new',
                viewType: 'mob_home',
                routeId: 'home',
                leaf: true
            },
            /* {
                text: 'Profile',
                iconCls: 'x-fa fa-user',
                viewType: 'profile',
                leaf: true
            }, */
            {
                text: 'Orders',
                iconCls: 'fa fa-shopping-cart',
                expanded: false,
                selectable: false,
                children: [     
                    { text: 'Order Dashboard', iconCls: 'x-fa fa-desktop', leaf: true, viewType: 'orderdashboardview', id: 'core-orderdashboard', permission: '/root/trading/order/list' },
                    { text: 'Limits', iconCls: 'x-fa fa-cart-plus ', leaf: true, viewType: 'dailylimitview', id: 'core-dailylimit', permission: '/root/trading/order/list' },
                    { text: 'Tradebook', iconCls: 'x-fa fa-book ', leaf: true, viewType: 'tradebook', id: 'core-tradebook', permission: '/root/trading/order/list' },
                    //{ text: 'Transaction',iconCls: 'fa fa-file-pdf',viewType: 'summary',leaf: true,permission: '/root/trading/order/list' },                      
                ]
            },            
            {
                text: 'Unfullfill PO',
                iconCls: 'fa fa-clock',
                viewType: 'unfulfillpodashboardview',
                leaf: true,
                permission: '/root/trading/order/list'
            },
            {
                text: 'Trader',
                iconCls: 'fa fa-flask',
                viewType: 'trader',
                permission: '/root/system/trader/list',
                viewType: 'trader',
                leaf: true
            },
            {
                text: 'Logistics',
                iconCls: 'fa fa-truck',
                viewType: 'logistics',
                permission: '/root/system/logistic/list',
                //viewType: 'logisticview',
                leaf: true
            },
            {
                text: 'Terms & Conditions',
                iconCls: 'fa fa-newspaper',
                viewType: 'widgets',
                leaf: true
            },
            {
                text: 'About Us',
                iconCls: 'fa fa-info-circle',
                viewType: 'forms',
                leaf: true
            },
        ]
    }
});
