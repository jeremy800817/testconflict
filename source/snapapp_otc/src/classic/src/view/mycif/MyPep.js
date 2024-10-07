Ext.define('snap.view.mycif.MyPep',{
  extend: 'snap.view.mycif.MyCifFormBase',
  xtype: 'mypepview',
  alias: 'mypepview',
  requires: [
      'snap.view.mycif.MyPepController'
  ],
  controller: 'mycif-mypep',
  listeners: {

  },
  initComponent: function() {
      this.callParent(arguments);
  },

  theData: null
});
