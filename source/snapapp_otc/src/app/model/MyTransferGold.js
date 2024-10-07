Ext.define('snap.model.MyTransferGold', {
    extend: 'snap.model.Base',
    
    fields: [
        {type: 'int', name: 'id'},
        {type: 'int', name: 'partnerid'},
        {type: 'int', name: 'accountholderid'},
        {type: 'string', name: 'type'},
        {type: 'int', name: 'fromaccountholderid'},
        {type: 'int', name: 'toaccountholderid'},
        {type: 'string', name: 'receiveremail'},
        {type: 'string', name: 'receivername'},
        {type: 'string', name: 'contact'},
        {type: 'string', name: 'refno'},
        {type: 'float', name: 'xau'},
        {type: 'float', name: 'price'},
        {type: 'float', name: 'amount'},
        {type: 'string', name: 'message'},

        {type: 'string', name: 'sendercode'},
        {type: 'string', name: 'receivercode'},

        {type: 'date', name: 'transferon'},
        {type: 'date', name: 'cancelon'},
        {type: 'date', name: 'expireon'},
        {type: 'int', name: 'isnotifyrecipient'},
        {type: 'int', name: 'status'},
        {type: 'date', name: 'createdon'},
        {type: 'int', name: 'createdby'},
        {type: 'date', name: 'modifiedon'},
        {type: 'int', name: 'modifiedby'},
        {type: 'string', name: 'checker'},
        {type: 'string', name: 'remarks'},
        {type: 'date', name: 'actionon'},

        // view
        {type: 'int', name: 'frompartnerid'},
        {type: 'string', name: 'fromfullname'},
        {type: 'string', name: 'fromaccountholdercode'},
        {type: 'string', name: 'tofullname'},
        {type: 'string', name: 'toaccountholdercode'},
        {type: 'string', name: 'partnercode'},
        {type: 'string', name: 'partnername'},
        {type: 'string', name: 'createdbyname'},
        {type: 'string', name: 'modifiedbyname'},
    ]
});
