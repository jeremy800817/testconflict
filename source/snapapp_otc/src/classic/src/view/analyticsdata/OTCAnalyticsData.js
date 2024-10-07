function formatNumber(number, decimalPlaces) {
    var parts = Math.abs(number).toFixed(decimalPlaces).split('.');
    var integerPart = parts[0];
    var decimalPart = parts[1] || '';
  
    var formattedInteger = '';
    for (var i = integerPart.length - 1, j = 1; i >= 0; i--, j++) {
      formattedInteger = integerPart.charAt(i) + formattedInteger;
      if (j % 3 === 0 && i !== 0) {
        formattedInteger = ',' + formattedInteger;
      }
    }
  
    var formattedNumber = formattedInteger + '.' + decimalPart;
  
    if (number < 0) {
      formattedNumber = '-' + formattedNumber.substring(1); // Remove the leading comma
    }
  
    return formattedNumber;
  }
  
Ext.define('snap.view.analyticsdata.OTCAnalyticsData', {
    extend:'Ext.panel.Panel',
    xtype: 'otcanalyticsdataview',
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/analytics',
    scrollable:true,
    controller: 'analyticsdata-analyticsdata',
    viewModel: {
        data: {
            totalAccountHolder: null,
            totalBuyGold: null,
            totalSellGold: null,
            vault: null,
            totalcustomerholding: null,
            balance: null,

            // marginbelow50k: null,
            // marginabove50k: null,
            // marginabove150k: null,
            // marginabove150ksell: null,

            tier1sellpercent: null,
            tier2sellpercent: null,
            tier3sellpercent: null,
            tier1buypercent: null,
            tier2buypercent: null,
            tier3buypercent: null,
            tier1sellamount: null,
            tier2sellamount: null,
            tier3sellamount: null,
            tier1buyamount: null,
            tier2buyamount: null,
            tier3buyamount: null,
        },
        formulas: {
          formattedTotalAccountHolder: function (get) {
            var totalAccountHolder = get('totalAccountHolder');
            return Ext.isNumber(totalAccountHolder) ? totalAccountHolder.toString() : '';
          },
          formattedtotalBuyGold: function (get) {
            var totalBuyGold = get('totalBuyGold');
            return Ext.isNumber(totalBuyGold) ? formatNumber(totalBuyGold, 2).toString() : '';
          },
          
          formattedtotalSellGold: function (get) {
            var totalSellGold = get('totalSellGold');
            return Ext.isNumber(totalSellGold) ? formatNumber(totalSellGold, 2) : '';
          },
          formattedvault: function (get) {
            var vault = get('vault');
            return Ext.isNumber(vault) ? formatNumber(vault, 3) : '';
          },
          formattedtotalcustomerholding: function (get) {
            var totalcustomerholding = get('totalcustomerholding');
            return Ext.isNumber(totalcustomerholding) ? formatNumber(totalcustomerholding, 3) : '';
          },
          formattedbalance: function (get) {
            var balance = get('balance');
            if(Ext.isNumber(balance)){
              return (balance < 0 ? '-' : '') + formatNumber(Math.abs(balance), 3);
            } else {
              return '';
            }
          }
        }
    },

    createPriceEvent: function(channelName, message){

        let elmnt = this;
        //format open gp price
        let formatDecimal = 3;
    
        //if (openGP.includes(channelName)) {
        message.data[0].companybuy = elmnt.formatPrice(message.data[0].companybuy, formatDecimal);
        message.data[0].companysell = elmnt.formatPrice(message.data[0].companysell, formatDecimal);
        //console.log(message);
        if (vm.get(channelName)) {
           
            Object.keys(message.data[0]).map(function(key, index) {
                let fields = [
                    'companybuy', 'companysell'
                ];
      
            if (fields.includes(key)) {
                if (vm.get(channelName)[key]) {
                    appendkey = key + 'display';
                    //message.data[0][appendkey] = elmnt.formatPrice(message.data[0][key], formatDecimal);
        
                    let originalPrice = parseFloat(message.data[0][key]);
                    
                     //price tier for RM0 - RM49,999.99
                     if (key === 'companysell') {
                        if(vm.get('tier1sellamount') == null && vm.get('tier1sellpercent') != null ){
                            // let decreasedPrice = originalPrice * (1 + (vm.get('tier1sellpercent') / 100));
                            // let decreasedPriceDisplay = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            // message.data[0][appendkey] = decreasedPriceDisplay;
                            message.data[0][appendkey] = elmnt.formatPrice(originalPrice, formatDecimal);
                        }else if(vm.get('tier1sellpercent') == null && vm.get('tier1sellamount') != null )
                        {
                            // let decreasedPrice = originalPrice + vm.get('tier1sellamount');
                            // let decreasedPriceDisplay = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            // message.data[0][appendkey] = decreasedPriceDisplay;
                            message.data[0][appendkey] = elmnt.formatPrice(originalPrice, formatDecimal);
                        }
                    }else if(key === 'companybuy')
                    {
                        if(vm.get('tier1buyamount') == null && vm.get('tier1buypercent') != null ){
                            // let decreasedPrice = originalPrice * (1 + (vm.get('tier1buypercent') / 100));
                            // let decreasedPriceDisplay = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            // message.data[0][appendkey] = decreasedPriceDisplay;
                            // message.data[0][appendkey] = decreasedPriceDisplay;
                            message.data[0][appendkey] = elmnt.formatPrice(originalPrice, formatDecimal);
                        }else if(vm.get('tier1buypercent') == null && vm.get('tier1buyamount') != null )
                        {
                            // let decreasedPrice = originalPrice + vm.get('tier1buyamount');
                            // let decreasedPriceDisplay = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            // message.data[0][appendkey] = decreasedPriceDisplay;
                            message.data[0][appendkey] = elmnt.formatPrice(originalPrice, formatDecimal);
                        }
                    }


                    //price tier for RM50,000 - RM149,999.99
                    if (key === 'companysell') {
                        if(vm.get('tier2sellamount') == null && vm.get('tier2sellpercent') != null ){
                            // let decreasedPrice = originalPrice * (1 + (vm.get('tier2sellpercent') / 100));
                            percent = 1 - (((vm.get('tier1sellpercent') - vm.get('tier2sellpercent')) / 100 ));
                            let decreasedPrice = originalPrice * percent;
                            let decreasedPriceDisplay1 = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            message.data[0][key + 'display1'] = decreasedPriceDisplay1;
                        }else if(vm.get('tier2sellpercent') == null && vm.get('tier2sellamount') != null ){
                            let decreasedPrice = originalPrice + vm.get('tier2sellamount');
                            let decreasedPriceDisplay1 = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            message.data[0][key + 'display1'] = decreasedPriceDisplay1;
                        }
                    }else if(key === 'companybuy')
                    {
                       // message.data[0][key + 'display1'] = originalPrice;
                        if(vm.get('tier2buyamount') == null && vm.get('tier2buypercent') != null ){
                            // percent = 1 - ((vm.get('tier2buypercent') / 100 ));
                            // let decreasedPrice = originalPrice * (1 + (vm.get('tier1buypercent') - (vm.get('tier2buypercent')) / 100));
                            // let decreasedPriceDisplay1 = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            // message.data[0][key + 'display1'] = decreasedPriceDisplay1;
                            message.data[0][key + 'display1'] =  elmnt.formatPrice(originalPrice, formatDecimal);
                        }else if(vm.get('tier2buypercent') == null && vm.get('tier2buyamount') != null){
                            let decreasedPrice = originalPrice + vm.get('tier2buyamount');
                            let decreasedPriceDisplay1 = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            message.data[0][key + 'display1'] = decreasedPriceDisplay1;
                        }
                    }

		
                    //price tier for Above RM150,000.00
                    if (key === 'companysell') {
                        if(vm.get('tier3sellamount') == null && vm.get('tier3sellpercent') != null){
                            // let decreasedPrice = originalPrice * (1 + (vm.get('tier3sellpercent') / 100));
                            percent = 1 - (((vm.get('tier1sellpercent') - vm.get('tier3sellpercent')) / 100 ));
                            let decreasedPrice = originalPrice * percent;
                            let decreasedPriceDisplay2 = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            message.data[0][key + 'display2'] = decreasedPriceDisplay2;
			
                        }else if(vm.get('tier3sellpercent') == null && vm.get('tier3sellamount') != null){
                            let decreasedPrice = originalPrice + vm.get('tier3sellamount');
                            let decreasedPriceDisplay2 = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            message.data[0][key + 'display2'] = decreasedPriceDisplay2;
                        }
			  
			  
                    }else if(key === 'companybuy')
                    {
                        if(vm.get('tier3buyamount') == null && vm.get('tier3buypercent') != null){
                            // let decreasedPrice = originalPrice * (1 + (vm.get('tier3buypercent') / 100));
                            percent = 1 - (((vm.get('tier2buypercent') - vm.get('tier3buypercent')) / 100 ));
                            let decreasedPrice = originalPrice * percent;
                            let decreasedPriceDisplay2 = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            message.data[0][key + 'display2'] = decreasedPriceDisplay2;
                        }else if(vm.get('tier3buypercent') == null && vm.get('tier3buyamount') != null){
                            let decreasedPrice = originalPrice + vm.get('tier3buyamount');
                            let decreasedPriceDisplay2 = elmnt.formatPrice(decreasedPrice, formatDecimal);
                            message.data[0][key + 'display2'] = decreasedPriceDisplay2;
                        }
			    
                    }
                }
            }
          });
        }
        vm.set(channelName, message.data[0]); 
        vm.set('getChannelName', channelName);
    },

    formatPrice: function(price, decimal){
        price = parseFloat(price);
        return price.toFixed(decimal);
    },

    // formatPriceColor: function(newPrice, oldPrice){
    //     newPrice = newPrice.toString();
    //     oldPrice = oldPrice.toString();

    //     let result = oldPrice.match(/\<span.*\>(.*)\<\/span\>/);

    //     if (result) {
    //         oldPrice = result[1];
    //     }
    //     // Green
    //     if (newPrice > oldPrice) {
    //         return '<span style="color:#1ac69c;">'+newPrice+' ↑</span>';
    //     }
    //     // Red
    //     if (newPrice < oldPrice) {
    //         return '<span style="color:#FF4848;">'+newPrice+' ↓</span>';
    //     }
    //     if (newPrice == oldPrice) {
    //         return newPrice;
    //     }
    // },

    initComponent: function(formView, form, record, asyncLoadCallback){
        elmnt = this;
        vm = this.getViewModel();

        // var totalAccountHolder = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() +'/analytics/totalaccholder');
        // var totalbuygold = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() +'/analytics/totalbuygold');
        // var totalsellgold = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() +'/analytics/totalsellgold');
        // var totalvault = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() +'/analytics/vault');
        // var totalcustomerholding = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() +'/analytics/totalcustomerholding');
        // var vaultbalance = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() +'/analytics/balance');

        async function getList(){
            return true
        }
        getList().then(
            function(data){
                //elmnt.loadFormSeq(data.return)
            }
        )
        // const source = new EventSource('https://otc-uat.ace2u.com:8443/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.BSN');
        const source = new EventSource('https://' + window.location.hostname + '/index.php?hdl=pssubscribe&action=subscribesales&pdt=DG-999-9&onecent=1&code=INTLX.BSN');
        source.onmessage = function(event) {
            jsonString  = event.data;
            jsonObj = JSON.parse(jsonString);
            elmnt.createPriceEvent(PROJECTBASE + "_CHANNEL", jsonObj);
        };

        this.getController().fetchValueDashboardData();
        this.callParent(arguments);
    },
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
        items: [,
            {
                xtype: 'panel',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                margin: '10 0 0 0',
                height: 230,
                items: [{
                        xtype: 'container',
                        flex: 3,
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        items: [{
                                xtype: 'displayfield',
                                width: '100%',
                                fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                                value: '<b>Price Type</b>',
                                fieldStyle: 'text-align: left; margin-top:10px;margin-left:20px;',
                            },
                            {
                                xtype: 'displayfield',
                                width: '100%',
                                fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                value: 'RM10.00 - RM49,999.99',
                                fieldStyle: 'text-align: left; margin-top:5px;margin-left:20px;font-size:20px;line-height: 40px;',
                            },
                            {
                                xtype: 'displayfield',
                                width: '100%',
                                fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                value: 'RM50,000.00 - RM149,999.99',
                                fieldStyle: 'text-align: left; margin-top:0px;margin-left:20px;font-size:20px;line-height: 40px;',
                            },
                            {
                                xtype: 'displayfield',
                                width: '100%',
                                fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                value: 'Above RM150,000.00',
                                fieldStyle: 'text-align: left; margin-top:0px;margin-left:20px;font-size:20px;line-height: 40px;',
                            }
                        ]
                    },
                    {
                        xtype: 'component',
                        flex: 1,
                    },
                    {
                        xtype: 'container',
                        flex: 1,
                        width: '30%',
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        items: [{
                            xtype: 'displayfield',
                            width: '40%',
                            fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                            value: '<b>Bank Buy</b>',
                            fieldStyle: 'text-align: center; margin-top:10px;margin-right:20px;',
                        },
                        {
                            xtype: 'displayfield',
                            width: '40%',
                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                            fieldStyle: 'text-align: center; margin-top:0px;margin-right:20px;font-size:20px;line-height: 40px;border: 2px solid #FF0000;color: #FF0000;border-radius: 5px;',
                            value: '0.00',
                            bind: {
                                value: '{' + PROJECTBASE + '_CHANNEL.companybuydisplay}', // original price
                            },
                        },
                        {
                            xtype: 'displayfield',
                            width: '40%',
                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                            fieldStyle: 'text-align: center; margin-top:0px;margin-right:20px;font-size:20px;line-height: 40px;border: 2px solid #00FF00;color: #00FF00;border-radius: 5px;',
                            value: '0.00',
                            bind: {
                                value: '{' + PROJECTBASE + '_CHANNEL.companybuydisplay1}', // reduce 0.5%
                            },
                        },
                        {
                            xtype: 'displayfield',
                            width: '40%',
                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                            fieldStyle: 'text-align: center; margin-top:0px;margin-right:20px;font-size:20px;line-height: 40px;border: 2px solid #808080;color: #808080;border-radius: 5px;',
                            value: '0.00',
                            bind: {
                                value: '{' + PROJECTBASE + '_CHANNEL.companybuydisplay2}', // reduce 0.75%
                            },
                        }],                        
                    },
                    {
                        xtype: 'container',
                        flex: 1,
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        items: [{
                            xtype: 'displayfield',
                            width: '100%',
                            fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                            value: '<b>Bank Sell</b>',
                            fieldStyle: 'text-align: center; margin-top:10px;margin-right:20px;margin-left:5px;',
                        },
                        {
                            xtype: 'displayfield',
                            width: '100%',
                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                            fieldStyle: 'text-align: center; margin-top:0px;margin-right:20px;font-size:20px;line-height: 40px;border: 2px solid #FF0000;color: #FF0000;border-radius: 5px;',
                            value: '0.00',
                            bind: {
                                value: '{' + PROJECTBASE + '_CHANNEL.companyselldisplay}', // original price
                            },
                        },
                        {
                            xtype: 'displayfield',
                            width: '100%',
                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                            fieldStyle: 'text-align: center; margin-top:0px;margin-right:20px;font-size:20px;line-height: 40px;border: 2px solid #00FF00;color: #00FF00;border-radius: 5px;',
                            value: '0.00',
                            bind: {
                                value: '{' + PROJECTBASE + '_CHANNEL.companyselldisplay1}', // reduce 0.5%
                            },
                        },
                        {
                            xtype: 'displayfield',
                            width: '100%',
                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                            fieldStyle: 'text-align: center; margin-top:0px;margin-right:20px;font-size:20px;line-height: 40px;border: 2px solid #808080;color: #808080;border-radius: 5px;',
                            value: '0.00',
                            bind: {
                                value: '{' + PROJECTBASE + '_CHANNEL.companyselldisplay2}', // reduce 0.75%
                            },
                        }],                        
                    },
                ]
            },
            {
                xtype: "toolbar",
                height: 30,
                minHeight: 75,
                maxHeight: 800,
                margin: "10 0 0 0",
                cls: 'otc-main-center search_bar',
                items: [
                    
                    {
                        xtype: 'combobox',
                        fieldLabel: 'Branch',
                        labelWidth: 50,
                        reference: 'statelist',
                        allowBlank: true,
                        editable: true,
                        store: {
                            autoLoad: true,
                            type: 'States',
                            sorters: 'name'
                        },
                        displayField: 'name',
                        valueField: 'code',
                        typeAhead: true,
                        queryMode: 'local',
                        forceSelection: true,
                        listeners: {
                            expand: function(combo){
                                combo.store.load({
                                    start: 0,
                                    limit: 1500
                                })
                            }
                        },
                    },
                    {
                        xtype: 'checkbox',
                        reference: 'allStateCheckbox',
                        boxLabel: 'All States',
                        inputValue: true,
                        uncheckedValue: false,
                        handler: function(checkbox, checked) {
                            var branchComboBox = Ext.ComponentQuery.query('combobox[reference=statelist]')[0];
                            branchComboBox.setDisabled(checked);
                            if (checked) {
                                branchComboBox.setValue(null);
                            }
                        }
                    },
                    {
                        iconCls: 'x-fa fa-redo-alt',
                        xtype: "button",
                        text: "Generate",
                        handler: "generateBranchreport",
                    },
                ],
            },
            {
                xtype: 'container',
                scrollable: false,
                layout: {
                    type: 'hbox',
                    align: 'stretch',
                    width: '50%'
                },
                defaults: {
                    bodyPadding: '20',
                },
                style: {
                    borderColor: '#red',
                },
                margin: '10 0 0 0',
                height: 120,
                autoheight: true,
                items: [{
                        xtype: 'panel',
                        reference: 'accountpanel',
                        cls: 'otc-main-center',
                        header: false,
                        flex: 13,
                        padding: '0 0 0 5',
                        margin: '0 10 0 0',
                        border: false,
                        listeners: {
                            afterrender: function(form) {
                                var hastotalacchodlerpermission = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() + '/dashboard/totalaccholder');
                                settings = !hastotalacchodlerpermission; // reverse variable
                                settings = false;
                                // Update the hidden property based on the variable
                                form.setHidden(settings);
                            }
                        },
                        items: [{
                            layout: 'hbox',
                            width: '100%',
                            height: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [{
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [{
                                            xtype: 'displayfield',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                                            value: ' Total Account Holder  <i class="fa fa-user"></i>',
                                            fieldStyle: 'text-align: center;',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                            fieldStyle: 'text-align: center; margin-top:-20px;',
                                            value: '-',
                                            bind: {
                                                value: '{formattedTotalAccountHolder}'
                                            }
                                        },
                                    ],

                                },

                            ]
                        }, ],


                    },
                    {
                        xtype: 'panel',
                        reference: 'buypanel',
                        cls: 'otc-main-center',
                        header: false,
                        flex: 13,
                        padding: '0 0 0 5',
                        margin: '0 10 0 10',
                        border: false,
                        listeners: {
                            afterrender: function(form) {
                                var hastotalbuygoldpermission = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() + '/dashboard/totalbuygold');
                                settings = !hastotalbuygoldpermission; // reverse variable
                                settings = false;
                                // Update the hidden property based on the variable
                                form.setHidden(settings);
                            }
                        },
                        items: [{
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [{
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [{
                                            xtype: 'displayfield',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                                            value: 'Total Customer Sell <i class="fas fa-coins"></i>',
                                            fieldStyle: 'text-align: center;',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                            fieldStyle: 'text-align: center; margin-top:-20px;',
                                            value: '-',
                                            bind: {
                                                value: 'RM {formattedtotalBuyGold}'
                                            }
                                        },
                                    ],
                                },

                            ]
                        }, ],
                    },
                    {
                        xtype: 'panel',
                        reference: 'sellpanel',
                        cls: 'otc-main-center',
                        header: false,
                        flex: 13,
                        padding: '0 0 0 5',
                        margin: '0 0 0 10',
                        border: false,
                        listeners: {
                            afterrender: function(form) {
                                var hastotalsellgoldpermission = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() + '/dashboard/totalsellgold');
                                settings = !hastotalsellgoldpermission; // reverse variable
                                settings = false;
                                // Update the hidden property based on the variable
                                form.setHidden(settings);
                            }
                        },
                        items: [{
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [{
                                layout: 'vbox',
                                width: '100%',
                                style: {
                                    'margin': '5px 5px 0px 0px',
                                },
                                items: [{
                                        xtype: 'displayfield',
                                        width: '100%',
                                        fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                                        value: 'Total Customer Buy <i class="fas fa-coins"></i>',
                                        fieldStyle: 'text-align: center;',
                                    },
                                    {
                                        xtype: 'displayfield',

                                        width: '100%',
                                        fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                        fieldStyle: 'text-align: center; margin-top:-20px;',
                                        value: '-',
                                        bind: {
                                            value: 'RM {formattedtotalSellGold}'
                                        }
                                    },
                                ],
                            }, ]
                        }, ],


                    },
                ]
            },
            {
                xtype: 'container',
                scrollable: false,
                layout: {
                    type: 'hbox',
                    align: 'stretch',
                },
                defaults: {
                    bodyPadding: '20',
                },
                style: {
                    borderColor: '#red',
                },
                margin: '10 0 0 0',
                height: 120,
                autoheight: true,
                items: [{
                        xtype: 'panel',
                        reference: 'vaultpanel',
                        cls: 'otc-main-center',
                        header: false,
                        flex: 13,
                        padding: '0 0 0 5',
                        margin: '0 10 0 0',
                        border: false,
                        listeners: {
                            afterrender: function(form) {
                                var hasvaultpermission = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() + '/dashboard/vault');
                                settings = !hasvaultpermission; // reverse variable
                                settings = false;
                                // Update the hidden property based on the variable
                                form.setHidden(settings);
                            }
                        },
                        items: [{
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [{
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [{
                                            xtype: 'displayfield',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                                            value: PROJECTBASE + ' Vault <i class="fa fa-university"></i>',
                                            fieldStyle: 'text-align: center;',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                            fieldStyle: 'text-align: center; margin-top:-20px;',
                                            value: '-',
                                            bind: {
                                                value: '{formattedvault} gm'
                                            }
                                        },
                                    ],
                                },

                            ]
                        }, ],
                    },
                    {
                        xtype: 'panel',
                        reference: 'customerpanel',
                        cls: 'otc-main-center',
                        header: false,
                        flex: 13,
                        padding: '0 0 0 5',
                        margin: '0 10 0 10',
                        border: false,
                        listeners: {
                            afterrender: function(form) {
                                var hastotalcustomerholdingpermission = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() + '/dashboard/totalcustomerholding');
                                settings = !hastotalcustomerholdingpermission; // reverse variable
                                settings = false;
                                // Update the hidden property based on the variable
                                form.setHidden(settings);
                            }
                        },
                        items: [{
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [{
                                    layout: 'vbox',
                                    width: '100%',
                                    style: {
                                        'margin': '5px 5px 0px 0px',
                                    },
                                    items: [{
                                            xtype: 'displayfield',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                                            value: 'Total Customer Holding <i class="fa fa-users"></i>',
                                            fieldStyle: 'text-align: center;',
                                        },
                                        {
                                            xtype: 'displayfield',
                                            width: '100%',
                                            fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                            fieldStyle: 'text-align: center; margin-top:-20px;',
                                            value: '-',
                                            bind: {
                                                value: '{formattedtotalcustomerholding} gm'
                                            }
                                        },
                                    ],

                                },

                            ]
                        }, ],
                    },
                    {
                        xtype: 'panel',
                        reference: 'balancepanel',
                        cls: 'otc-main-center',
                        header: false,
                        flex: 13,
                        padding: '0 0 0 5',
                        margin: '0 0 0 10',
                        border: false,
                        listeners: {
                            afterrender: function(form) {
                                var hasbalancepermission = snap.getApplication().hasPermission('/root/' + PROJECTBASE.toLowerCase() + '/dashboard/balance');
                                settings = !hasbalancepermission; // reverse variable
                                settings = false;
                                // Update the hidden property based on the variable
                                form.setHidden(settings);
                            }
                        },
                        items: [{
                            layout: 'hbox',
                            width: '100%',
                            componentCls: 'otc-main-left-dashboard-header',
                            items: [{
                                layout: 'vbox',
                                width: '100%',
                                style: {
                                    'margin': '5px 5px 0px 0px',
                                },
                                items: [{
                                        xtype: 'displayfield',
                                        width: '100%',
                                        fieldCls: 'otc-displayfield-medium-text-dashboard-center',
                                        value: 'Balance <i class="fas fa-warehouse"></i>',
                                        fieldStyle: 'text-align: center;',
                                    },
                                    {
                                        xtype: 'displayfield',
                                        width: '100%',
                                        fieldCls: 'otc-displayfield-large-text-dashboard-center',
                                        fieldStyle: 'text-align: center; margin-top:-20px;',
                                        value: '-',
                                        bind: {
                                            value: '{formattedbalance} gm'
                                        }

                                    },
                                ],

                            }, ]
                        }, ],


                    },
                ]
            },
            {
                // title: 'Summary',
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
                        xtype: 'datefield', reference: 'startDate', cls: 'datebox', flex: 4, fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto', width: '90%', margin: '0 10 0 0',
                    },
                    {
                        xtype: 'datefield', reference: 'endDate', cls: 'datebox', flex: 4, fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto', width: '90%', margin: '0 10 0 0',
                    },
                    // { 
                    //     flex:1,
                    //     xtype:'combobox',
                    //     cls:'combo_box',
                    //     store: {
                    //         fields: ['abbr', 'name'],
                    //         data : [
                    //             {"abbr":"", "name":""},
                    //             {"abbr":"ICNO", "name":"Identity Card Number"},
                    //             {"abbr":"ACCNO", "name":"Account Number"},
                    //             {"abbr":"CRNO", "name":"Company Registration Number"}
                                
                    //         ]
                    //     },
                    //     queryMode: 'local',
                    //     displayField: 'name',
                    //     valueField: 'abbr',
                    //     forceSelection: true,
                    //     editable: false,
                    //     margin: "0 10 0 10",
                    // },
          
                    {   
                        // title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                        // header: {
                        //     style: {
                        //         backgroundColor: 'white',
                        //         display: 'inline-block',
                        //         color: '#000000',
                                
                        //     }
                        // },
                        // style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                        //title: 'Ask',
                      
                        flex:1,
                        xtype:'button',
                        text:'SEARCH',
                        iconCls: 'x-fa fa-search',
                        cls:'search_btn',
                        handler:'',
                        margin: "0 0 0 10",
                        handler: 'searchPriceHistory'
                    },
              
                    {   
                        flex:1,
                        xtype:'button',
                        text:'DOWNLOAD',
                        iconCls: 'x-fa fa-download',
                        cls:'search_btn',
                        handler:'',
                        margin: "0 0 0 10",
                        handler: 'getHistoricalPriceReport'
                    },
                ]

            },
            // {
            //     xtype: 'container',
            //     title: '',
            //     layout: 'hbox',
            //     header: false,
            //     collapsible: true,
            //     border: false,
            //     items: [
            //         {
            //             xtype: 'datefield', flex: 0.4, fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto', width: '90%', margin: '0 10 0 0',
            //         },
            //         {
            //             xtype: 'datefield',  flex: 0.4, fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto', width: '90%', margin: '0 10 0 0',
            //         },
            //         // {
            //         //     text: 'Approve Transaction', flex: 0.4, cls: '', tooltip: 'Approve Buy/Sell Order',iconCls: 'x-fa fa-check', reference: 'approvetransaction', handler: 'approveTransaction',  showToolbarItemText: true, 
            //         // },
            //     ]
            // },
          
            {
                xtype: 'panel',
                title: '',
                layout: 'hbox',
                collapsible: true,
                header: false,
                // cls: 'otc-panel',
                // defaults: {
                //   layout: 'vbox',
                //   flex: 1,
                //   bodyPadding: 10
                // },
                margin: "10 0 0 0",
                scrollable:true,
                items: [
                    {
                        xtype: 'cartesian',
                        renderTo: document.body,
                        width: "98%",
                        height: 400,
                        insetPadding: 40,
                        reference: 'pricehistorygraph',
                        store: {
                            fields: ['open_sell', 'close_sell', 'high_sell', 'min_sell', 'priceproviderid', 'date'],
                            // data: [{
                            //     'date': 'metric one',
                            //     'close_sell': 10,
                            //     'data2': 14
                            // }, {
                            //     'date': 'metric two',
                            //     'close_sell': 7,
                            //     'data2': 16
                            // }, {
                            //     'date': 'metric three',
                            //     'close_sell': 5,
                            //     'data2': 14
                            // }, {
                            //     'date': 'metric four',
                            //     'close_sell': 2,
                            //     'data2': 6
                            // }, {
                            //     'date': 'metric five',
                            //     'close_sell': 27,
                            //     'data2': 36
                            // }]
                        },
                        axes: [{
                            type: 'numeric',
                            position: 'left',
                            fields: ['close_sell'],
                            title: {
                                text: 'Price (RM)',
                                fontSize: 15
                            },
                            grid: true,
                            // minimum: 0
                        }, {
                            type: 'category',
                            position: 'bottom',
                            fields: ['date'],
                            title: {
                                text: 'Date',
                                fontSize: 15
                            }
                        }],
                        series: [{
                            type: 'line',
                            title: 'Customer Buy',
                            style: {
                                stroke: '#30BDA7',
                                lineWidth: 2
                            },
                            xField: 'date',
                            yField: 'close_sell',
                            marker: {
                                type: 'path',
                                path: ['M', - 4, 0, 0, 4, 4, 0, 0, - 4, 'Z'],
                                stroke: '#30BDA7',
                                lineWidth: 2,
                                fill: 'white'
                            },
                            tooltip: {
                                trackMouse: true,
                                renderer: 'onSeriesTooltipRender'
                            }
                        }, {
                            type: 'line',
                            title: 'Customer Buy',
                            fill: true,
                            style: {
                                fill: '#96D4C6',
                                fillOpacity: .6,
                                stroke: '#0A3F50',
                                strokeOpacity: .6,
                            },
                            xField: 'date',
                            yField: 'close_sell',
                            marker: {
                                type: 'circle',
                                radius: 4,
                                lineWidth: 2,
                                fill: 'white'
                            },
                            tooltip: {
                                trackMouse: true,
                                renderer: 'onSeriesTooltipRender'
                            }
                        }],
                        listeners: {
                            afterrender: function(form) {
                                // Query Price History Default Range
                                var endDate = new Date(); // Current date
                                var startDate = Ext.Date.subtract(endDate, Ext.Date.MONTH, 1); // Subtract one month from the current date
                    
                                pricehistorygraph = elmnt.getController().lookupReference('pricehistorygraph');
                                snap.getApplication().sendRequest({ hdl: 'myhistoricalprice', action: 'getPriceHistory', 
                                    page_size: 1, 
                                    page_number: 1,
                                    date_from: startDate,
                                    date_to: endDate, 
                                    partnercode: PROJECTBASE,
                                })
                                .then(function(data){
                                    if(data.success) {        
                                        records = data.records;
                                        // load data
                                        pricehistorygraph.store.loadData(records);
                                    }
                                })
                            }
                        },
                     }
                        // ITEM 1
                        // {
                        //     flex: 1,
                        //     xtype: 'chartyearview',
                        //     enableFilter: true,
                        //     toolbarItems: [
                        //       'detail', '|', 'filter', '|',
                        //       {
                        //           xtype: 'datefield', fieldLabel: 'Start', reference: 'startDate', itemId: 'startDate', format: 'd/m/Y', menu: { items: []}, name:'startdateOn', labelWidth:'auto'
                        //       },
                        //       {
                        //           xtype: 'datefield', fieldLabel: 'End', reference: 'endDate', itemId: 'endDate', format: 'd/m/Y', menu: { items: []}, name:'enddateOn', labelWidth:'auto'
                        //       },
                        //       {
                        //           text: 'Download', cls: '', tooltip: 'Download Order',iconCls: 'x-fa fa-download', reference: 'dailytransactionreport', handler: 'getTransactionReport',  showToolbarItemText: true, printType: 'xlsx', // printType: pending
                        //       },
                        //       {
                        //         text: 'Approve Transaction', cls: '', tooltip: 'Approve Buy/Sell Order',iconCls: 'x-fa fa-check', reference: 'approvetransaction', handler: 'approveTransaction',  showToolbarItemText: true, 
                        //       },
                        //     ],
                        //     // reference: 'myorder',
                        //     // store: {
                        //     //       type: 'MyOrder', proxy: {
                        //     //           type: 'ajax',
                        //     //           url: 'index.php?hdl=myorder&action=list&partnercode=GO',
                        //     //           reader: {
                        //     //               type: 'json',
                        //     //               rootProperty: 'records',
                        //     //           }
                        //     //       },
                        //     // },
                        //     // Add form
                        //     formOtcApproval: {
                        //         formDialogWidth: 950,
                        //         controller: 'myorder-myorder',
                        
                        //         formDialogTitle: 'Transaction Approval',
                        
                        //         // Settings
                        //         enableFormDialogClosable: false,
                        //         formPanelDefaults: {
                        //             border: false,
                        //             xtype: 'panel',
                        //             flex: 1,
                        //             layout: 'anchor',
                        //             msgTarget: 'side',
                        //             margins: '0 0 10 10'
                        //         },
                        //         enableFormPanelFrame: false,
                        //         formPanelLayout: 'hbox',
                        //         formViewModel: {
                        
                        //         },
                        
                        //         formPanelItems: [
                        //             //1st hbox
                        //             {
                        //                 items: [
                        //                     { xtype: 'hidden', hidden: true, name: 'id' },
                        //                     {
                        //                         itemId: 'user_main_fieldset',
                        //                         xtype: 'fieldset',
                        //                         title: 'Main Information',
                        //                         title: 'Account Holder Details',
                        //                         layout: 'hbox',
                        //                         defaultType: 'textfield',
                        //                         fieldDefaults: {
                        //                             anchor: '100%',
                        //                             msgTarget: 'side',
                        //                             margin: '0 0 5 0',
                        //                             width: '100%',
                        //                         },
                        //                         items: [
                        //                             {
                        //                                 xtype: 'fieldcontainer',
                        //                                 fieldLabel: '',
                        //                                 defaultType: 'textboxfield',
                        //                                 layout: 'hbox',
                        //                                 items: [
                        //                                     {
                        //                                         xtype: 'displayfield', allowBlank: false, fieldLabel: 'Order No', reference: 'ordorderno', name: 'ordorderno', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                        //                                     },
                        //                                     {
                        //                                         xtype: 'displayfield', allowBlank: false, fieldLabel: 'Total Amount', reference: 'ordamount', name: 'ordamount', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                        //                                     },
                        //                                 ]
                        //                             }
                        //                         ]
                        //                     },
                        //                     {
                        //                         xtype: 'fieldset', title: 'Enter Approval Code', collapsible: false,
                        //                         items: [
                        //                             {
                        //                                 xtype: 'fieldcontainer',
                        //                                 layout: {
                        //                                     type: 'hbox',
                        //                                 },
                        //                                 items: [
                        //                                     {
                        //                                         xtype: 'textfield', fieldLabel: '', name: 'approvalcode', flex: 2, style: 'padding-left: 20px;', id: 'approvalcode'
                        //                                     },
                        //                                 ]
                        //                             },
                        //                         ]
                        //                     },
                        //                     // {
                        //                     //     xtype: 'form',
                        //                     //     reference: 'searchresultsforpep-form',
                        //                     //     border: false,
                        //                     //     items: [
                        //                     //         {
                        //                     //             title: '',
                        //                     //             flex: 13,
                        //                     //             xtype: 'mypepmatchdataview',
                        //                     //             reference: 'mypepematchdata',
                        //                     //             enablePagination: false
                        
                        //                     //         },
                        //                     //     ],
                        //                     // },
                        //                     {
                        //                         xtype: 'fieldset', title: 'Remarks', collapsible: false,
                        //                         items: [
                        //                             {
                        //                                 xtype: 'fieldcontainer',
                        //                                 layout: {
                        //                                     type: 'hbox',
                        //                                 },
                        //                                 items: [
                        //                                     {
                        //                                         xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'approvalremarks'
                        //                                     },
                        //                                 ]
                        //                             },
                        //                         ]
                        //                     }
                        //                 ],
                        //             },
                        //         ],
                        
                               
                        //     },
                        //     // Form for approval
                        // },
                        // END ITEM 1
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    },
});
