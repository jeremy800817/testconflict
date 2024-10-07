Ext.define('snap.view.user.SuperAdmin_BSN', {
    extend:'Ext.panel.Panel',
    xtype: 'superadminview_BSN',
    permissionRoot: '/root/system/user',
    
    
    requires: [
        'snap.store.User',
        'snap.model.User',
        'snap.view.user.UserController',
        'snap.view.user.UserModel',
    ],

    controller: 'user-user',
    viewModel: {
        type: 'user-user'
    },

    scrollable:true,
    items: {
        
        //width: 500,
        //height: 400,
        cls: Ext.baseCSSPrefix + 'shadow',
    
        layout: {
            type: 'vbox',
            pack: 'start',
            align: 'stretch'
        },
        
        bodyPadding: 10,
    
        defaults: {
            frame: true,
            //bodyPadding: 10
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
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
                        flex: 4,
                        margin: '0 10 0 0',
                        items: [ {
                            xtype: 'searchfield',
                            text: 'Search',
                            emptyText: 'MyKad Number',
                            flex:1,
                            style: 'text-align:center;',
                            width: '90%',
                        //     listeners: {
                        //         'change' : function(field, value, oldvalue, eOpts) {
                        //             alert('a');
                        //              this.store.load({params:{id: 1,search: value}});
                        //         },
                        //         onAfter : function(eventName, fn, scope, options) {
                        //             alert('aa');
                        //              this.store.load({params:{id: 1,search: value}});
                        //         },
                        //         scope:this,
                        //    }
                        }]
                    },

                    { 
                        flex:1,
                        xtype:'combobox',
                        cls:'combo_box',
                        store: {
                            fields: ['abbr', 'name'],
                            data : [
                                {"abbr":"", "name":""},
                                {"abbr":"ICNO", "name":"Identity Card Number"},
                                {"abbr":"ACCNO", "name":"Account Number"},
                                {"abbr":"CRNO", "name":"Company Registration Number"}
                                
                            ]
                        },
                        queryMode: 'local',
                        displayField: 'name',
                        valueField: 'abbr',
                        forceSelection: true,
                        editable: false,
                        margin: "0 10 0 10",
                    },
          
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
                        cls:'search_btn',
                        handler:'',
                        margin: "0 0 0 10",
                    },
              
               
                ]

            },
            {
                xtype: 'panel',
                title: 'User',
                layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                scrollable:true,
                items: [
                    {
                        xtype: 'otcsuperadminview', 
                        // minHeight: 300,
                        // maxHeight: 1000,
                        //height: Ext.getBody().getViewSize().height * 70/100,
                    }
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    }
});

