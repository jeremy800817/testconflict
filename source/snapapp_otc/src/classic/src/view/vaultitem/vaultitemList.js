Ext.define('snap.view.vaultitem.vaultitemList', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'vaultitemview',
    requires: [
        'snap.store.VaultItem',
        'snap.model.VaultItem',
        'snap.view.vaultitem.vaultitemController',
        'snap.view.vaultitem.vaultitemModel',
        'Ext.view.MultiSelector'
    ],
    controller: 'vaultitem',
    viewModel: {
        type: 'vaultitem',
    },
    gridSelectionMode: 'SINGLE',
    allowDeselect:true,
    store: { type: 'VaultItem', autoLoad: true },
    //permissionRoot: '/root/mbb/vault',
    enableFilter: true,
    /*toolbarItems: [
        'detail','filter', '|',
        { reference: 'reqtransferbutton',text: 'Transfer Item', itemId: 'transferitem',iconCls: 'fa fa-arrows-alt-h', handler: 'showTransferForm', validSelection: 'single' },
        { reference: 'confirmtransferbutton', text: 'Confirm Transfer', itemId: 'confirmtransfer', iconCls: 'x-fa fa-check-square', handler: 'confirmTransferOrReturn', validSelection: 'single' },
        '|',
        { reference: 'cancelreqtransferbutton', text: 'Cancel Request Transfer', itemId: 'cancelreqtransfer', iconCls: 'x-fa fa-stop-circle', handler: 'cancelTransfer', validSelection: 'single' },
        '|',
        { reference: 'returnbutton', itemId: 'returnitem', text: 'Return Item', iconCls: 'fa fa-reply', handler: 'returnItem', validSelection: 'single' },
        '|',
        {reference: 'printButton', text: 'Print Document', itemId: 'printButton', tooltip: 'Print Documents', iconCls: 'x-fa fa-print', handler: 'printButton', validSelection: 'single' },
        {reference: 'createPrintButton', text: 'Create Document', itemId: 'createPrintButton', tooltip: 'Print Documents', iconCls: 'x-fa fa-book', handler: 'createPrintButton', validSelection: 'single' },
    ],*/

    listeners: {        /*
        cellclick: function (view, cell, cellIndex, record, row, rowIndex, e) {   
            var me = this;
            me.checkActionPermission(view, record);
        },
        beforeitemkeyup: function (view, record, item, index, e) {            
            var me = this;
            me.checkActionPermission(view, record);
        },
        afterrender: function(store) {            
            var btntransferitem = Ext.ComponentQuery.query('#transferitem')[0];
            var btnconfirmtransfer = Ext.ComponentQuery.query('#confirmtransfer')[0];
            var btnreturn = Ext.ComponentQuery.query('#returnitem')[0];
            var btncanceltransfer = Ext.ComponentQuery.query('#cancelreqtransfer')[0];
            Ext.create('Ext.tip.ToolTip', {
                target: btntransferitem.getEl(),
                html: 'Transfer Item'
            });
            Ext.create('Ext.tip.ToolTip', {
                target: btnconfirmtransfer.getEl(),
                html: 'Confirm Transfer'
            });
            Ext.create('Ext.tip.ToolTip', {
                target: btnreturn.getEl(),
                html: 'Return Item'
            });
            Ext.create('Ext.tip.ToolTip', {
                target: btncanceltransfer.getEl(),
                html: 'Cancel Transfer'
            });

            this.store.sorters.clear();
            this.store.sort([{
                property: 'id',
                direction: 'DESC'
            }]);  
           
            // Enable fields
            var grid = this;           
            var columns=grid.query('gridcolumn');  
            columns.find(obj => obj.dataIndex === 'vaultlocationname').setVisible(true);
            columns.find(obj => obj.dataIndex === 'movetovaultlocationid').setVisible(true);
        }*/
    },
    checkActionPermission: function (view, record) {
        var selected = false;
        Ext.Array.each(view.getSelectionModel().getSelection(), function (items) {
            if (items.getId() == record.getId()) {
                selected = true;
                return false;
            }
        });

        var btntransferitem = Ext.ComponentQuery.query('#transferitem')[0];
        var btnconfirmtransfer = Ext.ComponentQuery.query('#confirmtransfer')[0];
        var btnreturn = Ext.ComponentQuery.query('#returnitem')[0];
        var btncanceltransfer = Ext.ComponentQuery.query('#cancelreqtransfer')[0];
        btntransferitem.disable();
        btnconfirmtransfer.disable();
        btnreturn.disable();
        btncanceltransfer.disable();
        var transferPermission = snap.getApplication().hasPermission('/root/mbb/vault/transfer');
        var returnPermission = snap.getApplication().hasPermission('/root/mbb/vault/return');
        if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.vaultlocationid != 0) {
            btntransferitem.enable();            
        }
        if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 1) && record.data.vaultlocationid == 0) {
            btntransferitem.enable();            
        }
        if (transferPermission == true && selected && record.data.status == 2 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.movetovaultlocationid != null && record.data.movetovaultlocationid != 1) {
            btnconfirmtransfer.enable();            
        }
        if (returnPermission == true && selected && record.data.status == 2 && record.data.allocated == 0 && record.data.vaultlocationid == 2 && record.data.movetovaultlocationid == 1) {
            btnreturn.enable();            
        }
        if (transferPermission == true && selected && record.data.status == 2 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.movetovaultlocationid != null && record.data.movetovaultlocationid != 1) {
            btnconfirmtransfer.enable();           
        }
        if (transferPermission == true && selected && record.data.status == 2) {
            btncanceltransfer.enable();            
        }
    },    
    columns: [        
        //{ text: 'partnerId', dataIndex: 'partnerid', filter: { type: 'int' }, flex: 1 },
       /* {
            text: 'Vault Location', dataIndex: 'vaultlocationid',hidden:true, filter: { type: 'int' },  renderer: function (value, rec, records) {
                if (records.data.vaultlocationid == 1) return 'ACE HQ';
                else if (records.data.vaultlocationid == 2) return 'ACE G4S Rack';
                else if (records.data.vaultlocationid == 3) return 'MBB G4S Rack';
                else return '';
            }
        },*/
        //{ text: 'ProductId', dataIndex: 'productid', filter: { type: 'int' }, flex: 1 },        
        { text: 'Vault Location', dataIndex: 'vaultlocationname', hidden: false, filter: { type: 'string' }, },
        { text: 'Serial No', dataIndex: 'serialno',minWidth:200, filter: { type: 'string' }, renderer: 'boldText'},
        {
            text: 'ID', dataIndex: 'id', filter: { type: 'int' },minWidth:100, renderer: function (val, m, record) {

                if (record.data.status == 1 && record.data.allocated == 1) {
                    //return 'Allocated Active';
                    return '<span style="margin-right:5px;color:#0aad3b" class="fa fa-square"></span><span>' + val + '</span>';
                } else if ((record.data.status == 3 || record.data.status == 4) && record.data.allocated == 1) {
                    //return 'Allocated Inactive';
                    return '<span style="margin-right:5px;color:#c23f10" class="fa fa-square"></span><span>' + val + '</span>';
                } else if (record.data.status == 2 && record.data.allocated == 1) {
                    //return 'Allocated Transferring';
                    return '<span style="margin-right:5px;color:#d4b104" class="fa fa-square"></span><span>' + val + '</span>';
                } else if (record.data.status == 1 && record.data.allocated == 0) {
                    //return 'Unallocated Active';
                    return '<span style="margin-right:5px;color:#aabfa6" class="fa fa-square"></span><span>' + val + '</span>';
                } else if ((record.data.status == 3 || record.data.status == 4) && record.data.allocated == 0) {
                    //return 'Unallocated Inactive';
                    return '<span style="margin-right:5px;color:#bd816c" class="fa fa-square"></span><span>' + val + '</span>';
                } else if (record.data.status == 2 && record.data.allocated == 0) {
                    //return 'Unallocated Transferring';
                    return '<span style="margin-right:5px;color:#c7bb7f" class="fa fa-square"></span><span>' + val + '</span>';
                } else if (record.data.status == 5 && record.data.allocated == 0) {
                    //return 'Unallocated Transferring';
                    return '<span style="margin-right:5px;color:#842bd7" class="fa fa-square"></span><span>' + val + '</span>';
                } else {
                    return '<span style="margin-right:5px;color:#bdb9b7" class="fa fa-square"></span><span>' + val + '</span>';
                }
            }
        },
        {
            text: 'Allocated', dataIndex: 'allocated',minWidth:100, filter: { type: 'string' }, renderer: function (value, rec, records) {
                if (records.data.allocated == 0) return 'No';
                else if (records.data.allocated == 1) return 'Yes';
                else return '';
            }
        },
        //{ text: 'AllocatedOn', dataIndex: 'allocatedon',xtype: 'datecolumn',format: 'd/m/Y H:i',minWidth:130, filter: { type: 'date' }, },
        { text: 'Allocated On', dataIndex: 'allocatedon',xtype: 'datecolumn', format: 'Y-m-d H:i:s', minWidth:130, filter: { type: 'date' }, },
        { text: 'Move to Vault Location', dataIndex: 'movetovaultlocationname', hidden: false, filter: { type: 'string' }, },
        /*{
            text: 'Move To Vault Location', dataIndex: 'movetovaultlocationid', hidden: false, filter: { type: 'int' },  renderer: function (value, rec, records) {
                if (records.data.movetovaultlocationid == 1) return 'ACE HQ';
                else if (records.data.movetovaultlocationid == 2) return 'ACE G4S Rack';
                else if (records.data.movetovaultlocationid == 3) return 'MBB G4S Rack';
                else if (records.data.movetovaultlocationid == 4) return 'BMMB';
                else return '';
            }
        },*/
        { text: 'Move Requested On', dataIndex: 'moverequestedon',xtype: 'datecolumn', format: 'Y-m-d H:i:s', minWidth:150, filter: { type: 'date' },  },
        { text: 'Move Completed On', dataIndex: 'movecompletedon',minWidth:150,xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' },  },
        { text: 'Returned On', dataIndex: 'returnedon',minWidth:150,xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, },
        { text: 'New Vault Location', dataIndex: 'newvaultlocationname', hidden: false, filter: { type: 'string' }, },
        /*{
            text: 'New Vault Location', dataIndex: 'newvaultlocationid',minWidth:130, filter: { type: 'int' },  renderer: function (value, rec, records) {
                if (records.data.newvaultlocationid == 1) return 'ACE HQ';
                else if (records.data.newvaultlocationid == 2) return 'ACE G4S Rack';
                else if (records.data.newvaultlocationid == 3) return 'MBB G4S Rack';
                else if (records.data.movetovaultlocationid == 4) return 'BMMB';
                else return '-';
            }
        },*/
        { text: 'Delivery Order No', dataIndex: 'deliveryordernumber', minWidth:150, filter: { type: 'string' }, },
        { text: 'Brand', dataIndex: 'brand',minWidth:130, filter: { type: 'string' }, },
        { text: 'Created On', dataIndex: 'createdon',xtype: 'datecolumn', format: 'Y-m-d H:i:s', minWidth:150, filter: { type: 'date' },  },
        { text: 'Modified On', dataIndex: 'modifiedon',xtype: 'datecolumn', format: 'Y-m-d H:i:s', minWidth:150, filter: { type: 'date' },  },
        {
            text: 'Status', dataIndex: 'status',minWidth:180, filter: {
                type: 'combo',
                store: [
                    ['0', 'Pending'],
                    ['1', 'Active'],
                    ['2', 'Transferring'],
                    ['3', 'Inactive'],
                    ['4', 'Removed'],
                    // 0 - pending, 1 - available, 2 - allocated, 3 - transferring, 4 - returned 
                ]

                /*   const STATUS_PENDING = 0; 
                     const STATUS_ACTIVE = 1; // 
                     const STATUS_TRANSFERRING = 2; // transferring to other vault location
                     const STATUS_INACTIVE = 3; 
                     const STATUS_REMOVED = 4; //  */
            }, renderer: function (value, rec, records) {
                // console.log(records.data.allocated);
              /*   if (value == 1) return 'Pending';
                 else if (value == 2) return 'Available';
                 else if (value == 3) return 'Allocated';
                 else if (value == 4) return 'Transferring';
                 else if (value == 0) return 'Returned';
                 else return '';  */
                if (value == 1 && records.data.allocated == 1) return 'Allocated Active';
                else if ((value == 3 || value == 4) && records.data.allocated == 1) return 'Allocated Inactive';
                else if (value == 2 && records.data.allocated == 1) return 'Allocated Transferring';
                else if (value == 1 && records.data.allocated == 0) return 'Unallocated Active';
                else if ((value == 3 || value == 4) && records.data.allocated == 0) return 'Unallocated Inactive';
                else if (value == 2 && records.data.allocated == 0) return 'Unallocated Transferring';
                else if (value == 5 && records.data.allocated == 0) return 'Unallocated Pending';
                else return '';
            },
        },
        { text: 'Partner Name', dataIndex: 'partnername',hidden:true, filter: { type: 'string' }, },
        { text: 'Partner Code', dataIndex: 'partnercode',hidden:true, filter: { type: 'string' }, },
        { text: 'Product Code', dataIndex: 'productcode',minWidth:130, filter: { type: 'string' }, },
        { text: 'Product Name', dataIndex: 'productname',hidden:true, filter: { type: 'string' }, },
        { text: 'Created By', dataIndex: 'createdbyname',hidden:true, filter: { type: 'string' }, },
        { text: 'Modified By', dataIndex: 'modifiedbyname',hidden:true, filter: { type: 'string' } },        
    ],

    //////////////////////////////////////////////////////////////
    /// View properties settings
    ///////////////////////////////////////////////////////////////
    

    //////////////////////////////////////////////////////////////
    /// Add / edit form settings
    ///////////////////////////////////////////////////////////////
    
    
    createvaultdocument: {
        controller: 'logistic-logistic',
  
        formDialogTitle: 'Documents',
  
        // Settings
        enableFormDialogClosable: false,
        formDialogWidth: '80%',
        enableFormDialogClosable: false,
        formPanelDefaults: {
            msgTarget: 'side',
            margins: '0 0 10 0'
        },
        height: '100%',
        formPanelDefaults: {
            border: false,
            //scrollable: true,
        },
  
        formPanelItems: [
            {
                xtype: 'fieldset',
                title: 'Documents Info',
                
                items: [{
                    xtype: 'container',
                    layout: {
                        type: 'vbox',
                        align: 'stretch'
                    },
                    items: [
                        {
                            xtype: 'container',
                            layout: {
                                type: 'vbox',
                                align: 'stretch'
                            },
                            items: [
                                {
                                    xtype: 'combobox', 
                                    fieldLabel:'Document Type', 
                                    store: [
                                        ['ConsignmentNote', 'Consignment Note'],
                                        ['TransferNote', 'Transfer Note'],
                                        ['DeliveryOrder', 'Delivery Order'],
                 
                                    ],
                                    queryMode: 'local', 
                                    remoteFilter: false, 
                                    name: 'type', 
                                    valueField: 'id', 
                                    displayField: 'code', 
                                    reference: 'documentcombox', 
                                    forceSelection: true, 
                                    editable: false 
                                
                                }
                            ]
                        },
                    ]
                }],
                
            },
          {
              xtype: 'tabpanel',
              flex: 1,
              reference: 'collectiontab',
              items: [
                
                  {
                      title: 'Pending Doc Details',
                      layout: 'column',
                      margin: '28 8 8 18',
                      width: 800,
                      // disabled: true,
                      items: [
                        
                          {
                              xtype: 'container',
                              width: 700,
                              height: 300,
                              layout: 'fit',
                              items: [{

                                    name: 'itemslisted',
                             
                                      xtype: 'multiselector',
                                      title: 'Selected Pending Doc',
  
                                      columns: {
                                          items: [
                                              {
                                                  text: "Serial No.",
                                                  dataIndex: "serialno"
                                              },{
                                                  text: "Weight",
                                                  dataIndex: "weight"
                                              },{
                                                  text: "Request",
                                                  dataIndex: "moverequestedondate",
                                              },{
                                                  text: "Complete",
                                                  dataIndex: "movecompletedondate",
                                              },
                                              
                                          ],
                                          name: 'itemslisted2',
                                          defaults: {
                                              flex: 1
                                          }
                                      },
                                      
                                      fieldName: 
                                          '<tpl for=".">[ {cardCode} ] {cardCode}</tpl>'
                                      ,
                                      
                                      forceFit: true,
                                      // minHeight: 1000,
                                      
                                      viewConfig: {
                                          deferEmptyText: false,
                                          emptyText: 'No Pending Doc selected'
                                      },
                                      reference: 'pocontainer',
  
                                      search: {
                                          listeners: {
                                              // active: function(){
                                              //     console.log('active')
                                              // },
                                              // search: function(){
                                              //     console.log('active1')
                                              // },
                                              add: function(a,b,c){
                                                  code = this.lookupController().getView().lookupReference('documentcombox').value
                                                  url = 'index.php?hdl=vaultitem&action=getPendingDocuments' + '&query=' + code
                                                  this.getSearchStore().getProxy().setUrl(url)
  
                                                  if (this.getSearchStore().getProxy().url != url){
                                                      this.getView().lookupReference('pocontainer').getStore().removeAll()
                                                      this.getSearchStore().removeAll();
                                                      this.getSearchStore().load()
                                                  }
                                                  
                                                  console.log(this,a,b,c)
                                              },
                                              
                                          },
                                          
                                          field: 'serialno',
  
                                          reference: 'searchpocontainer',
                                          // minWidth: 400,
                                          // minHeight: 300,
                                          width: 400,
                                          height: 300,
                                          store: {
                                              storeId: 'mcdd',
                                              alias: 'mcs',
                                              model: 'snap.model.Openpo',
                                              sorters: 'name',
                                              proxy: {
                                                  type: 'ajax',
                                                  limitParam: null,
                                                  url: 'index.php?hdl=vaultitem&action=getPendingDocuments'
                                                  // &query='+Ext.bind.getBind('customercombox.selection.cardCode')
                                              }
                                          }
                                      }
                              }]
                          },
                          {
                            items:[
                                { xtype: 'datefield', style:'padding-left: 20px;',  reference: 'scheduledate', flex: 1, fieldLabel: 'Scheduled Date', name: 'scheduledate', format: 'Y-m-d H:i:s', allowBlank: false },                      
                            ]
                          },	
                          
                      ]
                  },
              ]
          },
      ]
    },

});

