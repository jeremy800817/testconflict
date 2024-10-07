Ext.define('snap.view.collection.CollectionTreeController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-collectiontreecontroller',

    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.collection', {
            id: 0,
        }), null);
        this.onPreLoadForm(formView, form, Ext.create('snap.model.CollectionServices', {
            id: 0,
        }), null);
    },

    cmdc: function () {
        var me = this,
            view = me.getView(),
            viewModel = me.getViewModel(),
            total_purity = 0,
            total_inputweight = 0,
            total_xauweight = 0,
            total_weight = 0,
            final_purity = 0,
            final_balance = 0,
            selectedPo = view.lookupReference('pogrid').getSelection(),
            rateCardStore = view.lookupReference('ratecardgird').getStore(),
            balanceWeightSpan = '';

        selectedPo.map(function (item, index) {
            total_weight += parseFloat(item.get('opndraft'));
        });

        rateCardStore.each(function (record) {
            if (record.get('u_purity')) {
                total_purity += parseFloat(record.get('u_purity'));
            }
            if (record.get('gtp_xauweight')) {
                total_xauweight += parseFloat(record.get('gtp_xauweight'));
            }
            if (record.get('gtp_inputweight')) {
                total_inputweight += parseFloat(record.get('gtp_inputweight'));
            }
        });

        if (rateCardStore.getCount()) {
            final_purity = total_purity / rateCardStore.getCount();
        }

        final_balance = (total_weight.toFixed(3) - total_xauweight.toFixed(3));

        if (final_balance < 0) {
            balanceWeightSpan = '<span style="background-color:red;color:white">' + final_balance.toFixed(3) + '</span>';
        } else {
            balanceWeightSpan = '<span style="background-color:green;color:white">' + final_balance.toFixed(3) + '</span>';
        }

        viewModel.data.total_poweight = total_weight.toFixed(3);
        viewModel.data.total_balanceweight = final_balance.toFixed(3);
        viewModel.data.total_inputweight = total_inputweight.toFixed(2);
        viewModel.data.total_purity = final_purity.toFixed(3);
        viewModel.data.total_xauweight = total_xauweight.toFixed(3);

        view.lookupReference('display_weight').setValue(total_weight.toFixed(3));
        view.lookupReference('display_balanceweight').setValue(balanceWeightSpan);
        view.lookupReference('display_grossweight').setValue(total_inputweight.toFixed(3));
        view.lookupReference('display_purity').setValue(final_purity.toFixed(2));
        view.lookupReference('display_xauweight').setValue(total_xauweight.toFixed(3));
    },

    customerComboSelected: function (combo, record, eOpts) {
        var poGrid = this.getView().lookupReference('pogrid');
        var store = Ext.create('Ext.data.Store', {
            proxy: {
                type: 'ajax',
                url: 'index.php',
                extraParams: {
                    hdl: 'collection',
                    action: 'getPODetail',
                    query: combo.getValue(),
                }
            }
        });
        poGrid.setStore(store);
        poGrid.getStore().load();
    },
});
