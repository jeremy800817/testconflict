Ext.define('snap.view.otcregister.OTCRegisterController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.otcregister-otcregister',

    // nextButton: function(btn, formAction) {
    //     var myView = this.getView(),
    //         me = this;
    //     // var sm = myView.getSelectionModel();
    //     // var selectedRecords = sm.getSelection();

    //     // set path
    //     path = 'otcregisterview_' + PROJECTBASE;
    //     me.redirectTo(path);

    //     // if (selectedRecords.length == 1) {
    //     //     for (var i = 0; i < selectedRecords.length; i++) {
    //     //         selectedID = selectedRecords[i].get('id');
    //     //         record = selectedRecords[i];
    //     //         me.redirectTo(path + '/accountholder/' + selectedID);
    //     //         break;
    //     //     }
    //     // } else {
    //     //     Ext.MessageBox.show({
    //     //         title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
    //     //         msg: 'Select a record first'
    //     //     });
    //     //     return;
    //     // }
	// }

    _filterphone: function (number) {
        firstphone = number.substring(0,1);
        if (firstphone =='+') {
            contactno  = number;
        }else if(firstphone =='6'){
            contactno  = '+' . number;
        }else if(firstphone == '0'){
            contactno  = '+6' . number;
        }else{
            contactno  = '+60' . number;
        }
        return number;
    },

    
    _filtermykadno: function (ic) {
      
        var y = ic;
        var regTest = /[a-zA-Z]/g;

        if(regTest.test(y)){
            //letters found, do nothing
        } 
        else {
            //letters not found
            y = y.replace(new RegExp('-', 'g'),"");
            if(y.length == 12){
                y = y.substr(0,6)+'-'+y.substr(6,2)+'-'+y.substr(8,4);
            }
        }
        return y;
    },

    
    // _validatePassword(pass){
    //     var re = /^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
    //     return re.test(pass);
    // },

    _validatePassword(p, number){
        errors = [];
        var errors = [];
        if (p.length < 8 || p.length > 30) {
            errors.push("minimum 8 characters and maximum 30 characters"); 
        }
        if (p.search(/[A-Z]/) < 0) {
            errors.push("upper case letter");
        }
        if (p.search(/[a-z]/) < 0) {
            errors.push("lower case letter");
        }
        if (p.search(/[0-9]/) < 0) {
            errors.push("digit"); 
        }
        if (p.search(/[#?!@$%^&*-.]/) < 0) {
            errors.push("symbol (#?!@$%^&*-.)"); 
        }
        if (/\s/.test(p)) {
            errors.push("only visible characters"); 
        }
        if (errors.length > 0) {
            let baseText = "Password must contain at least"; 
            // alert(errors.join("\n"));
            // vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-error-'+ type).setValue('poki');
            this.lookupReference('password_error_' + number).setValue(baseText + " "+ errors.join(",\n") + "\n");
            this.lookupReference('password_error_' + number).setHidden(false);
            // return [false, baseText + " "+ errors.join(",\n")];
            return false;
        }else{
            this.lookupReference('password_error_' + number).setValue("");
        }

        // Hide component
        this.lookupReference('password_error_' + number).setHidden(true);
        // return [true, "Valid Password"];
        return true;
    },

    _comparePasswords(pass1, pass2, number){

        if (pass1 != pass2) {
            let newText = "Password does not match"; 
            password_1 = this._validatePassword(pass1, 1);
            // alert(errors.join("\n"));
            // vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-error-'+ type).setValue('poki');

            if(this.lookupReference('password_error_' + number).getValue() != null ){
                if(this.lookupReference('password_error_' + number).getValue()){
                    this.lookupReference('password_error_' + number).setValue(this.lookupReference('password_error_' + number).getValue() +",\n"+ newText);
                }else{
                    this.lookupReference('password_error_' + number).setValue(newText);
                }
               
            }else{
                this.lookupReference('password_error_' + number).setValue(newText);
            }
           
            this.lookupReference('password_error_' + number).setHidden(false);
            // return [false, baseText + " "+ errors.join(",\n")];
   
            return false;
        }else{
            password_1 = this._validatePassword(pass1, 1);
           

        }

        // Check and validate, if correct dont hide
        if(password_1){
            this.lookupReference('password_error_' + number).setHidden(true);

            return true;
        }else{
            this.lookupReference('password_error_' + number).setHidden(false);

            return false;
        }
       
    },

    _validatePin(p, number){
        errors = [];
        var errors = [];
        if (p.length < 5 || p.length > 7) {
            errors.push("6 characters"); 
        }
        if (p.search(/[0-9]/) < 0) {
            errors.push("all digits"); 
        }
        if (/\s/.test(p)) {
            errors.push("only visible characters"); 
        }
        if (errors.length > 0) {
            let baseText = "Pin must contain at least"; 
            // alert(errors.join("\n"));
            // vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-error-'+ type).setValue('poki');
            this.lookupReference('pin_error_' + number).setValue(baseText + " "+ errors.join(",\n")+ "\n");
            this.lookupReference('pin_error_' + number).setHidden(false);
            // return [false, baseText + " "+ errors.join(",\n")];
            return false;
        }else{
            this.lookupReference('pin_error_' + number).setValue("");
        }

        // Hide component
        this.lookupReference('pin_error_' + number).setHidden(true);
        // return [true, "Valid Password"];
        return true;
    },

    _comparePin(pin1, pin2, number){

        if (pin1 != pin2) {
            let newText = "Pin does not match"; 
            // alert(errors.join("\n"));
            // vm.get('orderpopup-gridform-'+ type).lookupController().lookupReference('orderpopup-error-'+ type).setValue('poki');

            if(this.lookupReference('pin_error_' + number).getValue() != null ){
                if(this.lookupReference('pin_error_' + number).getValue()){
                    this.lookupReference('pin_error_' + number).setValue(this.lookupReference('pin_error_' + number).getValue() +",\n"+ newText);
                }else{
                    this.lookupReference('pin_error_' + number).setValue(newText);
                }
              
            }else{
                  
                this.lookupReference('pin_error_' + number).setValue(newText);
            }
           
            this.lookupReference('pin_error_' + number).setHidden(false);
            // return [false, baseText + " "+ errors.join(",\n")];
   
            return false;
        }else{
            pin_1 = this._validatePin(pin1, 1);
           

        }

        // Check and validate, if correct dont hide
        if(pin_1){
            this.lookupReference('pin_error_' + number).setHidden(true);

            return true;
        }else{
            this.lookupReference('pin_error_' + number).setHidden(false);

            return false;
        }
       
    },

    nextButton: function(btn, formAction) {
        var myView = this.getView(),
            me = this;
        // var sm = myView.getSelectionModel();
        // var selectedRecords = sm.getSelection();

        // set path
        path = 'otcregisterview_' + PROJECTBASE;
        // grab form fields
        form = myView.getController().lookupReference('otcregisterform');
        data = form.getForm().getFieldValues();

        // New field
        directionRegistration = true;
        
        // validate
        personalform = myView.getController().lookupReference('register-form-personal');
        nokform = myView.getController().lookupReference('register-form-nok');
        bankaccountinfoform = myView.getController().lookupReference('register-form-bankaccountinfo');
        passwordform = myView.getController().lookupReference('register-form-password');

        pinform = myView.getController().lookupReference('register-form-pin');

        if(PROJECTBASE == 'BSN'){
            transactionpurpose = myView.getController().lookupReference('transactionpurpose').getRawValue();
        }else{
            transactionpurpose = '';
        }
        
        
        validation = false;
   
        // filter phone numbers
        mobileno = this._filterphone(data.mobile);

        if(PROJECTBASE != 'POSARRAHNU'){
            accounttypestr = me.lookupReference('accounttypestr').value;
        }else{
            accounttypestr = '';
        }
          
        

        // filter nric numbers
        // mykadno = this._filtermykadno(data.nric);
        // nokmykadno = this._filtermykadno(data.noknric);
        mykadno = data.nric;
        nokmykadno = data.noknric;
      
        nokcontactno = (data.nokmobile) ? this._filterphone(data.nokmobile) : '';

        email = data.email;
        // Validate email
        // If match, validate email will return result
        // If not match, return null
        // const validateEmail = (email) => {
        //     return String(email)
        //     .toLowerCase()
        //     .match(
        //       /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        //     );
        // };
        // Check if email validation is successful
        // If null means failed check
        // if(!validateEmail(email)){
        //     this.lookupReference('email_error').setHidden(false);
        //     // this.lookupReference('email').focus(false, 1000);
        // }else{
        //     this.lookupReference('email_error').setHidden(true);
        // }

        // Check if mandatory fields are filled
         // Confirm password
        // check if both passwords are validated 
        // if(data.enterpassword === "" || data.confirmpassword === ""){
        //     passwordValidation = true;
        // }else{
        //     passwordValidation = false;
        //     Ext.MessageBox.show({
        //         title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
        //         msg: 'Passwords cannot be blank'
        //     });
        //     return;
        // }
        
        // Check if passwordfield exists, otherwise skip this step
        if(passwordform){
            // Validate both passwords first
            // param 1 accepts string, param 2 accepts int to point reference
            password_1 = this._validatePassword(data.enterpassword, 1);
            password_2 = this._validatePassword(data.confirmpassword, 2);
            
            
            // Confirm password
            // check if both passwords are validated 
            comparePasswords = this._comparePasswords(data.enterpassword, data.confirmpassword, 1);

            password = data.enterpassword;
            generatePassword = false;
            //&& nokform.isValid() != false 
            if(personalform.isValid() != false && nokform.isValid() != false && bankaccountinfoform.isValid() != false && passwordform.isValid() != false && pinform.isValid() != false ){
                validation = true;
            }
        }else{
            // else skip check and randomly generate a password
            comparePasswords = true;
            password = null;
            // password = Math.random().toString(36).substr(2, 8);
            generatePassword = true;
            //&& nokform.isValid() != false 
            
            if(accounttypestr == 'BERSAMA'){	
                if(personalform.isValid() != false && nokform.isValid() != false && bankaccountinfoform.isValid() != false){	
                    validation = true;	
                }	
            }else if(accounttypestr == 'ORGANIS'){	
                if(personalform.isValid() != false){	
                    validation = true;	
                }	
            }else{	
                if(personalform.isValid() != false && bankaccountinfoform.isValid() != false ){	
                    validation = true;	
                }	
            }
        
        }

        /* 
        var invalidFields = [];
        Ext.suspendLayouts();
        passwordform.form.getFields().filterBy(function(field) {
            if (field.validate()) return;
            invalidFields.push(field);
        });
        Ext.resumeLayouts(true);
        */
       if(pinform){
            // Create pin
            initpin = data.init_pin_1.concat("", data.init_pin_2, 
            "", data.init_pin_3,
            "", data.init_pin_4,
            "", data.init_pin_5,
            "", data.init_pin_6);

            createpin = data.confirm_pin_1.concat("", data.confirm_pin_2, 
            "", data.confirm_pin_3,
            "", data.confirm_pin_4,
            "", data.confirm_pin_5,
            "", data.confirm_pin_6);

            pin_1 = this._validatePin(initpin, 1);
            pin_2 = this._validatePin(createpin, 2);

            comparePin = this._comparePin(initpin, createpin, 1);

            if(initpin == createpin && initpin.length === 6 && createpin.length === 6){
                pinValidation = true;
            }else{
                pinValidation = false;
                // Ext.MessageBox.show({
                //     title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                //     msg: 'Please make sure pin fields are filled and are matching'
                // });
                // return;
            }
            generatePin = false;
       }else{
            //initpin =   mykadno.substr(6,2) + mykadno.substr(8,4);
            currentmykadno = mykadno.replace(/\D/g,"");	
            newmykadno = currentmykadno.substr(currentmykadno.length - 6);	
            initpin = parseInt(newmykadno);	
            comparePin = true;	
            generatePin = true;
       }
      
       // Compare pin


        // Order Complete window
        // var windowforregistercomplete = new Ext.Window({
        //         title: 'Registration Successful.',
        //         html: 'Account Successfully Registered',
        //         layout: 'fit',
        //         width: 400,
        //         // width: '100%',
        //         // maxHeight: 700,
        //         modal: true,
        //         //closeAction: 'destroy',
        //         plain: true,
        //         buttonAlign: 'center',
        //         icon: Ext.MessageBox.INFO,
        //         buttons: [{
        //             text: 'OK',
        //             handler: function (btn) {

        //                 owningWindow = btn.up('window');
        //                 //owningWindow.closeAction='destroy';
        //                 owningWindow.close();
        //             }
        //         }, {
        //             text: 'Print PDF',
        //             handler: function (btn) {
        //                 me._printOrderPDFSpot(btn);
        //             }
        //         }],
        //         closeAction: 'destroy',
        //         //items: spotpanelbuytotalxauweight
        // });
        // do validation check
        // if all true, move on to post, else error
        //data.email = 'jaccob@gmail.my';
        //data.address = 'Kuala Lumpur City Centre, Kuala Lumpur , Kuala Lumpur, Malaysia Postcode (Poskod): 50088 Kuala Lumpur';

        // This part resets pin after pressing register
       // Pass myview to clear fields

        // if projectbase alrajhi, hide form
        if(PROJECTBASE == 'ALRAJHI'){
            // check if biometric is skipped, if yes get remarks
            // myView.getController().lookupReference('otcregisterform').setHidden(true);
            // myView.getController().lookupReference('casaaccountlist-tab').setHidden(true);
            // searchbar = myView.getController().lookupReference('otcregisterform-searchbar');
            // if(searchbar){
            //     searchbar.setHidden(true);
            // }
            // myView.getController().lookupReference('casasearchfields').reset();

            // // reshow biometric checker
            // myView.getController().lookupReference('otcregisterform-biometrics').setHidden(false);

            // Set default occupationcategory to 3
            data.occupationcategory = 3;

            //check accounttype and populate accountname
            if(data.accounttype == 22){
                // check if joint
                accountname = data.heading;
            }else{
                accountname = data.fullname;
            }

            // Check if company or sole
            // const TYPE_COMPANY = 21;
            // const TYPE_COHEADING = 22;
            // const TYPE_SOLEPROPRIETORSHIP = 23;
            if (data.accounttype == 21 || data.accounttype == 23){
                // no way to directly register unless they succeed
                directionRegistration = false;
               
            }
            
        }
        // if(PROJECTBASE == 'BSN'){
        //     // check if biometric is skipped, if yes get remarks
        //     myView.getController().lookupReference('otcregisterform').setHidden(true);
        //     myView.getController().lookupReference('casaaccountlist-tab').setHidden(true);
        //     searchbar = myView.getController().lookupReference('otcregisterform-searchbar');
        //     if(searchbar){

        //         searchbar.setHidden(true);
        //     }
        //     myView.getController().lookupReference('casasearchfields').reset();

        //     // reshow biometric checker
        //     //myView.getController().lookupReference('otcregisterform-biometrics').setHidden(false);

        // }

        accountname = '';
        
        // Add new validation before below validation
        if(validation == true && comparePasswords == true && comparePin == true){

            if(PROJECTBASE == 'ALRAJHI' && (data.accounttype == 21 || data.accounttype == 23)){
                // Only use for alrajhi corporate and sole proprietor accounts
                Ext.MessageBox.confirm(
                    'Confirm Approval', 'Are you sure you want to submit for approval ?', function (btn) {
                        if (btn === 'yes') {
                            // vm.set('otc-register-remarks', remarks);
                            var remarks = 'Company Registration';
                            snap.getApplication().sendRequest({
                                hdl: 'otcregisterremarks', 'action': 'registerapproval', 'ic_no':mykadno, 'remarks': remarks, 'partnercode' : PROJECTBASE, type : 'Registration',
                            }, 'Sending Approval').then(
                                function (approvedata) {
                                    console.log(approvedata)
                                    if (approvedata.success) {
                                        if (approvedata.isawait) {
                                            Ext.MessageBox.wait('Waiting For Approval...', 'Please wait', {
                                                icon: 'my-loading-icon'
                                            });
                                            const url = 'index.php?hdl=otcregisterremarks&action=checkapprovalstatus&id=' + approvedata.id + '&approve=yes';
                                            const intervalId = setInterval(async () => {
                                                try {
                                                    const response = await Ext.Ajax.request({
                                                        url: url,
                                                        method: 'GET'
                                                    });
                                                    const responseData = Ext.JSON.decode(response.responseText);
                                                    console.log(responseData);
                                        
                                                    if (!responseData.ispendingapproval) {
                                                        clearInterval(intervalId);
                                                        console.log('Approval process complete');
                                                        if (responseData.status === '1') {
                                                            // Code to execute when registration is approved
                                                        
                                                            // elmnt.lookupReference('statusremarks').setValue(remarks);
                                                            // modalBtn.up().up().close();
                                                            // elmnt.lookupReference('otcregisterform-biometrics').setHidden(true);
                                                            // elmnt.lookupReference('otcregisterform-searchbar').setHidden(false);
                                                            // elmnt.moveSelectionToSearchBox();

                                                            Ext.MessageBox.show({
                                                                title: 'Registration Approved',
                                                                buttons: Ext.MessageBox.OK,
                                                                iconCls: 'x-fa fa-check-circle',
                                                                msg: 'Proceed to Registration without biometric',
                                                            });
                                               
                                                            // register here
                                                            snap.getApplication().sendRequest({ hdl: 'myaccountholder', action: 'otcRegister',
                                                                partnercode: PROJECTBASE,
                                                                full_name: data.fullname,
                                                                mykad_number: mykadno,
                                                                phone_number: mobileno, 
                                                                occupation_category: data.occupationcategory ? data.occupationcategory : 0,
                                                                //occupation_subcategory: data.occupationsubcategory ? data.occupationsubcategory : 0,
                                                                email: data.email ? data.email : '',
                                                                password: password,
                                                                nok_full_name: data.nokfullname ? data.nokfullname : '',
                                                                nok_mykad_number: nokmykadno ? nokmykadno : '',
                                                                nok_phone: nokcontactno ? nokcontactno : '',
                                                                nok_email: data.nokemail ? data.nokemail : '',
                                                                nok_address: data.nokaddress ? data.nokaddress : '',
                                                                nok_relationship: data.nokrelationship  ? data.nokrelationship : '',
                                                                phone_verification_code: 220163,
                                                                partner_customer_id: data.partnercusid  ? data.partnercusid : '',
                                                                partner_data: data.partnerdata  ? data.partnerdata : null,
                                                                branchident: data.branchident ? data.branchident : '',
                                                                // new fields for bank
                                                                new_pin: initpin ? initpin : '',
                                                                address: data.address ? data.address : '',
                                                                city: data.city ? data.city : 'bandar puteri',
                                                                postcode: data.postcode ? data.postcode : '00000',
                                                                state: data.state ? data.state : 'selangor',
                                                                bank_account_id : data.bankaccounts ? data.bankaccounts : 0,
                                                                bank_account_number: data.bankaccountnumber,
                                                                generate_password: generatePassword ? generatePassword : false,
                                                                generate_pin: generatePin ? generatePin : false,
                                                                campaign_code: data.campaigncode ? data.campaigncode : '',

                                                                partnercusid: data.partnercusid ? data.partnercusid : '',
                                                                accounttype: data.accounttype ? data.accounttype : '',
                                                                referralsalespersoncode: data.referralsalespersoncode ? data.referralsalespersoncode : '',
                                                                referralintroducercode: data.referralintroducercode ? data.referralintroducercode : '',

                                                                nationality: data.nationality ? data.nationality : '',
                                                                dateofbirth: data.dateofbirth ? data.dateofbirth : '',
                                                                religion: data.religion ? data.religion : '',
                                                                gender: data.gender ? data.gender : '',
                                                                bumiputera: data.bumiputera ? data.bumiputera : '',
                                                                maritalstatus: data.maritalstatus ? data.maritalstatus : '',
                                                                race: data.race ? data.race : '',

                                                                jointgender: data.jointgender ? data.jointgender : '',
                                                                jointdateofbirth: data.jointdateofbirth ? data.jointdateofbirth : '',
                                                                jointnationality: data.jointnationality ? data.jointnationality : '',
                                                                jointreligion: data.jointreligion ? data.jointreligion : '',
                                                                jointrace: data.jointrace ? data.jointrace : '',
                                                                jointbumiputera: data.jointbumiputera ? data.jointbumiputera : '',
                                                                jointmaritalstatus: data.jointmaritalstatus ? data.jointmaritalstatus : '',

                                                                transactionpurpose: transactionpurpose ? transactionpurpose : '',
                                                                accountname: accountname ? accountname : data.accountname,

                                                            }, 'Sending request....')
                                                            .then(function(data){
                                                                if(data.success) {
                                                            
                                                                    myView.getController().lookupReference('otcregisterform').setHidden(true);
                                                                
                                                                    Ext.Msg.show({
                                                                        title: 'Account successfully registered',
                                                                        message: 'Account successfully registered for ' + data.accountholderfullname,
                                                                        // buttons: Ext.Msg.YESNOCANCEL,
                                                                        autoClose:false,
                                                                        buttonText: {
                                                                            yes: 'Print',
                                                                            // cancel: 'MyCancel'
                                                                        },
                                                                        closeAction: 'none',
                                                                        closable: false,
                                                                        draggable: false,
                                                                        modal: true,
                                                                        icon: Ext.Msg.QUESTION,
                                                                        fn: function (btn) {
                                                                            if (btn === 'yes') {
                                                                                me._printRegisterPDF(btn, data.accountholderid, data.password, data.pin, data.havewebsite);
                                                                            }
                                                                            // else if (btn === 'no') {
                                                                            //     owningWindow = btn.up('window');
                                                                            //     owningWindow.close();
                                                                            // } else {
                                                                            //     console.log('MyCancel');
                                                                            // }
                                                                        },
                                                                        
                                                                    });

                                                                    // manual reset confirm pin x 6
                                                                    myView.getController().lookupReference('register-panel-confirmpin').items.items[1].reset();
                                                                    myView.getController().lookupReference('register-panel-confirmpin').items.items[2].reset();
                                                                    myView.getController().lookupReference('register-panel-confirmpin').items.items[3].reset();
                                                                    myView.getController().lookupReference('register-panel-confirmpin').items.items[4].reset();
                                                                    myView.getController().lookupReference('register-panel-confirmpin').items.items[5].reset();
                                                                    myView.getController().lookupReference('register-panel-confirmpin').items.items[6].reset();

                                                                    // if projectbase alrajhi, hide form
                                                                    if(PROJECTBASE == 'ALRAJHI'){
                                                                        // reset form and hide all core form components
                                                                        me.lookupReference('otcregisterform').setHidden(true);
                                                                        searchbar = me.lookupReference('otcregisterform-searchbar');
                                                                        if(searchbar){
                                                                            searchbar.setHidden(true);
                                                                        }
                                                                        me.lookupReference('casasearchfields').reset()

                                                                        // reshow biometric checker
                                                                        me.lookupReference('otcregisterform-biometrics').setHidden(false);
                                                                    }

                                                                    // debugger;
                                                                    me._clearRegistrationForm(me);
                                                                    // Clear form
                                                                    personalform.reset();;
                                                                    // nokform.reset();
                                                                    bankaccountinfoform.reset();
                                                                    if(passwordform){
                                                                        passwordform.reset();
                                                                    }

                                                                
                                                    
                                                                    
                                                                    displayCallback(data.record);
                                                                }
                                                            })
                                                            me.redirectTo(path);
                                                        } else {
                                                            // Code to execute when registration is not approved
                                                            // elmnt.lookupReference('statusremarks').setValue('');
                                                            // elmnt.lookupReference('otcregisterform-biometrics').setHidden(false);
                                                            // elmnt.lookupReference('otcregisterform-searchbar').setHidden(true);
                                                            Ext.MessageBox.show({
                                                                title: 'Registration Not Approved',
                                                                buttons: Ext.MessageBox.OK,
                                                                iconCls: 'x-fa fa-times-circle',
                                                                msg: 'Cannot Proceed to Registration without biometric',
                                                            });
                                                        }
                                                    }
                                                } catch (error) {
                                                    console.error('Request failed', error);
                                                    clearInterval(intervalId);
                                                    Ext.MessageBox.show({
                                                        title: 'Error',
                                                        buttons: Ext.MessageBox.OK,
                                                        iconCls: 'x-fa fa-exclamation-circle',
                                                        msg: 'An error occurred while checking approval status. Please try again later.',
                                                    });
                                                }
                                            }, 10000);
                                        } else {
                                            console.warn('Data is not awaiting approval.');
                                            Ext.MessageBox.show({
                                                title: 'Not Await',
                                                buttons: Ext.MessageBox.OK,
                                                iconCls: 'x-fa fa-info-circle',
                                                msg: 'The data is not awaiting approval.',
                                            });
                                        }
                                    } else {
                                        Ext.MessageBox.show({
                                            title: 'Error Message',
                                            msg: data.errorMessage,
                                            buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ERROR
                                        });
                                    }
                            });
                            
                        }
                    }
                );
            }else{
                
                // Register like normal
                snap.getApplication().sendRequest({ hdl: 'myaccountholder', action: 'otcRegister',
                    partnercode: PROJECTBASE,
                    full_name: data.fullname,
                    mykad_number: mykadno,
                    phone_number: mobileno, 
                    occupation_category: data.occupationcategory ? data.occupationcategory : 3,
                    occupation_subcategory: data.occupationsubcategory ? data.occupationsubcategory : 0,
                    email: data.email ? data.email : '',
                    password: password,
                    nok_full_name: data.nokfullname ? data.nokfullname : '',
                    nok_mykad_number: nokmykadno ? nokmykadno : '',
                    nok_phone: nokcontactno ? nokcontactno : '',
                    nok_email: data.nokemail ? data.nokemail : '',
                    nok_address: data.nokaddress ? data.nokaddress : '',
                    nok_relationship: data.nokrelationship  ? data.nokrelationship : '',
                    phone_verification_code: 220163,
                    partner_customer_id: data.partnercusid  ? data.partnercusid : '',
                    partner_data: data.partnerdata  ? data.partnerdata : null,
                    branchident: data.branchident ? data.branchident : '',
                    // new fields for bank
                    new_pin: initpin ? initpin : '',
                    address: data.address ? data.address : '',
                    city: data.city ? data.city : 'bandar puteri',
                    postcode: data.postcode ? data.postcode : '00000',
                    state: data.state ? data.state : 'selangor',
                    bank_account_id : data.bankaccounts ? data.bankaccounts : 0,
                    bank_account_number: data.bankaccountnumber,
                    generate_password: generatePassword ? generatePassword : false,
                    generate_pin: generatePin ? generatePin : false,
                    campaign_code: data.campaigncode ? data.campaigncode : '',

                    partnercusid: data.partnercusid ? data.partnercusid : '',
                    accounttype: data.accounttype ? data.accounttype : '',
                    referralsalespersoncode: data.referralsalespersoncode ? data.referralsalespersoncode : '',
                    referralintroducercode: data.referralintroducercode ? data.referralintroducercode : '',

                    nationality: data.nationality ? data.nationality : '',
                    dateofbirth: data.dateofbirth ? data.dateofbirth : '',
                    religion: data.religion ? data.religion : '',
                    gender: data.gender ? data.gender : '',
                    bumiputera: data.bumiputera ? data.bumiputera : '',
                    maritalstatus: data.maritalstatus ? data.maritalstatus : '',
                    race: data.race ? data.race : '',

                    jointgender: data.jointgender ? data.jointgender : '',
                    jointdateofbirth: data.jointdateofbirth ? data.jointdateofbirth : '',
                    jointnationality: data.jointnationality ? data.jointnationality : '',
                    jointreligion: data.jointreligion ? data.jointreligion : '',
                    jointrace: data.jointrace ? data.jointrace : '',
                    jointbumiputera: data.jointbumiputera ? data.jointbumiputera : '',
                    jointmaritalstatus: data.jointmaritalstatus ? data.jointmaritalstatus : '',

                    category: data.category ? data.category : '',

                    transactionpurpose: transactionpurpose ? transactionpurpose : '',
                    accountname: accountname ? accountname : data.accountname,

                }, 'Sending request....')
                .then(function(data){
                    if(data.success) {
                
                        myView.getController().lookupReference('otcregisterform').setHidden(true);
                        
                        Ext.Msg.show({
                            title: 'Account successfully registered',
                            message: 'Account successfully registered for ' + data.accountholderfullname,
                            // buttons: Ext.Msg.YESNOCANCEL,
                            autoClose:false,
                            buttonText: {
                                yes: 'Print',
                                // cancel: 'MyCancel'
                            },
                            closeAction: 'none',
                            closable: false,
                            draggable: false,
                            modal: true,
                            icon: Ext.Msg.QUESTION,
                            fn: function (btn) {
                                if (btn === 'yes') {
                                    me._printRegisterPDF(btn, data.accountholderid, data.password, data.pin, data.havewebsite);
                                }
                                // else if (btn === 'no') {
                                //     owningWindow = btn.up('window');
                                //     owningWindow.close();
                                // } else {
                                //     console.log('MyCancel');
                                // }
                            },
                            
                        });

                        // if projectbase alrajhi, hide form
                        if(PROJECTBASE == 'ALRAJHI'){
                            // reset form and hide all core form components
                            me.lookupReference('otcregisterform').setHidden(true);
                            searchbar = me.lookupReference('otcregisterform-searchbar');
                            if(searchbar){
                                searchbar.setHidden(true);
                            }
                            me.lookupReference('casasearchfields').reset()

                            // reshow biometric checker
                            me.lookupReference('otcregisterform-biometrics').setHidden(false);
                        }

                        // debugger;
                        me._clearRegistrationForm(me);
                        // Clear form
                        personalform.reset();;
                        // nokform.reset();
                        bankaccountinfoform.reset();
                        if(passwordform){
                            passwordform.reset();
                        }

                    
        
                        
                        displayCallback(data.record);
                    }
                })
                me.redirectTo(path);
            }
            

        }else{
            // windowforregistercomplete.show();
            // Ext.Msg.show({
            //     title: 'Title',
            //     message: 'Message',
            //     // buttons: Ext.Msg.YESNOCANCEL,
            //     buttons: Ext.Msg.YESNO,
            //     buttonText: {
            //         yes: 'Print PDF',
            //         no: 'OK',
            //         // cancel: 'MyCancel'
            //     },
            //     icon: Ext.Msg.QUESTION,
            //     fn: function (btn) {
            //         if (btn === 'yes') {
            //             me._printRegisterPDF(btn, 1);
            //         } 
            //         // else if (btn === 'no') {
            //         //     owningWindow = btn.up('window');
            //         //     owningWindow.close();
            //         // } else {
            //         //     console.log('MyCancel');
            //         // }
            //     }
            // });
            
            Ext.MessageBox.show({
                title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                msg: 'Please fill all the mandatory form fields'
            });
        }
      
        // if (selectedRecords.length == 1) {
        //     for (var i = 0; i < selectedRecords.length; i++) {
        //         selectedID = selectedRecords[i].get('id');
        //         record = selectedRecords[i];
        //         me.redirectTo(path + '/accountholder/' + selectedID);
        //         break;
        //     }
        // } else {
        //     Ext.MessageBox.show({
        //         title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
        //         msg: 'Select a record first'
        //     });
        //     return;
        // }
	},

    // Print registration fields in PDF format
    _printRegisterPDF: function(btn, accountholderid, password = null, pin = null, haveWebsite = null) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this;

        // Get Printable data
        // orderid = btn.up().up().items.items[0].items.items[4].getValue();
         
        var url = 'index.php?hdl=myaccountholder&action=printRegisterPDF&accountholderid='+accountholderid+'&p='+password+'&b='+PROJECTBASE+'&c='+pin+'&w='+haveWebsite;
				Ext.Ajax.request({
					url: url,
					method: 'get',
					waitMsg: 'Processing',
					//params: { summaryfromdate: summaryfromdate, summarytodate: summarytodate, summarytype: summarytype },
					autoAbort: false,
					success: function (result) {
                        //if(PROJECTBASE != 'POSARRAHNU'){
                            //debugger;
                        //    window.location.href = result.responseText;
                        //}else{
                        //    var win = window.open('');
                        //    win.location = url;
                        //    win.focus();
                        //}
			var responseData = Ext.decode(result.responseText);
                        if (responseData.success === true) {
                            var win = window.open('');
                            win.location = responseData.url;
                            win.focus();
                        }else {
                            // The request was successful, but the response indicates failure
                            Ext.MessageBox.show({
                                title: 'Error Message',
                                msg: responseData.errorMessage, // You can customize the error message based on the response
                               buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                           });
                        }
					},
					failure: function () {
						
						Ext.MessageBox.show({
							title: 'Error Message',
							msg: 'Failed to retrieve data',
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				});

    },

    /*onPreLoadViewDetail: function(record, displayCallback) {
    	snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'detailview', id: record.data.id, status_text: record.data.status_text,})
    	.then(function(data){
    		if(data.success) {
    			displayCallback(data.record);
    		}
    	})
        return false;
	}*/

    // onPreLoadViewDetail: function(record, displayCallback) {
    //     snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'detailview', id: record.data.id})
    //     .then(function(data){
    //         if(data.success) {
    //             displayCallback(data.record);
    //         }
    //     })
    //     return false;
    // },


    // cancelOrders: function(btn, formAction) {
    //     var me = this, selectedRecord,
    //         myView = this.getView();
    //     var sm = myView.getSelectionModel();
    //     var selectedRecords = sm.getSelection();
    //     if (selectedRecords.length == 1) {
    //         for(var i = 0; i < selectedRecords.length; i++) {
    //             selectedID = selectedRecords[i].get('id');
    //             selectedRecord = selectedRecords[i];
    //             break;
    //         }
    //     } else if('add' != formAction) {
    //         Ext.MessageBox.show({
    //             title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
    //             msg: 'Select a record first'});
    //         return;
    //     }

    //     snap.getApplication().sendRequest({ hdl: 'orderqueue', action: 'cancelFutureOrder', 
    //                                         id: selectedRecord.data.id,
    //                                         partnerid: selectedRecord.data.partnerid,
    //                                         apiversion: selectedRecord.data.apiversion,
    //                                         refid: selectedRecord.data.partnerrefid,
    //                                         notifyurl: selectedRecord.data.notifyurl,
    //                                         reference: selectedRecord.data.remarks,
    //                                         timestamp: selectedRecord.data.createdon,
    //                                     })
    // 	.then(function(data){
    // 		if(data.success) {
    //             Ext.MessageBox.show({
    //                 title: 'Notification', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.ALERT,
    //                 msg: 'Successfully cancelled future order'});
    //         }
    //         if(!data.success) {
    //             Ext.MessageBox.show({
    //                 title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
    //                 msg: 'Unable to cancel order'});
    // 		}
    // 	})
    // }

    // BSN CASA GET
    getAccountsFromCasa: function(btn, accountholderid, password = null, pin = null) {
        // var owningWindow = btn.up('window');
        // var gridFormPanel = owningWindow.down('form');
        var me = this;

        // Get Printable data
        // orderid = btn.up().up().items.items[0].items.items[4].getValue();
        
        if(PROJECTBASE != 'POSARRAHNU'){
            searchfield = me.lookupReference('casasearchfields').getValue();
            option = me.lookupReference('casasearchtype').getValue();
        }
        

        // snap.getApplication().sendRequest({ hdl: 'myaccountholder', action: 'getAccountsFromCasa',
        //     partnercode: PROJECTBASE,
        //     searchFlag: option ? option : '',
        //     partyId: searchfield ? searchfield : ''
        // }, 'Sending request....')
        // .then(function(data){
        //     debugger;
        //     if(data.success) {
        //         me.lookupReference('casaaccountlist-tab').setHidden(false);
        //         me.lookupReference('casaaccountlist').store.setData(data.records);
        //         Ext.Msg.show({
        //             title: 'Accounts found',
        //             message: 'Please select an account to create account with',
        //             // buttons: Ext.Msg.YESNOCANCEL,
        //             buttons: Ext.Msg.YES,
        //         });
        //     }else{
        //         debugger;
        //         // me.lookupReference('casaaccountlist-tab').setHidden(true);
        //         me._clearRegistrationForm(me);
        //         debugger;
        //         Ext.Msg.show({
        //             title: 'Accounts not found',
        //             message: data.errorMessage,
        //             // buttons: Ext.Msg.YESNOCANCEL,
        //             buttons: Ext.Msg.YES,
        //         });
        //     }
        // })
        // Display a loading message or spinner
        Ext.MessageBox.show({
            msg: 'Loading...',
            progressText: 'Loading...',
            width: 300,
            wait: true,
            waitConfig: { interval: 200 }
        });
        var url = 'index.php?hdl=myaccountholder&action=getAccountsFromCasa&partnercode='+PROJECTBASE+'&option='+option+'&searchfield='+searchfield;
        Ext.Ajax.request({
            url: url,
            method: 'POST',
            // params: {
            //     hdl: 'myaccountholder',
            //     action: 'getAccountsFromCasa',
            //     partnercode: PROJECTBASE,
            //     searchFlag: option ? option : '',
            //     partyId: searchfield ? searchfield : ''
            // },
            success: function(response) {
                // Hide the loading message or spinner
                Ext.MessageBox.hide();

                var data = Ext.decode(response.responseText);
                // Handle the response data
                // Account data found
                // Access data.records to get the account records
                // Perform necessary actions
                if(data.success) {
                    //debugger;
                    if(PROJECTBASE == 'ALRAJHI'){
                        me.lookupReference('evidencecode').setValue(data.evidenceCode.data[0]);
                        // For alrajhi we populate registration fields directly
                        if (data.records.length > 0) {
                            me._populateRegistrationForm(me, data.records[0]);
                            // me.lookupReference('casaaccountlist-tab').setHidden(false);
                            me.lookupReference('casaaccountlist').store.setData(data.records);

                            // Search for any entry with accountstatus = 0
                            hasAccountStatusZero = data.records.some(record => record.accountstatus === 0);
                        
                            // if yes means account has registered before and lock out submit button
                            if (hasAccountStatusZero) {
                                me.lookupReference('next').setHidden(true);
                                Ext.Msg.show({
                                    title: 'Customer accounts already registered',
                                    message: 'Account has already been registered',
                                    // buttons: Ext.Msg.YESNOCANCEL,
                                    buttons: Ext.Msg.YES,
                                });
                            } else {
                                me.lookupReference('next').setHidden(false);
                                Ext.Msg.show({
                                    title: 'Customer accounts found',
                                    message: 'Please verify all the information before proceeding',
                                    // buttons: Ext.Msg.YESNOCANCEL,
                                    buttons: Ext.Msg.YES,
                                });
                            }

                           
                        }
                        
                        
                    }else if (PROJECTBASE == 'BSN'){
                        //debugger;
                        me.lookupReference('casaaccountlist-tab').setHidden(false);
                        me.lookupReference('casaaccountlist').store.setData(data.records);
                        me.lookupReference('otcregisterform').setHidden(true);

                        Ext.Msg.show({
                            title: 'Accounts found',
                            message: 'Please select an account to create account with',
                            // buttons: Ext.Msg.YESNOCANCEL,
                            buttons: Ext.Msg.YES,
                        });
                    }
                    
                    
                    
                   
                }else{
                    if(PROJECTBASE == 'ALRAJHI'){
                        me.lookupReference('evidencecode').setValue('');
                        me.lookupReference('casaaccountlist').store.setData('');
                    }else if (PROJECTBASE == 'BSN'){
                        me.lookupReference('casaaccountlist-tab').setHidden(true);
                    }
                    
                    me._clearRegistrationForm(me);
                    me.lookupReference('otcregisterform').setHidden(true);
                    // Ext.Msg.show({
                    //     title: 'Customer accounts not found',
                    //     message: data.errorMessage,
                    //     // buttons: Ext.Msg.YESNOCANCEL,
                    //     buttons: Ext.Msg.YES,
                    // });
                }
            },
            failure: function(response) {
                // Hide the loading message or spinner
   
                Ext.MessageBox.hide();
                // No account data found
                // Access data.errorMessage to get the error message
                // Perform necessary actions
                if(PROJECTBASE == 'ALRAJHI'){
                    me.lookupReference('evidencecode').setValue('');
                }
                me.lookupReference('casaaccountlist-tab').setHidden(true);
      
                Ext.Msg.show({
                    title: 'Accounts not found',
                    message: response.statusText,
                    // buttons: Ext.Msg.YESNOCANCEL,
                    buttons: Ext.Msg.YES,
                });
                // Handle the request failure
                // Access response.status to get the HTTP status code
                // Access response.statusText to get the status text
                // Perform necessary actions
            }
        });

    },

    _clearRegistrationForm: function(me){
        // me.lookupReference('otcregisterform').setHidden(true);

        me.lookupReference('fullname').setValue('');
        me.lookupReference('nokfullname').setValue('');
        me.lookupReference('mykadno').setValue('');
        me.lookupReference('nokmykadno').setValue('');
        me.lookupReference('email').setValue('');

        me.lookupReference('mobile').setValue('');
        me.lookupReference('address').setValue('');
        // me.lookupReference('city', selection.data.phoneno);
        me.lookupReference('postcode').setValue('');
        // me.lookupReference('parstate', selection.data.phoneno);

        // me.lookupReference('nokfullname', selection.data.fullname);
        // me.lookupReference('nokmykadno', selection.data.mykadno);
        // me.lookupReference('nokemail', selection.data.email);
        // me.lookupReference('nokphoneno', selection.data.phoneno);
        // me.lookupReference('nokaddress', selection.data.phoneno);
        // me.lookupReference('nokphoneno', selection.data.phoneno);
        // me.lookupReference('nokaddress', selection.data.phoneno);
        me.lookupReference('nokfullname').setValue('');
        me.lookupReference('nokmykadno').setValue('');
        me.lookupReference('nokemail').setValue('');

        me.lookupReference('partnercusid').setValue('');
        // me.lookupReference('bankaccount').setValue(selection.data.bankaccount);
        if(PROJECTBASE == 'BSN'){
            me.lookupReference('bankaccountnumber').setValue('');
            //me.lookupReference('referralsalespersoncode').setValue('');
      
        }else if(PROJECTBASE == 'ALRAJHI'){
            me.lookupReference('statusremarks').setValue('');

            me.lookupReference('address').setValue('');
            me.lookupReference('postcode').setValue('');
            me.lookupReference('city').setValue('');
            me.lookupReference('state').setValue('');

            me.lookupReference('mykadno').setValue('');
            me.lookupReference('partyid').setValue('');
            me.lookupReference('fullname').setValue('');
            
     
            me.lookupReference('nokmykadno').setValue('');
            me.lookupReference('nokpartyid').setValue('');
            me.lookupReference('nokfullname').setValue('');
            
            me.lookupReference('nokphoneno').setValue('');
            me.lookupReference('nokemail').setValue('');
        }

        // sales codes

        //me.lookupReference('referralintroducercode').setValue('');

        //me.lookupReference('campaigncode').setValue('');

        me.lookupReference('accounttype').setValue('');
        me.lookupReference('accounttypestr').setValue('');

        //me.lookupReference('confirm_pin_1').setValue('');
        //me.lookupReference('confirm_pin_2').setValue('');
        //me.lookupReference('confirm_pin_3').setValue('');
        //me.lookupReference('confirm_pin_4').setValue('');
        //me.lookupReference('confirm_pin_5').setValue('');
        //me.lookupReference('confirm_pin_6').setValue('');

        //me.lookupReference('init_pin_1').setValue('');
        //me.lookupReference('init_pin_2').setValue('');
        //me.lookupReference('init_pin_3').setValue('');
        //me.lookupReference('init_pin_4').setValue('');
        //me.lookupReference('init_pin_5').setValue('');
        //me.lookupReference('init_pin_6').setValue('');
    
       
    },

    showRegistrationForm: function(combobox, selection, func){

        var myView = this.getView(),
        me = this;

        if(PROJECTBASE == 'BSN'){
            // If accountstatus is unused, proceed
            if(selection.data.accountstatus !=0 ){
                phoneno = selection.data.phoneno.toString();
                // me.lookupReference('casaaccountlist').store.getData;
                // var getDisplayController = this.up().up().up().up().getController();
                personalform = me.lookupReference('register-form-personal');
                // nokform = myView.getController().lookupReference('register-form-nok');
                // bankaccountinfoform = myView.getController().lookupReference('register-form-bankaccountinfo');
                // passwordform = myView.getController().lookupReference('register-form-password');
                me.lookupReference('otcregisterform').setHidden(false);
                me.lookupReference('otcregisterform').reset();
                // pinform = myView.getController().lookupReference('register-form-pin');

                me.lookupReference('fullname').setValue(selection.data.fullname);
                me.lookupReference('mykadno').setValue(selection.data.mykadno);
                if(selection.data.email == ''){
                    me.lookupReference('email').setValue('default@mail.com');
                }else{
                    me.lookupReference('email').setValue(selection.data.email);
                }

                me.lookupReference('mobile').setValue(phoneno);
                me.lookupReference('address').setValue(selection.data.line1);
                // me.lookupReference('city', selection.data.phoneno);
                me.lookupReference('postcode').setValue(selection.data.postcode);
                // me.lookupReference('parstate', selection.data.phoneno);

                // me.lookupReference('nokfullname', selection.data.fullname);
                // me.lookupReference('nokmykadno', selection.data.mykadno);
                // me.lookupReference('nokemail', selection.data.email);
                // me.lookupReference('nokphoneno', selection.data.phoneno);
                // me.lookupReference('nokaddress', selection.data.phoneno);
                // me.lookupReference('nokphoneno', selection.data.phoneno);
                // me.lookupReference('nokaddress', selection.data.phoneno);

                me.lookupReference('partnercusid').setValue(selection.data.partnercusid);
                // me.lookupReference('bankaccount').setValue(selection.data.bankaccount);
                me.lookupReference('bankaccountnumber').setValue(selection.data.accountnumber);
                me.lookupReference('accounttype').setValue(selection.data.accounttype);
                me.lookupReference('accounttypestr').setValue(selection.data.accounttypestr);
                
                // hidden field
                me.lookupReference('city').setValue('-');
                me.lookupReference('category').setValue(selection.data.category);
                me.lookupReference('category').hide();
                me.lookupReference('nationality').setValue(selection.data.nationality);
                me.lookupReference('dateofbirth').setValue(selection.data.dateofbirth);
                me.lookupReference('bumiputera').setValue(selection.data.bumiputera);
                me.lookupReference('religion').setValue(selection.data.religion);
                me.lookupReference('gender').setValue(selection.data.gender);
                me.lookupReference('maritalstatus').setValue(selection.data.maritalstatus);
                me.lookupReference('race').setValue(selection.data.race);

                // check if sendiri 
                if(selection.data.accounttype == 1 || selection.data.accounttype == 23 || selection.data.accounttype == 24){
                    
                    me.lookupReference('register-form-nok').setHidden(true);

                    me.lookupReference('nokfullname').allowBlank = true;
                    me.lookupReference('nokemail').allowBlank = true;
                    me.lookupReference('nokmykadno').allowBlank = true;
                    me.lookupReference('nokphoneno').allowBlank = true;
                    //me.lookupReference('nokaddress').allowBlank = true;
                    me.lookupReference('nokrelationship').allowBlank = true;
                    //me.lookupReference('occupationsubcategory').allowBlank = true;
                    me.lookupReference('jointgender').allowBlank = true;
                    me.lookupReference('jointdateofbirth').allowBlank = true;
                    me.lookupReference('jointnationality').allowBlank = true;
                    me.lookupReference('jointreligion').allowBlank = true;
                    me.lookupReference('jointrace').allowBlank = true;
                    me.lookupReference('jointbumiputera').allowBlank = true;

                    me.lookupReference('nokfullname').setValue('');
                    me.lookupReference('nokemail').setValue('');
                    me.lookupReference('nokmykadno').setValue('');
                    me.lookupReference('nokphoneno').setValue('');
                    //me.lookupReference('nokaddress').setValue('');
                    me.lookupReference('nokrelationship').setValue('');
                    me.lookupReference('jointgender').setValue('');
                    me.lookupReference('jointdateofbirth').setValue('');
                    me.lookupReference('jointnationality').setValue('');
                    me.lookupReference('jointreligion').setValue('');
                    me.lookupReference('jointrace').setValue('');
                    me.lookupReference('jointbumiputera').setValue('');

                }else if(selection.data.accounttype == 2 || selection.data.accounttype == 22){
                    // check if joint
                    me.lookupReference('register-form-nok').setHidden(false);

                    me.lookupReference('nokfullname').allowBlank = false;
                    me.lookupReference('nokemail').allowBlank = false;
                    me.lookupReference('nokmykadno').allowBlank = false;
                    me.lookupReference('nokphoneno').allowBlank = false;
                    //me.lookupReference('nokaddress').allowBlank = false;
                    me.lookupReference('nokrelationship').allowBlank = false;
                    me.lookupReference('jointgender').allowBlank = false;
                    me.lookupReference('jointdateofbirth').allowBlank = false;
                    me.lookupReference('jointnationality').allowBlank = false;
                    me.lookupReference('jointreligion').allowBlank = false;
                    me.lookupReference('jointrace').allowBlank = false;
                    me.lookupReference('jointbumiputera').allowBlank = false;

                }else if(selection.data.accounttype == 3 || selection.data.accounttype == 21){
                    
                    me.lookupReference('register-form-nok').setHidden(true);

                    me.lookupReference('nokfullname').allowBlank = true;
                    me.lookupReference('nokemail').allowBlank = true;
                    me.lookupReference('nokmykadno').allowBlank = true;
                    me.lookupReference('nokphoneno').allowBlank = true;
                    //me.lookupReference('nokaddress').allowBlank = true;
                    me.lookupReference('nokrelationship').allowBlank = true;
                    //me.lookupReference('occupationsubcategory').allowBlank = true;
                    me.lookupReference('jointgender').allowBlank = true;
                    me.lookupReference('jointdateofbirth').allowBlank = true;
                    me.lookupReference('jointnationality').allowBlank = true;
                    me.lookupReference('jointreligion').allowBlank = true;
                    me.lookupReference('jointrace').allowBlank = true;
                    me.lookupReference('jointbumiputera').allowBlank = true;

                    me.lookupReference('nokfullname').setValue('');
                    me.lookupReference('nokemail').setValue('');
                    me.lookupReference('nokmykadno').setValue('');
                    me.lookupReference('nokphoneno').setValue('');
                    //me.lookupReference('nokaddress').setValue('');
                    me.lookupReference('nokrelationship').setValue('');
                    me.lookupReference('jointgender').setValue('');
                    me.lookupReference('jointdateofbirth').setValue('');
                    me.lookupReference('jointnationality').setValue('');
                    me.lookupReference('jointreligion').setValue('');
                    me.lookupReference('jointrace').setValue('');
                    me.lookupReference('jointbumiputera').setValue('');

                    // set default value for mandantory fields
                    me.lookupReference('nationality').setValue('-');
                    me.lookupReference('gender').setValue('-');
                    me.lookupReference('dateofbirth').setValue('-');
                    me.lookupReference('maritalstatus').setValue('-');
                    me.lookupReference('bumiputera').setValue('-');
                    me.lookupReference('race').setValue('-');
                    me.lookupReference('religion').setValue('-');

                    me.lookupReference('nationality').setHidden(true);
                    me.lookupReference('gender').setHidden(true);
                    me.lookupReference('dateofbirth').setHidden(true);
                    me.lookupReference('maritalstatus').setHidden(true);
                    me.lookupReference('bumiputera').setHidden(true);
                    me.lookupReference('race').setHidden(true);
                    me.lookupReference('religion').setHidden(true);

                    me.lookupReference('occupationcategory').setHidden(true);

                }
            }else{
                // warning msg
                me.lookupReference('otcregisterform').setHidden(true);

                me.lookupReference('fullname').setValue('');
                me.lookupReference('nokfullname').setValue('');
                me.lookupReference('mykadno').setValue('');
                me.lookupReference('nokmykadno').setValue('');
                me.lookupReference('email').setValue('');

                me.lookupReference('mobile').setValue('');
                me.lookupReference('address').setValue('');
                // me.lookupReference('city', selection.data.phoneno);
                me.lookupReference('postcode').setValue('');
                // me.lookupReference('parstate', selection.data.phoneno);

                // me.lookupReference('nokfullname', selection.data.fullname);
                // me.lookupReference('nokmykadno', selection.data.mykadno);
                // me.lookupReference('nokemail', selection.data.email);
                // me.lookupReference('nokphoneno', selection.data.phoneno);
                // me.lookupReference('nokaddress', selection.data.phoneno);
                // me.lookupReference('nokphoneno', selection.data.phoneno);
                // me.lookupReference('nokaddress', selection.data.phoneno);

                me.lookupReference('partnercusid').setValue('');
                // me.lookupReference('bankaccount').setValue(selection.data.bankaccount);
                me.lookupReference('bankaccountnumber').setValue('');
                me.lookupReference('accounttype').setValue('');
                me.lookupReference('accounttypestr').setValue('');
                
                Ext.MessageBox.show({
                    title: 'Warning', buttons: Ext.MessageBox.OK, icon: Ext.MessageBox.WARNING,
                    msg: 'The selected account has already been registered'
                });
            }
        }else if (PROJECTBASE == 'ALRAJHI'){
            phoneno = selection.data.phoneno.toString();
            // me.lookupReference('casaaccountlist').store.getData;
            // var getDisplayController = this.up().up().up().up().getController();
            personalform = me.lookupReference('register-form-personal');
            // nokform = myView.getController().lookupReference('register-form-nok');
            // bankaccountinfoform = myView.getController().lookupReference('register-form-bankaccountinfo');
            // passwordform = myView.getController().lookupReference('register-form-password');
            me.lookupReference('otcregisterform').setHidden(false);
            // pinform = myView.getController().lookupReference('register-form-pin');

            me.lookupReference('fullname').setValue(selection.data.fullname);
            me.lookupReference('mykadno').setValue(selection.data.mykadno);
            //debugger;
            me.lookupReference('email').setValue(selection.data.email);

            me.lookupReference('mobile').setValue(phoneno);
            me.lookupReference('address').setValue(selection.data.line1);
            // me.lookupReference('city', selection.data.phoneno);
            me.lookupReference('postcode').setValue(selection.data.postcode);
            // me.lookupReference('parstate', selection.data.phoneno);

            // me.lookupReference('nokfullname', selection.data.fullname);
            // me.lookupReference('nokmykadno', selection.data.mykadno);
            // me.lookupReference('nokemail', selection.data.email);
            // me.lookupReference('nokphoneno', selection.data.phoneno);
            // me.lookupReference('nokaddress', selection.data.phoneno);
            // me.lookupReference('nokphoneno', selection.data.phoneno);
            // me.lookupReference('nokaddress', selection.data.phoneno);

            me.lookupReference('partnercusid').setValue(selection.data.partnercusid);
            // me.lookupReference('bankaccount').setValue(selection.data.bankaccount);
            me.lookupReference('bankaccountnumber').setValue(selection.data.accountnumber);
            me.lookupReference('accounttype').setValue(selection.data.accounttype);
            me.lookupReference('accounttypestr').setValue(selection.data.accounttypestr);
            
            // hidden field
            me.lookupReference('city').setValue('-');
            me.lookupReference('state').setValue('-');

            if(selection.data.accountstatus !=0 ){
                me.lookupReference('evidencecode').setValue('');
            }

            // check if sendiri 
            if(selection.data.accounttype == 23 || selection.data.accounttype == 24){
                me.lookupReference('nokfullname').setHidden(true);
                me.lookupReference('nokmykadno').setHidden(true);
                
                me.lookupReference('nokfullname').setValue('');
                me.lookupReference('nokmykadno').setValue('');
            }else if(selection.data.accounttype == 21 || selection.data.accounttype == 22){
                // check if joint
                me.lookupReference('nokfullname').setHidden(false);
                me.lookupReference('nokmykadno').setHidden(false);

                me.lookupReference('nokfullname').setValue(selection.data.nokfullname);
                me.lookupReference('nokmykadno').setValue(selection.data.nokmykadno);
            }
        }
        
        
 
        
    },

    // For direct population use used by ALRAJHI
    _populateRegistrationForm: function(me, data){
        phoneno = data.phoneno;
        

       
        // check if sendiri 
        if(data.accounttype == 21 || data.accounttype == 23 || data.accounttype == 24){
    
            // clear all other fields
            me.lookupReference('register-form-join').setHidden(true);
            me.lookupReference('register-form-nok').setHidden(true);
            me.lookupReference('nokmykadno').setValue('');
            me.lookupReference('nokpartyid').setValue('');
            me.lookupReference('nokfullname').setValue('');
            
            // check if joint returns, if not joint hide all nok
            if(data.searchflag != 1){
                me.lookupReference('nokfullname').setHidden(true);
                me.lookupReference('nokmykadno').setHidden(true);
                me.lookupReference('partyid').setHidden(true);
                me.lookupReference('heading').setHidden(true);

                me.lookupReference('nokfullname').setValue('');
                me.lookupReference('nokmykadno').setValue('');
                me.lookupReference('partyid').setValue('');

                // Set mandatory property to true
                me.lookupReference('nokfullname').allowBlank = true;
                me.lookupReference('nokmykadno').allowBlank = true;
            }else{
                me.lookupReference('nokfullname').setHidden(false);
                me.lookupReference('nokmykadno').setHidden(false);
                me.lookupReference('heading').setHidden(false);
                // Set mandatory property to true
                me.lookupReference('nokfullname').allowBlank = false;
                me.lookupReference('nokmykadno').allowBlank = false;
    
                me.lookupReference('mykadno').setValue(data.mykadno);
    
                me.lookupReference('nokfullname').setValue(data.nokfullname);
                me.lookupReference('nokmykadno').setValue(data.nokmykadno);

                // third field custom to show joint account info
            }
          


            //debugger;
            
            if(data.mykadno != null){
                me.lookupReference('mykadno').setValue(data.mykadno);      
            }else{
                me.lookupReference('mykadno').setValue(me.lookupReference('casasearchfields').getValue());
            }
            
      
            
           
            
        }else if(data.accounttype == 22){

            // debugger;
            if(data.joinaccount.length > 0){
            
            //data.joinaccount[0]
                // Open join page first and set values
                me.lookupReference('register-form-join').setHidden(false);
                me.lookupReference('register-form-nok').setHidden(false);
                me.lookupReference('heading').setHidden(false);

                me.lookupReference('heading').setValue(data.accountname);
                
                me.lookupReference('nokfullname').setHidden(false);
                me.lookupReference('nokmykadno').setHidden(false);
                me.lookupReference('partyid').setHidden(false);
    
                // Set mandatory property to true
                me.lookupReference('nokfullname').allowBlank = false;
                me.lookupReference('nokmykadno').allowBlank = false;
    
               // me.lookupReference('mykadno').setValue(data.mykadno);
    
                me.lookupReference('partyid').setValue(data.joinaccount[0].PartyId);
                me.lookupReference('fullname').setValue(data.joinaccount[0].Heading1);
                me.lookupReference('mykadno').setValue(data.joinaccount[0].DocumentNumber);

                me.lookupReference('nokpartyid').setValue(data.joinaccount[1].PartyId);
                me.lookupReference('nokfullname').setValue(data.joinaccount[1].Heading1);

                // me.lookupReference('nokphoneno').setValue(data.joinaccount[1].Heading1);
                // me.lookupReference('nokemail').setValue(data.joinaccount[1].Heading1);
                me.lookupReference('nokmykadno').setValue(data.joinaccount[1].DocumentNumber);
                
                // me.lookupReference('register-form-nok').setHidden(false);
                // me.lookupReference('register-form-personal').setHidden(true);
                // me.lookupReference('register-form-nok').setHidden(true);
                //me.lookupReference('register-form-personal').reset();
                //me.lookupReference('register-form-nok').reset();
                
            }else{
                // close join
                me.lookupReference('register-form-join').setHidden(true);
                me.lookupReference('register-form-nok').setHidden(true);
                me.lookupReference('heading').setHidden(true);
                
                // me.lookupReference('address').setValue('');
                // me.lookupReference('postcode').setValue('');
                // me.lookupReference('city').setValue('');
                // me.lookupReference('state').setValue('');

                me.lookupReference('nokfullname').setHidden(true);
                me.lookupReference('nokmykadno').setHidden(true);

                me.lookupReference('mykadno').setValue('');
                me.lookupReference('partyid').setValue('');
                me.lookupReference('fullname').setValue('');
                
         
                me.lookupReference('nokmykadno').setValue('');
                me.lookupReference('nokpartyid').setValue('');
                me.lookupReference('nokfullname').setValue('');
        

                // Set mandatory property to true
                me.lookupReference('nokfullname').allowBlank = true;
                me.lookupReference('nokmykadno').allowBlank = true;

                me.lookupReference('partyid').setHidden(true);
                me.lookupReference('partyid').setValue('');

            }
      
        }

         // Shared fields here
        // me.lookupReference('casaaccountlist').store.getData;
        // var getDisplayController = this.up().up().up().up().getController();
        personalform = me.lookupReference('register-form-personal');
        // nokform = myView.getController().lookupReference('register-form-nok');
        // bankaccountinfoform = myView.getController().lookupReference('register-form-bankaccountinfo');
        // passwordform = myView.getController().lookupReference('register-form-password');
        me.lookupReference('otcregisterform').setHidden(false);
        // pinform = myView.getController().lookupReference('register-form-pin');

        me.lookupReference('fullname').setValue(data.fullname);

        me.lookupReference('email').setValue(data.email);

        me.lookupReference('mobile').setValue(phoneno);
        if(data.line2){
            me.lookupReference('address').setValue(data.line1 + ', ' + data.line2);
        }else{
            me.lookupReference('address').setValue(data.line1);
        }
       
        me.lookupReference('postcode').setValue(data.postcode);
        me.lookupReference('city').setValue(data.city);
        me.lookupReference('state').setValue(data.state);

        // me.lookupReference('parstate', selection.data.phoneno);

        // me.lookupReference('nokfullname', selection.data.fullname);
        // me.lookupReference('nokmykadno', selection.data.mykadno);
        // me.lookupReference('nokemail', selection.data.email);
        // me.lookupReference('nokphoneno', selection.data.phoneno);
        // me.lookupReference('nokaddress', selection.data.phoneno);
        // me.lookupReference('nokphoneno', selection.data.phoneno);
        // me.lookupReference('nokaddress', selection.data.phoneno);

        me.lookupReference('partnercusid').setValue(data.partnercusid);

        // Alrajhi only for CRS
        me.lookupReference('partnerdata').setValue(data.partnerdata);
        me.lookupReference('branchident').setValue(data.branchident);
        
        // me.lookupReference('bankaccount').setValue(selection.data.bankaccount);
        // me.lookupReference('bankaccountnumber').setValue(data.accountnumber);
        me.lookupReference('accounttype').setValue(data.accounttype);
        // me.lookupReference('accounttypestr').setValue(data.accounttypestr);
        
        // hidden field
        // me.lookupReference('city').setValue('-');
        // me.lookupReference('state').setValue('-');
        // me.lookupReference('nationality').setValue(selection.data.nationality);
        // me.lookupReference('dateofbirth').setValue(selection.data.dateofbirth);
        // me.lookupReference('bumiputera').setValue(selection.data.bumiputera);
        // me.lookupReference('religion').setValue(selection.data.religion);
        // me.lookupReference('gender').setValue(selection.data.gender);
        // me.lookupReference('maritalstatus').setValue(selection.data.maritalstatus);
        // me.lookupReference('race').setValue(selection.data.race);


        // populate form 
        
        // const TYPE_COMPANY = 21;
        // const TYPE_COHEADING = 22;
        // const TYPE_SOLEPROPRIETORSHIP = 23;
        // const TYPE_INDIVIDUAL = 24;
    
        
    }

});
