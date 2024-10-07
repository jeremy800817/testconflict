Ext.define('snap.model.MyDocumentationTranslation', {
    extend: 'snap.model.Base',
    idProperty: 'locid',
    fields: [
        { type: 'int', name: 'locid' },
        { type: 'string', name: 'loclanguage' },
        { type: 'string', name: 'locfilename' },
        { type: 'string', name: 'locfilecontent' },
        { type: 'string', name: 'loccreatedon' },
        { type: 'string', name: 'locmodifiedon' },
        { type: 'string', name: 'loccreatedbyname' },
        { type: 'string', name: 'locmodifiedbyname' },
    ]
});
