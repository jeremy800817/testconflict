Ext.define('snap.view.product.ProductController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.product-product',
    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {	
        return true;
    },
});
