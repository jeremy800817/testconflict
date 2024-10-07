Ext.define('snap.view.mycif.MyProfileController', {
  extend: 'snap.view.mycif.MyCifBaseController',
  alias: 'controller.mycif-myprofile',

  loadChildView: function (theData) {
    var view = this.getView();
    var myProfileForm = view.down('#myCifForm');
    if (myProfileForm != null) {
      myProfileForm.removeAll();

      var formItems = [
        {
          xtype: 'panel',
          title: 'Information',
          layout: 'hbox',
          collapsible: true,

          defaults: {
            layout: 'vbox',
            flex: 1,
            bodyPadding: 10
          },
          items: [
            {
              defaultType: 'displayfield',
              defaults: {
                labelStyle: 'font-weight:bold',
              },
              items: [
                {
                  fieldLabel: 'Full Name',
                  name: 'fullname',
                  value: theData.information.fullname
                },                
                {
                  fieldLabel: 'NRIC No',
                  name: 'mykadno',
                  value: theData.information.mykadno.slice(0, 6) + '-' + theData.information.mykadno.slice(6, 8) + '-' + theData.information.mykadno.slice(-4)
                },
                {
                  fieldLabel: 'Occupation Category',
                  name: 'occupationcategory',
                  value: theData.information.occupationcategory
                },               
                {
                  fieldLabel: 'Occupation',
                  name: 'occupation',
                  value: theData.information.occupation
                },
                {
                  fieldLabel: 'Email Notification On',
                  name: 'emailnotificationon',
                  value: theData.information.emailtriggeredon
                },
              ]
            },
            {
              defaultType: 'displayfield',
              defaults: {
                labelStyle: 'font-weight:bold',
              },
              items: [
               
                {
                  fieldLabel: 'Status',
                  name: 'status',
                  value: theData.information.status
                },
                {
                  fieldLabel: 'Verification',
                  name: 'verification',
                  value: theData.information.verification
                },
                {
                  fieldLabel: 'Verified On',
                  name: 'verifiedon',
                  value: theData.information.verifiedon
                },
                {
                  fieldLabel: 'Last Login IP',
                  name: 'lastloginip',
                  value: theData.information.lastloginip
                },
                {
                  fieldLabel: 'Last Login On',
                  name: 'lastloginon',
                  value: theData.information.lastloginon
                },                
              ]
            }
          ]

        },
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
              layout: 'column',
              defaults: {
                flex: 1,
                bodyPadding: 10
              },
              items: [
                {

                  columnWidth: 0.5,
                  layout: {
                    type: 'vbox',
                  },
                  collapsible: true,
                  title: 'Contact',
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },

                  items: [
                    {
                      fieldLabel: 'Email Address',
                      name: 'email',
                      value: theData.contact.email
                    },
                    {
                      fieldLabel: 'Phone No.',
                      name: 'phoneno',
                      value: theData.contact.phoneno.slice(0,5)+'-'+theData.contact.phoneno.slice(5)
                    },
                    {
                      fieldLabel: 'Address Line 1',
                      name: 'line1',
                      value: theData.contact.line1
                    },
                    {
                      fieldLabel: 'Address Line 2',
                      name: 'line2',
                      value: theData.contact.line2
                    },
                    {
                      fieldLabel: 'City',
                      name: 'city',
                      value: theData.contact.city
                    },
                    {
                      fieldLabel: 'Postcode',
                      name: 'postcode',
                      value: theData.contact.postcode
                    },
                    {
                      fieldLabel: 'State',
                      name: 'state',
                      value: theData.contact.state,
                      hidden: PROJECTBASE === 'BSN',
                    },
                  ]

                },
                {
                  columnWidth: 0.5,
                  layout: {
                    type: 'vbox',
                  },
                  collapsible: true,
                  title: 'Account & Banking Info',
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },

                  items: [     
                    {
                      fieldLabel: PROJECTBASE === 'BSN' ? 'Gold Account No' : 'Account Code',
                      name: 'accountcode',
                      value: theData.information.accountholdercode,
                    },
                    
                    {
                      fieldLabel: 'Investment Made',
                      name: 'investmentmade',
                      value: theData.information.investmentmade
                    },
                    {
                      fieldLabel: 'Bank Name',
                      name: 'bankname',
                      value: theData.bankinginfo.bankname
                    },
                    {
                      fieldLabel: 'Bank Account Name',
                      name: 'accountname',
                      value: theData.bankinginfo.accountname
                    },
                    {
                      fieldLabel: 'Bank Account Number',
                      name: 'accountnumber',
                      value: theData.bankinginfo.accountnumber
                    }                   
                  ]
                },

              ]
            }
          ]
        },
        {
          xtype: 'container',
          collapsible: false,
          layout: 'hbox',
          defaults: {
            bodyPadding: 10,
            border: false,
            xtype: 'panel',
            flex: 1,
            layout: 'anchor'
          },
          items: [
            {
              collapsible: true,
              title: 'Next Of Kin',
              layout: 'column',
              items: [
                {
                  columnWidth: 0.5,
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },

                  items: [
                    {
                      fieldLabel: 'Full Name',
                      name: 'nokfullname',
                      value: theData.nextofkin.nokfullname
                    },
                    {
                      fieldLabel: 'NRIC No',
                      name: 'nokmykadno',
                      value: theData.nextofkin.nokmykadno.slice(0, 6) + '-' + theData.nextofkin.nokmykadno.slice(6, 8) + '-' + theData.nextofkin.nokmykadno.slice(-4)                      
                    },
                  ]
                },
                {
                  columnWidth: 0.5,
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },

                  items: [
                    {
                      fieldLabel: 'Bank Name',
                      name: 'nokbankname',
                      value: theData.nextofkin.nokbankname
                    },
                    {
                      fieldLabel: 'Bank Account Number',
                      name: 'nokaccountnumber',
                      value: theData.nextofkin.nokaccountnumber
                    }
                  ]
                },
              ]
            }
          ]
        },
      ];
      if (Array.isArray(formItems) && formItems.length > 0) myProfileForm.add(formItems);
      else myProfileForm.add(theData.formitems);
      myProfileForm.updateLayout();
    }
  }
});
