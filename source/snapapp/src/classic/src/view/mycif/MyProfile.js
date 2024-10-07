Ext.define('snap.view.mycif.MyProfile',{
  extend: 'snap.view.mycif.MyCifFormBase',
  xtype: 'myprofileview',
  alias: 'myprofileview',

  requires: [
      'snap.view.mycif.MyProfileController'
  ],

  controller: 'mycif-myprofile',
  listeners: {
  },

  initComponent: function() {
      this.callParent(arguments);
  },

  theData: null,
});
