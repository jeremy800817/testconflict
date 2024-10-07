Ext.define('snap.view.mycif.MyCifFormBase', {
    extend: 'Ext.container.Container',

    requires: [
        'snap.view.mycif.MyCifBaseController',
    ],

    controller: 'mycif-mycifbase',

    medicalDateTitle: 'Date',
    myCifFormTitle: 'Date',

    myCifFormSaveHandler: '',
    myCifFormSaveAction: '',

    items: [{
        layout: {
            type: 'hbox',
            align: 'stretch'
        },
        items: [
            {
                xtype: 'container',
                margin: '0 0 0 5',
                width: 150,
                items: [{
                    itemId: 'faceImage',
                    xtype: 'displayfield',
                    value: '<div style="width: 150px;height: 190px;background: #ececec;"></div>'
                }]
            },
            {
                margin: '0 0 0 5',
                layout: 'anchor',
                width: '80%',
                itemId: 'myCifForm',
                xtype: 'form',
                bodyPadding: 10,
                scrollable: true,
            }
        ]
    }],

    listeners: {
        afterrender: 'setMyCifConfig'
    },

    initComponent: function () {
        this.callParent(arguments);
    }
});
