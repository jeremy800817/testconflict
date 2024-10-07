Ext.define('snap.view.collection.CollectionController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.collection-collection',

    config: {
        companyid: 4,
    },

    onPreLoadViewDetail: function (record, displayCallback) {
        snap.getApplication().sendRequest({
                hdl: 'collection',
                action: 'detailview',
                id: record.data.id,
            })
            .then(function (data) {
                if (data.success) {
                    displayCallback(data.record);
                }
            });

        return false;
    },

    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {
        var mask = Ext.getBody().mask('Loading...');
        mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);

        snap.getApplication().sendRequest({
            hdl: 'collection',
            action: 'fillform',
            id: record.data.id,
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {
                    Ext.getBody().unmask();
                    var collectionserviceStore = Ext.getStore("collectionserviceStore");
                    collectionserviceStore.removeAll();
                    collectionserviceStore.add(data.servicerecord);

                    var collectionbranchStore = Ext.getStore("collectionbranchStore");
                    collectionbranchStore.removeAll();
                    collectionbranchStore.add(data.branchrecord);

                    formView.getController().lookupReference('sapcompanysellcode1').getStore().loadData(data.apicodescustomer);
                    formView.getController().lookupReference('sapcompanybuycode1').getStore().loadData(data.apicodesvendor);
                }
            });

        return true;
    },

    onPostLoadEmptyForm: function (formView, form) {
        snap.getApplication().sendRequest({
            hdl: 'collection',
            action: 'prefillform',
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {
                    formView.getController().lookupReference('salespersoninput').setValue(data.salesperson);
                }
            });
    },

    // *************************************************************************************
    // Get SAP VENDOR
    // *************************************************************************************
    getSapVendorCodes: function (record) {
        snap.getApplication().sendRequest({
            hdl: 'collection',
            action: 'getSapVendorCodes',
            id: ((record && record.data) ? record.data.id : 0),
        }, 'Fetching data from server....').then(
            //Received data from server already
            function (data) {
                if (data.success) {

                }
            });
    },

    onclickcompany: function (record) {
        var x = record.lookupController().getView().controller.getCompanyid();
        return;
    },

    inventorycatComboSelected: function (combo, record) {
        var vm = this.getViewModel(),
            map = vm.get('inventoryMap');

        map[record.id] = record.data.name;
        vm.set('inventoryMap', map);
    },

    onPreAddEditSubmit: function (formAction, theGridFormPanel, theGridForm, btn) {
        btn.disable();

        var viewController = theGridFormPanel.getController();
        var selectedPo = viewController.lookupReference('pogrid').getSelection();
        var trans_po = [];
        selectedPo.map(function (item, index) {
            po = {
                "docEntry": item.get('docEntry'),
                "docNum": item.get('docNum'),
                "u_GTPREFNO": item.get('u_GTPREFNO'),
            }
            trans_po.push(po);
        });

        var rateCardStore = viewController.lookupReference('ratecardgird').getStore();
        var trans_ratecard = [];
        rateCardStore.each(function (record) {
            ratecard = {
                "u_itemcode": record.get('u_itemcode'),
                "u_purity": record.get('u_purity'),
                "u_inputweight": record.get('gtp_inputweight'),
                "u_xauweight": record.get('gtp_xauweight'),
            }
            trans_ratecard.push(ratecard);
        });

        var vatsum = viewController.lookupReference('display_balanceweight').getValue();
        var result = vatsum.match(/\<span.*\>(.*)\<\/span\>/);
        if (result) {
            vatsum = result[1];
        }
        var total_expected = viewController.lookupReference('display_weight').getValue();
        var total_gross_weight = parseFloat(total_expected) - parseFloat(vatsum);
        var total_xau_collected = viewController.lookupReference('display_xauweight').getValue();

        if (vatsum < 0 || total_xau_collected == 0) {
            Ext.MessageBox.show({
                title: 'Invalid GRN data.',
                msg: 'Balance Weight and XAU Weight less than 0',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
            });
            btn.enable();

            return;
        }

        var customer = viewController.lookupReference('customercombox').value;
        var summary = {
            "total_expected_xau": total_expected,
            "total_gross_weight": total_gross_weight,
            "total_xau_collected": total_xau_collected,
            "vatsum": vatsum,
        };

        var req = {
            'po': trans_po,
            'ratecard': trans_ratecard,
            'customer': customer,
            'summary': summary
        };

        snap.getApplication().sendRequest({
                hdl: 'collection',
                action: 'addgrn',
                data: JSON.stringify(req),
            }, 'Fetching data from server....')
            .then(
                //Received data from server already
                function (data) {
                    if (data.success) {
                        theGridFormPanel.close();
                    }
                });
    },

    getPrintReport: function (btn) {
        var reference = this.getView().getReferences();

        // grid header data
        var header = []
        btn.up('grid').getColumns().map(column => {
            if (column.isVisible() && column.dataIndex !== null) {
                var _key = column.text;
                var _value = column.dataIndex;
                var columnlist = {
                    text: _key,
                    index: _value,
                }
                if (column.exportdecimal !== null) {
                    var _decimal = column.exportdecimal;
                    columnlist.decimal = _decimal;
                }
                header.push(columnlist);
            }
        });

        var startDate = reference.startDate.getValue();
        var endDate = reference.endDate.getValue();

        if (startDate && endDate) {
            startDate = Ext.Date.format(startDate, 'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate, 'Y-m-d 23:59:59');
            var daterange = {
                startDate: startDate,
                endDate: endDate,
            }
        } else {
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR,
            });
            return
        }

        header = encodeURI(JSON.stringify(header));
        daterange = encodeURI(JSON.stringify(daterange));

        var url = '?hdl=collection&action=exportExcel&header=' + header + '&daterange=' + daterange;

        Ext.DomHelper.append(document.body, {
            tag: 'iframe',
            id: 'downloadIframe',
            frameBorder: 0,
            width: 0,
            height: 0,
            css: 'display:none;visibility:hidden;height: 0px;',
            src: url,
        });
    },
});
