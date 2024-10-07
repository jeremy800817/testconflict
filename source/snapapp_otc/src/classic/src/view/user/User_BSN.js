Ext.define('snap.view.user.User_BSN', {
    extend:'Ext.panel.Panel',
    xtype: 'userview_BSN',
    permissionRoot: '/root/system/user',
    
    
    requires: [
        'snap.store.User',
        'snap.model.User',
        'snap.view.user.UserController',
        'snap.view.user.UserModel',
        'snap.store.States',

    ],

    controller: 'user-user',
    viewModel: {
        type: 'user-user'
    },

    layout: 'fit',
    scrollable: true,

    items: {
        
        cls: Ext.baseCSSPrefix + 'shadow',
        
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },

        defaults: {
            frame: true,
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
            {
                xtype: 'panel',
                title: 'User',
                layout: 'vbox',
                collapsible: true,
                margin: "10 0 0 10",
                scrollable: true,
                flex: 1,
                items: [
                    {
                        xtype: 'otcuserview',
                        flex:1,
                        partnercode: 'BSN',
                        enableFilter: true,
                        scrollable:true, 
                        // minHeight: 300,
                        // maxHeight: 1000,
                        //height: Ext.getBody().getViewSize().height * 70/100,
                       // toolbarItems: [ 'add', 'edit', 'detail', '|', 'filter'],
                        toolbarItems: [
                            'add', 'edit', 'detail', '|', 'filter', '|',
                            {
                                xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                            },
                            {
                                xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                            },
                            {
                                iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
                            },
                            {
                                iconCls: 'x-fa fa-times-circle', style : "width : 130px;",  text: 'Clear Date', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true, labelWidth:'auto'
                            },
                            {
                                text: 'Download',tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'downloadusers', handler: 'getUserExport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                            },
                        ],
                
                        columns: [
                            {
                                text: 'User type', dataIndex: 'type', filter: {
                                    type: 'combo',
                                    // store: snap.store.UserType.data,
                                    store: [
                                        ['Operator', 'Operator'],
                                        //['Trader', 'Trader'],
                                        ['Customer', 'Customer'],
                                        //['Sale', 'Sale'],
                                        //['Referral', 'Referral'],
                                        //['Agent', 'Agent'],
                                        // 0 - pending, 1 - available, 2 - allocated, 3 - transferring, 4 - returned 
                                    ]
                                }, renderer: function (value, rec) {
                                    if ('Operator' == value) return 'Operator';
                                    //else if ('Trader' == value) return 'Trader';
                                    else if ('Customer' == value) return 'Customer';
                                    // else if ('Sale' == value) return 'Sale';
                                    // else if ('Referral' == value) return 'Referral';
                                    // else if ('PC' == value) return 'PC';
                                    else return 'Customer';
                                }, flex: 1
                            },
                            //{ text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true },
                            //{ text: 'Staff ID', dataIndex: 'staffid', filter: { type: 'string' }, width: 150 },
                            { text: 'Name', dataIndex: 'name', filter: { type: 'string' }, width: 150 },
                            { text: 'Username', dataIndex: 'username', filter: { type: 'string' }, width: 150, renderer: 'boldText'},
                            { text: 'Email', dataIndex: 'email', filter: { type: 'string' }, flex: 1 },
                            { text: 'Staff ID', dataIndex: 'phoneno', filter: { type: 'string' }, flex: 1 },
                            
                            {
                                text: 'Status', dataIndex: 'status', filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Inactive'],
                                        ['1', 'Active'],
                                        ['2', 'New'],
                                        ['3', 'Expired'],
                                        ['4', 'Dormant'],
                                        ['5', 'Suspended'],
                                        ['6', 'Disabled'],
                                        ['7', 'Disabled for Revoke'],
                                        ['8', 'Disabled for Resign'],
                                        ['9', 'Disabled for Temporary Leave'],
                                        ['10','Deleted'],
                                        // 0 - pending, 1 - active 
                                    ]
                                }, renderer: function (value, rec) {
                                    if (value == 1) return 'Active';
                                    else if (value == 0) return 'Inactive';
                                    else if (value == 2) return 'New';
                                    else if (value == 3) return 'Expired';
                                    else if (value == 4) return 'Dormant';
                                    else if (value == 5) return 'Suspended';
                                    else if (value == 6) return 'Disabled';
                                    else if (value == 7) return 'Disabled for Revoke';
                                    else if (value == 8) return 'Disabled for Resign';
                                    else if (value == 9) return 'Disabled for Temporary Leave';
                                    else if (value == 10) return 'Deleted';
                                    else return ' ';
                                }, flex: 1
                            },
                            { text: 'Partner Code', dataIndex: 'partnercode', filter: { type: 'string' }, width: 120 },
                            { text: 'Created On', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: true, minWidth: 130 },
                            { text: 'Last Login On', dataIndex: 'lastlogin', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, hidden: false, minWidth: 130 },
                            { text: 'Role', dataIndex: 'role', filter: { type: 'string' }, width: 120},
                            { 
                                text: 'State Permission', dataIndex: 'state', filter: {
                                    type: 'combo',
                                    store: [
                                        ['01', 'Johor'],
                                        ['02', 'Kedah'],
                                        ['03', 'Kelantan'],
                                        ['04', 'Melaka'],
                                        ['05', 'Negeri Sembilan'],
                                        ['06', 'Pahang'],
                                        ['07', 'Penang'],
                                        ['08', 'Perak'],
                                        ['09', 'Perlis'],
                                        ['10', 'Sabah'],
                                        ['11', 'Sarawak'],
                                        ['12', 'Selangor'],
                                        ['13', 'Terengganu'],
                                        ['14', 'Kuala Lumpur'],
                                        ['15', 'Labuan'],
                                        ['16', 'Putrajaya'],
                                        // 0 - pending, 1 - active 
                                    ]
                                }, renderer: function (value, rec) {
                                    if (value == '01') return 'Johor';
                                    else if (value == '02') return 'Kedah';
                                    else if (value == '03') return 'Kelantan';
                                    else if (value == '04') return 'Melaka';
                                    else if (value == '05') return 'Negeri Sembilan';
                                    else if (value == '06') return 'Pahang';
                                    else if (value == '07') return 'Penang';
                                    else if (value == '08') return 'Perak';
                                    else if (value == '09') return 'Perlis';
                                    else if (value == '10') return 'Sabah';
                                    else if (value == '11') return 'Sarawak';
                                    else if (value == '12') return 'Selangor';
                                    else if (value == '13') return 'Terengganu';
                                    else if (value == '14') return 'Kuala Lumpur';
                                    else if (value == '15') return 'Labuan';
                                    else if (value == '16') return 'Putrajaya';
                                    else return ' ';
                                }, width: 120
                            },
                        ],
                        listeners: {
                            afterrender: function(component) {
                                vm = this.getViewModel();
                                vm.set('user_view', this);

                            }
                        },
                        formConfig: {
                            formDialogWidth: 800,
                            height: '90%',
                            formDialogTitle: 'User',
                    
                            formPanelItems: [{
                                itemId: 'user_column_main',
                                layout: 'column',
                                xtype: 'fieldcontainer',
                                defaults: {
                                    columnWidth: 0.5
                                },
                                items: [{
                                    itemId: 'user_column_1',
                                    xtype: 'fieldcontainer',
                                    layout: {
                                        type: 'vbox',
                                        pack: 'start',
                                        align: 'stretch'
                                    },
                                    items: [{
                                        itemId: 'user_main_fieldset',
                                        xtype: 'fieldset',
                                        title: 'Personal Information',
                                        layout: 'anchor',
                                        defaultType: 'textfield',
                                        fieldDefaults: {
                                            anchor: '100%',
                                            msgTarget: 'side',
                                            margin: '0 0 5 0'
                                        },
                                        items: [{
                                            itemId: 'user_id',
                                            inputType: 'hidden',
                                            hidden: true,
                                            name: 'id',
                                            listeners: {
                                                change: function (field, newValue) {
                                                    var isEdit = newValue > 0;
                                                    // field.nextSibling('[name=type]').setDisabled(isEdit);
                                                    field.nextSibling('[name=partnerid]').setDisabled(isEdit);
                                                    field.up('[itemId=user_column_main]')
                                                        .getComponent('user_column_2')
                                                        .getComponent('user_access_fieldset')
                                                        .getComponent('user_username')
                                                        .setDisabled(isEdit);
                                                }
                                            }
                                        }, {
                                            inputType: 'hidden',
                                            hidden: true,
                                            name: 'hdl',
                                            value: 'user'
                                        }, {
                                            inputType: 'hidden',
                                            hidden: true,
                                            name: 'action'
                                        }, {
                                            inputType: 'hidden',
                                            hidden: true,
                                            name: 'selectedroles'
                                        }, {
                                            itemId: 'user_type',
                                            reference: 'userType',
                                            name: 'type',
                                            xtype: 'combobox',
                                            fieldLabel: 'Bank User',
                                            store: [
                                                ['Operator', 'Operator'],
                                                //['Trader', 'Trader'],
                                                ['Customer', 'Customer'],
                                                //['Sale', 'Sale'],
                                                //['Referral', 'Referral'],
                                                //['Agent', 'Agent'],
                                                // 0 - pending, 1 - available, 2 - allocated, 3 - transferring, 4 - returned 
                                            ],
                                            displayField: 'name',
                                            valueField: 'id',
                                            listeners: {
                                                change: function (field, newValue) {
                                                    if (snap.getApplication().canEditPartner && field.disabled) {
                                                        field.disabled = false;
                                                    }
                                                    if (field.disabled) return;
                                                    var toDisable = 'Customer' != newValue;
                                                    field.nextSibling('[name=partnerid]').setDisabled(toDisable);
                                                    if (toDisable) {
                                                        field.nextSibling('[name=partnerid]').setValue('');
                                                    }
                                                }
                                            }
                                        }, {
                                            itemId: 'user_partnerid',
                                            xtype: 'combobox',
                                            fieldLabel: 'Branch',
                                            allowBlank: true,
                                            name: 'partnerid',
                                            store: {
                                                autoLoad: true,
                                                type: 'Partner',
                                                sorters: 'name'
                                            },
                                            disabled: 'true',
                                            listConfig: {
                                                getInnerTpl: function () {
                                                    return '[ {code} ] {name}';
                                                }
                                            },
                                            displayTpl: Ext.create('Ext.XTemplate',
                                                '<tpl for=".">',
                                                '[ {code} ] {name}',
                                                '</tpl>'
                                            ),
                                            displayField: 'name',
                                            valueField: 'id',
                                            typeAhead: true,
                                            queryMode: 'local',
                                            listeners: {
                                                expand: function(combo){
                                                    combo.store.load({
                                                        //page:2,
                                                        start: 0,
                                                        limit: 1500
                                                    })
                                                }
                                            },
                                            // forceSelection: true,
                                            // allowBlank: false
                                        }, {
                                            itemId: 'user_name',
                                            fieldLabel: 'Name',
                                            name: 'name',
                                            allowBlank: false
                                        }, {
                                            itemId: 'user_expire',
                                            xtype: 'datefield',
                                            format: 'Y-m-d H:i:s',
                                            fieldLabel: 'Expire',
                                            name: 'expire'
                                        },
                                        {
                                            fieldLabel: 'Status', xtype: 'radiogroup',
                                            items: [
                                                { boxLabel: 'Inactive', name: 'status', inputValue: '0' },
                                                { boxLabel: 'Active', name: 'status', inputValue: '1' }
                                            ]
                                        },{
                                            fieldLabel: ' ', xtype: 'radiogroup',
                                            items: [
                                                { boxLabel: 'Disabled For Revoke', name: 'status', inputValue: '7' },
                                                { boxLabel: 'Disabled For Resign', name: 'status', inputValue: '8' },
                                                { boxLabel: 'Delete', name: 'status', inputValue: '10' },
                                            ]
                                        }]
                                    }, {
                                        itemId: 'user_contact_fieldset',
                                        xtype: 'fieldset',
                                        title: 'Contact Information',
                                        layout: 'anchor',                   
                                        defaultType: 'textfield',
                                        fieldDefaults: {
                                            anchor: '100%',
                                            msgTarget: 'side',
                                            margin: '0 0 5 0'
                                        },
                                        items: [{
                                            itemId: 'user_email',
                                            fieldLabel: 'Email',
                                            vtype: 'email',
                                            name: 'email'
                                        }, {
                                            itemId: 'user_contactno',
                                            fieldLabel: 'Staff ID',
                                            name: 'phoneno',
                                            allowBlank: false
                                        }]
                                    },{
                                        itemId: 'user_permission_fieldset',
                                        xtype: 'fieldset',
                                        title: 'State Permission',
                                        layout: 'anchor',
                                        defaultType: 'textfield',
                                        reference: 'state_section',
                                        fieldDefaults: {
                                            anchor: '100%',
                                            msgTarget: 'side',
                                            margin: '0 0 5 0'
                                        },
                                        hidden:true,
                                        items: [
                                            {
                                                itemId: 'regional_permissions',
                                                reference: 'regional_permissions',
                                                xtype: 'combobox',
                                                fieldLabel: 'State',
                                                name: 'state',
                                                store: {
                                                    fields: ['code', 'name'],
                                                    data : [
                                                        {"code":"01", "name":"Johor"},
                                                        {"code":"02", "name":"Kedah"},
                                                        {"code":"03", "name":"Kelantan"},
                                                        {"code":"04", "name":"Melaka"},
                                                        {"code":"05", "name":"Negeri Sembilan"},
                                                        {"code":"06", "name":"Pahang"},
                                                        {"code":"07", "name":"Penang"},
                                                        {"code":"08", "name":"Perak"},
                                                        {"code":"09", "name":"Perlis"},
                                                        {"code":"10", "name":"Sabah"},
                                                        {"code":"11", "name":"Sarawak"},
                                                        {"code":"12", "name":"Selangor"},
                                                        {"code":"13", "name":"Terengganu"},
                                                        {"code":"14", "name":"Kuala Lumpur"},
                                                        {"code":"15", "name":"Labuan"},
                                                        {"code":"16", "name":"Putrajaya"}
                                                    ]
                                                },
                                                queryMode: 'local',
                                                displayField: 'name',
                                                valueField: 'code',
                                                listeners: {
                                                    
                                                }
                                            }
                                        ]
                                    }]
                                }, {
                                    itemId: 'user_column_2',
                                    margin: '0 0 0 10',
                                    xtype: 'fieldcontainer',
                                    layout: {
                                        type: 'vbox',
                                        pack: 'start',
                                        align: 'stretch'
                                    },
                                    items: [{
                                        itemId: 'user_access_fieldset',
                                        xtype: 'fieldset',
                                        title: 'Login Information',
                                        layout: 'anchor',
                                        defaultType: 'textfield',
                                        fieldDefaults: {
                                            anchor: '100%',
                                            msgTarget: 'side',
                                            margin: '0 0 5 0'
                                        },
                                        height: 496,
                                        items: [{
                                            itemId: 'user_username',
                                            fieldLabel: 'Username',
                                            name: 'username',
                                            // allowBlank: false
                                        }, {
                                            xtype: 'fieldcontainer',
                                            layout: 'hbox',
                                            fieldLabel: 'Password',
                                            defaultType: 'textfield',
                                            defaults: {
                                                inputType: 'password',
                                                flex: 1
                                            },
                                            items: [{
                                                margin: '0 5 0 0',
                                                itemId: 'user_password',
                                                name: 'userpassword',
                                                emptyText: 'Password',
                                                minLength: 8,
                                                maxLength: 20
                                            }, {
                                                itemId: 'user_confirmpassword',
                                                name: 'confirmpassword',
                                                emptyText: 'Confirm'
                                            }]
                                        }, {
                                            itemId: 'user_userrole',
                                            xtype: 'multiselector',
                                            title: 'Selected Roles',
                                            name: 'userrole',
                                            fieldName: 'title',
                                            viewConfig: {
                                                deferEmptyText: false,
                                                emptyText: 'No roles selected'
                                            },
                                            search: {
                                                field: 'title',
                                                store: {
                                                    autoLoad: true,
                                                    type: 'Role',
                                                    sorters: 'title'
                                                }
                                            },
                                            scrollable: 'vertical',
                                            height: 345,
                                            frame: false
                                        }]
                                    }]
                                }],
                                listeners: {
                                    afterrender: function(component) {
                                        // Call your function here
                                        myView = vm.get('user_view');
                                        selectedRecords = myView.getSelectionModel().getSelection();
                                       
                                        userid = Ext.ComponentQuery.query('#user_id')[0].getValue();
                                        isEditMode = (userid > 0) ? true : false;

                                        if(isEditMode) {
                                            snap.getApplication().sendRequest({
                                                hdl: 'user', action: 'getUserAdditionalData',
                                                id : selectedRecords[0].data.id,
                                            }, 'Fetching data from server....').then(
                                                function (data) {
                                                    if (data.success) {
            
                                                        Ext.ComponentQuery.query('#user_staffid')[0].setValue(data.additionaldata[0].uad_staffid);
    
                                                    }
                                            })
                                        }
                                        
                                    }
                                },
                            }]
                        }
                    }
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    }
});

