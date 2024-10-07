Ext.define('snap.view.managementfee.ManagementFee_BSN', {
    extend: "Ext.form.Panel",
    xtype: 'managementfeeview_BSN',
    //title: 'Management Fee',
    // width: '50%',
    // height: '50%',
     layout: 'hbox',
    editAllClicked: false,
	viewModel: {
        data: {
            period: null,
            attempt: null,
            jobperiod: null,
        }
	},

    initComponent: function () {
        const me = this;
		const managementfeeview = this;
        const store = Ext.create('Ext.data.Store', {
            fields: ['minamount', 'maxamount', 'value', 'partnerid'],
            proxy: {
                type: 'ajax',
                url: 'index.php?hdl=managementfee&action=list&partnercode=ALRAJHI',
                reader: {
                    type: 'json',
                    rootProperty: 'records'
                }
            },
            autoLoad: true
        });
		
		var viewModel = me.getViewModel();
		
		store.on('load', function(store, records, successful, operation, eOpts) {
			if (successful) {
				records.forEach(function(record) {
					var period = record.get('period');
					if (null == viewModel.get('period')) {
						viewModel.set('period', period);
					}
					var attempt = record.get('attempt');
					if (null == viewModel.get('attempt')) {
						viewModel.set('attempt', attempt);
					}
					var jobperiod = record.get('jobperiod');
					if (null == viewModel.get('jobperiod')) {
						viewModel.set('jobperiod', jobperiod);
					}
				});
			}
		});

		
        var isAddGramVisible = false;
        const leftSide = {
            xtype: 'container',
            width: '50%',       
            items : [
                {
                    xtype: 'gridpanel',
					itemId: 'managementfeegridpanel',
                    store: store,
                    columns: [
                        {
                            text: 'ID', 
                            dataIndex: 'id',
                            flex: 1,
                            hidden: true
                        },
                        {
                            text: 'Gold Balance Range', 
                            dataIndex: 'avgdailygoldbalancegramfrom',
                            renderer: function(value, metaData, record) {
                                const minAmount = Ext.util.Format.number(record.get('avgdailygoldbalancegramfrom'), '0.000000');
                                const maxAmount = Ext.util.Format.number(record.get('avgdailygoldbalancegramto'), '0.000000');
                                return `${minAmount} - ${maxAmount}`;
                            },
                            flex: 1                            
                        },
                        { 
                            text: 'Value', 
                            dataIndex: 'feeamount', 
                            flex: 1,
                            renderer: function(value, metaData, record) {
                                const minusIcon = '<i class="x-fa fa-minus minus-button"></i>';
                                const plusIcon = '<i class="x-fa fa-plus plus-button"></i>';
                                const delIcon = '<i class="x-fa fa-trash trash-button"></i>';    
                                const formattedValue = parseFloat(value).toFixed(2);                          
                                const interactiveValue = me.editAllClicked
                                    ? `${minusIcon}  ${formattedValue}  ${plusIcon}  ${delIcon}`
                                    : formattedValue;

                                metaData.tdCls = 'interactive-value'; 
                                metaData.tdStyle = 'text-align: center;'; 
                                return interactiveValue;
                            }
                        },
						{ 
                            text: 'Start Date', 
                            dataIndex: 'starton', 
                            xtype: 'datecolumn', 
							format: 'Y-m-d H:i:s',
							flex: 1,
                        },
						{ 
							text: 'End Date', 
							dataIndex: 'endon', 
							xtype: 'datecolumn', 
							format: 'Y-m-d H:i:s',
							flex: 1,
						},
                    ],
                    margin: '0 0 10 0',
                    listeners: {
                        cellclick: function (grid, td, cellIndex, record, tr, rowIndex, e, eOpts) {
                            if (me.editAllClicked && cellIndex === 2) {
                                   
                                const target = e.target;                         
                                if (target && target.classList.contains('fa-minus')) {                               
                                    var id = record.get('id');
                                    const recordValue = parseFloat(record.get('feeamount'));
                                    tmpmin = (recordValue - 0.01);
                                    tmpmin = tmpmin.toFixed(2);
                                    tmpin = Number(tmpmin);                                                     
                                    record.set('feeamount', tmpmin);                               
                                    Ext.Ajax.request({
                                    url: 'index.php?hdl=managementfee&action=edit&partnercode=ALRAJHI',
                                    method: 'POST', 
                                    params: {
                                        id: id,
                                        feeamount: tmpmin 
                                    },
                                    success: function(response, options) {
                                    },
                                    failure: function(response, options) {
                                    }                              
                                    });
                                } else if (target && target.classList.contains('fa-plus')) {
                                var id = record.get('id');
                                const recordValue = parseFloat(record.get('feeamount'));
                                    if (typeof recordValue === 'number') {
                                        tmpmin = (recordValue + 0.01);
                                        tmpmin = tmpmin.toFixed(2);
                                        tmpin = Number(tmpmin);
                                        record.set('feeamount', tmpmin);   
                                        Ext.Ajax.request({
                                        url: 'index.php?hdl=managementfee&action=edit&partnercode=ALRAJHI',
                                        method: 'POST', 
                                        params: {
                                            id: id,
                                            feeamount: tmpmin
                                        },
                                        success: function(response, options) {
                                        },
                                        failure: function(response, options) {
                                        }                              
                                        });

                                    } else {
                                        console.error("Record value is not a number:", recordValue);
                                    }

                                } else if (target && target.classList.contains('fa-trash')) {

                                Ext.Msg.confirm('Confirm Delete', 'Are you sure you want to delete this record?', function(btn) {
                                if (btn === 'yes') {
                                var id = record.get('id');
                                const recordValue = parseFloat(record.get('value'));  
                                        Ext.Ajax.request({
                                        url: 'index.php?hdl=managementfee&action=edit&partnercode=ALRAJHI',
                                        method: 'POST',
                                        params: {
                                               status:0,
                                               id:id
                                        },
                                        success: function(response, options) {
                                            grid.getStore().remove(record);
                                        },
                                        failure: function(response, options) {
                                        }                              
                                        });
                                    }
                                });
                            }

                            }else if(me.editAllClicked && cellIndex === 1){
                                // handle edit item 

                                const minAmount = record.get('avgdailygoldbalancegramfrom');
                                const maxAmount = record.get('avgdailygoldbalancegramto');
                                const id = record.get('id');
                                // Create a modal window for editing the "Amount Range"
                                const editAmountWindow = Ext.create('Ext.window.Window', {
                                    title: 'Edit Amount Range',
                                    modal: true,
                                    items: [
                                        {
                                            xtype: 'form',
                                            layout: 'form',
                                            labelWidth: 100, 
                                            items: [
                                                  {
                                                    xtype: 'numberfield',
                                                    fieldLabel: 'id',
                                                    name: 'id',
                                                    value: id,
                                                    hidden:true,
                                                    itemId:'id',
                                                  
                                                },
                                                {
                                                    xtype: 'numberfield',
                                                    fieldLabel: 'Min Amount',
                                                    name: 'minAmount',
                                                    value: minAmount,
                                                    itemId:'minAmount',
													width: 300,
													minValue: 0,
													decimalPrecision: 6
                                                 
                                                },
                                                {
                                                    xtype: 'numberfield',
                                                    fieldLabel: 'Max Amount',
                                                    name: 'maxAmount',
                                                    value: maxAmount,
                                                    itemId:'maxAmount',
													width: 300,
													minValue: 0,
													decimalPrecision: 6
                                                 
                                                },
                                            ]
                                        }
                                    ],
                                    buttons: [
                                        {
                                            text: 'Save',
                                            handler: function () {
                                //   const form = addGramWindow.down('form');
                                //     const minAmount = form.down('#minAmountField').getValue();
                                //     const maxAmount = form.down('#maxAmountField').getValue();
                                //     const value = form.down('#valueField').getValue();

                                                const form = editAmountWindow.down('form');
                                                const newMinAmount = form.down('#minAmount').getValue();
                                                const newMaxAmount = form.down('#maxAmount').getValue();
                                                const id = form.down('#id').getValue();
                                                // Update the record with the new values
                                                record.set('avgdailygoldbalancegramfrom', newMinAmount);
                                                record.set('avgdailygoldbalancegramto', newMaxAmount);
                                                // Close the modal window

												Ext.Ajax.request({
												url: 'index.php?hdl=managementfee&action=edit&partnercode=ALRAJHI',
												method: 'POST', // Use the appropriate HTTP method
												params: {
													avgdailygoldbalancegramfrom: newMinAmount,
													avgdailygoldbalancegramto: newMaxAmount,
													name: '>='+newMinAmount+"-"+newMaxAmount,
													id:id
												},
												success: function(response) {
													var data = Ext.decode(response.responseText);
													if (data.success === true) {
														Ext.Msg.alert("Success", "Data successfully change");
														editAmountWindow.close();
													} else {
														Ext.Msg.alert("Failed", data.errmsg);
													}
												},
												failure: function(response) {
													var data = Ext.decode(response.responseText);
														 Ext.Msg.alert("Error", data.errmsg);
												}                              
												});

                                                
                                            }
                                        },
                                        {
                                            text: 'Cancel',
                                            handler: function () {
                                                // Close the modal window without saving changes
                                                editAmountWindow.close();
                                            }
                                        }
                                    ]
                                });

                                editAmountWindow.show();
                                // end handle edit item
                            } else if (me.editAllClicked && cellIndex === 3 || me.editAllClicked && cellIndex === 4){
                                // handle edit item 
								var startOn = Ext.Date.parse(record.get('starton'), 'Y-m-d\\TH:i:sP');
								startOn = Ext.Date.format(startOn, 'Y-m-d');
								var endOn = Ext.Date.parse(record.get('endon'), 'Y-m-d\\TH:i:sP');
								endOn = Ext.Date.format(endOn, 'Y-m-d');
                                const id = record.get('id');
                                // Create a modal window for editing the "Amount Range"
                                const editDateWindow = Ext.create('Ext.window.Window', {
                                    title: 'Edit Date Range',
                                    modal: true,
                                    items: [
                                        {
                                            xtype: 'form',
                                            layout: 'form',
                                            labelWidth: 100, 
                                            items: [
                                                  {
                                                    xtype: 'numberfield',
                                                    fieldLabel: 'id',
                                                    name: 'id',
                                                    value: id,
                                                    hidden:true,
                                                    itemId:'id',
                                                  
                                                },
												{
													xtype: 'datefield',
													fieldLabel: 'Start Date',
													name: 'starton',
													value: startOn,
													allowBlank: true,
													width: 300,
													format: 'Y-m-d'
												},
												{
													xtype: 'datefield',
													fieldLabel: 'End Date',
													name: 'endon',
													value: endOn,
													itemId:'endOnField',
													allowBlank: true,
													width: 300,
													format: 'Y-m-d'
												}
                                            ]
                                        }
                                    ],
                                    buttons: [
                                        {
                                            text: 'Save',
                                            handler: function () {
                                                const form = editDateWindow.down('form');
												const newStartOn = Ext.Date.parse(form.getValues().starton + ' 00:00:00', 'Y-m-d H:i:s');
												const newEndOn = Ext.Date.parse(form.getValues().endon + ' 23:59:59', 'Y-m-d H:i:s');
                                                const id = form.down('#id').getValue();
                                                // Update the record with the new values
												var newStartOn2 = Ext.Date.parse(form.getValues().starton, 'Y-m-d');
												var offset = Ext.Date.format(newStartOn2, 'O').replace(/(\d{2})(\d{2})/, '$1:$2');
												var formattedDateStartOn = Ext.Date.format(newStartOn2, 'Y-m-d\\TH:i:s') + offset;
												var newEndOn2 = Ext.Date.parse(form.getValues().endon, 'Y-m-d');
												var offset = Ext.Date.format(newEndOn2, 'O').replace(/(\d{2})(\d{2})/, '$1:$2');
												var formattedDateEndOn = Ext.Date.format(newEndOn2, 'Y-m-d\\TH:i:s') + offset;
                                                record.set('starton', formattedDateStartOn);
                                                record.set('endon', formattedDateEndOn);
                                                // Close the modal window

												Ext.Ajax.request({
												url: 'index.php?hdl=managementfee&action=edit&partnercode=ALRAJHI',
												method: 'POST', // Use the appropriate HTTP method
												params: {
													starton: newStartOn,
													endon: newEndOn,
													id:id
												},
												success: function(response) {
													var data = Ext.decode(response.responseText);
													if (data.success === true) {
														Ext.Msg.alert("Success", "Data successfully change");
														editDateWindow.close();
													} else {
														Ext.Msg.alert("Failed", data.errmsg);
													}
												},
												failure: function(response) {
													var data = Ext.decode(response.responseText);
														 Ext.Msg.alert("Error", data.errmsg);
												}                              
												});

                                                
                                            }
                                        },
                                        {
                                            text: 'Cancel',
                                            handler: function () {
                                                // Close the modal window without saving changes
                                                editDateWindow.close();
                                            }
                                        }
                                    ]
                                });

                                editDateWindow.show();
                                // end handle edit item
                            }
                        },


                    }
                }, 

                {
                    // add new gram button 

    
                xtype: 'button',
                text: 'Add Management Fee',
                handler: function (addBtn) {
                    const addGramWindow = Ext.create('Ext.window.Window', {
                        title: 'Add Management Fee',
                        modal: true,
                        items: [
                            {
                                xtype: 'form',
                                layout: 'form',
                                labelWidth: 100, // Adjust this value as needed
                                labelAlign: 'right', // Align labels to the right
                                labelSeparator: '',
                                items: [
                                    {
                                        xtype: 'container',
                                        layout: 'vbox',
                                        margin: '0 0 10 0', // Add margin to create space below this conta
                                        items: [
                                            {
                                                xtype: 'numberfield',
                                                fieldLabel: 'Min Amount',
                                                name: 'minamount',
                                                itemId:'minAmountField',
                                                allowBlank: false,
                                                width: 300,
                                                value: 0, // Set default value to 0
												minValue: 0,
												decimalPrecision: 6
                                            },
                                            {
                                                xtype: 'numberfield',
                                                fieldLabel: 'Max Amount',
                                                name: 'maxamount',
                                                itemId:'maxAmountField',
                                                allowBlank: false,
                                                width: 300,
                                                value: 0, // Set default value to 0
												minValue: 0,
												decimalPrecision: 6
                                            },
											{
                                                xtype: 'datefield',
                                                fieldLabel: 'Start Date',
                                                name: 'starton',
                                                itemId:'startOnField',
                                                allowBlank: true,
                                                width: 300,
												format: 'Y-m-d'
                                            },
											{
                                                xtype: 'datefield',
                                                fieldLabel: 'End Date',
                                                name: 'endon',
                                                itemId:'endOnField',
                                                allowBlank: true,
                                                width: 300,
												format: 'Y-m-d'
                                            }
                                        ]
                                    },
                                    {
                                        xtype: 'container',
                                        layout: 'hbox',
                                        labelWidth: 100, // Adjust this value as needed
                                        labelAlign: 'right', // Align labels to the right
                                        labelSeparator: '',
                                        margin: '0 0 10 0', // Add margin to create space below this conta
                                        items: [
                                            {
                                                xtype: 'label',
                                                text: 'Fee:',
                                                width: 100,
                                            },
                                            {
                                                xtype: 'button',
                                                iconCls: 'x-fa fa-minus',
                                                handler: function () {
                                                    const valueField = addGramWindow.down('[name="value"]');
													if (0 < valueField.getValue()) {
														valueField.setValue((parseFloat(valueField.getValue()) - 0.01).toFixed(2));
													}
                                                }
                                            },
                                            {
                                                xtype: 'numberfield',
                                                name: 'value',
                                                allowBlank: false,
                                                width: 120,
                                                margin: '0 5',
                                                decimalPrecision: 2,
                                                itemId: 'valueField', 
                                                value: 0,
												minValue: 0,
                                                fieldCls: 'hide-spinner-buttons'
                                            },
                                            {
                                                xtype: 'button',
                                                iconCls: 'x-fa fa-plus',
                                                handler: function () {
                                                    const valueField = addGramWindow.down('[name="value"]');
                                                    valueField.setValue((parseFloat(valueField.getValue()) + 0.01).toFixed(2));
                                                }
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        buttons: [
                            {
                                text: 'Save',
                                handler: function () {
                                    const form = addGramWindow.down('form');
                                    const minAmount = form.down('#minAmountField').getValue();
                                    const maxAmount = form.down('#maxAmountField').getValue();
                                    const startOn = Ext.Date.parse(form.getValues().starton + ' 00:00:00', 'Y-m-d H:i:s');
                                    const endOn = Ext.Date.parse(form.getValues().endon + ' 23:59:59', 'Y-m-d H:i:s');
                                    const value = form.down('#valueField').getValue();
									const viewModel = managementfeeview.getViewModel();
									const period = viewModel.get('period');
									const attempt = viewModel.get('attempt');
									const jobperiod = viewModel.get('jobperiod');

                                    if (form.isValid()) {
                                            Ext.Ajax.request({
                                            url: 'index.php?hdl=managementfee&action=add&partnercode=ALRAJHI',
                                            method: 'POST', // Use the appropriate HTTP method
                                            params: {
                                                avgdailygoldbalancegramfrom: minAmount,
                                                avgdailygoldbalancegramto: maxAmount,
                                                feeamount: value,
                                                status:1,
                                                name: '>='+minAmount+"-"+maxAmount,
                                                period: period,
                                                attempt: attempt,
                                                jobperiod: jobperiod,
                                                starton: startOn,
                                                endon: endOn,
                                            },
                                            success: function(response) {
                                                var data = Ext.decode(response.responseText);
                                                if (data.success === true) {
                                                    //Ext.Msg.alert("Success", "Data successfully added");
													Ext.Msg.alert('Success', 'Data successfully added', function (btn) {
														if (btn === 'ok') {
															// Your code to be executed after clicking "OK"
															addGramWindow.close();
															addBtn.prev('gridpanel').getStore().load();
														}
													});

                                                } else {
													Ext.Msg.alert("Failed", data.errmsg);
												}
                                            },
                                            failure: function(response) {
                                                var data = Ext.decode(response.responseText);
                                                     Ext.Msg.alert("Error", data.errmsg);
                                            }                              
                                            });
                                    }
                                }
                            },
                            {
                                text: 'Closed',
                                handler: function () {
                                    addGramWindow.close();
                                }
                            }
                        ]
                    });

                    addGramWindow.show();
                },
                hidden: true,
                width: '100%'
                },// end add new gram button
            ],
        }
        const rightSide = {
                xtype: 'container',
                width: '50%', // Adjust the width as needed
                html: '', // You can add any content here or leave it empty
            }

        this.items = [leftSide, rightSide];
        var countTemp =0;
        this.dockedItems = [
            {
                xtype: 'toolbar',
                dock: 'top',
                items: [
					{
						xtype: 'button',
						text: 'Add',
						iconCls: 'x-fa fa-plus',
						handler: function (addBtn) {
							const addGramWindow = Ext.create('Ext.window.Window', {
								title: 'Add Management Fee',
								modal: true,
								items: [{
									xtype: 'form',
									layout: 'form',
									labelWidth: 100, // Adjust this value as needed
									labelAlign: 'right', // Align labels to the right
									labelSeparator: '',
									items: [{
											xtype: 'container',
											layout: 'vbox',
											margin: '0 0 10 0', // Add margin to create space below this conta
											items: [{
													xtype: 'numberfield',
													fieldLabel: 'Min Amount',
													name: 'minamount',
													itemId: 'minAmountField',
													allowBlank: false,
													width: 300,
													value: 0, // Set default value to 0
													minValue: 0,
													decimalPrecision: 6
												},
												{
													xtype: 'numberfield',
													fieldLabel: 'Max Amount',
													name: 'maxamount',
													itemId: 'maxAmountField',
													allowBlank: false,
													width: 300,
													value: 0, // Set default value to 0
													minValue: 0,
													decimalPrecision: 6
												},
												{
													xtype: 'datefield',
													fieldLabel: 'Start Date',
													name: 'starton',
													itemId: 'startOnField',
													allowBlank: true,
													width: 300,
													format: 'Y-m-d'
												},
												{
													xtype: 'datefield',
													fieldLabel: 'End Date',
													name: 'endon',
													itemId: 'endOnField',
													allowBlank: true,
													width: 300,
													format: 'Y-m-d'
												}
											]
										},
										{
											xtype: 'container',
											layout: 'hbox',
											labelWidth: 100, // Adjust this value as needed
											labelAlign: 'right', // Align labels to the right
											labelSeparator: '',
											margin: '0 0 10 0', // Add margin to create space below this conta
											items: [{
													xtype: 'label',
													text: 'Fee:',
													width: 100,
												},
												{
													xtype: 'button',
													iconCls: 'x-fa fa-minus',
													handler: function() {
														const valueField = addGramWindow.down('[name="value"]');
														if (0 < valueField.getValue()) {
															valueField.setValue((parseFloat(valueField.getValue()) - 0.01).toFixed(2));
														}
													}
												},
												{
													xtype: 'numberfield',
													name: 'value',
													allowBlank: false,
													width: 120,
													margin: '0 5',
													decimalPrecision: 2,
													itemId: 'valueField',
													value: 0,
													minValue: 0,
													fieldCls: 'hide-spinner-buttons'
												},
												{
													xtype: 'button',
													iconCls: 'x-fa fa-plus',
													handler: function() {
														const valueField = addGramWindow.down('[name="value"]');
														valueField.setValue((parseFloat(valueField.getValue()) + 0.01).toFixed(2));
													}
												}
											]
										}
									]
								}],
								buttons: [{
										text: 'Save',
										handler: function() {
											const form = addGramWindow.down('form');
											const minAmount = form.down('#minAmountField').getValue();
											const maxAmount = form.down('#maxAmountField').getValue();
											const startOn = Ext.Date.parse(form.getValues().starton + ' 00:00:00', 'Y-m-d H:i:s');
											const endOn = Ext.Date.parse(form.getValues().endon + ' 23:59:59', 'Y-m-d H:i:s');
											const value = form.down('#valueField').getValue();
											const viewModel = managementfeeview.getViewModel();
											const period = viewModel.get('period');
											const attempt = viewModel.get('attempt');
											const jobperiod = viewModel.get('jobperiod');

											if (form.isValid()) {
												Ext.Ajax.request({
													url: 'index.php?hdl=managementfee&action=add&partnercode=ALRAJHI',
													method: 'POST', // Use the appropriate HTTP method
													params: {
														avgdailygoldbalancegramfrom: minAmount,
														avgdailygoldbalancegramto: maxAmount,
														feeamount: value,
														status: 2,
														name: '>=' + minAmount + "-" + maxAmount,
														period: period,
														attempt: attempt,
														jobperiod: jobperiod,
														starton: startOn,
														endon: endOn,
													},
													success: function(response) {
														var data = Ext.decode(response.responseText);
														if (data.success === true) {
															//Ext.Msg.alert("Success", "Data successfully added");
															Ext.Msg.alert('Success', 'Management fee pending approval, inform checker to approve the management fee.', function(btn) {
																if (btn === 'ok') {
																	// Your code to be executed after clicking "OK"
																	addGramWindow.close();
																	me.down('#managementfeegridpanel').getStore().load();
																}
															});

														} else {
															Ext.Msg.alert("Failed", data.errmsg);
														}
													},
													failure: function(response) {
														var data = Ext.decode(response.responseText);
														Ext.Msg.alert("Error", data.errmsg);
													}
												});
											}
										}
									},
									{
										text: 'Closed',
										handler: function() {
											addGramWindow.close();
										}
									}
								]
							});

							addGramWindow.show();
						}
					},
                    {
                        xtype: 'button',
                        text: 'Edit',
                        iconCls: 'x-fa fa-cog',
                        handler: function () {
							var sm = me.down('#managementfeegridpanel').getSelectionModel();
							var selectedRecords = sm.getSelection();
							if (selectedRecords.length == 1) {
								for(var i = 0; i < selectedRecords.length; i++) {
									selectedID = selectedRecords[i].get('id');
									selectedRecord = selectedRecords[i];
									break;
								}
							} else if(selectedRecords.length == 0) {
								Ext.MessageBox.show({
									title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
									msg: 'Select a record first'});
								return;
							}
							
							const editGramWindow = Ext.create('Ext.window.Window', {
								title: 'Edit Management Fee',
								modal: true,
								items: [{
									xtype: 'form',
									layout: 'form',
									labelWidth: 100, // Adjust this value as needed
									labelAlign: 'right', // Align labels to the right
									labelSeparator: '',
									items: [{
											xtype: 'container',
											layout: 'vbox',
											margin: '0 0 10 0', // Add margin to create space below this conta
											items: [{
													xtype: 'numberfield',
													fieldLabel: 'Min Amount',
													name: 'avgdailygoldbalancegramfrom',
													itemId: 'minAmountField',
													allowBlank: false,
													width: 300,
													value: 0, // Set default value to 0
													minValue: 0,
													decimalPrecision: 6
												},
												{
													xtype: 'numberfield',
													fieldLabel: 'Max Amount',
													name: 'avgdailygoldbalancegramto',
													itemId: 'maxAmountField',
													allowBlank: false,
													width: 300,
													value: 0, // Set default value to 0
													minValue: 0,
													decimalPrecision: 6
												},
												{
													xtype: 'datefield',
													fieldLabel: 'Start Date',
													name: 'starton',
													itemId: 'startOnField',
													allowBlank: true,
													width: 300,
													format: 'Y-m-d'
												},
												{
													xtype: 'datefield',
													fieldLabel: 'End Date',
													name: 'endon',
													itemId: 'endOnField',
													allowBlank: true,
													width: 300,
													format: 'Y-m-d'
												}
											]
										},
										{
											xtype: 'container',
											layout: 'hbox',
											labelWidth: 100, // Adjust this value as needed
											labelAlign: 'right', // Align labels to the right
											labelSeparator: '',
											margin: '0 0 10 0', // Add margin to create space below this conta
											items: [{
													xtype: 'label',
													text: 'Fee:',
													width: 100,
												},
												{
													xtype: 'button',
													iconCls: 'x-fa fa-minus',
													handler: function() {
														const valueField = editGramWindow.down('[name="feeamount"]');
														if (0 < valueField.getValue()) {
															valueField.setValue((parseFloat(valueField.getValue()) - 0.01).toFixed(2));
														}
													}
												},
												{
													xtype: 'numberfield',
													name: 'feeamount',
													allowBlank: false,
													width: 120,
													margin: '0 5',
													decimalPrecision: 2,
													itemId: 'valueField',
													value: 0,
													minValue: 0,
													fieldCls: 'hide-spinner-buttons'
												},
												{
													xtype: 'button',
													iconCls: 'x-fa fa-plus',
													handler: function() {
														const valueField = editGramWindow.down('[name="feeamount"]');
														valueField.setValue((parseFloat(valueField.getValue()) + 0.01).toFixed(2));
													}
												}
											]
										}
									]
								}],
								buttons: [{
										text: 'Save',
										handler: function() {
											const form = editGramWindow.down('form');
											const minAmount = form.down('#minAmountField').getValue();
											const maxAmount = form.down('#maxAmountField').getValue();
											const startOn = Ext.Date.parse(form.getValues().starton + ' 00:00:00', 'Y-m-d H:i:s');
											const endOn = Ext.Date.parse(form.getValues().endon + ' 23:59:59', 'Y-m-d H:i:s');
											const value = form.down('#valueField').getValue();
											const viewModel = managementfeeview.getViewModel();
											const period = viewModel.get('period');
											const attempt = viewModel.get('attempt');
											const jobperiod = viewModel.get('jobperiod');

											if (form.isValid()) {
												Ext.Ajax.request({
													url: 'index.php?hdl=managementfee&action=add&partnercode=ALRAJHI',
													method: 'POST', // Use the appropriate HTTP method
													params: {
														avgdailygoldbalancegramfrom: minAmount,
														avgdailygoldbalancegramto: maxAmount,
														feeamount: value,
														status: 2,
														name: '>=' + minAmount + "-" + maxAmount,
														period: period,
														attempt: attempt,
														jobperiod: jobperiod,
														starton: startOn,
														endon: endOn,
														parentid: selectedID,
													},
													success: function(response) {
														var data = Ext.decode(response.responseText);
														if (data.success === true) {
															//Ext.Msg.alert("Success", "Data successfully added");
															Ext.Msg.alert('Success', 'Management fee pending approval, inform checker to approve the management fee.', function(btn) {
																if (btn === 'ok') {
																	// Your code to be executed after clicking "OK"
																	editGramWindow.close();
																	me.down('#managementfeegridpanel').getStore().load();
																}
															});

														} else {
															Ext.Msg.alert("Failed", data.errmsg);
														}
													},
													failure: function(response) {
														var data = Ext.decode(response.responseText);
														Ext.Msg.alert("Error", data.errmsg);
													}
												});
											}
										}
									},
									{
										text: 'Closed',
										handler: function() {
											editGramWindow.close();
										}
									}
								]
							});

							var startOnYmd = Ext.Date.parse(selectedRecord.get('starton'), 'Y-m-d\\TH:i:sP');
							startOnYmd = Ext.Date.format(startOnYmd, 'Y-m-d');
							selectedRecord.set('starton', startOnYmd);
							var endOnYmd = Ext.Date.parse(selectedRecord.get('endon'), 'Y-m-d\\TH:i:sP');
							endOnYmd = Ext.Date.format(endOnYmd, 'Y-m-d');
							selectedRecord.set('endon', endOnYmd);
							
							editGramWindow.down('form').loadRecord(selectedRecord);
							editGramWindow.show();
            
                        }
                    },
                    {
                        xtype: 'button',
                        text: 'Refresh',
                        iconCls: 'x-fa fa-sync',
                        handler: function () {
                            me.editAllClicked = false;
                            me.down('gridpanel').getView().refresh();
                            isAddGramVisible = false;
                            me.down('button[text="Add Management Fee"]').setVisible(false);                          
                            store.reload();
                            countTemp = 0;
                        }
                    }
                ]
            },
			{
				xtype: 'container',
				hidden: true,
				layout: {
					type: 'hbox',
					//align: 'stretch'  // Optional, for stretching the textfield to full width
				},
				items: [
					{
						xtype: 'numberfield',
						labelWidth: 180,
						padding: '10 0 10 10',
						name: 'period',
						fieldLabel: 'Periodic Management Fee',
						allowBlank: false,  // requires a non-empty value
						minValue: 1,
						maxValue: 12,
						bind: {
							value: '{period}'
						}
					},
					{
						xtype: 'label',
						text: 'month',
						padding: '15 0 10 10',
						width: 50,
						style: {
							textAlign: 'center'
						}
					},
					{
                        xtype: 'button',
                        text: 'Save',
						margin: '10 0 10 10',
                        handler: function () {
                            // Get the value of the "period" field
							var periodField = this.up('container').down('[name=period]');
							var periodValue = periodField.getValue();
							if (null == periodValue) {
								alert('Please enter Periodic Management Fee');
								return;
							} else {
								Ext.Ajax.request({
									url: 'index.php?hdl=managementfee&action=addPeriod&partnercode=ALRAJHI',
									method: 'POST', // Use the appropriate HTTP method
									params: {
										period: periodValue
									},
									success: function(response) {
										var data = Ext.decode(response.responseText);
										if (data.success === true) {
											Ext.Msg.alert("Success", "Periodic Management Fee successfully added");
										} else {
											Ext.Msg.alert("Failed", data.errmsg);
										}
									},
									failure: function(response) {
										var data = Ext.decode(response.responseText);
											 Ext.Msg.alert("Error", data.errmsg);
									}                              
								});
							}
                        }
                    }
				]
			},
			{
				xtype: 'container',
				hidden: true,
				layout: {
					type: 'hbox',
					//align: 'stretch'  // Optional, for stretching the textfield to full width
				},
				items: [
					{
						xtype: 'numberfield',
						labelWidth: 180,
						padding: '10 0 10 10',
						name: 'attempt',
						fieldLabel: 'Attempting to collect the Management Fee',
						allowBlank: false,  // requires a non-empty value
						minValue: 1,
						maxValue: 10,
						bind: {
							value: '{attempt}'
						}
					},
					{
						xtype: 'label',
						text: 'times',
						padding: '20 0 10 10',
						width: 50,
						style: {
							textAlign: 'center'
						}
					},
					{
                        xtype: 'button',
                        text: 'Save',
						margin: '10 0 10 10',
                        handler: function () {
                            // Get the value of the "period" field
							var attemptField = this.up('container').down('[name=attempt]');
							var attemptValue = attemptField.getValue();
							if (null == attemptValue) {
								alert('Please enter Attempt to collect the Management Fee');
								return;
							} else {
								Ext.Ajax.request({
									url: 'index.php?hdl=managementfee&action=addAttempt&partnercode=ALRAJHI',
									method: 'POST', // Use the appropriate HTTP method
									params: {
										attempt: attemptValue
									},
									success: function(response) {
										var data = Ext.decode(response.responseText);
										if (data.success === true) {
											Ext.Msg.alert("Success", "Attempt to collect the Management Fee successfully added");
										} else {
											Ext.Msg.alert("Failed", data.errmsg);
										}
									},
									failure: function(response) {
										var data = Ext.decode(response.responseText);
											 Ext.Msg.alert("Error", data.errmsg);
									}                              
								});
							}
                        }
                    }
				]
			},
			{
				xtype: 'container',
				hidden: true,
				layout: {
					type: 'hbox',
					//align: 'stretch'  // Optional, for stretching the textfield to full width
				},
				items: [
					{
						xtype: 'numberfield',
						labelWidth: 180,
						padding: '10 0 10 10',
						name: 'jobperiod',
						fieldLabel: 'Cronjob to collect the management fee',
						allowBlank: false,  // requires a non-empty value
						minValue: 1,
						maxValue: 100,
						bind: {
							value: '{jobperiod}'
						}
					},
					{
						xtype: 'label',
						text: 'days',
						padding: '20 0 10 10',
						width: 50,
						style: {
							textAlign: 'center'
						}
					},
					{
                        xtype: 'button',
                        text: 'Save',
						margin: '10 0 10 10',
                        handler: function () {
                            // Get the value of the "period" field
							var jobperiodField = this.up('container').down('[name=jobperiod]');
							var jobperiodValue = jobperiodField.getValue();
							if (null == jobperiodValue) {
								alert('Please enter Cronjob to collect the management fee');
								return;
							} else {
								Ext.Ajax.request({
									url: 'index.php?hdl=managementfee&action=addJobPeriod&partnercode=ALRAJHI',
									method: 'POST', // Use the appropriate HTTP method
									params: {
										jobperiod: jobperiodValue
									},
									success: function(response) {
										var data = Ext.decode(response.responseText);
										if (data.success === true) {
											Ext.Msg.alert("Success", "Cronjob to collect the management fee successfully added");
										} else {
											Ext.Msg.alert("Failed", data.errmsg);
										}
									},
									failure: function(response) {
										var data = Ext.decode(response.responseText);
											 Ext.Msg.alert("Error", data.errmsg);
									}                              
								});
							}
                        }
                    }
				]
			}
        ];

        this.callParent(arguments);
    },
    // buttons : [

    // ]
});