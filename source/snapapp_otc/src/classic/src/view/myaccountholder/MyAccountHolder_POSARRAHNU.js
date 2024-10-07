Ext.define('snap.view.myaccountholder.MyAccountHolder_POSARRAHNU', {
    extend:'Ext.panel.Panel',
    xtype: 'myaccountholderview_POSARRAHNU',
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/profile',
    
    // requires: [
    //     'snap.store.MyAccountHolder',
    //     'snap.model.MyAccountHolder',
    //     'snap.view.myaccountholder.MyAccountHolderController',
    //     'snap.view.myaccountholder.MyAccountHolderModel',
    // ],

    scrollable:true,
    items: {
        
        
        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
    
        defaults: {
            frame: true,
            //bodyPadding: 10
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
          
            {
                xtype: 'panel',
                title: 'Register Report',
                layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                items: [
                        // ITEM 1
                        { 
                            xtype: 'myaccountholderview', partnercode: PROJECTBASE, partnerCode: PROJECTBASE,
                            controller: 'myaccountholder-myaccountholder',
                            viewModel: {
                                type: 'myaccountholder-myaccountholder'
                            },  
                            store: {
                                type: 'MyAccountHolder',
                                proxy: {
                                    type: 'ajax',
                                    url: 'index.php?hdl=myaccountholder&action=list&partnercode='+PROJECTBASE,
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'records',
                                    }
                                },
                            },
                            toolbarItems: [
                                'detail', '|', 'filter', '|',
                                { reference: 'profileBtn', handler: 'onViewAccountHolder', text: 'CIF', showToolbarItemText: true, itemId: 'profileBtn', tooltip: 'Account Holder Profile', viewType: 'myprofileview', iconCls: 'x-fa fa-user', validSelection: 'single' },
                                { reference: 'approvePep', showToolbarItemText: true, itemId: 'approvePep', tooltip: 'Approve PEP', iconCls: 'x-fa fa-check-square', handler: 'approvePep', validSelection: 'single' },
                                { reference: 'suspendBtn', showToolbarItemText: true, itemId: 'suspendBtn', tooltip: 'Suspend', iconCls: 'x-fa fa-lock', handler: 'onSuspendAccountHolder', validSelection: 'single' },
                                { reference: 'unsuspendBtn', showToolbarItemText: true, itemId: 'unsuspendBtn', tooltip: 'Unsuspend', iconCls: 'x-fa fa-unlock', handler: 'onUnsuspendAccountHolder', validSelection: 'single' },
                                { reference: 'closeBtn', showToolbarItemText: true, itemId: 'closeBtn', tooltip: 'Close', iconCls: 'x-fa fa-eraser', handler: 'onCloseAccountHolder', validSelection: 'single' },
                                { reference: 'approveEkyc', showToolbarItemText: true, itemId: 'approveEkyc', tooltip: 'Approve EKYC', iconCls: 'x-fa fa-thumbs-up', handler: 'approveEkyc', validSelection: 'single' },
                                { reference: 'displayIdentityPhoto', showToolbarItemText: true, itemId: 'displayIdentityPhoto', tooltip: 'Display Identity Photo', iconCls: 'x-fa fa-image', handler: 'displayIdentityPhoto', validSelection: 'single' },
                                // Date functions
                                {
                                    xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                                },
                                {
                                    xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                                },
                                {
                                    iconCls: 'x-fa fa-redo-alt', style : "width : 130px;",  text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true, labelWidth:'auto'
                                },
                                {
                                    iconCls: 'x-fa fa-times-circle', style : "width : 130px;",  text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true, labelWidth:'auto'
                                },
                                {
                                    style : "width : 130px;", text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', labelWidth:'auto'// printType: pending
                                },
                            ],
                        },
                        // END ITEM 1
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    }
});
