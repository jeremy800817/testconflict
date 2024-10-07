Ext.define('snap.view.product.ProductTreeController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-producttreecontroller',
    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.product', { id: 0, }), null);       
    },    
});
