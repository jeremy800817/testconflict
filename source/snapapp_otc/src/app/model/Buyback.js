Ext.define('snap.model.Buyback', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name:'id'},
        { type: 'int', name:'partnerid'},
        { type: 'string', name:'partnerrefno'},
        { type: 'string', name:'apiversion'},
        { type: 'int', name:'branchid'},
        { type: 'string', name:'buybackno'},
        { type: 'int', name:'pricestreamid'},
        { type: 'float', name:'price'},
       
        { type: 'float', name:'totalweight'},
        { type: 'float', name:'totalamount'},
        { type: 'int', name:'totalquantity'},
        { type: 'float', name:'fee'},
        { type: 'string', name:'items'},
        { type: 'string', name:'remarks'},

        { type: 'int', name:'confirmpricestreamid'},
        { type: 'float', name:'confirmprice'},
        { type: 'date', name:'confirmon'},
        { type: 'date', name:'collectedon'},
        { type: 'int', name:'collectedby'},
        { type: 'int', name: 'reconciled'},
        { type: 'date', name: 'reconciledon'},
        { type: 'int', name: 'reconciledby'},
        { type: 'string', name: 'reconciledsaprefno'},
        { type: 'int', name:'status'},
      
        { type: 'string', name:'createdon'},
        { type: 'int', name:'createdby'},
        { type: 'string', name:'modifiedon'},
        { type: 'int', name:'modifiedby'},

        { type: 'string', name:'partnername'},
        { type: 'string', name:'partnercode'},
        { type: 'string', name:'branchname'},
        { type: 'string', name:'branchcode'},
        
        { type: 'string', name:'createdbyname'},
        { type: 'string', name:'modifiedbyname'},
        	
    ]
});
            
       