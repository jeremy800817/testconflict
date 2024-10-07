Ext.define('snap.view.mycif.MyAmla', {
  extend: 'snap.view.mycif.MyCifFormBase',
  xtype: 'myamlaview',
  alias: 'myamlaview',

  requires: [
    'snap.view.mycif.MyAmlaController'
  ],
  controller: 'mycif-myamla',
  listeners: {
  },

  initComponent: function () {
    this.callParent(arguments);
  },

  theData: null
});
