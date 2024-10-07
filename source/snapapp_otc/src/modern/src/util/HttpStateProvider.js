Ext.define('snap.util.HttpStateProvider', {

    extend: 'Ext.state.Provider',
    requires: [
        'Ext.state.Provider',
        'Ext.Ajax'
    ],
    alias: 'util.HttpProvider',

    config: {
        app: null,
        another: null
    },

    constructor: function (config) {
        var me = this;
        this.initConfig(config);
        me.callParent(arguments);
        me.restoreState();
    },

    set: function (name, value) {
        var me = this;
        if (typeof value == 'undefined' || value === null) {
            me.clear(name);
            return;
        }
        if(Ext.encode(me.state[name]) == Ext.encode(value)) {
            return;
        }
        me.saveStateForKey(name, value);
        me.callParent(arguments);
    },

    // private
    restoreState: function () {
        var me = this,
            states = (me.getApp() && me.getApp().states) || [];
        for(var i = 0; i < states.length; i++) {
            me.state[states[i].key] = me.decodeValue(states[i].value);
        }
    },

    // private
    clear: function (name) {
        var me = this;
        me.clearStateForKey(name);
        me.callParent(arguments);
    },

    // private
    saveStateForKey: function (key, value) {
        var me = this;
        snap.getApplication().sendRequest({ 
            hdl: 'appstate',
            action: 'update',
            key: key,
            value: me.encodeValue(value)
        })/*.then(function()  {
            console.log('snap.util.HttpStateProvider: saveStateForKey failed', arguments);
        })*/;
    },

    // private
    clearStateForKey: function (key) {
        var me = this;
        snap.getApplication.sendRequest({
            hdl: 'appstate',
            action: 'delete',
            key: key
        })/*.then(function() {
            console.log('snap.util.HttpStateProvider: clearStateForKey failed', arguments);
        })*/;
    }
});