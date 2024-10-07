Ext.define('snap.view.spotorder.FormModel', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'productitem', type: 'string' },
        { name: 'amount', type: 'string' },
        { name: 'weight', type: 'string' },       
    ],

    validators: {		
        productitem: {
            type: 'presence', message: 'Product is required !'
        },
    }
});
Ext.define('snap.view.spotorder.SpotOrder', {
    extend: 'Ext.form.Panel',
    xtype: 'mobspotorder',
	id:'spotorder',
    requires: [
        'Ext.Button',
        'Ext.Img',
		'snap.store.PriceStreaming'
    ],
	store: 'priceStreaming',	
	controller: 'spotorder-spotorder',
    viewModel: {
        type: 'spotorder-spotorder'
    },
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
	title:'Spot Order',
	header:{
		cls: 'panelhead-modern',
	},
	formBind:true,	
	defaults: {
		errorTarget: 'under'
	},
	listeners: {
		initialize: function (view) {
			
			var view = this,
			model = Ext.create('snap.view.spotorder.FormModel', {
				
			});
			
		view.setRecord(model);
		view.clearErrors(); 
		},
	},

	
	onRender: function(){		
		Ext.create('snap.store.PriceStreaming');
	},
    items: [
		{
			xtype: 'panel',
			align: 'stretch',
			bodyStyle: 'margin: 20px 0px 40px 0px;font-size:1em',
			layout: {
				type: 'hbox',
				align: 'stretch',				
			},
			items:[{
				xtype: 'label',
				html: '<span style="font-weight:bold">Status</span>',
				flex:1				
			}, {
				xtype: 'label',
				html: '<span id="streaminstatus">Closed</span>',
				flex:1	
			}]       
		},		
		{ xtype: 'combobox',
			store: {
				autoLoad: true,
				type: 'ProductItems',
				sorters: 'name'
			},
			queryMode: 'local', 
			remoteFilter: false,
			name: 'productitem', 
			valueField: 'id', 
			displayField: 'name',
			forceSelection: true, editable: false,allowBlank:false,label: 'Product Item', listeners: {
				focus: function(form, e) {	
					this.setRequired(true);
				}
			}
		},	
		{ xtype: 'textfield', id: 'amount', name: 'amount', reference: 'amount',  maxLength:255, enableKeyEvents: true,label: 'Total Value (RM)', 	
			listeners: {
				focus: function(form, e) {								
					this.setReadOnly(false);
					this.setRequired(true);
					Ext.getCmp('weight').setRequired(false);
					Ext.getCmp('weight').setReadOnly(true);						
					Ext.getCmp('weight').setValue();	
				}
			}			
		},		
		{ xtype: 'textfield', id:'weight', name: 'weight', reference: 'weight', enableKeyEvents: true,label: 'Total Xau Weight (gram)',	required: false,
			listeners: {
				focus: function(form, e) {				
					this.setReadOnly(false);
					this.setRequired(true);
					Ext.getCmp('amount').setReadOnly(true);						
					Ext.getCmp('amount').setValue();		
					Ext.getCmp('amount').setRequired(false);	
				},				
			},			
		},
		{
			xtype: 'panel',
			align: 'stretch',			
			bodyStyle: 'margin: 20px 0px 40px 0px;font-size:1em',
			layout: {
				type: 'hbox',
				align: 'stretch',				
			},
			items:[{
				xtype: 'button',
				name:'sellbtn',		
				id:'sellbtn',
				reference:'sellbtn',
				html: '<span>ACE SELL</span><br><span id="spotorder_sell_price" style="font-size:1.5em">RM 0.000</span><br><span>per gram</span>',
				ui:'plain',
                style:'background-color:#669999',
				flex:1,
				handler: 'spotOrderAction'				
			}, {
				xtype: 'button',
				name:'buybtn',
				id:'buybtn',
				html: '<span>ACE BUY</span><br><span id="spotorder_buy_price" style="font-size:1.5em">RM 0.000</span><br><span>per gram</span>',
				flex:1	,
				ui:'plain',
                style:'background-color:#669999',
				handler: 'spotOrderAction'
			},
			
			{ xtype: 'hiddenfield', id: 'sellorbuy', name: 'sellorbuy', reference: 'sellorbuy'},
			{ xtype: 'hiddenfield', id: 'sellprice', name: 'sellprice', reference: 'sellprice'},
			{ xtype: 'hiddenfield', id: 'buyprice', name: 'buyprice', reference: 'buyprice'},
			{ xtype: 'hiddenfield', id: 'uuid', name: 'uuid', reference: 'uuid'}
			
			]       
		},        
    ]
});
