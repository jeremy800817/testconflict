Ext.define('snap.view.mycif.MyAmlaController', {
  extend: 'snap.view.mycif.MyCifBaseController',
  alias: 'controller.mycif-myamla',

  loadChildView: function (theData) {
    var view = this.getView();
    var MyAmlaForm = view.down('#myCifForm');
    var accountHolderId = view.up('#mycif').accountHolderId;
    if (MyAmlaForm != null) {
      MyAmlaForm.removeAll();

      var formItems = [
        {
          xtype: 'container',
          layout: 'hbox',
          defaults: {
            flex: 1,
            border: false,
            bodyPadding: 10
          },
          items: [
            {
              collapsible: true,
              layout: 'column',
              title: 'Information',
              items: [
                {
                  columnWidth:1,
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },

                  items: [
                    {
                      fieldLabel: 'AMLA Status',
                      name: 'amlastatus',
                      value: theData.amlastatus
                    },
                    {
                      fieldLabel: 'Remarks',
                      name: 'remarks',
                      value: theData.remarks
                    },
                    {
                      fieldLabel: 'Last Screening',
                      name: 'lastscreeningon',
                      value: theData.lastscreeningon
                    }
                  ]
                }
              ]
            },
          ]
        },
        {
          xtype: 'container',
          layout: 'hbox',
          items: [
            {
              xtype: 'tabpanel',
              flex: 1,
              reference: 'goldstatementtab',
              items: [
                {
                  title: 'Scan Log',
                  layout: 'hbox',
                  plugins: {
                    ptype: 'lazyitems',
                    items: [
                      {
                        flex: 1,
                        xtype: 'myamlascanlogview',
                        enableFilter: false,
                        toolbarItems: [
                          
                        ],
                        reference: 'myamlascanlog',
                        store: {
                          type: 'MyAmlaScanLog',
                          proxy: {
                            type: 'ajax',
                            url: 'index.php?hdl=myamlascanlog&action=list&accountholderid=' + accountHolderId,
                            reader: {
                              type: 'json',
                              rootProperty: 'records',
                            }
                          },
                        }
                      },
                    ]
                  }
                },
              ]
            }
          ]

        }
      ];
      if (Array.isArray(formItems) && formItems.length > 0) MyAmlaForm.add(formItems);
      else MyAmlaForm.add(theData.formitems);
      MyAmlaForm.updateLayout();
    }
  }

});
