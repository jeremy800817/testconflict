
Ext.define('snap.view.partner.Partner',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'partnerview',
    requires: [
        'snap.store.Partner',
        'snap.model.Partner',        
        'snap.view.partner.PartnerController',
        'snap.view.partner.PartnerModel',  
        'snap.store.ProductItems',
        'snap.model.ProductItems', 
                
    ],
    permissionRoot: '/root/system/partner',
    store: { type: 'Partner' },
    controller: 'partner-partner',
    viewModel: {
        type: 'partner-partner'
    },
    enableFilter: true,   
    listeners: {
        afterrender: function () {
            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);
            var columns=this.query('gridcolumn');             
            columns.find(obj => obj.text === 'ID').setVisible(false);
        }
    }, 
    toolbarItems: [
        'add', 'edit', 'detail', '|', 'delete', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            text: 'Download',tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'downloadpartners', handler: 'getPartnerExport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        },
        {
            iconCls: 'x-fa fa-redo-alt', text: 'Filter Date', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
        },
    ],
    columns: [
        { text: 'ID',  dataIndex: 'id', hidden: true, filter: {type: 'int'  } , flex: 1 },
        { text: 'Code',  dataIndex: 'code', filter: {type: 'string'  } , flex: 1 },
        { text: 'Name', dataIndex: 'name', filter: {type: 'string'  }, flex: 1, renderer: 'boldText' },		
        { text: 'Address', dataIndex: 'address', filter: {type: 'string'  }, flex: 1 },	
        { text: 'Postcode', dataIndex: 'postcode', filter: {type: 'string'  }, hidden: true},	
        { text: 'State', dataIndex: 'state', filter: {type: 'string'  }, flex: 1 },	
        { text: 'Type', dataIndex: 'type', filter: {type: 'string'  }, hidden: true},
        { text: 'Core Partner', dataIndex: 'corepartner',
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Non Core'],
                    ['1', 'Core'],
                ],

            },
            renderer: function(value, rec){
                if(value=='1') return 'Core Partner';
                else return 'Non Core';
            },    
        },
        //{ text: 'Branch Name', dataIndex: 'branchname', filter: {type: 'string'  }, hidden: true},
        //{ text: 'Price Source ID',  dataIndex: 'pricesourceid', filter: {type: 'int'  } , hidden: true},
        { text: 'Price Source',  dataIndex: 'pricesourcename', filter: {type: 'int'  } , hidden: true},
       //{ text: 'Sales Person ID',  dataIndex: 'salespersonid', filter: {type: 'int'  }},
        { text: 'Salesperson',  dataIndex: 'salespersonname', filter: {type: 'string'  }},
        //{ text: 'Trading Schedule ID',  dataIndex: 'tradingscheduleid', filter: {type: 'int'  } , hidden: true},
        { text: 'Trading Schedule',  dataIndex: 'tradingschedulename', filter: {type: 'string'  } , hidden: true},
        { text: 'Company Sell Code 1', dataIndex: 'sapcompanysellcode1', filter: {type: 'string'  }, hidden: true},
        { text: 'Company Buy Code 1', dataIndex: 'sapcompanybuycode1', filter: {type: 'string'  }, hidden: true},
        { text: 'Company Sell Code 2', dataIndex: 'sapcompanysellcode2', filter: {type: 'string'  }, hidden: true},
        { text: 'Company Buy Code 2', dataIndex: 'sapcompanybuycode2', filter: {type: 'string'  }, hidden: true},
        { text: 'Daily Buy Limit', dataIndex: 'dailybuylimitxau', filter: {type: 'string'  }},
        { text: 'Daily Sell Limit', dataIndex: 'dailyselllimitxau', filter: {type: 'string'  }},
        { text: 'Price Lapse Time Allowance',  dataIndex: 'pricelapsetimeallowance', filter: {type: 'int'  } , hidden: true },
        { text: 'Ordering Mode', dataIndex: 'orderingmode', filter: {type: 'string'  }, hidden: true},
        //{ text: 'Auto Submit Order',  dataIndex: 'autosubmitorder', filter: {type: 'int'  } , hidden: true },
        { text: 'Auto Submit Order',  dataIndex: 'autosubmitorder', flex: 1,
                filter: {
                    type: 'combo',
                    store: [
                        ['0', 'Inactive'],
                        ['1', 'Active'],
                    ],

                },
                renderer: function(value, rec){
                    if(value=='1') return 'Active';
                    else return 'Inactive';
                },
        },
        // text: 'Auto Create Matched Order',  dataIndex: 'autocreatematchedorder', filter: {type: 'int'  } , hidden: true },
        { text: 'Auto Create Matched Order',  dataIndex: 'autocreatematchedorder', flex: 1,
                filter: {
                    type: 'combo',
                    store: [
                        ['0', 'Inactive'],
                        ['1', 'Active'],
                    ],

                },
                renderer: function(value, rec){
                    if(value=='1') return 'Active';
                    else return 'Inactive';
                },
        },
        { text: 'Order Confirm Allowance',  dataIndex: 'orderconfirmallowance', filter: {type: 'int'  } , hidden: true },
        { text: 'Order Cancel Allowance',  dataIndex: 'ordercancelallowance', filter: {type: 'int'  } , hidden: true },
        { text: 'Calculator Mode', dataIndex: 'calculatormode', filter: {type: 'string'  }, hidden: true },		
        { text: 'Status', dataIndex: 'status',             
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
    formClass: 'snap.view.partner.PartnerGridForm'
});
