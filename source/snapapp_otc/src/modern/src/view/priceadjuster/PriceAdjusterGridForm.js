Ext.define('snap.view.priceadjuster.PriceAdjusterGridForm', {
    extend: 'Ext.form.Panel',
    alias: 'widget.PriceAdjusterGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.priceadjuster.PriceAdjusterTreeController',
        //'Ext.view.MultiSelector',
        'Ext.grid.*',
        //'Ext.layout.container.Column',
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
        
    },
    controller: 'gridpanel-priceadjustertreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'PriceAdjuster',
    formDialogWidth: '50%',
    enableFormDialogClosable: false,
    formPanelDefaults: {
        msgTarget: 'side',
        margins: '0 0 10 0'
    },
    height: '80%',
    formPanelDefaults: {
        border: false,
        //scrollable: true,
    },
    listeners: {
        'beforeedit': function (editor, e) {

        },
    },
    enableFormPanelFrame: false,
    /*formPanelItems: [
        {
            xtype: 'fieldset',
            title: 'Price Adjuster',
            
            items: [{
                xtype: 'container',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items: [
                    {
                        xtype: "container",
                        layout: {
                            type: "hbox",
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
                                listeners: {
                                    select : 'onclickprice',
                                    beforeactivate: function(){
                                        Ext.MessageBox.confirm('Confirm', 'This will change your current data.', function(id) {
                                            if (id == 'yes') {

                                            }
                                        })
                                    },
                                }
                            },
                        ]
                    },{
                        xtype: "container",
                        margin: '20 0 0 0',
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
                                margin: '10 0 0 20',
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
                            },{
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
                            }
                        ]
                    }
                ]
            }]
        }
    ] */
});
