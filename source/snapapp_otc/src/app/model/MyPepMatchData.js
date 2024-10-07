Ext.define('snap.model.MyPepMatchData', {
  extend: 'snap.model.Base',
  fields: [
    { type: 'int', name: 'score' },
    { type: 'int', name: 'accountholderid' },
    { type: 'int', name: 'id' },
    { type: 'int', name: 'personid' },
    { type: 'string', name: 'title' },
    { type: 'string', name: 'name' },
    { type: 'date', name: 'dateofbirth' },
    { type: 'boolean', name: 'ispep' },
    { type: 'int', name: 'peplevel' },
    { type: 'string', name: 'detail' },
    //{type: 'string', name: 'addresses'},
    //{type: 'string', name: 'aliases'},
  ]
});
