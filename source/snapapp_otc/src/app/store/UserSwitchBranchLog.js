Ext.define('snap.store.UserSwitchBranchLog', {
    extend: 'snap.store.Base',
    model: 'snap.model.UserSwitchBranchLog',
    alias: 'store.UserSwitchBranchLog',
    autoLoad: true,
    sorters: [{
        property: 'id',
        direction: 'DESC'
    }]
});
