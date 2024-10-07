Ext.define('snap.view.mycif.MyCifBaseController', {
  extend: 'Ext.app.ViewController',
  alias: 'controller.mycif-mycifbase',

  setAccountHolderId: function (accountHolderId) {
    var view = this.getView();
    view.accountHolderId = accountHolderId;
    this.getMyCifData(accountHolderId);
  },

  getMyCifData: function (accountHolderId) {
    var view = this.getView(),
      me = this;

    // Reset
    var myPic = view.down('#myprofile').down('#faceImage');
    myPic.setValue('<div style="width: 150px;height: 190px;background: #ececec;"></div>');

    var myPic = view.down('#mykyc').down('#faceImage');
    myPic.setValue('<div style="width: 150px;height: 190px;background: #ececec;"></div>');

    var myPic = view.down('#mypep').down('#faceImage');
    myPic.setValue('<div style="width: 150px;height: 190px;background: #ececec;"></div>');

    var myPic = view.down('#mygoldbalance').down('#faceImage');
    myPic.setValue('<div style="width: 150px;height: 190px;background: #ececec;"></div>');

    var myPic = view.down('#myamla').down('#faceImage');
    myPic.setValue('<div style="width: 150px;height: 190px;background: #ececec;"></div>');
    
    snap.getApplication().sendRequest({
      hdl: view.myDataHandler, action: view.myDataAction, id: accountHolderId
    }, 'Fetching data from server....').then(
      function (data) {
        if (data.success) {

          var view = me.getView();
          view.setActiveTab(0);

          if (data.accountholder.information.ispep) {
            view.getComponent('mypep').tab.show()
          } else {
            view.getComponent('mypep').tab.hide()
          }

          me.setAccountHolderInfo(data.image, data.accountholder);

          var myProfileController = view.down('#myprofile').getController();
          myProfileController.loadChildView(data.accountholder);

          var myKycController = view.down('#mykyc').getController();
          myKycController.loadChildView(data.kyc);

          var myPepController = view.down('#mypep').getController();
          myPepController.loadChildView(data.pep);

          var myGoldBalanceController = view.down('#mygoldbalance').getController();
          myGoldBalanceController.loadChildView(data.goldbalance);

          var myKycController = view.down('#myamla').getController();
          myKycController.loadChildView(data.amla);
        }
      });
  },

  setAccountHolderInfo: function (image, accountholder) {
    var mainViewModel = snap.getApplication().getMainView().getViewModel();
    mainViewModel.set('webtrail', '<a style="text-decoration: none;" href="javascript:history.go(-1)">Back</a> <span class="fa fa-angle-right"></span> Account Holder <span class="fa fa-angle-right"></span> ' + accountholder.information.fullname);

    var view = this.getView();
    var myPic = view.down('#myprofile').down('#faceImage');
    if (myPic != null) myPic.setValue(image);

    var myPic = view.down('#mykyc').down('#faceImage');
    if (myPic != null) myPic.setValue(image);

    var myPic = view.down('#mypep').down('#faceImage');
    if (myPic != null) myPic.setValue(image);

    var myPic = view.down('#mygoldbalance').down('#faceImage');
    if (myPic != null) myPic.setValue(image);

    var myPic = view.down('#myamla').down('#faceImage');
    if (myPic != null) myPic.setValue(image);
  },

  setMyCifConfig: function () {
    var view = this.getView();

    var myDate = view.down('#myDate');
    if (myDate != null) {
      myDate.setTitle(view.myDateTitle);
    }

    var myGrid = view.down('#myGrid');
    if (myGrid != null) {
      myGrid.setTitle(view.myGridTitle);
    }

    var myForm = view.down('#myForm');
    if (myForm != null) {
      myForm.setTitle(view.myFormTitle);
    }
  },

});
