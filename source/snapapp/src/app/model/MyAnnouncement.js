Ext.define('snap.model.MyAnnouncement', {
	extend: 'snap.model.Base',
	fields: [
		{ type: 'int', name: 'id' },
		{ type: 'string', name: 'code' },
		{ type: 'string', name: 'title' },
		{ type: 'string', name: 'content' },
		{ type: 'string', name: 'type' },
		{ type: 'string', name: 'locales' },
		{ type: 'int', name: 'status' },
		{ type: 'date', name: 'displaystarton' },
		{ type: 'date', name: 'displayendon' },
		{ type: 'date', name: 'approvedon' },
		{ type: 'int', name: 'approvedby' },
		{ type: 'date', name: 'disabledon' },
		{ type: 'int', name: 'disabledby' },
		{ type: 'date', name: 'createdon' },
		{ type: 'int', name: 'createdby' },
		{ type: 'date', name: 'modifiedon' },
		{ type: 'int', name: 'modifiedby' },
	]
});
