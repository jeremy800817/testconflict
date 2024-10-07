Ext.define('snap.view.mycif.MyPepController', {
  extend: 'snap.view.mycif.MyCifBaseController',
  alias: 'controller.mycif-mypep',

  loadChildView: function (theData) {
    var view = this.getView();
    var myPepForm = view.down('#myCifForm');
    var accountHolderId = view.up('#mycif').accountHolderId;

    if (myPepForm != null) {
      myPepForm.removeAll();

      var formItems = [
        {
          xtype: 'container',
          collapsible: false,
          layout: 'hbox',
          defaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor'
          },
          items: [
            {
              title: 'Details & Declaration',
              layout: 'column',
              items: [
                {
                  columnWidth: 1,
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },

                  items: []
                }
              ]
            }
          ]
        },
        {
          xtype: 'container',
          collapsible: false,
          layout: 'column',
          items: [
            {
              columnWidth: 1,
              defaultType: 'displayfield',
              defaults: {
                labelStyle: 'font-weight:bold',
              },

              items: [
                {
                  fieldLabel: 'PEP Status',
                  name: 'pepstatus',
                  value: theData.pepstatus
                },
                {
                  fieldLabel: 'Remarks',
                  name: 'documenttype',
                  value: theData.remarks
                },
              ]
            }
          ]
        },
        {
          xtype: 'container',
          layout: 'hbox',
          items: [
            {
              xtype: 'tabpanel',
              flex: 1,
              reference: 'partnertab',
              items: [
                {
                  title: 'Questionnaire',
                  layout: 'fit',
                  dockedItems: [{
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                      { reference: 'printButton', text: 'Print', itemId: 'printButton', tooltip: 'Print', iconCls: 'x-fa fa-print', handler: 'printQuestionnaireButton', validSelection: 'single' },
                    ]
                  }],
                  items: [
                    {
                      xtype: 'menuseparator',
                      width: '100%',
                    },
                    {
                      html: theData.questionnaire,
                    }
                  ]
                },
                {
                  title: 'Search Result',
                  plugins: {
                    ptype: 'lazyitems',
                    items: [
                      {
                        title: '',
                        flex: 13,
                        xtype: 'mypepmatchdataview',
                        reference: 'mypepematchdata',
                        store: {
                          type: 'MyPepMatchData',
                          proxy: {
                            type: 'ajax',
                            url: 'index.php?hdl=mypepsearchresult&action=getPepMatchData&accountholderid=' + accountHolderId,
                            reader: {
                              type: 'json',
                              rootProperty: 'records',
                            }
                          },
                        }
                      }
                    ]
                  },
                  layout: 'hbox',
                }
              ]
            }
          ]
        }
      ];
      if (Array.isArray(formItems) && formItems.length > 0) myPepForm.add(formItems);
      else myPepForm.add(theData.formitems);
      myPepForm.updateLayout();
    }
  },

  printQuestionnaireButton: function () {

    var accountholderid = this.getView().up("mycifview").accountHolderId;
    var url = 'index.php?hdl=mycif&action=printPepQuestionnaire&accountholderid=' + accountholderid;

    window.open(url, '_blank');
  }
});
