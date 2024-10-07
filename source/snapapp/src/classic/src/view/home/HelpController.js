Ext.define('snap.view.home.HelpController', {
    extend: 'snap.view.gridpanel.BaseController',
    alias: 'controller.help-help',

    init: function(view) {
        console.log(view,"ASD")
        data = {
            'html': `
                <div class="owl-carousel owl-theme">
                    <div class="item"><h4>1</h4></div>
                    <div class="item"><h4>2</h4></div>
                    <div class="item"><h4>3</h4></div>
                    <div class="item"><h4>4</h4></div>
                    <div class="item"><h4>5</h4></div>
                    <div class="item"><h4>6</h4></div>
                    <div class="item"><h4>7</h4></div>
                    <div class="item"><h4>8</h4></div>
                    <div class="item"><h4>9</h4></div>
                    <div class="item"><h4>10</h4></div>
                    <div class="item"><h4>11</h4></div>
                    <div class="item"><h4>12</h4></div>
                    <div class="item"><h4>13</h4></div>
                    <div class="item"><h4>14</h4></div>
                    <div class="item"><h4>15</h4></div>
                </div>
                
                <link rel="stylesheet" href="./src/resources/js/assets/owl.theme.default.min.css">
                <link rel="stylesheet" href="./src/resources/js/assets/animate.css">
                `
        }
        // <script src="./src/resources/js/jquery-3.6.0.js"></script>
        // <script src="./src/resources/js/jquery-1.12.4.js"></script>
        // <script src="./src/resources/js/owl.carousel.min.js"></script>
        _this = this
        let das = new Promise(function(xresolver, rejection){
            view.lookupReference('sliderhtml').setHtml('<div class="owl-carousel owl-theme"><div class="item"><img src="src/resources/images/user_guide/0001.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0002.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0003.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0004.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0005.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0006.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0007.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0008.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0009.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0010.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0011.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0012.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0013.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0014.jpg"></div><div class="item"><img src="src/resources/images/user_guide/0015.jpg"></div></div>')
            xresolver('ok')
            // snap.getApplication().sendRequest({ hdl: 'announcement', action: 'getSliders'})
            // .then(function(data){
            //     if(data.success) {
            //             // arr = data.data
            //             // html_data = _this.constructslider(arr);
            //             // view.lookupReference('sliderhtml').setHtml(html_data)
                     
            //         }
            //     })
        }); 
        das.then((value)=>{
            if (value == 'ok'){
                _this.initOwlCarousel();
            }
        })
        // await das
        // console.log(asdasd, 'adsasdasdasd');
    },

    initOwlCarousel: async function (a, b) {
        $(document).ready(function(){
            jQuery('.owl-carousel').owlCarousel({
                loop:true,
                margin:10,
                nav:true,
                pagination: true,
                autoplay:false,
                autoplayTimeout:5000,
                autoplayHoverPause:true,
                animateOut: 'animate__slideOutRight',
                navText: ["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
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