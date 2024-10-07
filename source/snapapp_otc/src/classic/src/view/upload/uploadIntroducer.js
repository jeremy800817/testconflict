Ext.define('snap.view.upload.uploadIntroducer', {
    extend: 'Ext.form.Panel',
    xtype: 'uploadIntroducerview',
    title: 'Form Upload',

    requires: [
        'snap.view.upload.UploadController' // Add the required controller
      ],
      controller: 'upload', // Use the new controller alias
    
    header: {
      titlePosition: 0,
      items: [{
        xtype: 'button',
        text: 'Add File',
        iconCls: 'x-fa fa-upload',
        handler: 'onUploadClick',
        ui: 'default',
        style: 'background-color: grey; color: #ffffff;'
      }]
    },
  
    layout: 'center', // Center align the form within the panel
   // Add padding to the body section
    items: [{
      xtype: 'htmleditor',
      labelAlign: 'top',
      hidden: true,
      flex: 1
    }],

    


    
  
  
  });
  