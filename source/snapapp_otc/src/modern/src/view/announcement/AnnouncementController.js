Ext.define('snap.view.announcement.AnnouncementController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.announcement-announcement',


    onPreLoadForm: function( formView, form, record, asyncLoadCallback) {
        var me = this;
        snap.getApplication().sendRequest({
            hdl: 'announcement', 'action': 'fillform', id: ((record && record.data) ? record.data.id : 0)
        }, 'Fetching data from server....').then(
        function(data) {
            if(data.success) {

                //formView.getController().lookupReference('type').getStore().loadData(data.type);
                formView.getController().lookupReference('attachmentPicture').setValue(data.picture);
                /*
                formView.getController().lookupReference('leveltagid').getStore().loadData(data.pricecharges);
                formView.getController().lookupReference('nokrelationship').getStore().loadData(data.relationship);
                formView.getController().lookupReference('gender').getStore().loadData(data.gender);
                formView.getController().lookupReference('maritalstatus').getStore().loadData(data.marital);
                formView.getController().lookupReference('attachmentPicture').setValue(data.picture);
                formView.getController().lookupReference('ethnic').getStore().loadData(data.ethnic);
                formView.getController().lookupReference('nokgender').getStore().loadData(data.nokgender);
                //formView.getController().lookupReference('smoke').getStore().loadData(data.smoke);
                formView.getController().lookupReference('cardiodoc').getStore().loadData(data.cardiodoc);

                record.data.cardio = false;
                record.data.gp = false;

                if(record.data.type == data.patienttype[2].code) {
                    record.data.gp = true;
                    record.data.cardio = true;
                }
                else if(record.data.type == data.patienttype[0].code) {
                    record.data.gp = true;
                }
                else if(record.data.type == data.patienttype[1].code) {
                    record.data.cardio = true;
                }
            */
            }
            
            if(Ext.isFunction(asyncLoadCallback)) asyncLoadCallback(record);
            else {
                record = Ext.apply(record, data.record);
                form.loadRecord(record);
            }
        });
        return false;
    },

    onPostLoadEmptyForm: function( formView, form) {
        this.onPreLoadForm(formView, form, Ext.create('snap.model.Announcement', {id: 0}), null);
    },




    onPreLoadViewDetail: function(record, displayCallback) {
        snap.getApplication().sendRequest({ hdl: 'announcement', action: 'detailview', id: record.data.id})
        .then(function(data){
            if(data.success) {
                displayCallback(data.record);
            }
        })
        return false;
    },

    // init: function(view) {
    //     console.log(view,"ASD")
    //     data = {
    //         'html': `
    //             <div class="owl-carousel owl-theme">
    //                 <div class="item"><h4>1</h4></div>
    //                 <div class="item"><h4>2</h4></div>
    //                 <div class="item"><h4>3</h4></div>
    //                 <div class="item"><h4>4</h4></div>
    //                 <div class="item"><h4>5</h4></div>
    //                 <div class="item"><h4>6</h4></div>
    //                 <div class="item"><h4>7</h4></div>
    //                 <div class="item"><h4>8</h4></div>
    //                 <div class="item"><h4>9</h4></div>
    //                 <div class="item"><h4>10</h4></div>
    //                 <div class="item"><h4>11</h4></div>
    //                 <div class="item"><h4>12</h4></div>
    //             </div>
                
    //             <link rel="stylesheet" href="./src/resources/js/assets/owl.theme.default.min.css">
    //             <link rel="stylesheet" href="./src/resources/js/assets/animate.css">
    //             `
    //     }
    //     // <script src="./src/resources/js/jquery-3.6.0.js"></script>
    //     // <script src="./src/resources/js/jquery-1.12.4.js"></script>
    //     // <script src="./src/resources/js/owl.carousel.min.js"></script>
    //     _this = this
    //     let das = new Promise(function(xresolver, rejection){
    //         snap.getApplication().sendRequest({ hdl: 'announcement', action: 'getSliders'})
    //         .then(function(data){
    //             if(data.success) {
    //                     // arr = data.data
    //                     // html_data = _this.constructslider(arr);
    //                     // view.lookupReference('sliderhtml').setHtml(html_data)
    //                     view.lookupReference('sliderhtml').setHtml(data.html)
    //                     xresolver('ok')
    //                 }
    //             })
    //     }); 
    //     das.then((value)=>{
    //         if (value == 'ok'){
    //             _this.initOwlCarousel();
    //         }
    //     })
    //     // await das
    //     // console.log(asdasd, 'adsasdasdasd');
    // },

    initOwlCarousel: async function (a, b) {
        $(document).ready(function(){
            jQuery('.owl-carousel').owlCarousel({
                loop:true,
                margin:10,
                nav:false,
                pagination: true,
                autoplay:true,
                autoplayTimeout:5000,
                autoplayHoverPause:true,
                animateOut: 'animate__slideOutRight',
                
                responsive:{
                    0:{
                        items:1
                    },
                    600:{
                        items:1
                    },
                    1000:{
                        items:1
                    }
                }
            })
        })
    },

    // constructslider: function (arr){
    //     html = `
    //             <div class="owl-carousel owl-theme">
    //             `;
    //     arr.map((list) => {
    //         html += `
    //                 <div class="item">
    //                 `+
    //                 list.picture
    //                 +`
    //                 </div>
    //         `
    //     })
    //     html += `</div>`;
    //     return html;
    // }
});

// Ext.onReady(function() {
//     // app has been loaded and is ready to use
//     jQuery('.owl-carousel').owlCarousel({
//         loop:true,
//         margin:10,
//         nav:true,
//         responsive:{
//             0:{
//                 items:1
//             },
//             600:{
//                 items:3
//             },
//             1000:{
//                 items:5
//             }
//         }
//     })
// });