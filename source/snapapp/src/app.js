/*
 * This file launches the application by asking Ext JS to create
 * and launch() the Application class.
 */
 // JS Code
Ext.Ajax.on('requestexception', function(connection,response) {
    if ((Ext.MessageBox).isComponent){
        msgbox = Ext.MessageBox;
    }else{
        msgbox = Ext.Msg;
    }
    if(401 == response.status) {
        // The script found the user is no longer logged in
        // Here we would redirect back to the login page
        msgbox.alert('Session Expired', 'Your current session has expired.  Please relogin to proceed', 
            function(){
                //window.location.reload(true);
                window.location.href = window.location.origin;
            });
    } else if(403 == response.status) {
        msgbox.alert('Forbidden', 'You are unauthorised to perform the requested action.  Please check with your administrator'); 
    } else if(406 == response.status) {
        msgbox.alert('Action not found', 'System unable to proceed with your actions'); 
    }
});

Ext.application({
    extend: 'snap.Application',

    name: 'snap',
    // The name of the initial view to create.
    mainView: 'snap.view.main.Main',
    requires: [
        // This will automatically load all classes in the snap namespace
        // so that application classes do not need to require each other.
        'snap.*'
    ],


    /**
     * Check if the user has permission for the particular operation.
     * @param  {string}  permissionString  The permission string that needs to be evaluated
     * @return {Boolean}                 True if the user has permission.  False otherwise.
     */
    hasPermission: function(permissionString) {
        var parts = permissionString.split('/');
        var comparator = this.permissions ? this.permissions : (_rightsInfo.permission ? _rightsInfo.permission : {});
        for (index = 1; index < parts.length; ++index) {
            if(comparator[parts[index]]) {
                if( 1 == comparator[parts[index]]) {
                    return true;
                }
                else comparator = comparator[parts[index]];
            } else return false;
        }
        return false;
    },

    /**
     * This is a utility method that helps in quickly constructing a request to be send over to the server side for processing.
     * By using this method to send request, the programmer does not need to remember all the variables that needs to be set as
     * standard.  Instead everything is predefined properly ready for use.
     * 
     * @param {Object} reqParams         The parameters to be sent over to the server.  Usually must include the hdl query.
     * @param {String/Func} callback     A string or function to be called when the server's response arrives.  Same parameters as DSCallBack.
     * @param {Object} clientContext     The client context that will be returned over in the dsResponse for use.
     * @param {String} promptText        Text when showing prompt. Optional.  Empty string will indicate no prompt.
     * @param {String} httpMethod        The method of posting to the server.  Valid values are 'GET' or 'POST'.  Defaults to 'POST'
     * @param {Boolean} handleErrors     Whether the function will handle any errors or error handling is left for the smartclient API.  Default to false.
     * @param {Boolean} rawResults       Determines if we will be expecting raw results or the json evaluated results.
     */
    sendRequest: function( reqParams, promptText, httpMethod, handleErrors, rawResults, syncronousRequest) {
        var showPrompt = false;
        var defaultParams = { 
            url: location.href, //'index.php', 
            // showPrompt: true, 
            useSimpleHttp: true,
            method : "POST",
            timeout : 18000,
            async : true,
            params: reqParams
         };
         if (typeof promptText == "string") {
            showPrompt = true;
            if (promptText.length == 0) promptText = 'Please wait';
            // Ext.getBody().mask(promptText);
            if ((Ext.MessageBox).isComponent){
                Ext.MessageBox.show({
                    msg: promptText,
                    progressText: 'Saving...',
                    width: 300,
                    wait: {
                        interval: 200
                    }
                });
            }else{
                Ext.Msg.show({
                    title: promptText,
                    message: 'Saving...',
                    width: 300,
                    wait: {
                        interval: 200
                    }
                })
            }
         }
         if( httpMethod ) defaultParams.httpMethod = httpMethod;
         if( syncronousRequest ) defaultParams.async = false;


         return new Ext.Promise(function(fulfilled, rejected) {
             //Configure the success / failure settings.
            if ((Ext.MessageBox).isComponent){
                msgbox = Ext.MessageBox;
            }else{
                msgbox = Ext.Msg;
            }
            defaultParams.success = function(response, opts) {
                if( showPrompt) {
                    //Ext.getBody().unmask();
                    msgbox.hide();
                }
                if(rawResults) {
                    fulfilled(response, true);
                } else {
                    console.log('checking response in success return.... ' + response);
                    var jsonResponse = Ext.decode(response.responseText.trim());
                    if(jsonResponse.success ) fulfilled(jsonResponse, true);
                    else if(handleErrors) fulfilled(jsonResponse, false);
                    else msgbox.alert('Error', jsonResponse.errors || jsonResponse.errorMessage, function(){});
                }
            };
            defaultParams.failure = function(response, opts) {
                if( showPrompt) {
                    // Ext.getBody().unmask();
                    msgbox.close()
                }
                console.log('server-side failure with status code ' + response.status);
            };
            Ext.Ajax.request(defaultParams)
            /*.then(function(response){
                if( showPrompt) {
                    //Ext.getBody().unmask();
                    Ext.MessageBox.hide();
                }
                if(rawResults) fulfilled(response, true);
                else {
                    var jsonResponse = Ext.decode(response.responseText.trim());
                    if(jsonResponse.success ) fulfilled(jsonResponse, true);
                    else if(handleErrors) fulfilled(jsonResponse, false);
                    else Ext.MessageBox.alert('Error', jsonResponse.errors, function(){});
                }
            //}).always(function() {
            }).otherwise(function(reason){
                if( showPrompt) {
                    // Ext.getBody().unmask();
                    Ext.MessageBox.hide();
                }
                rejected(reason);
            })*/;
         });
    }
});

Ext.define('snap.global.Vars', {
    singleton: true,
    showNotificationTab: false,
    notifications: []
});