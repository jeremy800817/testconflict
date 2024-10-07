Ext.define('snap.view.priceadjuster.PriceAdjusterOTC', {
    extend: 'Ext.panel.Panel',
    xtype: 'priceadjusterotcview',
    requires: [
        'Ext.layout.container.Border'
    ],
    profiles: {
        classic: {
            itemHeight: 100
        },
        neptune: {
            itemHeight: 100
        },
        graphite: {
            itemHeight: 120
        },
        'classic-material': {
            itemHeight: 120
        }
    },
    layout: 'border',
    width: 500,
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',

    bodyBorder: false,

    defaults: {
        collapsible: true,
        split: true,
        bodyPadding: 10
    },
    viewModel: {
        data: {
            withdoserialnumbers: [],
            withoutdoserialnumbers: [],
            transferringserialnumbers: [],
            permissions : [],
            acehqserialnumbers: [],
            aceg4sserialnumbers: [],
            mbbg4sserialnumbers: [],
            totalserialnumbers: [],
            status: '',

        }
    },
    type: PROJECTBASE.toLowerCase(),
    partnerCode: PROJECTBASE,
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
    //id: 'bmmbvaultitem',
    listeners: {
        afterrender: function () {
            // var originType = this.up().type;
            var adjusterPermission = '/root/system/priceadjuster/list';
            var providerPermission = '/root/system/priceprovider/list';
            var streamPermission = '/root/system/pricestream/list';
            var validationpermission = '/root/system/pricevalidation/list';

            // get panel 
            var panel = this;
            panel.removeAll();

            // Check for type 
            if ("HQ" == snap.getApplication().usertype || "Regional" == snap.getApplication().usertype  || "Branch" == snap.getApplication().usertype ){
                        
                 // Do permission check for said user roles
                 var hasAdjusterPermission = snap.getApplication().hasPermission(adjusterPermission);
                 if(hasAdjusterPermission == true){
                     panel.add({
                         title: 'Price Adjuster',
                         // region: 'center',
                         collapsible: true,
                         margin: '10 0 0 0',
                         xtype: 'priceadjusterview',
                         reference: 'priceadjuster',
                         maxHeight: 600,
                     },);
                 }
                 
                // Do permission check for said user roles
                var hasProviderPermission = snap.getApplication().hasPermission(providerPermission);
                if(hasProviderPermission == true){
                    panel.add({
                        title: 'Price Provider',
                        // region: 'center',
                        collapsible: true,
                        margin: '10 0 0 0',
                        xtype: 'pricedelayview',
                        reference: 'pricedelay',
                        maxHeight: 600,
                    },);
                }

                var hasStreamPermission = snap.getApplication().hasPermission(streamPermission);
                if(hasStreamPermission == true){
                    panel.add( {
                        title: 'Price Stream',
                        // region: 'center',
                        collapsible: true,
                        margin: '10 0 0 0',
                        xtype: 'pricestreamview',
                        reference: 'pricestream',
                        maxHeight: 600,
                        // store: {
                        //     type: 'MyVaultItem', proxy: {
                        //         type: 'ajax',
                        //         url: 'index.php?hdl=myvaultitem&action=list&partnercode='+partnerCode,
                        //         reader: {
                        //             type: 'json',
                        //             rootProperty: 'records',
                        //         }
                        //     },
                        // }
                    },);
                }

                // Do permission check for said user roles
                var hasValidationpermission = snap.getApplication().hasPermission(validationpermission);
                if(hasValidationpermission == true){
                    panel.add({
                        title: 'Price Provider',
                        // region: 'center',
                        collapsible: true,
                        margin: '10 0 0 0',
                        xtype: 'pricedelayview',
                        reference: 'pricedelay',
                        maxHeight: 600,
                    },);
                }
                
            } 
            
        }
    },

    items: [
       
       
       
    ]
        
});


serialnoTemplateWithDO = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: '100%',
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'donumber', value: data.deliveryordernumber, reference: 'deliveryorderno', fieldLabel: 'Delivery Order Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}

serialnoTemplateWithoutDO = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}

transferringserialnumbers = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                        {
                            xtype: 'displayfield', name:'fromlocation', value: data.from, reference: 'from', fieldLabel: 'From', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                        },
                        {
                        xtype: 'displayfield', name:'tolocation', value: data.to, reference: 'to', fieldLabel: 'To', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                        },
                    ]
                },
              ]
    },],


  }

  return returnx
}

serialnoTemplateInventory = (data) =>
{
  var returnx = {

      xtype: 'container',
      height: 200,
      //fieldStyle: 'background-color: #000000; background-image: none;',
      //scrollable: true,
      items: [{
        itemId: 'user_main_fieldset',
        xtype: 'fieldset',
        title: data.name,
        layout: 'hbox',
        defaultType: 'textfield',
        fieldDefaults: {
            anchor: '100%',
            msgTarget: 'side',
            margin: '0 0 5 0',
            width: '100%',
        },
        items: [
                  {
                    xtype: 'fieldcontainer',
                    layout: 'vbox',
                    flex: 2,
                    items: [
                      {
                        xtype: 'displayfield', name:'serialnumber', value: data.name, reference: 'serialno', fieldLabel: 'Serial Number', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #ffffff ",
                      },
                      {
                        xtype: 'displayfield', name:'allocatedon', value: data.allocatedon, reference: 'allocatedon', fieldLabel: 'Allocated On', flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle:" background-color: #fff ",
                      },
                    ]
                },
              ]
    },],


  }

  return returnx
}