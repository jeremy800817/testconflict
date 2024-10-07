Ext.define('snap.view.mygoldstatement.MyGoldStatement', {
  extend: 'snap.view.gridpanel.Base',
  xtype: 'mygoldstatementview',
  alias: 'mygoldstatementview',

  requires: [
    'snap.view.mygoldstatement.MyGoldStatementController'
  ],

  controller: 'mygoldstatement-mygoldstatement',

  initComponent: function () {
    this.callParent(arguments);
  },

  requires: [
    'snap.store.MyGoldStatement',
    'snap.model.MyGoldStatement',
    'snap.view.mygoldstatement.MyGoldStatementController',
    'snap.view.mygoldstatement.MyGoldStatementModel'
  ],
  permissionRoot: '/root/bmmb/gold statement',
  store: { type: 'MyGoldStatement' },
  controller: 'mygoldstatement-mygoldstatement',

  viewModel: {
    type: 'mygoldstatement-mygoldstatement'
  },

  detailViewWindowHeight: 400,

  enableFilter: false,
  sortableColumns: false,
  accountHolderId: 0,
  toolbarItems: [
    {
      xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: [] }, name: 'startdateOn', labelWidth: 'auto'
    },
    {
      xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: [] }, name: 'enddateOn', labelWidth: 'auto'
    },
    {
      text: 'Print', tooltip: 'Print Report', iconCls: 'x-fa fa-print', handler: 'getPrintReport', showToolbarItemText: true, printType: 'xlsx', // printType: pending
    },
    {
      iconCls: 'x-fa fa-redo-alt', text: 'Filter', tooltip: 'Filter Date', handler: 'getDateRange', showToolbarItemText: true,
    },
    {
      iconCls: 'x-fa fa-times-circle', text: 'Clear', tooltip: 'Clear Date', handler: 'clearDateRange', showToolbarItemText: true,
    },
  ],
  listeners: {
    
  },
  columns: [
    { xtype: 'rownumberer', text: 'No.' },
    { text: 'Trx Date', dataIndex: 'transactiondate', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
    { text: 'Transaction Type', dataIndex: 'type', filter: { type: 'string' }, minWidth: 130, flex: 1 },
    { text: 'GTP Transaction Code', dataIndex: 'refno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
    { text: 'Gold Price RM/g', dataIndex: 'ordgoldprice', filter: { type: 'string' }, exportdecimal: 3, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00') },
    { text: 'Xau In', dataIndex: 'credit', filter: { type: 'string' }, exportdecimal: 3, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
    { text: 'Xau Out', dataIndex: 'debit', filter: { type: 'string' }, exportdecimal: 3, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
    { text: 'Xau Total Balance', dataIndex: 'xaubalance', filter: { type: 'string' }, exportdecimal: 3, exportcolumn: ['credit', 'debit'], minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.000') },
    // Amount in for customer is amount out for ACE
    { text: 'Amt In', dataIndex: 'amountout', filter: { type: 'string' }, exportdecimal: 2, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00') },
    // Amount out for customer is amount in for ACE
    { text: 'Amt Out', dataIndex: 'amountin', filter: { type: 'string' }, exportdecimal: 2, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00') },
    { text: 'Amt Total Balance', dataIndex: 'amountbalance', exportdecimal: 2, filter: { type: 'string' }, exportcolumn: ['amountin', 'amountout'], minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0,000.00') },
    { text: 'Remarks', dataIndex: 'remarks', filter: { type: 'string' }, minWidth: 130, flex: 1 },
  ],

  //////////////////////////////////////////////////////////////
  /// View properties settings
  ///////////////////////////////////////////////////////////////

});
