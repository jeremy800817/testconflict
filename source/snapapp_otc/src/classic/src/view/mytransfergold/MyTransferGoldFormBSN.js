// Init searchfield
Ext.define('Ext.ux.form.SearchField', {
    extend: 'Ext.form.field.Trigger',    
    alias: 'widget.searchfield',    
    trigger1Cls: Ext.baseCSSPrefix + 'form-search-trigger',          
    initComponent: function(){
        this.callParent(arguments);
    },      
    afterRender: function(){
        this.callParent();        
    }
});
Ext.define('snap.view.mytransfergold.MyTransferGoldFormBSN',{
    extend: 'Ext.panel.Panel',
    xtype: 'MyTransferGoldForm_BSN',

    requires: [
        'snap.view.mytransfergold.MyTransferGoldController'
    ],
    store: {
        occupationcategory: Ext.create('snap.store.OccupationCategory'),
        occupationsubcategorychecker: Ext.create('snap.store.OccupationSubCategory'),
        occupationsubcategory: Ext.create('snap.store.OccupationSubCategory'),
        bankaccounts: Ext.create('snap.store.BankAccounts')
    },
    controller: 'mytransfergold-mytransfergold',
    viewModel: {
        data: {
            name: "Transfer Gold",
            senderid:'',
            senderxau:0,
            receiverid:''

        }
    },
    formClass: 'snap.view.gridpanel.GridFormOtc',
    initComponent: function(formView, form, record, asyncLoadCallback){
        elmntTransfer = this;
        vm = this.getViewModel();

        async function getList(){
            return true
        }
        getList().then(
            function(data){

            }
        );

        // Ext.getCmp('searchtype').setValue(store.getAt('0').get('id'));

        this.callParent(arguments);
        elmntTransfer.lookupReference('searchtype').setValue(2);
    },
    permissionRoot: '/root/gtp/cust',
    store: 'MyTransferGold',
    layout: 'fit',
    cls: Ext.baseCSSPrefix + 'shadow',
    items :{
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
        items: [{
            layout:{
                type:'hbox',
            },
            items:[{
                xtype: 'panel',
                title: 'SEARCH SENDER / RECEIVER',
                height:120,
                layout:{
                    type:'hbox',
                },
                margin: '0 0 0 10',
                cls: '',
                items:[{ 
                    flex:1,
                    xtype:'combobox',
                    cls:'combo_box',
                    store: {
                        fields: ['type', 'name'],
                        data : [
                            {"type":"1", "name":"Customer ID"},
                            {"type":"2", "name":"MyKad No"},
                            {"type":"2", "name":"Passport Number"},
                            {"type":"2", "name":"Company Registration No"},
                            {"type":"4", "name":"GIRO/ GIRO i Account No"},
                        ]
                    },
                    listeners: {
                        select: function(combo, records, eOpts) {
                            senderaccountholdersearch = elmntTransfer.lookupReference('senderaccountholdersearch');
                            receiveraccountholdersearch = elmntTransfer.lookupReference('receiveraccountholdersearch');

                            if(records.data.type == 'MyKad No'){
                                newText = records.data.name;
                            }else{
                                newText = records.data.name;
                            }
                            senderaccountholdersearch.setEmptyText(newText);
                            receiveraccountholdersearch.setEmptyText(newText);
                        }
                    },
                    id: 'searchtype',
                    reference: 'searchtype',
                    queryMode: 'local',
                    displayField: 'name',
                    valueField: 'type',
                    forceSelection: true,
                    editable: false,
                    margin: "0 0 0 10",
                },
                {
                    margin:'0 0 0 15',
                    items:[{
                        xtype:'label',
                        text:'SENDER'
                    },
                    {
                        xtype: 'textfield',
                        text: 'Search',
                        emptyText: 'MyKad No',
                        style: 'text-align:center',
                        reference: 'senderaccountholdersearch',
                        id:'searchsenderic',
                        margin: '4 0 0 0'
                    }]
                },
                {
                    xtype:'label',
                    cls: 'x-fa fa-arrow-right',
                    margin: '25 0 0 25'
                },
                {
                    margin:'0 0 0 15',
                    items:[{
                        xtype:'label',
                        text:'RECEIVER',
                        margin: '0 0 0 20'
                    },
                    {
                        xtype: 'textfield',
                        text: 'Search',
                        emptyText: 'MyKad No',
                        style: 'text-align:center;',
                        reference: 'receiveraccountholdersearch',
                        margin: '4 0 0 20',
                        id:'searchreceiveric'
                    }]
                },
                {
                    xtype:'button',
                    text:'SEARCH',
                    iconCls: 'x-fa fa-search',
                    cls:'',
                    margin: "21 0 0 10",
                    handler: 'searchaccountholder'
                }],
            }],
            style: 'padding: 0 0 15px 0'
        },
        {
            layout:'hbox',
            margin:'10 0 0 0',
            items:[{
                xtype: 'panel',
                flex:6,
                title: 'SENDER SEARCH RESULTS <span font-size: 5px;>(Please select a record)</span>',
                reference: 'senderpanel',
                border: true,
                margin: "10 5 0 0",
                items: [
                    {
                        title: '',
                        xtype: 'sendersearchresultview',
                        reference: 'sendersearchresults',
                        enablePagination: true,
                        store: {
                            proxy: {
                                type: 'ajax',
                                url: '',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                        },
                        viewConfig : {
                            listeners : {
                                cellclick : function(view, cell, cellIndex, record,row, rowIndex, e) {
                                    elmntTransfer.lookupReference('sendername').setText(record.data.fullname);
                                    elmntTransfer.lookupReference('senderic').setText(record.data.mykadno);
                                    elmntTransfer.lookupReference('senderemail').setText(record.data.email);
                                    elmntTransfer.lookupReference('senderstatus').setText(record.data.statusname);
                                    elmntTransfer.lookupReference('sendermygoldaccountno').setText(record.data.accountholdercode);
                                    var t = 0;
                                    if(record.data.xaubalance != null && record.data.xaubalance != ''){
                                        t = record.data.xaubalance;
                                    }
                                    elmntTransfer.lookupReference('senderxau').setText(t+'g');

                                    vm.set('senderid', record.data.id);
                                    vm.set('senderxau', t);
                                }
                             }
                        }
                    },
                ],
            },
            {
                xtype: 'panel',
                flex:6,
                title: 'RECEIVER SEARCH RESULTS <span font-size: 5px;>(Please select a record)</span>',
                reference: 'receiverpanel',
                border: true,
                margin: "10 0 0 5",
                items: [
                    {
                        title: '',
                        xtype: 'receiversearchresultview',
                        reference: 'receiversearchresults',
                        enablePagination: true,
                        store: {
                            proxy: {
                                type: 'ajax',
                                url: '',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                        },
                        viewConfig : {
                            listeners : {
                                cellclick : function(view, cell, cellIndex, record,row, rowIndex, e) {
                                    elmntTransfer.lookupReference('receivername').setText(record.data.fullname);
                                    elmntTransfer.lookupReference('receiveric').setText(record.data.mykadno);
                                    elmntTransfer.lookupReference('receiveremail').setText(record.data.email);
                                    elmntTransfer.lookupReference('receiverstatus').setText(record.data.statusname);
                                    elmntTransfer.lookupReference('receivermygoldaccountno').setText(record.data.accountholdercode);
                                    var t = 0;
                                    if(record.data.xaubalance != null && record.data.xaubalance != ''){
                                        t = record.data.xaubalance;
                                    }
                                    elmntTransfer.lookupReference('receiverxau').setText(t+'g');

                                    vm.set('receiverid', record.data.id);
                                }
                             }
                        }
                    },
                ],
            },],
        },
        {
            layout:{
                type:'hbox',
            },
            margin: '10 0 0 0',
            items:[{
                xtype: 'panel',
                title: 'SENDER DETAILS',
                layout: 'vbox',
                reference: 'senderdetails',
                flex:6,
                items:[{
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'Full Name: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'sendername'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'IC: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'senderic',
                        id:'senderic'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'Email: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'senderemail'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'BSN MyGold Account-I No: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'sendermygoldaccountno'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'Status: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'senderstatus'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'XAU Balance: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'senderxau'
                    }],
                }],
            },
            {
                xtype: 'panel',
                title: 'RECEIVER DETAILS',
                layout: 'vbox',
                reference: 'receiverdetails',
                flex:6,
                items:[{
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'Full Name: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'receivername'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'IC: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'receiveric'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'Email: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'receiveremail'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'BSN MyGold Account-I No: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'receivermygoldaccountno'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'Status: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'receiverstatus'
                    }],
                },
                {
                    layout: 'hbox',
                    items:[{
                        xtype:'label',
                        text:'XAU Balance: ',
                        margin: '0 0 10 10',
                        style:'width:150px'
                    },
                    {
                        xtype: 'label',
                        text:'-',
                        reference:'receiverxau'
                    }],
                }],
            }],
        },
        {
            layout:'hbox',
            margin:'10 0 0 0',
            items:[{
                layout:'hbox',
                margin:'0 0 0 0',
                items:[{
                    layout:'vbox',
                    margin:'10 0 0 15',
                    items:[{
                        xtype:'label',
                        text:'Teller Remarks (Mandatory)',
                        style:'padding: 5 0 0 0',
                    },
                    {
                        xtype:'textareafield',
                        id:'tellerremarks',
                        width: 470,
                        reference:'tellerremarks',
                        maxLength:255,
                        enforceMaxLength: true,
                    }],
                }],
            },
            {
                layout:'hbox',
                margin:'0 0 0 0',
                items:[{
                    layout:'vbox',
                    margin:'10 0 0 15',
                    items:[{
                        xtype:'label',
                        text:'Amount Transfer (g)',
                        style:'padding: 5 0 0 0',
                    },
                    {
                        xtype:'textfield',
                        emptyText: '0.000',
                        id:'amounttransfer',
                        reference:'amounttransfer',
                        maskRe:/[0-9.]/,
                    }],
                },
                {
                    xtype:'button',
                    text:'PROCEED',
                    iconCls:'x-fa fa-arrow-right',
                    cls:'proceed_btn',
                    margin: "27 0 0 10",
                    handler: 'proceedconfirmtransfer'
                }],

            }],
            
        }],
    },
    confirmationpopup: {
        controller: 'mytransfergold-mytransfergold',
        formDialogWidth: Ext.getBody().getViewSize().width * 0.7,
        formDialogHeight: Ext.getBody().getViewSize().height * 0.75,
        formDialogTitle: 'Confirm Transfer Gold',
        scrollable:'vertical',
        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: 'panel',
            flex: 10,
            layout: 'anchor',
            msgTarget: 'side',
            style:'border: 1px solid white',
        },
        enableFormPanelFrame: false,
        formPanelLayout: 'hbox',
        // style:'border: 1px solid white',
        formPanelItems: [
            //1st hbox
            {
                style:'border: 1px solid white',
                items: [{
                    layout:'hbox',
                    items:[{
                        xtype: 'fieldset',
                        title: 'Sender Information',
                        layout: 'hbox',
                        flex:5,
                        defaultType: 'textfield',
                        fieldDefaults: {
                            anchor: '100%',
                            msgTarget: 'side',
                            margin: '0 0 5 0',
                            width: '100%',
                        },
                        margin:'0 5 0 0',
                        items: [
                            {
                                xtype: 'fieldcontainer',
                                fieldLabel: '',
                                defaultType: 'textboxfield',
                                layout: 'vbox',
                                items: [
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Full Name', reference: 'confirmsendername', name: 'confirmsendername', style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'IC Number', reference: 'confirmsenderic', name: 'confirmsenderic', style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Email', reference: 'confirmsenderemail', name: 'confirmsenderemail', style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Gold Balance', reference: 'confirmsenderxau', name: 'confirmsenderemail', style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                ]
                            }
                        ]
                    },
                    {
                        xtype: 'fieldset',
                        title: 'Receiver Information',
                        layout: 'hbox',
                        flex:5,
                        defaultType: 'textfield',
                        fieldDefaults: {
                            anchor: '100%',
                            msgTarget: 'side',
                            margin: '0 0 5 0',
                            width: '100%',
                        },
                        margin:'0 0 0 5',
                        items: [
                            {
                                xtype: 'fieldcontainer',
                                fieldLabel: '',
                                defaultType: 'textboxfield',
                                layout: 'vbox',
                                items: [
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Full Name', reference: 'confirmreceivername', name: 'confirmreceivername', style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'IC Number', reference: 'confirmreceiveric', name: 'confirmreceiveric', style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Email', reference: 'confirmreceiveremail', name: 'confirmreceiveremail', style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                    {
                                        xtype: 'displayfield', allowBlank: false, fieldLabel: 'Gold Balance', reference: 'confirmreceiverxau', name: 'confirmreceiveremail', style: 'padding-left: 20px;', labelWidth: '10%',
                                    },
                                ]
                            }
                        ]
                    }]
                },
                {
                    xtype:'fieldset',
                    title:'Amount Transfer',
                    layout:'vbox',
                    flex:10,
                    items:[{
                        xtype: 'fieldcontainer',
                        fieldLabel: '',
                        defaultType: 'textboxfield',
                        layout: 'vbox',
                        items: [
                            {
                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Amount Transfer', reference: 'confirmamounttransfer', name: 'confirmamounttransfer', style: 'padding-left: 20px;', labelWidth: '10%',
                            },
                            {
                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Sender Balance After Transfer', reference: 'confirmsenderbalanceaftertransfer', name: 'confirmsenderbalanceaftertransfer', style: 'padding-left: 20px;', labelWidth: '10%',
                            },
                            {
                                xtype: 'displayfield', allowBlank: false, fieldLabel: 'Teller Remarks', reference: 'confirmtellerremarks', name: 'confirmtellerremarks', style: 'padding-left: 20px;', labelWidth: '10%',
                            },
                        ]
                    }],
                },
                {
                    layout:'vbox',
                    style:'text-align: -webkit-center;',
                    items:[{
                        // style:'margin-top:15px',
                        flex:10,
                        xtype: 'displayfield',
                        value : "Security Pin",
                        cls:'orderpopoutaqad_text_sell_subTitle',
                        width:400,
                        style:'text-align: -webkit-center;display:none',
                        // margin: '50 0 0 0',
                        margin: '0 0 0 0',
                        readOnly : true,
                            
                    },
                    {
                        xtype: 'form',
                        border: false,
                        layout: 'hbox',
                        // height:70,
                        height:10,
                        flex: 10,
                        width: 400,
                        reference: 'transfer-securitypin',
                        cls: 'security_pin_panel',
                        style: 'display:none;',
                        items: [
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                reference: 'init_pin_1',
                                inputType: 'password',
                                flex: 0.1,
                                width: 1,
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                        // debugger;
                                        this.lookupController().lookupReference('init_pin_2').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_2',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_3').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_3',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_4').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_4',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_5').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'), 
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_5',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                    
                                        this.lookupController().lookupReference('init_pin_6').focus();
                                    }
                                }
                            },
                            { xtype: 'textfield', name: getText('enterpassword'),
                                maskRe: /[0-9-]/,
                                maxLength: 1,
                                enforceMaxLength: true,
                                flex: 0.1,
                                reference: 'init_pin_6',
                                inputType: 'password',
                                listeners: {
                                    change: function(field, newVal, oldVal) {
                                        // this.callParent(arguments);
                                    }
                                }
                            },
                            
                        ]
                    },
                    {
                        xtype:'container',
                        layout:'hbox',
                        // width:400,
                        items:[{
                            xtype:'button',
                            text:'Print Transfer Confirmation',
                            reference:'btnprinttransferconfirmation',
                            iconCls: 'x-fa fa-file',
                            cls:'',
                            margin: "20 0 0 50",
                            style:'border-radius:5px',
                            handler: 'printconfirmtransfer'
                        },
                        {
                            xtype:'button',
                            text:'Confirm Transfer',
                            reference:'btnconfirmtransfer',
                            iconCls: 'x-fa fa-check',
                            cls:'',
                            margin: "20 0 0 10",
                            style:'border-radius:5px',
                            handler: 'confirmtransfer'
                        },
                        {
                            xtype:'button',
                            text:'Transfer Receipt',
                            reference:'btntransferreceipt',
                            iconCls: 'x-fa fa-file',
                            cls:'',
                            margin: "20 0 0 120",
                            style:'border-radius:5px',
                            handler: 'transferreceipt'
                        },
                        {
                            xtype:'button',
                            text:'OK',
                            reference:'btnokclose',
                            iconCls: 'x-fa fa-check',
                            cls:'',
                            margin: "20 0 0 10",
                            style:'border-radius:5px',
                            handler: 'okclose'
                        }]
                    }],
                },
                { xtype: 'hidden', hidden: true, name: 'confirmsenderid' },
                { xtype: 'hidden', hidden: true, name: 'confirmreceiverid' },
                ],
            },
        ],
    },
});
