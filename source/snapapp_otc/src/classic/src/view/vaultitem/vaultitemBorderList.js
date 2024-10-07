Ext.define('snap.view.vaultitem.vaultitemBorderList', {
    extend: 'Ext.panel.Panel',
    xtype: 'vaultitem-border',
    requires: [
        'Ext.layout.container.Border'
    ],
    profiles: {
        classic: {
            itemHeight: 100
        },
        neptune: {
            itemHeight: 100
        },
        graphite: {
            itemHeight: 120
        },
        'classic-material': {
            itemHeight: 120
        }
    },
    layout: 'border',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    bodyBorder: false,

    defaults: {
        collapsible: true,
        split: true,
        bodyPadding: 10
    },
    viewModel: {
        data: {
            name: "MIB",
            withdoserialnumbers: [],
            withoutdoserialnumbers: [],
            transferringserialnumbers: [],
            permissions : [],
            acehqserialnumbers: [],
            aceg4sserialnumbers: [],
            mbbg4sserialnumbers: [],
            totalserialnumbers: [],
            status: '',
            element: '',

        }
    },
    listeners: {
        afterrender: function () {
            elmnt = this;
            vmv = this.getViewModel();
            snap.getApplication().sendRequest({
                hdl: 'vaultitem', action: 'getSummary' , origintype : PROJECTBASE, 
            }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        Ext.get('withdocount').dom.innerHTML = data.withdocount;
                        Ext.get('withoutdocount').dom.innerHTML = data.withoutdocount;
                        Ext.get('transferringcount').dom.innerHTML = data.transferringcount;
                        
                        Ext.get('vaultreservedcount').dom.innerHTML = data.hqcount;
                        Ext.get('vaultaceg4scount').dom.innerHTML = data.aceg4scount;
                        Ext.get('vaultmbbg4scount').dom.innerHTML = data.mbbg4scount;
                        Ext.get('vaulttotalcount').dom.innerHTML = data.total;

                        vmv.set('withdoserialnumbers', data.withdoserialnumbers);
                        vmv.set('withoutdoserialnumbers', data.withoutdoserialnumbers);
                        vmv.set('transferringserialnumbers', data.transferringserialnumbers);
                            
                        vmv.set('acehqserialnumbers', data.acehqserialnumbers);
                        vmv.set('aceg4sserialnumbers', data.aceg4sserialnumbers);
                        vmv.set('mbbg4sserialnumbers', data.mbbg4sserialnumbers);
                        vmv.set('totalserialnumbers', data.totalserialnumbers);
                        
                        vmv.set('element', this);
                        // Set Status
                        //vmv.set('status', data.status);
    
                        //alert(data.withdoserialnumbers);
                        //alert(data.withoutdoserialnumbers);
                    }
                })
        }
    },

    items: [
        /*{
            title: 'Summary',
            region: 'south',
            height: 130,
            minHeight: 75,
            maxHeight: 800,
            layout: {
                type: 'hbox',
            },
            defaults: {
                bodyStyle: 'padding:0px;margin-top:10px',
            },
            // xtype: 'vaultitemview'            
            items: [
                {
                    html: '<div style="background:#EFE8DE;height:40px;padding-top:6px;margin-left:2px;text-align:center">Current Allocated : <span id="allocatedcount">0</span>&nbsp;</div>',
                    flex: 1,
                }, {
                    html: '&nbsp;',
                }, {
                    html: '<div style="background:#EFE8DE;height:40px;padding-top:6px;margin-left:2px;text-align:center">Available : <span id="availablecount">0</span>&nbsp;</div>',
                    flex: 1,
                }, {
                    html: '&nbsp;',
                }
                , {
                    html: '<div style="background:#EFE8DE;height:40px;padding-top:6px;margin-left:2px;text-align:center">On Request : <span id="onrequestcount">0</span>&nbsp;</div>',
                    flex: 1,
                }, {
                    html: '&nbsp;',
                }
                , {
                    html: '<div style="background:#EFE8DE;height:40px;padding-top:6px;margin-left:2px;text-align:center">Return : <span id="returncount">0</span>&nbsp;</div>',
                    flex: 1,
                }, {
                    html: '&nbsp;',
                }
            ],

        },*/
        {
            title: 'Summary',
            region: 'south',
            height: 280,
            minHeight: 75,
            maxHeight: 800,
            layout: {
                type: 'hbox',
            },
            defaults: {
                bodyStyle: 'padding:0px;margin-top:10px',
            },
            // xtype: 'vaultitemview'            
            /*
            items: [
                {
                    html: '<div style="background:#EFE8DE;height:40px;padding-top:6px;margin-left:2px;text-align:center">Current Allocated : <span id="allocatedcount">0</span>&nbsp;</div>',
                    flex: 1,
                }, {
                    html: '&nbsp;',
                }, {
                    html: '<div style="background:#EFE8DE;height:40px;padding-top:6px;margin-left:2px;text-align:center">Available : <span id="availablecount">0</span>&nbsp;</div>',
                    flex: 1,
                }, {
                    html: '&nbsp;',
                }
                , {
                    html: '<div style="background:#EFE8DE;height:40px;padding-top:6px;margin-left:2px;text-align:center">On Request : <span id="onrequestcount">0</span>&nbsp;</div>',
                    flex: 1,
                }, {
                    html: '&nbsp;',
                }
                , {
                    html: '<div style="background:#EFE8DE;height:40px;padding-top:6px;margin-left:2px;text-align:center">Return : <span id="returncount">0</span>&nbsp;</div>',
                    flex: 1,
                }, {
                    html: '&nbsp;',
                }
            ],*/
            // Size is 24 blocks spread across 3 screens
            items:[{   
                title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Number Status</span>',
                header: {
                    style: {
                        backgroundColor: 'white',
                        display: 'inline-block',
                        color: '#000000',
                        
                    }
                },
                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                //title: 'Ask',
                flex: 10,
                margin: '0 10 0 0',
                items: [{
                    title: 'Kilobar with Serial Numbers',
                    header: {
                        style: 'background-color: #204A6D;border-color: #204A6D;',
                    },
                    layout: 'hbox',
                    width: '100%',
                    items: [
                        {
                            layout: 'vbox',
                            width: '50%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#62059E"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withdocount">-</span><div style="color:#ffffff;font-size:1.3em;">With Delivery Order</div></div>',
                                    width: '100%',
                                },

                            ],
                            
                            listeners : {
                                render: function(p) {
                                    var theElem = p.getEl();
                                    withoutserialnumber = 0;
                                    var theTip = Ext.create('Ext.tip.Tip', {
                                        html:  '<div>Click to view all Serial Numbers with <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                        style: {

                                        },
                                        margin: '520 0 0 520',
                                        shadow: false,
                                        maxHeight: 400,
                                    });
                                    
                                    p.getEl().on('mouseover', function(){
                                        theTip.showAt(theElem.getX(), theElem.getY());
                                    });
                                    
                                    p.getEl().on('mouseleave', function(){
                                        theTip.hide();
                                    });
                                },
                                click: {
                                        element: 'el', //bind to the underlying el property on the panel
                                        fn: function(){ 
                                            var windowforserialnumberwithdo = new Ext.Window({
                                                iconCls: 'x-fa fa-cube',
                                                xtype: 'form',
                                                header: {
                                                    // Custom style for Migasit
                                                    /*style: {
                                                        backgroundColor: '#204A6D',
                                                    },*/
                                                    style : 'background-color: #204A6D;border-color: #204A6D;',
                                                },
                                                scrollable: true,
                                                title: 'Serial Numbers',
                                                layout: 'fit',
                                                width: 400,
                                                height: 600,
                                                maxHeight: 2000,
                                                modal: true,
                                                //closeAction: 'destroy',
                                                plain: true,
                                                buttonAlign: 'center',
                                                items: [
                                                   {   
                                                        title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                        header: {
                                                            style: {
                                                                backgroundColor: 'white',
                                                                display: 'inline-block',
                                                                color: '#000000',
                                                                
                                                            }
                                                        },
                                                        style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                        //title: 'Ask',
                                                        flex: 3,
                                                        scrollable: true,
                                                        margin: '0 10 0 0',
                                                        items: [
                                                            {
                                                                xtype: 'container',
                                                                items: [{
                                                                    id: 'windowforserialnumberwithdo',
                                                                }]
                                                       
                                                            }
                                                        ]
                                                    },
                                                ],
                                                buttons: [{
                                                    text: 'OK',
                                                    handler: function(btn) {
                                                        
                                                        owningWindow = btn.up('window');
                                                        //owningWindow.closeAction='destroy';
                                                        owningWindow.close();
                                                    } 
                                                },],
                                                closeAction: 'destroy',
                                                //items: spotpanelbuytotalxauweight
                                            });
                                            
                                            
                                            if(vmv.get('withdoserialnumbers').length != 0){
                                                windowforserialnumberwithdo.show();
                                            
                                        
                                                element = vmv.get('element');
                                                var panel = Ext.getCmp('windowforserialnumberwithdo');

                                                //date = data.createdon.date;
                                                //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                panel.removeAll();
                                                vmv.get('withdoserialnumbers').map((x) => {
                            
                                                panel.add(element.serialnoTemplateWithDO(x))
                                                })
                                            }else {
                                                Ext.MessageBox.show({
                                                    title: 'Alert',
                                                    msg: 'No records available for Serial Numbers with D/O ',
                                                    buttons: Ext.MessageBox.OK,
                                                    icon: Ext.MessageBox.WARNING,
                                                });
                                                Ext.getCmp('windowforserialnumberwithdo').destroy();
                                            }
                                         
                                           
                                        }
                                    },
                            }
                        },
                        {
                            layout: 'vbox',
                            width: '50%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {   
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#AB04C5"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="withoutdocount">-</span><div style="color:#ffffff;font-size:1.3em;">Without Delivery Order</div></div>',
                                    width: '100%',
                                    tooltip: 'Queue Order',
                                    listeners : {
                                        render: function(p) {
                                            //debugger;
                                            var theElem = p.getEl();
                                            //var theElem = this.down().getEl();
                                            var theTip = Ext.create('Ext.tip.ToolTip', {
                                                html:  '<div>Click to view all Serial Numbers without <span span style="color:#ffffff;font-weight:900;">Delivery Order Number</span>&nbsp;</div>',
                                                margin: '520 0 0 520',
                                                shadow: false,
                                                trackMouse: true,
                                            });
                                            
                                            p.getEl().on('mouseover', function(){
                                                //debugger;
                                                //theTip.showAt(theElem.getX(), theElem.getY());
                                                theTip.showAt(theElem.getX(), theElem.getY());
                                                //allserialnumbers = vmv.get('withdoserialnumbers');
                                                //theTip.update("newtip");
                                                //debugger;
                                            });
                                            
                                            p.getEl().on('mouseleave', function(){
                                                theTip.hide();
                                            });
                                        },
                                        click: {
                                            element: 'el', //bind to the underlying el property on the panel
                                            fn: function(){ 
                                                var windowforserialnumberwithoutdo = new Ext.Window({

                                               
                                                    iconCls: 'x-fa fa-cube',
                                                    xtype: 'form',
                                                    header: {
                                                        // Custom style for Migasit
                                                        /*style: {
                                                            backgroundColor: '#204A6D',
                                                        },*/
                                                        style : 'background-color: #204A6D;border-color: #204A6D;',
                                                    },
                                                    scrollable: true,
                                                    title: 'Serial Numbers',
                                                    layout: 'fit',
                                                    width: 400,
                                                    height: 600,
                                                    maxHeight: 2000,
                                                    modal: true,
                                                    //closeAction: 'destroy',
                                                    plain: true,
                                                    buttonAlign: 'center',
                                                    items: [
                                                       {   
                                                            title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                            header: {
                                                                style: {
                                                                    backgroundColor: 'white',
                                                                    display: 'inline-block',
                                                                    color: '#000000',
                                                                    
                                                                }
                                                            },
                                                            style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                            //title: 'Ask',
                                                            flex: 3,
                                                            scrollable: true,
                                                            margin: '0 10 0 0',
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    items: [{
                                                                        id: 'windowforserialnumberwithoutdo',
                                                                    }]
                                                           
                                                                }
                                                            ]
                                                        },
                                                    ],
                                                    buttons: [{
                                                        text: 'OK',
                                                        handler: function(btn) {
                                                             
                                                            owningWindow = btn.up('window');
                                                            //owningWindow.closeAction='destroy';
                                                            owningWindow.close();
                                                        } 
                                                    },],
                                                    closeAction: 'destroy',
                                                    //items: spotpanelbuytotalxauweight
                                                });
                                                if(vmv.get('withoutdoserialnumbers').length != 0){
                                                    windowforserialnumberwithoutdo.show();
                                                
                                            
                                                    element = vmv.get('element');
                                                    var panel = Ext.getCmp('windowforserialnumberwithoutdo');
        
                                                    //date = data.createdon.date;
                                                    //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                    panel.removeAll();
                                                    vmv.get('withoutdoserialnumbers').map((x) => {
                                
                                                      panel.add(element.serialnoTemplateWithoutDO(x))
                                                    })
                                                }else {
                                                    Ext.MessageBox.show({
                                                        title: 'Alert',
                                                        msg: 'No records available for Serial Numbers without D/O ',
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.WARNING,
                                                    });
                                                    Ext.getCmp('windowforserialnumberwithoutdo').destroy();
                                                }
                                              
                                             }
                                        },
                                    }
                                },

                            ],
                      
                        },
                    ]
                },]
            },
            /*{   
                title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Transferring</span>',
                header: {
                    style: {
                        backgroundColor: 'white',
                        display: 'inline-block',
                        color: '#000000',
                        
                    }
                },
                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                //title: 'Ask',
                flex: 3,
                margin: '0 10 0 0',
                items: [{
                    xtype: 'displayfield', id: 'bidpjricedashboard', name:'bidprice', reference: 'bidprice', value: '-', fieldStyle: 'padding-left:5px;font: 900 25px/5px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#21A6DB;', flex: 1,
                },]
            },*/
            {   
                title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;"> .</span>',
                header: {
                    style: {
                        backgroundColor: 'white',
                        display: 'inline-block',
                        color: '#000000',
                        
                    }
                },
                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                //title: 'Ask',
                flex: 6,
                margin: '0 10 0 0',
                items: [{
                    title: 'Transferring',
                    header: {
                        style: 'background-color: #204A6D;border-color: #204A6D;',
                    },
                    layout: 'hbox',
                    width: '100%',
                    items: [
                        {
                            layout: 'vbox',
                            width: '100%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#0A9DB3"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="transferringcount">-</span><div style="color:#ffffff;font-size:1.3em;">CURRENTLY IN TRANSFERRING</div></div>',
                                    width: '100%',
                                },

                            ],
                            tooltip: "Henlo",
                            listeners : {
                                click: {
                                    element: 'el', //bind to the underlying el property on the panel
                                    fn: function(){ 
                                        var windowforserialnumbertransferring = new Ext.Window({

                                            iconCls: 'x-fa fa-cube',
                                            xtype: 'form',
                                            header: {
                                                // Custom style for Migasit
                                                /*style: {
                                                    backgroundColor: '#204A6D',
                                                },*/
                                                style : 'background-color: #204A6D;border-color: #204A6D;',
                                            },
                                            scrollable: true,
                                            title: 'Serial Numbers',
                                            layout: 'fit',
                                            width: 600,
                                            height: 600,
                                            maxHeight: 2000,
                                            modal: true,
                                            //closeAction: 'destroy',
                                            plain: true,
                                            buttonAlign: 'center',
                                            items: [
                                               {   
                                                    title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                    header: {
                                                        style: {
                                                            backgroundColor: 'white',
                                                            display: 'inline-block',
                                                            color: '#000000',
                                                            
                                                        }
                                                    },
                                                    style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                    //title: 'Ask',
                                                    flex: 3,
                                                    scrollable: true,
                                                    margin: '0 10 0 0',
                                                    items: [
                                                        {
                                                            xtype: 'container',
                                                            items: [{
                                                                id: 'transferringserialnumbers',
                                                            }]
                                                   
                                                        }
                                                    ]
                                                },
                                            ],
                                            buttons: [{
                                                text: 'OK',
                                                handler: function(btn) {
                                                     
                                                    owningWindow = btn.up('window');
                                                    //owningWindow.closeAction='destroy';
                                                    owningWindow.close();
                                                } 
                                            },],
                                            closeAction: 'destroy',
                                            //items: spotpanelbuytotalxauweight
                                        });

                                        if(vmv.get('transferringserialnumbers').length != 0){
                                            windowforserialnumbertransferring.show();
                                        
                                    
                                            element = vmv.get('element');
                                            var panel = Ext.getCmp('transferringserialnumbers');
    
                                            //date = data.createdon.date;
                                            //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                            panel.removeAll();
                                            vmv.get('transferringserialnumbers').map((x) => {
                        
                                              panel.add(element.transferringserialnumbers(x))
                                            })
                                        }else {
                                            Ext.MessageBox.show({
                                                title: 'Alert',
                                                msg: 'No Serial Numbers are currently in Transferring',
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.WARNING,
                                            });
                                            Ext.getCmp('transferringserialnumbers').destroy();
                                        }

                                       
                                        
                                    
                                       
                                     }

                                     
                                },
                                dblclick: {
                                    element: 'body', //bind to the underlying body property on the panel
                                    fn: function(){ alert('dblclick body'); }
                                }
                            }
                        },
                    ]
                },]
            },
            {   
                title: '<span style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#ffffff;">.</span>',
                header: {
                    style: {
                        backgroundColor: 'white',
                        display: 'inline-block',
                        color: '#000000',
                        
                    }
                },
                style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #ffffff;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                //title: 'Ask',
                flex: 8,
                margin: '0 10 0 0',
                items: [{
                    title: 'Inventory',
                    header: {
                        style: 'background-color: #204A6D;border-color: #204A6D;',
                    },
                    layout: 'hbox',
                    width: '100%',
                    items: [
                        {
                            layout: 'vbox',
                            width: '25%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#0D47A1"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="vaultreservedcount">-</span><div style="color:#ffffff;font-size:1.3em;">RESERVED</div></div>',
                                    width: '100%',
                                    listeners : {
                                        click: {
                                            element: 'el', //bind to the underlying el property on the panel
                                            fn: function(){ 
                                                var windowforserialnumberinventory = new Ext.Window({
        
                                                    iconCls: 'x-fa fa-cube',
                                                    xtype: 'form',
                                                    header: {
                                                        // Custom style for Migasit
                                                        /*style: {
                                                            backgroundColor: '#204A6D',
                                                        },*/
                                                        style : 'background-color: #204A6D;border-color: #204A6D;',
                                                    },
                                                    scrollable: true,
                                                    title: 'Serial Numbers',
                                                    layout: 'fit',
                                                    width: 600,
                                                    height: 600,
                                                    maxHeight: 2000,
                                                    modal: true,
                                                    //closeAction: 'destroy',
                                                    plain: true,
                                                    buttonAlign: 'center',
                                                    items: [
                                                       {   
                                                            title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                            header: {
                                                                style: {
                                                                    backgroundColor: 'white',
                                                                    display: 'inline-block',
                                                                    color: '#000000',
                                                                    
                                                                }
                                                            },
                                                            style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                            //title: 'Ask',
                                                            flex: 3,
                                                            scrollable: true,
                                                            margin: '0 10 0 0',
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    items: [{
                                                                        id: 'acehqserialnumbers',
                                                                    }]
                                                           
                                                                }
                                                            ]
                                                        },
                                                    ],
                                                    buttons: [{
                                                        text: 'OK',
                                                        handler: function(btn) {
                                                             
                                                            owningWindow = btn.up('window');
                                                            //owningWindow.closeAction='destroy';
                                                            owningWindow.close();
                                                        } 
                                                    },],
                                                    closeAction: 'destroy',
                                                    //items: spotpanelbuytotalxauweight
                                                });
                                                
                                                if(vmv.get('acehqserialnumbers').length != 0){
                                                    windowforserialnumberinventory.show();
                                                
                                            
                                                    element = vmv.get('element');
                                                    var panel = Ext.getCmp('acehqserialnumbers');
            
                                                    //date = data.createdon.date;
                                                    //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                    panel.removeAll();
                                                    vmv.get('acehqserialnumbers').map((x) => {
                                
                                                      panel.add(element.serialnoTemplateInventory(x))
                                                    })
                                                }else {
                                                    Ext.MessageBox.show({
                                                        title: 'Alert',
                                                        msg: 'No Serial Numbers are currently in Reserve',
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.WARNING,
                                                    });
                                                    Ext.getCmp('acehqserialnumbers').destroy();
                                                }

                                                
                                             }
        
                                             
                                        },
                                        dblclick: {
                                            element: 'body', //bind to the underlying body property on the panel
                                            fn: function(){ alert('dblclick body'); }
                                        }
                                    }
                                },

                            ]
                        },
                        {
                            layout: 'vbox',
                            width: '25%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#ffb91b"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="vaultaceg4scount">-</span><div style="color:#ffffff;font-size:1.3em;">G4S-ACE</div></div>',
                                    width: '100%',
                                    listeners : {
                                        click: {
                                            element: 'el', //bind to the underlying el property on the panel
                                            fn: function(){ 
                                                var windowforserialnumberinventory = new Ext.Window({
        
                                                    iconCls: 'x-fa fa-cube',
                                                    xtype: 'form',
                                                    header: {
                                                        // Custom style for Migasit
                                                        /*style: {
                                                            backgroundColor: '#204A6D',
                                                        },*/
                                                        style : 'background-color: #204A6D;border-color: #204A6D;',
                                                    },
                                                    scrollable: true,
                                                    title: 'Serial Numbers',
                                                    layout: 'fit',
                                                    width: 600,
                                                    height: 600,
                                                    maxHeight: 2000,
                                                    modal: true,
                                                    //closeAction: 'destroy',
                                                    plain: true,
                                                    buttonAlign: 'center',
                                                    items: [
                                                       {   
                                                            title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                            header: {
                                                                style: {
                                                                    backgroundColor: 'white',
                                                                    display: 'inline-block',
                                                                    color: '#000000',
                                                                    
                                                                }
                                                            },
                                                            style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                            //title: 'Ask',
                                                            flex: 3,
                                                            scrollable: true,
                                                            margin: '0 10 0 0',
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    items: [{
                                                                        id: 'aceg4sserialnumbers',
                                                                    }]
                                                           
                                                                }
                                                            ]
                                                        },
                                                    ],
                                                    buttons: [{
                                                        text: 'OK',
                                                        handler: function(btn) {
                                                             
                                                            owningWindow = btn.up('window');
                                                            //owningWindow.closeAction='destroy';
                                                            owningWindow.close();
                                                        } 
                                                    },],
                                                    closeAction: 'destroy',
                                                    //items: spotpanelbuytotalxauweight
                                                });

                                                if(vmv.get('aceg4sserialnumbers').length != 0){
                                                    windowforserialnumberinventory.show();
                                                
                                            
                                                    element = vmv.get('element');
                                                    var panel = Ext.getCmp('aceg4sserialnumbers');
            
                                                    //date = data.createdon.date;
                                                    //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                    panel.removeAll();
                                                    vmv.get('aceg4sserialnumbers').map((x) => {
                                
                                                      panel.add(element.serialnoTemplateInventory(x))
                                                    })
                                                }else {
                                                    Ext.MessageBox.show({
                                                        title: 'Alert',
                                                        msg: 'No Serial Numbers are currently in G4S ACE',
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.WARNING,
                                                    });
                                                    Ext.getCmp('aceg4sserialnumbers').destroy();
                                                }

                                                
                                                
                                            
                                                
                                             }
        
                                             
                                        },
                                        dblclick: {
                                            element: 'body', //bind to the underlying body property on the panel
                                            fn: function(){ alert('dblclick body'); }
                                        }
                                    }
                                },

                            ]
                        },
                        {
                            layout: 'vbox',
                            width: '25%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#1aa124"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="vaultmbbg4scount">-</span><div style="color:#ffffff;font-size:1.3em;">G4S-MBB</div></div>',
                                    width: '100%',
                                    listeners : {
                                        click: {
                                            element: 'el', //bind to the underlying el property on the panel
                                            fn: function(){ 
                                                var windowforserialnumberinventory = new Ext.Window({
        
                                                    iconCls: 'x-fa fa-cube',
                                                    xtype: 'form',
                                                    header: {
                                                        // Custom style for Migasit
                                                        /*style: {
                                                            backgroundColor: '#204A6D',
                                                        },*/
                                                        style : 'background-color: #204A6D;border-color: #204A6D;',
                                                    },
                                                    scrollable: true,
                                                    title: 'Serial Numbers',
                                                    layout: 'fit',
                                                    width: 600,
                                                    height: 600,
                                                    maxHeight: 2000,
                                                    modal: true,
                                                    //closeAction: 'destroy',
                                                    plain: true,
                                                    buttonAlign: 'center',
                                                    items: [
                                                       {   
                                                            title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                            header: {
                                                                style: {
                                                                    backgroundColor: 'white',
                                                                    display: 'inline-block',
                                                                    color: '#000000',
                                                                    
                                                                }
                                                            },
                                                            style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                            //title: 'Ask',
                                                            flex: 3,
                                                            scrollable: true,
                                                            margin: '0 10 0 0',
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    items: [{
                                                                        id: 'mbbg4sserialnumbers',
                                                                    }]
                                                           
                                                                }
                                                            ]
                                                        },
                                                    ],
                                                    buttons: [{
                                                        text: 'OK',
                                                        handler: function(btn) {
                                                             
                                                            owningWindow = btn.up('window');
                                                            //owningWindow.closeAction='destroy';
                                                            owningWindow.close();
                                                        } 
                                                    },],
                                                    closeAction: 'destroy',
                                                    //items: spotpanelbuytotalxauweight
                                                });

                                                if(vmv.get('mbbg4sserialnumbers').length != 0){
                                                    windowforserialnumberinventory.show();
                                                
                                            
                                                    element = vmv.get('element');
                                                    var panel = Ext.getCmp('mbbg4sserialnumbers');
            
                                                    //date = data.createdon.date;
                                                    //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                    panel.removeAll();
                                                    vmv.get('mbbg4sserialnumbers').map((x) => {
                                
                                                      panel.add(element.serialnoTemplateInventory(x))
                                                    })
                                                }else {
                                                    Ext.MessageBox.show({
                                                        title: 'Alert',
                                                        msg: 'No Serial Numbers are currently in G4S MBB',
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.WARNING,
                                                    });
                                                    Ext.getCmp('mbbg4sserialnumbers').destroy();
                                                }

                                                
                                             }
        
                                             
                                        },
                                        dblclick: {
                                            element: 'body', //bind to the underlying body property on the panel
                                            fn: function(){ alert('dblclick body'); }
                                        }
                                    }
                                },

                            ]
                        },
                        {
                            layout: 'vbox',
                            width: '25%',
                            style: {
                                'margin': '5px 5px 0px 0px',
                            },
                            items: [
                                {
                                    html: '<div style="text-align: center;vertical-align: middle;line-height: 40px;background:#B71C1C"><span style="color:#ffffff;font-size:3em;font-weight:900;width:100%" id="vaulttotalcount">-</span><div style="color:#ffffff;font-size:1.3em;">TOTAL</div></div>',
                                    width: '100%',
                                    listeners : {
                                        click: {
                                            element: 'el', //bind to the underlying el property on the panel
                                            fn: function(){ 
                                                var windowforserialnumberinventory = new Ext.Window({
        
                                                    iconCls: 'x-fa fa-cube',
                                                    xtype: 'form',
                                                    header: {
                                                        // Custom style for Migasit
                                                        /*style: {
                                                            backgroundColor: '#204A6D',
                                                        },*/
                                                        style : 'background-color: #204A6D;border-color: #204A6D;',
                                                    },
                                                    scrollable: true,
                                                    title: 'Serial Numbers',
                                                    layout: 'fit',
                                                    width: 600,
                                                    height: 600,
                                                    maxHeight: 2000,
                                                    modal: true,
                                                    //closeAction: 'destroy',
                                                    plain: true,
                                                    buttonAlign: 'center',
                                                    items: [
                                                       {   
                                                            title: '<h1 style="font: 900 20px/30px Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif;color:#404040;">Serial Numbers</h1>',
                                                            header: {
                                                                style: {
                                                                    backgroundColor: 'white',
                                                                    display: 'inline-block',
                                                                    color: '#000000',
                                                                    
                                                                }
                                                            },
                                                            style: 'opacity: 1.0;background: linear-gradient(269deg, #b08f26 0%, #d4af37 100%);color: #000000;box-sizing: border-box;font-weight: bold;text-transform: uppercase;letter-spacing: 1px;',
                                                            //title: 'Ask',
                                                            flex: 3,
                                                            scrollable: true,
                                                            margin: '0 10 0 0',
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    items: [{
                                                                        id: 'totalserialnumbers',
                                                                    }]
                                                           
                                                                }
                                                            ]
                                                        },
                                                    ],
                                                    buttons: [{
                                                        text: 'OK',
                                                        handler: function(btn) {
                                                             
                                                            owningWindow = btn.up('window');
                                                            //owningWindow.closeAction='destroy';
                                                            owningWindow.close();
                                                        } 
                                                    },],
                                                    closeAction: 'destroy',
                                                    //items: spotpanelbuytotalxauweight
                                                });

                                                if(vmv.get('totalserialnumbers').length != 0){
                                                    windowforserialnumberinventory.show();
                                                
                                            
                                                    element = vmv.get('element');
                                                    var panel = Ext.getCmp('totalserialnumbers');
            
                                                    //date = data.createdon.date;
                                                    //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
                                                    panel.removeAll();
                                                    vmv.get('totalserialnumbers').map((x) => {
                                
                                                      panel.add(element.serialnoTemplateInventory(x))
                                                    })
                                                }else {
                                                    Ext.MessageBox.show({
                                                        title: 'Alert',
                                                        msg: 'No Serial Numbers to add to total',
                                                        buttons: Ext.MessageBox.OK,
                                                        icon: Ext.MessageBox.WARNING,
                                                    });
                                                    Ext.getCmp('totalserialnumbers').destroy();
                                                }
                                                
                                             }
        
                                             
                                        },
                                        dblclick: {
                                            element: 'body', //bind to the underlying body property on the panel
                                            fn: function(){ alert('dblclick body'); }
                                        }
                                    }
                                },

                            ]
                        },
                    ]
                },]
            },]

        },
        {
            title: 'Vault',
            region: 'center',
            collapsible: true,
            margin: '5 0 0 0',
            xtype: 'vaultitemview'
        }
    ]
});


serialnoTemplateWithDO = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: '100%',
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
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
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'donumber', value: data.deliveryordernumber, reference: 'deliveryorderno', fieldLabel: 'Delivery Order Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}

serialnoTemplateWithoutDO = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
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
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}

transferringserialnumbers = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
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
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                        {
                            xtype: 'displayfield', name:'fromlocation', value: data.from, reference: 'from', fieldLabel: 'From', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                        },
                        {
                        xtype: 'displayfield', name:'tolocation', value: data.to, reference: 'to', fieldLabel: 'To', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                        },
                    ]
                },
              ]
    },],


  }

  return returnx
}

serialnoTemplateInventory = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
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
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}