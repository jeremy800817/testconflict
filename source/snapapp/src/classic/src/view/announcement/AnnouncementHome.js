Ext.define('snap.view.home.Announcement', {
    extend: 'Ext.panel.Panel',
    xtype: 'announcementhomeview',
    controller: 'announcement-announcement',

    store: { type: 'AnnouncementBanner' },
    //anchor : '100% -1',
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
    //scrollable:true,
    bodyPadding: 10,

    defaults: {
        frame: true,
        bodyPadding: 10
    },
    items: [
                /*{
                    // Style for migasit default
                    style: {
                    borderColor: '#204A6D',
                    },
                    
                    height: 80,
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
                            html: '<h1>Announcements</h1>',
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
                    // xtype: 'announcementslider',
                    // xtype: 'panel',
                    // height: '70vh',
                    xtype: 'container',
                    layout: "fit",
                    cls: 'trader-container',
                    height: 1000,

                    reference: 'sliderhtml',
                    // html : function(){
                    //     return '<script src="./js/jquery-3.6.0.js"></script>'
                    // }()
                    
                },
    ]
    });