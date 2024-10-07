Ext.define('snap.model.MyAnnouncementTheme', {
	extend: 'snap.model.Base',
	fields: [
		{ type: 'int', name: 'id' },
		{ type: 'string', name: 'name' },
		{ type: 'string', name: 'templatename' },
		{ type: 'string', name: 'rank' },
		{ type: 'date', name: 'displaystarton' },
		{ type: 'date', name: 'displayendon' },
		{ type: 'date', name: 'validfrom' },
		{ type: 'date', name: 'validto' },
		{ type: 'date', name: 'createdon' },
		{ type: 'int', name: 'createdby' },
		{ type: 'date', name: 'modifiedon' },
		{ type: 'int', name: 'modifiedby' },
		{ type: 'int', name: 'status' },
	]
});
