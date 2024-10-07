Ext.define('snap.view.vaultitemtrans.vaultitemTrans', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'vaultitemtransview',
    requires: [
        'snap.store.VaultItemTrans',
        'snap.model.VaultItemTrans',
        'snap.view.vaultitemtrans.vaultitemtransController',
        'snap.view.vaultitemtrans.vaultitemtransModel',
        'Ext.view.MultiSelector'
    ],
    controller: 'vaultitemtrans',
    viewModel: {
        type: 'vaultitemtrans',
    },
    gridSelectionMode: 'SINGLE',
    allowDeselect: true,
    store: {
        type: 'vaultitemtrans',
        autoLoad: true
    },
    //permissionRoot: '/root/mbb/vault',
    enableFilter: true,

    rowexpander: {
        ptype: 'rowexpander', //rowexpander: true, //ptype: 'rowexpandergrid', 
        pluginId: 'rowexpander',
        expandOnDblClick: true,
        selectRowOnExpand: false,
        expandRow: function(rowIdx) {
            var rowNode = this.view.getNode(rowIdx),
                row = Ext.get(rowNode),
                nextBd = Ext.get(row).down(this.rowBodyTrSelector),
                record = this.view.getRecord(rowNode),
                grid = this.getCmp();
            if (row.hasCls(this.rowCollapsedCls)) {
                row.removeCls(this.rowCollapsedCls);
                nextBd.removeCls(this.rowBodyHiddenCls);
                this.recordsExpanded[record.internalId] = true;
                this.view.fireEvent('expandbody', rowNode, record, nextBd.dom);
            }
        },
    
        collapseRow: function(rowIdx) {
            var rowNode = this.view.getNode(rowIdx),
                row = Ext.get(rowNode),
                nextBd = Ext.get(row).down(this.rowBodyTrSelector),
                record = this.view.getRecord(rowNode),
                grid = this.getCmp();
            if (!row.hasCls(this.rowCollapsedCls)) {
                row.addCls(this.rowCollapsedCls);
                nextBd.addCls(this.rowBodyHiddenCls);
                this.recordsExpanded[record.internalId] = false;
                this.view.fireEvent('collapsebody', rowNode, record, nextBd.dom);
            }
        },

        rowBodyTpl: Ext.create('Ext.XTemplate',
        '<table style="width: 400px;border: 1px solid #d2d2d2;margin-bottom:15px;border-radius:5px;box-shadow: 0px 1px rgba(0,0,0,0.1);margin-left: 20px">',
            '<tr>',
                '<th style="text-align:center; width:200px">Serial No.</th>',
            '</tr>',
            '{child}',
        '</table>'
        )                                      
    },
    listeners: {
        afterlayout: function(grid){
            // console.log(this,grid,'this',grid.getPlugin("rowexpander").view.getNodes());
            var rowExpander = grid.getPlugin("rowexpander")
            var nodes = rowExpander.view.getNodes()
            for (var i = 0; i < nodes.length; i++) {
                rowExpander.expandRow(i);
            } 
        }
    },
    // checkActionPermission: function (view, record) {
    //     var selected = false;
    //     Ext.Array.each(view.getSelectionModel().getSelection(), function (items) {
    //         if (items.getId() == record.getId()) {
    //             selected = true;
    //             return false;
    //         }
    //     });

    //     var btntransferitem = Ext.ComponentQuery.query('#transferitem')[0];
    //     var btnconfirmtransfer = Ext.ComponentQuery.query('#confirmtransfer')[0];
    //     var btnreturn = Ext.ComponentQuery.query('#returnitem')[0];
    //     var btncanceltransfer = Ext.ComponentQuery.query('#cancelreqtransfer')[0];
    //     btntransferitem.disable();
    //     btnconfirmtransfer.disable();
    //     btnreturn.disable();
    //     btncanceltransfer.disable();
    //     var transferPermission = snap.getApplication().hasPermission('/root/mbb/vault/transfer');
    //     var returnPermission = snap.getApplication().hasPermission('/root/mbb/vault/return');
    //     if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.vaultlocationid != 0) {
    //         btntransferitem.enable();
    //     }
    //     if (transferPermission == true && selected && record.data.status == 1 && (record.data.allocated == 1) && record.data.vaultlocationid == 0) {
    //         btntransferitem.enable();
    //     }
    //     if (transferPermission == true && selected && record.data.status == 2 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.movetovaultlocationid != null && record.data.movetovaultlocationid != 1) {
    //         btnconfirmtransfer.enable();
    //     }
    //     if (returnPermission == true && selected && record.data.status == 2 && record.data.allocated == 0 && record.data.vaultlocationid == 2 && record.data.movetovaultlocationid == 1) {
    //         btnreturn.enable();
    //     }
    //     if (transferPermission == true && selected && record.data.status == 2 && (record.data.allocated == 1 || record.data.allocated == 0) && record.data.movetovaultlocationid != null && record.data.movetovaultlocationid != 1) {
    //         btnconfirmtransfer.enable();
    //     }
    //     if (transferPermission == true && selected && record.data.status == 2) {
    //         btncanceltransfer.enable();
    //     }
    // },
    columns: [
        {
            text: 'ID',
            dataIndex: 'id',
            filter: {
                type: 'int'
            },
            flex: 1
        },
        {
            text: 'Partner ID',
            dataIndex: 'partnerid',
            filter: {
                type: 'int'
            },
        },
        {
            text: 'Transaction Type',
            dataIndex: 'type',
            filter: {
                type: 'string'
            },
        },
        {
            text: 'Document No',
            dataIndex: 'documentno',
            filter: {
                type: 'string'
            },
            renderer: 'boldText',
            renderer: function (val, m, record) {

                if (record.data.status == 0) {
                    //return ' Inactive';
                    return '<span style="margin-right:5px;color:red;font-weight:bold" class="fa fa-square"></span><span>' + val + '</span>';
                } else if (record.data.status == 1) {
                    //return ' Active';

                    if (record.data.type == 'TRANSFERCONFIRMATION') {
                        // if got confirmation pending
                        return '<span style="margin-right:5px;color:orange;font-weight:bold" class="fa fa-check"></span><span>' + val + '</span>';
                    }
                    // as a confirmed transfer 
                    return '<span style="margin-right:5px;color:green;font-weight:bold" class="fa fa-square"></span><span>' + val + '</span>';
                } else {
                    return '<span style="margin-right:5px;color:black;font-weight:bold" class="fa fa-square"></span><span>' + val + '</span>';
                }
            }
        },
        {
            text: 'Document Date On',
            dataIndex: 'documentdateon',
            xtype: 'datecolumn',
            format: 'Y-m-d H:i:s',
            minWidth: 130,
            filter: {
                type: 'date'
            },
        },
        {
            text: 'From Vault Location',
            dataIndex: 'fromlocationname',
            hidden: false,
            filter: {
                type: 'string'
            },
        },
        {
            text: 'To Vault Location',
            dataIndex: 'tolocationname',
            hidden: false,
            filter: {
                type: 'string'
            },
        },
        {
            text: 'Created On',
            dataIndex: 'createdon',
            xtype: 'datecolumn',
            format: 'Y-m-d H:i:s',
            minWidth: 150,
            filter: {
                type: 'date'
            },
        },
        {
            text: 'Modified On',
            dataIndex: 'modifiedon',
            xtype: 'datecolumn',
            format: 'Y-m-d H:i:s',
            minWidth: 150,
            filter: {
                type: 'date'
            },
        },
        {
            text: 'Status',
            dataIndex: 'status',
            minWidth: 180,
            filter: {
                type: 'combo',
                store: [
                    ['0', 'Inactive'],
                    ['1', 'Active'],
                ]
            },
        },
        {
            text: 'Partner Name',
            dataIndex: 'partnername',
            hidden: true,
            filter: {
                type: 'string'
            },
        },
        {
            text: 'Cancel By',
            dataIndex: 'cancelbyname',
            filter: {
                type: 'string'
            },
        },
        {
            text: 'Cancel On',
            dataIndex: 'cancelon',
            xtype: 'datecolumn',
            format: 'Y-m-d H:i:s',
            minWidth: 150,
            filter: {
                type: 'date'
            },
        },
        {
            text: 'Created By',
            dataIndex: 'createdbyname',
            filter: {
                type: 'string'
            },
        },
        {
            text: 'Modified By',
            dataIndex: 'modifiedbyname',
            hidden: true,
            filter: {
                type: 'string'
            }
        },
    ],

    editvaulttrans: {
  
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
                
                items: [
                                        {
                                            xtype: 'container',
                                            layout: {
                                                type: 'vbox',
                                                align: 'stretch'
                                            },
                                            items: [
                                                {
                                                    xtype: 'fieldset', title: 'Detail', collapsible: false,
                                                    default: { labelWidth: 90, layout: 'hbox' },
                                                    items: [
                                                        { xtype: 'hidden', hidden: true, name: 'id' },
                                                        { xtype: 'textfield', fieldLabel: 'Document No.', name: 'documentno', reference: 'documentno', maxLength: 255, required: true, allowBlank: false },
                                                        { xtype: 'datefield', fieldLabel: 'Document On', name: 'documentdateon', editable: false, required: true, allowBlank: false, format: 'Y-m-d H:i:s' },
                                                        
                                                    ]
                                                },
                                            ]
                                        },
                                        {
                                            xtype: 'container',
                                            width: 700,
                                            height: 300,
                                            layout: 'fit',
                                            items: [
                                                //Grid Panel Definition - grid data populated from onPreLoadForm() and replaceListViewReady()
                                                { name: 'replacelist', xtype: 'gridpanel', reference: 'replaceListGrid', itemId: 'replaceListGrid', layout: 'fit',
                                                    flex: 1,
                                                    store: { type: 'array' , fields: ['name', 'value'] },
                                                    height: 420,
                                                    listeners: {
                                                        viewReady: 'replaceListViewReady',
                                                        selectionchange: 'replaceListSelectionChanged',
                                                        validateedit: 'onReplaceListValidate',
                                                        edit: 'onEditReplaceList'
                                                    },
                                                    columns: [
                                                        // { text: 'ID', dataIndex: 'id', inputType: 'hidden', hidden: true,
                                                        //     editor: { name: 'id', allowBlank: true }
                                                        // },
                                                        {reference: 'nameColumn', text: 'Name', dataIndex: 'name', 
                                                            editor: {name: 'name',xtype: 'textfield', allowBlank: false} },
                                                        {reference: 'valueColumn', text: 'Value', dataIndex: 'value', 
                                                            editor: {name: 'value',xtype: 'textfield', allowBlank: true}, flex: 1 },
                                                    ],
                                                    selType: 'rowmodel',
                                                    plugins: [
                                                        { ptype: 'rowediting', id: 'rowEditPlugin', clicksToMoveEditor: 1, autoCancel: false }
                                                    ]
                                                }
                                            ]
                                        },
                ],
                
            },
      ]
    },
});