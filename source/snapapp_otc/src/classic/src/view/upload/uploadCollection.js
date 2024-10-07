
Ext.define('snap.view.upload.uploadCollection',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'collectionview',
    requires: [
        'snap.store.Collection',
        'snap.model.Collection',        
        'snap.view.collection.CollectionController',
        'snap.view.collection.CollectionModel',  
        // 'snap.store.ProductItems',
        // 'snap.model.ProductItems', 
                
    ],
    permissionRoot: '/root/gtp/collection',
    store: { type: 'Collection' },
    controller: 'collection-collection',
    viewModel: {
        type: 'collection-collection'
    },
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
                '<th style="text-align:center; width:200px">SAP docNum</th>',
                '<th style="text-align:center; width:200px">SAP GTPREFNO</th>',
            '</tr>',
            '{child}',
        '</table>'
        )                                      
    },
    toolbarItems:[
        // 'add', 'detail', 'filter', '|',
        'add', 'detail', 'filter', '|',
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
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

            if(this.lookupReference('addButton') || this.lookupReference('editButton') || this.lookupReference('delButton')){
                // Hide buttons for non salesman
                addbutton = this.lookupReference('addButton');
                // editbutton = this.lookupReference('editButton');
                // delbutton = this.lookupReference('delButton');

                // Initialize button settings
                addbutton.setHidden(true);
                // editbutton.setHidden(true);
                // delbutton.setHidden(true);
                    
                // Check for type 
                if (snap.getApplication().usertype == "Operator" || "Sale"){
                    addbutton.setHidden(false);
                    // editbutton.setHidden(false);
                    // delbutton.setHidden(false);          
                } 
            }
            
           
            /*
            snap.getApplication().sendRequest({
                hdl: 'collection', action: 'isHideButtons'
                }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        if(!data.hide){
                            addbutton.setHidden(false);
                            editbutton.setHidden(false);
                            delbutton.setHidden(false);
                        }
                        
                        //Ext.get('allocatedcount').dom.innerHTML = data.allocatedcount;
                        //Ext.getCmp('allocatedcount').setValue(data.allocatedcount);
                        //Ext.get('availablecount').dom.innerHTML = data.availablecount;
                        //Ext.get('onrequestcount').dom.innerHTML = data.onrequestcount;
                        //Ext.get('returncount').dom.innerHTML = data.returncount;                      
                    }
            })*/
        },
        afterlayout: function(grid){
            // console.log(this,grid,'this',grid.getPlugin("rowexpander").view.getNodes());
            var rowExpander = grid.getPlugin("rowexpander")
            var nodes = rowExpander.view.getNodes()
            for (var i = 0; i < nodes.length; i++) {
                rowExpander.expandRow(i);
            } 
        }
    },
    columns: [
        { text: 'ID',  dataIndex: 'id', hidden: true, filter: {type: 'int'  } , flex: 1 },
        { text: 'Code',  dataIndex: 'partnercode', filter: {type: 'string'  } , flex: 1 },
        { text: 'Partner',  dataIndex: 'partnername', filter: {type: 'string'  }},
        { text: 'Salesperson',  dataIndex: 'salespersonname', filter: {type: 'string'  }},
        { text: 'Comments',  dataIndex: 'comments', filter: {type: 'string'  }},
        { text: 'XAU Expected', dataIndex: 'totalxauexpected', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
         },
        { text: 'Gross Weight', dataIndex: 'totalgrossweight', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'XAU Collected', dataIndex: 'totalxaucollected', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        { text: 'Vat Sum', dataIndex: 'vatsum', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
            editor: {    //field has been deprecated as of 4.0.5
                xtype: 'numberfield',
                decimalPrecision: 3
            } 
        },
        
        { text: 'Created On', dataIndex: 'createdon',  xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1},
        { text: 'Modified On', dataIndex: 'modifiedon',  xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: {type: 'date'}, flex: 1, hidden: true},
        { text: 'Created By', dataIndex: 'createdbyname', filter: {type: 'string'}, hidden: true },
        { text: 'Modified By', dataIndex: 'modifiedbyname', filter: {type: 'string'}, hidden: true },
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
