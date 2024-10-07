Ext.define('snap.view.mycif.MyKyc', {
  extend: 'snap.view.mycif.MyCifFormBase',
  xtype: 'mykycview',
  alias: 'mykycview',

  requires: [
    'snap.view.mycif.MyKycController'
  ],
  controller: 'mycif-mykyc',
  listeners: {
  },

  initComponent: function () {
    this.callParent(arguments);
  },

  theData: null
});
