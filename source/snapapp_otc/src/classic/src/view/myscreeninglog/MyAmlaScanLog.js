Ext.define('snap.view.myamlascanlog.MyAmlaScanLog', {
  extend: 'snap.view.gridpanel.Base',
  xtype: 'myamlascanlogview',
  alias: 'myamlascanlogview',

  requires: [
    'snap.view.myamlascanlog.MyAmlaScanLogController'
  ],

  controller: 'myamlascanlog-myamlascanlog',

  initComponent: function () {
    this.callParent(arguments);
  },

  requires: [
    'snap.store.MyAmlaScanLog',
    'snap.model.MyAmlaScanLog',
    'snap.view.myamlascanlog.MyAmlaScanLogController',
    'snap.view.myamlascanlog.MyAmlaScanLogModel'
  ],
  permissionRoot: '/root/bmmb/screeninglog',
  store: { type: 'MyAmlaScanLog' },
  controller: 'myamlascanlog-myamlascanlog',

  viewModel: {
    type: 'myamlascanlog-myamlascanlog'
  },

  detailViewWindowHeight: 400,

  enableFilter: true,
  toolbarItems: [
    'detail', 'filter',
  ],
  listeners: {
    afterrender: function () {
      this.store.sorters.clear();
      this.store.sort([{
        property: 'id',
        direction: 'DESC'
      }]);
    }
  },
  columns: [
    {
      text: 'Status', minWidth: 100,
      filter:false,
      renderer: function (val, meta, record, rowIndex) {
        if (null == record.data.scmmatchedon) return 'Pass';
        else return 'Matched';
      }
    },
    { text: 'Scanned on', dataIndex: 'scannedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
    { text: 'Remarks', dataIndex: 'scmremarks', filter: { type: 'string' }, minWidth: 130, flex: 1 },
    { text: 'Matched on', dataIndex: 'scmmatchedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
    { text: 'Matched Data', dataIndex: 'scmmatcheddata', filter: { type: 'string' }, minWidth: 130, flex: 1 },
    { text: 'Source', dataIndex: 'sclsourcetype', filter: { type: 'string' }, minWidth: 130, flex: 1 },
    {
      text: 'Matched Status', dataIndex: 'scmstatus', filter: { type: 'string' }, minWidth: 130, flex: 1, filter: false, renderer: function (val, meta, record, rowIndex) {
        if ('0' == val) return 'Pending';
        else if ('1' == val) return 'Ignored';
        else if ('2' == val) return 'Blacklisted';
        else if ('3' == val) return 'Suspended';
        else return '';
      }
    },
  ],

  //////////////////////////////////////////////////////////////
  /// View properties settings
  ///////////////////////////////////////////////////////////////
  enableDetailView: true,
  detailViewWindowHeight: 500,
  detailViewWindowWidth: 500,
  style: 'word-wrap: normal',
  detailViewSections: { default: 'Properties' },
  detailViewUseRawData: true,

  formConfig: {
    controller: 'myamlascanlog-myamlascanlog',
    formDialogTitle: 'Gold Statement',
    formDialogWidth: 950,
    enableFormDialogClosable: false,
    formPanelDefaults: {
      border: false,
      xtype: 'panel',
      flex: 1,
      layout: 'anchor',
      msgTarget: 'side',
      margins: '0 0 10 10'
    },
    enableFormPanelFrame: false,
    formPanelLayout: 'hbox',


    formPanelItems: [
      { inputType: 'hidden', hidden: true, name: 'id' },
      { xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'status', value: '1' },

    ]
  },
});
