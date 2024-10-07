Ext.define('snap.view.collection.CollectionGridForm', {
    extend: 'snap.view.gridpanel.GridForm',
    alias: 'widget.CollectionGridForm',
    requires: [
        'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.collection.CollectionTreeController',
        'Ext.view.MultiSelector',
        'Ext.grid.*',
        'Ext.layout.container.Column',
    ],
    viewModel: {
        data: {
            theCompany: null,
            inputxauweight: 0,
            total_poweight: 0, // all po sum weight
            total_xauweight: 0, // after * purity(all)
            total_balanceweight: 0, // remaining po weight
            total_purity: 0, // purity(all)
            total_inputweight: 0,
        }
    },
    store: {
        selectedGRNStore: {},
    },
    controller: 'gridpanel-collectiontreecontroller',
    reference: 'formWindow',
    formDialogTitle: 'Collection',
    formDialogWidth: '95%',
    scrollable: true,
    enableFormDialogClosable: false,
    formPanelDefaults: {
        msgTarget: 'side',
        margins: '0 0 10 0'
    },
    height: '100%',
    formPanelDefaults: {
        border: false,
    },
    enableFormPanelFrame: false,
    formPanelItems: [{
        xtype: 'fieldset',
        title: 'Sales Info',
        items: [{
            xtype: 'container',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [{
                xtype: "container",
                layout: {
                    type: "hbox",
                    align: "flex",
                    flex: 1
                },
                items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Sales',
                    name: 'salespersonname',
                    reference: 'salespersoninput',
                    margin: '0 0 0 20',
                    forceSelection: true,
                    enforceMaxLength: true,
                    readOnly: true,
                }, ]
            }, {
                xtype: "container",
                layout: {
                    type: "hbox",
                    align: "flex",
                    flex: 1
                },
                items: [{
                    fieldLabel: 'Customer <br><small><b>(SAP Vendor Name)</b></small>',
                    margin: '0 0 0 20',
                    xtype: 'combobox',
                    name: 'customerid',
                    minChars: 0,
                    queryMode: 'remote',
                    queryParam: 'query',
                    bind: {
                        store: {
                            type: 'CustomerStore',
                            autoLoad: true,
                            remoteFilter: true,
                            filters: [{
                                property: 'companyid',
                                value: ''
                            }],
                        },
                    },
                    valueField: 'cardCode',
                    displayField: 'cardName',
                    reference: 'customercombox',
                    forceSelection: true,
                    editable: true,
                    allowBlank: false,
                    listeners: {
                        select: 'customerComboSelected',
                    }
                }]
            }],
        }],
    }, {
        xtype: 'tabpanel',
        flex: 1,
        reference: 'collectiontab',
        items: [{
            title: 'Collection Details',
            layout: 'column',
            margin: '8 8 8 8',
            //width: 800,
            items: [{
                xtype: 'container',
                width: 400,
                height: 300,
                layout: 'fit',
                items: [{
                    xtype: 'grid',
                    selType: 'checkboxmodel',
                    reference: 'pogrid',
                    title: 'Selected Open PO',
                    columns: [{
                        text: "GTP NO.",
                        dataIndex: "u_GTPREFNO"
                    }, {
                        text: "PO NO.",
                        dataIndex: "docNum"
                    }, {
                        text: "Total Weight",
                        dataIndex: "opndraft"
                    }, {
                        text: "Total",
                        dataIndex: "docTotal"
                    }, {
                        text: "Price",
                        dataIndex: "price"
                    }, {
                        text: "Comments",
                        dataIndex: "comments"
                    }, {
                        text: "Date",
                        dataIndex: "docDate"
                    }, ],
                    listeners: {
                        select: function (me, record, index, eOpts) {
                            this.lookupController().cmdc();
                        },
                        deselect: function (me, record, index, eOpts) {
                            this.lookupController().cmdc();
                        },
                    },
                }]
            }, {
                xtype: 'container',
                margin: '0 0 0 10',
                //width: 800,
                height: 300,
                layout: 'column',
                items: [{
                    width: 400,
                    height: 300,
                    xtype: 'multiselector',
                    title: 'Selected Rate Card Items',
                    reference: 'ratecardgird',
                    fieldName: 'u_itemcode',
                    columns: {
                        items: [{
                            text: "Item Code",
                            dataIndex: "u_itemcode"
                        }, {
                            text: "Purity",
                            dataIndex: "u_purity"
                        }, {
                            text: 'XAU Weight',
                            dataIndex: 'gtp_xauweight',
                        }, {
                            text: 'Gross Weight',
                            dataIndex: 'gtp_inputweight',
                        }],
                        defaults: {
                            flex: 1
                        }
                    },
                    forceFit: true,
                    bind: {
                        selection: '{theCompany2}',
                    },
                    viewConfig: {
                        deferEmptyText: false,
                        emptyText: 'No Rate Card selected'
                    },
                    search: {
                        listeners: {
                            add: function (a, b, c) {
                                var code = this.lookupController().getView().lookupReference('customercombox').value
                                var url = 'index.php?hdl=collection&action=getSapRateCardList' + '&code=' + code
                                this.getSearchStore().getProxy().setUrl(url)

                                if (this.getSearchStore().getProxy().url != url) {
                                    this.getView().lookupReference('pocontainer').getStore().removeAll()
                                    this.getSearchStore().removeAll();
                                    this.getSearchStore().load()
                                }
                            },
                        },
                        field: 'item_html_list',
                        width: 450,
                        height: 300,
                        store: {
                            alias: 'store.ratecardstore',
                            storeId: 'ratecardstore',
                            reference: 'ratecardstore',
                            model: 'snap.model.Ratecard',
                            sorters: 'name',
                            proxy: {
                                type: 'ajax',
                                limitParam: null,
                                url: 'index.php?hdl=collection&action=getSapRateCardList'
                            }
                        }
                    }
                }, {
                    xtype: 'fieldset',
                    title: 'Rate Card details',
                    //minWidth: 210,
                    width: 330,
                    columnWidth: 0.35,
                    margin: '0 0 0 10',
                    layout: 'fit',
                    defaultType: 'numberfield',

                    items: [{
                        fieldLabel: 'Weight',
                        bind: '{theCompany2.gtp_inputweight}',
                        reference: 'inputweight',
                        decimalPrecision: 3,
                        labelWidth: 60,
                        enableKeyEvents: true,
                        step: 0.001,
                        listeners: {
                            change: function (field, newVal, oldVal) {
                                var selectedRateCardItem = this.lookupReferenceHolder().lookupReference('ratecardgird').getSelection();
                                var purity = selectedRateCardItem[0].get('u_purity') * 100;
                                var inputWeight = newVal * 1000;
                                var xauWeight = (parseFloat(inputWeight) * parseFloat(purity));
                                xauWeight = (xauWeight / 10000000);
                                xauWeight = (Math.round( xauWeight * 1000 ) / 1000).toFixed(3);
                                this.lookupReferenceHolder().lookupReference('inputxauweight').setValue(xauWeight);

                                Ext.Function.createDelayed(function () {
                                    this.lookupController().cmdc();
                                }, 100, this)();
                            }
                        },
                    }, {
                        fieldLabel: 'Purity',
                        reference: 'u_purity',
                        bind: {
                            value: '{theCompany2.u_purity}',
                            readOnly: '{theCompany2.u_purity_readonly}',
                        },
                        decimalPrecision: 2,
                        labelWidth: 60,
                        enableKeyEvents: true,
                        step: 0.01,
                        listeners: {
                            change: function (field, newVal, oldVal) {
                                var selectedRateCardItem = this.lookupReferenceHolder().lookupReference('ratecardgird').getSelection();
                                var inputWeight = this.lookupReferenceHolder().lookupReference('inputweight').getValue() * 1000;
                                var purity = newVal * 100;
                                var xauWeight = (parseFloat(purity) * parseFloat(inputWeight));
                                xauWeight = (xauWeight / 10000000);
                                xauWeight = (Math.round( xauWeight * 1000 ) / 1000).toFixed(3);
                                this.lookupReferenceHolder().lookupReference('inputxauweight').setValue(xauWeight);

                                Ext.Function.createDelayed(function () {
                                    this.lookupController().cmdc();
                                }, 100, this)();
                            }
                        },
                    }, {
                        labelAlign: 'top',
                        fieldLabel: 'Total XAU Weight',
                        labelSeparator: '',
                        reference: 'inputxauweight',
                        bind: '{theCompany2.gtp_xauweight}',
                        decimalPrecision: 3,
                        readOnly: true
                    }]
                }]
            }, {
                xtype: 'fieldset',
                layout: {
                    type: 'hbox',
                    align: "flex",
                },
                margin: '0 0 0 20',
                defaults: {
                    margin: '0 20 0 20',
                    width: 180,
                    align: 'fit'
                },
                items: [{
                    xtype: 'displayfield',
                    fieldLabel: 'PO Weight',
                    reference: 'display_weight',
                    bind: '{total_poweight}'
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Balance XAU',
                    reference: 'display_balanceweight',
                    bind: '{total_balanceweight}',
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Gross Weight (input)',
                    reference: 'display_grossweight',
                    bind: '{total_inputweight}',

                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'AVG Purity',
                    reference: 'display_purity',
                    bind: '{total_purity}'
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Xau Weight (input)',
                    reference: 'display_xauweight',
                    bind: '{total_xauweight}'
                }, ]
            }, ]
        }, ]
    }, ]
});
