Ext.define('snap.model.EventModuleType', {
    extend: 'snap.model.Base',
    proxy: { 
        type: 'ajax', 
        api :{
            read : 'index.php?hdl=eventtrigger&action=listeventmoduletype',
            create: 'index.php?hdl=eventtrigger&action=addeventmoduletype',
            update: 'index.php?hdl=eventtrigger&action=updateeventmoduletype',
            destroy: 'index.php?hdl=eventtrigger&action=deleteeventmoduletype'
        }
    },
    fields: [
        'id',
        'name',
        'desc'
    ]
});