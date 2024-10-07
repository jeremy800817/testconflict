Ext.define('snap.view.mycif.MyKycController', {
  extend: 'snap.view.mycif.MyCifBaseController',
  alias: 'controller.mycif-mykyc',

  loadChildView: function (theData) {
		var view = this.getView();
    var myKycForm = view.down('#myCifForm');
    if (myKycForm != null) {
      myKycForm.removeAll();

      var formItems = [
        {
          xtype: 'container',
          layout: 'hbox',
          defaults: {
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor',
          },
          items: [
            {
              title: 'NRIC Front & Back Image',
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
          defaults: {
            border: true
          },
          items: [
            {
              columnWidth: 0.5,
              defaultType: 'displayfield',
              defaults: {
                labelStyle: 'font-weight:bold',
              },
              layout: {
                type: 'hbox',
                pack: 'center',
                align: 'middle',

              },
              items: [
                {
                  height: '250px',
                  name: 'mykadfrontimage',
                  value: theData.mykadfrontimage,
                },
              ]
            },
            {
              defaultType: 'displayfield',
              defaults: {
                labelStyle: 'font-weight:bold',
              },
              layout: {
                type: 'hbox',
                pack: 'center',
                align: 'middle',
              },
              columnWidth: 0.5,
              items: [
                {
                  height: '250px',

                  name: 'mykadbackimage',
                  value: theData.mykadbackimage,
                },
              ]
            },
          ]
        },
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
              title: 'Details',
              header: {
                  // Custom style for Migasit
                  /*style: {
                      backgroundColor: '#204A6D',
                  },*/
                  style : 'border-color: #204A6D;',
                  titlePosition: 0,
                  items: [{
                      xtype: 'button',
                      text: 'EKYC Reminder Logs',
                      iconCls: 'x-fa fa-list',
                      reference: 'kyc-reminder',
                      // id: 'kyc-reminder-view-button',
                      //style: 'background-color: #B2C840'
                      style: 'border-radius: 20px;background-color: #606060;border-color: #204A6D',
                      listeners : {
                          render: function(p) {
                          
                              this.getEl().dom.title = 'View all Email Reminders sent out to client';
          
                          },
                          click: function() { 
                          
                            // point to ekyc reminder form
                            formEkycReminder = this.up().up().up().up().up().up().up().formEkycReminder;
                            accountholderid = this.up().up().up().up().up().lookupReferenceHolder("kycmanualapproveremarks").theData.accountholderid
                            accountholdername = this.up().up().up().up().up().lookupReferenceHolder("kycmanualapproveremarks").theData.accountholdername
                            kycremindercount = this.up().up().up().up().up().lookupReferenceHolder("kycmanualapproveremarks").theData.kycremindercount
                            
                            // override form 
                            // Replace name
                            formEkycReminder.formPanelItems[0].items[1].items[0].items[0].value = accountholdername;
                            // Replace Record Count
                            formEkycReminder.formPanelItems[0].items[1].items[0].items[1].value = kycremindercount;
                            // Replace Store

                            formEkycReminder.formPanelItems[0].items[2].items[0].store.proxy.url = 'index.php?hdl=mykycreminder&action=list&id='+accountholderid;
                          
                            // url: 'index.php?hdl=mykycreminder&action=list&id='+theData.accountholderid,
                            var gridFormView = Ext.create('snap.view.gridpanel.GridForm', Ext.apply(formEkycReminder ? formEkycReminder : {}, {
                              formDialogButtons: [{
                                  text: 'Close',
                                  handler: function(btn) {
                                      owningWindow = btn.up('window');
                                      owningWindow.close();
                                      me.gridFormView = null;
                                  }
                              },]
                             }));

                             // set value 
                             gridFormView.lookupReference('mykycreminder');
                             gridFormView.lookupReference('mykycreminder').getStore().reload()
                             
                            //  value: theData.accountholdername,
                            //  value: theData.kycremindercount,
                            //  store: {
                            //   type: 'MyCommission', proxy: {
                            //       type: 'ajax',
                            //       url: 'index.php?hdl=mykycreminder&action=list&id='+theData.accountholderid,
                            //       reader: {
                            //           type: 'json',
                            //           rootProperty: 'records',
                            //       }
                            //   },
                          // },
                             this.gridFormView = gridFormView;
                             this.gridFormView.show();
                          }
                          
                      }
                  }]
              },
              items: [
                {
                  columnWidth:0.5,
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },

                  items: [
                    {
                      fieldLabel: 'KYC Status',
                      name: 'kycstatus',
                      value: theData.kycstatus
                    },
                    {
                      fieldLabel: 'Document Type',
                      name: 'documenttype',
                      value: theData.documenttype
                    },
                    {
                      fieldLabel: 'Result',
                      name: 'result',
                      value: theData.result
                    },
                    {
                      fieldLabel: 'Journey ID',
                      name: 'journeyid',
                      value: theData.journeyid
                    },
                    {
                      fieldLabel: 'Manual Approval Timestamp',
                      name: 'kycmanualapproveon',
                      value: theData.kycmanualapproveon,
                      reference: 'kycmanualapproveon',
                      hidden: true,
                    },
                    {
                      fieldLabel: 'Manually Approved By',
                      name: 'kycmanualapproveby',
                      value: theData.kycmanualapproveby,
                      reference: 'kycmanualapproveby',
                      hidden: true,
                    },
                  ]
                },
                {
                  columnWidth:0.5,
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },

                  items: [
                    {
                      fieldLabel: 'Remarks',
                      name: 'remarks',
                      value: theData.remarks
                    },
                    {
                      fieldLabel: 'Submission Status',
                      name: 'submissionstatus',
                      value: theData.submissionstatus
                    },
                    {
                      fieldLabel: 'Last Submission On',
                      name: 'lastsubmissionon',
                      value: theData.lastsubmissionon
                    },
                    {
                      fieldLabel: 'Is KYC Manually Approved',
                      name: 'iskycmanualapproved',
                      value: theData.iskycmanualapproveddisplay,
                      reference: 'iskycmanualapproved',
                    },
                    {
                      fieldLabel: 'Manual Approval Remarks',
                      name: 'kycmanualapproveremarks',
                      value: theData.kycmanualapproveremarks,
                      reference: 'kycmanualapproveremarks',
                      hidden: true,
                    }
                  ]
                }
              ]
            }
          ]
        },
      ];
      if (Array.isArray(formItems) && formItems.length > 0) myKycForm.add(formItems);
      else myKycForm.add(theData.formitems);
      myKycForm.updateLayout();

      // Post layout settings
      // If KYC Manual, show fields
      if(theData.iskycmanualapproved == true){
        myKycForm.lookupReferenceHolder().lookupReference('kycmanualapproveon').setHidden(false);
        myKycForm.lookupReferenceHolder().lookupReference('kycmanualapproveby').setHidden(false);
        myKycForm.lookupReferenceHolder().lookupReference('kycmanualapproveremarks').setHidden(false);
      }else{
        myKycForm.lookupReferenceHolder().lookupReference('kycmanualapproveon').setHidden(true);
        myKycForm.lookupReferenceHolder().lookupReference('kycmanualapproveby').setHidden(true);
        myKycForm.lookupReferenceHolder().lookupReference('kycmanualapproveremarks').setHidden(true);
      }

      // Store data in obj
      this.theData = theData;

    }
  },

  exportMintedListButton: function(type){

      // grid header data
      header = [];

    
      
      //type = btn.reference;
      

      const reportingFields = [
          //  ['Serial Number', ['serial', 0]], 
          //  ['In Stock', ['quantity', 0]],
          ['GS-999-9-0.5g', ['GS-999-9-0.5g', 0]], 
          ['GS-999-9-1g', ['GS-999-9-1g', 0]], 
          ['GS-999-9-2.5g', ['GS-999-9-2.5g', 0]], 
          ['GS-999-9-5g', ['GS-999-9-5g', 0]], 
          ['GS-999-9-10g', ['GS-999-9-10g', 0]], 
          ['GS-999-9-50g', ['GS-999-9-50g', 0]], 
          ['GS-999-9-100g', ['GS-999-9-100g', 0]], 
          ['GS-999-9-1000g', ['GS-999-9-1000g', 0]], 
          ['GS-999-9-1-DINAR', ['GS-999-9-1-DINAR', 0]], 
          ['GS-999-9-5-DINAR', ['GS-999-9-5-DINAR', 0]], 
          
      ];
      //{ key1 : [val1, val2, val3] } 
      
      for (let [key, value] of reportingFields) {
          //alert(key + " = " + value);
          columnleft = {
              // [_key]: _value
              text: key,
              index: value[0]
          }
          
          if (value[0] !== 0){
              columnleft.decimal = value[1];
          }
          header.push(columnleft);
      }

      // btn.up('grid').getColumns().map(column => {
      //     if (column.isVisible() && column.dataIndex !== null){
      //         _key = column.text
      //         _value = column.dataIndex
      //         columnlist = {
      //             // [_key]: _value
      //             text: _key,
      //             index: _value
      //         }
      //         if (column.exportdecimal !== null){
      //             _decimal = column.exportdecimal;
      //             columnlist.decimal = _decimal;
      //         }
      //         header.push(columnlist);
      //     }
      // });

      startDate = '2000-01-01 00:00:00';
      endDate = '2100-01-01 23:59:59';

      daterange = {
          startDate: startDate,
          endDate: endDate,
      }

      header = encodeURI(JSON.stringify(header));
      daterange = encodeURI(JSON.stringify(daterange));

      url = '?hdl=goldbarstatus&action=exportMintedList&header='+header+'&daterange='+daterange+'&type='+type;
      // url = Ext.urlEncode(url);

      Ext.DomHelper.append(document.body, {
          tag: 'iframe',
          id:'downloadIframe',
          frameBorder: 0,
          width: 0,
          height: 0,
          css: 'display:none;visibility:hidden;height: 0px;',
          src: url
        });
  },

});
