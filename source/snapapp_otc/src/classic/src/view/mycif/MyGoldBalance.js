Ext.define('snap.view.mycif.MyGoldBalance',{
  extend: 'snap.view.mycif.MyCifFormBase',
  xtype: 'mygoldbalanceview',
  alias: 'mygoldbalanceview',

  requires: [
      'snap.view.mycif.MyGoldBalanceController'
  ],

  controller: 'mycif-mygoldbalance',

  initComponent: function() {
      this.callParent(arguments);
  },

  listeners: {
  },

  theData: null
});
