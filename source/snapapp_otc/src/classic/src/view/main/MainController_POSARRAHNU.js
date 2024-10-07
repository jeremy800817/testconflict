/**
 * This class is the controller for the main view for the application. It is specified as
 * the "controller" of the Main view class.
 */
 Ext.define('snap.view.main.MainController_POSARRAHNU', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.main_POSARRAHNU',
    init: function(view) {
        var me = this;
        // console.log(snap.getApplication()._sli);
        me.listen({
            controller: {
                '*': {
                    onUpdateNotificationBadge: me.onUpdateNotificationBadge
                }
            }
        });
        
        //init web socket connection
        if (Notification.permission === 'granted' && me.getNotificationStatus()) {
            me.openWebSocketConnection();
        }
        
        //notification denied
        if (Notification.permission === 'denied') {
            me.resetNotification(view);
        }
        
        //notification reset
        if (Notification.permission === 'default' && NOTIFICATIONSTATUS) {
            me.resetNotification(view);
        }
    },
    listen : {
        controller : {
            '#' : {
                unmatchedroute : 'onRouteChange',
            }
        }
    },

    routes: {
        ':node=:id': 'onRouteChange',
        ':node/:method/:item': 'onRouteToSubView'
    },

    onItemSelected: function (sender, record) {
        Ext.Msg.confirm('Confirm', 'Are you sure?', 'onConfirm', this);
    },

    onConfirm: function (choice) {
        if (choice === 'yes') {
            //
        }
    },

    onMainViewRender:function() {
        if (!window.location.hash) {
            if (snap.getApplication().direct != false){
                this.redirectTo(snap.getApplication().direct);
            }else{
                this.redirectTo("orderdashboardview_POSARRAHNU");
            }
        }
    },
    onUpdateNotificationBadge: function() {
        var data = snap.global.Vars.notifications
        var temp = Object.keys(data);
        var counter = 0;
        var view = this.getView();
        if(temp.length>0) {
            temp.forEach(ele=>{
                counter = counter + data[ele].count;
            })
        }
        if(counter > 0) {
            view.down('#notification_badge').setBadgeText(counter);
        } else {
            view.down('#notification_badge').setBadgeText('');
        }
    },
    onAfterRender: function() {
        var me = this;
        var view = this.getView();
        var runner = new Ext.util.TaskRunner();
        var runme = function() {
            //console.log('fetching notifications...');
            // Ext.Ajax.request({
            //     url: 'index.php?hdl=dashboard&action=getNotification',
            //     method: 'GET',
            //     success: function(response, opts) {
            //         var data = Ext.decode(response.responseText);
            //         var viewModel = me.getViewModel();
            //         if (data.success) {
            //             if(Object.keys(data.return_array).length>0) {
            //                 snap.global.Vars.notifications = data.return_array;
            //             } else {
            //                 snap.global.Vars.notifications = [];
            //             } 
            //             var temp = Object.keys(data.return_array);
            //             var counter = 0;
            //             if(temp.length>0) {
            //                 temp.forEach(ele=>{
            //                     counter = counter + data.return_array[ele].count;
            //                 })
            //             }
            //             if(counter > 0) {
            //                 view.down('#notification_badge').setBadgeText(counter);
            //             } else {
            //                 view.down('#notification_badge').setBadgeText('');
            //             }
            //         }
            //     },
            //     failure: function(response, opts) {
            //         console.log('server-side failure with status code ' + response.status);
            //     }
            //  });
        };
        var task = runner.start({
            run: runme,
            interval: 60000
        });
    },
    onClickNotification: function(temp) {
        // save to model first this action
        var viewModel = this.getViewModel();
        var view = this.getView();
        viewModel.set('showNotificationTab', true);
        snap.global.Vars.showNotificationTab = true;
        var temp = this.redirectTo("dashboardview");
        if(temp) {
            setTimeout(function(){
                var treelist = view.down('treelist'),
                    store = treelist.getStore(),
                    record = store.getAt(1);
                treelist.setSelection(null);
                treelist.setSelection(treelist.getStore().getRoot().firstChild);
                treelist.setSelection(record);
            }, 100);
            
        }
        this.fireEvent('notifcationIconClicked');
    },

    lastView: null,
    
    hashTagId: null,

    setCurrentView: function(hashTag, once) {
        hashTag = (hashTag || '').toLowerCase();

        var me = this,
            refs = me.getReferences(),
            mainCard = refs.mainCardPanel,
            mainLayout = mainCard.getLayout(),
            navigationList = refs.navigationTreeList,
            store = navigationList.getStore(),
            node = (store && store.findNode('routeId', hashTag)) ||
                   (store && store.findNode('viewType', hashTag)),
            viewModel = me.getViewModel(),
            view = (node && node.get('viewType')),
            lastView = me.lastView,
            existingItem = mainCard.child('component[routeId=' + hashTag + ']'),
            newView;

        //Added by Devon on 2017/5/10 to 
        //Have to delay the first time loading and retry it after menu has been loaded.....
        if(!view && !node && -1 != once) { 
            Ext.create('Ext.util.DelayedTask', function () {
                    let result = window.location.hash.match(/^#(.*)=(\d*)$/);
                    if (result) {
                        me.hashTagId = result[2];
                        me.redirectTo(hashTag);
                    } else {
                        me.setCurrentView(hashTag, -1);
                    }
                }).delay(10);
            return;
        }
        //Put in some bread crumb to identify the module.
        var webtrail = node.get('text'),
            trailNode = node.parentNode;
        while(trailNode.parentNode != null) {
            if(!trailNode.isExpanded()) trailNode.expand();
            webtrail = trailNode.get('text') + " <span class='fa fa-angle-right'></span> " + webtrail;
            trailNode = trailNode.parentNode;
        }
        var overrideTrail = viewModel.get('overrideWebtrail');
        viewModel.set('webtrail', overrideTrail || webtrail);
        viewModel.set('overrideWebtrail', null);

        //End Add by Devon on 2017/5/10
        
        // Kill any previously routed window
        if (lastView && lastView.isWindow) {
            lastView.destroy();
        }

        lastView = mainLayout.getActiveItem();
        if (!existingItem) {
            newView = Ext.create({
                xtype: view,
                routeId: hashTag,  // for existingItem search later
                hideMode: 'offsets',
                hashTagId: me.hashTagId
            });
            me.hashTagId = null;
        }

        if (!newView || !newView.isWindow) {
            // !newView means we have an existing view, but if the newView isWindow
            // we don't add it to the card layout.
            if (existingItem) {
                // We don't have a newView, so activate the existing view.
                if (existingItem !== lastView) {
                    mainLayout.setActiveItem(existingItem);
                    //reload store when existingItem have store
                    if (existingItem.store) {
                        existingItem.store.reload();
                    }
                }
                newView = existingItem;
            }
            else {
                // newView is set (did not exist already), so add it and make it the
                // activeItem.
                Ext.suspendLayouts();
                mainLayout.setActiveItem(mainCard.add(newView));
                Ext.resumeLayouts(true);
            }
        }

        // navigationList.setSelection(node);

        if (newView.isFocusable(true)) {
            newView.focus();
        }

        me.lastView = newView;
    },

    onNavigationTreeSelectionChange: function (tree, node) {
        var to = node && (node.get('routeId') || node.get('viewType'));

        if (to) {
            this.redirectTo(to);
        }
    },

    onToggleNavigationSize: function () {
        var me = this,
            refs = me.getReferences(),
            navigationList = refs.navigationTreeList,
            wrapContainer = refs.mainContainerWrap,
            collapsing = !navigationList.getMicro(),
            new_width = collapsing ? 56 : 250;

        if (Ext.isIE9m || !Ext.os.is.Desktop) {
            Ext.suspendLayouts();

            refs.senchaLogo.setWidth(new_width);

            navigationList.setWidth(new_width);
            navigationList.setMicro(collapsing);

            Ext.resumeLayouts(); // do not flush the layout here...

            // No animation for IE9 or lower...
            wrapContainer.layout.animatePolicy = wrapContainer.layout.animate = null;
            wrapContainer.updateLayout();  // ... since this will flush them
        }
        else {
            if (!collapsing) {
                // If we are leaving micro mode (expanding), we do that first so that the
                // text of the items in the navlist will be revealed by the animation.
                navigationList.setMicro(false);
                me.getViewModel().set('applogo', me.getViewModel().get('applogoNormal'));
            }
            navigationList.canMeasure = false;

            // Start this layout first since it does not require a layout
            refs.senchaLogo.animate({dynamic: true, to: {width: new_width}});

            // Directly adjust the width config and then run the main wrap container layout
            // as the root layout (it and its chidren). This will cause the adjusted size to
            // be flushed to the element and animate to that new size.
            navigationList.width = new_width;
            wrapContainer.updateLayout({isRoot: true});
            navigationList.el.addCls('nav-tree-animating');

            // We need to switch to micro mode on the navlist *after* the animation (this
            // allows the "sweep" to leave the item text in place until it is no longer
            // visible.
            if (collapsing) {
                navigationList.on({
                    afterlayoutanimation: function () {
                        navigationList.setMicro(true);
                        navigationList.el.removeCls('nav-tree-animating');
                        navigationList.canMeasure = true;
                        me.getViewModel().set('applogo', me.getViewModel().get('applogoMicro'));
                    },
                    single: true
                });
            }
        }
    },

    onRouteChange:function(id){
        this.setCurrentView(id);

        var view = this.getView();
        var treelist = view.down('treelist'),
            selection = treelist.getSelection(),
            msg = selection ? 'Selected: ' + selection.data.text : 'No selection';
        var redirection_temp = ['orderview', 'sponsorshipview', 'patientview', 'transferview'];
        if(msg == 'No selection' || redirection_temp.indexOf(id) != -1) {
            var task = new Ext.util.DelayedTask(function(){
                var store = treelist.getStore(),
                record = store.findNode('viewType', id);
                treelist.setSelection(record);
            });
            task.delay(100);
        }
    },

    onRouteToSubView: function(id, viewMethod, itemId) {
        var me = this;
        this.setCurrentView(id);
        var task = new Ext.util.DelayedTask(function(){
            //We will delay calling the function until the view is visible and if there is a store, data has been loaded.
            if( !me.lastView.isVisible() && (!me.lastView.store || me.lastView.store.isLoading())) task.delay(100);
            else {
                if(Ext.isFunction(me.lastView[viewMethod])) me.lastView[viewMethod](itemId);
                else if(me.lastView && me.lastView['getController']) {
                    var controller =  me.lastView.getController();
                    if(Ext.isFunction(controller[viewMethod])) controller[viewMethod](itemId);
                }
                task = null;
            }
        });
        task.delay(100);
    },

    onSearchRouteChange: function () {
        this.setCurrentView('searchresults');
    },

    _recursiveGetMenuItem: function( tree) {
        var app = snap.getApplication();
        var container = [];
        for(var i = 0; i < tree.length; i++) {
            var item = tree[i];
            if(! item.leaf && item.children) {
                item.children = this._recursiveGetMenuItem(item.children);
                if(item.children.length) container.push(item);
            } else {
                if(!item.permission || app.hasPermission(item.permission)) container.push(item);
            }
        }
        return container;
    },

    /**
     * We will initialise all our display menu to only those items that can be accessed by
     * the user.
     */
    initViewModel: function() {
        this.getViewModel().set('info', snap.getApplication().info);
        if (snap.getApplication().info.env == 'dev'){
            this.getViewModel().set('devcss', '<link rel="stylesheet" href="src/resources/css/devcss.css">');
            this.getViewModel().set('copyright', '<b>DEMO</b> - All Rights Reserved &copy; 2021 ACE Innovate Asia Berhad');
            this.getViewModel().set('version', '- <b>DEMO</b>');
            jQuery("title").prepend("DEMO-")
        }

        this.getViewModel().set('username', snap.getApplication().username);
        var treeItems = this._recursiveGetMenuItem(this.getViewModel().menuItems);
        this.getViewModel().set('navItems', {
            type: 'tree',
            root: {
                expanded: true,
                children: treeItems
            }
        });
    },

    onViewProfile: function() {
        var viewProfilePanel = new Ext.form.Panel({
            frame: false,
            layout: 'anchor',
            width: 800,
            maxHeight: 700,
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            waitMsgTarget: true,
            closeAction: 'destroy',
            defaults: {
                flex: 1
            },
            defaultType: 'textfield',
            fieldDefaults: {
                anchor: '100%',
                margin: '0 0 5 0',
                readOnly: true
            },
            items: [{
                itemId: 'profile_column_main',
                layout: 'column',
                xtype: 'fieldcontainer',
                defaults: {
                    columnWidth: 0.5
                },
                items: [{
                    xtype: 'fieldcontainer',
                    layout: {
                        type: 'vbox',
                        pack: 'start',
                        align: 'stretch'
                    },
                    items: [{
                        xtype: 'fieldset',
                        title: 'Picture',
                        layout: 'anchor',
                        height: 243,
                        items: [{
                            xtype: 'box',
                            layout: {
                                type: 'vbox',
                                pack: 'center',
                                align: 'center'
                            },
                            autoEl: {
                                tag: 'img',
                                src: '/src/resources/images/people.png',
                                style: 'display: block; margin: 0 auto; width: 180px; height: 180px;'
                            }
                        }]
                    },{
                        xtype: 'fieldset',
                        title: 'Contact Information',
                        layout: 'anchor',
                        height: 280,
                        defaultType: 'textfield',
                        fieldDefaults: {
                            anchor: '100%',
                            margin: '0 0 5 0',
                            readOnly: true
                        },
                        items: [{
                            fieldLabel: 'Email',
                            name: 'email'
                        },{
                            fieldLabel: 'Contact No',
                            name: 'contactno'
                        },{
                            fieldLabel: 'Address',
                            name: 'address'
                        },{
                            fieldLabel: 'District',
                            name: 'district'
                        },{
                            fieldLabel: 'Postcode',
                            name: 'postcode'
                        },{
                            fieldLabel: 'State',
                            name: 'state'
                        }]
                    }]
                },{
                    itemId: 'profile_column_2',
                    margin: '0 0 0 10',
                    xtype: 'fieldcontainer',
                    layout: {
                        type: 'vbox',
                        pack: 'start',
                        align: 'stretch'
                    },
                    items: [{
                        xtype: 'fieldset',
                        title: 'Main Information',
                        layout: 'anchor',
                        height: 243,
                        defaultType: 'textfield',
                        fieldDefaults: {
                            anchor: '100%',
                            margin: '0 0 5 0',
                            readOnly: true
                        },
                        items: [{
                            fieldLabel: 'Name',
                            name: 'name'
                        },{
                            fieldLabel: 'NRIC',
                            name: 'nirc'
                        },{
                            fieldLabel: 'Staff Code',
                            name: 'code',
                            fieldStyle: 'text-transform: uppercase'
                        },{
                            fieldLabel: 'Branch',
                            name: 'branchname'
                        },{
                            fieldLabel: 'Expire',
                            name: 'expire'
                        }]
                    },{
                        itemId: 'profile_role_fieldset',
                        xtype: 'fieldset',
                        title: 'Login Information',
                        layout: 'anchor',
                        height: 280,
                        defaultType: 'textfield',
                        fieldDefaults: {
                            anchor: '100%',
                            margin: '0 0 5 0',
                            readOnly: true
                        },
                        items: [{
                            fieldLabel: 'Username',
                            name: 'username'
                        },{
                            itemId: 'profile_userrole',
                            xtype: 'gridpanel',
                            store: Ext.create('Ext.data.ArrayStore', {
                                fields: [
                                    'role'
                                ]
                            }),
                            height: 180,
                            frame: true,
                            hideHeaders: false,
                            scrollable: 'vertical',
                            columnLines: true,
                            columns: [{
                                text: 'Role',
                                dataIndex: 'role',
                                flex: 1
                            }]
                        }]
                    }]
                }]
            }]
        });

        var viewProfileWindow = new Ext.Window({
            title: 'View Profile',
            layout: 'fit',
            width: 800,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Close',
                handler: function(btn) {
                    owningWindow = btn.up('window');
                    owningWindow.close();
                }
            }],
            closeAction: 'destroy',
            items: viewProfilePanel
        });

        snap.getApplication().sendRequest({
            hdl: 'staff', action: 'getuserprofile', id: snap.getApplication().user.id
        }, 'Fetching data from server....').then(
        function(data) {
            if (data.success) {
                var roleFieldset = viewProfilePanel.getComponent('profile_column_main').getComponent('profile_column_2').getComponent('profile_role_fieldset');
                var roleGrid = roleFieldset.getComponent('profile_userrole');
                var roleStore = roleGrid.getStore();
                roleStore.removeAll();
                roleStore.add(data.roledata);
                viewProfilePanel.getForm().setValues(data.userdata);
                viewProfileWindow.show();
            }
        });
    },

    onChangePassword: function() {
        /* var transferpanel = new Ext.form.Panel({			
			frame: true,
            layout: 'column',
            defaults: {
                columnWidth: .5,                
            },         
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            //defaultType: 'textfield',
            defaults: {                
                flex: 1
            },
            fieldDefaults: {
                labelWidth: 170,
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0'
            },
			items: [
                {
                   
                    xtype:'textfield',
                    hidden: true,
                    name: 'test',
                    value: snap.getApplication().username
                },
                {
                    xtype:'textfield',
                    inputType: 'password',
                    itemId: 'chg_currentpassword',
                    fieldLabel: 'Current Password',
                    name: 'oldpassword',
                    //allowBlank: false
                },{
                    xtype:'textfield',
                    inputType: 'password',
                    itemId: 'chg_newpassword',
                    fieldLabel: 'New Password',
                    name: 'userpassword',
                    //allowBlank: false,
                    minLength: 8,
                    maxLength: 20
                },{
                    xtype:'textfield',
                    inputType: 'password',
                    itemId: 'chg_confirmpassword',
                    fieldLabel: 'Confirm New Password',
                    name: 'confirmpassword',
                    //allowBlank: false
                }                                 
			],						
        });

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
                        transferpanel.getForm().submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            dataType: "json",
                            params: { hdl: 'user', action: 'changepassword' },
                            waitMsg: 'Processing',
                            success: function(frm, action) { //success                                   
                                Ext.MessageBox.show({
                                    title: 'Logistics creation',
                                    msg: 'Submitted Successfully',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.INFO
                                });
                                owningWindow = btn.up('window');
                                owningWindow.close();
                                myView.getSelectionModel().deselectAll();  
                                myView.getStore().reload();
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
            }],
            closeAction: 'destroy',
            items: transferpanel
        });
        transferwindow.show(); */
        var changePassFormPanel = new Ext.form.Panel({
            frame: true,
            layout: 'anchor',
            width: 480,
            maxHeight: 700,
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,
            waitMsgTarget: true,
            closeAction: 'destroy',
            border: 0,
            bodyBorder: false,
            bodyPadding: 10,            
            defaults: {                
                flex: 1
            },
            fieldDefaults: {
                labelWidth: 170,
                anchor: '100%',
                msgTarget: 'side',
                margin: '0 0 5 0'
            },
			items: [
                {                   
                    xtype:'textfield',
                    hidden: true,
                    name: 'user_name',
                    value: snap.getApplication().username
                },
                {
                    xtype:'textfield',
                    inputType: 'password',
                    itemId: 'chg_currentpassword',
                    fieldLabel: 'Current Password',
                    name: 'oldpassword',
                    //allowBlank: false
                },{
                    xtype:'textfield',
                    inputType: 'password',
                    itemId: 'chg_newpassword',
                    fieldLabel: 'New Password',
                    name: 'userpassword',
                    //allowBlank: false,
                    minLength: 8,
                    maxLength: 20
                },{
                    xtype:'textfield',
                    inputType: 'password',
                    itemId: 'chg_confirmpassword',
                    fieldLabel: 'Confirm New Password',
                    name: 'confirmpassword',
                    //allowBlank: false
                }                                 
			],			
        });

        var changePassWindow = new Ext.Window({
            title: 'Change Password',
            layout: 'fit',
            width: 480,
            modal: true,
            plain: true,
            buttonAlign: 'center',
            buttons: [{
                text: 'Save',
                handler: function(btn) {
                    if (changePassFormPanel.getForm().isValid()) {
                        var password = changePassFormPanel.getForm().findField('userpassword').getValue();
                        var confirmpassword = changePassFormPanel.getForm().findField('confirmpassword').getValue();
                        if (password != confirmpassword) {
                            changePassFormPanel.getForm().findField('confirmpassword').markInvalid('Password do not match');
                            btn.enable();
                            return false;
                        }

                        btn.disable();
                        changePassFormPanel.getForm().submit({
                            submitEmptyText: false,
                            url: 'index.php',
                            method: 'POST',
                            params: { hdl: 'user', action: 'changepassword' },
                            dataType: "json",
                            waitMsg: 'Processing',
                            success: function(frm, action) { //success
                                Ext.MessageBox.show({
                                    title: 'Change Password',
                                    msg: 'Success',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.INFO
                                });
                                owningWindow = btn.up('window');
                                owningWindow.close();
                            }, 
                            failure: function(frm,action) { //failed
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
                                    errmsg = 'Unknown Error: ' + action.response.responseText;
                                }
                                if(action.result.field) {
                                    var nameField = changePassFormPanel.getForm().findField(action.result.field);
                                    if(nameField) {
                                        nameField.markInvalid(errmsg);
                                        return;
                                    }
                                }
                                Ext.MessageBox.show({
                                    title: 'Error Message',
                                    msg: errmsg,
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });
                            }
                        });
                    } else {
                        Ext.MessageBox.show({
                            title: 'Error Message',
                            msg: 'Error in the Form',
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
            items: changePassFormPanel
        });
        changePassWindow.show();
    },
    
    onLanguageChange: function (select, newValue, oldValue, eOpts) {
        var formPanel = Ext.create({
            xtype: 'form',
            standardSubmit: true,
            
            items: [
                {
                    xtype: 'textfield',
                    name: 'locale',
                    value: newValue,
                }
            ]
        });
        formPanel.submit({url: window.location.href.replace(/#.*$/,'')});
    },
    
    //toggle notification handler
    onToggleNotification: function (btn) {
        var me = this;
        if (Notification.permission === 'granted') {
            me.saveNotificationStatus(btn);
        } else {
            var message = 'To receive the notification. Please click \'allow\' notifications.';
            var result = confirm(message);
            if (result) {
                me.requestNotification(btn);
            }
        }
        
    },
    
    //request notification permission
    requestNotification : function (btn) {
        var me = this;
        if (Notification.permission !== 'denied') {
            Notification.requestPermission().then((permission) => {
                if (permission === "granted") {
                    me.saveNotificationStatus(btn);
                }
            });
        }
        if (Notification.permission === 'denied') {
            alert("Sorry, notification was blocked by browser, please reset the permission to continue.");
        }
    },
    
    //save notification status, update button icon, ui, toggle connection
    saveNotificationStatus: function (btn) {
        var me = this;
        var status = {
            "x-fa fa-bell-slash": true,
            "x-fa fa-bell": false,
        };
        
        var cls = {
            "x-fa fa-bell-slash": "x-fa fa-bell",
            "x-fa fa-bell": "x-fa fa-bell-slash"
        };
        
        var ui = {
            "header-red-small": "header-green-small",
            "header-green-small": "header-red-small"
        };

        //set notification into local storage
        localStorage.setItem("notification", status[btn.iconCls]);
        
        //toggle connection
        me.toggleConnection(status[btn.iconCls]);
        
        //update ui and iconcls
        btn.setIconCls(cls[btn.iconCls]);
        btn.setUI(ui[btn.ui]);
    },
    
    //get notification status from local storage
    getNotificationStatus: function () {
        return (localStorage.getItem("notification") === 'true');
    },
    
    //toggle web socket connection
    toggleConnection: function (status) {
        var me = this;
        if (status) {
            me.openWebSocketConnection();
        } else {
            me.closeWebSocketConnection();
        }
    },
    
    //open web socket connection
    openWebSocketConnection: function () {
        var me = this;
        
        if (!me.getNotificationStatus()) {
            return;
        }
        
        //otc uat testing url
        //webSocket = new WebSocket("wss://otc-uat.ace2u.com/notification/sub/1");
        webSocket = new WebSocket("wss://"+window.location.hostname+window.location.pathname+"index.php?hdl=pssubscribe&action=notification");
        
        webSocket.onopen = function(event) {
            //console.log('webSocket open', event);
        }
        
        webSocket.onclose = function(event) {
            //console.log('webSocket close', event);
        }
        
        webSocket.onerror = function(event) {
            //console.log('webSocket error', event);
        }
        
        webSocket.onmessage = function(event) {
            //console.log('webSocket message', event.data);
            
            if (me.isJson(event.data)) {
                var obj = JSON.parse(event.data);
                if (obj.title && obj.body) {
                    me.showNotification(obj.title, obj.body, obj.url);
                }
            }
        }
    },
    
    //close web socket connection
    closeWebSocketConnection: function () {
        if (webSocket.readyState == WebSocket.OPEN) {
            webSocket.close();
        }
    },
    
    //check string is json string
    isJson: function (str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true; 
    },
    
    //show notification
    showNotification: function (title, body, url) {
        const notification = new Notification(title, {
            body: body,
        });
        
        if (url) {
            notification.onclick = (event) => {
                event.preventDefault(); // prevent the browser from focusing the Notification's tab
                window.open(url, '_blank');
            }
        }
    },
    
    //reset notification
    resetNotification : function (view) {
        localStorage.setItem("notification", false);

        var notificationBtn = view.lookupReference('notificationBtn');
        notificationBtn.setIconCls('x-fa fa-bell-slash');
        notificationBtn.setUI('header-red');
    }
    
    
});
