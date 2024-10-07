Ext.define('snap.profile.Tablet', {
    extend: 'Ext.app.Profile',

    requires: [      
    ],

    // Map tablet/desktop profile views to generic xtype aliases:
    //
    views: {       
    },

    isActive: function () {
        return !Ext.platformTags.phone;
    }
});
