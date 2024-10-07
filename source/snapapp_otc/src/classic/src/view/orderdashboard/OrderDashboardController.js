Ext.define('snap.view.orderdashboard.OrderDashboardController', {
  extend: 'snap.view.gridpanel.BaseController',
  alias: 'controller.orderdashboard-orderdashboard',


  onPreLoadViewDetail: function(record, displayCallback) {
      snap.getApplication().sendRequest({ hdl: 'order', action: 'detailview', id: record.data.id})
      .then(function(data){
          if(data.success) {
              displayCallback(data.record);
          }
      })
      return false;
  },

  spotOrderAction: function (btn) {
  if (btn.id == 'spotacebuy') {
    //Ext.getCmp('sellorbuy').setValue('sell');
  } else if (btn.id == 'spotacesell') {
    //Ext.getCmp('sellorbuy').setValue('buy');
  }
  var view = this.getView(),
    model = Ext.create('snap.view.spotorder.FormModel', view.getValues());
  var errors = { productitem: true, amount: true, weight: true, id: true, uuid:true };		
  if (view.getValues().uuid == null) {
    errors.uuid = "Sorry Your Order Cannot Be Process, Our ACE Connection Currently Offline";
    Ext.toast(errors.uuid,4000);
  }
  if (view.getValues().productitem == null) {
    errors.productitem = "Product is required";
  }
  if (view.getValues().amount == "" && view.getValues().weight == "") {
    errors.amount = "This field is required";
  }
  var regex = /^[0-9]*\.?[0-9]*$/;
  if (view.getValues().amount != "" && !regex.test(view.getValues().amount)) {
    errors.amount = "Enter valid number";
  }
  var validationerror = 0;
  for (var err in errors) {
    if (errors[err] != true) {
      validationerror++;
    }
  }		
  if (validationerror != 0) {
    //var errors = model.getValidation().getData();
    Object.keys(errors).forEach(function (f) {
      //console.log(view);
      var field = view.getFields(f);
      if (field && errors[f] !== true) {
        field.markInvalid(errors[f]);
      }
    });
    return false;
  }
  var form = this.getView();
  form.submit({
    submitEmptyText: false,
    url: 'index.php',
    method: 'POST',
    params: { hdl: 'spotorder', action: 'makeOrder' },
    waitMsg: 'Processing',
    success: function (frm, action) { //success
      Ext.Msg.alert('Success', 'Submitted Successfully !', Ext.emptyFn);
    },
    failure: function (frm, action) {
      Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
    }
  });
},

  _printOrderPDFSpot: function(btn) {
      // var owningWindow = btn.up('window');
      // var gridFormPanel = owningWindow.down('form');
      var me = this;
      
      // Get Printable data
      orderid = btn.up().up().items.items[0].items.items[4].getValue();

      var url = 'index.php?hdl=order&action=printSpotOrder&orderid='+orderid;
      Ext.Ajax.request({
        url: url,
        method: 'get',
        waitMsg: 'Processing',
        //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
        autoAbort: false,
        success: function (result) {
          var win = window.open('');
            win.location = url;
            win.focus();
        },
        failure: function () {
          
          Ext.MessageBox.show({
            title: 'Error Message',
            msg: 'Failed to retrieve data',
            buttons: Ext.MessageBox.OK,
            icon: Ext.MessageBox.ERROR
          });
        }
      });

  },

  // this is carousel function for gold slider
  init: function(view) {
      console.log(view,"ASD")
      data = {
          'html': `
              <div class="owl-carousel owl-theme">
                  <div class="item"><h4>1</h4></div>
                  <div class="item"><h4>2</h4></div>
                  <div class="item"><h4>3</h4></div>
                  <div class="item"><h4>4</h4></div>
                  <div class="item"><h4>5</h4></div>
                  <div class="item"><h4>6</h4></div>
                  <div class="item"><h4>7</h4></div>
                  <div class="item"><h4>8</h4></div>
                  <div class="item"><h4>9</h4></div>
                  <div class="item"><h4>10</h4></div>
                  <div class="item"><h4>11</h4></div>
                  <div class="item"><h4>12</h4></div>
                  <div class="item"><h4>13</h4></div>
                  <div class="item"><h4>14</h4></div>
                  <div class="item"><h4>15</h4></div>
              </div>
              <div id="customNav" class="owl-nav"></div>
              <div id="customDots" class="owl-dots"></div>  
              <link rel="stylesheet" href="./src/resources/js/assets/owl.theme.default.min.css">
              <link rel="stylesheet" href="./src/resources/js/assets/animate.css">
              `
      }
      // <script src="./src/resources/js/jquery-3.6.0.js"></script>
      // <script src="./src/resources/js/jquery-1.12.4.js"></script>
      // <script src="./src/resources/js/owl.carousel.min.js"></script>
      _this = this
      let das = new Promise(function(xresolver, rejection){

          view.lookupReference('sliderhtml').setHtml('<div class="owl-carousel owl-theme">'+
          //'<div class="item"><img data-product-code="GS-999-9-0.5g" data-product-weight="0.5" src="src/resources/img/goldbar/0.5gb.png" ></div>' +
          '<div class="item"><img data-product-code="GS-999-9-1g" data-product-weight="1" src="src/resources/img/goldbar/1gb.png"></div>' +
          //'<div class="item"><img data-product-code="GS-999-9-2.5g" data-product-weight="2.5" src="src/resources/img/goldbar/2.5gb.png"></div>' +
          '<div class="item"><img data-product-code="GS-999-9-5g" data-product-weight="5" src="src/resources/img/goldbar/5gb.png"></div>' +
          '<div class="item"><img data-product-code="GS-999-9-10g" data-product-weight="10" src="src/resources/img/goldbar/10gb.png"></div>' +
          '<div class="item"><img data-product-code="GS-999-9-50g" data-product-weight="50" src="src/resources/img/goldbar/50gb.png"></div>' +
          '<div class="item"><img data-product-code="GS-999-9-100g" data-product-weight="100" src="src/resources/img/goldbar/100gb.png"></div>' 
          //'<div class="item"><img data-product-code="GS-999-9-1-DINAR" data-product-weight="4.25" src="src/resources/img/goldbar/1dinarb.png"></div>' +
          //'<div class="item"><img data-product-code="GS-999-9-5-DINAR" data-product-weight="21.25" src="src/resources/img/goldbar/5dinarb.png"></div></div>'
          );
          xresolver('ok');
          // snap.getApplication().sendRequest({ hdl: 'announcement', action: 'getSliders'})
          // .then(function(data){
          //     if(data.success) {
          //             // arr = data.data
          //             // html_data = _this.constructslider(arr);
          //             // view.lookupReference('sliderhtml').setHtml(html_data)
                   
          //         }
          //     })
      }); 
      das.then((value)=>{
          if (value == 'ok'){
              _this.initOwlCarousel();
          }
      })
      // await das
      // console.log(asdasd, 'adsasdasdasd');
  },

  initOwlCarousel: async function (a, b) {
      $(document).ready(function(){
          jQuery('.owl-carousel').owlCarousel({
              loop:true,
              margin:10,
              nav:true,
              startPosition:1,
              pagination: true,
              autoplay:false,
              autoplayTimeout:5000,
              autoplayHoverPause:true,
              // rtl:true,
              animateOut: 'animate__slideOutRight',
              navText: ["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
              
              stagePadding: 12,
              responsive:{
                  0:{
                      items:1
                  },
                  600:{
                      items:1
                  },
                  1000:{
                      items:1
                  }
              },
              onTranslate: function(me) {
                  
                  // jQuery(me.target).find("item").eq(me.item.index).find("img").attr('src');

                  // jQuery(me.target).find(".item").eq(me.item.index).find("img").attr('value');
                  // jQuery(me.target).find(".item").eq(me.item.index).find("img").attr('src');

                  product = jQuery(me.target).find(".item").eq(me.item.index).find("img").attr('data-product-code');
                  weight = jQuery(me.target).find(".item").eq(me.item.index).find("img").attr('data-product-weight');
                  weight = parseFloat(weight).toFixed(3);
                  goldbalance = (vm.get('profile-goldbalance') != null ? vm.get('profile-goldbalance') : 0.000);
                  
                  // slides = $(slick.$slides.get(currentSlide));
                  // const product = slides[0].getElementsByClassName("btn")[0].dataset.productCode;
                  // const weight = slides[0].getElementsByClassName("btn")[0].dataset.productWeight;
          
                  // const quantity = $('#quantity').val();
                  // const total = weight * quantity;
                  // const initial = $('#gold-balance').text();
              
                  // $('#total-conversion').text((total).toFixed(3));
                  // $('#balance-conversion').text((initial - total).toFixed(3));
                  // $('#product').val(product);
                  // $('#weight').val(weight);
                  // debugger;
                  totalconversionvalue = weight*parseInt(elmnt.lookupReference('conversion-quantity').value);
                  Ext.getCmp('totalconversionvalue').setValue(totalconversionvalue.toFixed(3));

                  
                  totalconversionvalue = parseFloat(totalconversionvalue);
                  balanceafterconversion = goldbalance - totalconversionvalue;
                  balanceafterconversion = parseFloat(balanceafterconversion).toFixed(3);
                  elmnt.lookupReference('balanceafterconversion').setValue(balanceafterconversion > 0 ? balanceafterconversion: 0.000);

                  
                  if(balanceafterconversion >= parseFloat(vm.get('profile-minbalancexau'))){  
                      elmnt.lookupReference('convertButton').setDisabled(false)
                  }else{
                      elmnt.lookupReference('convertButton').setDisabled(true)
                  }
                  
                  // Save product and weight to viewmodel
                  vm.set("convert.product", product);
                  vm.set("convert.weight", weight);
                  // Ext.MessageBox.show({
                  //     title: product + 'Selected',
                  //     msg: 'Selected ' + weight,
                  //     buttons: Ext.MessageBox.OK,
                  //     icon: Ext.MessageBox.OK
                  // });

                  // $(me.target).find(".owl-item.active [data-src]:not(.loaded)").each(function(i, v) {
                        
                  //     $(v).addClass("loaded").css("background-image", "url(" + $(v).attr("data-src") + ")");
                  // });
              },
          })
      })
  },

  // OTC SPOT ORDER BUY ( ACE SELL )
  doSpotOrderBuyOTC: function(elemnt) {
      var me = this;

      var form = elemnt.lookupController().lookupReference('buyorder-form').getForm();
      //form2 = elemnt.lookupController().lookupReference('futureorder-form').getForm();
      
      // Create forms
      spotorder = form.getFieldValues();

      // Check if a record is selected
      // If not send error

      if(vm.get('profile-fullname') == '-'){
          Ext.MessageBox.show({
              title: 'Error Message',
              msg: 'Please select a user record',
              buttons: Ext.MessageBox.OK,
              icon: Ext.MessageBox.ERROR
          });
          return;
      }
      
      /*------------------------ Ace Buy Display ------------------------------------*/

      // Acquire Ace Sell 
      acesell = spotorder.acesellprice; 

      var me = this, selectedRecord,
      myView = this.getView();
      // var sm = myView.getSelectionModel();
      
      var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.orderpopup ? myView.orderpopup : {}, {
          // viewModel: {
          //     type: 'OrderDashboardViewModel'
          // },
          classView: me,
          parentView: myView,
          // formDialogButtons: [{
          //     xtype:'panel',
          //     flex:3
          // },
          // {
          //     text: 'Buy Gold',
          //     flex: 2,
          //     reference: 'orderpopup-button',
          //     handler: function(btn) {
          //         me.buyAqad(btn,elemnt);
          //     }
          // },
          // // {
          // //     text: 'Close',
          // //     flex: 1,
          // //     handler: function(btn) {
          // //         owningWindow = btn.up('window');
          // //         owningWindow.close();
          // //         me.gridFormView = null;
          // //     }
          // // },
          // {
          //     xtype:'panel',
          //     flex: 2,
          // }]
      }));
      
     // Set view model
     gridFormView.setViewModel(vm);
      

     // create button reference and store to vm
      //    buttonReference = gridFormView.lookupController().lookupReference('orderpopup-button-buy');

      //    vm.set('orderpopup-button-buy', buttonReference);
     vm.set('orderpopup-gridform-buy', gridFormView);

      // populate values
      goldbalance = (vm.get('profile-goldbalance') != null ? vm.get('profile-goldbalance') : 0.00)
      gridFormView.lookupReference('orderpopup-buy-goldbalance').setValue( parseFloat(goldbalance).toFixed(2)+'g');

      this.gridFormView = gridFormView;
      this._formAction = "edit";

      var addEditForm = this.gridFormView.down('form').getForm();

      if(PROJECTBASE == "BSN"){
          gridFormView.title = 'Bank Sell';
      }else{
          gridFormView.title = 'Buy Gold';
      }
      gridFormView.title.color = 'green';
      //debugger;
      this.gridFormView.show();
  },

  countdown: function(gridForm, element, minutes, seconds)  {
      // set time for the particular countdown
      var time = minutes*60 + seconds;
      var interval = setInterval(function() {
          // var el = document.getElementById(element);
          // if the time is 0 then end the counter
          if (time <= 0) {
              var text = "Expired";
              element.setValue(text);
              // remove recount
              // setTimeout(function() {
              //     countdown(element, 0, 5);
              // }, 2000);

              // debugger;
              // // Disable buttons 
              gridForm.lookupController().lookupReference('print-button-bank-aqad').setStyle('opacity', 0.5);
              gridForm.lookupController().lookupReference('print-button-bank-aqad').setDisabled(true);
              //gridForm.lookupController().lookupReference('print-button-cust').setStyle('opacity', 0.5);
              //gridForm.lookupController().lookupReference('print-button-cust').setDisabled(true);
              gridForm.lookupController().lookupReference('confirm-button').setStyle('opacity', 0.5);
              gridForm.lookupController().lookupReference('confirm-button').setDisabled(true);
              clearInterval(interval);
              return;
          }
          var minutes = Math.floor( time / 60 );
          if (minutes < 10) minutes = "0" + minutes;
          var seconds = time % 60;
          if (seconds < 10) seconds = "0" + seconds; 
          var text = minutes + ':' + seconds;
          element.setValue(text);
          time--;
      }, 1000);
  },
  // Do Aqad Check
  validateBuySell: function(spotorder) {
      // Do check, check for the following criteria
      /* 
      * 1) Make sure price is above RM 25
      * 2) Check if user has enough balance
      * 3) 
      * 
      */

      // Get Partner LImits 
      minbalancexau = parseFloat( (vm.get('profile-minbalancexau') ? vm.get('profile-minbalancexau') : 0) );
      threshold = 5.00;
      availablebalance = parseFloat( (vm.get('profile-availablebalance') ? vm.get('profile-availablebalance') : 0) );
      goldbalance = parseFloat( (vm.get('profile-goldbalance') ? vm.get('profile-goldbalance') : 0) );

      // Check if empty then do something
      input_amount = (spotorder.companybuyamount ? spotorder.companybuyamount : spotorder.companysellamount);
      input_xau = (spotorder.companybuyxau ? spotorder.companybuyxau : spotorder.companysellxau);
     
      remainderGold = goldbalance - input_xau;

      
      // Check goldbalance if its lower than min balance
      if(goldbalance < minbalancexau){
          Ext.MessageBox.show({
              title: 'Error Message',
              msg: 'The minimum sell value is '+minbalancexau+'g and user are required to have at least '+minbalancexau+'g in account',
              buttons: Ext.MessageBox.OK,
              icon: Ext.MessageBox.ERROR
          });
      }
      else{
          if(availablebalance <= 0){
              if(goldbalance > 0){
                      Ext.MessageBox.show({
                          title: 'Error Message',
                          msg: 'You need to have above 0.1gram of gold to sell',
                          buttons: Ext.MessageBox.OK,
                          icon: Ext.MessageBox.ERROR
                      });
              }
              else{
                      Ext.MessageBox.show({
                          title: 'Error Message',
                          msg: 'Your gold balance is 0. Please purchase gold.',
                          buttons: Ext.MessageBox.OK,
                          icon: Ext.MessageBox.ERROR
                      });
              }
          }
          else{
              // if (total < 25 ) {
              if (input_xau < minbalancexau ) {
                      Swal.fire({
                          title: 'Info!',
                          text: (availablebalance <= 0 ? 'Your gold balance is 0. Please purchase gold.' : 'Please designate a minimum value of '+minbalancexau+'g gold'),
                          icon: 'info',
                          confirmButtonText: 'OK'
                      });
                  }
              else{
                   return false
              }
          }
      }
      
  
      // Check if buy/sell price is above minimum
      // Check if xau does not exceed balance
      if(input_amount > threshold && input_xau <= goldbalance){
          return true;
      }else{
          return false;
      }
      // text: '<?php echo $_SESSION['available_balance'] <= 0 ? $lang['sell_zerogoldbalance'] : $lang['msg_zero_value']; ?>',

  },

  sellAqad: function(gridForm,elemnt){
      var me = this;

      if(PROJECTBASE != 'BSN'){
        vm.set('isprintaqad', true);
      }else{
        vm.set('isprintaqad', false);
      }
      // var form = btn.lookupController().lookupReference('orderpopupsell-form').getForm();
      // //form2 = elemnt.lookupController().lookupReference('futureorder-form').getForm();
      
      // // Create forms

      // spotorder = form.getFieldValues();


      /*------------------------ Ace Buy Display ------------------------------------*/

      // Validate Sell 
      // this.validateBuySell(spotorder);
      // Acquire Ace Sell 
      // acesell = spotorder.acesellprice; 

      var me = this, selectedRecord,
      myView = this.getView();
      // var sm = myView.getSelectionModel();
      price_arr = vm.get(PROJECTBASE + '_CHANNEL');
      vm.set('currentuuid', price_arr.uuid);
      //debugger;
      input_xau = gridForm.lookupController().lookupReference('companybuyxau').value;

      snap.getApplication().sendRequest({ hdl: 'myorder', action: 'doAqad', 
          is_order_type_sell: 'acebuy', 
          accountholder_id: vm.get('profile-id'),
          partnercode: PROJECTBASE,
          settlement_method: 'casa',
          uuid : price_arr['uuid'],
          weight: input_xau,
          product_code: "DG-999-9",
          from_alert: false,
      }, 'Loading Sell Aqad....')
      .then(function(data){

          if(data.success) {
              returndata = data.record.data;
              // set magic
              // populate values 
              
              // populate values 
              input_amount = gridForm.lookupController().lookupReference('companybuyamount').value;

              dateconfirmed = new Date();
              datetext = dateconfirmed.toString('dddd', 'mmmm', 'yyyy');

              gridFormView_sellaqad.lookupReference('sellaqad-date').setValue(datetext);
              gridFormView_sellaqad.lookupReference('sellaqad-fullname').setValue(vm.get('profile-fullname'));
              gridFormView_sellaqad.lookupReference('sellaqad-mykadno').setValue(vm.get('profile-mykadno'));
              gridFormView_sellaqad.lookupReference('sellaqad-accountholdercode').setValue(vm.get('profile-accountholdercode'));
              gridFormView_sellaqad.lookupReference('sellaqad-xau').setValue(returndata.weight.toFixed(3) + ' gram');
              gridFormView_sellaqad.lookupReference('sellaqad-price').setValue('RM' + returndata.price +' / gram');
              gridFormView_sellaqad.lookupReference('sellaqad-amount').setValue('RM' + returndata.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
              gridFormView_sellaqad.lookupReference('sellaqad-teller').setValue(snap.getApplication().username);
              gridFormView_sellaqad.lookupReference('sellaqad-finaltotal').setValue('RM' + returndata.total_transaction_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
              
              // Trigger aqad timer
              me.countdown(gridFormView_sellaqad, gridFormView_sellaqad.lookupReference('sellaqad-timer'), 3, 0);

              me.gridFormView_sellaqad = gridFormView_sellaqad;
              me._formAction = "edit";

              var addEditForm = me.gridFormView_sellaqad.down('form').getForm();

              if (PROJECTBASE == 'BSN'){
                gridFormView_sellaqad.title = 'Bank Buy';

                me.gridFormView_sellaqad.show();   
                    
              }else{
                me.gridFormView_buyaqad.show();
                gridFormView_sellaqad.lookupReference('print-button-bank-aqad').setHidden(true);
              }
              // Check if alrajhi
              if (PROJECTBASE == 'ALRAJHI'){
 
                
                // check if there are accounts
                if(returndata.casa_accounts){
                    gridFormView_sellaqad.lookupReference('sellaqad-accountselection').store.setData(returndata.casa_accounts);
                    gridFormView_sellaqad.lookupReference('sellaqad-accountselection').setValue(returndata.casa_accounts[0]);
                    
                    
                    gridFormView_sellaqad.title = 'Customer Sell';

                    me.gridFormView_sellaqad.show();   
                    
                }else{
                    Ext.MessageBox.show({
                        title: 'Error Message',
                        msg: 'Unable to connect to CASA API',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });  
                }
               
              }
              
              //debugger;
              // me.gridFormView_sellaqad.show();   
          }
      })

      var gridFormView_sellaqad = Ext.create(myView.formClass, Ext.apply(myView.orderpopupsellaqad ? myView.orderpopupsellaqad : {}, {
          formDialogButtons: [{
              xtype:'panel',
              flex:3
          },
          {
              text: 'Print Order Confirmation',
              flex: 5,
              reference: 'print-button-bank-aqad',
              handler: function(btn) {
                var finalTotal = gridFormView_sellaqad.lookupReference('sellaqad-finaltotal').getValue();
                finalTotal = finalTotal.replace(/,/g,'');
                finalTotal = parseFloat(finalTotal.substring(2));

                if(finalTotal >= 10){
                  me._printAqad(gridFormView_sellaqad, PROJECTBASE, 'sell','bank');
                }else{
                  Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'The final total is lower than RM 10',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                  }); 
                }
              }
          },
          // {
          //   text: 'Print (CUSTOMER COPY)',
          //   flex: 5,
          //   reference: 'print-button-cust-aqad',
          //   handler: function(btn) {
          //       me._printAqad(gridFormView_sellaqad, PROJECTBASE, 'sell','customer');
          //   }
          // },
          {
              text: 'Confirm',
              flex: 3,
              reference: 'confirm-button',
              handler: function(btn) {
                  
                  if(vm.get('isprintaqad')){
                    me._makeOrder(btn, gridForm, gridFormView_sellaqad, false);
                  }else{
                      Ext.MessageBox.show({
                          title: 'Error Message',
                          msg: 'Please Print before Submit',
                          buttons: Ext.MessageBox.OK,
                          icon: Ext.MessageBox.ERROR
                      });  
                  } 
              }
          },
          
          // {
          //     text: 'Close',
          //     flex: 1,
          //     handler: function(btn) {
          //         owningWindow = btn.up('window');
          //         owningWindow.close();
          //         me.gridFormView = null;
          //     }
          // },
          {
              xtype:'panel',
              flex: 2,
          }]
      }));

      
  },

  buyAqad: function(gridForm, elemnt){
      var me = this;
      if(PROJECTBASE != 'BSN'){
        vm.set('isprintaqad', true);
      }else{
        vm.set('isprintaqad', false);
      }
      
      pricedata = vm.get(PROJECTBASE + '_CHANNEL');

      //debugger;
      // var form = elemnt.lookupController().lookupReference('spotorder-form').getForm();
      // //form2 = elemnt.lookupController().lookupReference('futureorder-form').getForm();
      
      // // Create forms
      // spotorder = form.getFieldValues();

      // /*------------------------ Ace Buy Display ------------------------------------*/


      // // Acquire Ace Sell 
      // acesell = spotorder.acesellprice; 

      var me = this, selectedRecord,
      myView = this.getView();
      // var sm = myView.getSelectionModel();
      price_arr = vm.get(PROJECTBASE + '_CHANNEL');
      vm.set('currentuuid', price_arr.uuid);

      input_xau = gridForm.lookupController().lookupReference('companysellxau').value;

      if(PROJECTBASE != 'POSARRAHNU'){
        settlementMethod = 'casa';
      }else{
        settlementMethod = 'cash';
      }

      snap.getApplication().sendRequest({ hdl: 'myorder', action: 'doAqad', 
          is_order_type_sell: 'acesell', 
          accountholder_id: vm.get('profile-id'),
          partnercode: PROJECTBASE,
          settlement_method: settlementMethod,
          uuid : price_arr['uuid'],
          weight: input_xau,
          product_code: "DG-999-9",
          from_alert: false,
      }, 'Loading Buy Aqad....')
      .then(function(data){

          if(data.success) {

              returndata = data.record.data;
              // set magic
              // populate values 
              
              input_amount = gridForm.lookupController().lookupReference('companysellamount').value;

              dateconfirmed = new Date();
              datetext = dateconfirmed.toString('dddd', 'mmmm', 'yyyy');

              if(PROJECTBASE == 'BSN'){
                if(returndata.special_price != null){
                  discount = returndata.price - returndata.special_price;
                  discountAmount = discount.toFixed(2)*returndata.weight.toFixed(3);
                  gridFormView_buyaqad.lookupReference('buyaqad-discount').setValue('RM' + discount.toFixed(2) +' / gram');
                  gridFormView_buyaqad.lookupReference('buyaqad-price').setValue('RM' + returndata.special_price +' / gram');
                  gridFormView_buyaqad.lookupReference('buyaqad-discountAmount').setValue('RM' + discountAmount.toFixed(2));

                }else{
                  gridFormView_buyaqad.lookupReference('buyaqad-discount-field').setHidden(true);
                  gridFormView_buyaqad.lookupReference('buyaqad-discount-field-line').setHidden(true);
                  gridFormView_buyaqad.lookupReference('buyaqad-discountAmount-field').setHidden(true);
                  gridFormView_buyaqad.lookupReference('buyaqad-discountAmount-field-line').setHidden(true);
                  gridFormView_buyaqad.lookupReference('buyaqad-price').setValue('RM' + returndata.price +' / gram');
                }
              }

              gridFormView_buyaqad.lookupReference('buyaqad-date').setValue(datetext);
              gridFormView_buyaqad.lookupReference('buyaqad-fullname').setValue(vm.get('profile-fullname'));
              gridFormView_buyaqad.lookupReference('buyaqad-mykadno').setValue(vm.get('profile-mykadno'));
              gridFormView_buyaqad.lookupReference('buyaqad-accountholdercode').setValue(vm.get('profile-accountholdercode'));
              gridFormView_buyaqad.lookupReference('buyaqad-xau').setValue(returndata.weight.toFixed(3) + ' gram');
              gridFormView_buyaqad.lookupReference('buyaqad-amount').setValue('RM' + returndata.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
              gridFormView_buyaqad.lookupReference('buyaqad-teller').setValue(snap.getApplication().username);
              gridFormView_buyaqad.lookupReference('buyaqad-finaltotal').setValue('RM' + returndata.total_transaction_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

              // Trigger aqad timer
              me.countdown(gridFormView_buyaqad, gridFormView_buyaqad.lookupReference('buyaqad-timer'), 3, 0);
              
              me.gridFormView_buyaqad = gridFormView_buyaqad;
              me._formAction = "edit";

              var addEditForm = me.gridFormView_buyaqad.down('form').getForm();

              if (PROJECTBASE == 'BSN'){
                gridFormView_buyaqad.title = 'Bank Sell';
  
                me.gridFormView_buyaqad.show();   
                      
              }else{
                gridFormView_buyaqad.lookupReference('print-button-bank-aqad').setHidden(true);
                me.gridFormView_buyaqad.show();

              }
              // Check if alrajhi
              if (PROJECTBASE == 'ALRAJHI'){
  
                // check if there are accounts
                if(returndata.casa_accounts){
                    gridFormView_buyaqad.lookupReference('buyaqad-accountselection').store.setData(returndata.casa_accounts);
                    gridFormView_buyaqad.lookupReference('buyaqad-accountselection').setValue(returndata.casa_accounts[0]);
                    
                
                    gridFormView_buyaqad.title = 'Customer Buy';
                    //debugger;
                    me.gridFormView_buyaqad.show();   
                    
                }else{
                    Ext.MessageBox.show({
                        title: 'Error Message',
                        msg: 'Unable to connect to CASA API',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });  
                }
               
              }
              //debugger;
              //me.gridFormView_buyaqad.show();   
          }
      })

      

      var gridFormView_buyaqad = Ext.create(myView.formClass, Ext.apply(myView.orderpopupbuyaqad ? myView.orderpopupbuyaqad : {}, {
          formDialogButtons: [{
              xtype:'panel',
              flex:3
          },
          {
              text: 'Print Order Confirmation',
              flex: 5,
              reference: 'print-button-bank-aqad',
              style:{
                opacity: 1
              },
              handler: function(btn) {
                var finalTotal = gridFormView_buyaqad.lookupReference('buyaqad-finaltotal').getValue();
                finalTotal = finalTotal.replace(/,/g,'');
                finalTotal = parseFloat(finalTotal.substring(2));

                if(finalTotal >= 10){
                  me._printAqad(gridFormView_buyaqad, PROJECTBASE, 'buy','bank');
                }else{
                  Ext.MessageBox.show({
                    title: 'Error Message',
                    msg: 'The final total is lower than RM 10',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                  }); 
                }
              },
              
          },
          // {
          //   text: 'Print (CUSTOMER COPY)',
          //   flex: 5,
          //   reference: 'print-button-cust-aqad',
          //   handler: function(btn) {
          //       me._printAqad(gridFormView_buyaqad, PROJECTBASE, 'buy','customer');
          //   }
          // },
          {
              text: 'Confirm',
              flex: 3,
              reference: 'confirm-button',
              handler: function(btn) {
                if(vm.get('isprintaqad')){
                    me._makeOrder(btn, gridForm, gridFormView_buyaqad, true);
                }else{
                    Ext.MessageBox.show({
                        title: 'Error Message',
                        msg: 'Please Print before Submit',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });  
                } 
              }
          },
          // {
          //     text: 'Close',
          //     flex: 1,
          //     handler: function(btn) {
          //         owningWindow = btn.up('window');
          //         owningWindow.close();
          //         me.gridFormView = null;
          //     }
          // },
          {
              xtype:'panel',
              flex: 2,
          }]
      }));

      
  },

  // OTC SPOT ORDER SELL ( ACE BUY )
  doSpotOrderSellOTC: function(elemnt) {
      var me = this;

      var form = elemnt.lookupController().lookupReference('sellorder-form').getForm();
      //form2 = elemnt.lookupController().lookupReference('futureorder-form').getForm();
      
      // Create forms
      spotorder = form.getFieldValues();


      /*------------------------ Ace Buy Display ------------------------------------*/

      // Acquire Ace BUY 
      acebuy = spotorder.acebuyprice;

      // Check if a record is selected
      // If not send error

      if(vm.get('profile-fullname') == '-'){
          Ext.MessageBox.show({
              title: 'Error Message',
              msg: 'Please select a user record',
              buttons: Ext.MessageBox.OK,
              icon: Ext.MessageBox.ERROR
          });
          return;
      }

      var me = this, selectedRecord,
      myView = this.getView();
      // var sm = myView.getSelectionModel();
      // Get channel name
      // channelName = vm.get('getChannelName');

      var gridFormView_sell = Ext.create(myView.formClass, Ext.apply(myView.orderpopupsell ? myView.orderpopupsell : {}, {
          classView: me,
          parentView: myView,
          // formDialogButtons: [{
          //     xtype:'panel',
          //     flex:3
          // },
          // {
          //     text: 'Sell Gold',
          //     flex: 2,
          //     reference: 'orderpopup-button',
          //     handler: function(btn) {
          //         me.sellAqad(btn,elemnt);
          //     }
          // },
          // // {
          // //     text: 'Close',
          // //     flex: 1,
          // //     handler: function(btn) {
          // //         owningWindow = btn.up('window');
          // //         owningWindow.close();
          // //         me.gridFormView_sell = null;
          // //     }
          // // },
          // {
          //     xtype:'panel',
          //     flex: 2,
          // }]
      }));
      // Set view model
      gridFormView_sell.setViewModel(vm);

      //    vm.set('orderpopup-button-buy', buttonReference);
      vm.set('orderpopup-gridform-sell', gridFormView_sell);
      // debugger;
      // Set values  
      // gridFormView_sell.lookupController().lookupReference('orderpopup-sell-goldbalance').setValue(vm.get('profile-goldbalance'));
      goldbalance = (vm.get('profile-goldbalance') != null ? vm.get('profile-goldbalance') : 0.00)
      gridFormView_sell.lookupController().lookupReference('orderpopup-sell-goldbalance').setValue(parseFloat(goldbalance).toFixed(2) +'g');

      this.gridFormView_sell = gridFormView_sell;
      this._formAction = "edit";

      var addEditForm = this.gridFormView_sell.down('form').getForm();

      if(PROJECTBASE == "BSN"){
        gridFormView_sell.title = 'Bank Buy';
      }else {
        gridFormView_sell.title = 'Sell Gold';
      }

      gridFormView_sell.title.color = '#F64B4C';
      //debugger;
      this.gridFormView_sell.show();
  },

  conAqad: function (baseView, myView, data) {
    // alert("test");
    if(PROJECTBASE == 'ALRAJHI'){
      vm.set('isprintaqad', true);
    }else{
      vm.set('isprintaqad', false);
    }
     var me = this,
     myView = this.getView();

    var firstTimeConvert = '';
    // if(PROJECTBASE == "BSN"){
    //   snap.getApplication().sendRequest({ hdl: 'myconversion', action: 'checkFirstTimeConversion', 
    //       accountholder_id: vm.get('profile-id'),
    //       partnercode: PROJECTBASE,
    //       from_alert: false,
    //   }, 'Loading Convert Aqad....')
    //   .then(function(data){
    //       if(data.success) {
    //         firstTimeConvert = 'yes';
    //       }else{
    //         firstTimeConvert = 'no';
    //       }
    //   })
    // }

    me._getConversionFee(me, myView)
     .then(function (data) {
        var gridFormView = Ext.create(
          myView.formClass, Ext.apply(myView.orderpopupconvertaqad ? myView.orderpopupconvertaqad : {}, {
            formDialogButtons: [
              {
                xtype: "panel",
                flex: 3,
              },
              {
                text: 'Print Order Confirmation',
                flex: 5,
                reference: 'print-button-bank-aqad',
                style:{
                  opacity: 1
                },
                handler: function(btn) {
                    me._printConvertAqad(myView, gridFormView, PROJECTBASE,'bank');
                },
                
              },
              // {
              //   text: 'Print (CUSTOMER COPY)',
              //   flex: 5,
              //   reference: 'print-button-cust-aqad',
              //   handler: function(btn) {
              //       me._printConvertAqad(gridFormView, PROJECTBASE,'customer');
              //   }
              // },
              {
                text: "Confirm",
                flex: 2,
                reference: "confirm-button",
                handler: function (btn) {

                    if(vm.get('isprintaqad')){
                      me._makeConversion(btn, baseView, gridFormView, data);
                    }else{
                        Ext.MessageBox.show({
                            title: 'Error Message',
                            msg: 'Please Print before Submit',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR
                        });  
                    } 
                },
              },
              {
                xtype: "panel",
                flex: 2,
              },
            ],
          })
        );
        gridFormView.title = 'Customer Redeem Gold';
        gridFormView.setViewModel(vm);
        

  
        dateconfirmed = new Date();
        datetext = dateconfirmed.toString("dddd", "mmmm", "yyyy");
        gridFormView.lookupReference("convertaqad-date").setValue(datetext);
        gridFormView.lookupReference("convertaqad-fullname").setValue(vm.get("profile-fullname"));
        gridFormView.lookupReference("convertaqad-mykadno").setValue(vm.get("profile-mykadno"));
        gridFormView.lookupReference("convertaqad-accountholdercode").setValue(vm.get("profile-accountholdercode"));
        // let totalconversionvaluerow = Ext.getCmp("totalconversionvalue").getRawValue(); // get the display field by its ID
        quantity = myView.lookupController().lookupReference('conversion-quantity').value;

        totalconversionvaluerow = myView.lookupController().lookupReference('totalconversionvalue').value;
  
        balanceafterconversion = myView.lookupController().lookupReference('balanceafterconversion').value;
        let transactionfee = data.data.transaction_fee;
        //let total = parseInt(totalconversionvaluerow) + conversionfee;
        let conversionfee =  data.data.conversion_fee + data.data.totalHandlingFee;
        let makingcharges =  data.data.insurans_fee;
        let courierfee =  data.data.totalDeliveryFee;
        let finaltotal = data.data.total_fee;
        gridFormView.lookupReference('convertaqad-xau').setValue(totalconversionvaluerow + ' gram');
        gridFormView.lookupReference("convertaqad-fee").setValue("RM " + conversionfee.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        gridFormView.lookupReference("convertaqad-makingcharges").setValue("RM " + makingcharges.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        gridFormView.lookupReference("convertaqad-deliveryfee").setValue("RM " + courierfee.toFixed(2));
        //gridFormView.lookupReference("convertaqad-transactionfee").setValue("RM " + transactionfee.toFixed(2));
          
        gridFormView.lookupReference("convertaqad-amount").setValue("RM " + finaltotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        // gridFormView.lookupReference("purityconv").setValue("999.9 (LBMA Standar)");
        // gridFormView.lookupReference("vaultconv").setValue("SG4S, Malaysia (Appointed Security Provider)");
        gridFormView.lookupReference("convertaqad-teller").setValue(snap.getApplication().username);
        gridFormView.lookupReference("convertaqad-finaltotal").setValue("RM" +finaltotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        // let address = Ext.getCmp("id-profile-address").getRawValue(); // get the display field by its ID
        //gridFormView.lookupReference("convertaqad-address").setValue(vm.get('profile-address'));
        gridFormView.lookupReference("convertaqad-contactno").setValue(vm.get('profile-phoneno'));
        // //convaddress
        // gridFormView.lookupReference("convpostcode").setValue("123123");
      // gridFormView.lookupReference("campaigncodeconv").setValue("goldconv");
        //debugger;


        if (PROJECTBASE == 'BSN'){
          if(quantity <= 30 &&  totalconversionvaluerow < 1000){
            //if(firstTimeConvert == 'yes'){
              if(totalconversionvaluerow >= 200){
                gridFormView.show();
              }else{
                Ext.MessageBox.show({
                  title: 'Error Message',
                  msg: 'Total redemption should Be exceed 200g',
                  buttons: Ext.MessageBox.OK,
                  icon: Ext.MessageBox.ERROR
                }); 
              }
              
            // }else{
            //   gridFormView.show();
            // }
            
          }else{
            if(quantity > 30) {
              Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'Quantity cannot exceed 30',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
              }); 
            }else if (totalconversionvaluerow > 1000){
              Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'Total Conversion cannot exceed 1000g',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
              });
            }
            
          }
        }else{
          gridFormView_buyaqad.lookupReference('print-button-bank-aqad').setHidden(true);
        }

    })
    .catch(function(error) {
      // Handle the error
      console.error(error);
    });


  },

//   _getConversionFeeOriginal: function(me, myView){
//       snap.getApplication().sendRequest({ hdl: 'myconversion', action: 'getConversionFee', 
//           payment_mode: 'fpx', 
//           accountholder_id: vm.get('profile-id'),
//           partnercode: PROJECTBASE,
//           // settlement_method: settlementMethod,
//           // uuid : price_arr['uuid'],
//           weight: vm.get("convert.weight"),
//           quantity: parseInt(elmnt.lookupReference('conversion-quantity').getValue()),
//           product_code: vm.get("convert.product"),
//           from_alert: false,
//       }, 'Retrieving conversion fees..')
//       .then(function(data){
//           if(data.success) {
//               me._loadConvertAqad(me, myView, data.data);
//           }
        
//       })
  
//   },

  _getConversionFee: function (me, myView) {
    
    return new Promise(function(resolve, reject) {
        // debugger;
      snap.getApplication().sendRequest(
          {
            hdl: "myconversion",
            action: "getConversionFee",
            payment_mode: "casa",
            accountholder_id: vm.get("profile-id"),
            partnercode: PROJECTBASE,
            weight: vm.get("convert.weight"),
            quantity: parseInt(
              elmnt.lookupReference("conversion-quantity").getValue()
            ),
            product_code: vm.get("convert.product"),
            from_alert: false,
          },
          "Retrieving conversion fees.."
        )
        .then(function (data) {
          if (data.success) {
            resolve(data); // Resolve the promise with the data
          } else {
            reject(new Error("Failed to retrieve conversion fees.")); // Reject the promise with an error message
          }
        })
        .catch(function (error) {
          reject(error); // Reject the promise with the error
        });
    });
  },

  _loadConvertAqad(baseView, myView, data){
      var gridFormView_convert = Ext.create(myView.formClass, Ext.apply(myView.orderpopupconvertaqad ? myView.orderpopupconvertaqad : {}, {
          formDialogButtons: [{
              xtype:'panel',
              flex:3
          },
          {
              text: 'Redeem Gold',
              flex: 2,
              handler: function(btn) {
                  baseView._makeConversion(btn, baseView, gridFormView_convert, data);
              }
          },
          // {
          //     text: 'Close',
          //     flex: 1,
          //     handler: function(btn) {
          //         owningWindow = btn.up('window');
          //         owningWindow.close();
          //         me.gridFormView_sell = null;
          //     }
          // },
          {
              xtype:'panel',
              flex: 2,
          }]
      }));

      // populate values 
      product = vm.get("convert.product");
      input_xau = vm.get("convert.weight");
      fee = 0;
      quantity = myView.lookupController().lookupReference('conversion-quantity').value;

      totalconversionvalue = myView.lookupController().lookupReference('totalconversionvalue').value;

      balanceafterconversion = myView.lookupController().lookupReference('balanceafterconversion').value;
      // conversionfee = myView.lookupController().lookupReference('conversionfee').value;

      dateconfirmed = new Date();
      datetext = dateconfirmed.toString('dddd', 'mmmm', 'yyyy');


      gridFormView_convert.lookupReference('convertaqad-date').setValue(datetext);
      gridFormView_convert.lookupReference('convertaqad-fullname').setValue(vm.get('profile-fullname'));
      gridFormView_convert.lookupReference('convertaqad-mykadno').setValue(vm.get('profile-mykadno'));
      gridFormView_convert.lookupReference('convertaqad-accountholdercode').setValue(vm.get('profile-accountholdercode'));
      // gridFormView_convert.lookupReference('convertaqad-xau').setValue(input_xau + ' gram');
      gridFormView_convert.lookupReference('convertaqad-fee').setValue('RM' + data.conversion_fee.toFixed(2));
      gridFormView_convert.lookupReference('convertaqad-transactionfee').setValue('RM' + data.transaction_fee.toFixed(2));
      gridFormView_convert.lookupReference('convertaqad-amount').setValue('RM' + data.total_fee.toFixed(2));
      gridFormView_convert.lookupReference('convertaqad-teller').setValue(snap.getApplication().username);
      gridFormView_convert.lookupReference('convertaqad-finaltotal').setValue('RM' + (input_xau * vm.get(PROJECTBASE+'_CHANNEL.companybuy') + fee).toFixed(2));

      this.gridFormView_convert = gridFormView_convert;
      this._formAction = "edit";

      var addEditForm = this.gridFormView_convert.down('form').getForm();

      gridFormView_convert.title = 'Redeem Gold';
      //debugger;
      this.gridFormView_convert.show();
  },

  // New function to perform search for registered accounts
  searchAccountHolder: function(btn, formAction) {
      var myView = this.getView(),
          me = this;
      // var sm = myView.getSelectionModel();
      // var selectedRecords = sm.getSelection();

      // set path
      // path = 'otcregisterview_' + PROJECTBASE;
      // grab form fields
      // form = myView.getController().lookupReference('otcregisterform');
      // data = form.getValues();

      // validate
    
      searchpanel = myView.getController().lookupReference('searchresults');
      accountholdersearch = myView.getController().lookupReference('accountholdersearch');
      accountholdersearchgrid = myView.getController().lookupReference('myaccountholdersearchresults');

      // get search parameters
      searchparams = accountholdersearch.value;
      // Add option
      if(PROJECTBASE != 'POSARRAHNU'){
        option = me.lookupReference('casasearchtype').getValue();
      }else{
        option = 1;
      }
      
      // do validation check
      // if all true, move on to post, else error
      if(searchparams !== "" && option !== null ){

          // Finalized
          // Replace proxy URL with selection
          accountholdersearchgrid.getStore().proxy.url = 'index.php?hdl=myaccountholder&action=getOtcAccountHolders&searchparams='+searchparams+'&option='+option+'&partner='+PROJECTBASE;
          accountholdersearchgrid.getStore().reload();
          //debugger;
          searchpanel.setHidden(false);
          // snap.getApplication().sendRequest({ hdl: 'myaccountholder', action: 'getOtcAccountHolders', 
          //     mykadno: searchparams, 
          //     partner: PROJECTBASE,

          // })
          // .then(function(data){
          //     if(data.success) {
          //         debugger;
          //         accounts = data.otcaccountholder;
                  
          //         // Ext.MessageBox.show({
          //         //         title: 'Registration Successful', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.INFO,
          //         //         msg: 'Account successfully registered'
          //         // });
          //         // displayCallback(data.record);

          //         // If succesful show search results and populate grid

          //         // Unhide grid
          //         // point to ekyc reminder form
          //         formEkycReminder = this.up().up().up().up().up().up().up().formEkycReminder;
          //         accountholderid = this.up().up().up().up().up().lookupReferenceHolder("kycmanualapproveremarks").theData.accountholderid
          //         accountholdername = this.up().up().up().up().up().lookupReferenceHolder("kycmanualapproveremarks").theData.accountholdername
          //         kycremindercount = this.up().up().up().up().up().lookupReferenceHolder("kycmanualapproveremarks").theData.kycremindercount
                  
          //         // override form 
          //         // Replace name
          //         formEkycReminder.formPanelItems[0].items[1].items[0].items[0].value = accountholdername;
          //         // Replace Record Count
          //         formEkycReminder.formPanelItems[0].items[1].items[0].items[1].value = kycremindercount;
          //         // Replace Store

          //         formEkycReminder.formPanelItems[0].items[2].items[0].store.proxy.url = 'index.php?hdl=mykycreminder&action=list&id='+accountholderid;
                
          //         // url: 'index.php?hdl=mykycreminder&action=list&id='+theData.accountholderid,
          //         var gridFormView = Ext.create('snap.view.gridpanel.GridForm', Ext.apply(formEkycReminder ? formEkycReminder : {}, {
          //           formDialogButtons: [{
          //               text: 'Close',
          //               handler: function(btn) {
          //                   owningWindow = btn.up('window');
          //                   owningWindow.close();
          //                   me.gridFormView = null;
          //               }
          //           },]
          //          }));

          //          // set value 
          //          if (partnerid != null) {			
          //             // If form not present, enable form
          //             if(elmnt.lookupReference('unfulfilledjlistpo').isHidden() == true){
          //                 // Clear init form
          //                 elmnt.lookupReference('unfulfilledjlistpo').getStore().removeAll();
                         
          //                 elmnt.lookupReference('unfulfilledjlistpo').setHidden(false);
              
          //             }//'index.php?hdl=order&action=getUnfulfilledStatementsForCustomer&partnerid='+partnerid
                      
          //             // Replace proxy URL with selection
          //             accountholdersearchgrid.getStore().proxy.url = 'index.php?hdl=myaccountholder&action=getOtcAccountHolders&mykadno='+searchparams;
          //             accountholdersearchgrid.getStore().reload();
                      
          //         } else {
          //             Ext.Msg.alert('ERROR-A1001', 'Please Select GTP Customer', Ext.emptyFn);			
          //         }	
                   
          //     }
          // })
          // me.redirectTo(path);

      }else{
        if ( searchparams== ""){
          Ext.MessageBox.show({
              title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
              msg: 'Search Field cannot be blank'
          });
        }else{
          Ext.MessageBox.show({
            title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
            msg: 'Please select search option'
        });
        }
          
      }
    
      // if (selectedRecords.length == 1) {
      //     for (var i = 0; i < selectedRecords.length; i++) {
      //         selectedID = selectedRecords[i].get('id');
      //         record = selectedRecords[i];
      //         me.redirectTo(path + '/accountholder/' + selectedID);
      //         break;
      //     }
      // } else {
      //     Ext.MessageBox.show({
      //         title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
      //         msg: 'Select a record first'
      //     });
      //     return;
      // }
},

  buyReceipt: function(data){
      
      var me = this;
      vm.set('isprint', false);
      if (data.statusstring != 'Confirmed'){
          vm.set('isprint', true);
      }

      
      pricedata = vm.get(PROJECTBASE + '_CHANNEL');

      // var form = elemnt.lookupController().lookupReference('spotorder-form').getForm();
      // //form2 = elemnt.lookupController().lookupReference('futureorder-form').getForm();
      
      // // Create forms
      // spotorder = form.getFieldValues();

      // /*------------------------ Ace Buy Display ------------------------------------*/

      // // Acquire Ace Sell 
      // acesell = spotorder.acesellprice; 

      var me = this, selectedRecord,
      myView = this.getView();
      // var sm = myView.getSelectionModel();
      
      // debugger;
      var gridFormView_buyReceipt = Ext.create(myView.formClass, Ext.apply(myView.orderpopupbuyReceipt ? myView.orderpopupbuyReceipt : {}, {
          formDialogButtons: [{
              xtype:'panel',
              flex:3
          },
          {
              text: 'Print',
              flex: 3,
              reference: 'print-button-bank',
              handler: function(btn) {
                //debugger;
                  me._printOrderPDFOTC(data.record.id, PROJECTBASE,'bank');
              }
          },
          // {
          //   text: 'Print (CUSTOMER COPY)',
          //   flex: 5,
          //   reference: 'print-button-cust',
          //   handler: function(btn) {
          //     //debugger;
          //       me._printOrderPDFOTC(data.record.id, PROJECTBASE,'customer');
          //   }
          // },
          {
              text: 'OK',
              flex: 3,
              reference: 'ok-button',
              handler: function(btn) {
                if(vm.get('isprint')){
                    btn.up('window').close();
                }else{
                    Ext.MessageBox.show({
                        title: 'Error Message',
                        msg: 'Please Print before Submit',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });  
                }
              }
          },
          // {
          //     text: 'Close',
          //     flex: 1,
          //     handler: function(btn) {
          //         owningWindow = btn.up('window');
          //         owningWindow.close();
          //         me.gridFormView = null;
          //     }
          // },
          {
              xtype:'panel',
              flex: 2,
          }]
      }));



      if (data.statusstring != 'Confirmed'){
          gridFormView_buyReceipt.lookupReference('print-button-bank').setHidden(true);
          //gridFormView_buyReceipt.lookupReference('print-button-cust').setHidden(true);
      }

      
      // populate values 
      //input_xau = gridForm.lookupController().lookupReference('companysellxau').value;

      dateconfirmed = new Date();
      datetext = dateconfirmed.toString('dddd', 'mmmm', 'yyyy');

      if(data.record.discountprice != 0){
        gridFormView_buyReceipt.lookupReference('buyReceipt-discountprice').setValue('RM' + data.record.discountprice.toFixed(2) +' / gram');
        gridFormView_buyReceipt.lookupReference('buyReceipt-discountAmount').setValue('RM' + data.record.discountAmount.toFixed(2));  
      }else{
        gridFormView_buyReceipt.lookupReference('buyReceipt-discountprice-field').setHidden(true);
        gridFormView_buyReceipt.lookupReference('buyReceipt-discountAmount-field').setHidden(true); 
        gridFormView_buyReceipt.lookupReference('buyReceipt-discountprice-field-line').setHidden(true);
        gridFormView_buyReceipt.lookupReference('buyReceipt-discountAmount-field-line').setHidden(true);  
      }
      
      gridFormView_buyReceipt.lookupReference('buyReceipt-date').setValue(datetext);
      gridFormView_buyReceipt.lookupReference('buyReceipt-fullname').setValue(vm.get('profile-fullname'));
      gridFormView_buyReceipt.lookupReference('buyReceipt-mykadno').setValue(vm.get('profile-mykadno'));
      gridFormView_buyReceipt.lookupReference('buyReceipt-accountholdercode').setValue(vm.get('profile-accountholdercode'));
      gridFormView_buyReceipt.lookupReference('buyReceipt-xau').setValue(data.record.xau.toFixed(3) + ' gram');
      gridFormView_buyReceipt.lookupReference('buyReceipt-price').setValue('RM' + data.record.price.toFixed(2) +' / gram');
      gridFormView_buyReceipt.lookupReference('buyReceipt-amount').setValue('RM' + data.record.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      gridFormView_buyReceipt.lookupReference('buyReceipt-teller').setValue(snap.getApplication().username);
      gridFormView_buyReceipt.lookupReference('buyReceipt-finaltotal').setValue('RM' + data.record.total_transaction_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      gridFormView_buyReceipt.lookupReference('buyReceipt-status').setValue(data.record.status);
      
      this.gridFormView_buyReceipt = gridFormView_buyReceipt;
      this._formAction = "edit";

      var addEditForm = this.gridFormView_buyReceipt.down('form').getForm();

      if(PROJECTBASE == 'BSN'){
        gridFormView_buyReceipt.title = 'Bank Sell Receipt';
      }else{
        gridFormView_buyReceipt.title = 'Customer Buy Receipt';
      }
      //debugger;
      this.gridFormView_buyReceipt.show();   
  },

  sellReceipt: function(data){
      
      var me = this;

      vm.set('isprint', false);

      if (data.statusstring != 'Confirmed'){
          vm.set('isprint', true);
      }

      pricedata = vm.get(PROJECTBASE + '_CHANNEL');

      // var form = elemnt.lookupController().lookupReference('spotorder-form').getForm();
      // //form2 = elemnt.lookupController().lookupReference('futureorder-form').getForm();
      
      // // Create forms
      // spotorder = form.getFieldValues();

      // /*------------------------ Ace Buy Display ------------------------------------*/


      // // Acquire Ace Sell 
      // acesell = spotorder.acesellprice; 

      var me = this, selectedRecord,
      myView = this.getView();
      // var sm = myView.getSelectionModel();
      
      // debugger;
      var gridFormView_sellReceipt = Ext.create(myView.formClass, Ext.apply(myView.orderpopupsellReceipt ? myView.orderpopupsellReceipt : {}, {
          formDialogButtons: [{
              xtype:'panel',
              flex:3
          },
          {
              text: 'Print',
              flex: 3,
              reference: 'print-button-bank',
              handler: function(btn) {
                  // debugger;
                  me._printOrderPDFOTC(data.record.id, PROJECTBASE,'bank');
              }
          },
          // {
          //   text: 'Print (CUSTOMER COPY)',
          //   flex: 5,
          //   reference: 'print-button-cust',
          //   handler: function(btn) {
          //     //debugger;
          //       me._printOrderPDFOTC(data.record.id, PROJECTBASE,'customer');
          //   }
          // },
          {
              text: 'OK',
              flex: 3,
              reference: 'ok-button',
              handler: function(btn) {
                if(vm.get('isprint')){
                    btn.up('window').close();
                }else{
                    Ext.MessageBox.show({
                        title: 'Error Message',
                        msg: 'Please Print before Submit',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });  
                }
              }
          },
          // {
          //     text: 'Close',
          //     flex: 1,
          //     handler: function(btn) {
          //         owningWindow = btn.up('window');
          //         owningWindow.close();
          //         me.gridFormView = null;
          //     }
          // },
          {
              xtype:'panel',
              flex: 2,
          }]
      }));

      if (data.statusstring != 'Confirmed'){
        gridFormView_sellReceipt.lookupReference('print-button-bank').setHidden(true);
        //gridFormView_sellReceipt.lookupReference('print-button-cust').setHidden(true);
      }

      // populate values 
      //input_xau = gridForm.lookupController().lookupReference('companysellxau').value;

      dateconfirmed = new Date();
      datetext = dateconfirmed.toString('dddd', 'mmmm', 'yyyy');

      gridFormView_sellReceipt.lookupReference('sellReceipt-date').setValue(datetext);
      gridFormView_sellReceipt.lookupReference('sellReceipt-fullname').setValue(vm.get('profile-fullname'));
      gridFormView_sellReceipt.lookupReference('sellReceipt-mykadno').setValue(vm.get('profile-mykadno'));
      gridFormView_sellReceipt.lookupReference('sellReceipt-accountholdercode').setValue(vm.get('profile-accountholdercode'));
      gridFormView_sellReceipt.lookupReference('sellReceipt-xau').setValue(data.record.xau.toFixed(3) + ' gram');
      gridFormView_sellReceipt.lookupReference('sellReceipt-price').setValue('RM' + vm.get(PROJECTBASE+'_CHANNEL.companybuy') +' / gram');
      gridFormView_sellReceipt.lookupReference('sellReceipt-amount').setValue('RM' + data.record.amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      gridFormView_sellReceipt.lookupReference('sellReceipt-teller').setValue(snap.getApplication().username);
      gridFormView_sellReceipt.lookupReference('sellReceipt-finaltotal').setValue('RM' + data.record.total_transaction_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      gridFormView_sellReceipt.lookupReference('sellReceipt-status').setValue(data.record.status);
      
      this.gridFormView_sellReceipt = gridFormView_sellReceipt;
      this._formAction = "edit";

      var addEditForm = this.gridFormView_sellReceipt.down('form').getForm();

      if(PROJECTBASE == 'BSN'){
        gridFormView_sellReceipt.title = 'Bank Buy Receipt';
      }else{
        gridFormView_sellReceipt.title = 'Customer Sell Receipt';
      }
      
      //debugger;
      this.gridFormView_sellReceipt.show();   
  },

  convertReceipt: function(data,return_data){
    var me = this;
    vm.set('isprint', false);
    // if (data.statusstring != 'Confirmed'){
    //   vm.set('isprint', true);
    // }

    var me = this, selectedRecord,
    myView = this.getView();
    // var sm = myView.getSelectionModel();
    
    // debugger;
    var gridFormView_convertReceipt = Ext.create(myView.formClass, Ext.apply(myView.orderpopupconvertReceipt ? myView.orderpopupconvertReceipt : {}, {
        formDialogButtons: [{
            xtype:'panel',
            flex:3
        },
        {
            text: 'Print',
            flex: 3,
            reference: 'print-button-bank',
            handler: function(btn) {
                // debugger;
                me._printConversionPDFOTC(data.record.conversions[0].refno, PROJECTBASE,'bank');
            }
        },
        // {
        //   text: 'Print (CUSTOMER COPY)',
        //   flex: 5,
        //   reference: 'print-button-cust',
        //   handler: function(btn) {
        //     //debugger;
        //       me._printConversionPDFOTC(data.record.conversions[0].refno, PROJECTBASE,'customer');
        //   }
        // },
        {
            text: 'OK',
            flex: 3,
            reference: 'ok-button',
            handler: function(btn) {
              if(vm.get('isprint')){
                  btn.up('window').close();
              }else{
                  Ext.MessageBox.show({
                      title: 'Error Message',
                      msg: 'Please Print before Submit',
                      buttons: Ext.MessageBox.OK,
                      icon: Ext.MessageBox.ERROR
                  });  
              }
            }
        },
        // {
        //     text: 'Close',
        //     flex: 1,
        //     handler: function(btn) {
        //         owningWindow = btn.up('window');
        //         owningWindow.close();
        //         me.gridFormView = null;
        //     }
        // },
        {
            xtype:'panel',
            flex: 2,
        }]
    }));

    // if (data.statusstring != 'Confirmed'){
    //   gridFormView_convertReceipt.lookupReference('print-button-bank').setHidden(true);
    //   gridFormView_convertReceipt.lookupReference('print-button-cust').setHidden(true);
    // }

    // populate values 
    //input_xau = gridForm.lookupController().lookupReference('companysellxau').value;

    // debugger;
    dateconfirmed = new Date();
    datetext = dateconfirmed.toString('dddd', 'mmmm', 'yyyy');

    gridFormView_convertReceipt.lookupReference('convertReceipt-date').setValue(datetext);
    gridFormView_convertReceipt.lookupReference('convertReceipt-fullname').setValue(vm.get('profile-fullname'));
    gridFormView_convertReceipt.lookupReference('convertReceipt-mykadno').setValue(vm.get('profile-mykadno'));
    gridFormView_convertReceipt.lookupReference('convertReceipt-contactno').setValue(vm.get('profile-phoneno'));
    gridFormView_convertReceipt.lookupReference('convertReceipt-accountholdercode').setValue(vm.get('profile-accountholdercode'));
    gridFormView_convertReceipt.lookupReference('convertReceipt-xau').setValue(data.record.conversions[0].total_weight + ' gram');
    gridFormView_convertReceipt.lookupReference('convertReceipt-makingcharges').setValue('RM' + parseFloat(data.fees.insuransfee).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    gridFormView_convertReceipt.lookupReference('convertReceipt-deliveryfee').setValue('RM' + parseFloat(data.fees.courierCharges).toFixed(2));
    gridFormView_convertReceipt.lookupReference('convertReceipt-fee').setValue('RM' + parseFloat(data.fees.redemptionfee).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    gridFormView_convertReceipt.lookupReference('convertReceipt-amount').setValue('RM' + parseFloat(return_data.final_total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    gridFormView_convertReceipt.lookupReference('convertReceipt-teller').setValue(snap.getApplication().username);
    gridFormView_convertReceipt.lookupReference('convertReceipt-finaltotal').setValue('RM' + parseFloat(return_data.final_total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    gridFormView_convertReceipt.lookupReference('convertReceipt-status').setValue(return_data.statusstring);
    gridFormView_convertReceipt.lookupReference('convertReceipt-address').setValue(return_data.address);
    
    this.gridFormView_convertReceipt = gridFormView_convertReceipt;
    this._formAction = "edit";

    var addEditForm = this.gridFormView_convertReceipt.down('form').getForm();

    gridFormView_convertReceipt.title = 'Customer Redemption Receipt';
    //debugger;
    this.gridFormView_convertReceipt.show();   
  },

  // buy/ sell function for aqad dashboard
  _makeOrder: function(btn, gridForm, gridFormView, orderType = false, ) {
  
      var myView = this.getView(),
          me = this,
          myButton = btn;
      vm.set('makeorderform_1', gridForm);
      vm.set('makeorderform_2', gridFormView);
      vm.set('me', me);
      partner_data = '';
      // var sm = myView.getSelectionModel();
      // var selectedRecords = sm.getSelection();

      // set path
      // path = 'otcregisterview_' + PROJECTBASE;
      // grab form fields
      // form = myView.getController().lookupReference('otcregisterform');
      // data = form.getValues();

      // validate
      
      // searchpanel = myView.getController().lookupReference('searchresults');
      // accountholdersearch = myView.getController().lookupReference('accountholdersearch');
      // accountholdersearchgrid = myView.getController().lookupReference('myaccountholdersearchresults');
      
      // grab price for order
      price_arr = vm.get('currentuuid');
      referralsalespersoncode = null;
      referralintroducercode = null;
      // If ordertype = true, companysell
      if(orderType){
          type = 'companysell';
          aqadType = "buyaqad";
          settlementMethod = 'casa';
          weight = parseFloat(input_xau);
         
      }else{
          type = 'companybuy';
          aqadType = "sellaqad";
          settlementMethod = 'casa';
          weight = parseFloat(input_xau);
      }

      //debugger;

      campaignCode = gridFormView.lookupReference(aqadType+'-campaigncode').getValue();
      pinForm = gridFormView.lookupReference(aqadType+'-securitypin');

      // at the moment only bsn use this
      if(PROJECTBASE == 'BSN'){
          if(aqadType == 'buyaqad'){ 

            referralsalespersoncode = gridForm.lookupReference('buyaqad-referralsalespersoncode').getValue();
            // referralintroducercode = gridForm.lookupReference('buyaqad-referralintroducercode').getValue();

            if(referralsalespersoncode == ''){
              Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Salesperson ID cannot be empty'
              });
              return
            }

	  }
          // pin = vm.get('profile-mykadno').substring(6,12);
          currentmykadno = vm.get('profile-mykadno').replace(/\D/g,"");	
          pin = currentmykadno.substr(currentmykadno.length - 6);	
      }else{
        // pin = '123123';

        gridFormView = vm.get('makeorderform_2')
        var pin = '';
        var pinFields = [
            'init_pin_1',
            'init_pin_2',
            'init_pin_3',
            'init_pin_4',
            'init_pin_5',
            'init_pin_6'
        ];

        pinFields.forEach(function(fieldReference) {
            var field = gridFormView.lookupReference(fieldReference);
            var fieldValue = field.getValue();
            pin += fieldValue;
        });

        if(PROJECTBASE == 'ALRAJHI'){
          partner_data = gridFormView.lookupReference(aqadType+'-accountselection').getValue();
        }
        
      }

      if(PROJECTBASE == 'POSARRAHNU'){
        settlementMethod = 'cash';
      }
      //debugger;
      // get aqad values
      
      

      // get search parameters
      // searchparams = accountholdersearch.value;
      // do validation check
      // if all true, move on to post, else error
      //if(pinvalidation){

          // Finalized
          // Replace proxy URL with selection
          snap.getApplication().sendRequest({ hdl: 'myorder', action: 'doOtcOrders', 
              is_order_type_sell: orderType, 
              accountholder_id: vm.get('profile-id'),
              partnercode: PROJECTBASE,
              settlement_method: settlementMethod,
              uuid : price_arr,
              weight: weight,
              campaign_code: campaignCode,
              pin: pin,
              product_code: "DG-999-9",
              from_alert: false,
              referralsalespersoncode: referralsalespersoncode,
              referralintroducercode: referralintroducercode,
              partner_data: partner_data,
          }, 'Submiting order....')
          .then(function(data){
              if(data.success) {
                  //debugger;
                  if (data.isawait){          
                      // Ext.MessageBox.wait('Wating For Approval...', 'Please wait', {
                      //     icon: 'my-loading-icon'
                      // });
                      // Ext.MessageBox.show({
                      //     title: 'Please Wait', 
                      //     msg: 'Loading...',
                      //     progressText: 'Loading...',
                      //     width: 300,
                      //     progress: true,
                      //     closable: false,
                      //     icon: 'my-loading-icon'
                      // });
                      Ext.MessageBox.wait('Waiting For Approval...', 'Please wait', {
                          icon: 'my-loading-icon'
                      });
                      
                      const url = 'index.php?hdl=myorder&action=checkApprovalStatus&id='+data.id+'&approve=yes';
                      const intervalId = setInterval(async () => {
                          try {
                            const response = await Ext.Ajax.request({
                              url: url,
                              method: 'GET'
                            });
                            const data = Ext.JSON.decode(response.responseText);

                            // if pending = false, trx is approved
                            if (!data.ispendingapproval) {
                              clearInterval(intervalId);
                              console.log('Approval process complete');
                              if(data.ispurchasesuccessful){
                                  Ext.MessageBox.show({
                                          title: 'Order Successful', buttons: Ext.MessageBox.OK,
                                          iconCls: 'x-fa fa-check-circle',
                                          msg: 'Proceed to receipt',
                                          callback:function() { 
                                              me.buyReceipt(data);
                                          }
                                  });
                                  return
                                  // console.log(data);
                              }else{
                                  Ext.MessageBox.show({
                                          title: 'Order Unsuccessful', buttons: Ext.MessageBox.OK,
                                          iconCls: 'x-fa fa-check-circle',
                                          msg: 'Proceed to receipt',
                                          callback:function() { 
                                              me.buyReceipt(data);
                                          }
                                  });
                                  return
                                  // console.log(data);
                              }
                             
                              // Do something with the data
                            }
                          } catch (error) {
                            console.log('Request failed');
                          }
                       }, 10000); // interval set to 10 seconds
                        
                      // Schedule a function to be called after 5 minutes to end the process
                      // setTimeout(() => {
                      //     clearInterval(intervalId);
                      //     Ext.MessageBox.alert('Error', 'No response from server after 5 minutes');
                      // }, 5 * 60 * 1000); // 5 minutes in milliseconds
                      
                      // Ext.Ajax.request({
                      //     url: url,
                      //     method: 'GET',
                      //     success: function(response) {
                      //         // Handle the successful response here
                      //         debugger;
                      //         var data = Ext.JSON.decode(response.responseText);
                      //         //ispendingapproval
                      //         // if ispendingapproval is true, keep calling and loop ajax

                      //         // if else then check if ispaymentsuccessful is true
                      //         console.log(data);
                      //     },
                      //     failure: function(response) {
                      //         // Handle the failed response here
                      //         console.log('Request failed');
                      //     }
                      // });
                      
                  }else{
                      // Perhaps can add print order
                      //debugger;

                      Ext.MessageBox.wait('Waiting For Payment...', 'Please wait', {
                        icon: 'my-loading-icon'
                      });
                    
                      const url = 'index.php?hdl=myorder&action=checkApprovalStatus&id='+data.id+'&approve=no';
                      const intervalId = setInterval(async () => {
                          try {
                            const response = await Ext.Ajax.request({
                              url: url,
                              method: 'GET'
                            });
                            const return_data = Ext.JSON.decode(response.responseText);

                            // if pending = false, trx is approved
                            if (!return_data.ispendingpayment) {
                                clearInterval(intervalId);
                                console.log('Approval process complete');
                                
                                if(return_data.ispurchasesuccessful){
                                    Ext.MessageBox.show({
                                        title: 'Order Successful', buttons: Ext.MessageBox.OK,
                                        iconCls: 'x-fa fa-check-circle',
                                        msg: 'Proceed to receipt',
                                        callback:function() { 
                                            if(data.orderType == 'CompanySell'){
                                                me.buyReceipt(return_data);
                                            }else{
                                                me.sellReceipt(return_data);
                                            }
                                            
                                        }
                                    });
                                    return
                                    // console.log(data);
                                }else{
                                  //debugger;
                                    Ext.MessageBox.show({
                                            title: 'Order Unsuccessful', buttons: Ext.MessageBox.OK,
                                            iconCls: 'x-fa fa-check-circle',
                                            msg: 'Proceed to receipt',
                                            callback:function() { 
                                                if(data.orderType == 'CompanySell'){
                                                    me.buyReceipt(return_data);
                                                }else{
                                                    me.sellReceipt(return_data);
                                                }
                                            }
                                    });
                                    return
                                    // console.log(data);
                                }
                            
                                // Do something with the data
                            }
                          } catch (error) {
                            console.log('Request failed');
                          }
                       }, 10000); // interval set to 10 seconds
  
                       
                  }         

                  //ajax


                  // myButton.up('window').close();
                  vm.get('makeorderform_1').close();
                  vm.get('makeorderform_2').close();
                  
                  // record = data.record;

     
                  // displayCallback(data.record);
              }
          })
          // me.redirectTo(path);

      //}else{
      //    Ext.MessageBox.show({
      //        title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
      //        msg: 'Please fill in pin fields'
      //    });
      //}
    
      // if (selectedRecords.length == 1) {
      //     for (var i = 0; i < selectedRecords.length; i++) {
      //         selectedID = selectedRecords[i].get('id');
      //         record = selectedRecords[i];
      //         me.redirectTo(path + '/accountholder/' + selectedID);
      //         break;
      //     }
      // } else {
      //     Ext.MessageBox.show({
      //         title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
      //         msg: 'Select a record first'
      //     });
      //     return;
      // }
},

  _makeConversion(btn, myView, gridFormView_convert, data){
      // var myView = this.getView(),

      me = this,
      // myButton = btn;
      vm.set('makeconvertform_1', myView);
      vm.set('makeconvertform_2', gridFormView_convert);
      // grab price for order
      // price_arr = vm.get(PROJECTBASE + '_CHANNEL');
      // input = vm.get('input');
      // If ordertype = true, companysell

      // if(orderType){
      //     type = 'companysell';
      //     aqadType = "buyaqad";
      //     settlementMethod = 'casa';
      //     weight = parseFloat(input['companysellxau']);
      //     campaignCode = gridFormView.lookupReference(aqadType+'-campaigncode').getValue();
      //     pinForm = gridFormView.lookupReference(aqadType+'-securitypin');
      // }else{
      //     type = 'companybuy';
      //     aqadType = "sellaqad";
      //     settlementMethod = 'casa';
      //     weight = parseFloat(input['companybuyxau']);
      //     campaignCode = gridFormView.lookupReference(aqadType+'-campaigncode').getValue();
      //     pinForm = gridFormView.lookupReference(aqadType+'-securitypin');
      // }

      campaignCode = gridFormView_convert.lookupReference('convertaqad-campaigncode').getValue();

      address1 = gridFormView_convert.lookupReference('convertaqad-address1').getValue();
      address2 = gridFormView_convert.lookupReference('convertaqad-address2').getValue();
      postcode = gridFormView_convert.lookupReference('convertaqad-postcode').getValue();
      state = gridFormView_convert.lookupReference('convertaqad-state').getValue();

      conversion_fee = data.data.conversion_fee;
      payment_mode = data.data.payment_mode;
    //   debugger;
      // payment_mode = 'casa';
      total_fee = data.data.total_fee;
      transaction_fee = data.data.transaction_fee;
      quantity = myView.lookupController().lookupReference('conversion-quantity').value;
      totalconversionvalue = myView.lookupController().lookupReference('totalconversionvalue').value;
      // get aqad values
      pinForm = gridFormView_convert.lookupReference('convertaqad-securitypin');
      //   pinArr = pinForm.getValues()[getText('enterpassword')];
      //   pin = pinArr.join("");
      // pin = vm.get('profile-mykadno').substring(6,12);
      currentmykadno = vm.get('profile-mykadno').replace(/\D/g,"");	
      pin = currentmykadno.substr(currentmykadno.length - 6);	
      weight = vm.get('convert.weight');
      product = vm.get('convert.product');
  
      // get search parameters
      // searchparams = accountholdersearch.value;
      // do validation check
      // if all true, move on to post, else error
      if(pin.length == 6 && address1 != '' && address2 != '' && postcode != '' && state != ''){
          // Finalized
          // Replace proxy URL with selection
          snap.getApplication().sendRequest({ hdl: 'myconversion', action: 'doOtcConversion', 
              // is_order_type_sell: orderType, 
              accountholder_id: vm.get('profile-id'),
              partnercode: PROJECTBASE,
              payment_mode: payment_mode,
              // uuid : price_arr['uuid'],
              weight: weight,
              campaign_code: campaignCode,
              address1: address1,
              address2: address2,
              postcode: postcode,
              state: state,
              pin: pin,
              product_code: product,
              quantity: quantity,
              // from_alert: false,
          }, 'Submiting order....')
          .then(function(data){

              if(data.success) {



                Ext.MessageBox.wait('Waiting For Payment...', 'Please wait', {
                  icon: 'my-loading-icon'
                });
              
                const url = 'index.php?hdl=myconversion&action=checkConversionStatus&refno='+data.record.conversions[0].refno;
                const intervalId = setInterval(async () => {
                    try {
                      const response = await Ext.Ajax.request({
                        url: url,
                        method: 'GET'
                      });
                      const return_data = Ext.JSON.decode(response.responseText);

                      // if pending = false, trx is approved
                      if (!return_data.ispendingpayment) {
                          clearInterval(intervalId);
                          console.log('Payment process complete');
                          
                          if(return_data.isconvertsuccessful){
                              Ext.MessageBox.show({
                                  title: 'Conversion Successful', buttons: Ext.MessageBox.OK,
                                  iconCls: 'x-fa fa-check-circle',
                                  msg: 'Proceed to receipt',
                                  callback:function() { 
                                      me.convertReceipt(data,return_data); // receipt not yet added
                                      // can refer to buyreceipt and sellreceipt
                                  }
                              });
                              return
                              // console.log(data);
                          }else{
                              Ext.MessageBox.show({
                                  title: 'Conversion Unsuccessful', buttons: Ext.MessageBox.OK,
                                  iconCls: 'x-fa fa-check-circle',
                                  msg: 'Proceed to receipt',
                                  callback:function() { 
                                      me.convertReceipt(data,return_data); // receipt not yet added
                                      // can refer to buyreceipt and sellreceipt
                                  }
                              });
                              return
                              // console.log(data);
                          }
                      
                          // Do something with the data
                      }
                    } catch (error) {
                      console.log('Request failed');
                    }
                 }, 10000); // interval set to 10 seconds

                  
                  // return
              

                  //ajax

                  // myButton.up('window').close();
                  
                  
                  // record = data.record;

  
                  // displayCallback(data.record);
              }
              vm.get('makeconvertform_2').close();

          })
          // me.redirectTo(path);

      }else{
        if(pin.length != 6){
          Ext.MessageBox.show({
              title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
              msg: 'Please fill in pin fields'
          });
        }else{
          Ext.MessageBox.show({
            title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
            msg: 'Please fill in all address details fields'
        });
        }
      }
  
      // if (selectedRecords.length == 1) {
      //     for (var i = 0; i < selectedRecords.length; i++) {
      //         selectedID = selectedRecords[i].get('id');
      //         record = selectedRecords[i];
      //         me.redirectTo(path + '/accountholder/' + selectedID);
      //         break;
      //     }
      // } else {
      //     Ext.MessageBox.show({
      //         title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
      //         msg: 'Select a record first'
      //     });
      //     return;
      // }
  },

  _printOrderPDFOTC: function(orderid, partnercode,footer) {
      // var owningWindow = btn.up('window');
      // var gridFormPanel = owningWindow.down('form');
      var me = this;
      vm.set('isprint', true);
      //debugger;
      // Get Printable data
      // orderid = btn.up().up().items.items[0].items.items[4].getValue();

      var url = 'index.php?hdl=myorder&action=printSpotOrderOTC&orderid='+orderid+'&partnercode='+partnercode+'&footer='+footer;
      Ext.Ajax.request({
        url: url,
        method: 'get',
        waitMsg: 'Processing',
        //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
        autoAbort: false,
        success: function (result) {
                      //debugger;
           // if(PROJECTBASE != 'POSARRAHNU'){
           //   window.location.href = result.responseText;
           // }else{
           //   var win = window.open('');
           //   win.location = url;
           //   win.focus();
           // }
	    var responseData = Ext.decode(result.responseText);
                        if (responseData.success === true) {
                            var win = window.open('');
                            win.location = responseData.url;
                           win.focus();
                        }else {
                            // The request was successful, but the response indicates failure
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: responseData.errorMessage, // You can customize the error message based on the response
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
        },
        failure: function () {
          
          Ext.MessageBox.show({
            title: 'Error Message',
            msg: 'Failed to retrieve data',
            buttons: Ext.MessageBox.OK,
            icon: Ext.MessageBox.ERROR
          });
        }
      });

  },

  _printConversionPDFOTC: function(refno, partnercode,footer) {
    // var owningWindow = btn.up('window');
    // var gridFormPanel = owningWindow.down('form');
    var me = this;
    vm.set('isprint', true);
    //debugger;
    // Get Printable data
    // orderid = btn.up().up().items.items[0].items.items[4].getValue();

    var url = 'index.php?hdl=myconversion&action=printConversionOTC&refno='+refno+'&partnercode='+partnercode+'&footer='+footer;
    Ext.Ajax.request({
      url: url,
      method: 'get',
      waitMsg: 'Processing',
      //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
      autoAbort: false,
      success: function (result) {
                    //debugger;
          //if(PROJECTBASE != 'POSARRAHNU'){
          //  window.location.href = result.responseText;
          //}else{
          //  var win = window.open('');
          //  win.location = url;
          //  win.focus();
          //}
	    var responseData = Ext.decode(result.responseText);
                        if (responseData.success === true) {
                            var win = window.open('');
                            win.location = responseData.url;
                           win.focus();
                        }else {
                            // The request was successful, but the response indicates failure
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: responseData.errorMessage, // You can customize the error message based on the response
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
      },
      failure: function () {
        
        Ext.MessageBox.show({
          title: 'Error Message',
          msg: 'Failed to retrieve data',
          buttons: Ext.MessageBox.OK,
          icon: Ext.MessageBox.ERROR
        });
      }
    });

  },

  _printAqad: function(gridFormView, partnercode, type, footer) {
      // var owningWindow = btn.up('window');
      // var gridFormPanel = owningWindow.down('form');
      var me = this;
      vm.set('isprintaqad', true);

      data = {};
      data['date'] = gridFormView.lookupReference(type + 'aqad-date').getValue();
      data['fullname'] = gridFormView.lookupReference(type + 'aqad-fullname').getValue();
      data['mykadno'] = gridFormView.lookupReference(type + 'aqad-mykadno').getValue();
      data['accountnumber'] = vm.get('profile-accountnumber');
      data['accountholdercode'] = gridFormView.lookupReference(type + 'aqad-accountholdercode').getValue();
      data['teller'] = gridFormView.lookupReference(type + 'aqad-teller').getValue();
      data['finaltotal'] = gridFormView.lookupReference(type + 'aqad-finaltotal').getValue();
      data['xau'] = gridFormView.lookupReference(type + 'aqad-xau').getValue();
      data['amount'] = gridFormView.lookupReference(type + 'aqad-amount').getValue();
      data['price'] = gridFormView.lookupReference(type + 'aqad-price').getValue();
      data['type'] = type;
      data['footer'] = footer;
      
      json_data = JSON.stringify(data);
  
      // Get Printable data
      // orderid = btn.up().up().items.items[0].items.items[4].getValue();

      if(type == 'convert'){
        //var url = 'index.php?hdl=myconversion&action=printConversionOTC&partnercode='+partnercode+'&data='+json_data;
        data['transaction_fee'] = gridFormView.lookupReference(type + 'aqad-transactionfee').getValue();
      }else{
        var url = 'index.php?hdl=myorder&action=printSpotOrderOTC&partnercode='+partnercode+'&data='+json_data;
        data['price'] = gridFormView.lookupReference(type + 'aqad-price').getValue();
      }
      
      Ext.Ajax.request({
        url: url,
        method: 'get',
        waitMsg: 'Processing',
        //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
        autoAbort: false,
        success: function (result) {
                      //debugger;

            //if(PROJECTBASE != 'POSARRAHNU'){
            //  window.location.href = result.responseText;
            //}else{
            //  var win = window.open('');
            //  win.location = url;
            //  win.focus();
            //}
		var responseData = Ext.decode(result.responseText);
                        if (responseData.success === true) {
                            var win = window.open('');
                            win.location = responseData.url;
                           win.focus();
                        }else {
                            // The request was successful, but the response indicates failure
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: responseData.errorMessage, // You can customize the error message based on the response
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
        },
        failure: function () {
          
          Ext.MessageBox.show({
            title: 'Error Message',
            msg: 'Failed to retrieve data',
            buttons: Ext.MessageBox.OK,
            icon: Ext.MessageBox.ERROR
          });
        }
      });

  },

  _printConvertAqad: function(myView, gridFormView, partnercode, footer) {
    // var owningWindow = btn.up('window');
    // var gridFormPanel = owningWindow.down('form');
    var me = this;
    

    data = {};
    data['date'] = gridFormView.lookupReference('convertaqad-date').getValue();
    data['fullname'] = gridFormView.lookupReference('convertaqad-fullname').getValue();
    data['mykadno'] = gridFormView.lookupReference('convertaqad-mykadno').getValue();
    data['accountholdercode'] = gridFormView.lookupReference('convertaqad-accountholdercode').getValue();
    data['accountnumber'] = vm.get('profile-accountnumber');
    data['teller'] = gridFormView.lookupReference('convertaqad-teller').getValue();
    data['finaltotal'] = gridFormView.lookupReference('convertaqad-finaltotal').getValue();
    data['xau'] = gridFormView.lookupReference('convertaqad-xau').getValue();
    data['amount'] = gridFormView.lookupReference('convertaqad-amount').getValue();
    data['redemptionfee'] = gridFormView.lookupReference("convertaqad-fee").getValue();
    data['makingcharges'] = gridFormView.lookupReference("convertaqad-makingcharges").getValue();
    data['deliveryfee'] = gridFormView.lookupReference("convertaqad-deliveryfee").getValue();
    data['footer'] = footer;
    
    address1 = gridFormView.lookupReference("convertaqad-address1").getValue();
    address2 = gridFormView.lookupReference("convertaqad-address2").getValue();
    postcode = gridFormView.lookupReference("convertaqad-postcode").getValue();
    state = gridFormView.lookupReference("convertaqad-state").getValue();

    data['address'] = address1+', '+address2+' '+postcode+' '+state;

    data['quantity'] = myView.lookupController().lookupReference('conversion-quantity').value;
    product = vm.get('convert.product');
    data['product'] = product.match(/(\d+(\.\d+)?g)/)[0];
    
    json_data = JSON.stringify(data);

    // Get Printable data
    // orderid = btn.up().up().items.items[0].items.items[4].getValue();


    if(address1 != '' && address2 != '' && postcode != '' && state != ''){

      vm.set('isprintaqad', true);
      var url = 'index.php?hdl=myconversion&action=printConversionOTC&partnercode='+partnercode+'&data='+json_data;
      //data['transaction_fee'] = gridFormView.lookupReference('convertaqad-transactionfee').getValue();

      
      Ext.Ajax.request({
        url: url,
        method: 'get',
        waitMsg: 'Processing',
        //params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
        autoAbort: false,
        success: function (result) {

          //if(PROJECTBASE != 'POSARRAHNU'){
          //  window.location.href = result.responseText;
          //}else{
          //  var win = window.open('');
          //  win.location = url;
          //  win.focus();
          //}
            var responseData = Ext.decode(result.responseText);
                        if (responseData.success === true) {
                            var win = window.open('');
                            win.location = responseData.url;
                           win.focus();
                        }else {
                            // The request was successful, but the response indicates failure
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: responseData.errorMessage, // You can customize the error message based on the response
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }

        },
        failure: function () {
          
          Ext.MessageBox.show({
            title: 'Error Message',
            msg: 'Failed to retrieve data',
            buttons: Ext.MessageBox.OK,
            icon: Ext.MessageBox.ERROR
          });
        }
      });

    }else{
      Ext.MessageBox.show({
        title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
        msg: 'Please fill in all address details fields'
      });
    }
  },

  ManualDeduction: function(btn, formAction, id){
    var me = this, selectedRecord,
    myView = this.getView();

    partnerCode = myView.partnercode; 
            
    var sm = myView.getSelectionModel();
    var selectedRecords = sm.getSelection();

    type = btn.reference;

    if (selectedRecords.length == 1) {
      for (var i = 0; i < selectedRecords.length; i++) {
          selectedID = selectedRecords[i].get('id');
          selectedRecord = selectedRecords[i];
          break;
      }
      trxid = selectedRecords[0].data.id;

    } else if ('add' != formAction) {
        Ext.MessageBox.show({
            title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
            msg: 'Select a record first'
        });
        return;
    }

    store = myView.getStore();
    var record = store.findRecord('id', trxid);

    if (record) {
        // Do something with the record...
        achfullname = record.data.achfullname;
        achaccountholdercode = record.data.achaccountholdercode;
        partnername = record.data.partnername;
        amount = record.data.amount;
    }else{
        Ext.MessageBox.show({
            title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
            msg: 'No Records Found'
        });
        return;
    }

    var gridFormViewManualDeduction = Ext.create(myView.formClass, Ext.apply(myView.formManualDeduction ? myView.formManualDeduction : {}, {
      formDialogButtons: [{
          xtype: 'panel',
          flex: 1
      },
      {
          text: 'Confirm',
          flex: 2,
          handler: function (modalBtn) {
              var sm = myView.getSelectionModel();
              var selectedRecords = sm.getSelection();

              Ext.MessageBox.confirm(
                  'Confirm Management Fee Payment', 'Are you sure you want to approve ?', function (btn) {
                    if (btn === 'yes') {
                      snap.getApplication().sendRequest({
                          hdl: 'myoutstandingmanagementfee', 'action': 'PayManagementFee', 
                          // id: trxid, 
                          // 'remarks': remarks, 
                          // is_order_type_sell: 'acebuy',
                          accountholder_id: record.data.achaccountholderid,
                          partnercode: PROJECTBASE,
                          // settlement_method: settlementMethod,
                          // product_code: "DG-999-9",
                          // from_alert: false,
                          // partner_data: record.data.achaccountnumber,
                          // pdtamount: record.data.pdtamount,

                      }, 'Sending request....').then(
                              function (data) {
                                //debugger;
                                if (data.isawait){          

                                  Ext.MessageBox.wait('Waiting For Approval...', 'Please wait', {
                                      icon: 'my-loading-icon'
                                  });
                                  
                                  const url = 'index.php?hdl=myorder&action=checkApprovalStatus&id='+data.id+'&approve=yes';
                                  const intervalId = setInterval(async () => {
                                      try {
                                        const response = await Ext.Ajax.request({
                                          url: url,
                                          method: 'GET'
                                        });
                                        const responseData = Ext.JSON.decode(response.responseText);
            
                                        // if pending = false, trx is approved
                                        if (!responseData.ispendingapproval) {
                                          clearInterval(intervalId);
                                          console.log('Approval process complete');
                                          if(responseData.ispurchasesuccessful){
                                              Ext.MessageBox.show({
                                                      title: 'Successful', buttons: Ext.MessageBox.OK,
                                                      iconCls: 'x-fa fa-check-circle',
                                                      msg: 'Force Sell Successful',
                                                      // callback:function() { 
                                                      //   me.sellReceipt(responseData);
                                                        
                                                      // }
                                              });
                                              gridFormViewManualDeduction.close();
											  myView.getStore().reload();
                                              return
                                              // console.log(data);
                                          }else{
                                              Ext.MessageBox.show({
                                                      title: 'Unsuccessful', buttons: Ext.MessageBox.OK,
                                                      iconCls: 'x-fa fa-check-circle',
                                                      msg: responseData.errorMessage ? responseData.errorMessage : 'Force Sell Unsuccessful',
                                                      // callback:function() { 
                                                      //   me.sellReceipt(responseData);
                                                        
                                                      // }
                                              });
                                              gridFormViewManualDeduction.close();
											  myView.getStore().reload();
                                              return
                                              // console.log(data);
                                          }
                                         
                                          // Do something with the data
                                        }
                                      } catch (error) {
                                        console.log('Request failed');
                                      }
                                   }, 10000); // interval set to 10 seconds  
                                  
                              }
                      });
                    
                        
                        
                    }
                  });
          }
      },
      {
          text: 'Close',
          flex: 2,
          handler: function (btn) {
              owningWindow = btn.up('window');
              owningWindow.close();
              me.gridFormView = null;
          }
      },{
        xtype: 'panel',
        flex: 1
      }]
    }));

    gridFormViewManualDeduction.controller.getView().lookupReference('achfullname').setValue(achfullname);
    gridFormViewManualDeduction.controller.getView().lookupReference('achaccountholdercode').setValue(achaccountholdercode);
    gridFormViewManualDeduction.controller.getView().lookupReference('partnername').setValue(partnername);
    gridFormViewManualDeduction.controller.getView().lookupReference('amount').setValue(amount);

    gridFormViewManualDeduction.show();
  }
});
