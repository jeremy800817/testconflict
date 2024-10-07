Ext.define('snap.view.user.UserController', {
	extend: 'snap.view.gridpanel.BaseController',
	alias: 'controller.user-user',

	requires: [
		'snap.view.gridpanel.BaseController'
	],

	getRoleGrid: function(theGridFormPanel) {
		var accessFieldSet = theGridFormPanel.down('form').getComponent('user_column_main').getComponent('user_column_2').getComponent('user_access_fieldset');
		var roleGrid = accessFieldSet.getComponent('user_userrole');
		return roleGrid;
	},

	onPostLoadForm: function(theGridFormPanel, theGridForm, theRecord) {
		theGridForm.setValues({
			password: ''
		});
		var roleGrid = this.getRoleGrid(theGridFormPanel);
		roleGrid.setEmptyText('Loading Roles...');
		snap.getApplication().sendRequest({
			hdl: 'user', action: 'getuserrole', id: theGridForm.findField('id').getValue()
		}, 'Fetching data from server....').then(
		function(data) {
			if (data.success) {
				var roleStore = roleGrid.getStore();
				roleStore.removeAll();
				roleStore.add(data.roledata);
			}
		});

        if(theRecord.data.partnerid == 0){
			theGridFormPanel.lookupController().lookupReference('state_section').setHidden(false);
        }
	},

	onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm) {
		var isEditMode = (theGridForm.findField('id').getValue().length > 0) ? true : false;

		var password = theGridForm.findField('userpassword').getValue();
		if (password != '' || password != null || password !== undefined) {
			var confirmpassword = theGridForm.findField('confirmpassword').getValue();
			if (password != confirmpassword) {
				theGridForm.findField('confirmpassword').markInvalid('Password do not match');
				return false;
			}
		}
		if(isEditMode) {
			theGridForm.setValues({action: 'edit'});
		} else {
			theGridForm.setValues({action: 'add'});
		}

		var roleGrid = this.getRoleGrid(theGridFormPanel);
		var roleStore = roleGrid.getStore();
		var roleCount = roleStore.count();
		if (roleCount > 0) {
			var tmpRoles = [];
			roleStore.each(function(rec) {
				tmpRoles.push(rec.get('id'));
			});
			var selectedroles = tmpRoles.join("||");
			theGridForm.setValues({selectedroles: selectedroles});
		} else {
			Ext.MessageBox.show({
				title: 'Error Message',
				msg: 'Select roles',
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
			return false;
		}

		return true;
	},

	getDateRange: function(){ 

        // _this = this;
        vm = this.getViewModel();

        startDate = this.getView().getReferences().startDate.getValue()
        endDate = this.getView().getReferences().endDate.getValue()

        if (startDate && endDate){
            startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
        }else{
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
        this.getView().getStore().addFilter(
            {
                property: "createdon", type: "date", operator: "BETWEEN", value: [startDate, endDate]
            },
        )
    },

    clearDateRange: function(){
        startDate = this.getView().getReferences().startDate.setValue('')
        endDate = this.getView().getReferences().endDate.setValue('')
        filter = this.getView().getStore().getFilters().items[0];
        if (filter){
            this.getView().getStore().removeFilter(filter)
        }else{
            Ext.MessageBox.show({
                title: 'Clear Filter',
                msg: 'No Filter.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
    },

	getUserExport: function(btn){
        var myView = this.getView(),
        // grid header data
        header = [];
        // partnerCode = myView.partnercode;
        //debugger;
        // Check if buy or sell based on button reference
        /*
        if('dailytransactionsell' == btn.reference){
            // filter by companysell
            
        }else if('dailytransactionbuy' == btn.reference){
            // filter by companybuy
        }
        */
       type = btn.reference;
       

        //    const reportingFields = [
        //         ['Date', ['createdon', 0]], 
        //         ['Transaction Ref No', ['refno', 0]],
                
        //     ];
        //     //{ key1 : [val1, val2, val3] } 
            
        //     for (let [key, value] of reportingFields) {
        //         //alert(key + " = " + value);
        //         columnleft = {
        //             // [_key]: _value
        //             text: key,
        //             index: value[0]
        //         }
                
        //         if (value[0] !== 0){
        //             columnleft.decimal = value[1];
        //         }
        //         header.push(columnleft);
        //     }
        
       btn.up('grid').getColumns().map(column => {
        if (column.isVisible() && column.dataIndex !== null){
                _key = column.text
                _value = column.dataIndex
                columnlist = {
                    // [_key]: _value
                    text: _key,
                    index: _value
                }
                if (column.exportdecimal !== null){
                    _decimal = column.exportdecimal;
                    columnlist.decimal = _decimal;
                }
                if('ordpartnername' == column.dataIndex || 'ordstatus' == column.dataIndex){
                    // dont push header if its status
                }else {
                    header.push(columnlist);
                }
              
            }
        });

        // Add a transaction header 
        
        
        startDate = this.getView().getReferences().startDate.getValue();
        endDate = this.getView().getReferences().endDate.getValue();

        // Alter this check if partner is BMMB
        if(!startDate || !endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Please select date range for export',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }

        if(startDate > endDate){
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date cannot be later than End date',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
        
        // Do a daterange checker
        // If date exceeds 2 months, reject
        // Init date values
        var msecPerMinute = 1000 * 60;
        var msecPerHour = msecPerMinute * 60;
        var msecPerDay = msecPerHour * 24;

        // Calculate date interval 
        var interval = endDate - startDate;

        var intervalDays = Math.floor(interval / msecPerDay );

        // Get days of months
        // Startdate
        startMonth = new Date(startDate.getYear(), startDate.getMonth(), 0).getDate();

        endMonth = new Date(endDate.getYear(), endDate.getMonth(), 0).getDate();

        // Get 2 months range limit for filter
        rangeLimit = startMonth + endMonth;

        if (startDate && endDate){
            // Check if day exceeds 63 days 
            // Skip this check if partner is BMMB
            // if(partnerCode != 'BMMB'){
            //     if (rangeLimit >= intervalDays){
            //         // Check if day exceeds 63 days 
                    
            //         startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //         endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //         daterange = {
            //             startDate: startDate,
            //             endDate: endDate,
            //         }
            //     }else{
            //         Ext.MessageBox.show({
            //             title: 'Filter Date',
            //             msg: 'Please select date range within 2 months',
            //             buttons: Ext.MessageBox.OK,
            //             icon: Ext.MessageBox.ERROR
            //         });
            //         return
            //     }
            //     // End check
            // }else{
            //     // for bmmb no limit imposed
            //     startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            //     endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            //     daterange = {
            //         startDate: startDate,
            //         endDate: endDate,
            //     }
            // }
            startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
            daterange = {
                startDate: startDate,
                endDate: endDate,
            }
        }else{
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }

        filter = [{property: "createdon", type: "date", operator: "BETWEEN", value: [startDate, endDate]}];
        filter = encodeURI(JSON.stringify(filter));

        header = encodeURI(JSON.stringify(header));
        
        daterange = encodeURI(JSON.stringify(daterange));

        type = encodeURI(JSON.stringify(type));

        partnercode = myView.partnercode;
        //url = '?hdl=bmmborder&action=exportExcel&header='+header+'&daterange='+daterange+'&type='+type;'
        url = '?hdl=user&action=exportExcel&header='+header+'&filter='+filter+'&partnercode='+partnercode;
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
    },

    getuserswitchbranchlogReport: function(btn){
		var myView = this.getView(),
		// grid header data
		header = [];
		partnerCode = myView.partnercode;
  
		type = btn.reference;
	  
  
		const reportingFields = [
			['Date', ['createdon', 0]], 
		];
		//{ key1 : [val1, val2, val3] } 
		
		for (let [key, value] of reportingFields) {
			//alert(key + " = " + value);
			columnleft = {
				// [_key]: _value
				text: key,
				index: value[0]
			}
			
			if (value[0] !== 0){
				columnleft.decimal = value[1];
			}
			header.push(columnleft);
		}
		
		btn.up('grid').getColumns().map(column => {
		if (column.isVisible() && column.dataIndex !== null){
				_key = column.text
				_value = column.dataIndex
				columnlist = {
					// [_key]: _value
					text: _key,
					index: _value
				}
				if (column.exportdecimal !== null){
					_decimal = column.exportdecimal;
					columnlist.decimal = _decimal;
				}
				if('status' == column.dataIndex){
					// dont push header if its status
				}else {
					header.push(columnlist);
				}
			  
			}
		});
  
		// Add a transaction header 
		
		startDate = this.getView().getReferences().startDate.getValue();
		endDate = this.getView().getReferences().endDate.getValue();
  
		if(!startDate || !endDate){
			Ext.MessageBox.show({
				title: 'Filter Date',
				msg: 'Please select date range for export',
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
		}
  
		if(startDate > endDate){
			Ext.MessageBox.show({
				title: 'Filter Date',
				msg: 'Start date cannot be later than End date',
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
			return
		}
		
		// Do a daterange checker
		// If date exceeds 2 months, reject
		// Init date values
		var msecPerMinute = 1000 * 60;
		var msecPerHour = msecPerMinute * 60;
		var msecPerDay = msecPerHour * 24;
  
		// Calculate date interval 
		var interval = endDate - startDate;
  
		var intervalDays = Math.floor(interval / msecPerDay );
  
		// Get days of months
		// Startdate
		startMonth = new Date(startDate.getYear(), startDate.getMonth(), 0).getDate();
  
		endMonth = new Date(endDate.getYear(), endDate.getMonth(), 0).getDate();
  
		// Get 2 months range limit for filter
		rangeLimit = startMonth + endMonth;
  
		if (startDate && endDate){
			startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
			endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
			daterange = {
				startDate: startDate,
				endDate: endDate,
			}
		}else{
			Ext.MessageBox.show({
				title: 'Filter Date',
				msg: 'Start date and End date required.',
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
			return
		}
  
		header = encodeURI(JSON.stringify(header));
		
		daterange = encodeURI(JSON.stringify(daterange));
  
		type = encodeURI(JSON.stringify(type));
  
		url = '?hdl=otcuserswitchbranchlog&action=exportExcel&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
  
		Ext.DomHelper.append(document.body, {
			tag: 'iframe',
			id:'downloadIframe',
			frameBorder: 0,
			width: 0,
			height: 0,
			css: 'display:none;visibility:hidden;height: 0px;',
			src: url
		});
	},
});
