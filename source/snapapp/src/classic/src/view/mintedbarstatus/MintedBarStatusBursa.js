Ext.define('snap.view.mintedbarstatus.MintedBarStatusBursa', {
    extend: 'Ext.container.Container',
    xtype: 'mintedbarstatusbursaview',
    requires: [
        'snap.store.VaultItem',
        'snap.model.VaultItem',
        'snap.view.mintedbarstatus.MintedBarStatusController',
        'snap.view.mintedbarstatus.MintedBarStatusModel'
    ],
    controller: 'mintedbarstatus-mintedbarstatus',
    viewModel: {
        type: 'mintedbarstatus-mintedbarstatus',
    },
    permissionRoot: '/root/bursa/mintedbar',
    partnercode: 'BURSA',
    layout: {
        type: 'vbox',
        align: 'center'
    },
    listeners: {
        // afterlayout: function() {
        //     var height = Ext.getBody().getViewSize().height;
        //     if (this.getHeight() > height) {
        //         this.setHeight(height);
        //     }
        //     this.center();
          
        // },
        afterrender: function () {
            
            // Get the screen height
            var height = Ext.getBody().getViewSize().height;

            var cardHeight = height * 12.6/100;
            var me = this;

            // get partnercode
            if(this.partnercode){
                partnerCode = this.partnercode;
            }else{
                partnerCode = null;
            }
            // Set product id
            zeropointfivegrams = partnerCode + "GS-999-9-0-5g";
            onegram = partnerCode + "GS-999-9-1g";
            twopointfivegrams = partnerCode + "GS-999-9-2-5g";
            fivegrams = partnerCode + "GS-999-9-5g";
            tengrams = partnerCode + "GS-999-9-10g";

            fiftygrams = partnerCode + "GS-999-9-50g";
            hundredgrams = partnerCode + "GS-999-9-100g";
            thousandgrams = partnerCode + "GS-999-9-1000g";

            onedinar = partnerCode + "GS-999-9-1-DINAR";
            fivedinar = partnerCode + "GS-999-9-5-DINAR";
            // Set Windows

            // var panel = Ext.getCmp('mintedwarehouseinventory-display');
            // var button = Ext.getCmp('minted-warehouse-download-button');
            // mintedwarehouseinventorydisplay = partnerCode + 'mintedwarehouseinventory-display';
            // mintedwarehousedownloadbutton = partnerCode +'minted-warehouse-download-button';

            var panel = Ext.getCmp('bursa-mintedwarehouseinventory-display');
            var button = Ext.getCmp('bursa-minted-warehouse-download-button');
            
            // point this to button
            button.me = me;

            panel.removeAll();
            panel.add(
                {
                    layout:'hbox',
                    width: '100%',
                    items:[
                        {
                            layout: 'vbox',
                            width: '19.8%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: -webkit-linear-gradient(132deg, #F4D03F 0%, #16A085 100%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-01.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+zeropointfivegrams+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">0.5 g</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 0.5g minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-0.5g');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                        {
                            layout: 'vbox',
                            width: '19.8%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: -webkit-linear-gradient(30deg, #013A6B 0%, #004E95 100%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-02.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+onegram+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">1 g</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 1g minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-1g');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                        {
                            layout: 'vbox',
                            width: '20%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: -webkit-linear-gradient(0deg, #09203f 0%, #537895 100%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-03.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+twopointfivegrams+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">2.5 g</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 2.5g minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-2.5g');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                        // {
                        //     layout: 'vbox',
                        //     width: '20%',
                        //     style: {
                        //         'margin': '5px 5px 0px 0px',
                        //     },
                        //     items: [
                        //         {
                        //             html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: -webkit-linear-gradient(90deg, #FEE140  0%, #FA709A 100%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-04.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="GS-999-9-5g">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">5 g</div></div>',
                        //             width: '100%',
                        //         },

                        //     ]
                        // },
                        {
                            layout: 'vbox',
                            width: '20%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: linear-gradient(to right top, #f2e800, #fad000, #ffb700, #ff9e00, #ff8500);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-04.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+fivegrams+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">5 g</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 5g minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-5g');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                        {
                            layout: 'vbox',
                            width: '20%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: linear-gradient(to left bottom, #051937, #004d7a, #008793, #00bf72, #a8eb12);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-05.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+tengrams+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">10 g</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 10g minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-10g');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                    ]
                },
                {
                    layout:'hbox',
                    width: '100%',
                    items:[
                        {
                            layout: 'vbox',
                            // width: '24.8%',
                            width: '19.8%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: -webkit-linear-gradient(90deg, #74EBD5 0%, #9FACE6 100%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-06.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+fiftygrams+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">50 g</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 50g minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-50g');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                        {
                            layout: 'vbox',
                            width: '19.8%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: -webkit-linear-gradient(43deg, #4158D0 0%, #C850C0 46%, #FFCC70 100%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-07.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+hundredgrams+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">100 g</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 100g minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-100g');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                        {
                            layout: 'vbox',
                            width: '20%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #FFE53B;background-image: linear-gradient(147deg, #FFE53B 0%, #FF2525 74%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-10.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+thousandgrams+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">1000 g</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 1000g minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-100g');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                        {
                            layout: 'vbox',
                            width: '20%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: -webkit-linear-gradient(160deg, #0093E9 0%, #80D0C7 100%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-08.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+onedinar+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">1 Dinar</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 1 Dinar minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-1-DINAR');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                        {
                            layout: 'vbox',
                            width: '20%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: '+cardHeight+'px;background-color: #013A6B;background-image: -webkit-linear-gradient(315deg, #3f0d12 0%, #a71d31 74%);"><div class="icon" style="color:#ffffff;font-size:6.5em;"><img src="src/resources/images/igr/IGR-White-09.png"></div><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="'+fivedinar+'">-</span><div style="color:#ffffff;font-size:1.3em;font-weight: bold;font-size: 2.5rem;">5 Dinar</div></div>',
                                    width: '100%',
                                },

                            ],
                            // Do SN display
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'This is the stock for 5 Dinar minted bars';
                
                                },
                                // click: {
                                    
                                //         element: 'el', //bind to the underlying el property on the panel
                                //         fn: function(){ 
                                            
                                //             me.exportMintedListButton('GS-999-9-5-DINAR');
                                //         }
                                //     },
                            }
                            // End SN display
                        },
                    ]
                },
                // {
                //     xtype: 'toolbar',
                //     dock: 'top',
                //     layout: 'fit',
                //     items: [{
                //         xtype: 'button',
                //         text: 'Download',
                //         iconCls: 'x-fa fa-download',
                //     }],
                //     listeners : {
                //         render: function(p) {
                        
                //             this.getEl().dom.title = 'Export minted bar data';
        
                //         },
                //         click: {
                        
                //             element: 'el', //bind to the underlying el property on the panel
                //             fn: function(){ 
                                
                //                 me.exportMintedListButton('GS-999-9');
                //             }
                //         },
                //     }
                // },
            );

            snap.getApplication().sendRequest({
                hdl: 'goldbarstatus', action: 'getSharedMintedList', partnercode: partnerCode,
            }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        // O.5 and 2.5g have diff naming due to pointer not working with .
                        Ext.get(zeropointfivegrams).dom.innerHTML = data.zeropointfivegrams;
                        Ext.get(onegram).dom.innerHTML = data.onegram;
                        Ext.get(twopointfivegrams).dom.innerHTML = data.twopointfivegrams;
                        Ext.get(fivegrams).dom.innerHTML = data.fivegrams;
                        Ext.get(tengrams).dom.innerHTML = data.tengrams;
                        Ext.get(fiftygrams).dom.innerHTML = data.fiftygrams;
                        Ext.get(hundredgrams).dom.innerHTML = data.hundredgrams;
                        Ext.get(thousandgrams).dom.innerHTML = data.thousandgrams;
                        Ext.get(onedinar).dom.innerHTML = data.onedinar;
                        Ext.get(fivedinar).dom.innerHTML = data.fivedinar;
                        
                    }
                })
        }
    },
    exportMintedListButton: function(type){

        // grid header data
        header = [];
   
        partnercode = 'BURSA';
        
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

        url = '?hdl=goldbarstatus&action=exportMintedList&header='+header+'&daterange='+daterange+'&type='+type+'&partnercode='+partnercode;
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
    items: [
        // {
        //     xtype: 'panel',
        //     layout: {
        //         type: 'vbox',
        //     },
        //     width: '100%',
        //     style: {
        //         padding: '5px',
        //     },
        //     items: [
        //         {
        //             title: 'Kilobar Inventory By Warehouse Location',
        //             header: {
        //                 style: 'background-color: #204A6D;border-color: #204A6D;',
        //             },
        //             layout: 'vbox',
        //             width: '100%',
        //             items: [   
        //                 {
        //                     layout:'hbox',
        //                     width: '100%',
        //                     items:[
        //                         {
        //                             layout: 'vbox',
        //                             width: '33.2%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">TAIPAN</span></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                         {
        //                             layout: 'vbox',
        //                             width: '33.2%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">G4S</span></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                         {
        //                             layout: 'vbox',
        //                             width: '33.33%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="line-height: 10px;background:#204A6D;padding:5px;text-align:center"><span style="color:#ffffff;width:100%;">TOTAL</span></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                     ]
        //                 } ,                   
        //                 {
        //                     layout:'hbox',
        //                     width: '100%',
        //                     items:[
        //                         {
        //                             layout: 'vbox',
        //                             width: '16.6%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#988c59"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="logicalcount">-</span><div style="color:#ffffff;font-size:1.3em;">LOGICAL</div></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                         {
        //                             layout: 'vbox',
        //                             width: '16.6%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#0D47A1"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="reservedcount">-</span><div style="color:#ffffff;font-size:1.3em;">RESERVED</div></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                         {
        //                             layout: 'vbox',
        //                             width: '16.6%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#ffb91b"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="aceg4scount">-</span><div style="color:#ffffff;font-size:1.3em;">G4S-ACE</div></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                         {
        //                             layout: 'vbox',
        //                             width: '16.6%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#1aa124"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="mbbg4scount">-</span><div style="color:#ffffff;font-size:1.3em;">G4S-MBB</div></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                         {
        //                             layout: 'vbox',
        //                             width: '16.6%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#B71C1C"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="totalcount">-</span><div style="color:#ffffff;font-size:1.3em;">TOTAL</div></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                         ,
        //                         {
        //                             layout: 'vbox',
        //                             width: '16.6%',
        //                             style: {
        //                                 'margin': '5px 5px 0px 0px',
        //                             },
        //                             items: [
        //                                 {
        //                                     html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#fc4e70"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="overallcount">-</span><div style="color:#ffffff;font-size:1.3em;">OVERALL</div></div>',
        //                                     width: '100%',
        //                                 },
        
        //                             ]
        //                         },
        //                     ]
        //                 }

                        
        //             ]
        //         }, 
        //         // {
        //         //     title: 'Minted GoldBar Inventory By MIB Branch Location',
        //         //     header: {
        //         //         style: 'background-color: #204A6D;border-color: #204A6D;',
        //         //     },
        //         //     region: 'center',
        //         //     margin: '5 0 0 0',
        //         //     xtype: 'mintedbarlocationwise',
        //         // }
        //     ],
        // },

        {
            xtype: 'panel',
            layout: {
                type: 'vbox',
            },
            width: '100%',
            style: {
                padding: '5px',
            },
            items: [
                {
                    title: 'Minted Bar Inventory List By Denomination for Bursagold',
                    text: 'Download', 
                    tooltip: 'Export Data', 
                    header: {
                        style: 'background-color: #204A6D;border-color: #204A6D;',
                    }, 
                  
                    id: 'bursa-mintedwarehouseinventory-display',
                    layout: 'vbox',
                    width: '100%',
                    header: {
                        // Custom style for Migasit
                        /*style: {
                            backgroundColor: '#204A6D',
                        },*/
                        style : 'border-color: #204A6D;',
                        titlePosition: 0,
                        items: [{
                            xtype: 'button',
                            text: 'Download',
                            iconCls: 'x-fa fa-download',
                            reference: 'spotorder-status',
                            id: 'bursa-minted-warehouse-download-button',
                            //style: 'background-color: #B2C840'
                            style: 'border-radius: 20px;background-color: #606060;border-color: #204A6D',
                            listeners : {
                                render: function(p) {
                                
                                    this.getEl().dom.title = 'Export minted bar data';
                
                                },
                                click: {
                                
                                    element: 'el', //bind to the underlying el property on the panel
                                    fn: function(){ 
                                        
                                        Ext.getCmp('bursa-minted-warehouse-download-button').me.exportMintedListButton('GS-999-9');
                                    }
                                },
                            }
                        }]
                    },
                    // End SN displ
 
                }, 
                
               
            ],
        }
    ]
});
