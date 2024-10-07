Ext.define('snap.view.mykycreminder.MyKYCReminderController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.mykycreminder-mykycreminder',


    getDateRange: function(){ 

        // _this = this;
        vm = this.getViewModel();

        startDate = this.getView().getReferences().startDate.getValue()
        endDate = this.getView().getReferences().endDate.getValue()

        if (startDate && endDate){
            startDate = Ext.Date.format(startDate,'Y-m-d 00:00:00');
            endDate = Ext.Date.format(endDate,'Y-m-d 23:59:59');
        }else{
            Ext.MessageBox.show({
                title: 'Filter Date',
                msg: 'Start date and End date required.',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
            return
        }
        this.getView().getStore().addFilter(
            {
                property: "senton", type: "date", operator: "BETWEEN", value: [startDate, endDate]
            },
        )
    },

});
