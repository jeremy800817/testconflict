Ext.define('snap.view.mycif.MyCif',{
  extend: 'snap.view.mycif.MyCifBase',
  xtype: 'mycifview',
  alias: 'mycifview',
  initComponent: function() {
      this.callParent(arguments);
  },
  
  formEkycReminder: {
      formDialogWidth: 950,
      controller: 'myaccountholder-myaccountholder',

      formDialogTitle: 'EKYC Approval',

      // Settings
      enableFormDialogClosable: false,
      formPanelDefaults: {
          border: false,
          xtype: 'panel',
          flex: 1,
          layout: 'anchor',
          msgTarget: 'side',
          margins: '0 0 10 10'
      },
      enableFormPanelFrame: false,
      formPanelLayout: 'hbox',
      formViewModel: {

      },

      formPanelItems: [
          //1st hbox
          {
              items: [
                  { xtype: 'hidden', hidden: true, name: 'id' },
                  {
                      itemId: 'user_main_fieldset',
                      xtype: 'fieldset',
                      title: 'Main Information',
                      title: 'Account Holder Details',
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
                              fieldLabel: '',
                              defaultType: 'textboxfield',
                              layout: 'hbox',
                              items: [
                                  {
                                      xtype: 'displayfield', allowBlank: false, fieldLabel: 'Name', reference: 'accountholderkycname', name: 'accountholderkycname', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                  },
                                  {
                                      xtype: 'displayfield', allowBlank: false, fieldLabel: 'Total Record Count', reference: 'accountholderkycremindercount', name: 'accountholderkycremindercount', flex: 5, style: 'padding-left: 20px;', labelWidth: '10%',
                                  },
                              ]
                          }
                      ]
                  },
                  {
                      xtype: 'form',
                      reference: 'mykycresults-form',
                      border: false,
                      items: [
                          {
                              title: '',
                              flex: 13,
                              xtype: 'mykycreminderview',
                              reference: 'mykycreminder',
                              enablePagination: true,
                              store: {
                                  proxy: {
                                      type: 'ajax',
                                      url: '',
                                      reader: {
                                          type: 'json',
                                          rootProperty: 'records',
                                      }
                                  },
                              },
                          

                          },
                      ],
                  },
                  // {
                  //     xtype: 'fieldset', title: 'Remarks', collapsible: false,
                  //     items: [
                  //         {
                  //             xtype: 'fieldcontainer',
                  //             layout: {
                  //                 type: 'hbox',
                  //             },
                  //             items: [
                  //                 {
                  //                     xtype: 'textarea', fieldLabel: '', name: 'remarks', flex: 2, style: 'padding-left: 20px;', id: 'pepremarks'
                  //                 },
                  //             ]
                  //         },
                  //     ]
                  // }
              ],
          },
      ],

    
  },
});
