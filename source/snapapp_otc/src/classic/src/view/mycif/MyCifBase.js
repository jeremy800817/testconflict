Ext.define('snap.view.mycif.MyCifBase', {
    extend: 'Ext.tab.Panel',

    requires: [
        'snap.view.mycif.MyCifBaseController',
        'snap.view.mycif.MyCifBaseModel',
    ],

    controller: 'mycif-mycifbase',
    viewModel: {
        type: 'mycif-mycifbase'
    },

    accountHolderId: 0,
    myDataHandler: 'mycif',
    myDataAction: 'getmycifdata',

    itemId: 'mycif',
    activeTab: 0,
    bodyPadding: 5,
    scrollable: true,
    closable: false,
    border: false,
    tabPosition: 'left',

    defaultItems: [{
        itemId: 'myprofile',
        xtype: 'myprofileview',
        title: 'Profile',
        iconCls: 'x-fa fa-user-circle',
    }, {
        itemId: 'mykyc',
        xtype: 'mykycview',
        title: 'KYC',
        iconCls: 'x-fa fa-id-card'
    }, {
        itemId: 'myamla',
        xtype: 'myamlaview',
        title: 'AMLA',
        iconCls: 'x-fa fa-donate'
    }, {
        itemId: 'mypep',
        xtype: 'mypepview',
        title: 'PEP',
        iconCls: 'x-fa fa-landmark',
        tabConfig: {
            hidden: true
        }
    }, {
        itemId: 'mygoldbalance',
        xtype: 'mygoldbalanceview',
        title: 'Gold Statement',
        iconCls: 'x-fa fa-exchange-alt'
    }],

    initComponent: function () {
        this.items = this.defaultItems;
        this.callParent(arguments);
    },

    accountholder: function (id) {
        var controller = this.getController();
        controller.setAccountHolderId(id);
    },
});
