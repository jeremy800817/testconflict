
Ext.define('snap.view.myyearlystatement.MyYearlyStatement_BSN',{
    extend: 'snap.view.orderdashboard.OrderDashboard_BSN',
    xtype: 'myyearlystatementview_BSN',
    
    items: {
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable:true,
        bodyPadding: 10,
    
        defaults: {
            frame: true,
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
            {
                height: 30,
                minHeight: 75,
                maxHeight: 800,
                layout: {
                    type: 'hbox',
                },
                margin: "10 0 0 0",
                defaults: {
                    bodyStyle: 'padding:0px;margin-top:10px',
                },
                cls: 'otc-main-center search_bar',
                // Size is 24 blocks spread across 3 screens
                items:[
                  { 
                      flex:1,
                      xtype:'combobox',
                      cls:'combo_box',
                      store: {
                          fields: ['type', 'name'],
                          data : [
                              {"type":"", "name":""},
                              {"type":"1", "name":"Customer ID"},
                              {"type":"2", "name":"MyKad No"},
                              {"type":"2", "name":"Passport Number"},
                              {"type":"2", "name":"Company Registration No"},
                              {"type":"4", "name":"GIRO/ GIRO i Account No"},
                              
                          ]
                      },
                      listeners: {
                          select: function(combo, records, eOpts) {
                              accountholdersearch = this.up().up().up().getController().lookupReference('accountholdersearch');
                              if(records.data.name == 'MyKad No'){
                                  newText = "Enter " + records.data.name + " here (without alphabet or '-')";
                              }else{
                                  newText = "Enter " + records.data.name + " here";
                              }
                              accountholdersearch.setEmptyText(newText);
                              // this.up().up().up().getController().lookupReference('casasearchtype').setValue(records.data.type);
                          }
                      },
                      reference: 'casasearchtype',
                      queryMode: 'local',
                      displayField: 'name',
                      valueField: 'type',
                      forceSelection: true,
                      editable: false,
                      margin: "0 10 0 10",
                  },
                  {   
                      flex: 4,
                      margin: '0 10 0 0',
                      items: [ {
                          xtype: 'textfield',
                          text: 'Search',
                          emptyText: '',
                          flex:1,
                          style: 'text-align:center;',
                          width: '90%',
                          reference: 'accountholdersearch',
                     
                      }]
                  },
                  
                  {   
                      flex:1,
                      xtype:'button',
                      text:'SEARCH',
                      iconCls: 'x-fa fa-search',
                      cls:'search_btn',
                      handler:'',
                      margin: "0 0 0 10",
                      handler: 'searchAccountHolder'
                  },
            
             
              ]

            },
            {
                xtype: 'panel',
                title: 'Search Results <span font-size: 5px;>(Please select a record to view data)</span>',
                reference: 'searchresults',
                border: false,
                hidden: true,
                margin: "10 0 0 0",
                items: [
                    {
                        title: '',
                        flex: 13,
                        xtype: 'myaccountholdersearchresultview',
                        reference: 'myaccountholdersearchresults',
                        enablePagination: true,
                        store: {
                            proxy: {
                                type: 'ajax',
                                url: '',
                                reader: {
                                    type: 'json',
                                    rootProperty: 'records',
                                }
                            },
                        },
                      columns: [
                          { text: 'ID', dataIndex: 'id', filter: { type: 'string' }, hidden: true, minWidth: 100, flex: 1 },
                          //{ text: 'Amount Balance', dataIndex: 'amountbalance',exportdecimal:2, filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0.00') },
                          { text: 'Gold Account No', dataIndex: 'accountholdercode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          { text: 'Partner Code', dataIndex: 'partnercode', hidden: true, filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          { text: 'Partner', dataIndex: 'partnername', hidden: true, filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          { text: 'Full Name', dataIndex: 'fullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          { text: 'My Kad / Passport No', dataIndex: 'mykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          { text: 'Customer ID', dataIndex: 'partnercusid', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Email', dataIndex: 'email', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Phone Number', dataIndex: 'phoneno', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Preferred Lang', dataIndex: 'preferredlang', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Occupation', dataIndex: 'occupation', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Occupation Category ID', dataIndex: 'occupationcategoryid', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Salesperson Code', dataIndex: 'referralsalespersoncode', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Branch Code', dataIndex: 'referralbranchcode', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Branch Name', dataIndex: 'referralbranchname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          //{ text: 'Pin Code', dataIndex: 'pincode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
                          { text: 'SAP Buy Code', dataIndex: 'sapacebuycode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
                          { text: 'SAP Sell Code', dataIndex: 'sapacesellcode', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
                          { text: 'Bank Name', dataIndex: 'bankname', filter: { type: 'string' }, minWidth: 130, hidden: true, flex: 1 },
                          { text: 'Bank Account Name', dataIndex: 'accountname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'GIRO/ GIRO i Account No', dataIndex: 'accountnumber', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Account Type', dataIndex: 'accounttype',   filter: {
                              type: 'combo',
                              store: [
                                  ['1', 'Sendiri'],
                                  ['2', 'Bersama'],
                                  ['3', 'Organis'],
                                  ['4', 'Amanah'],
                                  ['5', 'Unknown'],
                                  ['6', 'Cashless'],
                                  ['7', 'Cashlne'],
                              ],
                          },
                          renderer: function (value, rec) {
                              if (value == '1') return 'Sendiri';
                              else if (value == '2') return 'Bersama';
                              else if (value == '3') return 'Organis';
                              else if (value == '4') return 'Amanah';
                              else if (value == '5') return 'Unknown';
                              else if (value == '6') return 'Cashless';
                              else if (value == '7') return 'Cashlne';
                              else return 'Unidentified';
                          }, minWidth: 130, flex: 1, },
                          { text: 'Secondary Full Name', dataIndex: 'nokfullname', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          { text: 'Secondary Mykad No', dataIndex: 'nokmykadno', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          //{ text: 'Secondary Bank Name', dataIndex: 'nokbankname', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          //{ text: 'Secondary Account No', dataIndex: 'nokaccountnumber', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Investment Made', dataIndex: 'investmentmade',   filter: {
                              type: 'combo',
                              store: [
                                  ['0', 'No'],
                                  ['1', 'Yes'],
                              ],
                          },
                          renderer: function (value, rec) {
                              if (value == '0') return 'No';
                              else if (value == '1') return 'Yes';
                              else return 'Unidentified';
                          }, hidden: true, minWidth: 130, flex: 1, },
                          { text: 'Xau Balance', dataIndex: 'xaubalance', exportdecimal:3, filter: { type: 'string' }, minWidth: 130, flex: 1, renderer: Ext.util.Format.numberRenderer('0.000') },
                          // { 
                          //     text: 'loan total', dataIndex: 'loantotal', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                          //     editor: {    //field has been deprecated as of 4.0.5
                          //         xtype: 'numberfield',
                          //         decimalPrecision: 3
                          //     } 
                          // },
                          // { 
                          //     text: 'loan balance', dataIndex: 'loanbalance', exportdecimal:3, filter: { type: 'string' },  align: 'right', minWidth: 130, renderer: Ext.util.Format.numberRenderer('0,000.000'),
                          //     editor: {    //field has been deprecated as of 4.0.5
                          //         xtype: 'numberfield',
                          //         decimalPrecision: 3
                          //     } 
                          // },
                          // { text: 'Loan approved on', dataIndex: 'loanapprovedate', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
                          // { text: 'Approved by', dataIndex: 'loanapproveby', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          // {
                          //     text: 'Loan Status', dataIndex: 'loanstatus', minWidth: 100,
                          //     filter: {
                          //         type: 'combo',
                          //         store: [
                          //             ['0', 'No'],
                          //             ['1', 'Approved'],
                          //             ['2', 'Settled'],
                          //         ],
                          //     },
                          //     renderer: function (value, rec) {
                          //         if (value == '0') return 'No';
                          //         else if (value == '1') return 'Approved';
                          //         else if (value == '2') return 'Settled';
                          //         else return 'Unidentified';
                          //     },
                          // },
                          // { text: 'Reference Number', dataIndex: 'loanreference', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          {
                              text: 'Is PEP', dataIndex: 'ispep', minWidth: 100,
                              filter: {
                                  type: 'combo',
                                  store: [
                                      ['0', 'No'],
                                      ['1', 'Yes'],
                                  ],
                              },
                              renderer: function (value, rec) {
                                  if (value == '0') return 'No';
                                  else if (value == '1') return 'Yes';
                                  else return 'Unidentified';
                              },
                          },
                          { text: 'Pep Declaration', dataIndex: 'pepdeclaration', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          {
                              text: 'Status', dataIndex: 'status', minWidth: 100,
                  
                              filter: {
                                  type: 'combo',
                                  store: [
                                      ['0', 'Inactive'],
                                      ['1', 'Active'],
                                      ['2', 'Suspended'],
                                      ['4', 'Blacklisted'],
                                      ['5', 'Closed'],
                  
                                  ],
                  
                              },
                              renderer: function (value, rec) {
                                  if (value == '0') return '<span data-qtitle="Inactive" data-qwidth="200" '+
                                  'data-qtip="Account Pending Email Activation">'+
                                   "Inactive" +'</span>';
                                  else if (value == '1') return '<span data-qtitle="Active" data-qwidth="200" '+
                                  'data-qtip="Active Accounts">'+
                                   "Active" +'</span>';
                                  else if (value == '2') return '<span data-qtitle="Suspended" data-qwidth="200" '+
                                  'data-qtip="Accounts Pending Closure Approval">'+
                                   "Suspended" +'</span>';
                                  else if (value == '4') return '<span data-qtitle="Blacklisted" data-qwidth="200" '+
                                  'data-qtip="Blacklisted Accounts">'+
                                   "Blacklisted" +'</span>';
                                  else if (value == '5') return '<span data-qtitle="Closed" data-qwidth="200" '+
                                  'data-qtip="Accounts Successfully Closed">'+
                                   "Closed" +'</span>';
                                  else return '<span data-qtitle="Unidentified" data-qwidth="200" '+
                                  'data-qtip="Unknown Status">'+
                                   "Unidentified" +'</span>';
                              },
                              // renderer: function (value, rec) {
                              //     if (value == '0') return 'Inactive';
                              //     else if (value == '1') return 'Active';
                              //     else if (value == '2') return 'Suspended';
                              //     else if (value == '4') return 'Blacklisted';
                              //     else if (value == '5') return 'Closed';
                                      
                              //     else return 'Unidentified';
                              // },
                          },
                          {
                              text: 'PEP Status', dataIndex: 'pepstatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
                              filter: {
                                  type: 'combo',
                                  store: [
                                      ['0', 'Pending'],
                                      ['1', 'Passed'],
                                      ['2', 'Failed'],
                                  ],
                              },
                              renderer: function (val, m, record) {
                                  // If PEP
                                  if (record.data.ispep == 1) {
                                      if (record.data.pepstatus == 0) {
                                          // PEP Status Pending
                                          return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                                      } else if (record.data.pepstatus == 1) {
                                          // PEP Status Passed
                                          return '<span class="fa fa-circle x-color-success"></span>';
                                      } else if (record.data.pepstatus == 2) {
                                          // PEP Status Failed
                                          return '<span class="fa fa-circle x-color-danger"></span>';
                                      } 
                                  } else {
                                      // PEP Status Unidentified
                                      return '<span class="fa fa-circle x-color-default"></span>';
                                  }
                              }
                          },
                          {
                              text: 'Is KYC Manually Approved', dataIndex: 'iskycmanualapproved', minWidth: 100,
                              filter: {
                                  type: 'combo',
                                  store: [
                                      ['0', 'No'],
                                      ['1', 'Yes'],
                                  ],
                              },
                              renderer: function (value, rec) {
                                  if (value == '0') return 'No';
                                  else if (value == '1') return 'Yes';
                                  else return 'Unidentified';
                              },
                          },
                          {
                              text: 'KYC Status', dataIndex: 'kycstatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
                              filter: {
                                  type: 'combo',
                                  store: [
                                      ['0', 'Incomplete'],
                                      ['1', 'Passed'],
                                      ['2', 'Pending'],
                                      ['7', 'Failed'],
                                  ],
                              },
                              renderer: function (val, m, record) {
                  
                                  if (record.data.kycstatus == 0) {
                                      // eKYC Status Incomplete
                  
                                      if (record.data.kycpastday == false) {
                                          return '<span class="fa fa-circle x-color-default"></span>';
                                      } else {
                                          return '<span class="fa fa-circle x-color-warning"></span>';
                                      }
                                  } else if (record.data.kycstatus == 1) {
                                      // eKYC Status Passed
                                      return '<span class="fa fa-circle x-color-success"></span><span>';
                                  } else if (record.data.kycstatus == 2) {
                                      // eKYC Status Pending
                                      return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                  
                                  } else if (record.data.kycstatus == 7) {
                                      // eKYC Status Failed
                                      return '<span class="fa fa-circle x-color-danger"></span><span>';
                                  } else {
                                      // eKYC Status Unidentified
                                      return '<span class="fa fa-circle x-color-default"></span><span>';
                                  }
                              }
                          },
                  
                          //{ text: 'Amla Status',  dataIndex: 'amlastatus', filter: {type: 'string'} , minWidth:130, flex: 1 },
                          {
                              text: 'AMLA Status', dataIndex: 'amlastatus', filter: { type: 'string' }, minWidth: 100, align: 'center',
                              filter: {
                                  type: 'combo',
                                  store: [
                                      ['0', 'Pending'],
                                      ['1', 'Passed'],
                                      ['2', 'Failed'],
                                  ],
                              },
                              renderer: function (val, m, record) {
                                  // If KYC pass
                                  if (record.data.kycstatus == 1) {
                                      if (record.data.amlastatus == 0) {
                                          // AMLA Status Pending
                                          return '<span class="fas fa-spinner fa-spin x-color-warning"></span>';
                                      } else if (record.data.amlastatus == 1) {
                                          // AMLA Status Passed
                                          return '<span class="fa fa-circle x-color-success"></span><span>';
                                      } else if (record.data.amlastatus == 2) {
                                          // AMLA Status Failed
                                          return '<span class="fa fa-circle x-color-danger"></span><span>';
                                      } 
                                  } else {
                                      // AMLA Status Unidentified
                                      return '<span class="fa fa-circle x-color-default"></span><span>';
                                  }       
                              }
                          },
                          { text: 'Status Remarks', dataIndex: 'statusremarks', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          {
                              text: 'Dormant', dataIndex: 'dormant', filter: { type: 'string' }, minWidth: 100, align: 'center',
                              filter: {
                                  type: 'combo',
                                  store: [
                                      ['1', 'Yes'],
                                      ['0', 'No'],                    
                                  ],
                              },
                              renderer: function (val, m, record) {
                                  if (record.data.dormant) {
                                      return '<span class="fa fa-circle x-color-danger"></span><span>';
                                  } else {                    
                                      return '<span class="fa fa-circle x-color-success"></span><span>';
                                  }       
                              }
                          },
                          { text: 'Campaign Code', dataIndex: 'campaigncode', filter: { type: 'string' }, minWidth: 130, flex: 1 },
                          { text: 'Password Modified', dataIndex: 'passwordmodified', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', hidden: true, minWidth: 100 },
                          { text: 'Last Login on', dataIndex: 'lastloginon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', hidden: true, minWidth: 100 },
                          { text: 'Last Login IP', dataIndex: 'lastloginip', filter: { type: 'string' }, hidden: true, minWidth: 130, flex: 1 },
                          { text: 'Verified on', dataIndex: 'verifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100, hidden: true, },
                  
                          { text: 'Created on', dataIndex: 'createdon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100 },
                          { text: 'Modified on', dataIndex: 'modifiedon', xtype: 'datecolumn', format: 'Y-m-d H:i:s', filter: { type: 'date' }, inputType: 'hidden', minWidth: 100, hidden: true, },
                          { text: 'Created by', dataIndex: 'createdbyname', filter: { type: 'string' }, inputType: 'hidden', hidden: true, minWidth: 100 },
                          { text: 'Modified by', dataIndex: 'modifiedbyname', filter: { type: 'string' }, inputType: 'hidden', hidden: true, minWidth: 100 },
                  
                      ],
                      viewConfig : {
                          listeners : {
                              cellclick : function(view, cell, cellIndex, record,row, rowIndex, e) {

                                  // Store information to Viewmodel
                                  vm.set('profile-fullname', record.data.fullname);
                                  vm.set('profile-accountholdercode', record.data.accountholdercode);
                                  vm.set('profile-accounttype', record.data.accounttypestr);
                                  vm.set('profile-accountnumber', record.data.accountnumber);
                                  vm.set('profile-id', record.data.id);
                                  vm.set('profile-xaubalance', record.data.xaubalance);
                                  
                                  // Set profile info 
                                  vm.set('profile-mykadno', record.data.mykadno);
                                  vm.set('profile-email', record.data.email);
                                  vm.set('profile-phoneno', record.data.phoneno);
                                  vm.set('profile-amountbalance', record.data.amountbalance);

                                  // Set more informatiosn
                                  vm.set('profile-goldbalance', record.data.goldbalance);
                                  vm.set('profile-avgbuyprice', record.data.avgbuyprice);
                                  vm.set('profile-totalcostgoldbalance', record.data.totalcostgoldbalance);
                                  vm.set('profile-diffcurrentpriceprcetage', record.data.diffcurrentpriceprcetage);
                                  vm.set('profile-currentgoldvalue', record.data.currentgoldvalue);
                                  vm.set('profile-availablebalance', record.data.availablebalance);
                                  vm.set('profile-minbalancexau', record.data.minbalancexau);
                                  vm.set('profile-address', record.data.address ? record.data.address : '-');

                                  var getDisplayController = this.up().up().up().up().getController();

                                  getDisplayController.lookupReference('profile-fullname').setValue(record.data.fullname);
                                  getDisplayController.lookupReference('profile-occupationcategory').setValue(record.data.occupationcategory);
                                  getDisplayController.lookupReference('profile-mykadno').setValue(record.data.mykadno);
                                  getDisplayController.lookupReference('profile-email').setValue(record.data.email);
                                  getDisplayController.lookupReference('profile-phoneno').setValue(record.data.phoneno);

                                  getDisplayController.lookupReference('profile-address').setValue(record.data.address);
                                  getDisplayController.lookupReference('profile-status').setValue(record.data.statusname);
                                  
                                  getDisplayController.lookupReference('profile-goldbalance').setValue((record.data.goldbalance ? record.data.goldbalance : 0)+ 'g');
                                  getDisplayController.lookupReference('profile-avgbuyprice').setValue('RM' + (record.data.avgbuyprice ? record.data.avgbuyprice : 0)+ '/g');
                                  getDisplayController.lookupReference('profile-totalcostgoldbalance').setValue('RM' + (record.data.totalcostgoldbalance ? record.data.totalcostgoldbalance : 0));
                                  getDisplayController.lookupReference('profile-diffcurrentpriceprcetage').setValue((record.data.diffcurrentpriceprcetage ? record.data.diffcurrentpriceprcetage : 0) + '%');
                                  getDisplayController.lookupReference('profile-currentgoldvalue').setValue('RM' + (record.data.currentgoldvalue ? record.data.currentgoldvalue : 0));

                                  // Additional fields
                                  getDisplayController.lookupReference('profile-accountnumber').setValue(record.data.accountnumber);
                                  getDisplayController.lookupReference('profile-partnercusid').setValue(record.data.partnercusid);
                                  getDisplayController.lookupReference('profile-partnername').setValue(record.data.partnername);
                                  getDisplayController.lookupReference('profile-accounttype').setValue(record.data.accounttypestr);
                                  getDisplayController.lookupReference('profile-accountbalance').setValue(record.data.accountbalance);

                                  Ext.Msg.show({
                                      title: 'Successful',
                                      message: 'Customer Account is selected.',
                                      buttons: Ext.Msg.YES,
                                  });
                          
                                }
                           }
                      }
                    },
                ],
            },
            // End Search
            {
              xtype: 'panel',
              title: 'Profile Details',
              layout: 'hbox',
              collapsible: true,
              cls: 'otcpanel',
              defaults: {
                layout: 'vbox',
                flex: 1,
                bodyPadding: 10
              },
              margin: "10 0 0 0",
              reference: 'profiledetails',
              items: [
                {
                  defaultType: 'displayfield',
                  defaults: {
                    labelStyle: 'font-weight:bold',
                  },
                  
                  style:'margin-left: 5px',
                  items: [
                    {
                      fieldLabel: 'Full Name',
                      name: 'fullname',
                      reference: "profile-fullname"
                    },                
                    {
                      fieldLabel: 'Occupation Category',
                      name: 'occupationcategory',
                      reference: "profile-occupationcategory"
                    },       
                    {
                      fieldLabel: 'My Kad / Passport No',
                      name: 'mykadno',
                      // value: theData.information.mykadno.slice(0, 6) + '-' + theData.information.mykadno.slice(6, 8) + '-' + theData.information.mykadno.slice(-4)
                      reference: "profile-mykadno"
                    },
                    {
                      fieldLabel: 'Email',
                      name: 'email',
                      reference: "profile-email"
                    },
                    {
                      fieldLabel: 'Phone Number',
                      name: 'phoneno',
                      reference: "profile-phoneno"
                    },
                    {
                  
                      fieldLabel: 'Address',
                      name: 'address',
                      reference: "profile-address",
                      // value: "Tower 1 @ PFCC, Jalan Puteri 1/2, Bandar Puteri, 47100 Puchong, Selangor",
                      width : '80%'
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
                      fieldLabel: 'Customer ID',
                      name: 'partnercusid',
                      reference: "profile-partnercusid"
                    },                
                    {
                      fieldLabel: 'GIRO/ GIRO i Account No',
                      name: 'accountnumber',
                      reference: "profile-accountnumber"
                    },       
                    {
                      fieldLabel: 'Account Type',
                      name: 'accounttype',
                      reference: "profile-accounttype"
                    },
                    {
                      fieldLabel: 'Branch Registered',
                      name: 'partnername',
                      // value: theData.information.mykadno.slice(0, 6) + '-' + theData.information.mykadno.slice(6, 8) + '-' + theData.information.mykadno.slice(-4)
                      reference: "profile-partnername"
                    },
                    {
                      fieldLabel: 'Status',
                      name: 'status',
                      reference: "profile-status"
                    },
                    {
                      fieldLabel: 'Bank Account Balance',
                      name: 'accountbalance',
                      reference: "profile-accountbalance"
                    },
                  ]
                },
              ]
    
            },
            {
                xtype: 'panel',
                title: 'Print Yearly Statement',
                layout: 'vbox',
                collapsible: true,
                cls: 'otcpanel',
                // hidden:true,
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                reference: 'printyearlystatement',
                items: [{
                  xtype: 'container',
                  layout: 'hbox',
                  width: '100%',
                  items: [{
                    margin: "20",
                      xtype: 'combobox',
                      fieldLabel: 'Select Year',
                      reference: 'selectedYearField',
                      itemId: 'selectedYearField',
                      store: Ext.create('Ext.data.Store', {
                          fields: ['year'],
                          data: (function () {
                              var years = [];
                              for (var i = new Date().getFullYear(); i >= 2022; i--) {
                                  years.push({ 'year': i });
                              }
                              return years;
                          })()
                      }),
                      displayField: 'year',
                      valueField: 'year',
                      queryMode: 'local',
                      editable: false,
                      forceSelection: true,
                      name: 'selectedyear',
                      labelWidth: 'auto'
                  }, {
                      xtype: 'button',
                      text: 'Print',
                      margin: '20 5 5 5',
                      handler: 'printYearlyStatement'
                  }]
              }]
            }, 
        ]
    },

});
