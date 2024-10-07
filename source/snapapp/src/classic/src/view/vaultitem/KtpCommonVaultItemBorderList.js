Ext.define('snap.view.vaultitem.KtpCommonVaultItemBorderList', { // _CHANGE
    extend: 'snap.view.vaultitem.vaultitemBorderList',
    xtype: 'ktpcommonvaultitem-border', // _CHANGE
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
    layout: 'border',
    width: 500,
    height: 400,
    partnerCode: 'KTP',
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
                partnercode: 'KTP',
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
    partnerCode:'KTP',
    items: [
        {
            title: 'KTP DGV Info',
            region: 'east',
            xtype: 'panel',
            width: '35%',
            layout: 'vbox',
            style: {
                backgroundColor: 'gray',
            },

            items: [
                {
                    xtype: 'polar',
                    innerPadding: 40,
                    width: '100%',
                    height: 360,
                    store: {
                        type: 'CommonVault',
                        proxy: {
                            type: 'ajax',	       	
                            url: 'index.php?hdl=vaultitem&action=getCommonVaultInfo&partnercode=KTP',		
                            reader: {
                                type: 'json', 
                            }            
                        },
                    },
                    interactions: ['itemhighlight', 'rotate'],
                    legend: {
                        type: 'sprite',
                        docked: 'bottom'
                    },
                    series: [
                        {
                            type: 'pie3d',
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
                },{
                    html: '',
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
            title: 'KTP DGV Vault',
            region: 'center',
            collapsible: true,
            xtype: 'ktpcommonvaultitemview',
            reference: 'vaultcentercontainer',
        },
        {
            title: 'Summary',
            region: 'south',
            collapsible: true,
            xtype: 'commonvaultitem-summary',
            reference: 'commonvaultitem-summary',
            type: 'KTP',
        }
    ]
});


serialnoTemplateWithDO = (data) => {
    var returnx = {

        xtype: 'container',
        height: '100%',
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