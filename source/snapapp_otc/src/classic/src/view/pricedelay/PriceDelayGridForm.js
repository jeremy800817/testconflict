Ext.define('snap.view.pricedelay.PriceDelayGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.PriceDelayGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.pricedelay.PriceDelayTreeController',
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
        
    },
    controller: 'gridpanel-pricedelaytreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'PriceDelay',
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
    formPanelItems: [
        {
            xtype: 'fieldset',
            title: 'Price Delay',
            
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
                                fieldLabel: 'Price provider',
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
                                fieldLabel: 'Delay for',
                                name: 'delaytime',
                                reference: 'delaytime',
                                margin: '10 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                step: 1,
                                minValue: 0,
                                allowBlank: false,
                                fieldStyle: "font-size: 1.1rem"
                            }
                        ]
                    }
                ]
            }]
        }
    ]
});
