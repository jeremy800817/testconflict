Ext.define('snap.model.EventActionType', {
    extend: 'snap.model.Base',

    proxy: { 
        type: 'ajax', 
        api :{
            read : 'index.php?hdl=eventtrigger&action=listeventactiontype',
            create: 'index.php?hdl=eventtrigger&action=addeventactiontype',
            update: 'index.php?hdl=eventtrigger&action=updateeventactiontype',
            destroy: 'index.php?hdl=eventtrigger&action=deleteeventactiontype'
        }
    },
    fields: [
        'id',
        'name',
        'desc'
    ]

});