Ext.define('snap.view.home.AboutUs', {
    extend: 'Ext.panel.Panel',
    xtype: 'aboutusview',
  
    requires: [
        'Ext.container.Container',
        //'Ext.ux.GMapPanel',
        //'Ext.ux.google.Map',
    ],
  
  //   anchor : '100% -1',
  
      //anchor : '100% -1',
      profiles: {
        classic: {
            panel1Flex: 1,
            panelHeight: 100,
            panel2Flex: 2
        },
        neptune: {
            panel1Flex: 1,
            panelHeight: 100,
            panel2Flex: 2
        },
        graphite: {
            panel1Flex: 2,
            panelHeight: 110,
            panel2Flex: 3
        },
        'classic-material': {
            panel1Flex: 2,
            panelHeight: 110,
            panel2Flex: 3
        }
      },
      width: '100%',
      height: '100%',
      cls: Ext.baseCSSPrefix + 'shadow',
  
      layout: {
          type: 'vbox',
          pack: 'start',
          align: 'stretch'
      },
      scrollable:true,
      bodyPadding: 10,
  
      defaults: {
          frame: true,
          // bodyPadding: 10
      },
     
      items: [
          /*
                  {
                      xtype : 'displayfield',
                      width : '99%',
                      padding: '0 1 0 1',
                      value: '<p><span style="font-size:16px;"><strong>Ace Capital Growth Sdn.Bhd.</strong> (880690-K)</span></p>'+
                      '<p><span style="font-size:16px;">No 19-1, Jalan USJ 10/1D,<br> 47620 Subang Jaya,<br> Selangor, Malaysia</span></p>'+
                      '<p><span style="font-size:16px;">Email: enquiry@ace2u.com<br> Tel: +603-8081 7198<br> Fax: +603-8081 7199</span></p>' 
                      //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                      
                  },
  
                  {
                      xtype : 'displayfield',
                      width : '99%',
                      padding: '0 1 0 1',
                      value: '<iframe allowfullscreen="" frameborder="0"  width="100%" height="400px" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1772.1874508946128!2d101.58341806201452!3d3.0476830215350645!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc4cc4f4441b67%3A0x107e988b4ecf7b54!2sAce+Capital+Growth+Sdn+Bhd!5e0!3m2!1sen!2smy!4v1482508800152" style="border:0" ></iframe>'
               
                      //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                      
                  },
                 */ 
                  {
                      style: {
                          borderColor: '#ffffff',
                      },
                      layout: {
                          type: 'hbox',
                          pack: 'start',
                          align: 'stretch'
                      },
                      items: [
                          
                          {
                              xtype : 'displayfield',
                              margin : '-8px 10 10 10',
                              padding: '0 10 0 10',
  
                              value: '<h5 style=" width:100%;line-height: normal;overflow: inherit; margin:0px 0 20px; font-size: 16px;color:#24333f;"><span style="background:#fff;position: relative;top: 10px;">Contact Us</span></h5><hr class="hr-title hr-left" style="margin-right: 30%";><p><span style="font-size:16px;"><strong>Ace Capital Growth Sdn.Bhd.</strong> (880690-K)</span></p><br>'+
                              '<p><span style="font-size:16px;">No 19-1, Jalan USJ 10/1D,</span></p>' +
                              '<p><span style="font-size:16px;">47620 Subang Jaya,</span></p>' +
                              '<p><span style="font-size:16px;">Selangor, Malaysia</span></p>'+
                              '<p style="margin-top: 45px"><span style="font-size:16px;"><i class="fa fa-envelope" style="padding-right: 5px;"></i><a href=mailto:enquiry@ace2u.com class=med>enquiry@ace2u.com</a></span></p>' +
                              '<p><span style="font-size:16px;"><i class="fa fa-phone" style="padding-right: 5px;"></i> <a href=tel:603-8081 7198 class=med>+603-8081 7198</a></span></p>' +
                              '<p><span style="font-size:16px;"><i class="fa fa-fax" style="padding-right: 5px;"></i> <a href=fax:603-8081 7198 class=med>+603-8081 7199</a></span></p>' 
                              //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                              
                          },                        
                          {
                              xtype : 'displayfield',
                              width : '100%',
                              padding: '0 10 0 10',
  
                              // style: 'margin-top:33px;',
                              value: '<iframe allowfullscreen="" frameborder="0"  width="100%" height="400px" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7946.604151708347!2d101.57885756588574!3d3.0488540867231912!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc4cc4f4441b67%3A0x107e988b4ecf7b54!2sAce%20Capital%20Growth%20Sdn%20Bhd!5e0!3m2!1sen!2smy!4v1623671163441!5m2!1sen!2smy" style="border:0" ></iframe>'
                       
                              //style: "content: 'OR';display: inline-block;padding: 3px 5px 3px 0;padding-top: 3px;padding-right: 5px;padding-bottom: 3px;padding-left: 0px;background-color: #ffffff;font-size: 12px;font-weight: bold;text-transform: uppercase;color: #BBC3CE;letter-spacing: 1px; position: absolute;top: -0.6em;left: 0;",
                              
                          },
                         
                      ]
                  }
                
                  
      ]
  });
  