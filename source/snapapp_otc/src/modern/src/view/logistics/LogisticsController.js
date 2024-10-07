Ext.define('snap.view.logistics.LogisticsController', {
	extend: 'Ext.app.ViewController',
	alias: 'controller.logistics-logistics',	
	updateDeliveryStatus: function () {
        if (Ext.getCmp('formwindow') != null){
            Ext.getCmp('formwindow').destroy();
        }
		var myView = this.getView();
		var grid = myView.down('#logisticsgrid');
		var selectedRecords = grid.getSelection();
		if (selectedRecords == null) {
			Ext.Msg.alert('Logistic', 'Please select a record');
			return false;
		}
		var ordertype = selectedRecords.data.type;
		var vendorid = selectedRecords.data.vendorid;
		var usertype = selectedRecords.data.usertype;
		var currentstatus = selectedRecords.data.status;
		var vendorname = selectedRecords.data.vendorname;
		//console.log(updatestatusinputform);
		//console.log(updatestatusinputform.isWindow);

		

		
		var orderdetailsfieldset = new Ext.container.Container({
			label: 'Order Details',
			layout: 'vbox',			
			items:[
				{
					xtype:'label',
					html:'<h2 style="width:100%;text-align:center">Order Details</h2>',					
				},
				{
					xtype:'container',
					layout:'hbox',					
					items:[
						{
							xtype:'label',
							html:'Order No:',
							style:{
								width:'40%',								
							}
						},
						{
							xtype:'label',
							html:+selectedRecords.data.typeid
						}
					]
				},
				{
					xtype:'container',
					layout:'hbox',					
					items:[
						{
							xtype:'label',
							html:'OrderType:',
							style:{
								width:'40%'
							}
						},
						{
							xtype:'label',
							html:selectedRecords.data.type
						}
					]
				},
				{
					xtype:'container',
					layout:'hbox',
					items:[
						{
							xtype:'label',
							html:'Delivery Mode:',
							style:{
								width:'40%'
							}
						},
						{
							xtype:'label',
							html:selectedRecords.data.vendorname
						}
					]
				},
				{
					xtype:'container',
					layout:'hbox',
					items:[
						{
							xtype:'label',
							html:'Attempts:',
							style:{
								width:'40%'
							}
						},
						{
							xtype:'label',
							html:selectedRecords.data.attemps
						}
					]
				},
				{
					xtype: 'label',
					width: '99%',
					padding: '0 1 0 1',
					html: "<h5 style=' width:100%;text-align:left; border-bottom: 1px solid #bcbcbc; overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;'></h5>",
				},
			]		
			 
		});

	
		
		var labelawbno=Ext.create('Ext.Label', {
			html: 'AWB No'			
		});

		var labeldeliveryorderno=Ext.create('Ext.Label', {
			html: 'Delivery Order Number'			
		});

		var labelpickupdate=Ext.create('Ext.Label', {
			html: 'Scheduled Pickup Date'			
		});

		var labelstatus=Ext.create('Ext.Label', {
			html: 'Status'			
		});

		var labelstatusdate=Ext.create('Ext.Label', {
			html: 'Status Date (Optional)'			
		});

		var labelremarks=Ext.create('Ext.Label', {
			html: 'Remarks'			
		});

		var labelcurrentstatus=Ext.create('Ext.Label', {
			html: 'Current Status'			
		});

		if(Ext.getCmp('awbno')!=null){
			Ext.getCmp('awbno').destroy();
		}		

		var cmpawbnumber = Ext.create('Ext.field.Text', {			
			id:'awbno',
			name:'awbno',			
		});
		if(Ext.getCmp('deliverydate')!=null){
			Ext.getCmp('deliverydate').destroy();
		}
		var cmpscheduleddate = Ext.create('Ext.field.Date', {
			//label: 'Scheduled Pickup Date',
			id:'deliverydate',
			//picker:'floated',
			name:'deliverydate',
			dateFormat:'Y-m-d',			
			
		});

		if(Ext.getCmp('status')!=null){
			Ext.getCmp('status').destroy();
		}		
		var	cmpstatus = Ext.create('Ext.field.ComboBox', {
			//label: 'Status',
			store: [
				{id:'0', code:'Pending'},
				{id:'1', code:'Processing'},
			/* 	{id:'2', code:'Packing'},
				{id:'3', code:'Packed'}, */
				{id:'4', code:'Collected'},
				{id:'5', code:'In Transit'},
				{id:'6', code:'Delivered'},
				{id:'7', code:'Completed'},
				{id:'8', code:'Failed'},
				{id:'9', code:'Missing'},
			],
			queryMode: 'local',
			remoteFilter: false,
			name: 'status',
			id: 'status',
			valueField: 'id',
			displayField: 'code',
		});	
		var currentstatusvalue='Missing';
		if (selectedRecords.data.status == '0') currentstatusvalue= 'Pending';
		else if (selectedRecords.data.status == '1') currentstatusvalue= 'Processing';
		else if (selectedRecords.data.status == '2') currentstatusvalue= 'Packing';
		else if (selectedRecords.data.status == '3') currentstatusvalue= 'Packed';
		else if (selectedRecords.data.status == '4') currentstatusvalue= 'Collected';
		else if (selectedRecords.data.status == '5') currentstatusvalue= 'In Transit';
		else if (selectedRecords.data.status == '6') currentstatusvalue= 'Delivered';
		else if (selectedRecords.data.status == '7') currentstatusvalue= 'Completed';
		else if (selectedRecords.data.status == '8') currentstatusvalue= 'Failed';
                      
		var cmpcurrentstatus = Ext.create('Ext.field.Text', {
			//label: 'Current Status',			
			value:currentstatusvalue,
			disabled: true,
			
		});

		if(Ext.getCmp('status_date')!=null){
			Ext.getCmp('status_date').destroy();
		}
		var cmpstatusdate = Ext.create('Ext.field.Date', {
			//label: 'Status Date',
			id:'status_date',
			name:'status_date',
			dateFormat:'Y-m-d',	
		});

		if(Ext.getCmp('remarks')!=null){
			Ext.getCmp('remarks').destroy();
		}
		var cmpremarks = Ext.create('Ext.field.TextArea', {
			//label: 'Remarks',
			id:'remarks',
			name:'remarks'
		});




		var logisticdetailsfieldset = new Ext.panel.Panel({
			//label: 'Select Logistics',			
			layout: 'vbox',
			items: [
				{
					xtype:'label',
					html:'<h2 style="width:100%;text-align:center">Select Logistics</h2>',					
				},
			]
		});


		
		/* logisticdetailsfieldset.add(cmpdeliveryordernumber);
		logisticdetailsfieldset.add(cmpawbnumber);
		logisticdetailsfieldset.add(cmpscheduleddate);
		logisticdetailsfieldset.add(cmpstatus);	
		logisticdetailsfieldset.add(cmpstatusdate);
		logisticdetailsfieldset.add(cmpremarks); */
		//console.log(selectedRecords);

		var updatestatuspanel = new Ext.form.Panel({
			frame: true,
			layout: 'vbox',
			id:'status_updation_form',
			reference:'status_updation_form',
			name:'status_updation_form',
			formBind:true,
			defaults: {
				errorTarget: 'under'
			},	
			/* listeners: {
				beforesubmit:function(){
					var deliverydate=Ext.getCmp('deliverydate');
					alert(Ext.Date.format(deliverydate.getValue(), "Y-m-d h:i:s"))
					Ext.getCmp('deliverydate').setValue(Ext.Date.format(deliverydate.getValue(), "Y-m-d h:i:s"));
					//alert(deliverydate.getValue())
					console.log(deliverydate);
					//return false;
				}
			}, */	
			items: [
				{
					items: [						
						{
							xtype:'hiddenfield',
							id:'id',
							name:'id',
							value:selectedRecords.data.id
						},
					/* {
						xtype:'hiddenfield',
						id:'awbno',
						name:'awbno',
						value:selectedRecords.data.awbno
					},
					{
						xtype:'hiddenfield',
						id:'deliverydate',
						name:'deliverydate',
						value:selectedRecords.data.deliverydate
					}, */
						orderdetailsfieldset, logisticdetailsfieldset
					]
				},
			],
		});

		var obj=this;
		var actionmenu = Ext.create('Ext.menu.Menu');
		var updatedeliverydetailsmenu={
			text: 'Update Delivery Details',
			handler: function(btn) {
				//console.log(obj.getView().updatestatuspanel.getForm());		
				//console.log(this.updatestatusinputform);		
				obj.fnUpdateDeliveryDetails(btn);
			}
		}
		var updatestatusmenu={
			text: 'Update Status',
			handler: function (btn) {				
				obj.fnUpdateStatus();
			}			
		}
		var reattemptdeiverymenu={
			text: 'Reattempt Delivery',
			handler: function (btn) {				
				obj.fnReAttemptDelivery();
			}	
		}
		var closemenu={
			text: 'Close',
			handler: function (btn) {				
				updatestatusinputform.close();
			}
		}
		
		//Action Buttons Starts
		var actionbuttons=Ext.create('Ext.Toolbar',{
			layout:'vbox',
			//id:'actionbar',
			style:{
				width:'100%',
				//margin:'10 10 10 10'
			},
			//dock: 'top',
		});		
		var btnupdatedeliverydetails = Ext.create('Ext.Button', {
			text: 'Update Delivery details',					
			style:{
				background:'#5fa2dd',
				color:'#ffffff',
				width:'100%',
				height:'40px',
			},			
			handler: function () {
				obj.fnUpdateDeliveryDetails();
			},
		});
		var btnupdatestatus = Ext.create('Ext.Button', {
			text: 'Update Status',					
			style:{
				background:'#5fa2dd',
				color:'#ffffff',
				width:'100%',
				height:'40px',
				texttransform: 'lowercase',				
			},
			handler: function () {
				obj.fnUpdateStatus();
			},
		});
		var btnupdateattempt = Ext.create('Ext.Button', {
			text: 'Reattempt Delivery',					
			style:{
				background:'#5fa2dd',
				color:'#ffffff',
				width:'100%',
				height:'40px',
				texttransform: 'lowercase'
			},
			handler: function () {
				obj.fnReAttemptDelivery();
			},
		});
		var btnclosewindow = Ext.create('Ext.Button', {
			text: 'Close',					
			style:{
				background:'#5fa2dd',
				color:'#ffffff',
				width:'100%',
				height:'40px',
				texttransform: 'lowercase'
			},
			handler: function () {
				updatestatusinputform.close();
			},
		});
		//Action Button Ends


		if (usertype == "Operator") {
			if (ordertype == "Redemption") {
				if (vendorname == "CourGDEX" || vendorname == "CourLineClear" || vendorname == "CourJ&T") {
					if (currentstatus == "5") {
						logisticdetailsfieldset.add(labelawbno);
						logisticdetailsfieldset.add(cmpawbnumber);
						logisticdetailsfieldset.add(labelpickupdate);
						logisticdetailsfieldset.add(cmpscheduleddate);
						logisticdetailsfieldset.add(labelstatus);
						logisticdetailsfieldset.add(cmpstatus);	
						logisticdetailsfieldset.add(labelcurrentstatus);
						logisticdetailsfieldset.add(cmpcurrentstatus);	
						logisticdetailsfieldset.add(labelstatusdate);
						logisticdetailsfieldset.add(cmpstatusdate);
						logisticdetailsfieldset.add(labelremarks);
						logisticdetailsfieldset.add(cmpremarks);

						/* actionmenu.add(updatedeliverydetailsmenu);
						actionmenu.add(updatestatusmenu);
						actionmenu.add(reattemptdeiverymenu);
						actionmenu.add(closemenu); */
						actionbuttons.add(btnupdatedeliverydetails);
						actionbuttons.add(btnupdatestatus);
						actionbuttons.add(btnupdateattempt);

					} else {
						logisticdetailsfieldset.add(labelawbno);
						logisticdetailsfieldset.add(cmpawbnumber);
						logisticdetailsfieldset.add(labelpickupdate);
						logisticdetailsfieldset.add(cmpscheduleddate);
						logisticdetailsfieldset.add(labelstatus);
						logisticdetailsfieldset.add(cmpstatus);	
						logisticdetailsfieldset.add(labelcurrentstatus);
						logisticdetailsfieldset.add(cmpcurrentstatus);
						logisticdetailsfieldset.add(labelstatusdate);
						logisticdetailsfieldset.add(cmpstatusdate);
						logisticdetailsfieldset.add(labelremarks);
						logisticdetailsfieldset.add(cmpremarks);

						/* actionmenu.add(updatedeliverydetailsmenu);
						actionmenu.add(updatestatusmenu);						
						actionmenu.add(closemenu); */
						actionbuttons.add(btnupdatedeliverydetails);
						//actionbuttons.add(btnupdatestatus);

					}
				} else {		
					if(currentstatus == "5"){
						//logisticdetailsfieldset.add(cmpdeliveryordernumber);
						logisticdetailsfieldset.add(labeldeliveryorderno);
						logisticdetailsfieldset.add(cmpawbnumber); //DeliveryOrderNumber
						logisticdetailsfieldset.add(labelpickupdate);
						logisticdetailsfieldset.add(cmpscheduleddate);
						logisticdetailsfieldset.add(labelstatus);
						logisticdetailsfieldset.add(cmpstatus);	
						logisticdetailsfieldset.add(labelcurrentstatus);
						logisticdetailsfieldset.add(cmpcurrentstatus);
						logisticdetailsfieldset.add(labelstatusdate);
						logisticdetailsfieldset.add(cmpstatusdate);
						logisticdetailsfieldset.add(labelremarks);
						logisticdetailsfieldset.add(cmpremarks);

						/* actionmenu.add(updatedeliverydetailsmenu);
						actionmenu.add(updatestatusmenu);
						actionmenu.add(reattemptdeiverymenu);
						actionmenu.add(closemenu); */
						actionbuttons.add(btnupdatedeliverydetails);
						//actionbuttons.add(btnupdatestatus);
						//actionbuttons.add(btnupdateattempt);
					}else{
						//logisticdetailsfieldset.add(cmpdeliveryordernumber);
						logisticdetailsfieldset.add(labeldeliveryorderno);
						logisticdetailsfieldset.add(cmpawbnumber); //DeliveryOrderNumber
						logisticdetailsfieldset.add(labelpickupdate);
						logisticdetailsfieldset.add(cmpscheduleddate);
						logisticdetailsfieldset.add(labelstatus);
						logisticdetailsfieldset.add(cmpstatus);	
						logisticdetailsfieldset.add(labelcurrentstatus);
						logisticdetailsfieldset.add(cmpcurrentstatus);
						logisticdetailsfieldset.add(labelstatusdate);
						logisticdetailsfieldset.add(cmpstatusdate);
						logisticdetailsfieldset.add(labelremarks);
						logisticdetailsfieldset.add(cmpremarks);

						/* actionmenu.add(updatedeliverydetailsmenu);
						actionmenu.add(updatestatusmenu);						
						actionmenu.add(closemenu); */
						actionbuttons.add(btnupdatedeliverydetails);
						//actionbuttons.add(btnupdatestatus);
					}			
				}
			} else if (ordertype == "Buyback") {

			} else if (ordertype == "Replenishment") {
				if(vendorname == "CourGDEX" || vendorname == "CourLineClear" || vendorname == "CourJ&T"){
					logisticdetailsfieldset.add(labelawbno);
					logisticdetailsfieldset.add(cmpawbnumber);
					logisticdetailsfieldset.add(labelpickupdate);
					logisticdetailsfieldset.add(cmpscheduleddate);
					logisticdetailsfieldset.add(labelstatus);
					logisticdetailsfieldset.add(cmpstatus);	
					logisticdetailsfieldset.add(labelcurrentstatus);
					logisticdetailsfieldset.add(cmpcurrentstatus);
					logisticdetailsfieldset.add(labelstatusdate);
					logisticdetailsfieldset.add(cmpstatusdate);
					logisticdetailsfieldset.add(labelremarks);
					logisticdetailsfieldset.add(cmpremarks);

					/* actionmenu.add(updatedeliverydetailsmenu);
					actionmenu.add(updatestatusmenu);						
					actionmenu.add(closemenu); */
					actionbuttons.add(btnupdatedeliverydetails);
					//actionbuttons.add(btnupdatestatus);
				}
			} else {
				//logisticdetailsfieldset.add(cmpdeliveryordernumber);
				logisticdetailsfieldset.add(labeldeliveryorderno);
				logisticdetailsfieldset.add(cmpawbnumber); //DeliveryOrderNumber
				logisticdetailsfieldset.add(labelpickupdate);
				logisticdetailsfieldset.add(cmpscheduleddate);
				logisticdetailsfieldset.add(labelstatus);
				logisticdetailsfieldset.add(cmpstatus);	
				logisticdetailsfieldset.add(labelcurrentstatus);
				logisticdetailsfieldset.add(cmpcurrentstatus);
				logisticdetailsfieldset.add(labelstatusdate);
				logisticdetailsfieldset.add(cmpstatusdate);
				logisticdetailsfieldset.add(labelremarks);
				logisticdetailsfieldset.add(cmpremarks);

				/* actionmenu.add(updatedeliverydetailsmenu);
				actionmenu.add(updatestatusmenu);						
				actionmenu.add(closemenu); */
				actionbuttons.add(btnupdatedeliverydetails);
				//actionbuttons.add(btnupdatestatus);
			}
		} else if (usertype == "Sale") {
			if(ordertype == "Redemption"){
				if(vendorname == "CourGDEX" || vendorname == "CourLineClear" || vendorname == "CourJ&T"){
					if(currentStatus < 3 || currentStatus > 6){
						logisticdetailsfieldset.add(labelawbno);
						logisticdetailsfieldset.add(cmpawbnumber);
						logisticdetailsfieldset.add(labelpickupdate);
						logisticdetailsfieldset.add(cmpscheduleddate);	
						logisticdetailsfieldset.add(labelstatusdate);					
						logisticdetailsfieldset.add(cmpstatusdate);
						logisticdetailsfieldset.add(labelremarks);
						logisticdetailsfieldset.add(cmpremarks);

						/* actionmenu.add(updatedeliverydetailsmenu);
						actionmenu.add(updatestatusmenu);						
						actionmenu.add(closemenu); */
						actionbuttons.add(btnupdatedeliverydetails);
						//actionbuttons.add(btnupdatestatus);
					}else{
						logisticdetailsfieldset.add(labelawbno);
						logisticdetailsfieldset.add(cmpawbnumber);
						logisticdetailsfieldset.add(labelpickupdate);
						logisticdetailsfieldset.add(cmpscheduleddate);
						logisticdetailsfieldset.add(labelstatus);
						logisticdetailsfieldset.add(cmpstatus);	
						logisticdetailsfieldset.add(labelcurrentstatus);
						logisticdetailsfieldset.add(cmpcurrentstatus);
						logisticdetailsfieldset.add(labelstatusdate);
						logisticdetailsfieldset.add(cmpstatusdate);
						logisticdetailsfieldset.add(labelremarks);
						logisticdetailsfieldset.add(cmpremarks);

						/* actionmenu.add(updatedeliverydetailsmenu);
						actionmenu.add(updatestatusmenu);						
						actionmenu.add(closemenu); */
						actionbuttons.add(btnupdatedeliverydetails);
						//actionbuttons.add(btnupdatestatus);
					}
				}else{
					if(currentStatus < 3 || currentStatus > 6){
						//logisticdetailsfieldset.add(cmpdeliveryordernumber);
						logisticdetailsfieldset.add(labeldeliveryorderno);
						logisticdetailsfieldset.add(cmpawbnumber); //DeliveryOrderNumber
						logisticdetailsfieldset.add(labelpickupdate);
						logisticdetailsfieldset.add(cmpscheduleddate);
						logisticdetailsfieldset.add(labelstatusdate);							
						logisticdetailsfieldset.add(cmpstatusdate);
						logisticdetailsfieldset.add(labelremarks);
						logisticdetailsfieldset.add(cmpremarks);

						/* actionmenu.add(updatedeliverydetailsmenu);
						actionmenu.add(updatestatusmenu);						
						actionmenu.add(closemenu); */
						actionbuttons.add(btnupdatedeliverydetails);
						//actionbuttons.add(btnupdatestatus);
					}else{
						if(currentStatus == "5"){
							//logisticdetailsfieldset.add(cmpdeliveryordernumber);
							logisticdetailsfieldset.add(labeldeliveryorderno);
							logisticdetailsfieldset.add(cmpawbnumber); //DeliveryOrderNumber
							logisticdetailsfieldset.add(labelpickupdate);
							logisticdetailsfieldset.add(cmpscheduleddate);
							logisticdetailsfieldset.add(labelstatus);
							logisticdetailsfieldset.add(cmpstatus);	
							logisticdetailsfieldset.add(labelcurrentstatus);
							logisticdetailsfieldset.add(cmpcurrentstatus);
							logisticdetailsfieldset.add(labelstatusdate);
							logisticdetailsfieldset.add(cmpstatusdate);
							logisticdetailsfieldset.add(labelremarks);
							logisticdetailsfieldset.add(cmpremarks);

							/* actionmenu.add(updatedeliverydetailsmenu);
							actionmenu.add(updatestatusmenu);
							actionmenu.add(reattemptdeiverymenu);
							actionmenu.add(closemenu); */
							actionbuttons.add(btnupdatedeliverydetails);
							//actionbuttons.add(btnupdatestatus);
							//actionbuttons.add(btnupdateattempt);
						}else{
							//logisticdetailsfieldset.add(cmpdeliveryordernumber);
							logisticdetailsfieldset.add(labeldeliveryorderno);
							logisticdetailsfieldset.add(cmpawbnumber); //DeliveryOrderNumber
							logisticdetailsfieldset.add(labelpickupdate);
							logisticdetailsfieldset.add(cmpscheduleddate);
							logisticdetailsfieldset.add(labelstatus);
							logisticdetailsfieldset.add(cmpstatus);	
							logisticdetailsfieldset.add(labelcurrentstatus);
							logisticdetailsfieldset.add(cmpcurrentstatus);
							logisticdetailsfieldset.add(labelstatusdate);
							logisticdetailsfieldset.add(cmpstatusdate);
							logisticdetailsfieldset.add(labelremarks);
							logisticdetailsfieldset.add(cmpremarks);

							/* actionmenu.add(updatedeliverydetailsmenu);
							actionmenu.add(updatestatusmenu);						
							actionmenu.add(closemenu); */
							actionbuttons.add(btnupdatedeliverydetails);
							//actionbuttons.add(btnupdatestatus);
						}
					}
				}
			}else if(ordertype == "Buyback"){
			}else if(ordertype == "Replenishment"){
				if(vendorname == "CourGDEX" || vendorname == "CourLineClear" || vendorname == "CourJ&T"){
					logisticdetailsfieldset.add(labelawbno);
					logisticdetailsfieldset.add(cmpawbnumber);
					logisticdetailsfieldset.add(labelpickupdate);
					logisticdetailsfieldset.add(cmpscheduleddate);	
					logisticdetailsfieldset.add(labelstatusdate);				
					logisticdetailsfieldset.add(cmpstatusdate);
					logisticdetailsfieldset.add(labelremarks);
					logisticdetailsfieldset.add(cmpremarks);

					/* actionmenu.add(updatedeliverydetailsmenu);
					actionmenu.add(updatestatusmenu);						
					actionmenu.add(closemenu); */
					actionbuttons.add(btnupdatedeliverydetails);
					//actionbuttons.add(btnupdatestatus);
				}else{
					//logisticdetailsfieldset.add(cmpdeliveryordernumber);
					logisticdetailsfieldset.add(labeldeliveryorderno);
					logisticdetailsfieldset.add(cmpawbnumber); //DeliveryOrderNumber
					logisticdetailsfieldset.add(labelpickupdate);
					logisticdetailsfieldset.add(cmpscheduleddate);
					logisticdetailsfieldset.add(labelstatus);
					logisticdetailsfieldset.add(cmpstatus);	
					logisticdetailsfieldset.add(labelcurrentstatus);
					logisticdetailsfieldset.add(cmpcurrentstatus);
					logisticdetailsfieldset.add(labelstatusdate);
					logisticdetailsfieldset.add(cmpstatusdate);
					logisticdetailsfieldset.add(labelremarks);
					logisticdetailsfieldset.add(cmpremarks);

					/* actionmenu.add(updatedeliverydetailsmenu);
					actionmenu.add(updatestatusmenu);						
					actionmenu.add(closemenu); */
					actionbuttons.add(btnupdatedeliverydetails);
					//actionbuttons.add(btnupdatestatus);
				}
			}else{

			}
		} else {

		}
		
		actionbuttons.add(btnclosewindow);
		var updatestatusinputform = new Ext.Window({
			title: '<span style="color:#ffffff">Update Logistics...</span>',
			id:'formwindow',
			name:'formwindow',
			reference:'formwindow',
			layout: 'fit',
			width: '90%',
			height: '90%',
			header: {
				style:{
					background:'#5fa2dd',					
				},
				//titlePosition: 0,
				items: [
					/* {						
						//iconCls: 'x-fa fa-ellipsis-v',						
						arrow: false,											
						xtype: 'button',
						menu: actionmenu,
						text: '<span class="x-fa fa-ellipsis-v" style="color:#ffffff;"> </span>'				
					} */
				]
			},
			//maxHeight: 700,
			modal: true,
			plain: true,
			buttonAlign: 'center',			
			buttons: [ 
				actionbuttons
				/* {
					xtype:'toolbar',
					layout:'vbox',
					id:'actionbar',
					style:{
						width:'100%',
						margin:'10 10 10 10'
					},
					dock: 'top',
					items:[						
						actionbuttons
					]
				}	 */			
			],
			closeAction: 'destroy',
			items: updatestatuspanel,	
		});
		
		updatestatusinputform.show();
		this.formPanel = updatestatuspanel;

	},	
	fnUpdateStatus:function(){
		var formvalues=Ext.getCmp('status_updation_form').getValues();
		if(formvalues.awbno==null){
			Ext.Msg.alert('Status update-Alert', "AWB/Delivery Order No is needed", Ext.emptyFn);
			return false;
		}
		if(formvalues.deliverydate==null){
			Ext.Msg.alert('Status update-Alert', "Scheduled Pickup Date is needed", Ext.emptyFn);
			return false;
		}
		if(formvalues.status==null){
			Ext.Msg.alert('Status update-Alert', "Status is needed", Ext.emptyFn);
			return false;
		}
		if(formvalues.remarks==null){
			Ext.Msg.alert('Status update-Alert', "Remark is needed", Ext.emptyFn);
			return false;
		}
		
		
	/* 	var myView = this.getView();
		var grid = myView.down('#logisticsgrid');
		var selectedRecords = grid.getSelection(); */
		
		//console.log(selectedRecords.data);
		
		var me=this;
		Ext.getCmp('status_updation_form').submit({
			submitEmptyText: false,
			url: 'index.php?hdl=logistic&action=updateLogisticStatus',
			method: 'POST',					
			waitMsg: 'Processing',
			success: function (form, action) { //success							
				Ext.Msg.alert('Success', 'Updated Successfully !', Ext.emptyFn);
				Ext.getCmp('formwindow').close();
				var myView = me.getView();
				var grid = myView.down('#logisticsgrid');
				grid.getStore().reload();
				
			},
			failure: function (form, action) {	
				Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
			}
		});
	},
	fnUpdateDeliveryDetails:function(){
		var formvalues=Ext.getCmp('status_updation_form').getValues();
		if(formvalues.awbno==null){
			Ext.Msg.alert('Update Delivery Details-Alert', "AWB/Delivery Order No is needed", Ext.emptyFn);
			return false;
		}
		if(formvalues.deliverydate==null){
			Ext.Msg.alert('Update Delivery Details-Alert', "Scheduled Pickup Date is needed", Ext.emptyFn);
			return false;
		}
		
		var me = this;
		Ext.getCmp('status_updation_form').submit({
			submitEmptyText: false,
			url: 'index.php?hdl=logistic&action=updateLogisticInformation',
			method: 'POST',
			waitMsg: 'Processing',
			success: function (form, action) { //success				
				Ext.Msg.alert('Success', 'Updated Successfully !', Ext.emptyFn);
				Ext.getCmp('formwindow').close();
				var myView = me.getView();
				var grid = myView.down('#logisticsgrid');
				grid.getStore().reload();
				
			},
			failure: function (form, action) {	
				Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
			}
		});	
	},	
	fnReAttemptDelivery:function(){	
		var me = this;
		Ext.getCmp('status_updation_form').submit({
			submitEmptyText: false,
			url: 'index.php?hdl=logistic&action=updateLogisticAttempts',
			method: 'POST',
			waitMsg: 'Processing',
			success: function (form, action) { //success				
				Ext.Msg.alert('Success', 'Updated Successfully !', Ext.emptyFn);
				Ext.getCmp('formwindow').close();
				var myView = me.getView();
				var grid = myView.down('#logisticsgrid');
				grid.getStore().reload();
				
			},
			failure: function (form, action) {	
				Ext.Msg.alert('Error', action.errorMessage, Ext.emptyFn);
			}
		});	
	}
});