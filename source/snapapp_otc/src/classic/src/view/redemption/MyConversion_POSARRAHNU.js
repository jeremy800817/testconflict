Ext.define('snap.view.redemption.MyConversion_POSARRAHNU', {
    extend:'Ext.panel.Panel',
    xtype: 'conversionview_'+PROJECTBASE,
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/redemption',
    
    // requires: [
    //     'snap.store.Redemption',
    //     'snap.model.Redemption',
    //     'snap.view.redemption.RedemptionController',
    //     'snap.view.redemption.RedemptionModel',
    // ],
    scrollable:true,
    listeners: {
        afterrender: function () {
            elmnt = this;
            vm = this.getViewModel();

            // Get the function type
            originType = PROJECTBASE.toLowerCase();
            grid = this.lookupController().lookupReference('otcconversionview');
            storage = grid.items.items[0].getStore();
            storage.load();
            storage.on('load',function (store, records, successful, eOpts ){
                //Block of codes
     
                storage.getRange().forEach((item, index, array) => {
                    // populate rdm status to vm
                    check = array[index].data.rdmstatus;
                    switch(check) {
                        case 0:
                            // code pending
                            vm.set('status.pending', vm.get('status.pending') + 1);
                            break;
                        case 1:
                            // code confirmed
                            vm.set('status.confirmed', vm.get('status.confirmed') + 1);
                            break;
                        case 2:
                            // code pending
                            vm.set('status.completed', vm.get('status.completed') + 1);
                            break;
                        case 3:
                            // code confirmed
                            vm.set('status.failed', vm.get('status.failed') + 1);
                            break;
                        case 4:
                            // code confirmed
                            vm.set('status.processdelivery', vm.get('status.processdelivery') + 1);
                            break;
                        case 5:
                            // code confirmed
                            vm.set('status.cancelled', vm.get('status.cancelled') + 1);
                            break;
                        case 6:
                            // code confirmed
                            vm.set('status.reversed', vm.get('status.reversed') + 1);
                            break;
                        case 7:
                            // code confirmed
                            vm.set('status.faileddelivery', vm.get('status.faileddelivery') + 1);
                            break;

                        
                        default:
                          // code block
                          vm.set('status.success', vm.get('status.success') + 1);
                      }
              
                });

                //Block of codes
            });
      
            // snap.getApplication().sendRequest({
            //     hdl: 'redemption', action: 'list',
            //     origintype : originType,
            // }, 'Fetching data from server....').then(
            //     function (data) {          
            //         debugger;          
            //         if (data.success) {
            //            debugger;
            //             // Ext.get(redemptionpendingcount).dom.innerHTML  = data.pendingstatuscount;
            //             // Ext.get(redemptionconfirmedcount).dom.innerHTML  = data.confirmedstatuscount;
            //             // Ext.get(redemptioncompletedcount).dom.innerHTML  = data.completedstatuscount;
            //             // Ext.get(redemptionfaileddeliverycount).dom.innerHTML  = data.redemptionfaileddeliverycount;			
            //             // Ext.get(redemptiondeliverycount).dom.innerHTML  = data.deliverystatuscount;			
            //             // Ext.get(redemptioncancelledcount).dom.innerHTML  = data.cancelledstatus;
            //             // Ext.get(redemptionfailedapicount).dom.innerHTML  = data.failedstatuscount;
            //         }
            //     })
        },
    },
    viewModel: {
        data: {
            isAdmin: true,
         
            status: {
                pending: 0,
                confirmed: 0,
                completed: 0,
                faileddelivery: 0,
                processdelivery: 0,
                cancelled: 0,
                failed: 0,
                reversed: 0,
                success: 0,
            },
            // output: {
            //     companybuyamount: 0,
            //     companybuyxau: 0,
            //     companysellamount: 0,
            //     companysellxau: 0
            // },
            // 'profile-fullname': '-',
            // 'profile-id': '-',
            // 'profile-goldbalance': 0,
            // 'profile-minbalancexau': 0,
        }
    },
    items: {
        
        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        
        bodyPadding: 10,
    
        defaults: {
            frame: true,
            //bodyPadding: 10
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
            {
                xtype: 'displayfield',
                value: 'Redemption Status',
                margin: '0 0 0 10',
                fieldStyle : 'background-color:transparent !important; font-size:16px;border-color:transparent'
      
            },
            {
                xtype: 'container',
                scrollable: false,
                layout: {
                    type: 'hbox',
                    align: 'stretch',
                },
                defaults: {
                    bodyPadding: '10',
                    // border: true
                },
                // cls: 'otc-container',
                style: {
                    //backgroundColor: '#204A6D',
                    borderColor: '#red',
                },

                margin: '10 0 0 0',
                // height: '28%',
                minHeight: 90,
                maxHeight: 110,
                autoheight: true,
                items: [
                    {
                        xtype: 'panel',
                        reference: 'sellpanel',
                        cls: 'otc-main-left',
                        // hidden: true,
                        header: false,
                        flex: 13,
                        padding: '0 0 0 5',
                        margin: '0 10 0 10',
                        border: false,
                        items: [
                            {
                                // title: 'Account Holder',
                                layout: 'hbox',
                                width: '100%',
                                

                                items: [
                                    {
                                        layout: 'vbox',
                                        width: '100%',
                                        style: {
                                            'margin': '5px 5px 0px 5px',
                                        },
                                        items: [
                                            {
                                                xtype: 'displayfield',
                                                fieldCls: 'otc-displayfield-medium-text-dashboard-left',
                                                value: 'Pending',
                                            },
                                            {
                                                xtype: 'displayfield',
                                                fieldCls: 'otc-displayfield-large-text-dashboard-left-conversion',
                                                cls: 'conversion_overview',
                                                value: '0',
                                                bind: {
                                                    value: '{status.pending}',
                                                },
                                            },
                                        ],
                                    
                                    },
                                    
                                ]
                            },
                        ],
    
                        
                    },
                
                {
                    xtype: 'panel',
                    reference: 'sellpanel',
                    cls: 'otc-main-left',
                    // hidden: true,
                    header: false,
                    flex: 13,
                    padding: '0 0 0 5',
                    margin: '0 10 0 10',
                    border: false,
                    items: [
                        {
                            // title: 'Account Holder',
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 5px',
                                    },
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-left',
                                            value: 'Confirmed',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-left-conversion',
                                            cls: 'conversion_overview',
                                            value: '0',
                                            bind: {
                                                value: '{status.confirmed}',
                                            },
                                        },
                                    ],
                                
                                },
                                
                            ]
                        },
                    ],

                    
                },
            
                {
                    xtype: 'panel',
                    reference: 'sellpanel',
                    cls: 'otc-main-left',
                    // hidden: true,
                    header: false,
                    flex: 13,
                    padding: '0 0 0 5',
                    margin: '0 10 0 10',
                    border: false,
                    items: [
                        {
                            // title: 'Account Holder',
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 5px',
                                    },
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-left',
                                            value: 'Completed',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-left-conversion',
                                            cls: 'conversion_overview',
                                            value: '0',
                                            bind: {
                                                value: '{status.completed}',
                                            },
                                        },
                                    ],
                                
                                },
                                
                            ]
                        },
                    ],

                    
                },

                {
                    xtype: 'panel',
                    reference: 'sellpanel',
                    cls: 'otc-main-left',
                    // hidden: true,
                    header: false,
                    flex: 13,
                    padding: '0 0 0 5',
                    margin: '0 10 0 10',
                    border: false,
                    items: [
                        {
                            // title: 'Account Holder',
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 5px',
                                    },
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-left',
                                            value: 'Failed Delivery',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-left-conversion',
                                            cls: 'conversion_overview',
                                            value: '0',
                                            bind: {
                                                value: '{status.faileddelivery}',
                                            },
                                        },
                                    ],
                                
                                },
                                
                            ]
                        },
                    ],

                    
                },

                {
                    xtype: 'panel',
                    reference: 'sellpanel',
                    cls: 'otc-main-left',
                    // hidden: true,
                    header: false,
                    flex: 13,
                    padding: '0 0 0 5',
                    margin: '0 10 0 10',
                    border: false,
                    items: [
                        {
                            // title: 'Account Holder',
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 5px',
                                    },
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-left',
                                            value: 'Process Delivery',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-left-conversion',
                                            cls: 'conversion_overview',
                                            value: '0',
                                            bind: {
                                                value: '{status.processdelivery}',
                                            },
                                        },
                                    ],
                                
                                },
                                
                            ]
                        },
                    ],

                    
                },

                {
                    xtype: 'panel',
                    reference: 'sellpanel',
                    cls: 'otc-main-left',
                    // hidden: true,
                    header: false,
                    flex: 13,
                    padding: '0 0 0 5',
                    margin: '0 10 0 10',
                    border: false,
                    items: [
                        {
                            // title: 'Account Holder',
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 5px',
                                    },
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-left',
                                            value: 'Cancelled',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-left-conversion',
                                            cls: 'conversion_overview',
                                            value: '0',
                                            bind: {
                                                value: '{status.cancelled}',
                                            },
                                        },
                                    ],
                                
                                },
                                
                            ]
                        },
                    ],

                    
                },

                {
                    xtype: 'panel',
                    reference: 'sellpanel',
                    cls: 'otc-main-left',
                    // hidden: true,
                    header: false,
                    flex: 13,
                    padding: '0 0 0 5',
                    margin: '0 10 0 10',
                    border: false,
                    items: [
                        {
                            // title: 'Account Holder',
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [
                                {
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 5px',
                                        'text-align' : 'center',
                                    },
                                    items: [
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-left',
                                            value: 'Failed',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-left-conversion',
                                            cls: 'conversion_overview',
                                            value: '0',
                                            bind: {
                                                value: '{status.failed}',
                                            },
                                        },
                                    ],
                                
                                },
                                
                            ]
                        },
                    ],

                    
                },
                
            ]
            },
          
            {
                xtype: 'panel',
                title: 'Redemption Report',
                layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                scrollable:true,
                items: [
                    {
                        xtype: 'otcconversionview',
                        reference: 'otcconversionview',
                        // minHeight: 300,
                        // maxHeight: 1000,
                        height: Ext.getBody().getViewSize().height * 85/100,
                    }
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    }
});

