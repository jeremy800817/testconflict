Ext.define('snap.view.myannouncementtheme.MyAnnouncementThemeController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.myannouncementtheme-myannouncementtheme',


    onPreLoadForm: function (formView, form, record, asyncLoadCallback) {
        var me = this;
        snap.getApplication().sendRequest({
            hdl: 'myannouncementtheme', 'action': 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
            function (data) {
                if (data.success) {

                }

                if (Ext.isFunction(asyncLoadCallback)) asyncLoadCallback(record);
                else {
                    if (data.record) {
                        record = Ext.apply(record, data.record);
                        form.loadRecord(record);
                    }
                }
            });
        return false;
    },

    onPostLoadEmptyForm: function (formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.MyAnnouncementTheme', { id: 0 }), null);
    },


    onPreLoadViewDetail: function (record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'myannouncementtheme', action: 'detailview', id: record.data.id })
            .then(function (data) {
                if (data.success) {
                    displayCallback(data.record);
                }
            })
        return false;
    },



});