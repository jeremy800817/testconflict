
Ext.define('snap.view.collection.PosCollection',{
    extend: 'snap.view.collection.Collection',
    xtype: 'poscollectionview',
    requires: [
        'snap.store.PosCollection',
        'snap.model.PosCollection',        
        'snap.view.collection.PosCollectionController',
        'snap.view.collection.PosCollectionModel',  
        // 'snap.store.ProductItems',
        // 'snap.model.ProductItems', 
                
    ],
    permissionRoot: '/root/pos/collection',
    //store: { type: 'Collection' },
    partnercode: 'POS',
    controller: 'collection-poscollection',
    viewModel: {
        type: 'collection-poscollection'
    },
    store:{
        type: 'Collection', proxy: {
            type: 'ajax',
            url: 'index.php?hdl=collection&action=list&partnercode=POS',
            reader: {
                type: 'json',
                rootProperty: 'records',
            }
        },
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

            if(this.lookupReference('addButton') || this.lookupReference('editButton') || this.lookupReference('delButton')){
                // Hide buttons for non salesman
                addbutton = this.lookupReference('addButton');
                // delbutton = this.lookupReference('delButton');

                // Initialize button settings
                addbutton.setHidden(true);
                // editbutton.setHidden(true);
                // delbutton.setHidden(true);
                    
                // Check for type 
                if (snap.getApplication().usertype == "Operator" || "Sale"){
                    addbutton.setHidden(false);    
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
            console.log(this,grid,'this',grid.getPlugin("rowexpander").view.getNodes());
            var rowExpander = grid.getPlugin("rowexpander")
            var nodes = rowExpander.view.getNodes()
            for (var i = 0; i < nodes.length; i++) {
                rowExpander.expandRow(i);
            } 
        }
    },
    toolbarItems:[
        // 'add', 'detail', 'filter', '|',
        'add', 'detail', 'filter', '|', 'edit', 'delete',
        {
            reference: 'uploadcollectionpos', text: 'Upload File', itemId: 'statusLgs', tooltip: 'Upload Collection POS', iconCls: 'x-fa fa-upload', handler: 'uploadcollectionpos',
            listeners : {
                afterrender : function(srcCmp) {
                    Ext.create('Ext.tip.ToolTip', {
                        target : srcCmp.getEl(),
                        html : 'Upload Collection POS'
                    });
                }
            }
        },  
        {
            xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
        },
        {
            xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
        },
        {
            handlerModule: 'poscollection', text: 'Download', tooltip: 'Export Data', iconCls: 'x-fa fa-download', handler: 'getPrintReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
        }
    ],
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
        
        { text: 'Created On', dataIndex: 'createdon',  xtype: 'datecolumn', format: 'Y-m-d h:i:s', filter: {type: 'date'}, flex: 1},
        { text: 'Modified On', dataIndex: 'modifiedon',  xtype: 'datecolumn', format: 'Y-m-d h:i:s', filter: {type: 'date'}, flex: 1, hidden: true},
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
    formClass: 'snap.view.collection.PosCollectionGridForm',

    // uploadcollectionposform: [

    // ],

    uploadcollectionposform: {
        controller: 'collection-poscollection',

        formDialogWidth: 700,
        formDialogHeight: 400,

        formDialogTitle: "Upload POS collection file",

        // Settings
        enableFormDialogClosable: false,
        formPanelDefaults: {
            border: false,
            xtype: "panel",
            flex: 1,
            layout: "anchor",
            msgTarget: "side",
            margins: "0 0 10 10",
        },
        enableFormPanelFrame: false,
        formPanelLayout: "hbox",
        formViewModel: {},

        formPanelItems: [
            //1st hbox
            {
                xtype: "form",
                reference: "grnposlist-form",
                items: [
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            
                            {
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; Please verify and get approved before upload </p>",
                                margin: '0 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },{
                                flex:1,
                                xtype: 'displayfield',
                                value : "<p>&#9679; Minimum 1 GRN record is require.</p>",
                                margin: '0 0 0 20',
                                forceSelection: true,
                                enforceMaxLength: true,
                                readOnly : true,
                            },
                            { xtype: 'panel', flex : 1},
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [
                            { xtype: 'filefield',fieldLabel: 'File (Required)', name: 'grnposlist', width: '90%', flex: 4, allowBlank: false, reference: 'grnposlist_field' },
                        ]
                    },
                ],
                // Input listeners here if any
            },
            {
                xtype: "panel",
                flex: 0,
                width: 10,
                items: [],
            }, //padding hbox
            //2nd hbox
        ],
    },
});
