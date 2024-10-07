Ext.define('snap.view.vaultitem.MyVaultItemBorderListBmmb', {
    extend:'snap.view.vaultitem.MyVaultItemBorderList',
    permissionRoot: '/root/bmmb/vault',
    xtype: 'mybmmbvaultitem-border', 
    type: 'bmmb',
    partnerCode : 'BMMB',
    listeners: {
        afterrender: function () {
            elmnt = this;

            if(this.items.items[1]){
                summary = this.items.items[1];
                summary.setHidden(true);
        
                // Check for type 
                if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
                    summary.setHidden(false);
                } 
            }
            
            // // Get Total Customer Holding
            // if(this.items.items[4]){
            //     totalCustomerHolding = this.items.items[4];
            //     totalCustomerHolding.setHidden(true);
        
            //     // Check for type 
            //     if ("Operator" == snap.getApplication().usertype || "Sale" == snap.getApplication().usertype  || "Trader" == snap.getApplication().usertype ){
            //         totalCustomerHolding.setHidden(false);
            //     } 
            //     // Do second check for permission, if user has permission, show customer holding
            //     // Check for type 
            //     var permission = '/root/bmmb/vault/customerholding';
            //     var viewCustomerHoldingPermission = snap.getApplication().hasPermission(permission);
            //     if (true == viewCustomerHoldingPermission){
            //         totalCustomerHolding.setHidden(false);
            //     } 
            // }
        }
    }
});