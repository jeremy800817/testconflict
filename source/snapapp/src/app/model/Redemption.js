Ext.define('snap.model.Redemption', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name:'id'},
        { type: 'int', name:'partnerid'},
        { type: 'int', name:'branchid'},
        { type: 'string', name:'branchname'},
        { type: 'int', name:'salespersonid'},
        { type: 'string', name:'partnerrefno'},
        { type: 'string', name:'redemptionno'},
        { type: 'string', name:'apiversion'},
        { type: 'string', name:'type'},
        { type: 'int', name:'productid'},
        { type: 'string', name:'redemptionfee'},
        { type: 'string', name:'insurancefee'},
        { type: 'string', name:'handlingfee'},
        { type: 'string', name:'specialdeliveryfee'},
        { type: 'string', name:'xaubrand'},
        { type: 'string', name:'xauserialno'},
        { type: 'string', name:'xau'},
        { type: 'string', name:'fee'},
        { type: 'string', name:'bookingon'},
        { type: 'string', name:'bookingprice'},
        { type: 'string', name:'bookingpricestreamid'},
        { type: 'string', name:'confirmon'},
        { type: 'int', name:'confirmby'},
        { type: 'string', name:'totalweight'},
        { type: 'string', name:'totalquantity'},
        { type: 'string', name:'confirmpricestreamid'},
        { type: 'string', name:'confirmprice'},
        { type: 'string', name:'confirmreference'},
        { type: 'string', name:'deliveryaddress1'},
        { type: 'string', name:'deliveryaddress2'},
        { type: 'string', name:'deliveryaddress3'},
        { type: 'string', name:'deliverypostcode'},
        { type: 'string', name:'deliverystate'},
        { type: 'string', name:'deliverycontactno1'},
        { type: 'string', name:'deliverycontactno2'},
        { type: 'string', name:'deliverycontactname1'},
        { type: 'string', name:'deliverycontactname2'},
        { type: 'string', name:'appointmentdatetime'},
        { type: 'string', name:'appointmenton'},
        { type: 'string', name:'inventory'},
        { type: 'string', name:'processedon'},
        { type: 'string', name:'deliveredon'},
        { type: 'string', name:'createdon'},
        { type: 'int', name:'createdby'},
        { type: 'string', name:'modifiedon'},
        { type: 'int', name:'modifiedby'},
        { type: 'int', name:'status'},
        { type: 'string', name:'remarks'},		
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
            
       