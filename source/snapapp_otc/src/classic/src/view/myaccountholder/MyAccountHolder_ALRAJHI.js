Ext.define('snap.view.myaccountholder.MyAccountHolder_ALRAJHI', {
    extend:'Ext.panel.Panel',
    xtype: 'myaccountholderview_ALRAJHI',
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/profile',
    
    // requires: [
    //     'snap.store.MyAccountHolder',
    //     'snap.model.MyAccountHolder',
    //     'snap.view.myaccountholder.MyAccountHolderController',
    //     'snap.view.myaccountholder.MyAccountHolderModel',
    // ],

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
        scrollable:true,
        bodyPadding: 10,
    
        defaults: {
            frame: true,
            //bodyPadding: 10
        },
        cls: 'otc-main',
        bodyCls: 'otc-main-body',
        items: [
          
            {
                xtype: 'panel',
                title: 'Register Report',
                layout: 'hbox',
                collapsible: true,
                // cls: 'otc-panel',
                defaults: {
                  layout: 'vbox',
                  flex: 1,
                  bodyPadding: 10
                },
                margin: "10 0 0 0",
                items: [
                        // ITEM 1
                        { 
                            xtype: 'myaccountholderview', partnercode: PROJECTBASE,
                            controller: 'myaccountholder-myaccountholder',
                            viewModel: {
                                type: 'myaccountholder-myaccountholder'
                            },  
                            partnerCode: PROJECTBASE,
                            store: {
                                type: 'MyAccountHolder',
                                proxy: {
                                    type: 'ajax',
                                    url: 'index.php?hdl=myaccountholder&action=list&partnercode='+PROJECTBASE,
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'records',
                                    }
                                },
                            },
                        },
                        // END ITEM 1
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    }
});
