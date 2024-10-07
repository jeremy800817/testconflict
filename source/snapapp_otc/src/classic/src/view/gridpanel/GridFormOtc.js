Ext.define('snap.view.gridpanel.GridFormOtc',{
	extend: 'Ext.window.Window',
	alias: 'widget.gridformotc',

	requires: [
		'Ext.panel.Panel',
        'Ext.window.Window',
        'Ext.form.*'
        // ,'snap.view.gridpanel.BaseController'
    ],

    // controller: 'gridpanel-base',
    // reference:'formWindow',

    config: {
		/////////////////////////////////////////////////////////
		//Form dialog title
		/////////////////////////////////////////////////////////
		formDialogTitle: '',

		// ///////////////////////////////////////////////////////////
		// ///Form view model information
		// ///../////////////////////////////////////////////////////
		// formViewModel: undefined,

		/////////////////////////////////////////////////////////
		//Form dialog sizing and layout
		/////////////////////////////////////////////////////////
		formDialogWidth: 480,
		formDialogHeight: 0,
		formDialogLayout: 'fit',

		/////////////////////////////////////////////////////////
		//Set true to enable the form dialog a modal
		/////////////////////////////////////////////////////////
		enableFormDialogModal: true,

		/////////////////////////////////////////////////////////
		//Set true to render a transparent background for form dialog
		/////////////////////////////////////////////////////////
		enableFormDialogPlain: true,

		/////////////////////////////////////////////////////////
		//Set the button alignment for the form dialog
		/////////////////////////////////////////////////////////
		formDialogButtonAlignment: 'center',    // valid values are 'right', 'left', 'center'

		/////////////////////////////////////////////////////////
		//Form panel sizing and styling
		/////////////////////////////////////////////////////////
		formPanelHeight: 'auto',
		formPanelWidth: 'auto',
		formPanelMaxHeight: 700,
		formPanelBodyPadding: 10,
		formPanelBodyStyle: '',

		/////////////////////////////////////////////////////////
		//Set true to apply a frame to the form panel
		/////////////////////////////////////////////////////////
		enableFormPanelFrame: false,    //will enable the form panel body border if true

		/////////////////////////////////////////////////////////
		//Form panel fields sizing and styling
		/////////////////////////////////////////////////////////
		formLabelWidth: 110,
		formPanelLayout: 'anchor',
		// formLabelStyle: 'font-weight: bold; font-family: Arial; padding-left: 0px;',

		/////////////////////////////////////////////////////////
		//Form panel fields default config
		/////////////////////////////////////////////////////////
        formPanelDefaults: {
            msgTarget: 'side',
            margin: '0 0 5 0'
        },

		/////////////////////////////////////////////////////////
		//Form panel fields
		/////////////////////////////////////////////////////////
		formPanelItems: [],

		/////////////////////////////////////////////////////////
		//Form dialog buttons
		/////////////////////////////////////////////////////////
		formDialogButtons: [],

		scrollable: false,

    },
    
	initComponent: function() {
		this.title = this.formDialogTitle;
		this.layout = this.formDialogLayout;
		this.width = this.formDialogWidth;
		if (this.formDialogHeight > 0) this.height = this.formDialogHeight;
		this.modal = this.enableFormDialogModal;
		this.plain = this.enableFormDialogPlain;
		this.buttonAlign = this.formDialogButtonAlignment;
		this.buttons = this.formDialogButtons;
		this.closeAction = 'hide';

		if (this.gridFormPanel) {
			this.gridFormPanel.removeAll();
			this.gridFormPanel.add(this.formPanelItems);
		} else {
			var gridFormPanel = new Ext.form.Panel({
				reference: 'formPanel',
				frame: this.enableFormPanelFrame,
				layout: this.formPanelLayout,
				height: this.formPanelHeight,
				width: this.formPanelWidth,
				maxHeight: this.formPanelMaxHeight,
				border: 0,
				bodyBorder: this.enableFormPanelFrame,
				bodyPadding: this.formPanelBodyPadding,
				waitMsgTarget: true,
				closeAction: 'hide',
				defaults: this.formPanelDefaults,
				defaultType: 'textfield',
				fieldDefaults: {
					labelAlign: 'left',
					labelWidth: this.formLabelWidth,
					labelStyle: this.formLabelStyle,
					anchor: '100%'
				},
				items: this.formPanelItems,
				scrollable: this.scrollable
			});
			this.gridFormPanel = gridFormPanel;
		}
		this.items = this.gridFormPanel;

		this.callParent();
	}
});