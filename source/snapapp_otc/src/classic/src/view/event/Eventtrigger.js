Ext.define('snap.view.event.Eventtrigger',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'eventtriggerview',

    requires: [
        'snap.store.Eventtrigger',
        'snap.model.Eventtrigger',
        'snap.view.event.EventtriggerController',
        'snap.view.event.EventtriggerModel'
    ],
    permissionRoot: '/root/developer/event',
    store: { type: 'Eventtrigger' },

    controller: 'eventtrigger-eventtrigger',

    viewModel: {
        type: 'eventtrigger-eventtrigger'
    },
    //title: 'EventTrigger',
    enableFilter: true,
    columns: [
        { text: 'Group Type', reference: 'gridGroupType', dataIndex: 'groupid', 
            filter: { type: 'combo', store: []
             }, flex: 1,
            renderer: function (t, meta, record) {
                var data = record.getData();
                return data.grouptypeid_text;
                // if(! this.theStore) {
                //     this.theStore = Ext.data.StoreManager.lookup('EventGroupType');
                // }
                // return this.theStore.getById(rec.groupid).name;
            } 
        },
        { text: 'Module', reference: 'gridModuleType', dataIndex: 'moduleid', 
                filter: { type: 'combo', store: []
             }, flex: 1,
            renderer: function (t, meta, record) {
                var data = record.getData();
                return data.moduleid_text;
            } 
        },
        { text: 'Action', reference: 'gridActionType', dataIndex: 'actionid', 
            filter: { type: 'combo', store: []}, flex: 1,
            renderer: function (t, meta, record) {
                var data = record.getData();
                return data.actionid_text;
            } 
        },
        { text: 'Processor', reference: 'gridProcessorType', dataIndex: 'processorclass', 
            filter: { type: 'combo', store: []}, flex: 1,
            renderer: function (t, meta, record) {
                var data = record.getData();
                return data.processor_desc;
            } 
        },
        { text: 'Matcher Class', dataIndex: 'matcherclass', filter: {type: 'string'  }, hidden: true, flex: 1 },
        // { text: 'Processor Class', dataIndex: 'processorclass', filter: {type: 'string'  }, hidden: true, flex: 1 },
        { text: 'Message Name', dataIndex: 'messagecode', filter: {type: 'string'  }, flex: 1 },
        { text: 'Observable Class', dataIndex: 'observableclass', filter: {type: 'string'  }, hidden: true, flex: 1 },
        { text: 'Old Status', dataIndex: 'oldstatus', filter: {type: 'int'  }, flex: 1 },
        { text: 'New Status', dataIndex: 'newstatus', filter: {type: 'int'  }, flex: 1 },
        { text: 'Object Class', dataIndex: 'objectclass', filter: {type: 'string'  }, hidden: true, flex: 1 },
        { text: 'Store to log', dataIndex: 'storetolog_text', filter: {type: 'bool'  }, flex: 1 },
        { text: 'GroupIdFieldName', dataIndex: 'groupidfieldname', filter: {type: 'string'  }, hidden: true, flex: 1 },
        { text: 'Eval. Code', dataIndex: 'evalcode', filter: {type: 'string'  }, hidden: true, flex: 1 },
        { text: 'Status', dataIndex: 'status', filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'], 
                    ['1', 'Active']
                    // 0 - pending, 1 - active 
                ]
            },renderer: function(value, rec) {
                if(value==1) return 'Active';
                else if (value==0) return 'Inactive';
                else return ' ';
            },flex: 1 },
    ],

    formConfig: {
        controller: 'eventtrigger-eventtrigger',
        formDialogWidth: 850,
        formDialogTitle: 'EventTrigger',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            msgTarget: 'side',
            margins: '0 0 10 0'
        },
        enableFormPanelFrame: false,
        formPanelItems: [
            { itemId: 'user_column_main',  layout: 'column',  xtype: 'fieldcontainer', defaults: { columnWidth: 0.5 },
                items: [
                    //First column
                    {  itemId: 'trigger_column_1', xtype: 'fieldcontainer', layout: { type: 'vbox', pack: 'start', align: 'stretch'},
                        items: [
                            { itemId: 'trigger_properties', xtype: 'fieldset', title: 'Trigger classication',
                                layout: 'anchor', defaultType: 'textfield', fieldDefaults: { anchor: '100%', msgTarget: 'side', margin: '0 0 5 0'},
                                items: [
                                    { inputType: 'hidden', hidden: true, name: 'id' },
                                    { inputType: 'hidden', hidden: true, name: 'hdl', value: 'eventtrigger' },
                                    { inputType: 'hidden', hidden: true, name: 'action' },
                                    { xtype: 'combobox', fieldLabel:'Group Type', store: {type: 'EventGroupType'}, remoteFilter: false,
                                        name: 'grouptypeid', valueField: 'id', displayField: 'desc', reference: 'grouptypeid', forceSelection: true, editable: false, allowBlank: false
                                    },
                                    { xtype: 'combobox', fieldLabel:'Module', store: {type: 'EventModuleType'}, remoteFilter: false,
                                        name: 'moduleid', valueField: 'id', displayField: 'desc', reference: 'moduleid', forceSelection: true, editable: false, allowBlank: false
                                    },
                                    { xtype: 'combobox', fieldLabel:'Action', store: {type: 'EventActionType'}, remoteFilter: false,
                                        name: 'actionid', valueField: 'id', displayField: 'desc', reference: 'actionid', forceSelection: true, editable: false, allowBlank: false
                                    },
                                ]
                            }, 
                            {itemId: 'trigger_contact_fieldset', xtype: 'fieldset', title: 'Trigger property', 
                                layout: 'anchor', defaultType: 'textfield', fieldDefaults: { anchor: '100%', msgTarget: 'side', margin: '0 0 5 0'},
                                items: [
                                    { xtype: 'combobox', fieldLabel:'Processor', store: {type: 'EventProcessorType'}, remoteFilter: false,
                                        name: 'processorclass', valueField: 'id', displayField: 'name', reference: 'processorclass', forceSelection: true, editable: false, allowBlank: false
                                    },
                                    { xtype: 'combobox', fieldLabel:'Message', store: {type: 'array', fields: ['id', 'code']}, queryMode: 'local', remoteFilter: false,
                                        name: 'messageid', valueField: 'id', displayField: 'code', reference: 'messageid', forceSelection: true, editable: false, allowBlank: false
                                    },
                                    { xtype: 'textfield', fieldLabel: 'Group ID Field Name', name: 'groupidfieldname'},
                                    { xtype: 'radiogroup', fieldLabel: 'Status', 
                                        items: [
                                            { boxLabel: 'Inactive', name: 'status', inputValue: '0' },
                                            { boxLabel: 'Active', name: 'status', inputValue: '1' }
                                        ]
                                    }
                                ]
                            }
                        ]
                    },
                    //Second Columm
                    { itemId: 'trigger_column_2', xtype: 'fieldcontainer', layout: { type: 'vbox', pack: 'start', align: 'stretch'}, margin: '0 0 0 10',
                        items: [
                            { itemId: 'trigger_condition_fieldset', xtype: 'fieldset', title: 'Trigger conditions',
                                layout: 'anchor', defaultType: 'textfield', fieldDefaults: { anchor: '100%', msgTarget: 'side', margin: '0 0 5 0'},
                                // height: 496,
                                items: [
                                    { xtype: 'combobox', fieldLabel:'Matcher Class', store: {type: 'array', fields: ['classname']}, queryMode: 'local', remoteFilter: false,
                                        name: 'matcherclass', valueField: 'classname', displayField: 'classname', reference: 'matcherclass', forceSelection: false, editable: true, allowBlank: false
                                    },
                                    { xtype: 'combobox', fieldLabel:'Observable Class', store: {type: 'array', fields: ['key', 'value']}, queryMode: 'local', remoteFilter: false,
                                        name: 'observableclass', valueField: 'value', displayField: 'value', reference: 'observableclass', forceSelection: false, editable: true, allowBlank: false
                                    },
                                    { xtype: 'combobox', fieldLabel:'Object Class', store: {type: 'array', fields: ['key', 'value']}, queryMode: 'local', remoteFilter: false,
                                        name: 'objectclass', valueField: 'value', displayField: 'value', reference: 'objectclass', forceSelection: false, editable: true, allowBlank: false
                                    },
                                    { xtype: 'textfield', fieldLabel: 'Old Status', name: 'oldstatus', maskRe: /[-1-9]/ , allowBlank: false},
                                    { xtype: 'textfield', fieldLabel: 'New Status', name: 'newstatus', maskRe: /[-1-9]/ , allowBlank: false},
                                    {
                                        reference: 'storetolog',
                                        fieldLabel: 'Store to Log',
                                        xtype: 'radiogroup',
                                        items: [{
                                            padding: '0 125 0 15',
                                            boxLabel  : 'No',
                                            name      : 'storetolog',
                                            inputValue: '0'
                                        },{
                                            boxLabel  : 'Yes',
                                            name      : 'storetolog',
                                            inputValue: '1'
                                        }]          
                                    },
                                    { xtype: 'textarea', fieldLabel: 'Eval. Code', name: 'evalcode' },
                                ]
                            }]
                    }
                ]
            }
        ]
    }
 /*
        formPanelItems: [
            
            { xtype: 'fieldset', title: 'Info', defaultType: 'textfield', collapsible: false, margin: '0 0 5 0',
                defaults: { labelWidth: 90, anchor: '100%', layout: 'hbox', hideLabel: false },
                items: [
                    // { xtype: 'combobox', fieldLabel:'Module', store: {type: 'array', fields: ['id', 'module', 'module_desc', 'name', 'desc']}, queryMode: 'local', remoteFilter: false,
                    //     name: 'moduleid', valueField: 'id', displayField: 'module_desc', reference: 'moduleid', forceSelection: true, editable: false, allowBlank: false,
                    //     tpl: Ext.create('Ext.XTemplate',
                    //         '<ul class="x-list-plain"><tpl for=".">',
                    //             '<li role="option" class="x-boundlist-item"><b>{module_desc}</b> - {desc}</li>',
                    //         '</tpl></ul>'
                    //     ),
                    //     // template for the content inside text field
                    //     displayTpl: Ext.create('Ext.XTemplate',
                    //         '<tpl for=".">',
                    //             '{module_desc} - {desc}',
                    //         '</tpl>'
                    //     )
                    // },
                    // { xtype: 'combobox', fieldLabel:'Action', store: {type: 'array', fields: ['id', 'name', 'desc']}, queryMode: 'local', remoteFilter: false,
                    //     name: 'actionid', valueField: 'id', displayField: 'desc', reference: 'actionid', forceSelection: true, editable: false, allowBlank: false
                    // },
                    // { xtype: 'combobox', fieldLabel:'Processor Class', store: {type: 'array', fields: ['processorname']}, queryMode: 'local', remoteFilter: false,
                    //     name: 'processorclass', valueField: 'processorname', displayField: 'processorname', reference: 'processorclass', forceSelection: false, editable: true, allowBlank: false
                    // },
               ]
            },
            {
                reference: 'eventstatus',
                fieldLabel: 'Status',
                xtype: 'radiogroup',
                items: [{
                    boxLabel  : 'Inactive',
                    name      : 'status',
                    inputValue: '0'
                },{
                    boxLabel  : 'Active',
                    name      : 'status',
                    inputValue: '1'
                }]          
            }]        
*/
});