Ext.define('snap.view.home.AnnouncementSlider', {
    extend: 'snap.view.home.AnnouncementSliderCarousel',
    xtype: 'announcementslider',

    requires: [
        'Ext.layout.container.Card',
        'Ext.tab.Panel'
    ],

    alias: 'widget.mycarousel',
    template: '<img src="{imgSrc}" alt="{title}"  />',
    id: 'announcementslider',
    store: { type: 'AnnouncementBanner', listeners : {
        load : function(store, records, success, opts) {
            Ext.getCmp('announcementslider').initializeView()
        
        }, 
    }},
    interval: 10000,
    direction: 'left',
    loop: true,
    buttons: false,
    puaseOnHover: true,
    width: 1800,
    height: 750,
    
});