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
    listeners: {
        painted: function(){
            
          
            elmnt = this;

            // // Ui height
            // var height = Ext.getBody().getViewSize().height;

            // var cardHeight = height * 0.8;

            // var boxHeight = height * 0.2;
            //elmnt.lookupReference('mobile-banner').setHeight(boxHeight);
            panel = elmnt.lookupReference('mobile-banner');
       
            // panel.removeAll();
            snap.getApplication().sendRequest({
                hdl: 'announcement', action: 'getSlidersMobile'
            }, false).then(
                function (data) {
                    if (data.success) {
                        // alert(data.success);
                        data.return.data.forEach(record => imgSrc = record.imgSrc);
                        panel.removeAll();
                        for( var key in data.return.data) {
                            var imgSrc = data.return.data[key].imgSrc;
                            panel.add(
                                {
                                    xtype: 'image',
                                    // html : 'Item 1',
                                    // style: 'background-color: #5E99CC'
                                    src: imgSrc,
                                },
                              
                            );
                          }
                       

                    }
                })
        }
    },
    items: [
                {
                    xtype: 'announcementhomesliderview',
                    // xtype: 'panel',
                    // height: '70vh',
                    
                    // cls: 'trader-container',
                    userCls: 'mobile-banner',
                    // height: cardHeight,
                    reference: 'mobile-banner',
                    

                   // src: 'src/resources/images/mobilebanner/wallpaper.jpeg',
                },

    ]
    });