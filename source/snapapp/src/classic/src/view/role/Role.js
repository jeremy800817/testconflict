
Ext.define('snap.view.role.Role',{
    extend: 'snap.view.gridpanel.Base',
    xtype: 'roleview',

    requires: [
        'snap.store.Role',
        'snap.model.Role',
        'snap.view.role.RoleController',
        'snap.view.role.RoleModel'
    ],
    permissionRoot: '/root/system/role',
    store: { type: 'Role' },

    controller: 'role-role',
    viewModel: {
        type: 'role-role'
    },
    //title: 'Role',

    enableFilter: true,
    // gridSelectionModel: 'checkboxmodel',
    columns: [{
        text: 'ID',
        dataIndex: 'id',
        filter: {
            type: 'int'
        },
        hidden: true
    },{
        text: 'Title',
        dataIndex: 'title',
        filter: {
            type: 'string'
        },
        width: 300
    },{
        text: 'Description',
        dataIndex: 'description',
        filter: {
            type: 'string'
        },
        flex: 1
    }],

    formConfig: {
        formDialogTitle: 'Role',

        formPanelItems: [{
            inputType: 'hidden',
            hidden: true,
            name: 'id'
        },{
            inputType: 'hidden',
            hidden: true,
            name: 'permissions'
        },{
            fieldLabel: 'Title',
            name: 'title',
            allowBlank: false
        },{
            fieldLabel: 'Description',
            name: 'description'
        },{
            reference: 'role_permissions',
            itemId: 'role_permissions',
            xtype: 'treepanel',
            checkPropagation: 'both',
            rootVisible: false,
            useArrows: false,
            lines: false,
            frame: true,
            title: 'Permissions',
            animate: true,
            height: 400,
            store: /*Ext.create('Ext.data.TreeStore', */{
                type: 'tree',
                autoLoad: true,
                fields: ['permission', 'text'],
                idProperty: 'permission',
                proxy: {
                    type: 'ajax',
                    url: 'index.php',
                    extraParams: {
                        hdl: 'role',
                        action: 'getrolepermissions'
                    }
                },
                reader: {
                    type: 'json'
                }
            }/*)*/
        }]
    }
});
