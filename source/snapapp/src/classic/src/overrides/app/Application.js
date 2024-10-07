/**
 * @class Ext.app.Application
 */
Ext.define('Ext.overrides.app.Application', {
    override: 'Ext.app.Application',
    uses: [
        'Ext.tip.QuickTipManager',
        'Ext.state.Manager',
        'snap.util.HttpStateProvider'
    ]
});
