Ext.define('snap.view.vaultitem.CommonVaultItemBorderList', { // _CHANGE
    extend: 'snap.view.vaultitem.vaultitemBorderList',
    xtype: 'commonvaultitem-border', // _CHANGE
    requires: [
        'Ext.layout.container.Border',
        'Ext.chart.*'
    ],
    profiles: {
        classic: {
            itemHeight: 100
        },
        neptune: {
            itemHeight: 100
        },
        graphite: {
            itemHeight: 120
        },
        'classic-material': {
            itemHeight: 120
        }
    },
    type: 'common',
    partnerCode: 'common',
    layout: 'border',
    width: 500,
    height: 400,
    //cls: [Ext.baseCSSPrefix + 'shadow', 'vaultranscontainerwrap'],

    bodyBorder: false,

    defaults: {
        collapsible: true,
        split: true,
        bodyPadding: 10
    },
    viewModel: {
        data: {
            withdoserialnumbers: [],
            withoutdoserialnumbers: [],
            transferringserialnumbers: [],
            permissions: [],
            acehqserialnumbers: [],
            aceg4sserialnumbers: [],
            mibg4sserialnumbers: [],
            totalserialnumbers: [],
            status: '',
            element: '',

        }
    },
    listeners: {
        afterrender: function () {
            elmnt = this;
            vmv = this.getViewModel();
            originType = this.type;

            snap.getApplication().sendRequest({
                hdl: 'vaultitem',
                action: 'infoTable',
                origintype: originType,
            }, 'Fetching data from server....').then(
                function (data) {
                    // console.log(elmnt);
                    elmnt.lookupController().getView().lookupReference('sharedgvinfotable').setHtml(data.html)
                    elmnt.lookupController().getView().lookupReference('sharedgvinfosidebar').setHtml(data.sidebar)
                    // this.getView().getReferenceHolder('sharedgvinfotable').html(data.html);
                })
        }
    },

    items: [
        // {
        //     xtype: 'polar',
        //     innerPadding: 40,
        //     width: '100%',
        //     height: 400,
        //     store: {
        //         fields: ['name', 'data1'],
        //         data: [{
        //             name: 'metric one',
        //             data1: 14
        //         }, {
        //             name: 'metric two',
        //             data1: 16
        //         }, {
        //             name: 'metric three',
        //             data1: 14
        //         }, {
        //             name: 'metric four',
        //             data1: 6
        //         }, {
        //             name: 'metric five',
        //             data1: 36
        //         }]
        //     },
        //     interactions: ['itemhighlight', 'rotate'],
        //     legend: {
        //         type: 'sprite',
        //         docked: 'bottom'
        //     },
        //     series: [
        //         {
        //             type: 'pie3d',
        //             angleField: 'total',
        //             donut: 30,
        //             distortion: 0.6,
        //             highlight: {
        //                 margin: 15
        //             },
        //             label: {
        //                 field: 'name'
        //             },
        //             tooltip: {
        //                 trackMouse: true,
        //                 renderer: 'onSeriesTooltipRender'
        //             }
        //         }
        //     ]
        // },
        {
            title: 'Common DGV Info',
            region: 'east',
            xtype: 'panel',
            // cls: 'vaultranscontainer',
            // listeners: {
            //     collapse: function (data, data1) {
            //         // data.getView().getStore().reload()
            //         // console.log(data,data1,this,'Collapse')
            //         data.getView().up().up().lookupReferenceHolder().lookupReference('vaultcentercontainer').getView().getStore().reload()
            //     },
            //     expand: function (data, data1) {
            //         data.getView().getStore().reload()
            //         // console.log(data,data1,this,'Expand')
            //     }
            // },
            width: '35%',
            layout: 'vbox',
            style: {
                backgroundColor: 'gray',
            },

            items: [
                // {
                //     xtype: 'polar',
                //     legend: {
                //         docked: 'top'
                //     },
                    
                //     // theme: 'green',
                //     animate: true,
                //     interactions: ['rotate', 'itemhighlight'],
                    
                //     store: {
                //         fields: ['name', 'data1'],
                //         data: [{
                //             name: 'metric one',
                //             data1: 14
                //         }, {
                //             name: 'metric two',
                //             data1: 16
                //         }, {
                //             name: 'metric three',
                //             data1: 14
                //         }, {
                //             name: 'metric four',
                //             data1: 6
                //         }, {
                //             name: 'metric five',
                //             data1: 36
                //         }]
                //     },
                    
                //     series: {
                //         showInLegend: true,
                //         type: 'pie',
                //         label: {
                //             field: 'name',
                //             display: 'rotate'
                //         },
                //         xField: 'data1',
                //         donut: 30
                //     }
                // }, {
                //     xtype: 'container',
                //     width: '100%',
                //     padding: 10,
                //     layout: {
                //         type: 'hbox',
                //         pack: 'center'
                //     },
                //     items: {
                        
                //     }
                // }
                {
                    xtype: 'polar',
                    // innerPadding: 40,
                    width: '100%',
                    height: 480,
                    // Add collapsible option,
                    title: 'Utilization Chart',
                    // header: {
                    //     style: {
                    //         backgroundColor: 'red',
                    //         color: 'blue',
                    //     }
                    // },
                    // cls: 'transparent-header',
                    collapsible: true,
                    collapseDirection:"top",
                    store: {
                        type: 'CommonVault'
                    },
                    interactions: ['itemhighlight', 'rotate'],
                    legend: {
                        type: 'sprite',
                        docked: 'bottom'
                    },
                    margin : '0 0 5 0',
                    series: [
                        {
                            type: 'pie3d',
                            // rootProperty: 'chart',
                            angleField: 'usage',
                            donut: 30,
                            distortion: 0.6,
                            highlight: {
                                margin: 15
                            },
                            label: {
                                field: 'name',
                                calloutLine: {
                                    color: 'rgba(0,0,0,0)' // Transparent to hide callout line
                                },
                                calloutColor: 'rgba(0,0,0,0)',
                                renderer: function(val) {
                                    return ''; // Empty label to hide text
                                }
                            },
                            tooltip: {
                                trackMouse: true,
                                renderer: function(tooltip, record, item) {
                                    tooltip.setHtml(record.get('name') + ': ' + record.get('usage') + '%  (grams:'+record.get('grams')+')');
                                }
                            }
                        }
                    ],
                    // listeners: {
                    //     onSeriesTooltipRender: function(tooltip, record, item) {
                    //         tooltip.setHtml(record.get('name') + ': ' + record.get('data1') + '%');
                    //     },
                    // }
                },{
                    html: '',
                    autoScroll: true,
                    reference: 'sharedgvinfotable',
                    width: '100%',
                    height: '500px',
                    flex: 1,
                },
            ]
        },
        {
            title: 'Total Customer Holding',
            region: 'east',
            xtype: 'panel',
            // cls: 'vaultranscontainer',
            // listeners: {
            //     collapse: function (data, data1) {
            //         // data.getView().getStore().reload()
            //         // console.log(data,data1,this,'Collapse')
            //         data.getView().up().up().lookupReferenceHolder().lookupReference('vaultcentercontainer').getView().getStore().reload()
            //     },
            //     expand: function (data, data1) {
            //         data.getView().getStore().reload()
            //         // console.log(data,data1,this,'Expand')
            //     }
            // },
            width: 200,
            layout: 'vbox',
            items: [
                {
                    html: '',
                    reference: 'sharedgvinfosidebar',
                    width: '100%',
                    height: '500px',
                    flex: 1,
                },
            ]
        },
       
       
        {
            title: 'Common DGV Vault',
            region: 'center',
            collapsible: true,
            // margin: '5 0 0 0',
            xtype: 'commonvaultitemview',
            reference: 'vaultcentercontainer',
        },
        // New dashbaord to track delivery 
        {
            title: 'Summary',
            region: 'south',
            collapsible: true,
            // margin: '5 0 0 0',
            xtype: 'commonvaultitem-summary',
            reference: 'commonvaultitem-summary',
            type: 'common',
        },
        {
            title: 'Transaction',
            region: 'east',
            xtype: 'commonvaultitemtransview',
            cls: 'vaultranscontainer',
            listeners: {
                collapse: function(data, data1){
                    // data.getView().getStore().reload()
                    // console.log(data,data1,this,'Collapse')
                    data.getView().up().up().lookupReferenceHolder().lookupReference('vaultcentercontainer').getView().getStore().reload()
                },
                expand:  function(data, data1){
                    data.getView().getStore().reload()
                    // console.log(data,data1,this,'Expand')
                }
            }
        },
    ]
});


serialnoTemplateWithDO = (data) => {
    var returnx = {

        xtype: 'container',
        height: '100%',
        //fieldStyle: 'background-color: #000000; background-image: none;',
        //scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: data.name,
            layout: 'hbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            items: [{
                xtype: 'fieldcontainer',
                layout: 'vbox',
                flex: 2,
                items: [{
                        xtype: 'displayfield',
                        name: 'serialnumber',
                        value: data.name,
                        reference: 'serialno',
                        fieldLabel: 'Serial Number',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield',
                        name: 'donumber',
                        value: data.deliveryordernumber,
                        reference: 'deliveryorderno',
                        fieldLabel: 'Delivery Order Number',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield',
                        name: 'allocatedon',
                        value: data.allocatedon,
                        reference: 'allocatedon',
                        fieldLabel: 'Allocated On',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #fff ",
                    },
                ]
            }, ]
        }, ],


    }

    return returnx
}

serialnoTemplateWithoutDO = (data) => {
    var returnx = {

        xtype: 'container',
        height: 200,
        //fieldStyle: 'background-color: #000000; background-image: none;',
        //scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: data.name,
            layout: 'hbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            items: [{
                xtype: 'fieldcontainer',
                layout: 'vbox',
                flex: 2,
                items: [{
                        xtype: 'displayfield',
                        name: 'serialnumber',
                        value: data.name,
                        reference: 'serialno',
                        fieldLabel: 'Serial Number',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield',
                        name: 'allocatedon',
                        value: data.allocatedon,
                        reference: 'allocatedon',
                        fieldLabel: 'Allocated On',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #fff ",
                    },
                ]
            }, ]
        }, ],


    }

    return returnx
}

transferringserialnumbers = (data) => {
    var returnx = {

        xtype: 'container',
        height: 200,
        //fieldStyle: 'background-color: #000000; background-image: none;',
        //scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: data.name,
            layout: 'hbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            items: [{
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [{
                            xtype: 'displayfield',
                            name: 'serialnumber',
                            value: data.name,
                            reference: 'serialno',
                            fieldLabel: 'Serial Number',
                            flex: 1,
                            style: 'padding-left: 20px;padding-right: 20px;',
                            fieldStyle: " background-color: #ffffff ",
                        },
                        {
                            xtype: 'displayfield',
                            name: 'allocatedon',
                            value: data.allocatedon,
                            reference: 'allocatedon',
                            fieldLabel: 'Allocated On',
                            flex: 1,
                            style: 'padding-left: 20px;padding-right: 20px;',
                            fieldStyle: " background-color: #fff ",
                        },
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [{
                            xtype: 'displayfield',
                            name: 'fromlocation',
                            value: data.from,
                            reference: 'from',
                            fieldLabel: 'From',
                            flex: 1,
                            style: 'padding-left: 20px;padding-right: 20px;',
                            fieldStyle: " background-color: #ffffff ",
                        },
                        {
                            xtype: 'displayfield',
                            name: 'tolocation',
                            value: data.to,
                            reference: 'to',
                            fieldLabel: 'To',
                            flex: 1,
                            style: 'padding-left: 20px;padding-right: 20px;',
                            fieldStyle: " background-color: #fff ",
                        },
                    ]
                },
            ]
        }, ],


    }

    return returnx
}

serialnoTemplateInventory = (data) => {
    var returnx = {

        xtype: 'container',
        height: 200,
        //fieldStyle: 'background-color: #000000; background-image: none;',
        //scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: data.name,
            layout: 'hbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            items: [{
                xtype: 'fieldcontainer',
                layout: 'vbox',
                flex: 2,
                items: [{
                        xtype: 'displayfield',
                        name: 'serialnumber',
                        value: data.name,
                        reference: 'serialno',
                        fieldLabel: 'Serial Number',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #ffffff ",
                    },
                    {
                        xtype: 'displayfield',
                        name: 'allocatedon',
                        value: data.allocatedon,
                        reference: 'allocatedon',
                        fieldLabel: 'Allocated On',
                        flex: 1,
                        style: 'padding-left: 20px;padding-right: 20px;',
                        fieldStyle: " background-color: #fff ",
                    },
                ]
            }, ]
        }, ],


    }

    return returnx
}