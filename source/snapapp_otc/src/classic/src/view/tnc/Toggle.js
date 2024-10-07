Ext.define('MyApp.view.Toggle', {
    extend: 'Ext.slider.Single',
    alias: 'widget.togglebutton',
    config: {
 defaultBindProperty: 'value'
    },
    cls: 'toggleoff',
    width: 10,
    value: 0,
    animate: false,
    maxValue: 1,
    initComponent: function () { 
 this.callParent();
    },
   getValue: function () {
        var toggleValue = this.thumbs[0].value;
        if (toggleValue === 1) {
            this.removeCls('toggleoff');
            this.addCls('toggleon');
        } else {
            this.removeCls('toggleon');
            this.addCls('toggleoff');
        }
        return this.callParent([0]);
    },
    listeners: {
        afterRender: function (toggle) {
            var toggleValue = toggle.thumbs[0].value;
            if (toggleValue === 1) {
                this.removeCls('toggleoff');
                this.addCls('toggleon');
            }
        }
    }
});
