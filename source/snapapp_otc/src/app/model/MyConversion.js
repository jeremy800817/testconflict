Ext.define('snap.model.MyConversion', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name:'id'},
        { type: 'string', name:'refno'},
        { type: 'int', name:'productid'},
        { type: 'float', name:'commissionfee'},
        { type: 'float', name:'premiumfee'},
        { type: 'float', name:'courierfee'},
        { type: 'float', name:'handlingfee'},
        { type: 'float', name:'rdmtotalfee'},
        { type: 'int', name:'redemptionid'},
        { type: 'string', name:'logisticfeepaymentmode'},
        { type: 'int', name:'accountholderid'},
        { type: 'string', name:'createdon'},
        { type: 'int', name:'createdby'},
        { type: 'string', name:'modifiedon'},
        { type: 'int', name:'modifiedby'},
        { type: 'int', name:'status'},
        { type: 'string', name:'remarks'},	
        { type: 'int', name:'rdmstatus'},
        { type: 'int', name:'rdmpartnerid'},
        { type: 'int', name:'rdmbranchid'},
        { type: 'string', name:'rdmbranchname'},
        { type: 'int', name:'rdmsalespersonid'},
        { type: 'string', name:'rdmpartnerrefno'},
        { type: 'string', name:'rdmredemptionno'},
        { type: 'string', name:'rdmapiversion'},
        { type: 'string', name:'rdmtype'},
        { type: 'string', name:'rdmsapredemptioncode'},
        //{ type: 'int', name:'rdmproductid'},
        //{ type: 'string', name:'rdmredemptionfee'},
        { type: 'float', name:'rdmredemptionfee'},
        { type: 'float', name:'rdminsurancefee'},
        { type: 'float', name:'rdmhandlingfee'},
        { type: 'float', name:'rdmspecialdeliveryfee'},
        //{ type: 'string', name:'rdmxaubrand'},
        //{ type: 'string', name:'rdmxauserialno'},
        { type: 'float', name:'rdmtotalweight'},
        { type: 'int', name:'rdmtotalquantity'},
        { type: 'string', name:'rdmitems'},
        //{ type: 'string', name:'rdmxau'},
        //{ type: 'string', name:'rdmfee'},
        { type: 'string', name:'rdmbookingon'},
        { type: 'string', name:'rdmbookingprice'},
        { type: 'string', name:'rdmbookingpricestreamid'},
        { type: 'string', name:'rdmconfirmon'},
        { type: 'int', name:'rdmconfirmby'},
        { type: 'string', name:'rdmconfirmpricestreamid'},
        { type: 'string', name:'rdmconfirmprice'},
        { type: 'string', name:'rdmconfirmreference'},
        { type: 'string', name:'rdmdeliveryaddress1'},
        { type: 'string', name:'rdmdeliveryaddress2'},
        { type: 'string', name:'rdmdeliveryaddress3'},
        { type: 'string', name:'rdmdeliveryaddress'},
        { type: 'string', name:'rdmdeliverycity'},
        { type: 'string', name:'rdmdeliverypostcode'},
        { type: 'string', name:'rdmdeliverystate'},
        { type: 'string', name:'rdmdeliverycountry'},
        { type: 'string', name:'rdmdeliverycontactname1'},
        { type: 'string', name:'rdmdeliverycontactname2'},
        { type: 'string', name:'rdmdeliverycontactno1'},
        { type: 'string', name:'rdmdeliverycontactno2'},
        { type: 'int', name:'rdmappointmentbranchid'},
        { type: 'date', name:'rdmappointmentdatetime'},
        { type: 'date', name:'rdmappointmenton'},
        { type: 'string', name:'rdmappointmentby'},
        { type: 'int', name:'rdmreconciled'},
        { type: 'date', name:'rdmreconciledon'},
        { type: 'int', name:'rdmreconciledby'},
        { type: 'int', name:'rdmstatus'},

        { type: 'string', name:'accountholdercode'},
        { type: 'string', name:'accountholdername'},
        //{ type: 'string', name:'rdminventory'},
        //{ type: 'string', name:'rdmprocessedon'},
        //{ type: 'string', name:'rdmdeliveredon'},
       	

    ],formulas: {        
        pendingcount: function (get) {
            var pendingcount=get('status');
            console.log(get);
           // return pendingcount;
            /* var pendingstatuscount = get('status'), pendingcount = 0;
            pendingcount=pendingcount+pendingstatuscount;
            return pendingcount; */
        }
    }
    
});
            
       