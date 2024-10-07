//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.AnnouncementBanner', {
    extend: 'snap.store.Announcement',
    // autoLoad: true,

    model: 'snap.model.Announcement',
    alias: 'store.AnnouncementBanner',
    autoLoad: true,
   
    storeId:'announcementbanner', 
    proxy: {
        type: 'ajax',	       	
        url: 'index.php?hdl=announcement&action=getRecordDisplay',		
        reader: {
            type: 'json',
            rootProperty: 'records',  
        }            
    },
    sorters: [{
        property: 'rank',
        direction: 'ASC'
    },],
    autoLoad: true,
    listeners:{
        /*load : function(store, records, success, opts) {
           debugger;
        }, */
        datachanged : function(store, eOpts){
             //debugger;
            //store.data.items[0].data.imgSrc = "https://image.shutterstock.com/image-photo/bright-spring-view-cameo-island-260nw-1048185397.jpg"
            //store.data.items[1].data.imgSrc = "https://upload.wikimedia.org/wikipedia/commons/thumb/b/b6/Image_created_with_a_mobile_phone.png/1200px-Image_created_with_a_mobile_phone.png"
           
        }
    }
});



