Ext.define('snap.view.logistic.OTCLogistic', {
    extend:'Ext.panel.Panel',
    xtype: 'otclogisticview',
    permissionRoot: '/root/' + PROJECTBASE.toLowerCase() + '/logistic',
    
    layout: 'fit',
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
                title: 'Logistic',
                collapsible: true,
                layout: 'vbox',
                flex: 1,
                margin: "10 0 0 0",
                items: [
                        // ITEM 1
                        { 
                            xtype: 'mylogisticview',
                            controller: 'logistic-logistic',
                            viewModel: {
                                type: 'logistic-logistic'
                            },  
                            partnerCode: PROJECTBASE,
                            scrollable:true, 
                            store: {
                                type: 'MyLogistic', proxy: {
                                    type: 'ajax',
                                    url: 'index.php?hdl=mylogistic&action=list&partnercode='+PROJECTBASE,
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'records',
                                    }
                                },
                            },
                            toolbarItems: [
                                'detail', '|', 'filter',
                            ], 
                        },
                        // END ITEM 1
                  ]
      
            },
            // End test
            // Conversion container
           
        ]
    }
});
