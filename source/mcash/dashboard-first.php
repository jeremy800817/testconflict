<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/login.php');  ?>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard - First Time | GoGold</title>
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


<!-- End of Async Drift Code -->
<script>
$(document).ready(function(){
      $(this).scrollTop(0);

         $("#close").hide();
         $("#open").show();
         $("#logoff").hide();
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
            var sellcount = datasell.push(newsellprice);
            todaytime1.push(todaytime2);
           if (buycount > 20) {
          databuy.shift();
          todaytime1.shift();
          }
          if(sellcount>20){
          datasell.shift();
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



        if (runlive =="No") {
          $("#logoff").hide();
    $("#logon").hide();
    $("#resetsecurity").hide();
    $("#editprofile").hide();
    $("#editbankaccount").hide();
    $("#resetsecuritypintop").hide();
    $("#editprofiletop").hide();
    $("#editbankaccounttop").hide();
        }else if (login == "no") {
      $("#logoff").show();
    //  $("#language-en").hide(); 
    //  $("#language-bm").hide();
    $("#resetsecurity").hide();
    $("#editprofile").hide();
    $("#editbankaccount").hide();
    $("#resetsecuritypintop").hide();
    $("#editprofiletop").hide();
    $("#editbankaccounttop").hide();


    $("#logon").hide();
   
    }else if(login =="nodirect"){
      $("#logon").hide();
      $("#logoff").hide();
      $("#resetsecurity").hide();
    $("#editprofile").hide();
    $("#editbankaccount").hide();
    $("#resetsecuritypintop").hide();
    $("#editprofiletop").hide();
    $("#editbankaccounttop").hide();
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

var options = {
  chart: {
    height: 380,
    width: "100%",
    type: "line",
    animations: {
      initialAnimation: {
        enabled: true
      }
    }
  },
  stroke: {
          curve: 'smooth',
          width: [3, 2]
        },
        fill: {
  type: "gradient",
   
},
  series: [
    {
      type: 'line',
      name: "Customer Buy",
      data: databuy
    },
    {
      type: 'line',
      name: "Customer Sell",
      data:datasell
    }
  ],
  markers: {
    size: [2,1],
},
  xaxis: {
    categories: 
      todaytime1
    
      
  },
  yaxis: {
          title: {
            text: 'Price (RM)'
          }
        },
        title: {
          text: 'Current Gold Price per Gram',
          align: 'left'
        }
}

var chart = new ApexCharts(document.querySelector("#chart"), options);

chart.render();

window.setInterval(function () {
  chart.updateOptions( {
  xaxis: {
    categories: todaytime1
  }
});
        chart.updateSeries([{
          name: "Customer Buy",
          data: databuy
        },
      {name: "Customer Sell",
      data:datasell
      }])
      }, 1000)
    
});
</script>

</head>
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="dashboard-first.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="dashboard-first.php?lang=bm">BM</a></li>
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
			
			<div class="main-widget">
				<div class="balance-row">
					<label><?php echo $lang['GOLDBALANCE']; ?></label>
					<!--<div class="col current right">
						<div class="unit">Total Purchased</div>
						<div class="num">RM0.00</div>
					</div>-->
				</div>
				<div class="buy-sell-row">
					<div class="col">
						<div class="action-title"><?php echo $lang['CustomerSell']; ?></div>
						<div class="current-value down">
							<div class="icon"></div>
							<div id="rootsell" class="num">240.00</div>
						</div>
						<div class="unit"><?php echo $lang['pergram']; ?></div>
					</div>
					<div class="col">
						<div class="action-title"><?php echo $lang['CustomerBuy']; ?></div>
						<div class="current-value up">
							<div class="icon"></div>
							<div id="rootbuy" class="num">240.00</div>
						</div>
						<div class="unit"><?php echo $lang['pergram']; ?></div>
					</div>
				</div>
			</div>
			
			<div class="widget-grid icon-actions">
				<div class="row">
					<a class="col full">
						<div class="icon">
							<img src="img/icon/icon-register-mcash.svg" />
						</div>
						<div class="title"><?php echo $lang['RegisterNow']; ?></div>
					</a>
				</div>
			</div>
			
		</div>
		
		<div id="wrapper">
		<div id="chart">
    	<div id="chart2">
		</div>
		
		<footer>
			<p id="open" styly="">Status: Open ✅</p>
			<p id="close" style="display: none;">Status: Close ⛔</p>
			<p>Powered by ACE Capital Growth Sdn Bhd</p>
			<p>Operation hours : 830am till 1159pm daily</p>
			<div class="footer-links">
				<ul>
					<li>
						<a rel="noopener noreferrer" href="pdpa.php">PDPA</a>
					</li>
					<li>
						<a href="Tnc.php" rel="noopener noreferrer">Terms and Conditions</a>
					</li>
				</ul>
			</div>
		</footer>
		
	</div>
	
</body>
</html>