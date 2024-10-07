Ext.define('MyApp.view.MyTabView', {
    extend: 'Ext.tab.Panel',
    xtype: 'myreconcilreportview_ALRAJHI',
    
    items: [
        {
            title: 'Match transaction',
            xtype: 'grid',
            columns: [
                { text: 'Alrajhi TransactionTime', dataIndex: 'transdatec', flex: 1 },
                { text: 'Alrajhi TransactionCode', dataIndex: 'txncode', flex: 1 },
               // { text: 'Alrajhi Gram', dataIndex: '', flex: 1 },
              //  { text: 'Alrajhi Reversal', dataIndex: '', flex: 1 },
                { text: 'Alrajhi Amount', dataIndex: 'gtpamountc', flex: 1 },
                { text: 'GTP TransactionTime', dataIndex: 'transdates', flex: 1 },
                { text: 'GTP TransactionCode', dataIndex: 'transactioncodes', flex: 1 },
              //  { text: 'GTP PriceQueryId', dataIndex: '', flex: 1 },
                { text: 'GTP Type', dataIndex: 'typeoftransactioncs', flex: 1 },
                { text: 'GTP gram', dataIndex: 'gtpgrams', flex: 1 },
              //  { text: 'GTP Reversal', dataIndex: '', flex: 1 },
                { text: 'GTP GoldPrice (P1)', dataIndex: 'gtpgoldprices', flex: 1 },      
                { text: 'GTP Amount', dataIndex: 'gtpamounts', flex: 1 }, 
            ],
            store: {
                type: "MyOrder",
                pageSize: 25,
                proxy: {
                    type: "ajax",
                      url:'index.php?hdl=reconcilreporthandler&action=matchtransaction&partnercode=' + PROJECTBASE,
                    reader: {
                        type: "json",
                        rootProperty: "records",
                    },
                },
            },
                dockedItems: [{
                xtype: 'pagingtoolbar',
                dock: 'top',
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: 'No records to display',
                items: [
                    {
                        xtype: 'button',
                        text: 'Refresh',
                        handler: function () {
                            // Logic to refresh the grid data
                            var textField = Ext.ComponentQuery.query('#filterDate')[0];
                            textField.setValue(''); 
                        }
                    },
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Select Date',
                        labelWidth: 80,
                        itemId: 'filterDate'
                    },
                    {
                        xtype: 'button',
                        text: 'Apply',
                        handler: function () {
                            var grid = this.up('grid');
                            var filterDate = grid.down('#filterDate').getValue();

                            // Logic to apply the date filter and retrieve data
                            grid.getStore().load({
                                params: {
                                    'transdatec': filterDate
                                }
                            });
                        }
                    }

                ]
            }],  
        },
   
      {
            title: 'Missing transaction from Alrajhi',
            xtype: 'grid',
            columns: [
                // { text: 'Alrajhi TransactionTime', dataIndex: 'transdatec', flex: 1 },
                // { text: 'Alrajhi TransactionCode', dataIndex: 'txncode', flex: 1 },
                // { text: 'Alrajhi Gram', dataIndex: '', flex: 1 },
                // { text: 'Alrajhi Reversal', dataIndex: '', flex: 1 },
                // { text: 'Alrajhi Amount', dataIndex: 'gtpamounts', flex: 1 },
                { text: 'GTP TransactionTime', dataIndex: 'transdates', flex: 1 },
                { text: 'GTP TransactionCode', dataIndex: 'transactioncodes', flex: 1 },
              //  { text: 'GTP PriceQueryId', dataIndex: '', flex: 1 },
                { text: 'GTP Type', dataIndex: 'typeoftransactioncs', flex: 1 },
                { text: 'GTP Gram', dataIndex: 'gtpgrams', flex: 1 },
               // { text: 'GTP Reversal', dataIndex: '', flex: 1 },
                { text: 'GTP GoldPrice (P1)', dataIndex: 'gtpgoldprices', flex: 1 },      
                { text: 'GTP Amount', dataIndex: 'gtpamounts', flex: 1 }, 
            ],
            store: {
                type: "MyOrder",
                pageSize: 25,
                proxy: {
                    type: "ajax",
                      url:'index.php?hdl=reconcilreporthandler&action=missingmbb&partnercode=' + PROJECTBASE,
                    reader: {
                        type: "json",
                        rootProperty: "records",
                    },
                },
            },
                dockedItems: [{
                xtype: 'pagingtoolbar',
                dock: 'top',
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: 'No records to display',
                items: [
                    {
                        xtype: 'button',
                        text: 'Refresh',
                        handler: function () {
                            // Logic to refresh the grid data
                            this.up('grid').getStore().load();
                            var textField = Ext.ComponentQuery.query('#filterDate')[0];
                            textField.setValue(''); 
                        }
                    },
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Select Date',
                        labelWidth: 80,
                        itemId: 'filterDate'
                    },
                    {
                        xtype: 'button',
                        text: 'Apply',
                        handler: function () {
                            var grid = this.up('grid');
                            var filterDate = grid.down('#filterDate').getValue();

                            // Logic to apply the date filter and retrieve data
                            grid.getStore().load({
                                params: {
                                    'transdatec': filterDate
                                }
                            });
                        }
                    }

                ]
            }],  
        },
        {
            title: 'Missing transaction from GTP',
            xtype: 'grid',
            columns: [
                { text: 'Alrajhi TransactionTime', dataIndex: 'transdatec', flex: 1 },
                { text: 'Alrajhi TransactionCode', dataIndex: 'txncode', flex: 1 },
              //  { text: 'Alrajhi Gram', dataIndex: '', flex: 1 },
              //  { text: 'Alrajhi Reversal', dataIndex: '', flex: 1 },
                { text: 'Alrajhi Amount', dataIndex: 'gtpamountc', flex: 1 },
                //            { text: 'GTP TransactionTime', dataIndex: 'transdates', flex: 1 },
                //          { text: 'GTP TransactionCode', dataIndex: 'transactioncodes', flex: 1 },
              //  { text: 'GTP PriceQueryId', dataIndex: '', flex: 1 },
            //                { text: 'GTP Type', dataIndex: 'typeoftransactioncs', flex: 1 },
            //              { text: 'GTP Gram', dataIndex: 'gtpgrams', flex: 1 },
               // { text: 'GTP Reversal', dataIndex: '', flex: 1 },
                // { text: 'GTP GoldPrice (P1)', dataIndex: 'gtpgoldprices', flex: 1 },      
                // { text: 'GTP Amount', dataIndex: 'gtpamounts', flex: 1 }, 
            ],
            store: {
                type: "MyOrder",
                pageSize: 25,
                proxy: {
                    type: "ajax",
                      url:'index.php?hdl=reconcilreporthandler&action=missinggpt&partnercode=' + PROJECTBASE,
                    reader: {
                        type: "json",
                        rootProperty: "records",
                    },
                },
            },
                dockedItems: [{
                xtype: 'pagingtoolbar',
                dock: 'top',
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: 'No records to display',
                items: [
                    {
                        xtype: 'button',
                        text: 'Refresh',
                        handler: function () {
                            // Logic to refresh the grid data
                            this.up('grid').getStore().load();
                            var textField = Ext.ComponentQuery.query('#filterDate')[0];
                            textField.setValue(''); 
                        }
                    },
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Select Date',
                        labelWidth: 80,
                        itemId: 'filterDate'
                    },
                    {
                        xtype: 'button',
                        text: 'Apply',
                        handler: function () {
                            var grid = this.up('grid');
                            var filterDate = grid.down('#filterDate').getValue();

                            // Logic to apply the date filter and retrieve data
                            grid.getStore().load({
                                params: {
                                    'transdatec': filterDate
                                }
                            });
                        }
                    }

                ]
            }],  
        },
        {
            title: 'Unmatch transaction satus',
            xtype: 'grid',
            columns: [
            //     { text: 'Alrajhi TransactionTime', dataIndex: '', flex: 1 },
            //     { text: 'Alrajhi TransactionCode', dataIndex: 'txncode', flex: 1 },
            //    // { text: 'Alrajhi Gram', dataIndex: '', flex: 1 },
            //   //  { text: 'Alrajhi Reversal', dataIndex: '', flex: 1 },
            //     { text: 'Alrajhi Amount', dataIndex: '', flex: 1 },
            //     { text: 'GTP TransactionTime', dataIndex: 'transdates', flex: 1 },
            //     { text: 'GTP TransactionCode', dataIndex: 'transactioncodes', flex: 1 },
            //   //  { text: 'GTP PriceQueryId', dataIndex: '', flex: 1 },
            //     { text: 'GTP Type', dataIndex: 'typeoftransactioncs', flex: 1 },
            //     { text: 'GTP gram', dataIndex: 'gtpgrams', flex: 1 },
            //   //  { text: 'GTP Reversal', dataIndex: '', flex: 1 },
            //     { text: 'GTP GoldPrice (P1)', dataIndex: 'gtpgoldprices', flex: 1 },      
            //     { text: 'GTP Amount', dataIndex: 'gtpamounts', flex: 1 }, 
            ],
            store: {
                type: "MyOrder",
                pageSize: 25,
                proxy: {
                    type: "ajax",
                      url:'index.php?hdl=reconcilreporthandler&action=matchtransaction&partnercode=' + PROJECTBASE,
                    reader: {
                        type: "json",
                        rootProperty: "records",
                    },
                },
            },
                dockedItems: [{
                xtype: 'pagingtoolbar',
                dock: 'top',
                displayInfo: true,
                displayMsg: 'Displaying records {0} - {1} of {2}',
                emptyMsg: 'No records to display',
                items: [
                    {
                        xtype: 'button',
                        text: 'Refresh',
                        handler: function () {
                            // Logic to refresh the grid data
                            var textField = Ext.ComponentQuery.query('#filterDate')[0];
                            textField.setValue(''); 
                        }
                    },
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Select Date',
                        labelWidth: 80,
                        itemId: 'filterDate'
                    },
                    {
                        xtype: 'button',
                        text: 'Apply',
                        handler: function () {
                            var grid = this.up('grid');
                            var filterDate = grid.down('#filterDate').getValue();

                            // Logic to apply the date filter and retrieve data
                            grid.getStore().load({
                                params: {
                                    'transdatec': filterDate
                                }
                            });
                        } 
                    }

                ]
            }],  
        },

        
    ]
});

// Ext.application({
//     name: 'MyApp',
//     launch: function () {
//         Ext.create('MyApp.view.MyTabView', {
//             fullscreen: true
//         });
//     }
// });