Ext.define('snap.view.otcregister.PasswordField', {
    extend: 'Ext.form.field.Text',
    alias: 'widget.passwordfield',

    //inputType: 'password',
    msgTarget: 'under',
    
    
    validators: [{
        errorMessage: "Password should contain at least 6 character;",
        fn: (value) => {
            return value.length >= 6
        }
    }, {
        errorMessage: "Password should contain at least one number;",
        fn: (value) => {
            return /\d/.test(value)
        }
    }, {
        errorMessage: "Password should contain at least one lowercase and one uppercase letter;",
        fn: (value) => {
            return /[a-z]/.test(value) && /[A-Z]/.test(value);
        }
    }, {
        errorMessage: "Password should contain at least one special character;",
        fn: (value) => {
            return /[~`!#$%@\^&*+=\-\[\]\\';,/{}|\\":<>\?]/g.test(value);
        }
    }],
    
    initComponent: function () {
        this.callParent();
    },
    
    onRender: function() {
        this.callParent();
        this.validate();
    },
    
    validator: function(val) {
        const errorMessages = [];
        this.validators.map( (validator, index) => {
            const icon = validator.fn(val) ? '<i class="fa fa-check" style="color: green; width: 20px;"></i>': '<i style="width: 20px;">&nbsp;</i>';
            errorMessages.push(`<li>${icon}${validator.errorMessage}</li>`);
        });
        return errorMessages.join('');
    }
});