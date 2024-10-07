Ext.define('snap.view.redemption.MyConversionOTC', {
    extend:'snap.view.redemption.Redemption',
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/redemption',
    xtype: 'otcconversionview',
    viewModel: {
        type: 'redemption-redemption'
    },  
    store: { type: 'MyConversion' },
    items: [
        
        { 
            reference: 'redemptionreq', ui: 'redemptionrequests',  xtype: 'myconversionrequests', partnercode: PROJECTBASE,
            store: {
                
                type: 'MyConversion', proxy: {
                    type: 'ajax',
                    url: 'index.php?hdl=myconversion&action=list&partnercode='+PROJECTBASE,
                    reader: {
                        type: 'json',
                        rootProperty: 'records',
                    }
                },
            },
            height: Ext.getBody().getViewSize().height * 83/100,
            columns: [
                { text: 'ID', dataIndex: 'id', renderer: 'setTextColor' ,hidden:true,filter: { type: 'int' }},
                { text: 'Redemption ID', dataIndex: 'redemptionid', renderer: 'setTextColor',filter: { type: 'string' } },
                // { text: 'Partner Code', dataIndex: 'partnercode', hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                // { text: 'Partner', dataIndex: 'partnername', hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Gold Account No', dataIndex: 'accountholdercode', renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Customer Name', dataIndex: 'accountholdername', renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Redemption Type', dataIndex: 'rdmtype', renderer: 'setTextColor',filter: { type: 'string' },minWidth:130 },
                { text: 'Payment Status',  dataIndex: 'status', minWidth:130, renderer: 'setTextColor',
        
                    filter: {
                        type: 'combo',
                        store: [
                            ['0', 'Pending Payment'],
                            ['1', 'Paid'],
                            ['2', 'Expired'],
                            ['3', 'Payment Cancelled'],
                            ['4', 'Reversed'],
                        ],
                    },
                    renderer: function(value, rec){
                        if (value == 0) return '<span data-qtitle="Pending" data-qwidth="200" '+
                        'data-qtip="Redemption is pending for payment">'+
                        "Pending" +'</span>';
                        if (value == 1) return '<span data-qtitle="Paid" data-qwidth="200" '+
                        'data-qtip="Redemption has been paid">'+
                        "Paid" +'</span>';
                        if (value == 2) return '<span data-qtitle="Expired" data-qwidth="200" '+
                        'data-qtip="Redemption has expired">'+
                        "Expired" +'</span>';
                            if (value == 3) return '<span data-qtitle="Cancelled" data-qwidth="200" '+
                            'data-qtip="Redemption was cancelled by merchant">'+
                                "Cancelled" + '</span>';
                            if (value == 4) return '<span data-qtitle="Reversed" data-qwidth="200" '+
                            'data-qtip="Redemption was reversed">'+
                            "Cancelled" +'</span>';
                            else return '';
                    },
                },
                { text: 'Redemption Status',  dataIndex: 'rdmstatus', minWidth:130, renderer: 'setTextColor',
        
                        filter: {
                            type: 'combo',
                            store: [
                                ['0', 'Pending'],
                                ['1', 'Confirmed'],
                                ['2', 'Completed'],
                                ['3', 'Failed'],
                                ['4', 'Process Delivery'],
                                ['5', 'Cancelled'],
                                ['6', 'Reversed'],
                                ['7', 'Failed Delivery'],
                                //['Collecting', 10],
        
                            ],
        
                        },
                        renderer: function(value, rec){
                            if (value == 0) return '<span data-qtitle="Pending" data-qwidth="200" '+
                            'data-qtip="Redemption is pending for the next action">'+
                            "Pending" +'</span>';
                            if (value == 1) return '<span data-qtitle="Confirmed" data-qwidth="200" '+
                            'data-qtip="Redemption request is confirmed for delivery">'+
                            "Confirmed" +'</span>';
                            if (value == 2) return '<span data-qtitle="Completed" data-qwidth="200" '+
                            'data-qtip="Redemption is successful or delivered">'+
                            "Completed" +'</span>';
                            if (value == 3) return '<span style="color:#F42A12;" data-qtitle="Failed" data-qwidth="200" '+
                            'data-qtip="Branch for redemption failed">'+
                            "Failed" +'</span>';
                            if (value == 4) return '<span data-qtitle="Process Delivery" data-qwidth="200" '+
                            'data-qtip="Redemption is being processed for delivery">'+
                            "Process Delivery" +'</span>';
                            if (value == 5) return '<span data-qtitle="Cancelled" data-qwidth="200" '+
                            'data-qtip="Redemption was cancelled by merchant">'+
                            "Cancelled" +'</span>';
                            if (value == 6) return '<span data-qtitle="Reversed" data-qwidth="200" '+
                            'data-qtip="Logistic is being reversed">'+
                            "Reversed" +'</span>';
                            if (value == 7) return '<span style="color:#F42A12;" data-qtitle="Failed Delivery" data-qwidth="200" '+
                            'data-qtip="Logistic/Delivery for redemption failed">'+
                            "Failed Delivery" +'</span>';
                            else return '';
                    },
                },
                { text: 'Logistic Fee Payment Mode',  dataIndex: 'logisticfeepaymentmode', minWidth:130, renderer: 'setTextColor',
        
                        filter: {
                            type: 'combo',
                            store: [
                                ['CONTAINER', 'CONTAINER'],
                                ['FPX', 'FPX'],
                                ['GOLD', 'GOLD'],
                                ['WALLET', 'WALLET'],
                                //['Collecting', 10],
        
                            ],
        
                        },
                        renderer: function(value, rec){
                            if (value == 'CONTAINER') return 'CONTAINER';
                            if (value == 'FPX') return 'FPX';
                            if (value == 'GOLD') return 'GOLD';
                            if (value == 'WALLET') return 'WALLET';
                            else return '';
                    },
                },
                { text: 'Remarks', dataIndex: 'rdmremarks',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Partner ID', dataIndex: 'rdmpartnerid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Branch Code', dataIndex: 'partnercode', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Branch Name', dataIndex: 'partnername', hidden:true, minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Product ID', dataIndex: 'rdmproductid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Partner Ref No', dataIndex: 'rdmpartnerrefno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' },
                    renderer: function(value){
                        return '<span style="color:#006600;">' + value + '</span>';
                    }
                },
                { text: 'Redemption No', dataIndex: 'rdmredemptionno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' },
                    renderer: function(value){
                        return '<span style="color:#006600;">' + value + '</span>';
                    } 
                },
                { text: 'API Version', dataIndex: 'apiversion',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        
        
                { text: 'Premium Fee', dataIndex: 'premiumfee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                editor: {    //field has been deprecated as of 4.0.5
                    xtype: 'numberfield',
                    decimalPrecision: 3
                    }
                },
                { text: 'Handling Fee', dataIndex: 'handlingfee',  exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                editor: {    //field has been deprecated as of 4.0.5
                    xtype: 'numberfield',
                    decimalPrecision: 2
                }   },
                { text: 'Insurance Fee', dataIndex: 'rdminsurancefee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                editor: {    //field has been deprecated as of 4.0.5
                    xtype: 'numberfield',
                    decimalPrecision: 2
                    }
                },
                { text: 'Packing and Shipment', dataIndex: 'courierfee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                    editor: {
                        xtype: 'numberfield',
                        decimalPrecision: 2
                    }
                },
                { text: 'Marketing Fund', dataIndex: 'commissionfee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                    editor: {
                        xtype: 'numberfield',
                        decimalPrecision: 2
                    }
                },
                { text: 'Total Fee', dataIndex: 'rdmtotalfee', exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 150, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                    editor: {
                        xtype: 'numberfield',
                        decimalPrecision: 2
                    }
                },
                // { text: 'Special Delivery Fee', dataIndex: 'rdmspecialdeliveryfee',  exportdecimal:2, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.00'),
                // editor: {    //field has been deprecated as of 4.0.5
                //     xtype: 'numberfield',
                //     decimalPrecision: 2
                // }   },
                /*
                { text: 'Brand', dataIndex: 'xaubrand', renderer: 'setTextColor',hidden:true,filter: { type: 'string' } },
                { text: 'Serial No', dataIndex: 'xauserialno',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'XAU', dataIndex: 'xau', renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                editor: {    //field has been deprecated as of 4.0.5
                    xtype: 'numberfield',
                    decimalPrecision: 3
                }   },      */ 
                //{ text: 'Booking On', dataIndex: 'bookingon',minWidth:130, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },
                { text: 'Booking On', dataIndex: 'rdmbookingon',minWidth:130, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
                { text: 'Booking Price', dataIndex: 'rdmbookingprice',  exportdecimal:3, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                editor: {    //field has been deprecated as of 4.0.5
                    xtype: 'numberfield',
                    decimalPrecision: 3
                }  },
                { text: 'Booking Price Strem ID', dataIndex: 'rdmbookingpricestreamid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                //{ text: 'Confirm On', dataIndex: 'confirmon',minWidth:130, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },  
                { text: 'Confirm On', dataIndex: 'rdmconfirmon',minWidth:130, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
                { text: 'Confirm Pricestream ID', dataIndex: 'rdmconfirmpricestreamid',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Confirmed Price', dataIndex: 'rdmconfirmprice',  exportdecimal:3, renderer: 'setTextColor',filter: { type: 'string' }, align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                editor: {    //field has been deprecated as of 4.0.5
                    xtype: 'numberfield',
                    decimalPrecision: 3
                }   },
                { text: 'Confirm Reference', dataIndex: 'rdmconfirmreference',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Delivery Address', dataIndex: 'rdmdeliveryaddress',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
                //{ text: 'Delivery Address 1', dataIndex: 'deliveryaddress1',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
                //{ text: 'Delivery Address 2', dataIndex: 'deliveryaddress2',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                //{ text: 'Delivery Address 3', dataIndex: 'deliveryaddress3',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Delivery Postcode', dataIndex: 'rdmdeliverypostcode',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Delivery State', dataIndex: 'rdmdeliverystate',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Delivery Country', dataIndex: 'rdmdeliverycountry',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },
                { text: 'Delivery Contact Name 1', dataIndex: 'rdmdeliverycontactname1',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },     
                { text: 'Delivery Contact Name 2', dataIndex: 'rdmdeliverycontactname2',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },    
                { text: 'Delivery Contact No 1', dataIndex: 'rdmdeliverycontactno1',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },     
                { text: 'Delivery Contact No 2', dataIndex: 'rdmdeliverycontactno2',minWidth:130, renderer: 'setTextColor',filter: { type: 'string' } },    
                //{ text: 'Processed On', dataIndex: 'processedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },
                //{ text: 'Delivered On', dataIndex: 'deliveredon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' } },
                //{ text: 'Created On', dataIndex: 'createdon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date' }, },
        
                //{ text: 'Processed On', dataIndex: 'rdmprocessedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
                //{ text: 'Delivered On', dataIndex: 'rdmdeliveredon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' } },
                { text: 'Created On', dataIndex: 'createdon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' }, },
        
                { text: 'Created By', dataIndex: 'createdby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
                //{ text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('d/m/Y H:i'),filter: { type: 'date'} },
                { text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date'} },
                { text: 'Modified By', dataIndex: 'modifiedby',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        
            ],
        },
        
        // { reference: 'redemptionsummary', ui: 'redemptionsummary',  xtype: 'redemptionsummary', type: 'go'},
    ]
});
