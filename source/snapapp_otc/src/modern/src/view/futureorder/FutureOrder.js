Ext.define('snap.view.futureorder.FormModel', {
    extend: 'Ext.data.Model',
    fields: [
        { name: 'foproductitem', type: 'string' },
        { name: 'foamount', type: 'string' },
        { name: 'foweight', type: 'string' },       
        { name: 'fobuyprice', type: 'string' },       
        { name: 'fosellprice', type: 'string' },      
    ],

    validators: {		
        foproductitem: {
            type: 'presence', message: 'Product is required !'
        },	
    }
});
Ext.define('snap.view.futureorder.FutureOrder', {
	extend: 'Ext.form.Panel',
    xtype: 'mobfutureorder',

    requires: [
        'Ext.Button',
        'Ext.Img'
	],
	controller: 'futureorder-futureorder',
    viewModel: {
        type: 'futureorder-futureorder'
    },
	
    layout: {
        type: 'vbox',
        fullscreen: true,
	},
	title:'Future Order',
	header:{
		cls: 'panelhead-modern',
	},
	formBind:true,
	//defaultListenerScope: true,
	defaults: {
		errorTarget: 'under'
	},
	listeners: {
		initialize: function (view) {
			
			var view = this,
			
			model = Ext.create('snap.view.futureorder.FormModel', {
				
			});
			//console.log(view);
			view.setRecord(model);
			view.clearErrors(); 
		},
		
	},
    items: [			
		{ xtype: 'combobox',
			store: {
				autoLoad: true,
				type: 'ProductItems',
				sorters: 'name'
			},
			queryMode: 'local', 
			remoteFilter: false,
			name: 'foproductitem', 
			id: 'foproductitem', 
			valueField: 'id', 
			displayField: 'name',
			forceSelection: true, editable: false,allowBlank:false,label: 'Product Item', listeners: {
				focus: function(form, e) {	
					this.setRequired(true);
				}
			},
			
		},
		{ xtype: 'numberfield',label:'Total Value (RM)', name: 'foamount', id: 'foamount', reference: 'foamount',  maxLength:255,
			decimalSeparator: '.',			
			decimalPrecision : 4,
			config : {		
				roundValue : 6
			},	
			listeners: {
				focus: function(form, e) {		
					Ext.getCmp('foweight').reset();					
				},
				
			},			
		},
		{ xtype: 'numberfield',label:'Total Xau Weight (gram)', name: 'foweight',id: 'foweight', reference: 'foweight',  maxLength:255,
			decimalSeparator: '.',			
			decimalPrecision : 4,
			config : {		
				roundValue : 6
			},
			listeners: {
				focus: function(form, e) {	
					Ext.getCmp('foamount').reset();									
				}
			}
		},
		{ xtype: 'numberfield',label:'ACE Buy Price (RM/g)', name: 'fobuyprice', id: 'fobuyprice',reference: 'fobuyprice',  maxLength:255,
			decimalSeparator: '.',			
			decimalPrecision : 4,
			config : {		
				roundValue : 6
			},
			listeners: {
				focus: function(form, e) {	
					Ext.getCmp('fosellprice').reset();															
				}
			}
		},
		{ xtype: 'numberfield',label:'ACE Sell Price (RM/g)', name: 'fosellprice',id: 'fosellprice', reference: 'fosellprice',  maxLength:255,
			decimalSeparator: '.',			
			decimalPrecision : 4,
			config : {		
				roundValue : 6
			},
			listeners: {
				focus: function(form, e) {	
					Ext.getCmp('fobuyprice').reset();															
				}
			}
		},
		{ xtype: 'button',name:'fosubmitbtn',html: 'Queue Order',ui:'plain',style:'background-color:#1A5276;margin-top:30px;min-height:50px',handler: 'futureOrderAction'}
	]
});
