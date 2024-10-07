Ext.define('snap.store.MyPepMatchData', {
    extend: 'snap.store.Base',
    model: 'snap.model.MyPepMatchData',
    alias: 'store.MyPepMatchData',
	storeId:'mypepmatchdata',
    autoLoad: false,
    proxy: {
        type: 'ajax',
        url: 'index.php?hdl=mypepsearchresult&action=getPepMatchData',
        reader: {
            type: 'json',
            rootProperty: 'records',
        }
    },
});
