
Ext.define('snap.view.replenishment.ReplenishmentRequests', {
    extend: 'snap.view.gridpanel.Base',
    xtype: 'replenishmentrequests',
    requires: [
        'snap.store.Replenishment',
        'snap.model.Replenishment',
        'snap.store.SalesPersons',
        'snap.model.SalesPersons',
        'snap.view.replenishment.ReplenishmentController',
        'snap.view.replenishment.ReplenishmentModel',
    ],
    permissionRoot: '/root/mbb/replenishment',
    store: { type: 'Replenishment' },
    controller: 'replenishment-replenishment',
    gridSelectionMode: 'SINGLE',
    allowDeselect:true,
    height: '85%',
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
                editbutton = this.lookupReference('editButton');
                delbutton = this.lookupReference('delButton');

                // Initialize button settings
                addbutton.setHidden(true);
                editbutton.setHidden(true);
                delbutton.setHidden(true);
                    
                // Check for type 
                if (snap.getApplication().usertype == "Operator" || "Sale"){
                    addbutton.setHidden(false);
                    editbutton.setHidden(false);
                    delbutton.setHidden(false);          
                } 
            }
        }
    },
    toolbarItems: [
        //'add', 'edit', 'detail', '|', 'delete', 'filter','|',
        'detail', 'filter','|',
        { reference: 'sendButton', text: 'Send', itemId: 'sendToLogistics', tooltip: 'Send to Logistics', iconCls: 'x-fa fa-paper-plane', handler: 'addLogistic', validSelection: 'ignore' },

        '|',
        // {reference: 'summaryButton', text: 'Summary', itemId: 'summaryOfRedemption', tooltip: 'Summary', iconCls: 'x-fa fa-list-alt', handler: 'summaryOfRedemption', validSelection: 'single' }
    ],   
    columns: [
        { text: 'ID', dataIndex: 'id', renderer: 'setTextColor' ,hidden:true,filter: { type: 'int' }},
        //{ text: 'Type', dataIndex: 'type', renderer: 'setTextColor',filter: { type: 'string' },minWidth:130 },
        /*{
            text: 'Status', dataIndex: 'status', renderer: function (value, store) {
                if (value == 0) return 'Pending';
                if (value == 1) return 'Confirmed';
                if (value == 2) return 'Completed';
                if (value == 3) return 'Failed';
                if (value == 4) return 'Process Delivery';
                if (value == 5) return 'Cancelled';
                else return '';

                
            },
        },*/
        { text: 'Status',  dataIndex: 'status', minWidth:130, renderer: 'setTextColor',

               filter: {
                   type: 'combo',
                   store: [
                       ['0', 'Pending'],
                       ['1', 'Confirmed'],
                       ['2', 'Completed'],
                       ['3', 'Failed'],
                       ['4', 'Process Delivery'],
                       ['5', 'Cancelled'],
                       //['Collecting', 10],

                   ],

               },
               renderer: function(value, rec){
                    if (value == 0) return 'Pending';
                    if (value == 1) return 'Confirmed';
                    if (value == 2) return 'Completed';
                    if (value == 3) return 'Failed';
                    if (value == 4) return 'Process Delivery';
                    if (value == 5) return 'Cancelled';
                    else return '';
              },
        },
        
        { text: 'Partner Code', dataIndex: 'partnercode', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Branch Name', dataIndex: 'branchname', renderer: 'setTextColor', filter: { type: 'string' } },
        //{ text: 'Sales Name', dataIndex: 'salesname', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'SAP ref no', dataIndex: 'saprefno', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Product Name', dataIndex: 'productname', renderer: 'setTextColor', filter: { type: 'string' } },
        { text: 'Serial Number', dataIndex: 'serialno', renderer: 'setTextColor', filter: { type: 'string' } },
        
        { text: 'Schedule On', dataIndex: 'schedule',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' }, },
        { text: 'Replenished On', dataIndex: 'replenishedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' }, },
        
        { text: 'Created On', dataIndex: 'createdon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date' }, },
        { text: 'Created By', dataIndex: 'createdbyname',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },
        { text: 'Modified On', dataIndex: 'modifiedon',minWidth:130,hidden:true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'),filter: { type: 'date'} },
        { text: 'Modified By', dataIndex: 'modifiedbyname',hidden:true, renderer: 'setTextColor',filter: { type: 'string' } },

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
    formClass: 'snap.view.replenishment.ReplenishmentGridForm',
    listeners: {
        /*
        cellclick: function (view, cell, cellIndex, record, row, rowIndex, e) {
            var permission = snap.getApplication().hasPermission('/root/dg999/redemption/edit');           
            if (permission === true && record.data.status==1) {
                Ext.ComponentQuery.query('#sendToLogistics')[0].enable();
            }else{
                Ext.ComponentQuery.query('#sendToLogistics')[0].disable();
            }
        },*/
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
});



