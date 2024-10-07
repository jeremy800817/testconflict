<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/login.php');  ?>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $lang['TITLE_DASHBOARD_REGISTERED'];?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/style-mcash.css">
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/script.js"></script>
<script src="js/tooltip-script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<!-- End of Async Drift Code -->
<script>
$(document).ready(function(){
         $("#close").hide();
         $("#open").show();
         $("#logoff").hide();
         $("#chart2").hide();
         $("#chart3").hide();
        var today = new Date();
        var time = new Date();
        var todaytime = today.getHours() +":"+ today.getMinutes()+":"+today.getSeconds();
        var js_variable  = '<?php echo $_SESSION['token'];?>';
        var runlive = '<?php echo $livenow ?>';
      //var runlive = 'No';
        var tradenow ='<?php echo  $_SESSION['tradenow']; ?>';
        var databuy=[];
        var datasell=[];
        var todaytime1=[];
        var countdown=0;
        var par_code = '<?php echo $_SESSION['par_code']; ?>'
        var login = '<?php echo $_SESSION['login'];?>';
      //  var host = 'wss://gopayzuatapi.ace2u.com/mygtp.php?version=1.0my&action=pricestream&merchant_id='+ par_code +'&access_token=' + js_variable ;
     var sockethost ='<?php echo $socket ?>'
      var host = sockethost + par_code +'&access_token=' + js_variable ;
      var socket = new WebSocket(host);
        var newsellprice;
        var newbuyprice;
        var previousbuy;
        var previoussell;
        var uuid;

        var options = {
          series: [{
          data: databuy.slice()
        }],
          chart: {
          id: 'realtime',
          height: 100,
          type: 'line',
          animations: {
            enabled: true,
            easing: 'linear',
            dynamicAnimation: {
              speed: 1000
            }
          },
          toolbar: {
            show: false
          },
          zoom: {
            enabled: false
          }
        },
        dataLabels: {
          enabled: false
        },
        floating: true,
  axisTicks: {
    show: false
  },
  axisBorder: {
    show: false
  },
  labels: {
    show: false
  },
        grid: {
      show: false,      // you can either change hear to disable all grids
    },
        stroke: {
          curve: 'smooth'
        },
        markers: {
          size: 0
        },
        xaxis: {
          labels: {
    show: false
  },
          categories: 
           todaytime1
        },
        yaxis: {
          show:false
       //   title: {
        //    text: 'Price (RM)'
        //  }
        },
       
        };

        var chart = new ApexCharts(document.querySelector("#smallchart"), options);
        chart.render();
       
        if (!tradenow) {
              $("#open").hide();
        $("#close").show();
        $("#logoff").hide();
        $("#logon").hide();
          
        }else{$("#open").show();
        $("#close").hide();
       // $("#logoff").show();
        $("#logon").show();
        
        }
          
        socket.onmessage = function(e) {
            // console.log(e.data);
            var myObj = JSON.parse(e.data);
            var xx = new Date();
            var t = xx.getHours();
            var minutes = xx.getMinutes();
            var todaytime2 = xx.getMinutes() + ":" + xx.getSeconds();
            customerbuy = Number(myObj.data[0].companysell).toFixed(2);
            customersell = Number(myObj.data[0].companybuy).toFixed(2);
            uuid =  myObj.data[0].uuid;

            newbuyprice = customerbuy;
            newsellprice = customersell;
            var buycount = databuy.push(newbuyprice);
            countdown+=1;
            todaytime1.push(todaytime2);
           if (countdown > 30) {
          databuy.shift();
          todaytime1.shift();
          countdown -=1;
          }
         



            if (t <= 8) {
              $("#open").hide();
        $("#close").show();
        $("#logoff").hide();
        $("#logon").hide();
          if(t == 8 && minutes > 29 ){
         
        $("#open").show();
        $("#close").hide();
       // $("#logoff").show();
        $("#logon").show();
        }
        }
        //rootarrow
          
                   
            if(previousbuy > newbuyprice )
            {
            document.getElementById("rootbuy").style.color = "red";
            document.getElementById('rootbuy').innerHTML ="&#8595; " + customerbuy ;
           
            }else{ 
                document.getElementById("rootbuy").style.color = "green";
            document.getElementById('rootbuy').innerHTML = "&#8593; " + customerbuy;

            }
            if(previoussell > newsellprice )
            {
            document.getElementById("rootsell").style.color = "red";
            document.getElementById('rootsell').innerHTML = "&#8595; " + customersell;
            }else{
                document.getElementById("rootsell").style.color = "green";
            document.getElementById('rootsell').innerHTML = "&#8593; " + customersell;

            }
           
            $.ajax({
                
    type: "POST",
url: "captureid.php",
data: {uuid: uuid, customersell: customersell, customerbuy:  customerbuy},
dataType: 'json',
cache: false,
success: function(response) {

        alert(response.message);

    }
});

            previousbuy = newbuyprice;

            previoussell = newsellprice;

         
        };

        window.setInterval(function () {
      chart.updateOptions({
xaxis: {
  Categories: todaytime1
}

      });
      
        chart.updateSeries([{
          data: databuy
        }])
      }, 1000)


        if (runlive =="No") {
          $("#logoff").hide();
          $("#logon").hide();
          $("#buttonactions").hide();
          $("#resetsecurity").hide();
          $("#editprofile").hide();
          $("#editbankaccount").hide();
          $("#resetsecuritypintop").hide();
          $("#editprofiletop").hide();
          $("#editbankaccounttop").hide();

          $("#col1").hide();
          $("#col2").hide();
          $("#col3").hide();
          $("#col4").hide();
          if($(window).width() <= 661){
            $("#ancView").hide();
          }
        }
        else if (login == "no") {
          $("#logoff").show();
          //  $("#language-en").hide(); 
          //  $("#language-bm").hide();
          $("#resetsecurity").hide();
          $("#editprofile").hide();
          $("#editbankaccount").hide();
          $("#resetsecuritypintop").hide();
          $("#editprofiletop").hide();
          $("#editbankaccounttop").hide();
          
          $("#col1").hide();
          $("#col2").hide();
          $("#col3").hide();
          $("#col4").hide();
          if($(window).width() <= 661){
            $("#ancView").hide();
          }

          $("#logon").hide();
          $("#buttonactions").hide();
        }
        else if(login =="nodirect"){
          $("#logon").hide();
          $("#logoff").hide();
          $("#buttonactions").hide();
          $("#resetsecurity").hide();
          $("#editprofile").hide();
          $("#editbankaccount").hide();
          $("#resetsecuritypintop").hide();
          $("#editprofiletop").hide();
          $("#editbankaccounttop").hide();
          
          $("#col1").hide();
          $("#col2").hide();
          $("#col3").hide();
          $("#col4").hide();
          if($(window).width() <= 661){
            $("#ancView").hide();
          }
    }
    else{
     // alert('check');
      $("#logoff").hide();
      $("#resetsecurity").show();
    $("#editprofile").show();
    $("#editbankaccount").show();
    $("#resetsecuritypintop").show();
    $("#editprofiletop").show();
    $("#editbankaccounttop").show();
    }
 
   
  function openTab(url) {
    // Create link in memory
    var a = window.document.createElement("a");
    a.target = '_blank';
    a.href = url;
 
    // Dispatch fake click
    var e = window.document.createEvent("MouseEvents");
    e.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
    a.dispatchEvent(e);
};

historyprice(1,0);

$( "#1week" ).click(function() {
  historyprice(1,5);

  // Change classname
  document.getElementById("1week").className = "btn-selected";
  document.getElementById("1month").className = "btn";
  document.getElementById("3Months").className = "btn";
});


$( "#1month" ).click(function() {
  historyprice(1, 0);
  
  // Change classname
  document.getElementById("1week").className = "btn";
  document.getElementById("1month").className = "btn-selected";
  document.getElementById("3Months").className = "btn";
});


$( "#3Months" ).click(function() {
  historyprice(3, 0);

  // Change classname
  document.getElementById("1week").className = "btn";
  document.getElementById("1month").className = "btn";
  document.getElementById("3Months").className = "btn-selected";
});
$("#ancView").click(function(e){
  if($("#idcaret").attr("class") == "caret-up"){
    $("#ancView").html("<i><?php echo $lang["CaretButton_more"]; ?><span id='idcaret' class='caret-down'></span></i>");
    $('#btnAnchor').css({'display': 'block', 'border-bottom': 'solid 0px #d8d8d8'});
    $("#col1").fadeOut();
    $("#col2").fadeOut();
    $("#col3").fadeOut();
    $("#col4").fadeOut();
  }
  else{
    $("#ancView").html("<i><?php echo $lang["CaretButton_less"]; ?><span id='idcaret' class='caret-up'></span></i>");
    $('#btnAnchor').css({'display': 'block', 'border-bottom': 'solid 1px #d8d8d8'});
    $("#col1").fadeIn();
    $("#col2").fadeIn();
    $("#col3").fadeIn();
    $("#col4").fadeIn();
  }
  
});

$(window).resize(function(){
    if (runlive =="No") {
      $("#ancView").hide();
      $("#col1").hide();
      $("#col2").hide();
      $("#col3").hide();
      $("#col4").hide();
    }
    else if (login == "no") {
      $("#ancView").hide();
      $("#col1").hide();
      $("#col2").hide();
      $("#col3").hide();
      $("#col4").hide();
    }
    else if(login =="nodirect"){
      $("#ancView").hide();
      $("#col1").hide();
      $("#col2").hide();
      $("#col3").hide();
      $("#col4").hide();
    }
    else{
      if($(window).width() > 661){
        if($("#idcaret").attr("class") == "caret-up"){
          //do nothing
        }
        else{
          $("#ancView").html("<i><?php echo $lang['CaretButton_less']; ?><span id='idcaret' class='caret-up'></span></i>");
          $("#col1").fadeIn();
          $("#col2").fadeIn();
          $("#col3").fadeIn();
          $("#col4").fadeIn();
        }
      }
      else{
        //do nothing
      }
    }
  });
});

function historyprice(month, day){

  $.ajax({
                
                type: "POST",
            url: "controllers/pricehistory.php",
            data: {month: month},
            dataType: 'json',
            cache: false,
            success: function(data) {
           
              var name = [];
                    var databuy = [];

var count =0;
var max =0;
var maxtext="max ";
var minim=0.0;
var tempvalue=0;
var mintext="min ";
if (day==0){
                    for (var i in data) {
                      name.push(data[i].date);
                        databuy.push(data[i].companysell);
                        tempvalue = parseFloat(data[i].companysell);
                        if (count == 0) {
                          
                          minim = tempvalue;
                          
                          max = tempvalue;
                        }

                        if (tempvalue < minim) {
                         if(tempvalue >200){
                          minim = parseFloat(data[i].companysell);}
                        } 
                        if(tempvalue > max)
                        {max = tempvalue;}
                        count+=1;
                        }
                    
                  }else{
                    for (var i in data) {
                        if (count == 7) {
                          break;
                        }
                        name.push(data[i].date);
                        databuy.push(data[i].companysell);
                       
                        tempvalue = parseFloat(data[i].companysell);
                        if (count == 0) {
                          minim = tempvalue;
                          max = tempvalue;
                        }
                        if (tempvalue< minim) {
                         
                          minim = parseFloat(data[i].companysell);
                        } 
                        if(tempvalue > max)
                        {max = tempvalue;}
                        count+=1;
                    }
                  }
                    
           
                    
           maxtext = maxtext + max;   
           mintext = mintext + minim; 
          
                    var options = {
  chart: {
    height: 380,
    scaleShowLabels : true,
    type: "area",
    fzoom: {
            autoScaleYaxis: true
          },
    animations: {
      initialAnimation: {
        enabled: true
      }
    }
  },
  colors:['#FFA500', '#FBB917', '#FFAE42'],
  stroke: {
          curve: 'smooth',
          width: [1, 0]
        },
        fill: {
  type: "gradient"
   
},
dataLabels: {
          enabled: false
        },
  series: [
    {
      type: 'area',
      name: "Customer Buy",
      style: {
                fontFamily: "Nunito, Arial, sans-serif",
              },
      data: databuy
    }
  ],
  grid: {
      show: false,      // you can either change hear to disable all grids
    },
  xaxis: {
    type: 'datetime',
    categories: name,
    tickAmount: 6,
        
  },
  annotations: {
          yaxis: [{
            y: max,
            borderColor: '#999',
            label: {
              show: true,
              text: maxtext,
              style: {
                color: "#fff",
                background: '#00E396'
              }
            }
          },
          {
            y: minim,
            borderColor: '#999',
            label: {
              show: true,
              text: mintext,
              style: {
                color: "#fff",
                background: '#FEB019'
              }
            }
          }]},
  yaxis: {
          title: {
            style: {
                fontFamily: "Nunito, Arial, sans-serif",
                fontWeight: "bold"
              },
            text: 'Price (RM)'
          }
        },
         title: {
          //text: 'Current Gold Price per Gram',
          style: {
                //fontSize: "140px",
                fontFamily: "Nunito, Arial, sans-serif",
                fontWeight: "bold"
              },
          text: 'Historical Gold Price',
          align: 'left'
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.9,
            stops: [0, 100]
          },
          colors: "#FFD700"
        },
}

var chart = new ApexCharts(document.querySelector("#chart"), options);
$('#chart').show();
$('#chart2').hide();
$('#chart3').hide();
if (day ==5) {
  var chart = new ApexCharts(document.querySelector("#chart2"), options);
  $('#chart').hide();
  $('#chart3').hide();
  $('#chart2').show();
}
if (month ==3) {
  var chart = new ApexCharts(document.querySelector("#chart3"), options);
  $('#chart').hide();
  $('#chart3').show();
  $('#chart2').hide();
}

chart.render();
            
                }
            });
            

  
}

    </script>

<style>

/* Media Query Settings */
@media only screen and (max-width: 660px) {
	
	#faq-switcher-small{
		display: block;
	}
	#faq-switcher{
		display: none;
	}
  #open {
    text-align: center;
    margin-top: 1%;
    margin-bottom: -5%;
  }
  #close {
    text-align: center;
    margin-top: 1%;
    margin-bottom: -5%;
  }

  #chartbuttons {
    margin:auto;
    width:auto;
    background:transparent;
    border:none;
 
  }

  #btnAnchor{display:block;}
  #col1,#col2,#col3,#col4{display:none;}
}


@media only screen  and (min-width: 661px) and (max-width: 880px) {
	
	#faq-switcher-small{
		display: block;
	}
	#faq-switcher{
		display: none;
	}
  #open {
    text-align: center;
    margin-top: 1%;
    margin-bottom: -7%;
  }
  #close {
    text-align: center;
    margin-top: 1%;
    margin-bottom: -7%;
  }
  .main-widget{
    min-height: 160px;
  }

  #chartbuttons {
    margin:auto;
    width:auto;
    background:transparent;
    border:none;
   
  }

  #btnAnchor{display:none;}
  #col1 #col2 #col3 #col4{display:block;}
}


@media only screen and (min-width: 881px) {
	
	#faq-switcher-small{
		display: none;
	}
	#faq-switcher{
		display: block;
	}

  #open {
    text-align: center;
    margin-top: 1%;
    margin-bottom: -1%;
  }
  #close {
    text-align: center;
    margin-top: 1%;
    margin-bottom: -1%;
  }
  #chartbuttons {
    margin:auto;
    width:auto;
    background:transparent;
    border:none;
    
  }

  #btnAnchor{display:none;}
  #col1 #col2 #col3 #col4{display:block;}
}

.chart-container {
  position: relative;
  margin: auto;
  height: 190vh;
  width: 80vw;
}

.caret-up{
  background: transparent url(./img/icon/caret-up.png) center right/contain no-repeat;
          height:20px;
          width:20px;
          float:right;
}
.caret-down{
  background: transparent url(./img/icon/caret-down.png) center right/contain no-repeat;
          height:20px;
          width:20px;
          float:right;
}

@media only screen and (max-width: 780px) {
  .balance-row2 .col.current {
		width: 50%;
		border-bottom: 0px;
	}
}
</style>


</head>
<?php
 // Init session variables for guest user
  if($_SESSION['goldbalance']){
    $_SESSION['goldbalance'] = $_SESSION['goldbalance'];
  }else{
    $_SESSION['goldbalance'] = 0;
  }

  if($_SESSION['avgbuyprice']){
    $_SESSION['avgbuyprice'] = $_SESSION['avgbuyprice'];
  }else{
    $_SESSION['avgbuyprice'] = '0.00';
  }

  if($_SESSION['totalcostgoldbalance']){
    $_SESSION['totalcostgoldbalance'] = $_SESSION['totalcostgoldbalance'];
  }else{
    $_SESSION['totalcostgoldbalance'] = '0.00';
  }

  if($_SESSION['diffcurrentpriceprcetage']){
    $_SESSION['diffcurrentpriceprcetage'] = $_SESSION['diffcurrentpriceprcetage'];
  }else{
    $_SESSION['diffcurrentpriceprcetage'] = 0;
  }

  if($_SESSION['currentgoldvalue']){
    $_SESSION['currentgoldvalue'] = $_SESSION['currentgoldvalue'];
  }else{
    $_SESSION['currentgoldvalue'] = '0.00';
  }
 
?>
<body class="gogold">
  <div id="faq-switcher" class="faq-switcher">
		<ul>
			<li>
				<a href="FAQ.php" rel="noopener noreferrer"><?php echo $lang['FAQ'] ?></a>
			</li>
      		<li>
				<a href="pdpa.php">PDPA</a>
			</li>
    		<li>
				<a href="Tnc.php"><?php echo $lang['Terms_and_Conditions'] ?></a>
			</li>
     		<!-- <li id="deliveryrefundpolicies">
				<a href="DeliveryRefundPolicies.php"><php echo $lang['RefundPolicy'] ?></a>
			</li> -->
     		<li>
				<a href="productDisclosure.php"><?php echo $lang['ProductDisclosure_HEADER'] ?></a>
			</li>
			<li id="resetsecuritypintop">
				<a href="dashboard-pin.php"><?php echo $lang['ResetPin'] ?></a>
			</li>
			<li id="editprofiletop">
				<a href="edit-profile.php" rel="noopener noreferrer"><?php echo $lang['EditProfile'] ?></a>
			</li>
			<li id="editbankaccounttop">
				<a  href="edit-bank.php" rel="noopener noreferrer"><?php echo $lang['EditBankAccount'] ?></a>
			</li>
			<!-- <li id="closeaccount">
				<a href="close-account.php" rel="noopener noreferrer"><php echo $lang['CloseAccount'] ?></a>
			</li> -->
		</ul>
	</div>
	<div id="faq-switcher-small" class="navbar">
		<div class="dropdown">
			<button class="dropbtn">
				<i class="fa fa-bars"></i>
			</button>
			<div class="dropdown-content">
				<a href="FAQ.php"><?php echo $lang['FAQ'] ?></a>
				<a href="pdpa.php">PDPA</a>
				<a href="Tnc.php"><?php echo $lang['Terms_and_Conditions'] ?></a>
       			<!-- <a href="DeliveryRefundPolicies.php"><php echo $lang['RefundPolicy'] ?></a> -->
        		<a href="productDisclosure.php"><?php echo $lang['ProductDisclosure_HEADER'] ?></a>
				<a id="resetsecurity" href="dashboard-pin.php"><?php echo $lang['ResetPin'] ?></a>
				<a id="editprofile" href="edit-profile.php" rel="noopener noreferrer"><?php echo $lang['EditProfile'] ?></a>
				<a id="editbankaccount" href="edit-bank.php" rel="noopener noreferrer"><?php echo $lang['EditBankAccount'] ?></a>
				<!-- <a id="closeaccount" href="close-account.php" rel="noopener noreferrer"><php echo $lang['CloseAccount'] ?></a> -->
			</div>
		</div>
	</div>
	<div class="language-switcher"> 
		<ul>
			<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?></li>
			<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="index.php?lang=en">Eng</a></li>
			<li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="index.php?lang=bm">BM</a></li>
			<!--<li><a href="https://dev.finexusgroup.com:4445/gopayz/services/FinancialServices">Back to GoPayz</a></li>-->
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div id="userdashboard" class="main-widget">
				<div class="balance-row balance-row2">
					<label><?php echo $lang['GOLDBALANCE']; ?></label>
					<div class="balance-value">
            <div class="unit"><abbr title="The gold remaining in your account in grams." rel="tooltip"><?php echo $lang['gram']; ?></abbr></div>
						<div class="num"><?php echo $_SESSION['goldbalance']; ?></div>
            <!--<div class="num"><abbr title="The gold remaining in your account in grams." rel="tooltip"><php echo $_SESSION['goldbalance']; ?></abbr></div>-->
						<!--<div class="unit"><php echo $lang['gram']; ?></div>-->
           
					</div>
          <div id="btnAnchor" style="width:100%;text-align:right;font-size:smaller;font-weight:100">
            <a id="ancView"><i><?php echo $lang["CaretButton_more"]; ?><span id="idcaret" class="caret-down"></span></i></a>
          </div>
					<div class="col" id="col1">
						<!--<div class="unit">Avg Purchase Price</div>-->
            <div class="unit"><abbr title="The average of all the buy prices you have bought gold with." rel="tooltip"><?php echo $lang["AvgPrice"]; ?></abbr></div>
						<div class="num"><?php echo $_SESSION['avgbuyprice']; ?>/g</div>
					</div>
					<div class="col" id="col2">
						<!--div class="unit">Total Purchased</div>-->
            <div class="unit"><abbr title="The total cost of gold remaining in your account." rel="tooltip"><?php echo $lang["MyGoldCost"]; ?></abbr></div>
						<div class="num">RM<?php echo $_SESSION['totalcostgoldbalance']; ?></div>
            
					</div>
          <div class="col current" id="col3">
          <div class="unit"><abbr title="The percentage of rise and fall of your gold investment value against the current prevailing price." rel="tooltip"><?php echo $lang["Percentage"]; ?></abbr></div>
             <?php 
                    if($_SESSION['diffcurrentpriceprcetage'] > 0){
                      $percentage = ' ↑ '.abs($_SESSION['diffcurrentpriceprcetage']).'%'; 
                      // Set color for background
                      $color = 'green';
                    }else if($_SESSION['diffcurrentpriceprcetage'] < 0){
                      $percentage = ' ↓ '.abs($_SESSION['diffcurrentpriceprcetage']).'%';  
                      // Set color for background
                      $color = 'red';
                    }else{
                      $percentage = abs($_SESSION['diffcurrentpriceprcetage']).'%'; 
                      // Set color for background
                      $color = 'grey';
                    }
                  
                ?>
						<div class="num-box">
              <div class="trend" style="background-color:<?php echo $color;?>">
                <?php echo $percentage;?>
              </abbr></div>
							
						</div>
            
          </div>
          <div class="col" id="col4">
						
						<div class="num-box">
            <div class="unit" id="goldvalue"> <abbr title="The value of your gold investment based on the current prevailing price." rel="tooltip"><?php echo $lang['CurrentGoldValue']; ?></abbr></div>
							<div  id="currentgoldvalue" class="num">RM<?php echo $_SESSION['currentgoldvalue']; ?></div>
						</div>
            
          </div>
					<!--<div class="col current">
						
						<div class="num-box">
            <div class="unit" id="goldvalue"><php echo $lang['CurrentGoldValue']; ?></div>
              <div class="trend">↑ 0.49%</div>
              <div class="trend"><abbr title="The percentage of rise and fall of your gold investment value against the current prevailing price." rel="tooltip">
                <php 
                $_SESSION['totalcostgoldbalance'] = 1;
                    if($_SESSION['totalcostgoldbalance'] > 0){
                      $percentage = ' ↑ '.$_SESSION['totalcostgoldbalance'].'%'; 
                    }else if($_SESSION['totalcostgoldbalance'] < 0){
                      $percentage = ' ↓ '.$_SESSION['totalcostgoldbalance'].'%';  
                    }else{
                      $percentage = ' ↑ '.$_SESSION['totalcostgoldbalance'].'%'; 
                    }
                    echo $percentage;
                   
                   
                ?>
              </abbr></div>
							<div  id="currentgoldvalue" class="num">RM2000.00</div>
              <div id="currentgoldvalue" class="num"><abbr title="The value of your gold investment based on the current prevailing price." rel="tooltip">RM2000.00</abbr></div>
						</div>
            
					</div>-->
				</div>
				<div class="buy-sell-row">
					<div class="col">
						<div class="action-title"><?php echo $lang['CustomerSell']; ?></div>
						<div class="current-value up">
						
							<div id="rootsell" class="num">240.00</div>
						</div>
						<div class="unit"><?php echo $lang['pergram']; ?></div>
					</div>
					<div class="col">
						<div class="action-title"><?php echo $lang['CustomerBuy']; ?></div>
						<div class="current-value down">
						
							<div id="rootbuy" class="num">240.00</div>
						</div>
						<div class="unit"><?php echo $lang['pergram']; ?></div>
            <div id="smallchart"></div>
					</div>
				</div>
        <p id="open" style="color:green;font-weight: bold;">Status: Open  &#9989;</p>
        <p id="close" style="color:red;font-weight: bold;">Status: Close  &#9940;</p>
			</div>
			
			<div id="logon" class="widget-grid icon-actions">
				<div class="row col2">
					<a href="dashboard-sell-gold.php" class="col">
						<div class="icon">
							<img src="img/icon/icon-sell-gold-mcash.svg" />
						</div>
						<div class="title"><?php echo $lang['SellGold']; ?></div>
					</a>
					<a href="dashboard-buy-gold.php" class="col">
						<div class="icon">
							<img src="img/icon/icon-buy-gold-mcash.svg" />
						</div>
						<div class="title"><?php echo $lang['BuyGold']; ?></div>
					</a>
				</div>
				<div class="row col2">
					<a id="conversion" href="convert.php" class="col">
						<div class="icon">
							<img src="img/icon/icon-convert-mcash.svg" />
						</div>
						<div class="title"><?php echo $lang['ConvertGold']; ?></div>
					</a>
					<a href="price-alert.php" class="col">
						<div class="icon">
							<img src="img/icon/icon-price-mcash.svg" />
						</div>
						<div class="title"><?php echo $lang['PriceAlert']; ?></div>
					</a>
					<a href="transactions.php" class="col">
						<div class="icon">
							<img src="img/icon/icon-history-mcash.svg" />
						</div>
						<div class="title"><?php echo $lang['Transaction']; ?></div>
					</a>
					<!--<a class="col">
						<div class="icon">
							<img src="img/icon/icon-transfer-mcash.svg" />
						</div>
						<div class="title">Transfer Gold</div>
					</a>-->
				</div>
       
				<!--<div class="row col2">
					<a href="price-alert.php" class="col">
						<div class="icon">
							<img src="img/icon/icon-price-mcash.svg" />
						</div>
						<div class="title"><php echo $lang['PriceAlert']; ?></div>
					</a>
					<a href="transactions.php" class="col">
						<div class="icon">
							<img src="img/icon/icon-history-mcash.svg" />
						</div>
						<div class="title"><php echo $lang['Transaction']; ?></div>
					</a>
				</div>-->
			</div>
			<div id="logoff" class=" icon-actions" >
				<div class="main-widget" style="text-align: center;" >
					<a style="display:inline-block" href="register-1.php" class="col">
						<div class="icon">
							<img src="img/icon/icon-register-mcash.svg" />
						</div>
						<div class="title"><?php echo $lang['RegisterNow']; ?></div>
					</a>
					
				</div>
				
			</div>
		</div>
		
    <div id="logon" class="widget-grid icon-actions">
        <div class="wrapper" id="wrapper">
          <div id="chart"></div>
          <div id="chart2"></div>
          <div id="chart3"></div>
        </div>
		</div>

    <div class="widget-grid icon-actions">
  
      <div id="chartbuttons" class="row col">
       
  		
          <button id="1week" class="btn">
            <div class="title"><?php echo $lang['1Week']; ?></div>
          </button>
        
          <button id="1month" class="btn-selected">
            <div class="title"><?php echo $lang['1Month']; ?></div>
          </button>
          <button id="3Months" class="btn">
            <div class="title"><?php echo $lang['3Months']; ?></div>
          </button>

				</div>
    </div>
  
</div>
<footer style="text-align: center;">
   
   <p style="color:gray">Powered by ACE Capital Growth Sdn Bhd</p><p style="color:gray">Operation hours : 830am till 1159pm daily</p>
</footer>

		
	</div>
	
</body>
</html>