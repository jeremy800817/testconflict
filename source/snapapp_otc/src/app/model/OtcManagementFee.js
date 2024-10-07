//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.model.OtcManagementFee', {
    extend: 'snap.model.Base',
    fields: [
		{ type: 'int',    name: 'id'},
		{ type: 'string', name: 'name'},
		{ type: 'float', name: 'avgdailygoldbalancegramfrom'},
		{ type: 'float', name: 'avgdailygoldbalancegramto'},
		{ type: 'float', name: 'feepercent'},
		{ type: 'float', name: 'feeamount'},
		{ type: 'int', name: 'period'},
		{ type: 'int', name: 'attempt'},
		{ type: 'int', name: 'jobperiod'},
		{ type: 'date', name: 'starton'},
		{ type: 'date', name: 'endon'},
		{ type: 'date', name: 'createdon'},
		{ type: 'int', name: 'createdby'},
		{ type: 'date', name: 'modifiedon'},
		{ type: 'int', name: 'modifiedby'},
		{ type: 'int', name: 'status'},
		{ type: 'int', name: 'parentid'},
		{ type: 'string', name: 'checker'},
		{ type: 'string', name: 'remarks'},
		{ type: 'date', name: 'actionon'},
		{ type: 'string', name: 'requestaction'},
	]
});