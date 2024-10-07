Ext.define('snap.view.orderdashboard.DailyLimit',{
    extend: 'Ext.panel.Panel',
    xtype: 'dailylimitview',

    requires: [

        'Ext.layout.container.Fit'


    ],
    permissionRoot: '/root/gtp/limits',
    viewModel: {
      data: {
          name: "Spot Order Special",
          dailylimit: [],

      }
  },
  
    initComponent: function(formView, form, record, asyncLoadCallback){
      elmnt = this;
      vma = this.getViewModel();
      
      async function getList(){
          const item_list = await snap.getApplication().sendRequest({
              hdl: 'orderdashboard', 'action': 'initDailyLimit',
              id: 1,
          }, 'Fetching data from server....').then(
          function(data) {
              if (data.success) {
                  //alert(data.fees);else {
              

                    if(data.products){
                      var panel = Ext.getCmp('dailylimitformdisplay');
    
                        //date = data.createdon.date;
                        //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                        panel.removeAll();
                        data.products.map((x) => {
    
                          panel.add(this.limitTemplate(x))
                        })
                    }else {
                      var panel = Ext.getCmp('dailylimitformdisplay');
    
                        //date = data.createdon.date;
                        //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                        panel.removeAll();
                        panel.add(this.defaultTemplate());
                    }
                /* ****************************************** Old **********************************************************
                //Check if admin
                if(data.usertype == 'Operator' || data.usertype == 'Sale'){
                    var panel = Ext.getCmp('dailylimitformdisplay');
    
                    //date = data.createdon.date;
                    //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                    panel.removeAll();
                    panel.add(this.adminTemplate());
                }else {
                  if(data.products){
                    var panel = Ext.getCmp('dailylimitformdisplay');
  
                      //date = data.createdon.date;
                      //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                      panel.removeAll();
                      data.products.map((x) => {
  
                        panel.add(this.limitTemplate(x))
                      })
                  }else {
                    var panel = Ext.getCmp('dailylimitformdisplay');
  
                      //date = data.createdon.date;
                      //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                      panel.removeAll();
                      panel.add(this.defaultTemplate());
                  }
                }****************************************** End Old **********************************************************
                
                  

                  vma.set('dailylimit', data.dailylimit);
                  /*
                  Ext.getCmp('dailylimithomebuy').setValue(parseFloat(vma.get('dailylimit')[0].dailybuylimitxau).toFixed(3));
                  
                  Ext.getCmp('dailylimithomesell').setValue(parseFloat(vma.get('dailylimit')[0].dailyselllimitxau).toFixed(3));

                  Ext.getCmp('balancehomebuy').setValue(vma.get('dailylimit')[0].dailybuylimitxau);
                  Ext.getCmp('balancehomesell').setValue(vma.get('dailylimit')[0].dailyselllimitxau);

                  Ext.getCmp('pertransactionminhomebuy').setValue(vma.get('dailylimit')[0].dailybuylimitxau);
                  Ext.getCmp('pertransactionminhomesell').setValue(vma.get('dailylimit')[0].dailyselllimitxau);

                  Ext.getCmp('pertransactionmaxhomebuy').setValue(vma.get('dailylimit')[0].dailybuylimitxau);
                  Ext.getCmp('pertransactionmaxhomesell').setValue(vma.get('dailylimit')[0].dailyselllimitxau);
                  */
                  // Set product permissions 
                  //vm.set('permissions', data.permissions);
                  // Set PartnerService permissions
                  //vm.set('fees', data.fees);

                  //Ext.getCmp('productspot').getStore().loadData(data.items);
                  //Ext.getCmp('productfuture').getStore().loadData(data.items);

                  //Ext.getCmp('userrefineryfee').getStore().loadData(data.items);

                  //Bid Price
                  //Ext.getCmp('bidpricedashboard').getStore().loadData(data.items);
                  //Ext.getCmp('askpricedashboard').getStore().loadData(data.items);
                  
                  //alert(data.items);
                  //console.log('data_success')
                  //return data
                  // Add tempplate
                  
                    //return;
              }
          });
          return true
      }
      getList().then(
          function(data){
              //elmnt.loadFormSeq(data.return)
          }
      )
      this.callParent(arguments);
  },
    formDialogWidth: 950,
    permissionRoot: '/root/trading/order',
    layout: 'fit',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    bodyPadding: 25,


    items: {
        profiles: {
            classic: {
                panel1Flex: 1,
                panelHeight: 100,
                panel2Flex: 2
            },
            neptune: {
                panel1Flex: 1,
                panelHeight: 100,
                panel2Flex: 2
            },
            graphite: {
                panel1Flex: 2,
                panelHeight: 110,
                panel2Flex: 3
            },
            'classic-material': {
                panel1Flex: 2,
                panelHeight: 110,
                panel2Flex: 3
            }
        },
        width: 500,
        height: 400,
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
            bodyPadding: 10
        },
        
        items: [/*
            {
                frame: false,
                margin: '0 0 10 0',
                id: 'titledailylimit',
                html:  '<h2>Daily Limit</h2>',
            },*/
            /*{
                 // Style for migasit default
                 style: {
                    borderColor: '#204A6D',
                },
                
                height: 120,
                margin: '0 0 10 0',
                items: [{
                    xtype: 'container',
                    scrollable: false,
                    layout: 'hbox',
                    defaults: {
                        bodyPadding: '5',
                        // border: true
                    },
                    items: [{
                      html: '<h1>Daily Limit</h1>',
                      flex: 10,
                      //xtype: 'orderview',
                     //reference: 'spotorder',
                    },{
                      // spacing in between
                      flex: 1,
                    },{
                      
                        layout: {
                            type: 'hbox',
                            pack: 'start',
                            align: 'stretch'
                        },
                        flex: 6,
                    
                        //bodyPadding: 10,
                    
                        defaults: {
                            frame: false,
                        },

                    }]
    
                // id: 'medicalrecord',
                },]
            },*/
            {
                title: 'Daily Limit',
                iconCls: 'x-fa fa-calendar',
                xtype: 'form',
                id: 'dailylimitformdisplay',
                reference: 'userdailylimit',
                store: { type: 'Partner' },
                viewModel: {
                    type: 'partner-partner'
                },
                header: {
                    // Custom style for Migasit
                    /*style: {
                        backgroundColor: '#204A6D',
                    },*/
                    style : 'background-color: #204A6D;border-color: #204A6D;',
                },
                scrollable: true,
                items: [
                    /*
                        {
                            itemId: 'user_main_fieldset',
                            xtype: 'fieldset',
                            title: 'Main Information',
                            title: 'Daily Limit',
                            layout: 'hbox',
                            defaultType: 'textfield',
                            fieldDefaults: {
                                anchor: '100%',
                                msgTarget: 'side',
                                margin: '0 0 5 0',
                                width: '100%',
                            },
                            items: [
                                    {
                                      xtype: 'fieldcontainer',
                                      fieldLabel: 'Limits',
                                      defaultType: 'textboxfield',
                                      layout: 'hbox',
                                      flex: 4,
                                      items: [
                                                {
                                                  xtype: 'fieldcontainer',
                                                  layout: 'vbox',
                                                  flex: 2,
                                                  items: [
                                                    {
                                                      xtype: 'displayfield', id: 'dailylimithomebuy', name:'limitbuy', reference: 'limitbuy', fieldLabel: 'Buy limit (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                                    },
                                                    {
                                                      xtype: 'displayfield', id: 'dailylimithomesell', name:'limitsell', reference: 'limitsell', fieldLabel: 'Sell Limit (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                                    },
                                                  ]
                                                },
                                              
                                            ]
                                    },
                                    {
                                        xtype: 'panel',
                                        flex: 1,
                                        
                                    },
                                    {
                                        xtype: 'fieldcontainer',
                                        fieldLabel: 'Balance',
                                        defaultType: 'textboxfield',
                                        layout: 'hbox',
                                        flex: 4,
                                        items: [

                                                  // ALL CHECKBOX INPUT -- jsonConversion => to 'data[key] = value'
                                                  /*
                                                  {
                                                    xtype: 'displayfield', name:'vtweight', reference: 'vtweight', fieldLabel: 'Weight (kg)', name: 'weight', flex: 1, //style:'padding-left: 20px;'
                                                  },
                                                  {
                                                    xtype: 'displayfield', name:'vtheight', reference: 'vtheight', fieldLabel: 'Height (cm)', name: 'height', flex: 1, //style:'padding-left: 20px;'
                                                  },
                                                  {
                                                    xtype: 'displayfield', name:'vtbmi', reference: 'vtbmi', fieldLabel: 'BMI', name: 'bmi', flex: 1, style:'padding-left: 20px;',
                                                  },*/ /*
                                                  {
                                                    xtype: 'fieldcontainer',
                                                    layout: 'vbox',
                                                    flex: 2,
                                                    items: [
                                                      {
                                                        xtype: 'displayfield', id: 'balancehomebuy', name:'balancebuy', reference: 'balancebuy', fieldLabel: 'Buy Balance (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                                      },
                                                      {
                                                        xtype: 'displayfield', id: 'balancehomesell', name:'balancesell', reference: 'balancesell', fieldLabel: 'Sell Balance (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                                      },
                                                    ]
                                                  },
                                                
                                              ]
                                    },
                                    {
                                        xtype: 'panel',
                                        flex: 1,
                                        
                                    },
                                  ]
                        },
                        {
                            itemId: 'user_main_fieldset2',
                            xtype: 'fieldset',
                            title: 'Per Transaction',
                            layout: 'anchor',
                            layout: 'hbox',
                            defaultType: 'textfield',
                            fieldDefaults: {
                                anchor: '100%',
                                msgTarget: 'side',
                                margin: '0 0 5 0',
                                width: '100%',
                            },
                            items: [
                                {
                                  xtype: 'fieldcontainer',
                                  fieldLabel: 'Per Transaction Minimum',
                                  defaultType: 'textboxfield',
                                  layout: 'hbox',
                                  flex: 4,
                                  items: [
                                            {
                                              xtype: 'fieldcontainer',
                                              layout: 'vbox',
                                              flex: 2,
                                              items: [
                                                {
                                                  xtype: 'displayfield',  id: 'pertransactionminhomebuy', name:'pertransactionminbuy', reference: 'pertransactionminbuy', fieldLabel: 'Buy (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                                },
                                                {
                                                  xtype: 'displayfield',  id: 'pertransactionminhomesell',  name:'pertransactionminsell', reference: 'pertransactionminsell', fieldLabel: 'Sell (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                                },
                                              ]
                                            },
                                          
                                        ]
                                },
                                {
                                    xtype: 'panel',
                                    flex: 1,
                                    
                                },
                                {
                                    xtype: 'fieldcontainer',
                                    fieldLabel: 'Per Transaction Maximum',
                                    defaultType: 'textboxfield',
                                    layout: 'hbox',
                                    flex: 4,
                                    items: [

                                              // ALL CHECKBOX INPUT -- jsonConversion => to 'data[key] = value'
                                              /*
                                              {
                                                xtype: 'displayfield', name:'vtweight', reference: 'vtweight', fieldLabel: 'Weight (kg)', name: 'weight', flex: 1, //style:'padding-left: 20px;'
                                              },
                                              {
                                                xtype: 'displayfield', name:'vtheight', reference: 'vtheight', fieldLabel: 'Height (cm)', name: 'height', flex: 1, //style:'padding-left: 20px;'
                                              },
                                              {
                                                xtype: 'displayfield', name:'vtbmi', reference: 'vtbmi', fieldLabel: 'BMI', name: 'bmi', flex: 1, style:'padding-left: 20px;',
                                              },*//*
                                              {
                                                xtype: 'fieldcontainer',
                                                layout: 'vbox',
                                                flex: 2,
                                                items: [
                                                  {
                                                    xtype: 'displayfield', id: 'pertransactionmaxhomebuy', name:'pertransactionmaxbuy', reference: 'pertransactionmaxbuy', fieldLabel: 'Buy (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                                                  },
                                                  {
                                                    xtype: 'displayfield', id: 'pertransactionmaxhomesell', name:'pertransactionmaxsell', reference: 'pertransactionmaxsell', fieldLabel: 'Sell (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                                                  },
                                                ]
                                              },
                                            
                                          ]
                                },
                                {
                                    xtype: 'panel',
                                    flex: 1,
                                    
                                },
                              ]
                        },*/
                        /*{
                            buttons: [{
                                text: 'Cancel',
                                handler: 'onFormReset'
                            }, {
                                text: 'Submit',
                                handler: 'onRecordRequest'
                            }, {
                                text: 'Something else',
                                width: 150,
                                handler: 'onCompleteClick'
                            }],
                        }*/
                    
                ],

            },
            
            
            
        ]
    }


});


// Default blank template
defaultTemplate = () =>
{
  var returnx = {
    xtype : 'displayfield',
    width : '99%',
    padding: '0 1 0 1',
    value: "<h5 style=' width:100%;text-align:center; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>No Products Has Been Mapped With the Partner, Please Contact GTP Admin</span></h5>",
    //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
    
  }

  return returnx
}

// Admin Message template
adminTemplate = () =>
{
  var returnx = {
    xtype : 'displayfield',
    width : '99%',
    padding: '0 1 0 1',
    value: "<h5 style=' width:100%;text-align:center; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>No Products Has Been Mapped With the Partner, Please Contact GTP Admin<</span></h5>",
    //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
    
  }

  return returnx
}



// Daily Limit Template

limitTemplate = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        // From partner perspective
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'limitbuy', value: parseFloat(data.dailybuylimitxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'limitbuy', fieldLabel: 'Buy Limit (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                      {
                        xtype: 'displayfield', name:'limitsell', value: parseFloat(data.dailyselllimitxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'limitsell', fieldLabel: 'Sell limit (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                     
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'balancebuy', value: parseFloat(data.buybalance).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'balancebuy', fieldLabel: 'Buy Balance (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                      {
                        xtype: 'displayfield', name:'balancesell', value: parseFloat(data.sellbalance).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'balancesell', fieldLabel: 'Sell Balance (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },  
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield',  name:'pertransactionminbuy', value: parseFloat(data.buyclickminxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'pertransactionminbuy', fieldLabel: 'Per Transaction Min Buy (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                      {
                        xtype: 'displayfield', name:'pertransactionminsell', value: parseFloat(data.sellclickminxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'pertransactionminsell', fieldLabel: 'Per Transaction Min Sell (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'pertransactionmaxbuy', value: parseFloat(data.buyclickmaxxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'pertransactionmaxbuy', fieldLabel: 'Per Transaction Max Buy (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                      {
                        xtype: 'displayfield', name:'pertransactionmaxsell', value: parseFloat(data.sellclickmaxxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'pertransactionmaxsell', fieldLabel: 'Per Transaction Max Sell (g)', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}

