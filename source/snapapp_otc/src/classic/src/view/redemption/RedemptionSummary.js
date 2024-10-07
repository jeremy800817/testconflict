Ext.define('snap.view.redemption.RedemptionSummary', {
    extend: 'Ext.panel.Panel',
    xtype: 'redemptionsummary',
    title: '',
    requires: [
        'snap.store.Redemption',
        'snap.model.Redemption',
        'snap.view.redemption.RedemptionController',
        'snap.view.redemption.RedemptionModel',

    ],
    //permissionRoot: '/root/mbb/redemption',
    //store: { type: 'RedemptionStatusCounts' },
    listeners: {
        afterrender: function () {
            
            // Get the function type
            originType = this.type;

            redemptionpendingcount = originType + "redemptionpendingcount";
            redemptionconfirmedcount = originType + "redemptionconfirmedcount";
            redemptioncompletedcount = originType + "redemptioncompletedcount";
            redemptionfaileddeliverycount = originType + "redemptionfaileddeliverycount";
            redemptiondeliverycount = originType + "redemptiondeliverycount";
            redemptioncancelledcount = originType + "redemptioncancelledcount";
            redemptionfailedapicount = originType + "redemptionfailedapicount";

            var panel = this;

            //date = data.createdon.date;
            //filtereddate = Ext.Date.format(new Date(date), "Y-m-d");
            panel.removeAll();
            
            panel.add(
                {
                    html: '<div style="background:#007bc5;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Pending : <span id="'+  redemptionpendingcount +'">0</span>&nbsp;</div>',
                    flex: 1,
                    listeners: {
                        render: function(c) {
                          Ext.create('Ext.tip.ToolTip', {
                            target: c.getEl(),
                            //html: c.tip 
                            html:  '<div><span span style="color:#ffffff;font-weight:900;">Pending</span> - Redemption is pending for the next action &nbsp;</div>',
                            shadow: false,
                            maxHeight: 400,
                            
                          });
                        }
                    }
                }, {
                    html: '&nbsp;',
                }, {
                    html: '<div style="background:#FFA500;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Confirmed : <span id="'+ redemptionconfirmedcount +'">0</span>&nbsp;</div>',
                    flex: 1,
                    listeners: {
                        render: function(c) {
                          Ext.create('Ext.tip.ToolTip', {
                            target: c.getEl(),
                            //html: c.tip 
                            html:  '<div><span span style="color:#ffffff;font-weight:900;">Confirmed</span> - Redemption request is confirmed for Delivery &nbsp;</div>',        
                            shadow: false,
                            maxHeight: 400,
                            
                          });
                        }
                    }
                }, {
                    html: '&nbsp;',
                }
                , {
                    html: '<div style="background:#0ead30;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Completed : <span id="' + redemptioncompletedcount +'">0</span>&nbsp;</div>',
                    flex: 1,
                    listeners: {
                        render: function(c) {
                          Ext.create('Ext.tip.ToolTip', {
                            target: c.getEl(),
                            //html: c.tip 
                            html:  '<div><span span style="color:#ffffff;font-weight:900;">Completed</span> -Redemption is successful or delivered &nbsp;</div>',
                            shadow: false,
                            maxHeight: 400,
                            
                          });
                        }
                    }
                }, {
                    html: '&nbsp;',
                }
                , {
                    html: '<div style="background:#F42A12;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Failed Delivery : <span id="'+ redemptionfaileddeliverycount +'">0</span>&nbsp;</div>',
                    flex: 1,
                    listeners: {
                        render: function(c) {
                          Ext.create('Ext.tip.ToolTip', {
                            target: c.getEl(),
                            //html: c.tip 
                            html:  '<div><span span style="color:#ffffff;font-weight:900;">Failed Delivery </span> - Logistic/Delivery for redemption failed &nbsp;</div>',
                            shadow: false,
                            maxHeight: 400,
                            
                          });
                        }
                    }
                }, {
                    html: '&nbsp;',
                }, {
                    html: '<div style="background:#6C3483;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Process Delivery : <span id="'+ redemptiondeliverycount +'">0</span>&nbsp;</div>',
                    flex: 1,
                    listeners: {
                        render: function(c) {
                          Ext.create('Ext.tip.ToolTip', {
                            target: c.getEl(),
                            //html: c.tip 
                            html:  '<div><span span style="color:#ffffff;font-weight:900;">Process Delivery</span> - Redemption is being processed for delivery &nbsp;</div>',
                            shadow: false,
                            maxHeight: 400,
                            
                          });
                        }
                    }
                }, {
                    html: '&nbsp;',
                }, {
                    html: '<div style="background:#6E2C00;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Cancelled : <span id="'+ redemptioncancelledcount +'">0</span>&nbsp;</div>',
                    flex: 1,
                    listeners: {
                        render: function(c) {
                          Ext.create('Ext.tip.ToolTip', {
                            target: c.getEl(),
                            //html: c.tip 
                            html:  '<div><span span style="color:#ffffff;font-weight:900;">Cancelled</span> - Redemption was cancelled by merchant &nbsp;</div>',
                            shadow: false,
                            maxHeight: 400,
                            
                          });
                        }
                    }
                }, {
                    html: '<div style="background:#BF0000;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Failed : <span id="'+ redemptionfailedapicount +'">0</span>&nbsp;</div>',
                    flex: 1,
                    listeners: {
                        render: function(c) {
                          Ext.create('Ext.tip.ToolTip', {
                            target: c.getEl(),
                            html:  '<div><span span style="color:#ffffff;font-weight:900;">Failed</span> - Branch for redemption failed &nbsp;</div>',
                            shadow: false,
                            maxHeight: 400,
                            
                          });
                        }
                    }
                }, {
                    html: '&nbsp;',
                }
            );
       

            snap.getApplication().sendRequest({
                hdl: 'redemption', action: 'getSummary',
                origintype : originType,
            }, 'Fetching data from server....').then(
                function (data) {                    
                    if (data.success) {
                       
                        Ext.get(redemptionpendingcount).dom.innerHTML  = data.pendingstatuscount;
                        Ext.get(redemptionconfirmedcount).dom.innerHTML  = data.confirmedstatuscount;
                        Ext.get(redemptioncompletedcount).dom.innerHTML  = data.completedstatuscount;
                        Ext.get(redemptionfaileddeliverycount).dom.innerHTML  = data.redemptionfaileddeliverycount;			
                        Ext.get(redemptiondeliverycount).dom.innerHTML  = data.deliverystatuscount;			
                        Ext.get(redemptioncancelledcount).dom.innerHTML  = data.cancelledstatus;
                        Ext.get(redemptionfailedapicount).dom.innerHTML  = data.failedstatuscount;
                    }
                })
        }
    },
    controller: 'redemption-redemption',
    width: '100%',
    //height: 150,
    layout: {
        type: 'hbox',
    },
    defaults: {
        bodyStyle: 'padding:0px;margin-top:10px',
    },
 
    items: [
       
    ],

});

xTemplate = () => {
    var returnx = [ {
        html: '<div style="background:#007bc5;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Pending : <span id="'+  vmr.get('redemptionpendingcount') +'">0</span>&nbsp;</div>',
        flex: 1,
        listeners: {
            render: function(c) {
              Ext.create('Ext.tip.ToolTip', {
                target: c.getEl(),
                //html: c.tip 
                html:  '<div><span span style="color:#ffffff;font-weight:900;">Pending</span> - Redemption is pending for the next action &nbsp;</div>',
                shadow: false,
                maxHeight: 400,
                
              });
            }
        }
    }, {
        html: '&nbsp;',
    }, {
        html: '<div style="background:#FFA500;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Confirmed : <span id="'+ vmr.get('redemptionconfirmedcount') +'">0</span>&nbsp;</div>',
        flex: 1,
        listeners: {
            render: function(c) {
              Ext.create('Ext.tip.ToolTip', {
                target: c.getEl(),
                //html: c.tip 
                html:  '<div><span span style="color:#ffffff;font-weight:900;">Confirmed</span> - Redemption request is confirmed for Delivery &nbsp;</div>',        
                shadow: false,
                maxHeight: 400,
                
              });
            }
        }
    }, {
        html: '&nbsp;',
    }
    , {
        html: '<div style="background:#0ead30;height:40px;padding-top:6px;color:#ffffff;margin-left:2px;text-align:center">Completed : <span id="'+ vmr.get('redemptioncompletedcount') +'">0</span>&nbsp;</div>',
        flex: 1,
        listeners: {
            render: function(c) {
              Ext.create('Ext.tip.ToolTip', {
                target: c.getEl(),
                //html: c.tip 
                html:  '<div><span span style="color:#ffffff;font-weight:900;">Completed</span> -Redemption is successful or delivered &nbsp;</div>',
                shadow: false,
                maxHeight: 400,
                
              });
            }
        }
    }, {
        html: '&nbsp;',
    }
    , {
        html: '<div style="background:#F42A12;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Failed Delivery : <span id="'+ vmr.get('redemptionfaileddeliverycount') +'">0</span>&nbsp;</div>',
        flex: 1,
        listeners: {
            render: function(c) {
              Ext.create('Ext.tip.ToolTip', {
                target: c.getEl(),
                //html: c.tip 
                html:  '<div><span span style="color:#ffffff;font-weight:900;">Failed Delivery </span> - Logistic/Delivery for redemption failed &nbsp;</div>',
                shadow: false,
                maxHeight: 400,
                
              });
            }
        }
    }, {
        html: '&nbsp;',
    }, {
        html: '<div style="background:#6C3483;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Process Delivery : <span id="'+ vmr.get('redemptiondeliverycount') +'">0</span>&nbsp;</div>',
        flex: 1,
        listeners: {
            render: function(c) {
              Ext.create('Ext.tip.ToolTip', {
                target: c.getEl(),
                //html: c.tip 
                html:  '<div><span span style="color:#ffffff;font-weight:900;">Process Delivery</span> - Redemption is being processed for delivery &nbsp;</div>',
                shadow: false,
                maxHeight: 400,
                
              });
            }
        }
    }, {
        html: '&nbsp;',
    }, {
        html: '<div style="background:#6E2C00;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Cancelled : <span id="'+ vmr.get('redemptioncancelledcount') +'">0</span>&nbsp;</div>',
        flex: 1,
        listeners: {
            render: function(c) {
              Ext.create('Ext.tip.ToolTip', {
                target: c.getEl(),
                //html: c.tip 
                html:  '<div><span span style="color:#ffffff;font-weight:900;">Cancelled</span> - Redemption was cancelled by merchant &nbsp;</div>',
                shadow: false,
                maxHeight: 400,
                
              });
            }
        }
    }, {
        html: '<div style="background:#BF0000;height:40px;color:#ffffff;padding-top:6px;margin-left:2px;text-align:center">Failed : <span id="'+ vmr.get('redemptionfailedapicount') +'">0</span>&nbsp;</div>',
        flex: 1,
        listeners: {
            render: function(c) {
              Ext.create('Ext.tip.ToolTip', {
                target: c.getEl(),
                html:  '<div><span span style="color:#ffffff;font-weight:900;">Failed</span> - Branch for redemption failed &nbsp;</div>',
                shadow: false,
                maxHeight: 400,
                
              });
            }
        }
    }, {
        html: '&nbsp;',
    }]
    return returnx
}