var limitItems=Ext.create('Ext.panel.Panel',{
    items:[
        // {
        //     style: {
        //         'border': '2px solid #204A6D',
        //         'padding-left':'5px'
        //     },
        //     height: 60,
        //     margin: '0 0 10 0',
        //     items: [{
        //         xtype: 'container',
        //         scrollable: false,
        //         layout: 'hbox',
        //         defaults: {
        //             bodyPadding: '5',
        //         },
        //         items: [{
        //             html: '<h1>Daily Limit</h1>',                        
        //         }]
        //     },]
        // },
        {
            title: 'Daily Limit',
            iconCls: 'x-fa fa-calendar',
            xtype: 'formpanel',
            id: 'dailylimitformdisplay',
            reference: 'userdailylimit',
            store: { type: 'Partner' },
            viewModel: {
                //type: 'partner-partner'
            },
            // header: {
            //     style: 'background-color: #204A6D;border-color: #204A6D;',
            // },
            scrollable: true,
            // border:true,
            style: "font-family:'Open Sans', 'Helvetica Neue', helvetica, arial, verdana, sans-serif;border: 1px solid #5fa2dd",
            userCls: 'dailylimit-box',
            items: [


            ],

        },
    ]
});
Ext.define('snap.view.limits.DailyLimit', {
    extend: 'Ext.panel.Panel',
    xtype: 'dailylimitview',
    requires: [
    ],
    permissionRoot: '/root/gtp/limits',
    viewModel: {
        data: {
            name: "Spot Order Special",
            dailylimit: [],
        }
    },
    // title: 'Daily Limit',
    // userCls: 'transactionlisting-head',
    initialize: function (formView, form, record, asyncLoadCallback) {        
        elmnt = this;
        vma = this.getViewModel();

        async function getList() {            
            const item_list = await snap.getApplication().sendRequest({
                hdl: 'orderdashboard', 'action': 'initDailyLimit',
                id: 1,
            }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {    
                        if (data.products) {
                            var panel = Ext.getCmp('dailylimitformdisplay');

                            //date = data.createdon.date;
                            //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                            panel.removeAll();
                            data.products.map((x) => {

                                panel.add(this.limitTemplate(x))
                            })
                        } else {
                            var panel = Ext.getCmp('dailylimitformdisplay');

                            //date = data.createdon.date;
                            //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                            panel.removeAll();
                            panel.add(this.defaultTemplate());
                        }



                        vma.set('dailylimit', data.dailylimit);

                    }
                });
            return true
        }
        getList().then(
            function (data) {
                //elmnt.loadFormSeq(data.return)
            }
        )
        this.callParent(arguments);
    },
    formDialogWidth: 950,
    permissionRoot: '/root/trading/order',
    layout: 'fit',
    width: '100%',
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
        width: '100%',
        height: '100%',
        //cls: Ext.baseCSSPrefix + 'shadow',

        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        scrollable: true,
        bodyPadding: 10,

        defaults: {
            frame: true,
            bodyPadding: 10
        },

        items: [
            limitItems
        ]
    }


});


// Default blank template
defaultTemplate = () => {
    var returnx = {
        xtype: 'displayfield',
        width: '99%',
        padding: '0 1 0 1',
        value: "<h5 style=' width:100%;text-align:center; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>No Products Has Been Mapped With the Partner, Please Contact GTP Admin</span></h5>",
        //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
        renderer: function (html) {
            this.setHtml(html)
        }
    }

    return returnx
}

// Admin Message template
adminTemplate = () => {
    var returnx = {
        xtype: 'displayfield',
        width: '99%',
        padding: '0 1 0 1',
        value: "<h5 style=' width:100%;text-align:center; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 20px;position: relative;top: 10px;'>Section For Customer Reference</span></h5>",
        renderer: function (html) {
            this.setHtml(html)
        }
    }
    return returnx
}
limitTemplate = (data) => {
    var returnx = {
        xtype: 'container',
        scrollable: true,
        items: [{
            itemId: 'user_main_fieldset',
            xtype: 'fieldset',
            title: '<span style="font-weight:bold">'+data.name+'</span>',
            layout: 'vbox',
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0',
                width: '100%',
            },
            userCls: 'dailylimit-container',
            items: [
                {
                    xtype:'container',
                    layout:'hbox',
                    items:[
                        {
                            xtype:'label',html:'Buy limit (g)',style:{width:'55%',padding:'7px 0px 0px 0px','font-size':'1.1em'}
                        },
                        {
                            xtype: 'displayfield', name: 'limitbuy', value: parseFloat(data.dailybuylimitxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'limitbuy', fieldLabel: '',  style: 'padding-left: 5%;padding-right: 5%;', fieldStyle: " background-color: #ffffff ",  userCls: 'dailylimit-amt',
                        },                        
                    ]
                },
                {
                    xtype: 'label',
                    width: '99%',
                    // margin : '10 1 -20',
                    padding: '0 1 0 1',
                    // html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>OR</span></h5>",
                    html: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                    userCls: 'line-margin',
                },
                {
                    xtype:'container',
                    layout:'hbox',
                    items:[                        
                        {
                            xtype:'label',html:'Sell Limit (g)',style:{width:'55%',padding:'7px 0px 0px 0px','font-size':'1.1em'}
                        },
                        {
                            xtype: 'displayfield', name: 'limitsell', value: parseFloat(data.dailyselllimitxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'limitsell', fieldLabel: '', style: 'padding-left: 5%;padding-right: 5%;', fieldStyle: " background-color: #fff ", userCls: 'dailylimit-amt',
                        },
                    ]
                },
                {
                    xtype: 'label',
                    width: '99%',
                    // margin : '10 1 -20',
                    padding: '0 1 0 1',
                    // html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>OR</span></h5>",
                    html: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                    userCls: 'line-margin',
                },
                {
                    xtype:'container',
                    layout:'hbox',
                    items:[                        
                        {
                            xtype:'label',html:'Buy Balance (g)',style:{width:'55%',padding:'7px 0px 0px 0px','font-size':'1.1em'}
                        },
                        {
                            xtype: 'displayfield', name: 'balancebuy', value: parseFloat(data.buybalance).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'balancebuy', fieldLabel: '', style: 'padding-left: 5%;padding-right: 5%;', fieldStyle: " background-color: #ffffff ", userCls: 'dailylimit-amt',
                        },
                    ]
                },
                {
                    xtype: 'label',
                    width: '99%',
                    // margin : '10 1 -20',
                    padding: '0 1 0 1',
                    // html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>OR</span></h5>",
                    html: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                    userCls: 'line-margin',
                },
                {
                    xtype:'container',
                    layout:'hbox',
                    items:[                        
                        {
                            xtype:'label',html:'Sell Balance (g)',style:{width:'55%',padding:'7px 0px 0px 0px','font-size':'1.1em'}
                        },
                        {
                            xtype: 'displayfield', name: 'balancesell', value: parseFloat(data.sellbalance).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'balancesell', fieldLabel: '', style: 'padding-left: 5%;padding-right: 5%;', fieldStyle: " background-color: #fff ", userCls: 'dailylimit-amt',
                        },
                    ]
                },
                {
                    xtype: 'label',
                    width: '99%',
                    // margin : '10 1 -20',
                    padding: '0 1 0 1',
                    // html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>OR</span></h5>",
                    html: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                    userCls: 'line-margin',
                },
                {
                    xtype:'container',
                    layout:'hbox',
                    items:[                        
                        {
                            xtype:'label',html:'Per Transaction Min Buy (g)',style:{width:'55%',padding:'7px 0px 0px 0px','font-size':'1.1em'}
                        },
                        {
                            xtype: 'displayfield', name: 'pertransactionminbuy', value: parseFloat(data.buyclickminxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'pertransactionminbuy', fieldLabel: '', style: 'padding-left: 5%;padding-right: 5%;', fieldStyle: " background-color: #ffffff ", userCls: 'dailylimit-amt',
                        },
                    ]
                },
                {
                    xtype: 'label',
                    width: '99%',
                    // margin : '10 1 -20',
                    padding: '0 1 0 1',
                    // html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>OR</span></h5>",
                    html: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                    userCls: 'line-margin',
                },
                {
                    xtype:'container',
                    layout:'hbox',
                    items:[                        
                        {
                            xtype:'label',html:'Per Transaction Min Sell (g)',style:{width:'55%',padding:'7px 0px 0px 0px','font-size':'1.1em'}
                        },
                        {
                            xtype: 'displayfield', name: 'pertransactionminsell', value: parseFloat(data.sellclickminxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'pertransactionminsell', fieldLabel: '', style: 'padding-left: 5%;padding-right: 5%;', fieldStyle: " background-color: #fff ", userCls: 'dailylimit-amt',
                        },
                    ]
                },
                {
                    xtype: 'label',
                    width: '99%',
                    // margin : '10 1 -20',
                    padding: '0 1 0 1',
                    // html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>OR</span></h5>",
                    html: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                    userCls: 'line-margin',
                },
                {
                    xtype:'container',
                    layout:'hbox',
                    items:[                        
                        {
                            xtype:'label',html:'Per Transaction Max Buy (g)',style:{width:'55%',padding:'7px 0px 0px 0px','font-size':'1.1em'}
                        },
                        {
                            xtype: 'displayfield', name: 'pertransactionmaxbuy', value: parseFloat(data.buyclickmaxxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'pertransactionmaxbuy', fieldLabel: '', style: 'padding-left: 5%;padding-right: 5%;', fieldStyle: " background-color: #ffffff ", userCls: 'dailylimit-amt',
                        },
                    ]
                },
                {
                    xtype: 'label',
                    width: '99%',
                    // margin : '10 1 -20',
                    padding: '0 1 0 1',
                    // html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;padding:0 20px 0px 0px;position: relative;top: 10px;'>OR</span></h5>",
                    html: "<h5 style=' width:100%;text-align:center; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
                    userCls: 'line-margin',
                },
                {
                    xtype:'container',
                    layout:'hbox',
                    items:[                        
                        {
                            xtype:'label',html:'Per Transaction Max Sell (g)',style:{width:'55%',padding:'7px 0px 0px 0px','font-size':'1.1em'}
                        },
                        {
                            xtype: 'displayfield', name: 'pertransactionmaxsell', value: parseFloat(data.sellclickmaxxau).toLocaleString('en', { minimumFractionDigits: 3 }), reference: 'limitbuy', fieldLabel: '',  style: 'padding-left: 5%;padding-right: 5%;', fieldStyle: " background-color: #ffffff ",  userCls: 'dailylimit-amt',
                        },  
                    ]
                },               
            ]
        },],
    }
    return returnx
}

