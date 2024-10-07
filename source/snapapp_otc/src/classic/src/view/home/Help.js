Ext.define('snap.view.home.Help', {
    extend: 'Ext.panel.Panel',
    xtype: 'helpview',
    controller: 'help-help',

    requires: [
        'Ext.container.Container'
    ],

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

    // defaults: {
    //     frame: true,
    //     bodyPadding: 10
    // },
    items: [
        /*{
            // Style for migasit default
            style: {
            borderColor: '#204A6D',
            },
            
            height: 120,
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
                    html: '<h1>Terms and Conditions</h1>',
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
        // {
        //     xtype: 'displayfield',
        //     width: '100%',
        //     padding: '0 10 0 10',
        //     margin: '-8px 18 10 10',

        //     // #757575
        //     value: "<h5 style=' width:100%;line-height: normal;overflow: inherit; margin:0px 0 20px; font-size: 16px;color:#24333f;'><span style='background:#fff;position: relative;top: 10px;'>Welcome to our site. We maintain this web site as a service to our members. By using our site, you are agreeing to comply with and be bound by the following terms of use. Please review the following terms carefully. If you do not agree to these terms, you should not use this site. </span></h5>",
        //     //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",

        // },
        /*{
            xtype : 'displayfield',
            width : '99%',
            padding: '30 1 0 1',
            value: '',
            //value: "<h5 style=' width:100%;line-height: normal;overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'><span style='background:#fff;position: relative;top: 10px;'>Welcome to our site. We maintain this web site as a service to our members. By using our site, you are agreeing to comply with and be bound by the following terms of use. Please review the following terms carefully. If you do not agree to these terms, you should not use this site. </span></h5>",
            //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
            
        },*/
        // {
        //     xtype: 'displayfield',
        //     width: '100%',
        //     padding: '0 10 0 10',
        //     //#0099ff
        //     value: "<ol style='padding: 0px; margin: 0px 0px 10px 25px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 20px; background-color: rgb(255, 255, 255);'>" +
        //         "<li style='line-height: 27px;'><span style='color:#24333f;'><span style='font-size:16px;'><strong>You may download <a href='src/resources/downloadables/GTP_UserGuide_v3.pdf' download>here</a> for our User Guide &nbsp;</strong>.</span></span><br> <br> &nbsp;</li>" +
        //         // "<li style='line-height: 27px;'><span style='font-size:16px;'><span style='color:#24333f;'><strong>Copyright</strong>.</span></span><br> The content, organization, graphics, design, compilation, magnetic translation, digital conversion and other matters related to the Site are protected under applicable copyrights, trademarks and other proprietary (including but not limited to intellectual property) rights. The copying, redistribution, use or publication by you of any such matters or any part of the Site, except as allowed by Section 4, is strictly prohibited. You do not acquire ownership rights to any content, document or other materials viewed through the Site. The posting of information or materials on the Site does not constitute a waiver of any right in such information and materials.&nbsp;<br> &nbsp;</li>" +
        //         // "<li style='line-height: 27px;'><span style='color:#24333f;'><span style='font-size:16px;'><strong>Service Marks</strong>.</span></span><br> Products and names mentioned on the Site may be trademarks of their respective owners.&nbsp;<br> &nbsp;</li>" +
        //         // "<li style='line-height: 27px;'><span style='color:#24333f;'><span style='font-size:16px;'><strong>Limited Right to Use</strong>.</span></span><br> The viewing, printing or downloading of any content, graphic, form or document from the Site grants you only a limited, nonexclusive license for use solely by you for your own personal use and not for republication, distribution, assignment, sublicense, sale, preparation of derivative works or other use. No part of any content, form or document may be reproduced in any form or incorporated into any information retrieval system, electronic or mechanical, other than for your personal use (but not for resale or redistribution).&nbsp;<br> &nbsp;</li>" +
        //         // "<li style='line-height: 27px;'><span style='font-size:16px;'><span style='color:#24333f;'><strong>Editing, Deleting and Modification</strong>.</span></span><br> We reserve the right in our sole discretion to edit or delete any documents, information or other content appearing on the Site.&nbsp;<br> &nbsp;</li>" +
        //         // "<li style='line-height: 27px;'><span style='color:#24333f;'><span style='font-size:16px;'><strong>Indemnification</strong>.</span></span><br> You agree to indemnify, defend and hold us and our partners, attorneys, staff, advertisers, product and service providers, and affiliates (collectively, " + '"Affiliated Parties"' + ") harmless from any liability, loss, claim and expense, including reasonable attorney's fees, related to your violation of this Agreement or use of the Site.&nbsp;<br> &nbsp;</li>" +
            
        //         "</ol>",
        //     //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",

        // },
        {
            // xtype: 'announcementslider',
            // xtype: 'panel',
            // height: '70vh',
            xtype: 'container',
            layout: "fit",
            cls: 'trader-container',
            // height: 1000,

            reference: 'sliderhtml',
            // html : function(){
            //     return '<script src="./js/jquery-3.6.0.js"></script>'
            // }()
            
        },
    ]
});
