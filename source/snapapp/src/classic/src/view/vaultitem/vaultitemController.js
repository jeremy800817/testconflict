Ext.define('snap.view.vaultitem.vaultitemController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.vaultitem',
    showTransferForm:function(record){

        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection(); 
        var origintype = myView.up().type;

        // Initiallze values
        serialno = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;
        
        //console.log(selectedRecords[0].data.serialno);

        // Temp disable mbb check
        /*
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 1 && selectedRecords[0].data.vaultlocationid==3){
            Ext.MessageBox.show({
                title: 'Vault item transfer',
                msg: 'You have to wait until MBB initiates a return request on this item / Please refresh list',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.INFO
            });
            return false;
        }
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 0 && selectedRecords[0].data.vaultlocationid==1){
            Ext.MessageBox.show({
                title: 'Vault item transfer',
                msg: 'You have to wait until MBB initiates a request on this item / Please refresh list',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.INFO
            });
            return false;
        }*/

        /*
        var option1data=option2data=[];
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 1 && selectedRecords[0].data.vaultlocationid==1){
            var option1data = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":"1", "name":"ACE HQ"},                   
                ]
            });
            var option2data = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":"2", "name":"ACE G4S Rack"},                   
                ]
            }); 
        }
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 1 && selectedRecords[0].data.vaultlocationid==2){
            var option1data = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":"2", "name":"ACE G4S Rack"},               
                ]
            });
            var option2data = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":"3", "name":"MBB G4S Rack"},                   
                ]
            });
        }
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 0 && selectedRecords[0].data.vaultlocationid==3){
            var option1data = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":"3", "name":"MBB G4S Rack"},                                  
                ]
            });
            var option2data = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":"2", "name":"ACE G4S Rack"},               
                ]
            });
        }
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 0 && selectedRecords[0].data.vaultlocationid==2){
            var option1data = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":"2", "name":"ACE G4S Rack"},                                
                ]
            });
            var option2data = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":"1", "name":"ACE HQ"},              
                ]
            });
        }*/

        //*****************************TEMP SOLUTION BASED ON EXISTING CODE************************************/
        //locationdata = Ext.create('snap.store.VaultLocationData');
        // ** Determine whether its start or end ** //
        // if location is empty / location id is start 
        //var productitems =Ext.getStore('vaultlocationdatastore');
        //a = locationdata.load();
        //var productitems =Ext.getStore('productitemsstore');
        
        //debugger;

        // Scenario where 
        // Have serial Number
        // No physical bar
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 1 && selectedRecords[0].data.vaultlocationid==0){
            var defaultvalue = "1";
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":"ACE HQ", "value":defaultvalue},                   
                ],
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsStart',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsStart&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
                /*filters: [{
                    filterFn:function(record){
                        if('BMMB'== vmv.get('name')){
                            return (record.data.partnerid == 0 || record.data.partnerid == 1);
                        }else if('MIB'== vmv.get('name')){
                            return record.data.partnerid == 1;
                        }                                       
                    }
                }]*/
            }
        }// Emd Scenario

        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 1 && selectedRecords[0].data.vaultlocationid==1){
            var defaultvalue = selectedRecords[0].data.vaultlocationid;
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},                   
                ]
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsStart',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsStart&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
                /*filters: [{
                    filterFn:function(record){
                        if(partnerid=='1'){
                            alert("dada");
                            return record.data.value=='CourAce';
                        }else{
                            return record.data.value!='CourAce';
                        }                                       
                    }
                }]*/
            }
        }
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 1 && selectedRecords[0].data.vaultlocationid==2){
            var defaultvalue = selectedRecords[0].data.vaultlocationid;
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},               
                ]
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsIntermediate',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsIntermediate&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
                filters: [{
                    filterFn:function(record){
                        // Retun End Point when allocated 
                        return record.data.type=='End';
                                                         
                    }
                }]
            }
        }
        if(selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 0 || selectedRecords[0].data.allocated == 1) && (selectedRecords[0].data.vaultlocationid==3 || selectedRecords[0].data.vaultlocationid==4 || selectedRecords[0].data.vaultlocationid==7 || selectedRecords[0].data.vaultlocationid== 8)){
            var defaultvalue = selectedRecords[0].data.vaultlocationid;
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},                                  
                ]
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsEnd',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsEnd&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
            }
           
        }
        if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 0 && selectedRecords[0].data.vaultlocationid==2){
            var defaultvalue = selectedRecords[0].data.vaultlocationid;
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},                                
                ]
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsIntermediate',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsIntermediate&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
                filters: [{
                    filterFn:function(record){
                        // Return Start Point when not allocated
                        // No filter
                        // Return both start and finish locations
                        return record.data.type=='Start';
                                                      
                    }
                }]
            }
            
        }
        
        var transferpanel = new Ext.form.Panel({			
			frame: true,
            layout: 'vbox',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
            width: 580,
			items: [
                {
                    layout: 'column',
                    items:[
                        {
                            items: [ 
                                //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'serialno' , value:selectedRecords[0].data.serialno,allowBlank: false},	                      
                                /*{ 
                                    xtype: 'combobox',
                                    fieldLabel: 'From',
                                    name:'vaultfrom',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: option1data,             
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                    allowBlank: false
                                },*/ 
                                { 
                                    xtype: 'combobox',
                                    fieldLabel: 'From',
                                    name:'vaultfrom',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: fromlocation,               
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    valueField: 'value',
                                    value: defaultvalue,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                    allowBlank: false,
                                    /*listeners:{
                                        load:function (store, recs) {
                                            store.add({id:'1', name:'paprika'});  //adding empty record to enable deselection of assignment
                                        }
                                    }*/
                                },   
                            ]
                        },
                        {
                            items:[
                                { 
                                    xtype: 'combobox',
                                    fieldLabel: 'To',
                                    name:'vaultto',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: tolocation,               
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                    allowBlank: false,
                                },   
                                
                            ]
                        },	
                    ]
                },
                {
                    // xtype: 'datefield',
                    flex: 1,
                    // align: 'center',
                    xtype: 'datefield', 
                    format: 'Y-m-d H:i:s', 
                    fieldLabel: 'Document Date On', 
                    name: 'documentdateon',  
                    reference: 'documentdateon',
                },
                {
                    xtype:'panel',
                    flex: 10,
                    width: 580,
                    height: 230,
                    layout: {
                        type: 'hbox',
                        align: 'center',
                        pack: 'center'
                    }, 
                    items: [
                        {
                            xtype: "fieldset",
                            title: "Selected Serial Number(s)",
                            collapsible: false,
                            default: {
                                labelWidth: 30,
                            },
                            items: [
                                {
                                    xtype: "container",
                                    height: 150,
                                    width: 300,
                                    scrollable: true,
                                    id: 'vaultserialnumberselection',
                                    reference:
                                        "deliverystatusdisplayfield",
                                },
                            ],
                        },
                    ],
                },
			],						
        });

        // Add to transferpanel
        var panel = Ext.getCmp('vaultserialnumberselection');
        panel.removeAll();
        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;

            

              //date = data.createdon.date;
              //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");

            // Color if bar has physical or not
            // If no physical = orange
            // If have physical = green
            // The rest = purple
            if(selectedRecords[i].data.deliveryordernumber){
                color = "color: green ";
                label = count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' has physical bar">'+
                 serialno[i] +'</span>';
            }else{
                color = "color: orange ";
                label =  count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' does not have physical bar">'+
                 serialno[i] +'</span>';
            }
            panel.add({
                xtype: 'container',
                height: 30,
                //fieldStyle: 'background-color: #000000; background-image: none;',
                //scrollable: true,
                items: [{
                    xtype: 'displayfield', name:'serialnumber', value: value, reference: 'serialnumbers', fieldLabel: label, flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle: color,
                  },
                ]
              },);
           
            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            //count++;
        }

        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";

        var transferwindow = new Ext.Window({
            title: 'Transfer Item',
            layout: 'fit',
            width: 600,
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Transfer',
                handler: function(btn) {
                    if (transferpanel.getForm().isValid()) {
                        btn.disable();
                        // special action for bmmb 
                        /*
                        if(transferpanel.getForm().getValues().vaultfrom == 4){
                            var actionname = 'requestForTransferItemMultiple';
                        }else{
                            var actionname = 'requestForTransferItem';
                        }*/    
                        
                        vaultFrom = this.up().up().items.items[0].items.items[0].items.items[0].items.items[0].value;
                        vaultTo = this.up().up().items.items[0].items.items[0].items.items[1].items.items[0].value;
                        
                        transferpanel.getForm().submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            dataType: "json",
                            params: { hdl: 'vaultitem', 'action': 'requestForTransferItemMultiple', 'serialno[]': serialno,
                                        vaultfrom: vaultFrom,
                                        vaultto: vaultTo,
                             },
                            waitMsg: 'Processing',
                            success: function(frm, action) { //success                                   
                                Ext.MessageBox.show({
                                    title: 'Transfer Success',
                                    msg: 'Submitted Successfully',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.INFO
                                });
                                //debugger;
                                owningWindow = btn.up('window');
                                owningWindow.close();
                                myView.getSelectionModel().deselectAll();  
                                myView.getStore().reload();

                                myView.lookupReferenceHolder().lookupReference('summarycontainer').doFireEvent('reloadsummary');

                                snap.getApplication().getStore('snap.store.VaultItemTrans').reload()
                            },
                            failure: function(frm,action) {
                                btn.enable();                                    
                                var errmsg = action.result.errmsg;
                                if (action.failureType) {
                                    switch (action.failureType) {
                                        case Ext.form.action.Action.CLIENT_INVALID:
                                            console.log('client invalid');
                                            break;
                                        case Ext.form.action.Action.CONNECT_FAILURE:
                                            console.log('connect failure');
                                            break;
                                        case Ext.form.action.Action.SERVER_INVALID:
                                            console.log('server invalid');
                                            break;
                                    }
                                }
                                if (!action.result.errmsg || errmsg.length == 0) {
                                    errmsg = 'Error in form: ' + action.result.errorMessage;
                                }                                   
                                Ext.MessageBox.show({
                                    title: 'Message',
                                    msg: errmsg,
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });                            
                            }
                        });
                        }else{
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: 'All fields are required',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                } 
            },{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                }
            },
            // {
            //     text: 'Print',
            //     name: 'printButton',
            //     reference: 'printButton',
            //     hidden: true,
            //     listeners: {
            //         render: function(field, eOpts) {
            //             record = myView.getSelectionModel().getSelection()[0].data;
            //             if(record.serialno && record.deliveryordernumber){
            //                this.setHidden(false);
            //             }              

            //         }
            //     },
            //     handler: function(btn) {
                
            //        // Input function for print here

            //        // If doing check based on where to where 
            //        // Initialize record
            //        record = myView.getSelectionModel().getSelection()[0].data;

            //        // From ACE G4S 

            //        // This is from ACE HQ with physical bar, destination is not specified
            //        if(record.status == 1 && record.allocated == 1 && record.vaultlocationid == 1 ){
            //         // It can go from ACE HQ -> ACE G4S or ACE HQ -> MIB
            //         // Consignment or Internal DO
            //         // Print Specific Document
            //        }        

            //        // This is from ACE G4S, only destination is MIB
            //        if(record.status == 1 && record.allocated == 1 && record.vaultlocationid == 2 ){
            //         // From ACE G4S -> MIB
            //         // Print Specific Document
            //        }    

            //     }
            // }
        ],
            closeAction: 'destroy',
            items: transferpanel
        });

        transferwindow.show();
       

    },

    showTransferFormCommon:function(record){

        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection(); 
        var origintype = myView.up().type;

        // Initiallze values
        serialno = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;

        //*****************************TEMP SOLUTION BASED ON EXISTING CODE************************************/
        //locationdata = Ext.create('snap.store.VaultLocationData');
        // ** Determine whether its start or end ** //
        // if location is empty / location id is start 
        //var productitems =Ext.getStore('vaultlocationdatastore');
        //a = locationdata.load();
        //var productitems =Ext.getStore('productitemsstore');
        
        // Scenario where 
        // Have serial Number
        // Keep allocation
        if(selectedRecords[0].data.status == 1  && (selectedRecords[0].data.allocated == 0 || selectedRecords[0].data.allocated == 1) && selectedRecords[0].data.vaultlocationid==0){
            var defaultvalue = "1";
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":"ACE HQ", "value":defaultvalue},              
                ],
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsStart',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsStart&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
                /*filters: [{
                    filterFn:function(record){
                        if('BMMB'== vmv.get('name')){
                            return (record.data.partnerid == 0 || record.data.partnerid == 1);
                        }else if('MIB'== vmv.get('name')){
                            return record.data.partnerid == 1;
                        }                                       
                    }
                }]*/
            }
        }// Emd Scenario

        // Start from ACE HQ -> get G4S location
        if(selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 0 || selectedRecords[0].data.allocated == 1) && (selectedRecords[0].data.vaultlocationid==1 || selectedRecords[0].data.vaultlocationid==10)){
            var defaultvalue = selectedRecords[0].data.vaultlocationid;
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},                   
                ]
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsStart',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsStart&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
                /*filters: [{
                    filterFn:function(record){
                        if(partnerid=='1'){
                            alert("dada");
                            return record.data.value=='CourAce';
                        }else{
                            return record.data.value!='CourAce';
                        }                                       
                    }
                }]*/
            }
        }
         // Start from ACE HQ -> get G4S location for allocated transferring
         if(selectedRecords[0].data.status == 2 && (selectedRecords[0].data.allocated == 0 || selectedRecords[0].data.allocated == 1) && (selectedRecords[0].data.vaultlocationid==1 || selectedRecords[0].data.vaultlocationid==10)){
            var defaultvalue = selectedRecords[0].data.vaultlocationid;
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},                   
                ]
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsStart',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsStart&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
                /*filters: [{
                    filterFn:function(record){
                        if(partnerid=='1'){
                            alert("dada");
                            return record.data.value=='CourAce';
                        }else{
                            return record.data.value!='CourAce';
                        }                                       
                    }
                }]*/
            }
        }
        // At G4S location -> Get MBB 
        // if(selectedRecords[0].data.status == 1 && selectedRecords[0].data.allocated == 1 && (selectedRecords[0].data.vaultlocationid==2 || selectedRecords[0].data.vaultlocationid==9)){
        //     var defaultvalue = selectedRecords[0].data.vaultlocationid;
        //     var fromlocation = Ext.create('Ext.data.Store', {
        //         fields: ['id', 'name'],
        //         data : [
        //             {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},               
        //         ]
        //     });
        //     var tolocation = {
        //         autoLoad: true,
        //         type: 'VaultTransferLocationsIntermediate',                   
        //         sorters: 'name',
        //         proxy: {
        //             type: 'ajax',	       	
        //             url: 'index.php?hdl=vaultitem&action=getTransferLocationsIntermediate&origintype='+origintype,		
        //             reader: {
        //                 type: 'json',
        //                 rootProperty: 'locations',
        //                 idProperty: 'product_list'            
        //             },	
        //         },
        //         filters: [{
        //             filterFn:function(record){
        //                 // Retun End Point when allocated 
        //                 return record.data.type=='End';
                                                         
        //             }
        //         }]
        //     }
        // }
        // FROM MBB -> GET START AND INTERMEDIATE
        // if(selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 0 || selectedRecords[0].data.allocated == 1) && selectedRecords[0].data.vaultlocationid==3 || selectedRecords[0].data.vaultlocationid==4){
        //     var defaultvalue = selectedRecords[0].data.vaultlocationid;
        //     var fromlocation = Ext.create('Ext.data.Store', {
        //         fields: ['id', 'name'],
        //         data : [
        //             {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},                                  
        //         ]
        //     });
        //     var tolocation = {
        //         autoLoad: true,
        //         type: 'VaultTransferLocationsEnd',                   
        //         sorters: 'name',
        //         proxy: {
        //             type: 'ajax',	       	
        //             url: 'index.php?hdl=vaultitem&action=getTransferLocationsEnd&origintype='+origintype,		
        //             reader: {
        //                 type: 'json',
        //                 rootProperty: 'locations',
        //                 idProperty: 'product_list'            
        //             },	
        //         },
        //     }
           
        // }
        // fROM g4s BACK TO START 
        if(selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 0 || selectedRecords[0].data.allocated == 1) && (selectedRecords[0].data.vaultlocationid==2 || selectedRecords[0].data.vaultlocationid==9)){
            var defaultvalue = selectedRecords[0].data.vaultlocationid;
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},                                
                ]
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsIntermediate',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsIntermediate&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
                // filters: [{
                //     filterFn:function(record){
                //         // Return Start Point when not allocated
                //         // No filter
                //         // Return both start and finish locations
                //         return record.data.type=='Start';
                                                      
                //     }
                // }]
            }
            
        }

        if(selectedRecords[0].data.status == 1 && (selectedRecords[0].data.allocated == 0 || selectedRecords[0].data.allocated == 1) && (selectedRecords[0].data.vaultlocationid == 3 || selectedRecords[0].data.vaultlocationid == 4 || selectedRecords[0].data.vaultlocationid == 5 || selectedRecords[0].data.vaultlocationid == 6 || selectedRecords[0].data.vaultlocationid == 7 || selectedRecords[0].data.vaultlocationid == 8) ){
            var defaultvalue = selectedRecords[0].data.vaultlocationid;
            var fromlocation = Ext.create('Ext.data.Store', {
                fields: ['id', 'name'],
                data : [
                    {"id":selectedRecords[0].data.vaultlocationid, "name":selectedRecords[0].data.vaultlocationname, "value":defaultvalue},                                  
                ]
            });
            var tolocation = {
                autoLoad: true,
                type: 'VaultTransferLocationsEnd',                   
                sorters: 'name',
                proxy: {
                    type: 'ajax',	       	
                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsEnd&origintype='+origintype,		
                    reader: {
                        type: 'json',
                        rootProperty: 'locations',
                        idProperty: 'product_list'            
                    },	
                },
            }
           
        }
        
        var transferpanel = new Ext.form.Panel({			
			frame: true,
            layout: 'vbox',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
            width: 580,
			items: [
                {
                    layout: 'column',
                    items:[
                        {
                            items: [ 
                                //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'serialno' , value:selectedRecords[0].data.serialno,allowBlank: false},	                      
                                /*{ 
                                    xtype: 'combobox',
                                    fieldLabel: 'From',
                                    name:'vaultfrom',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: option1data,             
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                    allowBlank: false
                                },*/ 
                                { 
                                    xtype: 'combobox',
                                    fieldLabel: 'From',
                                    name:'vaultfrom',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: fromlocation,               
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    valueField: 'value',
                                    value: defaultvalue,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                    allowBlank: false,
                                    /*listeners:{
                                        load:function (store, recs) {
                                            store.add({id:'1', name:'paprika'});  //adding empty record to enable deselection of assignment
                                        }
                                    }*/
                                },   
                            ]
                        },
                        {
                            items:[
                                { 
                                    xtype: 'combobox',
                                    fieldLabel: 'To',
                                    name:'vaultto',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: tolocation,               
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                    allowBlank: false,
                                },   
                                
                            ]
                        },	
                    ]
                },
                {
                    // xtype: 'datefield',
                    flex: 1,
                    // align: 'center',
                    xtype: 'datefield', 
                    format: 'Y-m-d H:i:s', 
                    fieldLabel: 'Document Date On', 
                    name: 'documentdateon',  
                    reference: 'documentdateon',
                },
                {
                    xtype:'panel',
                    flex: 10,
                    width: 580,
                    height: 230,
                    layout: {
                        type: 'hbox',
                        align: 'center',
                        pack: 'center'
                    }, 
                    items: [
                        {
                            xtype: "fieldset",
                            title: "Selected Serial Number(s)",
                            collapsible: false,
                            default: {
                                labelWidth: 30,
                            },
                            items: [
                                {
                                    xtype: "container",
                                    height: 150,
                                    width: 300,
                                    scrollable: true,
                                    id: 'vaultserialnumberselection',
                                    reference:
                                        "deliverystatusdisplayfield",
                                },
                            ],
                        },
                    ],
                },
			],						
        });

        // Add to transferpanel
        var panel = Ext.getCmp('vaultserialnumberselection');
        panel.removeAll();
        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;

            

              //date = data.createdon.date;
              //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");

            // Color if bar has physical or not
            // If no physical = orange
            // If have physical = green
            // The rest = purple
            if(selectedRecords[i].data.deliveryordernumber){
                color = "color: green ";
                label = count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' has physical bar">'+
                 serialno[i] +'</span>';
            }else{
                color = "color: orange ";
                label =  count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' does not have physical bar">'+
                 serialno[i] +'</span>';
            }
            panel.add({
                xtype: 'container',
                height: 30,
                //fieldStyle: 'background-color: #000000; background-image: none;',
                //scrollable: true,
                items: [{
                    xtype: 'displayfield', name:'serialnumber', value: value, reference: 'serialnumbers', fieldLabel: label, flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle: color,
                  },
                ]
              },);
           
            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            //count++;
        }

        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";

        var transferwindow = new Ext.Window({
            title: 'Transfer Item',
            layout: 'fit',
            width: 600,
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Transfer',
                handler: function(btn) {
                    if (transferpanel.getForm().isValid()) {
                        btn.disable();
                        // special action for bmmb 
                        /*
                        if(transferpanel.getForm().getValues().vaultfrom == 4){
                            var actionname = 'requestForTransferItemMultiple';
                        }else{
                            var actionname = 'requestForTransferItem';
                        }*/    
                        
                        vaultFrom = this.up().up().items.items[0].items.items[0].items.items[0].items.items[0].value;
                        vaultTo = this.up().up().items.items[0].items.items[0].items.items[1].items.items[0].value;
                        
                        transferpanel.getForm().submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            dataType: "json",
                            params: { hdl: 'vaultitem', 'action': 'requestForTransferItemMultiple', 'serialno[]': serialno,
                                        vaultfrom: vaultFrom,
                                        vaultto: vaultTo,
                             },
                            waitMsg: 'Processing',
                            success: function(frm, action) { //success                                   
                                Ext.MessageBox.show({
                                    title: 'Transfer Success',
                                    msg: 'Submitted Successfully',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.INFO
                                });
                                //debugger;
                                owningWindow = btn.up('window');
                                owningWindow.close();
                                myView.getSelectionModel().deselectAll();  
                                myView.getStore().reload();

                                myView.lookupReferenceHolder().lookupReference('summarycontainer').doFireEvent('reloadsummary');

                                snap.getApplication().getStore('snap.store.VaultItemTrans').reload()
                            },
                            failure: function(frm,action) {
                                btn.enable();                                    
                                var errmsg = action.result.errmsg;
                                if (action.failureType) {
                                    switch (action.failureType) {
                                        case Ext.form.action.Action.CLIENT_INVALID:
                                            console.log('client invalid');
                                            break;
                                        case Ext.form.action.Action.CONNECT_FAILURE:
                                            console.log('connect failure');
                                            break;
                                        case Ext.form.action.Action.SERVER_INVALID:
                                            console.log('server invalid');
                                            break;
                                    }
                                }
                                if (!action.result.errmsg || errmsg.length == 0) {
                                    errmsg = 'Error in form: ' + action.result.errorMessage;
                                }                                   
                                Ext.MessageBox.show({
                                    title: 'Message',
                                    msg: errmsg,
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });                            
                            }
                        });
                        }else{
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: 'All fields are required',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                } 
            },{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                }
            },
          
        ],
            closeAction: 'destroy',
            items: transferpanel
        });

        transferwindow.show();
       

    },
    requestTransfer: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();            
        Ext.MessageBox.confirm(
            'Confirm', 'Are you sure you want to transfer item ?', function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'vaultitem', 'action': 'requestTransfer', serialno: selectedRecords[0].data.serialno
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {           
                                myView.getSelectionModel().deselectAll();                    
                                myView.getStore().reload(); 
                                
                                snap.getApplication().getStore('snap.store.VaultItemTrans').reload()
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errmsg,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });

    },
    myRequestTransfer: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        
      
       // Initiallze values
       serialno = [];
       serialNumberLine = "";
       selectedSerialNumbers = "";
       length = 0;
       count = 0;

        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;

            // Ace HQ default
            startLocation = 1;

            // Bmmb location
            if (partnerCode == 'BMMB'){
                endLocation = 4;
            }else if (partnerCode == 'BURSA'){
                endLocation = 8;
            }
    
           
            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            //count++;
        }
        
        /*
        // Do loop counter
        if(totalLength > 5){
            loopCount = 0 > (selectedRecords.length/5) ? 1 : Math.ceil(selectedRecords.length/5);
            for(i = 1; i < loopCount; i++){
                
            }

        }else {
            // Populate line
            selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
        }
        alert(loopCount);
        */
        
        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";
        
        
        // If starting location is ace
        if(startLocation == selectedRecords[0].data.vaultlocationid){
            messageText = 'Are you sure you want to transfer the following to ' + partnerCode + '? : \n' + layout;
            vaultTo = endLocation;
        }else{
            messageText = 'Are you sure you want to transfer item the following to ACE HQ? : \n' + layout;
            vaultTo = startLocation;
        }
        Ext.MessageBox.confirm(
            'Confirm', messageText, function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'vaultitem', 'action': 'requestForTransferItemMultipleConfirmation', 'serialno[]': serialno,
                        vaultfrom: selectedRecords[0].data.vaultlocationid,
                        vaultto: vaultTo,
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {     
                                myView.getSelectionModel().deselectAll();                    
                                myView.getStore().reload();   

                                snap.getApplication().getStore('snap.store.VaultItemTrans').reload()
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errmsg,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });
       

    },
    cancelTransfer: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        
        // Initiallze values
        serialno = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;
        
        
        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;;
           
             // If length is 5 or less, populate horizontally
             if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            
        }
        
        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";

        Ext.MessageBox.confirm(
            'Confirm', 'Are you sure you want to cancel transfer for the following items? \n' + layout, function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'vaultitem', 'action': 'cancelTransfer', 'serialno[]': serialno,
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {      
                                myView.getSelectionModel().deselectAll();                         
                                myView.getStore().reload();                                
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errmsg,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });

    },    
    confirmTransferOrReturn: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        var action='';      

        // Initiallze values
        serialno = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;
        
        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;

            // Ace HQ
            startLocation = 1;
           
            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            
        }
        
        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";
      
        // Check partner
        // If MBB = Proceed as usual
        // If BMMB = Custom Responses
        // Do checking 
        if("bmmb" == myView.up().type){
            // Check and modify visuals
            if(startLocation == selectedRecords[0].data.vaultlocationid){
                // If Transfer is made from ace -> bmmb not here do it in confirm
                messageText = 'You are receiving Serial Number from ACE: \n' + layout;
            }else{
                messageText = 'You are receiving Serial Number from BMMB: \n' + layout;
            }
            // Set do bmmb balance update
                isbmmbvaultbalance = true;

        } else if("mib" == myView.up().type){
            // Check and do something
            messageText = 'Are you sure you want to confirm ?';
            isbmmbvaultbalance = false;
        } if("bursa" == myView.up().type){
            // Check and modify visuals
            if(startLocation == selectedRecords[0].data.vaultlocationid){
                // If Transfer is made from ace -> bmmb not here do it in confirm
                messageText = 'You are receiving Serial Number from ACE: \n' + layout;
            }else{
                messageText = 'You are receiving Serial Number from BURSA: \n' + layout;
            }
            // Set do bmmb balance update
            //isbmmbvaultbalance = true;
            messageText = 'Are you sure you want to confirm ?';
            isbmmbvaultbalance = false;

        } else {
            // Do sometthing
            isbmmbvaultbalance = false;
        }

        if("mib" == myView.up().type){
            // Check and modify visuals
            ismib = true;
        }else{
            ismib = false;
        }

        Ext.MessageBox.confirm(
            'Confirm', messageText , function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'vaultitem', 'action': 'confirmTransfer', 'serialno[]': serialno, 'isbmmbvaultbalance': isbmmbvaultbalance, 'ismib': ismib, 'partnercode' : partnerCode,
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {  
                                // Do checking 
                                originType = myView.up().type;

                                reservedcount = originType + "reservedcount";
                                g4scount = originType + "g4scount";
                                vaultamount = originType + "vaultamount";
                                totalcustomerholding = originType + "totalcustomerholding";
                                totalbalance = originType + "totalbalance";

                                if("bmmb" == myView.up().type || "go" == myView.up().type || "one" == myView.up().type){
                                    // Check and modify visuals
                                    if(startLocation == selectedRecords[0].data.vaultlocationid){
                                        // If Transfer is made from ace -> destination not here do it in confirm
                                        Ext.get(reservedcount).dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count; 
                                        Ext.get(g4scount).dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;

                                        Ext.get(vaultamount).dom.innerHTML = data.balance.vaultamount;                                        
                                        Ext.get(totalcustomerholding).dom.innerHTML = data.balance.totalcustomerholding;
                                        Ext.get(totalbalance).dom.innerHTML = data.balance.totalbalance;
                                        //Ext.get('pendingtransactionbmmb').dom.innerHTML = data.balance.pendingtransaction;
                
                                        
                                    }else{
                                        Ext.get(reservedcount).dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) + count; 
                                        Ext.get(g4scount).dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) - count;

                                        Ext.get(vaultamount).dom.innerHTML = data.balance.vaultamount;                                        
                                        Ext.get(totalcustomerholding).dom.innerHTML = data.balance.totalcustomerholding;
                                        Ext.get(totalbalance).dom.innerHTML = data.balance.totalbalance;
                                        //Ext.get('pendingtransactionbmmb').dom.innerHTML = data.balance.pendingtransaction;
                                    }
                                } else if("mib" == myView.up().type){
                                    // Check and do something
                                    // Check and modify visuals
                                    if(startLocation == selectedRecords[0].data.vaultlocationid){
                                        // If Transfer is made from ace -> destination not here do it in confirm
                                        // transfer -> mbb

                                        //Ext.get('mibtransferringcount').dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count;
                                        
                                        // do check if added to acebgs or mbbg4s
                                        //Ext.get('mibvaultaceg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                        //Ext.get('mibvaultmbbg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                        
                                        
                                    }else{
                                        
                                        //Ext.get('mibtransferringcount').dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count;
                                        
                                        // do check if added to acebgs or mbbg4s
                                        //Ext.get('mibvaultaceg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                        //Ext.get('mibvaultmbbg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;

                                    }
                                    
                                } else {
                                    // Do sometthing
                                }
                                
                            
                                
                                myView.getSelectionModel().deselectAll();                         
                                myView.getStore().reload();                                
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errorMessage,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });

    },

    returnToHQ: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        var action='';      

        // Initiallze values
        serialno = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;
        
        // Experimental display 
        var transferpanel = new Ext.form.Panel({			
			frame: true,
            layout: 'vbox',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
            width: 580,
			items: [
                {
                    layout: 'column',
                    items:[
                        {
                            items: [ 
                                //{ xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'serialno' , value:selectedRecords[0].data.serialno,allowBlank: false},	                      
                                /*{ 
                                    xtype: 'combobox',
                                    fieldLabel: 'From',
                                    name:'vaultfrom',
                                    typeAhead: true,
                                    triggerAction: 'all',
                                    selectOnTab: true,
                                    store: option1data,             
                                    lazyRender: true,
                                    displayField: 'name',
                                    valueField: 'id',
                                    queryMode: 'remote',
                                    remoteFilter: false,
                                    listClass: 'x-combo-list-small',
                                    forceSelection: true,
                                    allowBlank: false
                                },*/ 
                                { 
                                    xtype: 'displayfield',
                                    fieldLabel: 'From',
                                    value: 'MBB G4S',
                                    margin: '10 20 0 40'
                                    /*listeners:{
                                        load:function (store, recs) {
                                            store.add({id:'1', name:'paprika'});  //adding empty record to enable deselection of assignment
                                        }
                                    }*/
                                },   
                            ]
                        },
                        {
                            items:[
                                { 
                                    xtype: 'displayfield',
                                    fieldLabel: 'To',
                                    value: 'ACE HQ',
                                    margin: '10 20 0 80'
                                },   
                                
                            ]
                        },	
                    ]
                },
                {
                    xtype:'panel',
                    flex: 10,
                    width: 580,
                    height: 230,
                    layout: {
                        type: 'hbox',
                        align: 'center',
                        pack: 'center'
                    }, 
                    items: [
                        {
                            xtype: "fieldset",
                            title: "Selected Serial Number(s)",
                            collapsible: false,
                            default: {
                                labelWidth: 30,
                            },
                            items: [
                                {
                                    xtype: "container",
                                    height: 150,
                                    width: 300,
                                    scrollable: true,
                                    id: 'vaultserialnumberselection',
                                    reference:
                                        "deliverystatusdisplayfield",
                                },
                            ],
                        },
                    ],
                },
			],						
        });

        
        // Add to transferpanel
        var panel = Ext.getCmp('vaultserialnumberselection');
        panel.removeAll();

        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;

            

              //date = data.createdon.date;
              //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");

            // Color if bar has physical or not
            // If no physical = orange
            // If have physical = green
            // The rest = purple
            if(selectedRecords[i].data.deliveryordernumber){
                color = "color: green ";
                label = count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' has physical bar">'+
                 serialno[i] +'</span>';
            }else{
                color = "color: orange ";
                label =  count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' does not have physical bar">'+
                 serialno[i] +'</span>';
            }
            panel.add({
                xtype: 'container',
                height: 30,
                //fieldStyle: 'background-color: #000000; background-image: none;',
                //scrollable: true,
                items: [{
                    xtype: 'displayfield', name:'serialnumber', value: value, reference: 'serialnumbers', fieldLabel: label, flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle: color,
                  },
                ]
              },);
           
            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            //count++;
        }
        // Check partner
        // If MBB = Proceed as usual
        // If BMMB = Custom Responses
        // Do checking 
        if("mib" == myView.up().type){
            // Check and do something
            messageText = 'Are you sure you want to return to HQ?';
        } else {
            messageText = 'Are you sure you want to confirm ?';
        }

        if("mib" == myView.up().type){
            // Check and modify visuals
            ismib = true;
        }else{
            ismib = false;
        }

        
        
        var transferwindow = new Ext.Window({
            title: 'Return to HQ',
            layout: 'fit',
            width: 600,
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Transfer',
                handler: function(btn) {
                    if (transferpanel.getForm().isValid()) {
                        btn.disable();
                        // special action for bmmb 
                        /*
                        if(transferpanel.getForm().getValues().vaultfrom == 4){
                            var actionname = 'requestForTransferItemMultiple';
                        }else{
                            var actionname = 'requestForTransferItem';
                        }*/    
                        snap.getApplication().sendRequest({
                            hdl: 'vaultitem', 'action': 'returnToHq', 'serialno[]': serialno, 'ismib': ismib, 
                        }, 'Sending request....').then(                        
                            function (data) {
                                if (data.success) {  
                                    // Do checking 
                                    originType = myView.up().type;
    
                                    reservedcount = originType + "reservedcount";
                                    g4scount = originType + "g4scount";
                                    vaultamount = originType + "vaultamount";
                                    totalcustomerholding = originType + "totalcustomerholding";
                                    totalbalance = originType + "totalbalance";
    
                                    if("bmmb" == myView.up().type || "go" == myView.up().type || "one" == myView.up().type){
                                        // Check and modify visuals
                                        if(startLocation == selectedRecords[0].data.vaultlocationid){
                                            // If Transfer is made from ace -> destination not here do it in confirm
                                            //Ext.get(reservedcount).dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count; 
                                            //Ext.get(g4scount).dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
    
                                            //Ext.get(vaultamount).dom.innerHTML = data.balance.vaultamount;                                        
                                            //Ext.get(totalcustomerholding).dom.innerHTML = data.balance.totalcustomerholding;
                                            //Ext.get(totalbalance).dom.innerHTML = data.balance.totalbalance;
                                            //Ext.get('pendingtransactionbmmb').dom.innerHTML = data.balance.pendingtransaction;
                    
                                            
                                        }else{
                                            //Ext.get(reservedcount).dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) + count; 
                                            //Ext.get(g4scount).dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) - count;
    
                                            //Ext.get(vaultamount).dom.innerHTML = data.balance.vaultamount;                                        
                                            //Ext.get(totalcustomerholding).dom.innerHTML = data.balance.totalcustomerholding;
                                            //Ext.get(totalbalance).dom.innerHTML = data.balance.totalbalance;
                                            //Ext.get('pendingtransactionbmmb').dom.innerHTML = data.balance.pendingtransaction;
                                        }
                                    } else if("mib" == myView.up().type){
                                        // Check and do something
                                        // Check and modify visuals
                                        Ext.MessageBox.show({
                                            title: 'Return to HQ',
                                            msg: 'Returned to HQ Successfully',
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO
                                        });

                                        owningWindow = btn.up('window');
                                        owningWindow.close();
                                        myView.getSelectionModel().deselectAll();  
                                        myView.getStore().reload();

                                        if(startLocation == selectedRecords[0].data.vaultlocationid){
                                            // If Transfer is made from ace -> destination not here do it in confirm
                                            // transfer -> mbb
    
                                            //Ext.get('mibtransferringcount').dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count;
                                            
                                            // do check if added to acebgs or mbbg4s
                                            //Ext.get('mibvaultaceg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                            //Ext.get('mibvaultmbbg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                            
                                            
                                        }else{
                                            
                                            //Ext.get('mibtransferringcount').dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count;
                                            
                                            // do check if added to acebgs or mbbg4s
                                            //Ext.get('mibvaultaceg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                            //Ext.get('mibvaultmbbg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
    
                                        }
                                        
                                    } else {
                                        // Do sometthing
                                    }
                                    
                                
                                    
                                    myView.getSelectionModel().deselectAll();                         
                                    myView.getStore().reload();                                
                                }else{
                                    Ext.MessageBox.show({
                                        title: 'Error Message',
                                        msg: data.errorMessage,
                                        buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                    });
                                }
                            });   
                        }
                } 
            },{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                }
            },
            ],
            closeAction: 'destroy',
            items: transferpanel
        });

        transferwindow.show();
        // End 
        // Old code
        /*
        Ext.MessageBox.confirm(
            'Confirm', messageText , function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'vaultitem', 'action': 'returnToHq', 'serialno[]': serialno, 'ismib': ismib, 
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {  
                                // Do checking 
                                originType = myView.up().type;

                                reservedcount = originType + "reservedcount";
                                g4scount = originType + "g4scount";
                                vaultamount = originType + "vaultamount";
                                totalcustomerholding = originType + "totalcustomerholding";
                                totalbalance = originType + "totalbalance";

                                if("bmmb" == myView.up().type || "go" == myView.up().type || "one" == myView.up().type){
                                    // Check and modify visuals
                                    if(startLocation == selectedRecords[0].data.vaultlocationid){
                                        // If Transfer is made from ace -> destination not here do it in confirm
                                        Ext.get(reservedcount).dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count; 
                                        Ext.get(g4scount).dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;

                                        Ext.get(vaultamount).dom.innerHTML = data.balance.vaultamount;                                        
                                        Ext.get(totalcustomerholding).dom.innerHTML = data.balance.totalcustomerholding;
                                        Ext.get(totalbalance).dom.innerHTML = data.balance.totalbalance;
                                        //Ext.get('pendingtransactionbmmb').dom.innerHTML = data.balance.pendingtransaction;
                
                                        
                                    }else{
                                        Ext.get(reservedcount).dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) + count; 
                                        Ext.get(g4scount).dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) - count;

                                        Ext.get(vaultamount).dom.innerHTML = data.balance.vaultamount;                                        
                                        Ext.get(totalcustomerholding).dom.innerHTML = data.balance.totalcustomerholding;
                                        Ext.get(totalbalance).dom.innerHTML = data.balance.totalbalance;
                                        //Ext.get('pendingtransactionbmmb').dom.innerHTML = data.balance.pendingtransaction;
                                    }
                                } else if("mib" == myView.up().type){
                                    // Check and do something
                                    // Check and modify visuals
                                    if(startLocation == selectedRecords[0].data.vaultlocationid){
                                        // If Transfer is made from ace -> destination not here do it in confirm
                                        // transfer -> mbb

                                        //Ext.get('mibtransferringcount').dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count;
                                        
                                        // do check if added to acebgs or mbbg4s
                                        //Ext.get('mibvaultaceg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                        //Ext.get('mibvaultmbbg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                        
                                        
                                    }else{
                                        
                                        //Ext.get('mibtransferringcount').dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count;
                                        
                                        // do check if added to acebgs or mbbg4s
                                        //Ext.get('mibvaultaceg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;
                                        //Ext.get('mibvaultmbbg4scount').dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;

                                    }
                                    
                                } else {
                                    // Do sometthing
                                }
                                
                            
                                
                                myView.getSelectionModel().deselectAll();                         
                                myView.getStore().reload();                                
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errorMessage,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });*/

    },

    returnToHqForce: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        var action='';      

        // Initiallze values
        serialno = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;
        
        // Experimental display 
        var transferpanel = new Ext.form.Panel({			
			frame: true,
            layout: 'vbox',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
            width: 580,
			items: [
                {
                    xtype:'panel',
                    flex: 10,
                    width: 580,
                    height: 230,
                    layout: {
                        type: 'hbox',
                        align: 'center',
                        pack: 'center'
                    }, 
                    items: [
                        {
                            xtype: "fieldset",
                            title: "Selected Serial Number(s)",
                            collapsible: false,
                            default: {
                                labelWidth: 30,
                            },
                            items: [
                                {
                                    xtype: "container",
                                    height: 150,
                                    width: 300,
                                    scrollable: true,
                                    id: 'vaultserialnumberselection',
                                    reference:
                                        "deliverystatusdisplayfield",
                                },
                            ],
                        },
                    ],
                },
			],						
        });

        
        // Add to transferpanel
        var panel = Ext.getCmp('vaultserialnumberselection');
        panel.removeAll();

        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;

            

              //date = data.createdon.date;
              //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");

            // Color if bar has physical or not
            // If no physical = orange
            // If have physical = green
            // The rest = purple
            if(selectedRecords[i].data.deliveryordernumber){
                color = "color: green ";
                label = count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' has physical bar">'+
                 serialno[i] +'</span>';
            }else{
                color = "color: orange ";
                label =  count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' does not have physical bar">'+
                 serialno[i] +'</span>';
            }
            panel.add({
                xtype: 'container',
                height: 30,
                //fieldStyle: 'background-color: #000000; background-image: none;',
                //scrollable: true,
                items: [{
                    xtype: 'displayfield', name:'serialnumber', value: value, reference: 'serialnumbers', fieldLabel: label, flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle: color,
                  },
                ]
              },);
           
            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            //count++;
        }
        
        messageText = 'Are you sure you want to return ?';
        
        var transferwindow = new Ext.Window({
            title: 'Return to HQ',
            layout: 'fit',
            width: 600,
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Transfer',
                handler: function(btn) {
                    if (transferpanel.getForm().isValid()) {
                        btn.disable();
                        snap.getApplication().sendRequest({
                            hdl: 'vaultitem', 'action': 'returnToHqForce', 'serialno[]': serialno,  
                        }, 'Sending request....').then(                        
                            function (data) {
                                if (data.success) {  
                                    // Do checking 
                                    originType = myView.up().type;
    
                                    reservedcount = originType + "reservedcount";
                                    g4scount = originType + "g4scount";
                                    vaultamount = originType + "vaultamount";
                                    totalcustomerholding = originType + "totalcustomerholding";
                                    totalbalance = originType + "totalbalance";
    
                                    Ext.MessageBox.show({
                                        title: 'Return to HQ',
                                        msg: 'Returned to HQ Successfully',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO
                                    });
                                    
                                    
                                    owningWindow = btn.up('window');
                                    owningWindow.close();
                                    myView.getSelectionModel().deselectAll();                         
                                    myView.getStore().reload();                                
                                }else{
                                    Ext.MessageBox.show({
                                        title: 'Error Message',
                                        msg: data.errorMessage,
                                        buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                    });
                                }
                            });   
                        }
                } 
            },{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                }
            },
            ],
            closeAction: 'destroy',
            items: transferpanel
        });

        transferwindow.show();

    },

    returnToHqForceDirect: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        var action='';      

        // Initiallze values
        serialno = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;
        
        // Experimental display 
        var transferpanel = new Ext.form.Panel({			
			frame: true,
            layout: 'vbox',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
            width: 580,
			items: [
                {
                    xtype:'panel',
                    flex: 10,
                    width: 580,
                    height: 230,
                    layout: {
                        type: 'hbox',
                        align: 'center',
                        pack: 'center'
                    }, 
                    items: [
                        {
                            xtype: "fieldset",
                            title: "Selected Serial Number(s)",
                            collapsible: false,
                            default: {
                                labelWidth: 30,
                            },
                            items: [
                                {
                                    xtype: "container",
                                    height: 150,
                                    width: 300,
                                    scrollable: true,
                                    id: 'vaultserialnumberselection',
                                    reference:
                                        "deliverystatusdisplayfield",
                                },
                            ],
                        },
                    ],
                },
			],						
        });

        
        // Add to transferpanel
        var panel = Ext.getCmp('vaultserialnumberselection');
        panel.removeAll();

        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;

            

              //date = data.createdon.date;
              //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");

            // Color if bar has physical or not
            // If no physical = orange
            // If have physical = green
            // The rest = purple
            if(selectedRecords[i].data.deliveryordernumber){
                color = "color: green ";
                label = count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' has physical bar">'+
                 serialno[i] +'</span>';
            }else{
                color = "color: orange ";
                label =  count.toString();
                value = '<span data-qtitle="'+serialno[i]+'" data-qwidth="200" '+
                'data-qtip="Selected '+serialno[i]+' does not have physical bar">'+
                 serialno[i] +'</span>';
            }
            panel.add({
                xtype: 'container',
                height: 30,
                //fieldStyle: 'background-color: #000000; background-image: none;',
                //scrollable: true,
                items: [{
                    xtype: 'displayfield', name:'serialnumber', value: value, reference: 'serialnumbers', fieldLabel: label, flex: 1, style:'padding-left: 20px;padding-right: 20px;', fieldStyle: color,
                  },
                ]
              },);
           
            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            //count++;
        }
        
        messageText = 'Are you sure you want to return ?';
        
        var transferwindow = new Ext.Window({
            title: 'Return to HQ',
            layout: 'fit',
            width: 600,
            maxHeight: 700,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Transfer',
                handler: function(btn) {
                    if (transferpanel.getForm().isValid()) {
                        btn.disable();
                        snap.getApplication().sendRequest({
                            hdl: 'vaultitem', 'action': 'returnToHqForce', 'serialno[]': serialno, 'direct': true,
                        }, 'Sending request....').then(                        
                            function (data) {
                                if (data.success) {  
                                    // Do checking 
                                    originType = myView.up().type;
    
                                    reservedcount = originType + "reservedcount";
                                    g4scount = originType + "g4scount";
                                    vaultamount = originType + "vaultamount";
                                    totalcustomerholding = originType + "totalcustomerholding";
                                    totalbalance = originType + "totalbalance";
    
                                    Ext.MessageBox.show({
                                        title: 'Return to HQ',
                                        msg: 'Returned to HQ Successfully',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO
                                    });
                                    
                                    
                                    owningWindow = btn.up('window');
                                    owningWindow.close();
                                    myView.getSelectionModel().deselectAll();                         
                                    myView.getStore().reload();                                
                                }else{
                                    Ext.MessageBox.show({
                                        title: 'Error Message',
                                        msg: data.errorMessage,
                                        buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                    });
                                }
                            });   
                        }
                } 
            },{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                }
            },
            ],
            closeAction: 'destroy',
            items: transferpanel
        });

        transferwindow.show();

    },

    returnItem: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        
        // Initialize values
        serialno = [];
        serialNumberLine = "";
        selectedSerialNumbers = "";
        length = 0;
        count = 0;

        isbmmbvaultbalance = 0;
        
        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;;
           
             // If length is 5 or less, populate horizontally
             if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            
        }

        if("bmmb" == myView.up().type){
            
            isbmmbvaultbalance = true;

        }else{
            isbmmbvaultbalance = false;
        }
        
        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";

        Ext.MessageBox.confirm(
            'Confirm', 'Are you sure you want to return ? : \n' + layout, function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'vaultitem', 'action': 'returnItem', 'serialno[]': serialno, partnerid: selectedRecords[0].data.partnerid, 'isbmmbvaultbalance': isbmmbvaultbalance,
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {  

                                // Do checking 
                                originType = myView.up().type;

                                reservedcount = originType + "reservedcount";
                                g4scount = originType + "g4scount";
                                vaultamount = originType + "vaultamount";
                                totalcustomerholding = originType + "totalcustomerholding";
                                totalbalance = originType + "totalbalance";

                                if("bmmb" == myView.up().type  || "go" == myView.up().type || "one" == myView.up().type){
                                    // Check and modify visuals
                                    if(1 == selectedRecords[0].data.vaultlocationid){
                                        // If Transfer is made from ace -> bmmb not here do it in confirm
                                        Ext.get(reservedcount).dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) - count; 
                                        Ext.get(g4scount).dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) + count;

                                        Ext.get(vaultamount).dom.innerHTML = data.balance.vaultamount;                                        
                                        Ext.get(totalcustomerholding).dom.innerHTML = data.balance.totalcustomerholding;
                                        Ext.get(totalbalance).dom.innerHTML = data.balance.totalbalance;
                                        //Ext.get('pendingtransactionbmmb').dom.innerHTML = data.balance.pendingtransaction;
                                       

                                    }else{
                                        Ext.get(reservedcount).dom.innerHTML = parseInt(Ext.get(reservedcount).dom.innerHTML) + count; 
                                        Ext.get(g4scount).dom.innerHTML = parseInt(Ext.get(g4scount).dom.innerHTML) - count;

                                        Ext.get(vaultamount).dom.innerHTML = data.balance.vaultamount;                                        
                                        Ext.get(totalcustomerholding).dom.innerHTML = data.balance.totalcustomerholding;
                                        Ext.get(totalbalance).dom.innerHTML = data.balance.totalbalance;
                                        //Ext.get('pendingtransactionbmmb').dom.innerHTML = data.balance.pendingtransaction;

                                    }
                                } else if("mib" == myView.up().type){
                                    // Check and do something
                                } else {
                                    // Do sometthing
                                }

                                myView.getSelectionModel().deselectAll();                                     
                                myView.getStore().reload();                                
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errmsg,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });

    },

    requestActivateItemForTransfer: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        
      
       // Initiallze values
       serialno = [];
       serialNumberLine = "";
       selectedSerialNumbers = "";
       length = 0;
       count = 0;

        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;

            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            //count++;
        }
        
        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";
        
        
        // If starting location is ace
        messageText = 'Are you sure you want to allocate the following? : \n' + layout;

        Ext.MessageBox.confirm(
            'Confirm', messageText, function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'vaultitem', 'action': 'requestActivateItemForTransfer', 'serialno[]': serialno,
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {     
                                myView.getSelectionModel().deselectAll();                    
                                myView.getStore().reload();   
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errmsg,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });
       

    },

    approvePendingItemForTransfer: function (record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();
        var selectedRecords = sm.getSelection();  
        
      
       // Initiallze values
       serialno = [];
       serialNumberLine = "";
       selectedSerialNumbers = "";
       length = 0;
       count = 0;

        for(i = 0; i < selectedRecords.length; i++){
            serialno[i] = selectedRecords[i].data.serialno;
            //records[i] = selectedRecords[i].data.movetovaultlocationid;

            // Set a length limit counter to populate for horizontal display
            // Add length counter 
            length++;
            count++;
           
            // If length is 5 or less, populate horizontally
            if(length <= 1){
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);

            }else {
                // Reset length
                length = 0;

                // Add last entry in line
                serialNumberLine += "&nbsp;" + count + ".&nbsp;" + selectedRecords[i].data.serialno + "&nbsp;".repeat(25);
                // Add length to a newline
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
                // Reset Serial Number Line
                serialNumberLine = "";
            }
            
            // Marks end of loop
            if(selectedRecords.length - count == 0){
                selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
            }
            
           
            //count++;
        }
        
        /*
        // Do loop counter
        if(totalLength > 5){
            loopCount = 0 > (selectedRecords.length/5) ? 1 : Math.ceil(selectedRecords.length/5);
            for(i = 1; i < loopCount; i++){
                
            }

        }else {
            // Populate line
            selectedSerialNumbers += '<p>' + serialNumberLine+ '</p>';
        }
        alert(loopCount);
        */

        layout = "<div style='height:100px;width:350px;border:1px solid #4e4e4e;overflow:auto;'>"+ selectedSerialNumbers  +"</div>";
        
        
        // If starting location is ace
        messageText = 'Are you sure you want to approve the allocation for the following? : \n' + layout;

        Ext.MessageBox.confirm(
            'Confirm', messageText, function (btn) {
                if (btn === 'yes') {
                    snap.getApplication().sendRequest({
                        hdl: 'vaultitem', 'action': 'approvePendingItemForTransfer', 'serialno[]': serialno,
                    }, 'Sending request....').then(                        
                        function (data) {
                            if (data.success) {     
                                myView.getSelectionModel().deselectAll();                    
                                myView.getStore().reload();   
                            }else{
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: data.errmsg,
                                    buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                });
                            }
                        });                    
                }                
            });
       

    },
    

    printButton: function(btn)  {
        // Add print function here 
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();     
        var address=selectedRecords[0].data.address1+' '+selectedRecords[0].data.address2+' '+selectedRecords[0].data.address3;
        
        var record = selectedRecords[0].data;
        

        var url = 'index.php?hdl=vaultitem&action=getPrintDocuments&id='+record.id;
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
                    msg: 'Failed to retrieve data.',
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        });

        // var url = 'index.php?hdl=logistic&action=getPrintDocuments';
        // Ext.DomHelper.append(document.body, {
        //     tag: 'iframe',
        //     id:'downloadIframe',
        //     frameBorder: 0,
        //     width: 0,
        //     height: 0,
        //     css: 'display:none;visibility:hidden;height: 0px;',
        //     src: url
        // });

        // MY CODE -- START
        var selectedRecords = sm.getSelection(); 
        // console.log(selectedRecords);return;
        var record = selectedRecords[0].data;

        snap.getApplication().sendRequest({
            hdl: 'vaultitem', action: 'getPrintDocuments', id: record.id, recordType: record.type,
        }, 'Fetching data from server....').then(
        //Received data from server already
        function(data){
            if(data.success){
                
            }
        });
        return false;
        // MY CODE -- END
    },

    createPrintButton: function(btn, formAction) {
        myView = this.getView();
        me = this;
        var gridFormView = Ext.create(myView.formClass, Ext.apply(myView.createvaultdocument ? myView.createvaultdocument : {}, {
            formDialogButtons: [{
                xtype:'panel',
                flex:1
            },
            {
                text: 'Create Document',
                flex: 2.5,
                handler: function(btn) {
                    me._onSaveGridForm(btn);
                }
            },{
                text: 'Close',
                flex: 1,
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                    me.gridFormView = null;
                }
            }]
        }));

        this.gridFormView = gridFormView;
        // this._formAction = "createDocuments";

        var addEditForm = this.gridFormView.down('form').getForm();

        gridFormView.title = 'Create ' + gridFormView.title + '...';
        
        this.gridFormView.show();

        // Set transfer note to default settings for myinventory
        if ("myvaultitemview" == btn.up().up().xtype){
            combobox = this.gridFormView.items.items[0].items.items[0].items.items[0].items.items[0].items.items[0];
            
            combobox.setValue(combobox.store.data.items[1].data.field1);

            combobox.readOnly = true;

            combobox.triggerEl.hide();
        }
     
    },

    printGoldBarListButton: function (elemnt) {
        
        var url = 'index.php?hdl=myvaultitem&action=doGoldBarList';
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

    printDocumentSelection: function(record) {
        var myView = this.getView(),
            me = this, record;
        var sm = myView.getSelectionModel();        
        var selectedRecords = sm.getSelection();  
        var partnerCode = this.view.up().partnerCode;   
          
        //var address=selectedRecords[0].data.address1+' '+selectedRecords[0].data.address2+' '+selectedRecords[0].data.address3;
        
        var record = selectedRecords[0].data;

        var address=selectedRecords[0].data.deliveryaddress1+' '+selectedRecords[0].data.deliveryaddress2+' '+selectedRecords[0].data.deliveryaddress3+' '+selectedRecords[0].data.deliverystate;
        var schedulepanel = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
			bodyPadding: 10,
			items: [
                {
                    items: [
                        { xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                        { 
                            xtype: 'combobox',
                            fieldLabel: 'From',
                            name:'vaultfrom',
                            typeAhead: true,
                            triggerAction: 'all',
                            selectOnTab: true,
                            store: {
                                autoLoad: true,
                                //type: 'VaultItem',                   
                                //sorters: 'name',
                                proxy: {
                                    type: 'ajax',	       	
                                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsAll&partnercode='+partnerCode,		
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'locations',
                                        idProperty: 'product_list'            
                                    },	
                                },
                            },               
                            lazyRender: true,
                            displayField: 'name',
                            valueField: 'id',
                            queryMode: 'remote',
                            remoteFilter: false,
                            listClass: 'x-combo-list-small',
                            forceSelection: true,
                            allowBlank: false
                        },     
                    ]
                },	
                {
                    items: [
                        { xtype: 'hiddenfield', inputType: 'hidden', hidden: true, name: 'id' , value:selectedRecords[0].id,allowBlank: false},	
                        { 
                            xtype: 'combobox',
                            fieldLabel: 'To',
                            name:'vaultto',
                            typeAhead: true,
                            triggerAction: 'all',
                            selectOnTab: true,
                            store: {
                                autoLoad: true,
                                //type: 'VaultItem',                   
                                //sorters: 'name',
                                proxy: {
                                    type: 'ajax',	       	
                                    url: 'index.php?hdl=vaultitem&action=getTransferLocationsAll&partnercode='+partnerCode,		
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'locations',
                                        idProperty: 'product_list'            
                                    },	
                                },
                            },               
                            lazyRender: true,
                            displayField: 'name',
                            valueField: 'id',
                            queryMode: 'remote',
                            remoteFilter: false,
                            listClass: 'x-combo-list-small',
                            forceSelection: true,
                            allowBlank: false
                        },     
                    ]
                },	
			],						
        });
        
        

        // Temp 
        // No meaning here
        // Should be checking if its in transferring
        var status=selectedRecords[0].get('status');     
        // Transferring
        if (status) {
            var type=selectedRecords[0].get('vendorname');            
            var salesmaninputform = new Ext.Window({
                title: 'Select locations for document to be printed..',
                layout: 'fit',
                width: 600,
                maxHeight: 700,
                modal: true,
                plain: true,
                buttonAlign: 'center',
                buttons: [{
                    text: 'Submit',
                    handler: function(btn) {
                        if (schedulepanel.getForm().isValid()) {
                            btn.disable();
                            from = schedulepanel.getForm().getValues().vaultfrom;
                            to = schedulepanel.getForm().getValues().vaultto;
                            var url = 'index.php?hdl=vaultitem&action=getPrintDocuments&id='+record.id+'&from='+from+'&to='+to;
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

                                        Ext.MessageBox.show({
                                            title: 'Print Document.',
                                            msg: 'Successful',
                                            buttons: Ext.MessageBox.OK,
                                            icon: Ext.MessageBox.INFO
                                        });

                                        owningWindow = btn.up('window');
                                        owningWindow.close(); 
                                },
                                failure: function () {
                                    
                                    Ext.MessageBox.show({
                                        title: 'Error Message',
                                        msg: 'Failed to retrieve data.',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.ERROR
                                    });
                                }
                            });
                            //debugger;
                            /*snap.getApplication().sendRequest({
                                hdl: 'vaultitem', action: 'getPrintDocuments', id: record.id, recordType: record.type,
                                from: schedulepanel.getForm().getValues().vaultfrom,
                                to: schedulepanel.getForm().getValues().vaultto,
                            }, 'Fetching data from server....').then(
                            //Received data from server already
                            function(data){
                                if(data.success){
                                    Ext.MessageBox.show({
                                        title: 'Print Document.',
                                        msg: 'Successful',
                                        buttons: Ext.MessageBox.OK,
                                        icon: Ext.MessageBox.INFO
                                    });
                                    var win = window.open('');
                                    win.location = url;
                                    win.close();
                                }else{
                                    Ext.MessageBox.show({
                                        title: 'Error Message',
                                        msg: data.errmsg,
                                        buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                    });
                                }
                            });*/
                            return false;
                            
                        }else{
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: 'All fields are required',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                    } 
                },{
                    text: 'Close',
                    handler: function(btn) {
                        owningWindow = btn.up('window');
                        owningWindow.close();
                    }
                }],
                closeAction: 'destroy',
                items: schedulepanel
            });

            salesmaninputform.show();
           
           
         }else{
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Please select request in transferring'});
         }        
    },

    onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm) {
        console.log(formAction, theGridFormPanel, theGridForm,'onPreAddEditSubmit');

        polists = theGridFormPanel.getController().lookupReference('pocontainer').getStore().data.items;
        scheduledate = theGridFormPanel.getController().lookupReference('scheduledate');
        
        
        trans_po = []
        // Update po with scheduled date
        polists.map(function(value, index){
            po = {
                "id": value.data.id,
            }
            trans_po.push(po);
            // total_weight += parseFloat(value.data.docTotalAmt)
        })

        customer = theGridFormPanel.getController().lookupReference('documentcombox').value
        req = {
            'po': trans_po,
            'type': customer,
        }
        // console.log(req);return;

        snap.getApplication().sendRequest({
            hdl: 'vaultitem', 
            action: 'createdocuments', 
            data: JSON.stringify(req),
            scheduledate:  scheduledate.getValue(),
        }, 'Fetching data from server....')
            .then(
                //Received data from server already
                function(data){
                    if(data.success){
                        Ext.MessageBox.show({
                            title: 'Create Document.',
                            msg: 'Successful',
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.INFO
                        });
                        return false;
                        // snap.getApplication().sendRequest({
                        //     hdl: 'vaultitem', action: 'getPrintDocuments', id: data.id,
                        // }, 'Fetching data from server....').then(
                        // //Received data from server already
                        // function(data){
                        //     if(data.success){
                                
                        //     }
                        // });
                        // return false;
                    }
            });
    },

    exportVaultListButton: function(btn){

        // grid header data
        header = []
        var partnerCode = this.view.up().partnerCode;   
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
                header.push(columnlist);
            }
        });

        startDate = '2000-01-01 00:00:00';
        endDate = '2100-01-01 23:59:59';
    
        daterange = {
            startDate: startDate,
            endDate: endDate,
        }

        header = encodeURI(JSON.stringify(header));
        daterange = encodeURI(JSON.stringify(daterange));

        url = '?hdl=vaultitem&action=exportVaultList&header='+header+'&daterange='+daterange+'&partnercode='+partnerCode;
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

    transactionButton: function(view){
        console.log(view)
    },

    reloadSummary: function(){
            type = 'mib',
            originType = type;

            snap.getApplication().sendRequest({
                hdl: 'vaultitem', action: 'getSummary',
                origintype : originType,
            }, 'Fetching data from server....').then(
                function (data) {
                    if (data.success) {
                        Ext.get('mibwithdocount').dom.innerHTML = data.withdocount;
                        Ext.get('mibwithoutdocount').dom.innerHTML = data.withoutdocount;
                        Ext.get('mibtransferringcount').dom.innerHTML = data.transferringcount;
                        
                        Ext.get('mibvaultreservedcount').dom.innerHTML = data.hqcount;
                        Ext.get('mibvaultaceg4scount').dom.innerHTML = data.aceg4scount;
                        Ext.get('mibvaultmbbg4scount').dom.innerHTML = data.mbbg4scount;
                        Ext.get('mibvaulttotalcount').dom.innerHTML = data.total;

                    }
                })
    }
});


