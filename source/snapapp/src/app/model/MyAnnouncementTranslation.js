Ext.define('snap.model.MyAnnouncementTranslation', {
    extend: 'snap.model.Base',
    fields: [
        { type: 'int', name: 'id' },
        { type: 'string', name: 'language' },
        { type: 'string', name: 'title' },
        { type: 'string', name: 'content' },

    ]
});
