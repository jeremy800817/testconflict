Ext.define('snap.model.EventGroupType', {
    extend: 'snap.model.Base',
    proxy: { 
        type: 'ajax', 
        api :{
            read : 'index.php?hdl=eventtrigger&action=listeventgrouptype',
            create: 'index.php?hdl=eventtrigger&action=addeventgrouptype',
            update: 'index.php?hdl=eventtrigger&action=updateeventgrouptype',
            destroy: 'index.php?hdl=eventtrigger&action=deleteeventgrouptype'
        }
    },
    fields: [
        'id',
        'name',
        'desc'
    ]
});