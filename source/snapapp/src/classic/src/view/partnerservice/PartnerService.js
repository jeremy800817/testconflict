
Ext.define('snap.view.partnerservice.PartnerService',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'partnerdailylimitview',
    requires: [
        'snap.store.PartnerService',
        'snap.model.PartnerService',        
        'snap.view.partnerservice.PartnerServiceController',
        'snap.view.partnerservice.PartnerServiceModel',  
        // 'snap.store.ProductItems',
        // 'snap.model.ProductItems', 
                
    ],
    permissionRoot: '/root/gtp/partnerlimits',
    store: { type: 'PartnerService' },
    controller: 'partnerservice-partnerservice',
    viewModel: {
        type: 'partnerservice-partnerservice'
    },
    enableFilter: true,    
    // rowexpander: {
    //     ptype: 'rowexpander', //rowexpander: true, //ptype: 'rowexpandergrid', 
    //     pluginId: 'rowexpander',
    //     expandOnDblClick: true,
    //     selectRowOnExpand: false,
    //     expandRow: function(rowIdx) {
    //         var rowNode = this.view.getNode(rowIdx),
    //             row = Ext.get(rowNode),
    //             nextBd = Ext.get(row).down(this.rowBodyTrSelector),
    //             record = this.view.getRecord(rowNode),
    //             grid = this.getCmp();
    //         if (row.hasCls(this.rowCollapsedCls)) {
    //             row.removeCls(this.rowCollapsedCls);
    //             nextBd.removeCls(this.rowBodyHiddenCls);
    //             this.recordsExpanded[record.internalId] = true;
    //             this.view.fireEvent('expandbody', rowNode, record, nextBd.dom);
    //         }
    //     },
    
    //     collapseRow: function(rowIdx) {
    //         var rowNode = this.view.getNode(rowIdx),
    //             row = Ext.get(rowNode),
    //             nextBd = Ext.get(row).down(this.rowBodyTrSelector),
    //             record = this.view.getRecord(rowNode),
    //             grid = this.getCmp();
    //         if (!row.hasCls(this.rowCollapsedCls)) {
    //             row.addCls(this.rowCollapsedCls);
    //             nextBd.addCls(this.rowBodyHiddenCls);
    //             this.recordsExpanded[record.internalId] = false;
    //             this.view.fireEvent('collapsebody', rowNode, record, nextBd.dom);
    //         }
    //     },

    //     rowBodyTpl: Ext.create('Ext.XTemplate',
    //     '<table style="width: 400px;border: 1px solid #d2d2d2;margin-bottom:15px;border-radius:5px;box-shadow: 0px 1px rgba(0,0,0,0.1);margin-left: 20px">',
    //         '<tr>',
    //             '<th style="text-align:center; width:200px">SAP docNum</th>',
    //             '<th style="text-align:center; width:200px">SAP GTPREFNO</th>',
    //         '</tr>',
    //         '{child}',
    //     '</table>'
    //     )                                      
    // },
    toolbarItems:[
        // 'add', 'detail', 'filter', '|',
        'detail', 'filter', '|',
        // {
        //     xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        // },
        // {
        //     xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        // },
        {
            handlerModule: 'collection', text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        }
    ],
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns=this.query('gridcolumn');             
            columns.find(obj => obj.text === 'ID').setVisible(false);
        },
        // afterlayout: function(grid){
        //     // console.log(this,grid,'this',grid.getPlugin("rowexpander").view.getNodes());
        //     var rowExpander = grid.getPlugin("rowexpander")
        //     var nodes = rowExpander.view.getNodes()
        //     for (var i = 0; i < nodes.length; i++) {
        //         rowExpander.expandRow(i);
        //     } 
        // }
    },
    columns: [
        { text: 'ID',  dataIndex: 'id', hidden: true, filter: {type: 'int'  } , flex: 1 },
        { text: 'Code',  dataIndex: 'partnercode', filter: {type: 'string'  } , flex: 1 },
        { text: 'Partner',  dataIndex: 'partnername', filter: {type: 'string'  }},
        { text: 'Product',  dataIndex: 'productname', filter: {type: 'string'  }},
        { text: 'Product Code',  dataIndex: 'productcode', hidden: true, filter: {type: 'string'  }},
        // { text: 'Comments',  dataIndex: 'comments', filter: {type: 'string'  }},

        // Get the 8 fields for daily limit
        { text: 'Refinery Fee', dataIndex: 'refineryfee', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Premium Fee', dataIndex: 'premiumfee', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Daily Buy Limit Xau', dataIndex: 'dailybuylimitxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Daily Sell Limit Xau', dataIndex: 'dailyselllimitxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        // Get Balances
        { text: 'Buy Balance', dataIndex: 'buybalance', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Sell Balance', dataIndex: 'sellbalance', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        // End Balances
        { text: 'Buy Click Max Xau', dataIndex: 'buyclickmaxxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Sell Click Max Xau', dataIndex: 'sellclickmaxxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Buy Click Min Xau', dataIndex: 'buyclickminxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Sell Click Min Xau', dataIndex: 'sellclickminxau', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        // End fields
       
        { text: 'Redemption Premium Fee', hidden: true, dataIndex: 'redemptionpremiumfee', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Redemption Comission', hidden: true, dataIndex: 'redemptioncommission', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },

        { text: 'Redemption Insurance Fee', hidden: true,  dataIndex: 'redemptioninsurancefee', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },

        { text: 'Redemption Handling Fee', hidden: true, dataIndex: 'redemptionhandlingfee', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        
        // Can buy / Sell
        {
            text: 'Include Fee In Price', dataIndex: 'includefeeinprice', hidden: true, filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return 'Yes';
                else return 'No';
            },
        },
        {
            text: 'Can Buy', dataIndex: 'canbuy', hidden: true, filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return 'Yes';
                else return 'No';
            },
        },
        {
            text: 'Can Sell', dataIndex: 'cansell', hidden: true, filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return 'Yes';
                else return 'No';
            },
        },
        {
            text: 'Can Queue', dataIndex: 'canqueue', hidden: true, filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return 'Yes';
                else return 'No';
            },
        },
        {
            text: 'Can Redeem', dataIndex: 'canredeem', hidden: true, filter: { type: 'string' }, minWidth: 100,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'No'],
                    ['1', 'Yes'],
                ],
            },
            renderer: function (value, rec) {
                if (value == true) return 'Yes';
                else return 'No';
            },
        },

       
      
        { text: 'Special Price Company Buy Offset', hidden: true, dataIndex: 'specialpricecompanybuyoffset', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Special Price Company Sell Offset', hidden: true, dataIndex: 'specialpricecompanyselloffset', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Created On', dataIndex: 'createdon',  xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1},
        { text: 'Modified On', dataIndex: 'modifiedon',  xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1, hidden: true},
        { text: 'Created By', dataIndex: 'createdbyname', filter: {type: 'string'}, hidden: true },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: {type: 'string'}, hidden: true },
        { text: 'Status', dataIndex: 'status',    hidden: true,         
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Active'],
                    ['3', 'Rejected'],
                ],
            },
            renderer: function(value, rec){
                if(value=='0') return 'Pending';
                if(value=='1') return 'Active';
                if(value=='3') return 'Rejected';
                else return '';
            },
        },	     
    ],

     //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    enableDetailView: true,
    detailViewWindowHeight: 500,
    detailViewWindowWidth: 500,
    style: 'word-wrap: normal',
    detailViewSections: { default: 'Properties' },
    detailViewUseRawData: true,

    
    formClass: 'snap.view.collection.CollectionGridForm'
});
