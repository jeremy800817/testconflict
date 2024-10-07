/**
 * This class is the main view for the application. It is specified in app.js as the
 * "mainView" property. That setting automatically applies the "viewport"
 * plugin causing this view to become the body element (i.e., the viewport).
 *
 * TODO - Replace this content of this view to suite the needs of your application.
 **/

Ext.define('Ext.ux.plugin.BadgeText', {
    extend: 'Ext.AbstractPlugin',
    alias: 'plugin.badgetext',

    disableBg: 'gray',
    enableBg: 'red',
    textSize: 10,
    textColor: 'white',
    defaultText: '&#160;',
    disableOpacity: 0,
    align: 'left',
    text: '&#160;',
    disable: true,
    button: null,
    /**
     *
     * @param button
     */
    init: function(button){

        var me = this;

        me.button = button;
        me.text = me.defaultText;

        button.on('render', me.addBadgeEl, me);

        Ext.apply(button,{

            setBadgeText:function(text){
                me.disable = typeof text == 'undefined' || text === me.defaultText;
                me.text = !me.disable ? text : me.defaultText;
                if (button.rendered) {
                    button.badgeEl.update(text.toString ? text.toString() : text);
                    if (Ext.isStrict && Ext.isIE8) {
                        button.el.repaint();
                    }
                    me.setDisabled(me.disable);
                }
                return button;
            },

            getBadgeText:function(){
                return me.text;
            }


        });

    },

    /**
     *
     * @param button
     */
    addBadgeEl: function(button){
        var me = this,
            styles = {
                'position': 'absolute',
                'background-color': me.disableBg,
                'font-size': me.textSize + 'px',
                'color': me.textColor,
                'padding': '1px 2px',
                'index': 50,
                'top': '-5px',
                'border-radius': '3px',
                'font-weight': 'bold',
                'text-shadow': 'rgba(0, 0, 0, 0.5) 0 -0.08em 0',
                'box-shadow': 'rgba(0, 0, 0, 0.3) 0 0.1em 0.1em',
                'cursor':'pointer'
            };

        if(me.align == 'left'){
            styles.left = '2px';
        }else{
            styles.right = '2px';
        }

        button.badgeEl = Ext.DomHelper.append(button.el, { tag:'div', cls:'badgeText x-unselectable'}, true);
        button.badgeEl.setOpacity(me.disableOpacity);
        button.badgeEl.setStyle(styles);
        button.badgeEl.update(me.text.toString ? me.text.toString() : me.text);

    },

    /**
     *
     */
    onBadgeClick:function(){
        var me = this;
        me.button.fireEvent('notifcationIconClicked');
    },

    /**
     *
     * @param disable
     */
    setDisabled:function(disable){
        var me = this;

        me.button.badgeEl.setStyle({
            'background-color': (disable ? me.disableBg : me.enableBg),
            //'color': (disable ? 'black' : 'white'),
            'opacity': (disable ? me.disableOpacity : 1)
        });

        me.button.badgeEl.clearListeners();
        // if(!disable) me.button.badgeEl.on('click', me.onBadgeClick, me, { preventDefault: true, stopEvent:true });
        
    }
});
Ext.define('snap.view.main.Main_ALRAJHI', {
    extend: 'Ext.container.Viewport',
    xtype: 'app-main',

    requires: [
        'Ext.container.Viewport',
        'Ext.window.MessageBox',
        'Ext.list.Tree',
        'Ext.util.DelayedTask',
        'snap.view.main.MainController_ALRAJHI',
        'snap.view.main.MainModel_ALRAJHI',
        'snap.view.main.MainContainerWrap_ALRAJHI',
        'snap.view.main.BlankPage_ALRAJHI',
        'snap.view.gridpanel.Base',
        'snap.view.tag.Tag',
        'snap.view.role.Role',
        'snap.view.tradingschedule.TradingSchedule',
        'snap.view.orderqueue.OrderQueue',
    ],

    controller: 'main_ALRAJHI',
    viewModel: 'main_ALRAJHI',

    listeners: {
        render: 'onMainViewRender',
        afterrender: 'onAfterRender'
    },

    layout: {
        type: 'border',
        align: 'fit'
    },
 
    items: [
        {   region: 'north',
            xtype: 'toolbar',
            ui: 'navigation',
            cls: 'sencha-dash-dash-headerbar',
            itemId: 'headerBar',
            items: [
                {
                    xtype: 'component',
                    reference: 'senchaLogo',
                    cls: 'sencha-logo',
                    bind: '{applogo}',                 
                    width: 250
                },
                {
                    xtype: 'component',
                    bind: '{devcss}',                 
                },
                {
                    // margin: '0 0 0 8',
                    ui: 'header',
                    iconCls:'x-fa fa-bars',
                    handler: 'onToggleNavigationSize',
                    tooltip: 'Expand / Collapse menu'
                },
                {
                    xtype: 'tbtext',
                    bind: {
                        text: '<b>{webtrail}</b>'
                    },
                    cls: 'top-user-name'
                },
                '->',
/*                {
                    xtype: 'segmentedbutton',
                    margin: '0 16 0 0',
                    platformConfig: {
                        ie9m: {
                            hidden: true
                        }
                    },
                    items: [
                    {
                        iconCls: 'x-fa fa-desktop',
                        pressed: true
                    }, {
                        iconCls: 'x-fa fa-tablet',
                        handler: 'onSwitchToModern',
                        tooltip: 'Switch to modern toolkit'
                    }]
                },
                {
                    iconCls:'x-fa fa-search',
                    ui: 'header',
                    href: '#searchresults',
                    hrefTarget: '_self',
                    tooltip: 'See latest search'
                },
                {
                    iconCls:'x-fa fa-envelope',
                    ui: 'header',
                    href: '#email',
                    hrefTarget: '_self',
                    tooltip: 'Check your email'
                },
*/              /*{
                    iconCls:'x-fa fa-question',
                    ui: 'header-yellow',
                    href: '#faq',
                    hrefTarget: '_self',
                    tooltip: 'Help / FAQ\'s'
                },*/
                {
                    xtype: 'combobox',
                    width: 100,
                    store: Ext.create('Ext.data.Store', {
                        fields: ['code', 'text'],
                        data: [{
                            code: 'en_US',
                            text: 'English'
                        }, {
                            code: 'zh_CN',
                            text: '中文'
                        }]
                    }),
                    valueField: 'code',
                    displayField: 'text',
                    value: LOCALE,
                    listeners: {
                        change: {
                            fn: 'onLanguageChange'
                        }
                    }
                },
                {
                    itemId: 'notification_badge',
                    iconCls:'x-fa fa-globe',
                    ui: 'header',
                    href: '#dashboardview',
                    hrefTarget: '_self',
                    tooltip: 'Notifications',
                    handler: 'onClickNotification',
                    plugins:[
                        {
                            ptype:'badgetext',
                            defaultText: 0,
                            disableBg: 'red',
                            align:'right',
                            disable: true
                        }
                    ],
                },
                {
                    ui: (NOTIFICATIONSTATUS) ? 'header-green': 'header-red',
                    iconCls: (NOTIFICATIONSTATUS) ? 'x-fa fa-bell': 'x-fa fa-bell-slash',
                    reference: 'notificationBtn',
                    tooltip: 'Notification',
                    handler: 'onToggleNotification',
                    hidden: (!CHECKER) ? true: false
                },
                {
                    //itemId: 'notification_badge',
                    iconCls:'x-fa fa-key',
                    ui: 'header',
                    href: '#changepasswordview',
                    hrefTarget: '_self',
                    tooltip: 'Change Password',
                    handler: 'onChangePassword',
                    plugins:[
                        {
                            ptype:'badgetext',
                            defaultText: 0,
                            disableBg: 'red',
                            align:'right',
                            disable: true
                        }
                    ],
                },
                /*{
                    iconCls:'x-fa fa-th-large',
                    ui: 'header-purple',
                    tooltip: 'Profile',
                    xtype: 'button',
                    menu: [{
                        text: 'View Profile',
                        iconCls: 'x-fa fa-info',
                        handler: 'onViewProfile'
                    },{
                        text: 'Change Password',
                        iconCls: 'x-fa fa-key',
                        handler: 'onChangePassword'
                    }]
                },*/
                {
                    xtype: 'tbtext',
                    ui: 'header',
                    bind: '{username}' ,                      
                    cls: 'top-user-name',
                    html: 'Hover to see tooltip',
                    listeners: {
                        'render': function() {
                            Ext.create({
                                xtype: 'tooltip',
                                target: this.getEl(),
                                listeners: {
                                    scope: this,
                                    beforeshow: function(tip) {
                                        tip.setHtml(this.getEl().getHtml() + 'Branch Code - ');
                                    }
                                }
                            });
                        }
                    }
                },
                {
                    iconCls:'x-fa fa-power-off',
                    ui: 'header-red',
                    handler: function(btn) {
                        Ext.MessageBox.confirm('Confirm Logout', 'Are you sure you want to logout?', 
                            function(buttonId , text) {
                                if(buttonId  == "yes") {
                                    snap.getApplication().sendRequest({hdl: 'logout'}).then(function(data){
                                        window.location.reload(true);
                                    })
                                }
                            });
                    },
                    // href: 'index.php?hdl=logout',
                    hrefTarget: '_self',
                    tooltip: 'Logout'
                }
            ]
        },
        {   region: 'center',
            xtype: 'maincontainerwrap_ALRAJHI',
            id: 'main-view-detail-wrap',
            reference: 'mainContainerWrap',
            flex: 1,
            items: [
                {
                    xtype: 'treelist',
                    reference: 'navigationTreeList',
                    itemId: 'navigationTreeList',
                    ui: 'navigation',
                    //scrollable: 'y',
                    //store: 'NavigationTree',,
                    bind: '{navItems}',
                    width: 250,
                    expanderFirst: false,
                    expanderOnly: false,
                    singleExpand: true,
                    listeners: {
                        selectionchange: 'onNavigationTreeSelectionChange'
                    },
                    style: { 'overflow-y': 'auto' }
                },
                {
                    xtype: 'container',
                    // xtype: 'vendorview',
                    flex: 1,
                    reference: 'mainCardPanel',
                    cls: 'sencha-dash-right-main-container',
                    itemId: 'contentPanel',
                    layout: {
                        type: 'card',
                        anchor: '100%'
                    }
                }
            ]
        },
        {   region: 'south',
            ui: 'footer',
            aign: 'center',
            xtype: 'toolbar',
            cls: 'sencha-dash-dash-footerbar',
            items: [
                '->', { xtype: 'label', bind: {html: '{copyright}' }},{ xtype: 'label', bind: {html: '{version}' }}, '->'
            ]
        }
    ]
});