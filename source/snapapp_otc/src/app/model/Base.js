//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.model.Base', {
    extend: 'Ext.data.Model',

    requires: [
        'Ext.data.*',
        'Ext.data.ErrorCollection',
        'Ext.data.operation.*',
        'Ext.data.field.*',
        'Ext.data.identifier.Generator',
        'Ext.data.identifier.Sequential',
        'Ext.data.proxy.*',
        'Ext.data.reader.*',
        'Ext.data.ResultSet',
        'Ext.util.LruCache',
        'Ext.util.XTemplateCompiler',
        'Ext.util.ObjectTemplate',
        'Ext.XTemplate',
        'Ext.data.schema.*'
        // 'Ext.data.schema.OneToOne',
        // 'Ext.data.schema.ManyToOne',
        // 'Ext.data.schema.ManyToMany',
        // 'Ext.data.schema.Namer'
    ],
	
    schema: {
        namespace: 'snap.model',		
        proxy: { 
            type: 'ajax', 
//			id: 'snap-schema',
					
            api :{
                read : 'index.php?hdl={entityName:lowercase}&action=list',
                create: 'index.php?hdl={entityName:lowercase}&action=add',
                update: 'index.php?hdl={entityName:lowercase}&action=update',
                destroy: 'index.php?hdl={entityName:lowercase}&action=delete'
            }
            // writer: {
            //     type: 'associatedjson',
            //     writeAllFields: true,
            //     encode: true,
            //     rootProperty: 'data',
            //     allowSingle: false
            // },
        }
    }
});
