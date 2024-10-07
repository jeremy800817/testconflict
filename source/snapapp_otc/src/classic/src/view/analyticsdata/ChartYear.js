Ext.define('snap.view.analyticsdata.ChartYear', {
    extend: 'Ext.Panel',
    xtype: 'chartyearview',


    initComponent: function() {
        var me = this;

        this.myDataStore = Ext.create('Ext.data.JsonStore', {
            fields: ['month', 'data1', 'data2', 'data3', 'data4' ],
            data: [
                { month: 'Jan', data1: 20, data2: 37, data3: 35, data4: 4 },
                { month: 'Feb', data1: 20, data2: 37, data3: 36, data4: 5 },
                { month: 'Mar', data1: 19, data2: 36, data3: 37, data4: 4 },
                { month: 'Apr', data1: 18, data2: 36, data3: 38, data4: 5 },
                { month: 'May', data1: 18, data2: 35, data3: 39, data4: 4 },
                { month: 'Jun', data1: 17, data2: 34, data3: 42, data4: 4 },
                { month: 'Jul', data1: 16, data2: 34, data3: 43, data4: 4 },
                { month: 'Aug', data1: 16, data2: 33, data3: 44, data4: 4 },
                { month: 'Sep', data1: 16, data2: 32, data3: 44, data4: 4 },
                { month: 'Oct', data1: 16, data2: 32, data3: 45, data4: 4 },
                { month: 'Nov', data1: 15, data2: 31, data3: 46, data4: 4 },
                { month: 'Dec', data1: 15, data2: 31, data3: 47, data4: 4 }
            ]
        });

        var txtDraw =  Ext.create('Ext.draw.Sprite', {
            type:'text',
            text  : 'Initial Text',
            font  : '24px Arial',
            width : 500,
            height: 100, 
            x:20,
            y:20
        },{
            type  : 'text',
            text  : 'Column Charts - Clustered Columns',
            font  : '22px Helvetica',
            width : 100,
            height: 30,
            x : 40, //the sprite x position
            y : 12  //the sprite y position
        }, {
            type: 'text',
            text: 'Data: Browser Stats 2012',
            font: '10px Helvetica',
            x: 12,
            y: 380
        }, {
            type: 'text',
            text: 'Source: http://www.w3schools.com/',
            font: '10px Helvetica',
            x: 12,
            y: 390
        });

        me.items = [{
            xtype: 'chart',
            height: 410,
            padding: '10 0 0 0',
            animate: true,
            shadow: false,
            style: 'background: #fff;',
            legend: {
                position: 'bottom',
                boxStrokeWidth: 0,
                labelFont: '12px Helvetica'
            },
            store: this.myDataStore,
            insetPadding: 40,
            sprites: [txtDraw],
            axes: [{
                type: 'numeric',
                position: 'left',
                fields: 'data1',
                grid: true,
                minimum: 0,
                label: {
                    renderer: function(v) { return v + '%'; }
                }
            }, {
                type: 'category',
                position: 'bottom',
                fields: 'month',
                grid: true,
                label: {
                    rotate: {
                        degrees: -45
                    }
                }
            }],
            series: [{
                type: 'bar',
                axis: 'left',
                title: [ 'IE', 'Firefox', 'Chrome', 'Safari' ],
                xField: 'month',
                yField: [ 'data1', 'data2', 'data3', 'data4' ],
                style: {
                    opacity: 0.80
                },
                highlight: {
                    fill: '#000',
                    'stroke-width': 1,
                    stroke: '#000'
                },
                tips: {
                    trackMouse: true,
                    style: 'background: #FFF',
                    height: 20,
                    renderer: function(storeItem, item) {
                        var browser = item.series.title[Ext.Array.indexOf(item.series.yField, item.yField)];
                        this.setTitle(browser + ' for ' + storeItem.get('month') + ': ' + storeItem.get(item.yField) + '%');
                    }
                }
            }]
        }];

        this.callParent();
    }
});