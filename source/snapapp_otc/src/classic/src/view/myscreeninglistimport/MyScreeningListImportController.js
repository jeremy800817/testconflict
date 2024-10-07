Ext.define('snap.view.myscreeninglistimport.MyScreeningListImportController', {
  extend: 'snap.view.gridpanel.BaseController',
  alias: 'controller.myscreeninglistimport-myscreeninglistimport',

  getUrlAction: function (elemnt) {
    var gridFormView = this.getView();
    var form = elemnt.lookupController().lookupReference('importForm').getForm();
    gtpscreening = form.getFieldValues();

    sourcetype = gtpscreening.sourcetype;
    url = gtpscreening.url;
    if (form.isValid()) {
      if (gtpscreening.sourcetype != null) {
        var mask = Ext.getBody().mask('Loading...');
        mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);

        var url = 'index.php?hdl=myscreeninglistimport&action=getUrlForAmla&sourcetype=' + sourcetype + '&url=' + url;
        Ext.Ajax.request({
          url: url,
          method: 'get',
          waitMsg: 'Processing',
          autoAbort: false,
          success: function (result) {
            response = JSON.parse(result.responseText)
            if (true == response.success) {
              Ext.MessageBox.show({
                title: 'Acquiring records from URL',
                msg: 'Fetch Successfully',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.INFO
              });

              gridFormView.close();
              gridFormView.myView.getStore().reload();
            
          } else {
              Ext.MessageBox.show({
                title: 'Error',
                msg: response.errorMessage,
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
              });
            }
            
            gridFormView.myView.getController().gridFormView = null;
            gridFormView = null;

            Ext.getBody().unmask();

          },
          failure: function (err) {

            Ext.MessageBox.show({
              title: 'Error',
              msg: err.responseText,
              buttons: Ext.MessageBox.OK,
              icon: Ext.MessageBox.ERROR
            });
            Ext.getBody().unmask();

          }
        });
      } else {
        Ext.MessageBox.show({
          title: "Error",
          msg: "Unable to fetch request",
          buttons: Ext.MessageBox.OK,
          icon: Ext.MessageBox.WARNING
        });
      }

    } else {
      Ext.MessageBox.show({
        title: "Error",
        msg: "Please fill the required fields correctly.",
        buttons: Ext.MessageBox.OK,
        icon: Ext.MessageBox.WARNING
      });
    }
  },

  importForm: function (elemet) {
    var myView = this.getView();
    var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.formImport ? myView.formImport : {}, {}));
    this.gridFormView = gridFormView;
    this.gridFormView.myView = myView;
    this._formAction = "edit";
    this.gridFormView.show();
  }

});
