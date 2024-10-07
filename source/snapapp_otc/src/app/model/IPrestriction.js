//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.model.IPrestriction', {
    extend: 'snap.model.Base',
    fields: [
		{ type: 'int',    name: 'id'},
		{ type: 'string', name: 'restricttype'},
		{ type: 'string', name: 'partnertype'},
		{ type: 'int', name: 'partnerid'},
		{ type: 'string', name: 'ip'},
		{ type: 'string', name: 'remark'},
		{ type: 'int', name: 'status'},
		{ type: 'int', name: 'createdby'},
		{ type: 'date', name: 'createdon'},
		{ type: 'int', name: 'modifiedby'},
		{ type: 'date', name: 'modifiedon'}
	]
});
