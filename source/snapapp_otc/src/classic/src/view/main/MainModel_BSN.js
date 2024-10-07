/**
 * This class is the view model for the Main view of the application.
 **/
Ext.define("snap.view.main.MainModel_BSN", {
  extend: "Ext.app.ViewModel",

  alias: "viewmodel.main_BSN",

  data: {
    applogo:
      '<div class="main-logo"><img src="src/resources/images/bsn_logo_2.png"></div>',
    applogoNormal:
      '<div class="main-logo"><img src="src/resources/images/bsn_logo.png" style="height:50px; margin-left:4px; top:2px;"></div>',
    //applogoMicro:
    //  '<div class="main-logo"><img src="src/resources/images/logo_mini.png" style="height:50px; margin-left:4px; top:2px;"></div>',

    webtrail: "&nbsp;&nbsp;",

    redirectParam: null,

    overrideWebtrail: null,

    username: "Who am I?",

    copyright: "All Rights Reserved &copy; 2023 ACE Innovate Asia Berhad",

    version: "1.8.0",

    devcss: "",
    // devcss: '<link rel="stylesheet" href="src/resources/css/devcss.css">',
  },

  stores: {
    navItems: {
      type: "tree",
      root: {
        expanded: true,
        children: [],
      },
    },
  },

  menuItems: [
    {
      text: getText("search"),
      iconCls: "x-fa fa-search",
      leaf: true,
      viewType: "orderdashboardview_" + PROJECTBASE,
	  permission: "/root/" + PROJECTBASE.toLowerCase() + "/search",
    },
    {
      text: getText("dashboard"),
      iconCls: "x-fa fa-desktop",
      leaf: true,
      viewType: "otcanalyticsdataview",
      permission: "/root/" + PROJECTBASE.toLowerCase() + "/analytics",
    },
    {
      text: getText("register"),
      iconCls: "x-fa fa-user-plus",
      leaf: true,
      viewType: "otcregisterview_" + PROJECTBASE,
      permission: "/root/" + PROJECTBASE.toLowerCase() + "/register",
    },
    {
      visible: false,
      text: getText("register"),
      iconCls: "x-fa fa-user",
      leaf: true,
      viewType: "tncview_" + PROJECTBASE,
    },
    { 
      text: getText('Transfer Gold'), 
      iconCls: 'x-fa fa-random', 
      leaf: true, 
      viewType: 'MyTransferGoldForm_'+PROJECTBASE, 
      permission: '/root/' + PROJECTBASE.toLowerCase() + '/transfergold' 
    },
    { 
      text: getText('Outstanding Management Fee'), 
      iconCls: 'x-fa fa-dollar-sign', 
      leaf: true, 
      viewType: 'manualdeduction_'+PROJECTBASE, 
      permission: '/root/' + PROJECTBASE.toLowerCase() + '/transfergold' 
    },
    {
      text: getText("Yearly Gold Statement"),
      iconCls: "x-fa fa-lock",
      leaf: true,
      viewType: "myyearlystatementview_"+PROJECTBASE,
      permission:
        "/root/" + PROJECTBASE.toLowerCase() + "/yearlystatement/reprintyearlystatement",
    },
    {
      text: "Report",
      iconCls: "x-fa fa-file",
      selectable: false,
      children: [
        {
          text: getText("accountholder"),
          iconCls: "x-fa fa-id-card",
          leaf: true,
          viewType: "myaccountholderview_" + PROJECTBASE,
          permission: "/root/" + PROJECTBASE.toLowerCase() + "/profile/list",
        },
        {
          text: "User Profile",
          iconCls: "x-fa fa-id-card",
          visible: false,
          leaf: true,
          viewType: "my" + PROJECTBASE.toLowerCase() + "cifview",
          permission: "/root/" + PROJECTBASE.toLowerCase() + "/profile/list",
        },
        {
          text: getText("transaction"),
          iconCls: "x-fa fa-id-card",
          leaf: true,
          viewType: "transactionview",
          permission:
            "/root/" + PROJECTBASE.toLowerCase() + "/goldtransaction/list",
        },
        {
          text: getText("inventory"),
          iconCls: "x-fa fa-id-card",
          leaf: true,
          viewType: "inventoryview",
          permission:
            "/root/" + PROJECTBASE.toLowerCase() + "/vault/list",
        },
        {
          text: getText("redemption"),
          iconCls: "x-fa fa-shopping-cart",
          leaf: true,
          viewType: "conversionview_" + PROJECTBASE,
          permission: "/root/" + PROJECTBASE.toLowerCase() + "/redemption/list",
        },
        {
          text: getText("Transfer Gold"),
          iconCls: "x-fa fa-random",
          leaf: true,
          viewType: "mytransfergoldview_" + PROJECTBASE,
          permission: "/root/" + PROJECTBASE.toLowerCase() + "/transfergold/list",
        },
		{ 
			text: getText('Management Fee Report'), 
			iconCls: "x-fa fa-id-card", 
			leaf: true, 
			viewType: "managementfeededuction", 
			permission: "/root/" + PROJECTBASE.toLowerCase() + "/managementfeereport/list" 
		},
      ],
    },
    {
      text: getText("permission"),
      iconCls: "x-fa fa-lock",
      selectable: false,
      children: [
        //{ text: 'Merchant', iconCls: 'x-fa fa-building', leaf: true, viewType: 'pageblank', id: 'branchview', permission: '/root/system/branch/list' },
        // { text: 'Users', iconCls: 'x-fa fa-user', leaf: true, viewType: 'userview', permission: '/root/system/user/list' },
        // {
        //   text: getText("superadmin"),
        //   iconCls: "x-fa fa-user-secret",
        //   leaf: true,
        //   viewType: "superadminview_" + PROJECTBASE,
        //   permission: "/root/system/user/superadmin",
        // },
        {
          text: getText("users"),
          iconCls: "x-fa fa-users",
          leaf: true,
          viewType: "userview_" + PROJECTBASE,
          permission: "/root/system/user/list",
        },
        {
          text: "Roles",
          iconCls: "x-fa fa-id-card",
          leaf: true,
          viewType: "roleview",
          permission: "/root/system/role/list",
        },
        {
          text: "Restrict IP",
          iconCls: "x-fa fa-globe",
          leaf: true,
          viewType: "iprestrictionview",
          permission: "/root/system/ip/list",
        },
        {
          text: "Api Logs",
          iconCls: "x-fa fa-book",
          leaf: true,
          viewType: "apilogsview",
          permission: "/root/system/apilog/list",
        },
      ],
    },
    // { text: getText('priceadjuster'), iconCls: 'x-fa fa-book', leaf: true, viewType: 'priceadjusterview', permission: '/root/system/priceadjuster/list' },
    // { text: getText('pricemanagement'), iconCls: 'x-fa fa-warehouse', leaf: true, viewType: 'priceadjusterotcview', permission: '/root/system/priceadjuster/list'},
    {
      text: getText("pricemanagement"),
      iconCls: "x-fa fa-book",
      selectable: false,
      children: [
        // { text: 'Trader', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderview', permission: '/root/system/trader/list'  },
        // { text: 'Trader Order', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderordersview', permission: '/root/system/trader/list'  },
        // { text: 'KTP Trader', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderktpview', permission: '/root/system/trader/ktplist'  },
        // { text: 'KTP Trader Order', iconCls: 'x-fa fa-wrench', leaf: true, viewType: 'traderordersktpview', permission: '/root/system/trader/ktplist'  },
        {
          text: "Price Adjuster",
          iconCls: "x-fa fa-book",
          leaf: true,
          viewType: "priceadjusterview",
          permission: "/root/system/priceadjuster/list",
        },
        {
          text: "Price Delay",
          iconCls: "x-fa fa-book",
          leaf: true,
          viewType: "pricedelayview",
          permission: "/root/system/priceadjuster/list",
        },
        {
          text: "Price Stream",
          iconCls: "x-fa fa-stream",
          leaf: true,
          viewType: "pricestreamview",
          permission: "/root/system/pricestream/list",
        },
        {
          text: "Price Provider",
          iconCls: "x-fa fa-dollar-sign",
          leaf: true,
          viewType: "priceproviderview",
          permission: "/root/system/priceprovider/list",
        },
        {
          text: "Price Validation",
          iconCls: "x-fa fa-user-check",
          leaf: true,
          viewType: "PriceValidationView",
          permission: "/root/system/pricevalidation/list",
        },
      ],
    },

    {
      text: getText("logistic"),
      iconCls: "x-fa fa-truck",
      leaf: true,
      viewType: "otclogisticview",
      permission: "/root/" + PROJECTBASE.toLowerCase() + "/logistic/list",
    },
    // { text: getText('accountholder'), iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'myaccountholderview_'+ PROJECTBASE, permission: '/root/go/profile/list' },
    {
      text: getText("vaultinventory"),
      iconCls: "x-fa fa-warehouse",
      leaf: true,
      viewType: "myvaultitemborder_" + PROJECTBASE,
      permission: "/root/" + PROJECTBASE.toLowerCase() + "/vault/list",
    },
    {
      text: "Logs",
      iconCls: "x-fa fa-book",
      selectable: false,
      children: [
        {
          text: "User Activities Log",
          iconCls: "x-fa fa-book",
          leaf: true,
          viewType: "userlogsview",
          permission: "/root/" + PROJECTBASE.toLowerCase() + "/profile/list",
        },
      ],
    },
    {
      text: getText("approvetransaction"),
      iconCls: "x-fa fa-check",
      leaf: true,
      viewType: "approvalview",
      permission:
        "/root/" + PROJECTBASE.toLowerCase() + "/goldtransaction/approval",
    },

    {
      text: "System Settings",
      iconCls: "x-fa fa-wrench",
      selectable: false,
      children: [
        {
          text: "Partner",
          iconCls: "x-fa fa-users",
          leaf: true,
          viewType: "partnerview",
          permission: "/root/system/partner/list",
        },
        {
          text: "Product",
          iconCls: "x-fa fa-shopping-cart",
          leaf: true,
          viewType: "productview",
          permission: "/root/system/product/list",
        },
        {
          text: "Trading Schedule",
          iconCls: "x-fa fa-calendar",
          leaf: true,
          viewType: "tradingscheduleview",
          permission: "/root/system/tradingschedule/list",
        },
        {
          text: "Announcement",
          iconCls: "x-fa fa-bullhorn",
          leaf: true,
          viewType: "announcementview",
          permission: "/root/system/announcement/list",
        },
        {
          text: "Account Holder Announcement",
          iconCls: "x-fa fa-bullhorn",
          leaf: true,
          viewType: "myannouncementview",
          permission: "/root/system/myannouncement/list",
        },
        {
          text: "Announcement Theme",
          iconCls: "x-fa fa-swatchbook",
          leaf: true,
          viewType: "myannouncementthemeview",
          permission: "/root/system/myannouncementtheme/list",
        },
        {
          text: "AMLA Imports",
          iconCls: "x-fa fa-download",
          leaf: true,
          viewType: "myscreeninglistimportview",
          permission: "/root/system/amla/list",
        },
        {
          text: "Push Notification",
          iconCls: "x-fa fa-bell",
          leaf: true,
          viewType: "mypushnotificationview",
          permission: "/root/system/pushnotification/list",
        },
        {
          text: "Documentation",
          iconCls: "x-fa fa-file-contract",
          leaf: true,
          viewType: "mydocumentationview",
          permission: "/root/system/documentation/list",
        },
        {
          text: "Tags",
          iconCls: "x-fa fa-tags",
          leaf: true,
          viewType: "tagview",
          permission: "/root/developer/tag/list",
        },
        /* ---- Event ---- added on 2017-08-17 */
		/* { 
			text: 'Management Fee', 
			iconCls: 'x-fa fa-id-card', 
			leaf: true, 
			viewType: 'managementfeeview_' + PROJECTBASE, 
			permission: "/root/" + PROJECTBASE.toLowerCase() + "/managementfee/list"
		}, */
		{ 
			text: 'Management Fee', 
			iconCls: 'x-fa fa-id-card', 
			leaf: true, 
			viewType: 'otcmanagementfeeview', 
			permission: "/root/" + PROJECTBASE.toLowerCase() + "/managementfee/list"
		},
        {
          text: "Event",
          iconCls: "x-fa fa-bullhorn",
          selectable: false,
          children: [
            {
              text: "Event Subscription",
              iconCls: "x-fa fa-bell",
              leaf: true,
              viewType: "eventsubscriberview",
              permission: "/root/system/event/event_subscription",
            },
            {
              text: "Event Log",
              iconCls: "x-fa fa-bell",
              leaf: true,
              viewType: "eventlogview",
              permission: "/root/system/event/event_log",
            },
            {
              text: "Event Message Template",
              iconCls: "x-fa fa-bell",
              leaf: true,
              viewType: "eventmessageview",
              permission: "/root/system/event/event_message",
            },
            {
              text: "Event Trigger",
              iconCls: "x-fa fa-bell",
              leaf: true,
              viewType: "eventtriggerview",
              permission: "/root/developer/event",
            },
          ],
        },
      ],
    },
    // { text: 'Manage Access', iconCls: 'x-fa fa-lock', selectable: false,
    //     children: [
    //         { text: 'Partner',iconCls: 'x-fa fa-users',leaf: true, viewType: 'partnerview',  permission: '/root/system/partner/list' },
    //         //{ text: 'Merchant', iconCls: 'x-fa fa-building', leaf: true, viewType: 'pageblank', id: 'branchview', permission: '/root/system/branch/list' },
    //         { text: 'Users', iconCls: 'x-fa fa-user', leaf: true, viewType: 'userview', permission: '/root/system/user/list' },
    //         { text: 'Roles', iconCls: 'x-fa fa-id-card', leaf: true, viewType: 'roleview', permission: '/root/system/role/list' },
    //         { text: 'Restrict IP', iconCls: 'x-fa fa-globe', leaf: true, viewType: 'iprestrictionview', permission: '/root/system/ip/list' },
    //         { text: 'Api Logs', iconCls: 'x-fa fa-book', leaf: true, viewType: 'apilogsview', permission: '/root/system/apilog/list' },
    //     ]
    // },
  ],
});
