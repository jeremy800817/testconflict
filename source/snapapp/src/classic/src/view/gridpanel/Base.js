Ext.define('snap.view.gridpanel.Base',{
    extend: 'Ext.grid.Panel',

    requires: [
        'Ext.data.Store',
        'Ext.data.Model',
        'Ext.Window',
        'Ext.menu.*',
        'Ext.view.Table',
        'Ext.selection.Model',
        'Ext.toolbar.*',
        'Ext.grid.*',
        'Ext.grid.plugin.*',
        'snap.view.gridpanel.BaseController',
        'snap.view.gridpanel.BaseModel',
        'snap.store.Base',
        'snap.util.PagingToolbar',
        'snap.util.FilterBar',
        'snap.view.gridpanel.GridForm'
    ],

    /////////////////////////////////////////////////////////
    //The rights assigned for view
    /////////////////////////////////////////////////////////
    permissionRoot: undefined,

    /////////////////////////////////////////////////////////
    ///Grid data sizing and layout
    /////////////////////////////////////////////////////////
    width: '100%',
    height: 'auto',
    showSummaryRow: false, 
    defaultPageSize: 50,
    stateful:true,

    /////////////////////////////////////////////////////////
    //Grouping options
    /////////////////////////////////////////////////////////
    enableGrouping: false,      //true or false values. 
    groupbyField: [],           //Array of field names to group by
    groupWithSummary: false,    //Any summary of the grouping
    groupMenuOnColumn: true,    //Allow column header to customise grouping
    groupHeaderText: undefined, //Group header description text; defaults to {columnName}: {name}

    /////////////////////////////////////////////////////////
    //Column filtering / Row bar filtering
    /////////////////////////////////////////////////////////
    enableFilter: false,        //Enable filting feature for the grid or not.
    enableColumnFilter: false,  //show a bar or column filter
    showFilterBar: false,      //set the filter bar as visible initially
    showFilterShowHideButton: false,
    showFilterClearButton: true,
    showFilterClearAllButton: true,

    /////////////////////////////////////////////////////////
    //Inline editing
    /////////////////////////////////////////////////////////
    enableCellEditing : false,
    enableRowEditing: false,

    /////////////////////////////////////////////////////////
    //Grid selection options
    /////////////////////////////////////////////////////////
    gridSelectionModel: 'checkboxmodel', //checkboxmodel or rowmodel
    gridSelectionMode: 'MULTI',     //SINGLE, MULTI or SIMPLE selection type
    gridSelectionCheckOnly: false,  //for checkboxmodel only, whether can only be selected by click on the box.

    /////////////////////////////////////////////////////////
    //Show details window feature
    /////////////////////////////////////////////////////////
    enableDetailView: true,       //Enable default view details feature, true/false
    detailViewConfig: {},     //Additional properties for the property grid configuration
    detailViewWindowWidth: 530,   //Width of the view detail window
    detailViewWindowHeight: 600,  //Height of the view detail window
    detailViewSections: {},       //Sections to separate the details into  sectionkey => section description pair.
    detailViewSectionMap: {},     //Mapping of column belongs to which section.  sectionKey => array to columns in this section.
    detailViewUseRawData: false,  //boolean.  True assumes that the record will be used as is without processing the content. (e.g.  formatting title & data)
                                  //Use callback method onPreLoadViewDetail(rec) to do own processing and record record to show on properties

    /////////////////////////////////////////////////////////   
    //Pagination options
    /////////////////////////////////////////////////////////
    enablePagination: true,       //Show the pagination bar
    paginationBarDisabled: false, //Whether to enable it or not.
    gridShowDeleteSuccessfulMessage: true,

    /////////////////////////////////////////////////////////
    ///Sorting options
    /////////////////////////////////////////////////////////
    defaultSorters: [],   //Array of fields to sort by, e.g. {property: 'timestamp', direction: 'ASC'}

    /////////////////////////////////////////////////////////
    ///Sorting options
    /////////////////////////////////////////////////////////
    enableToolbar: true,
    showToolbarItemIconOnly: true,  //Whether to display a button and text for the toolbar item.

    /////////////////////////////////////////////////////////
    ///Toolbar configuration
    /////////////////////////////////////////////////////////
    /*  
    Toolbar items to add.  

    Built in features are: add, edit, delete, detail and view [with default permission sets]
    Build in toolbar arrangment helper includes: separator (|) , spacer and filler (->) for right align items after it.

    Custom toolbar will need to be a separate object { ...... } format with the following
    elements:
        *xtype - ExtJS type to create (defaults to button type)
        text - description of the item.  Will be shown or hide according to showToolbarItemIconOnly setting
        *tooltip - tooltip to display
        iconCls - the icons to show (CSS class), 
        handler - Function (name) to run in controller upon action, 
        permission - Sub-permission AFTER the rootPermission defined.  (E.g. 'add';  this will generate /{grid rootPermission property}/add)
                     For special cases, you can also specify a full permission path, e.g.  /root/system/some/specific/permission
        reference - the ID of the item for easy lookup in the controller
        *buttonText - override for text on toolbar (will show even if the toolbar showToolbarItemIconOnly is true to force text display even only icon only mode), 
        *menuText - override for context menu description, 
        *enableMenu - whether to show on the context menu [true / false]
        *validSelection - Can it operate on multiple selections? (single / multiple / ignore)  Defaults to single,
        *canEnableItem - provide a function with argument selections that should return true or false to indicate 
                        whether the button should be enabled (True) or disabled (false)
    * denotes optional items.
    */
    toolbarItems: [ 'add', 'edit', 'detail', '|', 'delete', 'filter'],
    toolbarDefaultAddItem:  {reference: 'addButton', text: 'Add', tooltip: 'Add New Record', iconCls:'x-fa fa-plus',
                            handler: 'onAdd', validSelection: 'ignore', enableMenu: false, permission: 'add'},
    toolbarDefaultEditItem: {reference: 'editButton', text: 'Edit', tooltip: 'Edit Record', iconCls:'x-fa fa-edit',
                            handler: 'onEdit', validSelection: 'single', enableMenu: true, permission: 'edit'},
    toolbarDefaultDeleteItem: { reference: 'delButton', text: 'Delete', tooltip: 'Delete Record', iconCls:'x-fa fa-trash',
                                handler: 'onDelete', validSelection: 'multiple', enableMenu: false},
    toolbarDefaultDetailItem: {reference: 'detailButton', text: 'Details', tooltip: 'View Record Details', iconCls:'x-fa fa-list',
                                handler: 'showDetails', validSelection: 'single', enableMenu: true},
    toolbarDefaultFilterItem: {reference: 'filterButton', text: 'Filter', tooltip: 'Show filters', iconCls: 'fa fa-filter fa-lg',
                                handler: 'showHideFilters', validSelection: 'ignore', allowDepress: true, enableToggle: true },

    enableContextMenu: true,

    showStatusBar: false,

    /////////////////////////////////////////////////////////
    ///Configuration of the grid view in general
    /////////////////////////////////////////////////////////
    viewConfig: {
        reference: 'gridView',
        trackOver: true,
        enableTextSelection: true,
        stripeRows: true,
        deferEmptyText: false,
        emptyText: '<br/><br/><center>No Records</center><br/><br/><br/>',
        forceFit: true,
        listeners: {
            afterrender: 'firstTimeMenuRefresh',
            itemdblclick: 'onGridItemDoubleClicked',
            cellclick: 'onGridCellClicked',
            selectionchange: 'onGridSelectionChanged',
            itemcontextmenu: 'onContextMenuClick'
        }
    },

    /////////////////////////////////////////////////////////
    ///Specify class for add / edit form implementation
    /////////////////////////////////////////////////////////
    formClass: 'snap.view.gridpanel.GridForm',
    formConfig: undefined,

    /**
     * This method is called by the framework upon initialization of this component or
     * its derived class.  The main objective of this method is to set proper configuration settings
     * for the creation of this handler
     */
    initComponent: function() {
        // (snap.application.permission)
        this.stateId = this.stateId || Ext.getClass(this).getName();

        //Datastore configuration
        this.store.pageSize = this.defaultPageSize;
        this.store.sorters = this.defaultSorters;
        this.store.remoteFilter = true;
        //Configuring of grid features - Summary row, grouping
        var gridFeatures = [], gridPlugins = [];
        if (this.showSummaryRow) {
            gridFeatures.push({ftype: 'summary', dock: 'bottom'});
        }
        if (this.enableGrouping) {
            if (this.groupbyField.length > 0) {
                this.store.groupField = this.groupbyField;
                var ftype = 'grouping';
                if (this.groupWithSummary) {
                    ftype = 'groupingsummary';
                }
                gridFeatures.push({
                    ftype: ftype,
                    enableGroupingMenu: this.groupMenuOnColumn,
                    enableNoGroups: true,
                    groupByText: 'Group By',
                    showGroupsText: 'Show Group',
                    groupHeaderTpl: (this.groupHeaderText) ? this.groupHeaderText : '{columnName}: {name}'
                });
            }
        }
        if(gridFeatures.length) this.features = gridFeatures;

        //Configuring of grid plugins - filters, editing
        if(this.enableFilter && ! this.enableColumnFilter) {
            gridPlugins.push({
                ptype: 'filterbar',
                pluginId: 'filters',
                autoStoresRemoteProperty: 'filterData',
                autoStoresNullValue: '###NULL###',
                // autoStoresNullText: __('[prázdne]'),
                autoUpdateAutoStores: true,

                renderHidden: !this.showFilterBar,
                showShowHideButton: this.showFilterShowHideButton,
                showClearButton: this.showFilterClearButton,
                showClearAllButton: this.showFilterClearAllButton,
                // showHideButtonTooltipDo: __('Zobraziť filtre'),
                // showHideButtonTooltipUndo: __('Schovať filtre'),

                // Texts for the operator button
                // textEq: __('je rovné'),
                // textNe: __('je rôzne od'),
                // textGte: __('väčšie alebo rovné ako'),
                // textLte: __('menšie alebo rovné'),
                // textGt: __('väčšie ako'),
                // textLt: __('menšie ako'),
                // custom
                showTool: true,
                dock: 'top'                
            });
        } else if(this.enableFilter && this.enableColumnFilter) {
            gridPlugins.push('gridfilters');
        }

        if (this.enableCellEditing) {
            gridPlugins.push(Ext.create('Ext.grid.plugin.CellEditing', {
                clicksToEdit: 2
            }));
        }
        if (this.enableRowEditing) {
            gridPlugins.push({
                xclass: 'Ext.grid.plugin.RowEditing',
                clicksToMoveEditor: 1,
                autoCancel: false
            });
        }
        if (this.rowexpander) {
            gridPlugins.push(this.rowexpander);
        }
        this.plugins = gridPlugins;

        this.selModel = {
            selType: this.gridSelectionModel,
            ignoreRightMouseSelection: true,
            checkOnly: this.gridSelectionCheckOnly,
            mode: this.gridSelectionMode
        };


        //Configuring docked items
        if(!this.dockedItems) this.dockedItems = [];
        //Configure toolbar item.
        if(this.enableToolbar){
            this.dockedItems.push({
                xtype: 'toolbar',
                reference: 'toolbar',
                dock: 'top',
                //Commented due to not able to proceed with proper production build.
                // layout: {
                //     overflowHandler: 'Menu'
                // },
                showTitle: false,
                items: this.formatToolbarItems()
            });        
        }
        if (this.enablePagination) {
            this.dockedItems.push({
                xtype: 'pagingtoolbarresizer',
                reference: 'gridPagingToolbar',
                dock: 'bottom',
                displayInfo: true,
                disabled: this.paginationBarDisabled,
                emptyMsg: 'No records to be displayed'
            });
        };
        if (this.showStatusBar) {
            this.dockedItems.push({
                reference: 'gridStatusBar',
                dock: 'bottom',
                xtype: 'statusbar',
                defaultIconCls: 'i-status',
                text: ''
            });
        };
        // call the parent to continue the intialisation chain.
        this.callParent(arguments); 
    },


    formatToolbarItems: function() {
        var me = this;
        var app = snap.getApplication();
        var toolbarConfig = [];
        var controller = this.getController();
        for(var i = 0; i < this.toolbarItems.length; i++) {
            var item = this.toolbarItems[i];
            switch(item) {
                case 'add':
                case 'edit':
                case 'detail':
                case 'filter':
                case 'delete':
                    var title = item;
                    title = title.charAt(0).toUpperCase() + title.substr(1).toLowerCase();
                    item = me['toolbarDefault' + title + 'Item']; //get info from toolbarDefaultXXXXItem config
                    break;
                case 'separator':
                case '|':
                    item = { xtype: 'tbseparator', validSelection: 'ignore' };
                    break;
                case 'spacer':
                    item = { xtype: 'tbspacer', validSelection: 'ignore' };
                    break;
                case 'filler':
                case '->':
                    item = { xtype: 'tbfill', validSelection: 'ignore' };
                    break;
            } 
            var itemPermission = item.permission ? (item.permission.match(/\//) ? item.permission : (this.permissionRoot + '/' + item.permission)) : null;
            var menuText = item.menuText || item.buttonText || item.text;
            var itemText = item.buttonText || (this.showToolbarItemIconOnly ? (item.showToolbarItemText) ? item.text : '' : item.text);

            if(!item.permission || app.hasPermission(itemPermission) ) {
                toolbarConfig.push(Ext.apply(item, {text: itemText, menuText: menuText}));
            }
        }
        this.toolbarConfig = toolbarConfig;
        return toolbarConfig;
    }
});
