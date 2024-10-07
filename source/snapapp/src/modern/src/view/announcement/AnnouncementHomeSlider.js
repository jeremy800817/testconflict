Ext.define('snap.view.home.AnnouncementHomeSlider', {
    extend: 'Ext.Carousel',
    xtype: 'announcementhomesliderview',
    fullscreen: true,
    items: [
        // {
        //     xtype: 'image',
        //     // html : 'Item 1',
        //     // style: 'background-color: #5E99CC'
        //     src: 'src/resources/images/mobilebanner/mobile_1_en.png',
        // },
        // {
        //     xtype: 'image',
        //     src: 'src/resources/images/mobilebanner/mobile_2_en.png',
        // },
        // {
        //     xtype: 'image',
        //     src: 'src/resources/images/mobilebanner/mobile_1_cn.png',
        // },
        // {
        //     xtype: 'image',
        //     src: 'src/resources/images/mobilebanner/mobile_2_cn.png',
        // },
        // {
        //     xtype: 'image',
        //     src: 'src/resources/images/mobilebanner/mobile_1_bm.png',
        // },
        // {
        //     xtype: 'image',
        //     src: 'src/resources/images/mobilebanner/mobile_2_bm.png',
        // }
    ],
    // listeners: {
    //     afterrender: function () {
    //         elmnt = this;
    //         debugger;
    //         var panel = Ext.getCmp('dailylimitformdisplay');
    //         snap.getApplication().sendRequest({
    //             hdl: 'announcement', action: 'getSlidersMobile'
    //         }, 'Fetching data from server....').then(
    //             function (data) {
    //                 if (data.success) {
                      
    //                     panel.removeAll();
    //                     panel.add(
    //                         {
    //                             xtype: 'image',
    //                             // html : 'Item 1',
    //                             // style: 'background-color: #5E99CC'
    //                             src: 'index.php?hdl=announcement&action=viewpicture&pid=10&aid=12',
    //                         },
                          
    //                     );

    //                 }
    //             })
    //     }
    // },
});