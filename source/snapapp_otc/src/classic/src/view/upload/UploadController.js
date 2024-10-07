Ext.define('snap.view.upload.UploadController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.upload',
  
    onUploadClick: function () {
      var uploadWindow = Ext.create('Ext.window.Window', {
        title: 'Upload File',
        modal: true,
        layout: 'fit',
        items: [{
          xtype: 'form',
          bodyPadding: 10,
          enctype: 'multipart/form-data',
          items: [{
            xtype: 'filefield',
            name: 'file',
            fieldLabel: 'Select File',
            labelWidth: 80,
            allowBlank: false,
            buttonText: 'Browse...',
            validator: function (value) {
              // Custom file validation logic
              var fileExtension = value.split('.').pop().toLowerCase();
              if (fileExtension !== 'txt') {
             
                Ext.Msg.alert('Error', 'Only text files (.txt) are allowed.', function () {
                  this.up('form').getForm().findField('file').reset();
                });
                
                return false;
                
              }
              return true;
            }
          }],
          buttons: [{
            text: 'Upload',
            handler: function () {
              var form = this.up('form').getForm();
              if (form.isValid()) {
                form.submit({
                  url: 'index.php?hdl=uploadfilehandler&action=onFileUpload', // Replace with the appropriate server-side URL for file upload
                  waitMsg: 'Uploading file...', // Loading message while the file is being uploaded
                  success: function (form, action) {
                    Ext.Msg.alert('Success', 'File uploaded successfully!');
                    uploadWindow.close();
                    var htmlEditor = Ext.create('Ext.form.field.HtmlEditor', {
                      labelAlign: 'top',
                      flex: 1
                    });
                    form.owner('window').add(htmlEditor);
                  
                  },
                  failure: function (form, action) {
                    Ext.Msg.alert('Error', 'File upload failed.');
                  }
                });
              }
            }
          }, {
            text: 'Cancel',
            handler: function () {
              uploadWindow.close();
            }
          }]
        }]
      });
  
      uploadWindow.show();
    },

    onFormRendered:function () {

    }
  
    // Other methods specific to file upload functionality
  });
  