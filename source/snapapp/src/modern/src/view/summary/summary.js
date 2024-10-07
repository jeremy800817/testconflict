Ext.define('snap.view.summary.FormModel', {
    extend: 'Ext.data.Model',
    /* fields: [
        { name: 'summaryfromdate', type: 'string' },       
    ],

    validators: {		
        summaryfromdate: {
            type: 'presence', message: 'From date is required !'
        },	
    } */
});
Ext.define('snap.view.summary.summary', {
    extend: 'Ext.form.Panel',
    xtype: 'summary',
    requires: [
        'Ext.Button',
        'Ext.Img'
	],
	title:'Transaction Summary',

    bodyPadding: '0 20 20 20',
    formBind:true,
	//defaultListenerScope: true,
	defaults: {
		errorTarget: 'under'
	},
    config: {
        store: null
    },
    controller: 'summary-summary',
    viewModel: {
        type: 'summary-summary'
    },
    header:{
		cls: 'panelhead-modern',
	},
    listeners: {
		initialize: function (view) {
			
			var view = this,
			
			model = Ext.create('snap.view.summary.FormModel', {				
            });	           		
			view.setRecord(model);
			view.clearErrors(); 
		},
		
	},
    items: [
        { xtype: 'datefield',label:'From Date', name: 'summaryfromdate',id: 'summaryfromdate', reference: 'summaryfromdate',required:true,
            //dateFormat: 'Y-m-d', renderer: Ext.util.Format.dateRenderer('Y-m-d')
        },
        { xtype: 'datefield',label:'To Date', name: 'summarytodate',id: 'summarytodate', reference: 'summarytodate',required:true},
        { xtype: 'combobox',
            store: { type: 'array', fields: ['id','name'], 
                    data:[
                        [1,'Buy'],
                        [2,'Sell'],
                    ]
            },
			queryMode: 'local', 
            forceSelection: true, 
            editable: false,
			name: 'summarytype', 
            id: 'summarytype', 
            label:'Type',
            required:true,
			valueField: 'id', 
			displayField: 'name',
			
			
		},
        /* {
            xtype      : 'fieldcontainer',
            label : 'Type',
            defaultType: 'radiofield',
            defaults: {
                flex: 1
            },
                    
            layout: 'hbox',
            items: [
                {
                    boxLabel  : 'Buy',
                    name      : 'type',
                    inputValue: 'buy',
                    id        : 'radiotypebuy',
                    required:true,    
                }, {
                    boxLabel  : 'Sell',
                    name      : 'type',
                    inputValue: 'sell',
                    id        : 'radiotypesell',
                    required:true,    
                },
            ]
        }, */
        { xtype: 'button',name:'summarysubmitbtn',html: 'Get Summary',ui:'plain',style:'width:100%;background-color:#1A5276;margin-top:30px;min-height:50px',handler: 'summaryAction'}
    ],

});
