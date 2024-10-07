/**
 * This class is the view model for the Main view of the application.
 **/
Ext.define('snap.view.main.MainModel', {
    extend: 'Ext.app.ViewModel',
    alias: 'viewmodel.main',
    data: {
        applogo: '<div class="main-logo"><img src="src/resources/images/logo_normal.png" style="height:50px; margin-left:4px; top:2px;"></div>',
        applogoNormal: '<div class="main-logo"><img src="src/resources/images/logo_normal.png" style="height:50px; margin-left:4px; top:2px;"></div>',
        applogoMicro: '<div class="main-logo"><img src="src/resources/images/logo_mini.png" style="height:50px; margin-left:4px; top:2px;"></div>',
        webtrail: '&nbsp;&nbsp;',
        redirectParam: null,
        overrideWebtrail: null,
        username: 'Who am I?',
        copyright: 'All Rights Reserved &copy; '+new Date().getFullYear()+' ACE Innovate Asia Berhad'
    },
    stores: {
        navItems: {
            type: 'tree',
            root: {
                expanded: true,
                children: []
            }
        }
    },
    menuItems: [        
        { text: 'Home', iconCls: 'x-fa fa-home', selectable: false,
            children: [
                { text: 'Announcement', iconCls: 'x-fa fa-newspaper', leaf: true, viewType: 'announcementhomeview' },
                { text: 'Terms & Conditions', iconCls: 'x-fa fa-newspaper', leaf: true, viewType: 'termsandconditionsview' },
                { text: 'About Us', iconCls: 'x-fa fa-newspaper', leaf: true, viewType: 'aboutusview'},
            ]
        },
        { text: 'GTP', iconCls: 'x-fa fa-shopping-cart', selectable: false,
            children: [  
                { text: 'Trade', iconCls: 'x-fa fa-desktop', leaf: true, viewType: 'orderdashboardview',permission: '/root/gtp/cust' },
                { text: 'Unfulfilled Order', iconCls: 'x-fa fa-ban', leaf: true, viewType: 'unfulfillpodashboardview', permission: '/root/gtp/unfulfilledorder/list' },    
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart ', leaf: true, viewType: 'orderview', permission: '/root/gtp/order/list' },
                { text: 'FO ACE Buy', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'orderqueuebuyview', permission: '/root/gtp/ftrorder/list' },
                { text: 'FO ACE Sell', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'orderqueuesellview', permission: '/root/gtp/ftrorder/list' },
                //{ text: 'Tradebook', iconCls: 'x-fa fa-book ', leaf: true, viewType: 'tradebook', id: 'core-tradebook', permission: '/root/gtp/order/list' },    
                //{ text: 'Unfullfill PO',iconCls: 'fa fa-clock',viewType: 'unfulfillpodashboardview',leaf: true,permission: '/root/trading/order/list'}, 
                // { text: 'Logistic',iconCls: 'fa fa-truck', viewType: 'logistics',permission: '/root/mbb/logistic/list',leaf: true },        
                { text: 'Limits', iconCls: 'x-fa fa-cart-plus ', leaf: true, viewType: 'dailylimitview', id: 'core-dailylimit', permission: '/root/gtp/limits' },
            ]
        },     
        { text: 'Price Management', iconCls: 'x-fa fa-book', selectable: false,
            children: [
                { text: 'Trader', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderview', permission: '/root/system/trader/list'  }, 
                { text: 'Trader Order', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderordersview', permission: '/root/system/trader/list'  },
                { text: 'Price Adjuster', iconCls: 'x-fa fa-book', leaf: true, viewType: 'priceadjusterview', permission: '/root/system/priceadjuster/list' },
                { text: 'Price Stream', iconCls: 'x-fa fa-stream', leaf: true, viewType: 'pricestreamview', permission: '/root/trading/pricestream/list' }, 
                { text: 'Price Provider', iconCls: 'x-fa fa-dollar-sign', leaf: true, viewType: 'priceproviderview', permission: '/root/system/priceprovider/list' },
                { text: 'Price Validation', iconCls: 'x-fa fa-user-check', leaf: true, viewType: 'PriceValidationView', permission: '/root/system/pricevalidation/list' },  
            ]
        },  
    ]
});
