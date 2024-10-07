Ext.define('snap.view.product.Product',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'productview',
    requires: [
        'snap.store.Product',
        'snap.model.Product',        
        'snap.view.product.ProductController',
        'snap.view.product.ProductModel',          
    ],
    permissionRoot: '/root/system/product',
    store: { type: 'Product' },
    controller: 'product-product',
    viewModel: {
        type: 'product-product'
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
    columns: [
        { text: 'ID',  dataIndex: 'id', hidden: true,  filter: {type: 'int'  } ,flex:1,},
        { text: 'Category ID',  dataIndex: 'categoryid', filter: {type: 'int'  } ,flex:1,renderer: function (value, metaData, record, rowIndex, colIndex, store, view) {     
            var productitems =Ext.getStore('productcategories').load();                                                                              
            var catRecord = productitems.findRecord('id', value);                                       
            return catRecord ? catRecord.get('value') : ''; 
        },   },
        { text: 'Code',  dataIndex: 'code', filter: {type: 'string'  } ,  flex:1},
        { text: 'Name',  dataIndex: 'name', filter: {type: 'string'  } , flex:1},
        { xtype : 'checkcolumn',text: 'Company can sell',  dataIndex: 'companycansell',disabledCls : '', disabled: true ,flex:1 },
        { xtype : 'checkcolumn',text: 'Company can buy',  dataIndex: 'companycanbuy',disabledCls : '', disabled: true , flex:1 },
        { xtype : 'checkcolumn',text: 'Trx by weight',  dataIndex: 'trxbyweight',disabledCls : '', disabled: true , flex:1 },
        { xtype : 'checkcolumn',text: 'Trx by currency',  dataIndex: 'trxbycurrency',disabledCls : '', disabled: true ,  flex:1},
        { xtype : 'checkcolumn',text: 'Deliverable',  dataIndex: 'deliverable',disabledCls : '', disabled: true ,  flex:1},
        { text: 'SAP Item code',  dataIndex: 'sapitemcode', filter: {type: 'string'  } ,flex:1 },
        { xtype : 'checkcolumn',text: 'Status',  dataIndex: 'status',disabledCls : '', disabled: true ,  flex:1 },        
    ],
    formClass: 'snap.view.product.ProductGridForm'    
});
