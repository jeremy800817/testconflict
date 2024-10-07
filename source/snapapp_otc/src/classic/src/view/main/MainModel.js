/**
 * This class is the view model for the Main view of the application.
 **/
Ext.define('snap.view.main.MainModel', {
    extend: 'Ext.app.ViewModel',

    alias: 'viewmodel.main',

    data: {
        applogo: '<div class="main-logo"><img src="src/resources/images/logo_normal.png"></div>',
        applogoNormal: '<div class="main-logo"><img src="src/resources/images/logo_normal.png" style="height:50px; margin-left:4px; top:2px;"></div>',
        applogoMicro: '<div class="main-logo"><img src="src/resources/images/logo_mini.png" style="height:50px; margin-left:4px; top:2px;"></div>',

        webtrail: '&nbsp;&nbsp;',

        redirectParam: null,

		overrideWebtrail: null,

        username: 'Who am I?',

        copyright: 'All Rights Reserved &copy; 2021 ACE Innovate Asia Berhad',

        version: '1.0.1',

        devcss: '',
        // devcss: '<link rel="stylesheet" href="src/resources/css/devcss.css">',
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
                { text: 'Terms & Conditions', iconCls: 'x-fa fa-gavel', leaf: true, viewType: 'termsandconditionsview' },
                { text: 'About Us', iconCls: 'x-fa fa-info-circle', leaf: true, viewType: 'aboutusview'},
                { text: 'Help', iconCls: 'x-fa fa-question-circle', leaf: true, viewType: 'helpview'},
            ]
        },
    	{ text: 'GTP', iconCls: 'x-fa fa-shopping-cart', selectable: false,
            children: [
                //{ text: 'Order', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'orderview', id: 'coreorder', permission: '/root/trading/order/list' },
                { text: 'Trade', iconCls: 'x-fa fa-desktop', leaf: true, viewType: 'orderdashboardview', permission: '/root/gtp/cust' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'salesview', permission: '/root/gtp/sale' },
                { text: 'Unfulfilled Order', iconCls: 'x-fa fa-ban', leaf: true, viewType: 'unfulfillpodashboardview', permission: '/root/gtp/unfulfilledorder/list' },
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart ', leaf: true, viewType: 'gtporderview', permission: '/root/gtp/order/list' },
                //{ text: 'Future Orders', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'orderqueueview', permission: '/root/gtp/ftrorder/list' },
                { text: 'FO ACE Buy', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'orderqueuebuyview', permission: '/root/gtp/ftrorder/list' },
                { text: 'FO ACE Sell', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'orderqueuesellview', permission: '/root/gtp/ftrorder/list' },
                { text: 'Limits', iconCls: 'x-fa fa-cart-plus ', leaf: true, viewType: 'dailylimitview', permission: '/root/gtp/limits' },
                { text: 'Product Limits', iconCls: 'x-fa fa-shopping-cart ', leaf: true, viewType: 'partnerdailylimitview', permission: '/root/gtp/partnerlimits' },
                { text: 'Collection', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'collectionview', permission: '/root/gtp/collection/list'  },
                //{ text: 'Cancelled Orders', iconCls: 'x-fa fa-times', leaf: true, viewType: 'ordercancelview', id: 'core-cancellimits', permission: '/root/trading/futureordercancel/list' },
                //{ text: 'Tradebook', iconCls: 'x-fa fa-book', leaf: true, viewType: 'pageblank', id: 'core-tradebook', permission: '/root/branch/patient/list' },
                //{ text: 'Unfulfilled Order', iconCls: 'x-fa fa-ban', leaf: true, viewType: 'pageblank', id: 'core-unfulfilledorder', permission: '/root/branch/patient/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'gtplogisticview', permission: '/root/gtp/logistic/list' },
                /*{ text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        //{ text: 'Sales', iconCls: 'x-fa fa-money-bill-alt', leaf: true, viewType: 'sponsorshipreportview', permission: '/root/system/announcement/list' }
                        { text: 'Commission', iconCls: 'x-fa fa-money-bill-alt', leaf: true, viewType: 'commissionview', permission: '/root/gtp/salescommission/list' },
                        //{ text: 'Audit Trail', iconCls: 'x-fa fa-money-bill-alt', leaf: true, viewType: 'gtp-auditview', permission: '/root/gtp/audit/list' }
                    ]
                },*/

            ]
        },
        { text: 'MIGA', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'miborderview', permission: '/root/mbb/order/list' },
                //{ text: 'Future Orders', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'miborderqueueview', permission: '/root/mbb/ftrorder/list' },
                { text: 'FO ACE Buy', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'miborderqueuebuyview', permission: '/root/mbb/ftrorder/list' },
                { text: 'FO ACE Sell', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'miborderqueuesellview', permission: '/root/mbb/ftrorder/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                { text: 'Gold Bar Inventory', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'goldbarstatusview', permission: '/root/mbb/goldbarstatus/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialmibview', permission: '/root/mbb/sale' },
                { text: 'Redemption', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'redemptionview', permission: '/root/mbb/redemption/list' },
                { text: 'Buyback', iconCls: 'x-fa fa-shopping-basket', leaf: true, viewType: 'buybackview', permission: '/root/mbb/buyback/list' },
                { text: 'Replenishment', iconCls: 'x-fa fa-shopping-basket', leaf: true, viewType: 'replenishmentview', permission: '/root/mbb/buyback/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'miblogisticview', permission: '/root/mbb/logistic/list' },
                // { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'mibvaultitem-border', permission: '/root/mbb/vault/list'},
                { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'mibvaultitem-border-new', permission: '/root/mbb/vault/list'},
                // { text: 'Inventory', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'inventoryview', permission: '/root/mbb/vault/list'},
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        //{ text: 'Sales', iconCls: 'x-fa fa-money-bill-alt', leaf: true, viewType: 'sponsorshipreportview', permission: '/root/system/announcement/list' }
                        { text: 'A&P Pool', iconCls: 'x-fa fa-money-bill-alt', leaf: true, viewType: 'anpview', permission: '/root/mbb/anppool/list' },
                        //{ text: 'Audit Trail', iconCls: 'x-fa fa-money-bill-alt', leaf: true, viewType: 'mib-auditview', permission: '/root/trading/mbb/audittrail/list' }
                    ]
                },
            ]
        },
        { text: 'EASIGOLD', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'bmmborderview', permission: '/root/bmmb/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'bmmbpricealertview', permission: '/root/bmmb/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/bmmb/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'bmmbconversionview', permission: '/root/bmmb/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'bmmblogisticview', permission: '/root/bmmb/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderbmmbview', permission: '/root/bmmb/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mybmmbaccountclosureview', permission: '/root/bmmb/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mybmmbcifview', permission: '/root/bmmb/profile/list', },
                { text: 'Kilobar Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'mybmmbvaultitem-border', permission: '/root/bmmb/vault/list'},
                { text: 'Minted Vault', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mintedbarstatusbmmbview', permission: '/root/bmmb/mintedbar/list' },

                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/bmmb/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/bmmb/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/bmmb/fpx/list' },
                
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mypushnotificationview', permission: '/root/go/pushnotification/list' },
                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/bmmb/profile/list' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/bmmb/goldtransaction/list' },
                //{ text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'mydocumentationview', permission: '/root/bmmb/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        // { text: 'Buy & Sell Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mybmmbcommissionview', permission: '/root/bmmb/report/commission/list' },
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mybmmbcommissionview', permission: '/root/bmmb/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mybmmbcommissionnonpeakview', permission: '/root/bmmb/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mybmmbdailystoragefeeview', permission: '/root/bmmb/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mybmmbmonthlystoragefeeview', permission: '/root/bmmb/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mybmmbmonthlysummaryview', permission: '/root/bmmb/report/monthlysummary/list' },
                        { text: 'Failed Registration', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'mybmmbfailedregistrationview', permission: '/root/bmmb/report/registration/list' },
                    ]
                },
            ]
        },
        { text: 'POS', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Buyback', iconCls: 'x-fa fa-shopping-basket', leaf: true, viewType: 'posbuybackview', permission: '/root/pos/buyback/list' },
                { text: 'Tender / Auction', iconCls: 'x-fa fa-shopping-basket', leaf: true, viewType: 'postenderview', permission: '/root/pos/tender/list' },
                { text: 'Collection / GRN', iconCls: 'x-fa fa-shopping-basket', leaf: true, viewType: 'poscollectionview', permission: '/root/pos/collection/list' },
            ]
        },
        { text: 'GOGOLD', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'goorderview', permission: '/root/go/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'gopricealertview', permission: '/root/go/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/go/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'goconversionview', permission: '/root/go/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'gologisticview', permission: '/root/go/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholdergoview', permission: '/root/go/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mygoaccountclosureview', permission: '/root/go/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mygocifview', permission: '/root/go/profile/list', },
                // { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'mygovaultitem-border', permission: '/root/go/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/bmmb/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/bmmb/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/bmmb/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mygopushnotificationview', permission: '/root/go/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/bmmb/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldgoview', permission: '/root/go/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgergoview', permission: '/root/go/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialgoview', permission: '/root/go/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/bmmb/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'godocumentationview', permission: '/root/go/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mygocommissionview', permission: '/root/go/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mygocommissionnonpeakview', permission: '/root/go/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mygodailystoragefeeview', permission: '/root/go/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mygomonthlystoragefeeview', permission: '/root/go/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mygomonthlysummaryview', permission: '/root/go/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'ONECENT', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'oneorderview', permission: '/root/one/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'onepricealertview', permission: '/root/one/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/one/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'oneconversionview', permission: '/root/one/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'onelogisticview', permission: '/root/one/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderoneview', permission: '/root/one/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'myoneaccountclosureview', permission: '/root/one/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'myonecifview', permission: '/root/one/profile/list', },
                // { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myonevaultitem-border', permission: '/root/one/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/one/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/one/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/one/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'myonepushnotificationview', permission: '/root/one/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/one/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldoneview', permission: '/root/one/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgeroneview', permission: '/root/one/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialoneview', permission: '/root/one/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/one/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'onedocumentationview', permission: '/root/one/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myonecommissionview', permission: '/root/one/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myonecommissionnonpeakview', permission: '/root/one/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'myonedailystoragefeeview', permission: '/root/one/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'myonemonthlystoragefeeview', permission: '/root/one/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'myonemonthlysummaryview', permission: '/root/one/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'ONECALL', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'onecallorderview', permission: '/root/onecall/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'onecallpricealertview', permission: '/root/onecall/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/one/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'onecallconversionview', permission: '/root/onecall/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'onecalllogisticview', permission: '/root/onecall/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderonecallview', permission: '/root/onecall/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'myonecallaccountclosureview', permission: '/root/onecall/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'myonecallcifview', permission: '/root/onecall/profile/list', },
                // { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myonecallvaultitem-border', permission: '/root/onecall/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/one/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/one/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/one/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'myonepushnotificationview', permission: '/root/one/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/one/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldonecallview', permission: '/root/onecall/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgeronecallview', permission: '/root/onecall/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialonecallview', permission: '/root/onecall/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/one/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'onedocumentationview', permission: '/root/one/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myonecallcommissionview', permission: '/root/onecall/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myonecallcommissionnonpeakview', permission: '/root/onecall/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'myonecalldailystoragefeeview', permission: '/root/onecall/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'myonecallmonthlystoragefeeview', permission: '/root/onecall/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'myonecallmonthlysummaryview', permission: '/root/onecall/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        // { text: 'AIRGOLD', iconCls: 'x-fa fa-university', selectable: false,
        //     children: [
        //         { text: 'Order', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'airorderview', permission: '/root/air/goldtransaction/list' },
        //         { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'airpricealertview', permission: '/root/air/pricealert/list' },
        //         //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
        //         //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/one/disbursement/list' },
        //         { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'airconversionview', permission: '/root/air/redemption/list' },
        //         { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'airlogisticview', permission: '/root/air/logistic/list' },
        //         { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderairview', permission: '/root/air/profile/list' },
        //         { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'myairaccountclosureview', permission: '/root/air/accountclosure/list' },
        //         { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'myaircifview', permission: '/root/air/profile/list', },
        //         { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myairvaultitem-border', permission: '/root/air/vault/list'},
        //         //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/one/goldbarstatus/list' },
        //         // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/one/approval/list' },
        //         //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/one/fpx/list' },
        //         //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'myonepushnotificationview', permission: '/root/one/pushnotification/list' },

        //         //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/one/profile/list' },
        //         { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialairview', permission: '/root/air/sale' },
        //         {
        //             text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
        //             children: [
                        
        //             ]
        //         },
        //         // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/one/goldtransaction/list' },
        //         // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'onedocumentationview', permission: '/root/one/documentation/list' },               
        //         { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
        //             children:[
        //                 { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myaircommissionview', permission: '/root/air/report/commission/list' },
        //                 { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myaircommissionnonpeakview', permission: '/root/air/report/commission/list' },
        //                 { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'myairdailystoragefeeview', permission: '/root/air/report/storagefee/list' },
        //                 { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'myairmonthlystoragefeeview', permission: '/root/air/report/storagefee/list' },
        //                 { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'myairmonthlysummaryview', permission: '/root/air/report/monthlysummary/list' },
        //             ]
        //         },
        //     ]
        // },
        { text: 'MGOLD', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'mcashorderview', permission: '/root/mcash/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'mcashpricealertview', permission: '/root/mcash/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/one/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'mcashconversionview', permission: '/root/mcash/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mcashlogisticview', permission: '/root/mcash/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholdermcashview', permission: '/root/mcash/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mymcashaccountclosureview', permission: '/root/mcash/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mymcashcifview', permission: '/root/mcash/profile/list', },
                // { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'mymcashvaultitem-border', permission: '/root/mcash/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/one/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/one/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/one/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'myonepushnotificationview', permission: '/root/one/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/one/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldmcashview', permission: '/root/mcash/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgermcashview', permission: '/root/mcash/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialmcashview', permission: '/root/mcash/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/one/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'onedocumentationview', permission: '/root/one/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mymcashcommissionview', permission: '/root/mcash/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mymcashcommissionnonpeakview', permission: '/root/mcash/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mymcashdailystoragefeeview', permission: '/root/mcash/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mymcashmonthlystoragefeeview', permission: '/root/mcash/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mymcashmonthlysummaryview', permission: '/root/mcash/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'TOYYIB', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Order', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'toyyiborderview', permission: '/root/toyyib/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'toyyibpricealertview', permission: '/root/toyyib/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/toyyib/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'toyyibconversionview', permission: '/root/toyyib/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'toyyiblogisticview', permission: '/root/toyyib/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholdertoyyibview', permission: '/root/toyyib/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mytoyyibaccountclosureview', permission: '/root/toyyib/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mytoyyibcifview', permission: '/root/toyyib/profile/list', },
                // { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myonevaultitem-border', permission: '/root/toyyib/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/toyyib/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/toyyib/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/toyyib/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'myonepushnotificationview', permission: '/root/toyyib/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/toyyib/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldtoyyibview', permission: '/root/toyyib/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgertoyyibview', permission: '/root/toyyib/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialtoyyibview', permission: '/root/toyyib/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/toyyib/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'onedocumentationview', permission: '/root/toyyib/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mytoyyibcommissionview', permission: '/root/toyyib/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mytoyyibcommissionnonpeakview', permission: '/root/toyyib/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mytoyyibdailystoragefeeview', permission: '/root/toyyib/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mytoyyibmonthlystoragefeeview', permission: '/root/toyyib/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mytoyyibmonthlysummaryview', permission: '/root/toyyib/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'HOPE', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Order', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'hopeorderview', permission: '/root/hope/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'hopepricealertview', permission: '/root/hope/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/hope/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'hopeconversionview', permission: '/root/hope/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'hopelogisticview', permission: '/root/hope/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderhopeview', permission: '/root/hope/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'myhopeaccountclosureview', permission: '/root/hope/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'myhopecifview', permission: '/root/hope/profile/list', },
                // { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myonevaultitem-border', permission: '/root/hope/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/hope/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/hope/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/hope/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'myonepushnotificationview', permission: '/root/hope/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/hope/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldhopeview', permission: '/root/hope/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerhopeview', permission: '/root/hope/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialhopeview', permission: '/root/hope/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/hope/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'onedocumentationview', permission: '/root/hope/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myhopecommissionview', permission: '/root/hope/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myhopecommissionnonpeakview', permission: '/root/hope/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'myhopedailystoragefeeview', permission: '/root/hope/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'myhopemonthlystoragefeeview', permission: '/root/hope/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'myhopemonthlysummaryview', permission: '/root/hope/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'REDGOLD', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Order', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'redorderview', permission: '/root/red/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'redpricealertview', permission: '/root/red/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/red/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'redconversionview', permission: '/root/red/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'redlogisticview', permission: '/root/red/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderredview', permission: '/root/red/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'myredaccountclosureview', permission: '/root/red/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'myredcifview', permission: '/root/red/profile/list', },
                // { text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myonevaultitem-border', permission: '/root/red/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/red/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/red/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/red/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'myonepushnotificationview', permission: '/root/red/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/hope/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldredview', permission: '/root/red/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerredview', permission: '/root/red/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialredview', permission: '/root/red/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/red/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'onedocumentationview', permission: '/root/red/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myredcommissionview', permission: '/root/red/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myredcommissionnonpeakview', permission: '/root/red/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'myreddailystoragefeeview', permission: '/root/red/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'myredmonthlystoragefeeview', permission: '/root/red/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'myredmonthlysummaryview', permission: '/root/red/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'PITIH EMAS', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'ktporderview', permission: '/root/ktp/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'ktppricealertview', permission: '/root/ktp/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/go/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'ktpconversionview', permission: '/root/ktp/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'ktplogisticview', permission: '/root/ktp/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderktpview', permission: '/root/ktp/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'myktpaccountclosureview', permission: '/root/ktp/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'myktpcifview', permission: '/root/ktp/profile/list', },
                //{ text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myktpvaultitem-border', permission: '/root/ktp/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/bmmb/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/bmmb/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/bmmb/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mygopushnotificationview', permission: '/root/go/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/bmmb/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldktpview', permission: '/root/ktp/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerktpview', permission: '/root/ktp/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialktpview', permission: '/root/ktp/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/bmmb/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'godocumentationview', permission: '/root/go/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myktpcommissionview', permission: '/root/ktp/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'myktpcommissionnonpeakview', permission: '/root/ktp/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'myktpdailystoragefeeview', permission: '/root/ktp/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'myktpmonthlystoragefeeview', permission: '/root/ktp/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'myktpmonthlysummaryview', permission: '/root/ktp/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'PITIH EMAS AFFILIATE', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'pkbaffiorderview', permission: '/root/pkbaffi/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'pkbaffipricealertview', permission: '/root/pkbaffi/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/go/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'pkbafficonversionview', permission: '/root/pkbaffi/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'pkbaffilogisticview', permission: '/root/pkbaffi/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderpkbaffiview', permission: '/root/pkbaffipkbaffi/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mypkbaffiaccountclosureview', permission: '/root/pkbaffi/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mypkbafficifview', permission: '/root/pkbaffi/profile/list', },
                //{ text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myktpvaultitem-border', permission: '/root/ktp/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/bmmb/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/bmmb/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/bmmb/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mygopushnotificationview', permission: '/root/go/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/bmmb/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldpkbaffiview', permission: '/root/pkbaffi/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerpkbaffiview', permission: '/root/pkbaffi/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialpkbaffiview', permission: '/root/pkbaffi/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/bmmb/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'godocumentationview', permission: '/root/go/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mypkbafficommissionview', permission: '/root/pkbaffi/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mypkbafficommissionnonpeakview', permission: '/root/pkbaffi/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mypkbaffidailystoragefeeview', permission: '/root/pkbaffi/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mypkbaffimonthlystoragefeeview', permission: '/root/pkbaffi/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mypkbaffimonthlysummaryview', permission: '/root/pkbaffi/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'KOPETRO', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'kopetroorderview', permission: '/root/kopetro/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'kopetropricealertview', permission: '/root/kopetro/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/go/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'kopetroconversionview', permission: '/root/kopetro/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'kopetrologisticview', permission: '/root/kopetro/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderkopetroview', permission: '/root/kopetro/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mykopetroaccountclosureview', permission: '/root/kopetro/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mykopetrocifview', permission: '/root/kopetro/profile/list', },
                //{ text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myktpvaultitem-border', permission: '/root/ktp/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/bmmb/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/bmmb/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/bmmb/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mygopushnotificationview', permission: '/root/go/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/bmmb/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldkopetroview', permission: '/root/kopetro/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerkopetroview', permission: '/root/kopetro/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialkopetroview', permission: '/root/kopetro/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/bmmb/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'godocumentationview', permission: '/root/go/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykopetrocommissionview', permission: '/root/kopetro/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykopetrocommissionnonpeakview', permission: '/root/kopetro/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mykopetrodailystoragefeeview', permission: '/root/kopetro/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mykopetromonthlystoragefeeview', permission: '/root/kopetro/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mykopetromonthlysummaryview', permission: '/root/kopetro/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'KOPERASI TENTERA', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'kopttrorderview', permission: '/root/kopttr/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'kopttrpricealertview', permission: '/root/kopttr/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/go/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'kopttrconversionview', permission: '/root/kopttr/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'kopttrlogisticview', permission: '/root/kopttr/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderkopttrview', permission: '/root/kopttr/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mykopttraccountclosureview', permission: '/root/kopttr/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mykopttrcifview', permission: '/root/kopttr/profile/list', },
                //{ text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myktpvaultitem-border', permission: '/root/ktp/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/bmmb/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/bmmb/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/bmmb/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mygopushnotificationview', permission: '/root/go/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/bmmb/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldkopttrview', permission: '/root/kopttr/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerkopttrview', permission: '/root/kopttr/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialkopttrview', permission: '/root/kopttr/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/bmmb/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'godocumentationview', permission: '/root/go/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykopttrcommissionview', permission: '/root/kopttr/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykopttrcommissionnonpeakview', permission: '/root/kopttr/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mykopttrdailystoragefeeview', permission: '/root/kopttr/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mykopttrmonthlystoragefeeview', permission: '/root/kopttr/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mykopttrmonthlysummaryview', permission: '/root/kopttr/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'BUMIRA', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'bumiraorderview', permission: '/root/bumira/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'bumirapricealertview', permission: '/root/bumira/pricealert/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'bumiraconversionview', permission: '/root/bumira/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'bumiralogisticview', permission: '/root/bumira/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderbumiraview', permission: '/root/bumira/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mybumiraaccountclosureview', permission: '/root/bumira/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mybumiracifview', permission: '/root/bumira/profile/list', },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldbumiraview', permission: '/root/bumira/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerbumiraview', permission: '/root/bumira/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialbumiraview', permission: '/root/bumira/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mybumiracommissionview', permission: '/root/bumira/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mybumiracommissionnonpeakview', permission: '/root/bumira/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mybumiradailystoragefeeview', permission: '/root/bumira/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mybumiramonthlystoragefeeview', permission: '/root/bumira/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mybumiramonthlysummaryview', permission: '/root/bumira/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'NUSAGOLD', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'nubexorderview', permission: '/root/nubex/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'nubexpricealertview', permission: '/root/nubex/pricealert/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'nubexconversionview', permission: '/root/nubex/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'nubexlogisticview', permission: '/root/nubex/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholdernubexview', permission: '/root/nubex/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mynubexaccountclosureview', permission: '/root/nubex/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mynubexcifview', permission: '/root/nubex/profile/list', },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldnubexview', permission: '/root/nubex/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgernubexview', permission: '/root/nubex/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialnubexview', permission: '/root/nubex/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                }, 
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mynubexcommissionview', permission: '/root/nubex/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mynubexcommissionnonpeakview', permission: '/root/nubex/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mynubexdailystoragefeeview', permission: '/root/nubex/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mynubexmonthlystoragefeeview', permission: '/root/nubex/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mynubexmonthlysummaryview', permission: '/root/nubex/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'WAVPAYGOLD', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'wavpayorderview', permission: '/root/wavpay/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'wavpaypricealertview', permission: '/root/wavpay/pricealert/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'wavpayconversionview', permission: '/root/wavpay/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'wavpaylogisticview', permission: '/root/wavpay/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderwavpayview', permission: '/root/wavpay/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mywavpayaccountclosureview', permission: '/root/wavpay/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mywavpaycifview', permission: '/root/wavpay/profile/list', },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldwavpayview', permission: '/root/wavpay/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerwavpayview', permission: '/root/wavpay/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialwavpayview', permission: '/root/wavpay/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                }, 
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mywavpaycommissionview', permission: '/root/wavpay/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mywavpaycommissionnonpeakview', permission: '/root/wavpay/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mywavpaydailystoragefeeview', permission: '/root/wavpay/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mywavpaymonthlystoragefeeview', permission: '/root/wavpay/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mywavpaymonthlysummaryview', permission: '/root/wavpay/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        // { text: 'M-PrimeGold', iconCls: 'x-fa fa-university', selectable: false,
        //     children: [
        //         { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'mbsborderview', permission: '/root/mbsb/goldtransaction/list' },
        //         { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'mbsbpricealertview', permission: '/root/mbsb/pricealert/list' },
        //         { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'mbsbconversionview', permission: '/root/mbsb/redemption/list' },
        //         { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mbsblogisticview', permission: '/root/mbsb/logistic/list' },
        //         { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholdermbsbview', permission: '/root/mbsb/profile/list' },
        //         { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mymbsbaccountclosureview', permission: '/root/mbsb/accountclosure/list' },
        //         { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mymbsbcifview', permission: '/root/mbsb/profile/list', },
        //         { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialmbsbview', permission: '/root/mbsb/sale' },
        //         {
        //             text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
        //             children: [
                        
        //             ]
        //         }, 
        //         { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
        //             children:[
        //                 { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mymbsbcommissionview', permission: '/root/mbsb/report/commission/list' },
        //                 { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mymbsbcommissionnonpeakview', permission: '/root/mbsb/report/commission/list' },
        //                 { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mymbsbdailystoragefeeview', permission: '/root/mbsb/report/storagefee/list' },
        //                 { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mymbsbmonthlystoragefeeview', permission: '/root/mbsb/report/storagefee/list' },
        //                 { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mymbsbmonthlysummaryview', permission: '/root/mbsb/report/monthlysummary/list' },
        //             ]
        //         },
        //     ]
        // },
        { text: 'KGOLD', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'kodimasorderview', permission: '/root/kodimas/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'kodimaspricealertview', permission: '/root/kodimas/pricealert/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'kodimasconversionview', permission: '/root/kodimas/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'kodimaslogisticview', permission: '/root/kodimas/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderkodimasview', permission: '/root/kodimas/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mykodimasaccountclosureview', permission: '/root/kodimas/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mykodimascifview', permission: '/root/kodimas/profile/list', },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldkodimasview', permission: '/root/kodimas/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerkodimasview', permission: '/root/kodimas/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialkodimasview', permission: '/root/kodimas/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                }, 
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykodimascommissionview', permission: '/root/kodimas/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykodimascommissionnonpeakview', permission: '/root/kodimas/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mykodimasdailystoragefeeview', permission: '/root/kodimas/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mykodimasmonthlystoragefeeview', permission: '/root/kodimas/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mykodimasmonthlysummaryview', permission: '/root/kodimas/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'KGOLD AFFILIATE', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'kgoldaffiorderview', permission: '/root/kgoldaffi/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'kgoldaffipricealertview', permission: '/root/kgoldaffi/pricealert/list' },
                //{ text: 'Reversal', iconCls: 'x-fa fa-undo', leaf: true, viewType: 'reversalview', permission: '' },
                //{ text: 'Disbursement', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mydisbursementview', permission: '/root/go/disbursement/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'kgoldafficonversionview', permission: '/root/kgoldaffi/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'kgoldaffilogisticview', permission: '/root/kgoldaffi/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderkgoldaffiview', permission: '/root/kgoldaffi/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mykgoldaffiaccountclosureview', permission: '/root/kgoldaffi/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mykgoldafficifview', permission: '/root/kgoldaffi/profile/list', },
                //{ text: 'Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'myktpvaultitem-border', permission: '/root/ktp/vault/list'},
                //{ text: 'Dashboard', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mygoldbarstatusview', permission: '/root/bmmb/goldbarstatus/list' },
                // { text: 'Approval', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'myaccountholderforpepview', permission: '/root/bmmb/approval/list' },
                //{ text: 'FPX', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mypaymentdetailview', permission: '/root/bmmb/fpx/list' },
                //{ text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mygopushnotificationview', permission: '/root/go/pushnotification/list' },

                //{ text: 'Bank', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mybankview', permission: '/root/bmmb/profile/list' },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldkgoldaffiview', permission: '/root/kgoldaffi/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerkgoldaffiview', permission: '/root/kgoldaffi/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialkgoldaffiview', permission: '/root/kgoldaffi/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                },
                // { text: 'Gold Transaction', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'mygoldtransactionview', permission: '/root/bmmb/goldtransaction/list' },
                // { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'godocumentationview', permission: '/root/go/documentation/list' },               
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykgoldafficommissionview', permission: '/root/kgoldaffi/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykgoldafficommissionnonpeakview', permission: '/root/kgoldaffi/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mykgoldaffidailystoragefeeview', permission: '/root/kgoldaffi/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mykgoldaffimonthlystoragefeeview', permission: '/root/kgoldaffi/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mykgoldaffimonthlysummaryview', permission: '/root/kgoldaffi/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'KIGA', iconCls: 'x-fa fa-university', selectable: false,
            children: [
                { text: 'Spot Orders', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'koponasorderview', permission: '/root/koponas/goldtransaction/list' },
                { text: 'Price Alert', iconCls: 'x-fa fa-clock', leaf: true, viewType: 'koponaspricealertview', permission: '/root/koponas/pricealert/list' },
                { text: 'Conversion', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'koponasconversionview', permission: '/root/koponas/redemption/list' },
                { text: 'Logistic', iconCls: 'x-fa fa-truck', leaf: true, viewType: 'koponaslogisticview', permission: '/root/koponas/logistic/list' },
                { text: 'Account Holder', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderkoponasview', permission: '/root/koponas/profile/list' },
                { text: 'Account Closure', iconCls: 'x-fa fa-eraser', leaf: true, viewType: 'mykoponasaccountclosureview', permission: '/root/koponas/accountclosure/list' },
                { text: 'User Profile', iconCls: 'x-fa fa-id-card', visible: false, leaf: true, viewType: 'mykoponascifview', permission: '/root/koponas/profile/list', },
                { text: 'Gold Transfer', iconCls: 'x-fa fa-arrows-alt-h', leaf: true, viewType: 'mytransfergoldkoponasview', permission: '/root/koponas/transfergold/list' },
                { text: 'Promo', iconCls: 'x-fa fa-rocket', leaf: true, viewType: 'myledgerkoponasview', permission: '/root/koponas/promo/list' },
                { text: 'Special Trade', iconCls: 'x-fa fa-phone', leaf: true, viewType: 'myspotorderspecialkoponasview', permission: '/root/koponas/sale' },
                {
                    text: 'Storage Fee', iconCls: 'x-fa fa-coins', selectable: false,
                    children: [
                        
                    ]
                }, 
                { text: 'Report', iconCls: 'x-fa fa-chart-bar', selectable: false,
                    children:[
                        { text: 'Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykoponascommissionview', permission: '/root/koponas/report/commission/list' },
                        { text: 'Non-Peak Commission', iconCls: 'x-fa fa-coins', leaf: true, viewType: 'mykoponascommissionnonpeakview', permission: '/root/koponas/report/commission/list' },
                        { text: 'Daily Admin & Storage Fee', iconCls: 'x-fa fa-calendar-day', leaf: true, viewType: 'mykoponasdailystoragefeeview', permission: '/root/koponas/report/storagefee/list' },
                        { text: 'Monthly Admin & Storage Fee', iconCls: 'x-fa fa-calendar-alt', leaf: true, viewType: 'mykoponasmonthlystoragefeeview', permission: '/root/koponas/report/storagefee/list' },
                        { text: 'Monthly Summary', iconCls: 'x-fa fa-user', leaf: true, viewType: 'mykoponasmonthlysummaryview', permission: '/root/koponas/report/monthlysummary/list' },
                    ]
                },
            ]
        },
        { text: 'Common Minted Vault', iconCls: 'x-fa fa-bars', leaf: true, viewType: 'mintedbarstatusview', permission: '/root/system/mintedbar/list' },
        { text: 'Common DGV Vault', iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'commonvaultitem-border', permission: '/root/common/vault/list' },

        { text: 'Price Management', iconCls: 'x-fa fa-book', selectable: false,
            children: [
                { text: 'Trader', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderview', permission: '/root/system/trader/list'  },
                { text: 'Trader Order', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderordersview', permission: '/root/system/trader/list'  },
                { text: 'KTP Trader', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderktpview', permission: '/root/system/trader/ktplist'  },
                { text: 'KTP Trader Order', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderordersktpview', permission: '/root/system/trader/ktplist'  },
                { text: 'Price Adjuster', iconCls: 'x-fa fa-book', leaf: true, viewType: 'priceadjusterview', permission: '/root/system/priceadjuster/list' },
                { text: 'Price Delay', iconCls: 'x-fa fa-book', leaf: true, viewType: 'pricedelayview', permission: '/root/system/priceadjuster/list' },
                { text: 'Price Stream', iconCls: 'x-fa fa-stream', leaf: true, viewType: 'pricestreamview', permission: '/root/system/pricestream/list' },
                { text: 'Price Provider', iconCls: 'x-fa fa-dollar-sign', leaf: true, viewType: 'priceproviderview', permission: '/root/system/priceprovider/list' },
                { text: 'Price Validation', iconCls: 'x-fa fa-user-check', leaf: true, viewType: 'PriceValidationView', permission: '/root/system/pricevalidation/list' },

            ]
        },
        { text: 'System Settings', iconCls: 'x-fa fa-wrench', selectable: false,
            children: [
                { text: 'Product', iconCls: 'x-fa fa-shopping-cart', leaf: true, viewType: 'productview', permission: '/root/system/product/list' },
                { text: 'Trading Schedule', iconCls: 'x-fa fa-calendar', leaf: true, viewType: 'tradingscheduleview', permission: '/root/system/tradingschedule/list' },
                { text: 'Announcement', iconCls: 'x-fa fa-bullhorn', leaf: true, viewType: 'announcementview', permission: '/root/system/announcement/list' },
                { text: 'Account Holder Announcement', iconCls: 'x-fa fa-bullhorn', leaf: true, viewType: 'myannouncementview', permission: '/root/system/myannouncement/list' },
                { text: 'Announcement Theme', iconCls: 'x-fa fa-swatchbook', leaf: true, viewType: 'myannouncementthemeview', permission: '/root/system/myannouncementtheme/list' },
                { text: 'AMLA Imports', iconCls: 'x-fa fa-download', leaf: true, viewType: 'myscreeninglistimportview', permission: '/root/system/amla/list' },
                { text: 'Push Notification', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'mypushnotificationview', permission: '/root/system/pushnotification/list' },
                { text: 'Documentation', iconCls: 'x-fa fa-file-contract', leaf: true, viewType: 'mydocumentationview', permission: '/root/system/documentation/list' },   
                { text: 'Tags', iconCls: 'x-fa fa-tags', leaf: true, viewType: 'tagview', permission: '/root/developer/tag/list' },
                /* ---- Event ---- added on 2017-08-17 */
                { text: 'Event', iconCls: 'x-fa fa-bullhorn', selectable: false,
                    children: [
                        { text: 'Event Subscription', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'eventsubscriberview', permission: '/root/system/event/event_subscription' },
                        { text: 'Event Log', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'eventlogview', permission: '/root/system/event/event_log' },
                        { text: 'Event Message Template', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'eventmessageview', permission: '/root/system/event/event_message' },
                        { text: 'Event Trigger', iconCls: 'x-fa fa-bell', leaf: true, viewType: 'eventtriggerview', permission: '/root/developer/event' },
                    ]
                },

            ]
        },
        { text: 'Manage Access', iconCls: 'x-fa fa-lock', selectable: false,
            children: [
                { text: 'Partner',iconCls: 'x-fa fa-users',leaf: true, viewType: 'partnerview',  permission: '/root/system/partner/list' },
                //{ text: 'Merchant', iconCls: 'x-fa fa-building', leaf: true, viewType: 'pageblank', id: 'branchview', permission: '/root/system/branch/list' },
                { text: 'Users', iconCls: 'x-fa fa-user', leaf: true, viewType: 'userview', permission: '/root/system/user/list' },
                { text: 'Roles', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'roleview', permission: '/root/system/role/list' },
                { text: 'Restrict IP', iconCls: 'x-fa fa-globe', leaf: true, viewType: 'iprestrictionview', permission: '/root/system/ip/list' },
                { text: 'Api Logs', iconCls: 'x-fa fa-book', leaf: true, viewType: 'apilogsview', permission: '/root/system/apilog/list' },
            ]
        },
    ]
});
