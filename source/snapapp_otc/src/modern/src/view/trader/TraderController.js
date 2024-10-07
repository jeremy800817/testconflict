// Ext.Loader.setConfig({
// 	enabled: true,
// 	paths: {
// 		'Ext.ux.WebSocket': './bower_components/ext.ux.websocket/WebSocket.js' ,
// 		'Ext.ux.WebSocketManager': './bower_components/ext.ux.websocket/WebSocketManager.js'
// 	}
// });
// Ext.Loader.setConfig({
// 	enabled: true
// });
// Ext.require(['Ext.ux.WebSocket', 'Ext.ux.WebSocketManager']);




// // A 'stop' event is sent from the server
// // 'data' has 'cmd' and 'msg' fields
// websocket.on ('stop', function (data) {
// 	console.log ('Command: ' + data.cmd);
// 	console.log ('Message: ' + data.msg);
// });
// websocket.on('intlX', function(data){
//     console.log("GOTTTT DATA")
// })
Ext.define('snap.view.trader.TraderController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.trader-trader',

    model:{
        datax: null
    },

    // plugins: ['Ext.ux.WebSocket', 'Ext.ux.WebSocketManager'],
    // require: ['Ext.ux.WebSocket', 'Ext.ux.WebSocketManager'],
     

    afterRender: function(view) {
        this.source = new Ext.drag.Source({
            element: view.el.down('.simple-source'),
            constrain: view.body,

            listeners: {
                dragmove: function(source, info) {
                    var pos = info.element.current,
                        html;

                    html = Ext.String.format(
                        'X: {0}<br>Y: {1}',
                        Ext.Number.roundToPrecision(pos.x, 2),
                        Ext.Number.roundToPrecision(pos.y, 2)
                    );

                    source.getElement().setHtml(html);
                },

                dragend: function(source) {
                    source.getElement().setHtml('Drag Me!');
                }
            }
        });
    },

    destroy: function() {
        this.source = Ext.destroy(this.source);

        this.callParent();
    }
});