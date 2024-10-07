Ext.define('snap.view.analyticsdata.OTCAnalyticsDataController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.analyticsdata-analyticsdata',

 // New function to perform search for registered accounts
    searchPriceHistory: function(btn, formAction) {
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
        searchparams = [];

        searchpanel = myView.getController().lookupReference('searchresults');
        startDate = myView.getController().lookupReference('startDate');
        endDate = myView.getController().lookupReference('endDate');
        pricehistorygraph = myView.getController().lookupReference('pricehistorygraph');
        // accountholdersearchgrid = myView.getController().lookupReference('myaccountholdersearchresults');

        // get search parameters
        searchparams = {
            'date_from' : startDate.value,
            'date_to' : endDate.value
        }

        // do validation check
        // if all true, move on to post, else error
        if(searchparams && Object.keys(searchparams).length ){
  
            // Finalized
            // Replace proxy URL with selection
            // accountholdersearchgrid.getStore().proxy.url = 'index.php?hdl=myaccountholder&action=getOtcAccountHolders&mykadno='+searchparams+'&partner='+PROJECTBASE;
            // accountholdersearchgrid.getStore().reload();
            
            // searchpanel.setHidden(false);
            snap.getApplication().sendRequest({ hdl: 'myhistoricalprice', action: 'getPriceHistory', 
                page_size: 1, 
                page_number: 1,
                date_from: searchparams['date_from'],
                date_to: searchparams['date_to'], 
                partnercode: PROJECTBASE,
            })
            .then(function(data){
                if(data.success) {
           
                    records = data.records;
                    
                    // load data
                    pricehistorygraph.store.loadData(records);

                    // Ext.MessageBox.show({
                    //         title: 'Registration Successful', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.INFO,
                    //         msg: 'Account successfully registered'
                    // });
            
                }
            })
            // me.redirectTo(path);

        }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Search Field cannot be blank'
            });
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

    onSeriesTooltipRender: function (tooltip, record, item) {
        var title = item.series.getTitle();
        // item.series.getYField()
        tooltip.setHtml(title + ' on ' + record.get('date') + ': ' +
            'RM' + record.get('close_sell').toFixed(3));
    },

    fetchValueDashboardData: function(filter = false, allstate = false, filterstate = null) {
        var me = this;
        snap
          .getApplication()
          .sendRequest({
            hdl: 'myhistoricalprice',
            action: 'getvaluedashboard',
            partnercode: PROJECTBASE,
            filter: filter,
            state: allstate ? allstate : filterstate
          }, 'Fetching data from server....')
          .then(function(data) {
            // console.log(data);
            if (data.success) {
              // Data is received successfully
              var viewModel = me.getView().getViewModel();
              var newData = {
                totalAccountHolder: data.data.totalAccountHolder,
                // totalBuyGold: parseFloat(data.data.totalBuyGold.replace(/,/g, '')),
                // totalSellGold: parseFloat(data.data.totalSellGold.replace(/,/g, '')),
                totalBuyGold: (data.data.totalBuyGold) ? parseFloat(data.data.totalBuyGold.replace(/,/g, '')) : data.data.totalBuyGold,
                totalSellGold: (data.data.totalSellGold) ? parseFloat(data.data.totalSellGold.replace(/,/g, '')) : data.data.totalSellGold,
                vault: data.data.vault != 0 ? parseFloat(data.data.vault.replace(/,/g, '')) : 0 ,
                totalcustomerholding: parseFloat(data.data.totalcustomerholding.replace(/,/g, '')),
                // totalcustomerholding: data.data.totalcustomerholding,
                // balance: parseFloat(data.data.balance.replace(/,/g, '')),
                balance: data.data.balance,
                // margin: data.data.margin1,
                // marginbelow50k: data.data.marginbelow50k,
                // marginabove50k: data.data.marginabove50k,
                // marginabove150k: data.data.marginabove150k,
                // marginabove150ksell: data.data.marginabove150ksell,
                tier1sellpercent: data.data.tier[2].sellmarginpercent,
                tier2sellpercent: data.data.tier[1].sellmarginpercent,
                tier3sellpercent: data.data.tier[0].sellmarginpercent,
                tier1buypercent: data.data.tier[2].buymarginpercent,
                tier2buypercent: data.data.tier[1].buymarginpercent,
                tier3buypercent: data.data.tier[0].buymarginpercent,
                tier1sellamount: data.data.tier[2].sellmarginamount,
                tier2sellamount: data.data.tier[1].sellmarginamount,
                tier3sellamount: data.data.tier[0].sellmarginamount,
                tier1buyamount: data.data.tier[2].buymarginamount,
                tier2buyamount: data.data.tier[1].buymarginamount,
                tier3buyamount: data.data.tier[0].buymarginamount,
              };
              viewModel.set(newData);
            //   console.log(newData);
            } else {
              // Data retrieval failed
              console.log('Failed to retrieve data.');
            }
          });
    },

    generateBranchreport:function (){
        var allstate = this.lookupReference('allStateCheckbox').getValue();
        var filter = false;
        var filterstate = this.lookupReference('statelist').getValue();
    
        if(!allstate){
            filter = true;
        }
    
        this.fetchValueDashboardData(filter,allstate,filterstate);  
    },

    getHistoricalPriceReport:function(){
        var myView = this.getView(),
            me = this;

        startDate = myView.getController().lookupReference('startDate').value;
        endDate = myView.getController().lookupReference('endDate').value;

        dateStart = new Date(startDate);

        dateStart = dateStart.getFullYear() + '-' + 
                      ('0' + (dateStart.getMonth() + 1)).slice(-2) + '-' + 
                      ('0' + dateStart.getDate()).slice(-2) + ' ' + 
                      '00:00:00'
        
        dateEnd = new Date(endDate);

        dateEnd = dateEnd.getFullYear() + '-' + 
                    ('0' + (dateEnd.getMonth() + 1)).slice(-2) + '-' + 
                    ('0' + dateEnd.getDate()).slice(-2) + ' ' + 
                    '16:00:00'

        url = '?hdl=myhistoricalprice&action=exportExcel&dateStart='+dateStart+'&dateEnd='+dateEnd+'&partner='+PROJECTBASE;
        // url = Ext.urlEncode(url);

        Ext.DomHelper.append(document.body, {
            tag: 'iframe',
            id:'downloadIframe',
            frameBorder: 0,
            width: 0,
            height: 0,
            css: 'display:none;visibility:hidden;height: 0px;',
            src: url
        });
    }
       
});
