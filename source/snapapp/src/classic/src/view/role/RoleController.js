Ext.define('snap.view.role.RoleController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.role-role',

    requires: [
        'snap.view.gridpanel.BaseController'
    ],

    // onGridSelectionChanged: function(sm, selections) {
    //    return this.callParent(sm, selections);
    // },

    getPermissionTree: function(theGridFormPanel) {
        var permissionTree = theGridFormPanel.down('form').getComponent('role_permissions');
        return permissionTree;
    },

    onPostLoadForm: function(theGridFormPanel, theGridForm, theRecord) {
        var permissionTree = this.getPermissionTree(theGridFormPanel);
        var pStore = permissionTree.getStore();

        var me = this;
        pStore.on('load', function(treeStore, records, successful, operation, node, eOpts) {
            if (successful) {
                me.clearPermissionTree(pStore);
                if (theRecord.get('permissions').length > 0) {
                    var permissionPaths = theRecord.get('permissions').split('||');
                    me.setCheckNodePermission(pStore, permissionPaths);
                }
            }
        });
    },

    onPostLoadEmptyForm: function(theGridFormPanel, theGridForm) {
        var permissionTree = this.getPermissionTree(theGridFormPanel);
        var pStore = permissionTree.getStore();

        var me = this;
        pStore.on('load', function(treeStore, records, successful, operation, node, eOpts) {
            if (successful) me.clearPermissionTree(pStore);
        });
    },

    clearPermissionTree: function(pStore) {
        pStore.getRootNode().cascadeBy(function(node) {
            if (node.get('checked')) node.set('checked', false);
        });
    },

    setCheckNodePermission: function(pStore, permissionPaths) {
        for (var p = 0; p < permissionPaths.length; p++) {
            var node = pStore.findNode('permission', permissionPaths[p]);
            if (node) this.setCheckChildNode(node);
            else console.log("node cannot find " + permissionPaths[p]);
        }
    },

    setCheckChildNode: function(node) {
        var me = this;
        if (node) {
            node.set("checked", true);
            if (node.hasChildNodes()) {
                node.eachChild(function(childNode) {
                    me.setCheckChildNode(childNode);
                });
            }
        }
    },

    onPreAddEditSubmit: function(formAction, theGridFormPanel, theGridForm) {
        var permissionTree = this.getPermissionTree(theGridFormPanel);
        var checkedPermissions = permissionTree.getChecked();

        var tmpPermissions = [];
        Ext.Array.each(checkedPermissions, function(permission) {
            tmpPermissions.push(permission.get('permission'));
        });
        var permissions = tmpPermissions.join("||");
        if (permissions.length > 0) {
            theGridForm.setValues({permissions: permissions});
            return true;
        } else {
            Ext.MessageBox.show({
                title: 'Error Message',
                msg: 'Select permissions',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return false;
        }
    }
});
