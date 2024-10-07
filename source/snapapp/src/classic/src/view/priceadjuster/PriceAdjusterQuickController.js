Ext.define('snap.view.priceadjuster.PriceAdjusterQuickController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gridpanel-priceadjusterquickcontroller',

    onNotesRequest: function () {
        var me = this;
        snap.getApplication().sendRequest({
            hdl: 'stationservice', 'action': 'getMedicalRecord',
            stationid: me.getStationid(),
            //serviceid: serviceid,
            //appointmentid: appointmentid,
            appointmenttaskid: me.getAppointmenttaskid(),
            patientid: me.getPatientid(),
        }, 'Fetching data from server...').then(
            function(data){
                if (data.success && data.notes) {
                //var panel = Ext.getCmp('notesDisplays');
                var panel = me.lookupReference('notesDisplays');
                panel.removeAll();
                data.notes.map((x) => {
                    panel.add(me.noteTemplate(x))
                })
                return;
                }
            });
    },

    noteTemplate: function (data) {
        var returnx = {
            xtype: 'container',
            height: 200,
            //fieldStyle: 'background-color: #000000; background-image: none;',
            scrollable: true,
            items: [{
                xtype: 'container',
                flex: 1,

                items: [/*{
                xtype: 'label',
                text: 'Latest Remarks'
                },*/
                {
                    items: [{
                        xtype: 'container',
                        layout: 'hbox',
                        items: [{
                            html: '<br><hr/>',
                            flex: 1,
                            xtype: 'container',
                            layout: 'vbox',
                            scrollable: true,
                            width: 100,
                            items: [{
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'stretch'
                                },
                                defaults: {
                                    margin: ' 0 30 0 0',
                                },
                                items: [{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Note ID: ',
                                    reference: '',
                                    name: 'medicalrecordid',
                                    value: data.id,
                                    style: 'padding-right: 10px',
                                    flex: 1,
                                },
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Date Created: ',
                                    reference: 'stationid',
                                    name: 'datecreated',
                                    value: data.createdon,
                                    style: 'padding-right: 10px',
                                    flex: 1,
                                },{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Created By',
                                    reference: 'mdrName',
                                    name: 'createdby',
                                    value: data.createdbyname,
                                    style: 'padding-right: 10px',
                                    flex: 1,
                                },{
                                    xtype: 'displayfield',
                                    fieldLabel: 'Station',
                                    reference: '',
                                    name: 'station',
                                    value: data.type,
                                    style: 'padding-right: 10px',
                                    flex: 1,
                                },
                                ]
                            },
                            {
                                xtype: 'displayfield',
                                fieldLabel: 'Remarks',
                                width: '88%',
                                reference: '',
                                name: 'remark',
                                value: data.remark,
                            },
                            ],
                        },

                        ]
                    },
                    ]
                }
            ]
            }],
        }
    return returnx
    },
    // notes

})