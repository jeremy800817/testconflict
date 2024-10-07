Ext.define('snap.view.home.AboutUs', {
    extend: 'Ext.panel.Panel',
    xtype: 'aboutusview',

    requires: [
        'Ext.container.Container',
        //'Ext.ux.GMapPanel',
        //'Ext.ux.google.Map',
    ],
    title: 'About Us',
    anchor: '100% -1',

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
    scrollable: true,
    bodyPadding: 10,

    defaults: {
        frame: true,
        bodyPadding: 10
    },
    userCls: 'transactionlisting-head',
    items: [
        // {
        //     // Style for migasit default
        //     style: {
        //         'border': '2px solid #204A6D',
        //     },
        //     height: 60,
        //     margin: '0 0 0 0',
        //     items: [{
        //         xtype: 'container',
        //         scrollable: false,
        //         layout: 'hbox',
        //         defaults: {
        //             bodyPadding: '5',
        //         },
        //         items: [{
        //             html: '<h1>About Us</h1>',
        //             style:{
        //                 'padding-left':'5px'
        //             }
        //         }]
        //     },]
        // },
        {
            xtype: 'displayfield',
            width: '99%',
            padding: '0 1 0 1',
            value: '<iframe allowfullscreen="" frameborder="0" height="225" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1772.1874508946128!2d101.58341806201452!3d3.0476830215350645!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc4cc4f4441b67%3A0x107e988b4ecf7b54!2sAce+Capital+Growth+Sdn+Bhd!5e0!3m2!1sen!2smy!4v1482508800152" style="border:0" width="100%"></iframe>',
            renderer: function (html) {
                this.setHtml(html)
            }
        },
        {
            xtype: 'displayfield',
            width: '99%',
            padding: '0 1 0 1',
            value: '<p><span style="font-size:16px;"><strong>Ace Capital Growth Sdn.Bhd.</strong> (880690-K)</span></p>' +
                '<p><span style="font-size:16px;">No 19-1, Jalan USJ 10/1D,<br> 47620 Subang Jaya,<br> Selangor, Malaysia</span></p>' +
                '<p><span style="font-size:16px;">Email: enquiry@ace2u.com<br> Tel: +603-8081 7198<br> Fax: +603-8081 7199</span></p>',
            renderer: function (html) {
                this.setHtml(html)
            }
        },

    ]
});
