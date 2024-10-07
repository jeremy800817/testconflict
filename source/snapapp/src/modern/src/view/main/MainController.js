Ext.define('snap.view.main.MainController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.main',

    requires: [
        'Ext.MessageBox'
    ],

    listen: {
        controller: {
            '#': {
                unmatchedroute: 'setCurrentView'
            }
        }
    },

    routes: {
        ':node': 'setCurrentView'
    },

    config: {
        showNavigation: true
    },

    collapsedCls: 'main-nav-collapsed',

    onNavigationItemClick: function () {
        // The phone profile's controller uses this event to slide out the navigation
        // tree. We don't need to do anything but must be present since we always have
        // the listener on the view...
    },

    onNavigationTreeSelectionChange: function (tree, node) {
        var to = node && (node.get('routeId') || node.get('viewType'));

        if (to) {
            this.redirectTo(to);
        }
    },
    initViewModel: function () {
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

    onSwitchToClassic: function () {
        Ext.Msg.confirm('Switch to Classic', 'Are you sure you want to switch toolkits?',
            'onSwitchToClassicConfirmed', this);
    },

    onSwitchToClassicConfirmed: function (choice) {
        if (choice === 'yes') {
            var obj = Ext.Object.fromQueryString(location.search);

            delete obj.modern;

            obj.classic = '';

            location.search = '?' + Ext.Object.toQueryString(obj).replace('classic=', 'classic');
        } else {
            var button = this.lookup('toolkitSwitch');

            button.setValue(Ext.isModern ? 'modern' : 'classic');
        }
    },
    
    onMainViewRender:function() {
        if (!window.location.hash) {
            if (snap.getApplication().direct != false){
                this.redirectTo(snap.getApplication().direct);
            }else{
                this.redirectTo("announcementhomeview");
            }
        }
    },

    onToggleNavigationSize: function () {
        this.setShowNavigation(!this.getShowNavigation());
    },

    setCurrentView: function (hashTag,once) {     
        hashTag = (hashTag || '').toLowerCase();

        var me = this,
            refs = me.getReferences(),
            mainCard = refs.mainCardPanel,
            //mainLayout = mainCard.getLayout(),
            navigationList = refs.navigationTree,
            store = navigationList.getStore(),
            node = (store && store.findNode('routeId', hashTag)) ||
                   (store && store.findNode('viewType', hashTag)),
            viewModel = me.getViewModel(),
            view = (node && node.get('viewType')),
            lastView = me.lastView,
            //existingItem = mainCard.child('component[routeId=' + hashTag + ']'),
            newView;            
            //Added by Devon on 2017/5/10 to 
            //Have to delay the first time loading and retry it after menu has been loaded.....
            if(!view && !node && -1 != once) { 
                Ext.create('Ext.util.DelayedTask', function () {
                        me.setCurrentView(hashTag, -1);
                    }).delay(1000);
                return;
            }
            item = this.getView().child('component[viewType=' + hashTag + ']');
            //console.log(item);
            item = {
                xtype: node.get('viewType'),
                routeId: hashTag
            };
            this.getView().setActiveItem(item);
           
    },

    updateShowNavigation: function (showNavigation, oldValue) {
        // Ignore the first update since our initial state is managed specially. This
        // logic depends on view state that must be fully setup before we can toggle
        // things.
        //
        if (oldValue !== undefined) {
            var me = this,
                cls = me.collapsedCls,
                logo = me.lookup('logo'),
                navigation = me.lookup('navigation'),
                navigationTree = me.lookup('navigationTree'),
                rootEl = navigationTree.rootItem.el;

            navigation.toggleCls(cls);
            logo.toggleCls(cls);

            if (showNavigation) {
                // Restore the text and other decorations before we expand so that they
                // will be revealed properly. The forced width is still in force from
                // the collapse so the items won't wrap.
                navigationTree.setMicro(false);
            } else {
                // Ensure the right-side decorations (they get munged by the animation)
                // get clipped by propping up the width of the tree's root item while we
                // are collapsed.
                rootEl.setWidth(rootEl.getWidth());
            }

            logo.element.on({
                single: true,
                transitionend: function () {
                    if (showNavigation) {
                        // after expanding, we should remove the forced width
                        rootEl.setWidth('');
                    } else {
                        navigationTree.setMicro(true);
                    }
                }
            });
        }
    },

    toolbarButtonClick: function (btn) {
        var href = btn.config.href;

        this.redirectTo(href);
    },
    _recursiveGetMenuItem: function (tree) {
        var app = snap.getApplication();
        var container = [];
        for (var i = 0; i < tree.length; i++) {
            var item = tree[i];
            if (!item.leaf && item.children) {
                item.children = this._recursiveGetMenuItem(item.children);
                if (item.children.length) container.push(item);
            } else {
                if (!item.permission || app.hasPermission(item.permission)) container.push(item);
            }
        }
        return container;
    },

});
