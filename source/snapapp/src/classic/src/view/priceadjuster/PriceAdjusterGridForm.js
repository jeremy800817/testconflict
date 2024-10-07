Ext.define('snap.view.priceadjuster.PriceAdjusterGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.PriceAdjusterGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.priceadjuster.PriceAdjusterTreeController',
        'Ext.view.MultiSelector',
        'Ext.grid.*',
        'Ext.layout.container.Column',
    ],
    viewModel: {
        data: {
            theCompany: null,
            inputgrossweight: 0,

            total_poweight: 100, // all po sum weight
            total_grossweight: 0,   // after * purity(all)
            total_balanceweight: 0, // remaining po weight
            total_purity: 0,    // purity(all)

        }
    },
    store:{
        selectedGRNStore: {},
        hours: [{
            id: '0',
            name: 'Peak Hours',
            time: '07:00:00',
            timeend: '17:59:59',
        },{
            id: '1',
            name: 'Non-Peak Hours',
            time: '18:00:00',
            timeend: '06:59:59',
        }],
    },
    controller: 'gridpanel-priceadjustertreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'PriceAdjuster',
    formDialogWidth: '1000px',
    enableFormDialogClosable: false,
    formPanelDefaults: {
        msgTarget: 'side',
        margins: '0 0 10 0'
    },
    height: '530px',
    // height: '90%',
    // width: '80%',
    // maxWidth: '1024px',
    formPanelDefaults: {
        border: false,
        //scrollable: true,
    },
    listeners: {
        'beforeedit': function (editor, e) {

        },
    },
    enableFormPanelFrame: false,
    formPanelItems: [
        {
            xtype: 'fieldset',
            title: 'Price Adjuster',
            
            items: [{
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: [
                    {
                        xtype: 'container',
                        items: [{
                            xtype: "container",
                            layout: {
                                type: "vbox",
                                align: "stretch",
                                
                            },
                            items: [
                                {
                                    xtype: 'combobox',
                                    fieldLabel: 'Price Provider',
                                    margin: '0 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    width: '350px',
                                    name: 'priceproviderid',
                                    store: [{
                                        id: '-',
                                        name: 'Select Price Provider',
                                    }],
                                    autoLoad: true,
                                    queryMode: 'local',
                                    editable: false,
                                    // disableKeyFilter: false,
                                    valueField: 'id',
                                    displayField: 'name',
                                    reference: 'pricecombo',
                                    // handler: 'onclickcompany_handler',
                                    allowBlank: false,
                                    bind: {
                                        selection: "{selectedProvider}",
                                        // selection: function(){
                                        //     return '{selectedhours}'
                                        // },
                                    },
                                    listeners: {
                                        select : 'onclickpriceprovider',
                                        beforeactivate: function(){
                                            Ext.MessageBox.confirm('Confirm', 'This will change your current data.', function(id) {
                                                if (id == 'yes') {
    
                                                }
                                            })
                                        },
                                    },
                                },
    
                                {
                                    xtype: 'combobox',
                                    fieldLabel: 'Hours',
                                    margin: '5 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    width: '350px',
                                    name: 'hours',
                                    store: {
                                        storeId: 'hours',
                                        alias: 'hours',
                                        data: [{
                                            id: '0',
                                            name: 'Peak Hours',
                                            time: '07:00:00',
                                            timeend: '17:59:59',
                                            tier: 0,
                                            // time: '2021-02-01 08:30:00',
                                        },{
                                            id: '1',
                                            name: 'Non-Peak Hours',
                                            time: '18:00:00',
                                            timeend: '06:59:59',
                                            tier: 1,
                                            // time: '2021-02-01 18:00:00',
                                        }],
                                    },
                                    autoLoad: true,
                                    queryMode: 'local',
                                    editable: false,
                                    valueField: 'id',
                                    displayField: 'name',
                                    reference: 'hourscombo',
                                    allowBlank: false,
                                    bind: {
                                        selection: "{selectedhours}",
                                        // selection: function(){
                                        //     return '{selectedhours}'
                                        // },
                                    },
                                    listeners: {
                                        select : 'onclickprice',
                                        beforeactivate: function(){
                                            Ext.MessageBox.confirm('Confirm', 'This will change your current data.', function(id) {
                                                if (id == 'yes') {
    
                                                }
                                            })
                                        },
                                    },
                                    value: '2',
                                },
                                {
                                    xtype: 'panel',
                                    layout: 'hbox',
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            // xtype: 'datefield',
                                            // format: 'Y-m-d H:i:s',
                                            fieldLabel: 'From Time',
                                            name: 'effectiveon',
                                            reference: 'effectiveon',
                                            margin: '0 0 0 20',
                                            forceSelection: true,
                                            enforceMaxLength: true,
                                            // step: 0.1,
                                            // minValue: 0,
                                            allowBlank: false,
                                            fieldStyle: "font-size: 1.1rem",
                                            bind: "{selectedhours.time}".toString(),
                                            // bind: function(){
                                            //     return '{selectedhours.time}'
                                            // }
                                            readOnly: true,
                                        },
                                        {
                                            xtype: 'textfield',
                                            // xtype: 'datefield',
                                            // format: 'Y-m-d H:i:s',
                                            fieldLabel: 'To Time',
                                            name: 'effectiveendon',
                                            reference: 'effectiveendon',
                                            margin: '0 0 0 20',
                                            forceSelection: true,
                                            enforceMaxLength: true,
                                            // step: 0.1,
                                            // minValue: 0,
                                            allowBlank: false,
                                            fieldStyle: "font-size: 1.1rem",
                                            bind: "{selectedhours.timeend}".toString(),
                                            // bind: function(){
                                            //     return '{selectedhours.time}'
                                            // }
                                            readOnly: true,
                                        },
                                    ]
                                }
                            ]
                        },{
                            xtype: "container",
                            margin: '10 0 0 0',
                            layout: {
                                type: "hbox",
                                align: "stretch",
                            },
                            items: [
                                {
                                    xtype: 'displayfield',
                                    value: 'ACE Buy',
                                    flex: 1,
                                    // fieldLabel: 'TASDASD',
                                    margin: '10 0 0 20',
                                    fieldStyle: "font-size: 1.0rem"
                                },{
                                    xtype: 'displayfield',
                                    value: 'ACE Sell',
                                    flex: 1,
                                    // fieldLabel: 'TASDASD',
                                    margin: '10 0 0 0',
                                    fieldStyle: "font-size: 1.0rem"
                                }
                            ]
                        },{
                            xtype: "container",
                            margin: '0 0 0 0',
                            layout: {
                                type: "hbox",
                                align: "stretch",
                            },
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Fx Buy Premium',
                                    name: 'fxbuypremium',
                                    reference: 'fxbuypremium',
                                    margin: '0 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem"
                                },{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Fx Sell Premium',
                                    name: 'fxsellpremium',
                                    reference: 'fxsellpremium',
                                    margin: '0 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem"
                                }
                            ]
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                align: "stretch",
                            },
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Buy Margin',
                                    name: 'buymargin',
                                    reference: 'buymargin',
                                    margin: '10 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem"
                                },{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Sell Margin',
                                    name: 'sellmargin',
                                    reference: 'sellmargin',
                                    margin: '10 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem"
                                }
                            ]
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                align: "stretch",
                            },
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Refine Fee',
                                    name: 'refinefee',
                                    reference: 'refinefee',
                                    margin: '10 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem"
                                },{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Supplier Premium',
                                    name: 'supplierpremium',
                                    reference: 'supplierpremium',
                                    margin: '10 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem"
                                }
                            ]
                        },{
                            xtype: "container",
                            layout: {
                                type: "hbox",
                                align: "stretch",
                            },
                            items: [
                                {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Buy Spread',
                                    name: 'buyspread',
                                    reference: 'buyspread',
                                    margin: '10 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem"
                                },{
                                    xtype: 'numberfield',
                                    fieldLabel: 'Sell Spread',
                                    name: 'sellspread',
                                    reference: 'sellspread',
                                    margin: '10 0 0 20',
                                    forceSelection: true,
                                    enforceMaxLength: true,
                                    step: 0.1,
                                    // minValue: 0,
                                    allowBlank: false,
                                    fieldStyle: "font-size: 1.1rem"
                                }   
                            ]
                        },]
                    },
                
                    {
               
                        xtype: 'container',
                        margin: '0 0 0 30',
                        // stytle: 'position:absolute',
                        html: [`
                        <div style="font-size: 0.7rem;">
                            <span style="color:#00309c">ACG Buy GP = INTLb (USD/oz) x ReutersFXb(USD → MYR) x 32.148 (Oz-->Kg) - Refining INTL - Premium ReutersFXb - Marginb - Spread Adjustor</span>
                            <br><b>INTLb</b>: INTL Gold buy price
                            <br><b>ReutersFXb</b>: Reuters USD->MYR buy price
                            <br><b>Refining INTL</b>: INTL Refining Fee
                            <br><b>Premium ReutersFXb</b>: Buy Premium for ReutersFX
                            <br><b>Marginb</b>: ACG Buy Margin
                            <br><b>Spread Adjustor</b>: Price adjustor
                            <br><br>
                            <span style="color:#00309c">ACG Sell GP = INTLs (USD/oz) x ReutersFXs(MYR → USD) x 32.148 (Oz-->Kg) + Premium INTL + Premium ReutersFXs + Margins + Spread Adjustor</span>
                            <br><b>INTLs</b>: INTL Gold sell price
                            <br><b>ReutersFXs</b>: Reuters MYR->USD sell price
                            <br><b>Premium INTL</b>: Premium for INTL
                            <br><b>Premium ReutersFXs</b>: Sell Premium for ReutersFX
                            <br><b>Margins</b>: ACG Sell Margin
                            <br><b>Spread Adjustor</b>: Price adjustor
                        </div>
                            `
                        ]
                    }]
            },]
        }
    ]
});
