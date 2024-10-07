Ext.define('snap.view.user.User_ALRAJHI', {
    extend:'Ext.panel.Panel',
    xtype: 'userview_ALRAJHI',
    permissionRoot: '/root/system/user',
    
    
    requires: [
        'snap.store.User',
        'snap.model.User',
        'snap.view.user.UserController',
        'snap.view.user.UserModel',
    ],

    controller: 'user-user',
    viewModel: {
        type: 'user-user'
    },

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
                title: 'User',
                layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                scrollable:true,
                items: [
                    {
                        xtype: 'otcuserview', 
                        columns: [
                            {
                                text: 'User type', dataIndex: 'type', filter: {
                                    type: 'combo',
                                    // store: snap.store.UserType.data,
                                    store: [
                                        ['HQ', 'HQ'],
                                        ['Regional', 'Regional'],
                                        ['Branch', 'Branch'],
                                        // 0 - pending, 1 - available, 2 - allocated, 3 - transferring, 4 - returned 
                                    ]
                                }, renderer: function (value, rec) {
                                    if ('HQ' == value) return 'HQ';
                                    else if ('Regional' == value) return 'Regional';
                                    else return 'Branch';
                                }, flex: 1
                            },
                            //{ text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true },
                            { text: 'Staff ID', dataIndex: 'staffid', filter: { type: 'string' }, width: 150 },
                            { text: 'Name', dataIndex: 'name', filter: { type: 'string' }, width: 150 },
                            { text: 'Username', dataIndex: 'username', filter: { type: 'string' }, width: 150, renderer: 'boldText'},
                            { text: 'Email', dataIndex: 'email', filter: { type: 'string' }, flex: 1 },
                            { text: 'Contact No', dataIndex: 'phoneno', filter: { type: 'string' }, flex: 1 },
                            
                            {
                                text: 'Status', dataIndex: 'status', filter: {
                                    type: 'combo',
                                    store: [
                                        ['0', 'Inactive'],
                                        ['1', 'Active']
                                        // 0 - pending, 1 - active 
                                    ]
                                }, renderer: function (value, rec) {
                                    if (value == 1) return 'Active';
                                    else if (value == 0) return 'Inactive';
                                    else return ' ';
                                }, flex: 1
                            },
                            { text: 'Partner Code', dataIndex: 'partnercode', filter: { type: 'string' }, width: 120 },
                        ],
                        listeners: {
                            afterrender: function(component) {
                                // Call your function here
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
                                                    field.nextSibling('[name=type]').setDisabled(isEdit);
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
                                            store: { type: 'OtcUserType' },
                                            displayField: 'name',
                                            valueField: 'id',
                                            listeners: {
                                                change: function (field, newValue) {
                                                    if (snap.getApplication().canEditPartner && field.disabled) {
                                                        field.disabled = false;
                                                    }
                                                    if (field.disabled) return;
                                                    var toDisable = 'Branch' != newValue;
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
                                            itemId: 'user_staffid',
                                            fieldLabel: 'Staff ID',
                                            name: 'staffid',
                                            allowBlank: false
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
                                            fieldLabel: 'Contact No',
                                            name: 'phoneno',
                                            allowBlank: false
                                        }]
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
                        // minHeight: 300,
                        // maxHeight: 1000,
                        //height: Ext.getBody().getViewSize().height * 70/100,
                    }
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    },

    
});

