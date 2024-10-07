Ext.define('snap.view.mycif.MyGoldBalanceController', {
  extend: 'snap.view.mycif.MyCifBaseController',
  alias: 'controller.mycif-mygoldbalance',

  loadChildView: function (theData) {
    var view = this.getView();
    var myGoldBalanceForm = view.down('#myCifForm');

    var accountHolderId = view.up('#mycif').accountHolderId;

    // Post layout settings
    // If account is closed, show 0 
    if(theData.isclosed == true){
      theData.availablebalance = parseFloat(0).toFixed(3);
    }
      
    if (myGoldBalanceForm != null) {
      myGoldBalanceForm.removeAll();

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
              layout: 'column',
              defaults: {
                border: true
              },
              items: [
                {
                  margin: '0 0 0 0',
                  title: 'Total Balance',
                  columnWidth: 0.5,
                  layout: {
                    type: 'hbox',
                    pack: 'center',
                    align: 'middle',

                  },
                  items: [
                    {
                      html: `<div style="padding:3em;text-align: center;vertical-align: middle;line-height: 40px;"><span style="font-size:3em;font-weight:900;width:100%">${theData.totalbalance}</span></div>`
                    }
                  ]
                },
                {
                  margin: '0 0 0 0',
                  title: 'Available Balance',
                  columnWidth: 0.5,
                  layout: {
                    type: 'hbox',
                    pack: 'center',
                    align: 'middle',

                  },
                  items: [
                    {
                      html: `<div style="padding:3em;text-align: center;vertical-align: middle;line-height: 40px;"><span style="font-size:3em;font-weight:900;width:100%">${theData.availablebalance}</span></div>`

                    }
                  ]
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
              reference: 'goldstatementtab',
              items: [
                {
                  title: 'Gold Statement',
                  layout: 'hbox',
                  plugins: {
                    ptype: 'lazyitems',
                    items: [
                      {
                        flex: 1,
                        xtype: 'mygoldstatementview',
                        defaultPageSize: 10,
                        reference: 'mygoldstatement',
                        accountHolderId: accountHolderId,
                        store: {
                          type: 'MyGoldStatement',
                          autoLoad: true,
                          proxy: {
                            type: 'ajax',
                            url: 'index.php?hdl=mygoldstatement&action=list&accountholderid=' + accountHolderId,
                            reader: {
                              type: 'json',
                              rootProperty: 'records',
                              idProperty: 'index'
                            }
                          },
                        }
                      },
                    ]
                  }
                },
                {
                  title: 'Monthly Admin & Storage Fee',
                  layout: 'hbox',
                  plugins: {
                    ptype: 'lazyitems',
                    items: [
                      {
                        flex: 1,
                        xtype: 'mymonthlystoragefeeprofileview',
                        defaultPageSize: 10,
                        reference: 'mymonthlystoragefee',
                        accountHolderId: accountHolderId,                       
                        store: {
                          type: 'MyMonthlyStorageFee',
                          autoLoad: true,
                          proxy: {
                            type: 'ajax',
                            url: 'index.php?hdl=mymonthlystoragefee&action=list&accountholderid=' + accountHolderId,
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
                {
                  title: 'Daily Admin & Storage Fee',
                  layout: 'hbox',
                  plugins: {
                    ptype: 'lazyitems',
                    items: [
                      {
                        flex: 1,
                        xtype: 'mydailystoragefeeprofileview',
                        reference: 'mydailystoragefee',
                        accountHolderId: accountHolderId,
                        store: {
                          type: 'MyDailyStorageFee',
                          autoLoad: true,
                          proxy: {
                            type: 'ajax',
                            url: 'index.php?hdl=mydailystoragefee&action=list&accountholderid=' + accountHolderId,
                            reader: {
                              type: 'json',
                              rootProperty: 'records',
                            }
                          },
                        }
                      },
                    ]
                  }
                }
              ]
            }
          ]
        }
      ];
      if (Array.isArray(formItems) && formItems.length > 0) myGoldBalanceForm.add(formItems);
      else myGoldBalanceForm.add(theData.formitems);
      myGoldBalanceForm.updateLayout();
    }
  }

});
