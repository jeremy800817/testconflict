<!doctype html>
<html lang="en-gb" dir="ltr">
<head>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_FAQ'];?></title>
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
<script src="http://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"> </script>
<script>
  $(document).ready(function(){
    $(this).scrollTop(0);

      // Set FAQ Setting
      var login = '<?php echo $_SESSION['login'];?>';
      if (login == "no") {

        $("#resetsecurity").hide();
        $("#editprofile").hide();
        $("#editbankaccount").hide();
        $("#resetsecuritypintop").hide();
        $("#editprofiletop").hide();
        $("#editbankaccounttop").hide();

      }else if(login =="nodirect"){
        $("#resetsecurity").hide();
        $("#editprofile").hide();
        $("#editbankaccount").hide();
        $("#resetsecuritypintop").hide();
        $("#editprofiletop").hide();
        $("#editbankaccounttop").hide();

      }
      else {

        $("#resetsecurity").show();
        $("#editprofile").show();
        $("#editbankaccount").show();
        $("#resetsecuritypintop").show();
        $("#editprofiletop").show();
        $("#editbankaccounttop").show();
      }
    
      
    $("#back_btn").click(function (){
  window.history.back();
});

  var myState = {
            pdf: null,
            currentPage: 1,
            zoom: 1
        }
      
        pdfjsLib.getDocument('GoGoldFAQfinal.pdf').then((pdf) => {
      
            myState.pdf = pdf;
            render();
 
        });
 
        function render() {
            myState.pdf.getPage(myState.currentPage).then((page) => {
          
                var canvas = document.getElementById("pdf_renderer");
                var ctx = canvas.getContext('2d');
      
                var viewport = page.getViewport(myState.zoom);
 
                canvas.width = viewport.width;
                canvas.height = viewport.height;
          
                page.render({
                    canvasContext: ctx,
                    viewport: viewport
                });
            });
        }
 
        
        $("#go_previous").click(function(){
            if(myState.pdf == null || myState.currentPage == 1) 
              return;
            myState.currentPage -= 1;
            document.getElementById("current_page").value = myState.currentPage;
            render();
  });
    
         $("#go_next").click(function(){
            if(myState.pdf == null || myState.currentPage > myState.pdf._pdfInfo.numPages) 
               return;
            myState.currentPage += 1;
            document.getElementById("current_page").value = myState.currentPage;
            render();
  });

      
        $("#current_page").click(function(){
            if(myState.pdf == null) return;
          
          // Get key code
          var code = (e.keyCode ? e.keyCode : e.which);
        
          // If key code matches that of the Enter key
          if(code == 13) {
              var desiredPage = 
              document.getElementById('current_page').valueAsNumber;
                                
              if(desiredPage >= 1 && desiredPage <= myState.pdf._pdfInfo.numPages) {
                  myState.currentPage = desiredPage;
                  document.getElementById("current_page").value = desiredPage;
                  render();
              }
          }
  });

        $("#zoom_in").click(function(){
            if(myState.pdf == null) return;
            myState.zoom += 0.5;
            render();
  });

  $("#zoom_out").click(function(){
    if(myState.pdf == null) return;
            myState.zoom -= 0.5;
            render();
  });

  $("#hidpager").val(1);
  $("[id*=faq-page-]").attr("style","display:none");
  $("#faq-page-"+$("#hidpager").val()).attr("style","display:block");
  $(".btn-previous").attr("disabled","true");
  $(".lbl-pager").html("page 1 of 7");
  
  $(".btn-next").click(function(e){
    var i = $("#hidpager").val();
    $("#faq-page-"+i).attr("style","display:none");
    i++;
    
    $("#hidpager").val(i);
    $("#faq-page-"+i).attr("style","display:block");
    $(".lbl-pager").html("page "+i+" of 7");
    
    if($(this).attr("id")=="btn-next2"){
      window.scroll({
        top: 0, 
        left: 0, 
        behavior: 'smooth'
      });
    }
    
    if(i == 7){
      $(".btn-next").attr("disabled","true");
    }
    else{
      $(".btn-next").removeAttr("disabled");
      $(".btn-previous").removeAttr("disabled");
    }
  });
  
  $(".btn-previous").click(function(e){
    var i = $("#hidpager").val();
    $("#faq-page-"+i).attr("style","display:none");
    i--;
    $("#hidpager").val(i);
    $("#faq-page-"+i).attr("style","display:block");
    
    $(".lbl-pager").html("page "+i+" of 7");
    
    if($(this).attr("id")=="btn-previous2"){
      window.scroll({
        top: 0, 
        left: 0, 
        behavior: 'smooth'
      });
    }
    
    if(i == 1){
      $(".btn-previous").attr("disabled","true");
    }
    else{
      $(".btn-next").removeAttr("disabled");
      $(".btn-previous").removeAttr("disabled");
    }
  });


  
});

</script>
<style>
      #canvas_container {
          width: 100%;
          height: 100%;
          overflow: auto;
      }
 
      #canvas_container {
        background: #333;
        text-align: center;
        border: solid 3px;
      }

      /*for small device eg smartphone*/
      @media only screen and (max-width: 660px) {

        .faq-item-header {
          text-align:center;
          font-weight:bold;
          border:5px solid #0cb14b;
          padding-top:5px;
          padding-bottom:5px;
          background-color:#0cb14b;
        }

        .tbl-body{
          text-align:justify;
        }

        .tbl-custom thead{
          font-weight:bold;
          text-align:center;
        }
        .tbl-custom td{
          border:1px solid black;
          text-align:center;
        }

        .tbl-custom-2 td{
          border:1px solid black;
          padding-left:2%;
          text-align:left;
        }

        .tbl-no-border{
          width:100%;
        }

        .tbl-no-border td{
          border: 0px;
          text-align:justify;
          padding-left:5%;
          padding-right:5%;
        }

        #html-faq-wrapper{
          border:0px dotted black;
          padding-left: 5%;
          padding-right: 5%;
          padding-top: 2%;
          margin-top:2%;
          margin-bottom:5%;
          background: transparent url(./img/bg/bg-pdf.jpg) top left/contain repeat-y;
          background-color:white;
          background-size:10px;
        }

        .gogold-logo {
          /*background: transparent url(./img/bg/bg-logo-pdf.png) center right/contain no-repeat;*/
          height:30px;
          width:100px;
          float:right;
          margin-left:100%;
        }

        .ace-group-logo {
          background: transparent url(./img/bg/bg-ace-group-pdf.png) center right/contain no-repeat;
          height:30px;
          width:100px;
          float:left;
          margin-right:100%;
        }
        .mcash-logo {
          /*width: 147px;*/
          height: 100%;
          width:200px;margin-left:30%;margin-right:30%;
          /* background: transparent url(./img/icon/icon-logo-mcash.png) center center/contain no-repeat; */
        }
      }

      /*for big device eg laptop, pc, etc*/
      @media only screen and (min-width: 661px) {

        .faq-item-header {
          text-align:center;
          font-weight:bold;
          border:5px solid #0cb14b;
          padding-top:5px;
          padding-bottom:5px;
          background-color:#0cb14b;
          margin-left: 10%;
        }

        .tbl-body{
          text-align:justify;
        }

        .tbl-custom thead{
          font-weight:bold;
          text-align:center;
        }
        .tbl-custom td{
          border:1px solid black;
          text-align:center;
        }

        .tbl-custom-2 td{
          border:1px solid black;
          padding-left:2%;
          text-align:left;
        }

        .tbl-no-border{
          width:100%;
        }

        .tbl-no-border td{
          border: 0px;
          text-align:justify;
          padding-left:5%;
          padding-right:5%;
        }

        #html-faq-wrapper{
          border:1px solid black;
          padding-left: 5%;
          padding-right: 5%;
          padding-top: 2%;
          margin-top:2%;
          margin-bottom:5%;
          background: transparent url(./img/bg/bg-pdf.jpg) top left/contain repeat-y;
          background-color:white;
          background-size:20px;
        }

        .gogold-logo {
          /*background: transparent url(./img/bg/bg-logo-pdf.png) center right/contain no-repeat;*/
          height:50px;
          width:200px;
          float:right;
          margin-left:100%;
        }

        .ace-group-logo {
          background: transparent url(./img/bg/bg-ace-group-pdf.png) center right/contain no-repeat;
          height:30px;
          width:100px;
          float:left;
          margin-right:100%;
        }

        .mcash-logo {
          /*width: 147px;*/
          height: 100%;
          width:200px;margin-left:40%;margin-right:40%;
          /* background: transparent url(./img/icon/icon-logo-mcash.png) center center/contain no-repeat; */
        }
      }

      .btn-pager {
        min-width:50px;
        /*background-color:#a3e0e3;*/
        background-color:#0DB14B;
        border:5px solid #0DB14B;
      }

      .btn-pager:active {
        opacity:0.8;
        transform: translateY(0.5px);
      }

      #something{
        display: inline-block;
        width: 16px;
        height: 16px;
        position: absolute;
        left: -20px;
        top: 50%;
        margin-top: -40px;
        transform: translateY(-100%);
        background: transparent url(./img/icon/icon-back.svg) center center/contain no-repeat;
        /*background: transparent url(./img/icon/backbutton-1.svg) center center/contain no-repeat;*/
      }
      
  </style>
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
		<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="FAQ.php?lang=en">Eng</a></li>
        <li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="FAQ.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
    <div id="faq-switcher" style="margin-top: 10px;margin-left:5px;" >
        <ul>
          <li>
            <div class="header-inside">
              <!-- <a id="something" href="index.php"></a> -->
            </div>
          </li>
        </ul>
    </div>
    <div id="faq-switcher-small" style="margin-top: 10px;margin-left:5px;" >
        <ul>
          <li>
            <div class="header-inside">
              <!-- <a id="something" href="index.php"></a> -->
            </div>
          </li>
        </ul>
    </div>
		<div class="hero">
      <div class="mcash-logo"></div>
    </div>
	</div>
  <div class="header-inside">
		<div class="inside-title"><?php echo $lang['FAQ_HEADER'] ?><br/>(FAQs)</div>
    <a class="back" href="index.php"></a>
	</div>
	<div class="page-content" style="margin-left:5%;margin-right:5%">
      <div id="faq-pager" style="width:100;text-align:center;display:none;">
        <input type="hidden" id="hidpager" name="hidpager" value="1" />
        <br/>
        <table style="width:100%;">
          <tr>
            <td style="float:right;padding-right:5px">
              <button id="btn-previous1" class="btn-pager btn-previous" disabled="false"><span style="font-weight:bold"><</span></button>
            </td>
            <td style="width:30%;vertical-align:middle;background-color:white;">
              <label id="lbl-pager" class="lbl-pager" style="min-width:50px"></label>
            </td>
            <td style="float:left;padding-left:5px">
              <button id="btn-next1" class="btn-pager btn-next"><span style="font-weight:bold">></span></button>
            </td>
          </tr>
        </table>
      </div>
      <br/>
      <!-- <div class="link-paging" style="text-align:center">
        <label>Page:</label>
        &nbsp;
        <u><a class="link-number" href="javascript:void(0)">1</a></u>
        &nbsp;&nbsp;
        <a class="link-number" href="javascript:void(0)">2</a>
        &nbsp;&nbsp;
        <a class="link-number" href="javascript:void(0)">3</a>
        &nbsp;&nbsp;
        <a class="link-number" href="javascript:void(0)">4</a>
        &nbsp;&nbsp;
        <a class="link-number" href="javascript:void(0)">5</a>
        &nbsp;&nbsp;
        <a class="link-number" href="javascript:void(0)">6</a>
        &nbsp;&nbsp;
        <a class="link-number" href="javascript:void(0)">7</a>
      </div> -->
    <div id="html-faq-wrapper">
      <div class="gogold-logo">
      </div>
      <br/><br/>
      <div id="faq-header" style="width:100%">
        <p style="font-weight:bold;text-align:center"></p>
      </div>
      <div id="faq-page-1">
        <div class="faq-item-header">
          MGold PRODUCT
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">What is MGold?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              MGold is a simple and cost-effective way to own Digital gold in quantities to suit all budgets, you are able to buy, invest and accumulated Digital gold in fractional amount 
              with multiple redemption options to physical Minted gold bar.
              <br/>
              Note:
              <br/>
              <table class="tbl-body">
                <tr>
                  <td style="width:13%">1)</td>
                  <td>MRuncit Commerce Sdn Bhd. is the referral of this product.</td>
                </tr>
                <tr>
                  <td>2)</td>
                  <td>The buying and selling of gold are powered by Ace Capital Growth Sdn. Bhd. (“ACE”) a subsidiary of Ace Innovate Asia Berhad.</td>
                </tr>
              </table>
              <br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q2.</td>
              <td style="font-weight:bold">Who owns the gold that I buy?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              ACE buys large gold bars and stores safely in the Security vault. These golds allow ACE to offer economies of scale and pass on the saving to MGold user. When you buy 
              Digital gold, you own it and have a legal title to it, with ACE acting as a custodian. 
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q3.</td>
              <td style="font-weight:bold">How is quality ensured?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              ACE guarantees that:
              <br/>
              <table class="tbl-body">
                <tr>
                  <!-- <td style="width:13%;font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>You own and have full legal title to every gram of gold in your MGold account</td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>The gold is the highest 24k standards and 999.9 purity accredited by London Bullion Market Associated (LMBA).</td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>Store your gold bars private and securely in the Vault provided by the Safeguards G4S Malaysia (“SG4S”) that guarded around the clock by trained security team.</td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>All gold held are insured for its full replacement value.</td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>Your gold is stored securely in the Security Vault which is reconciled and accounted for each day.</td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>ACE is Certified Shariah Compliant by Salihin advisor and all of our operations and processes are in line with the Shariah Standard on Gold.</td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>ACE always holds more gold in the Vault than is held by our clients.</td>
                </tr>
              </table>
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q4.</td>
              <td style="font-weight:bold">Who may open a MGold account?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              <table class="tbl-body">
                <tr>
                  <!-- <td style="width:10%;font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>An individual above 18 years old;</td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>Citizen and Non-citizen of Malaysia (with Malaysian NRIC and passport holder);</td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>
                    User of MCash account.
                    <br/>
                    Note:
                    <br/>
                    *You can download the MCash App from Google Play Store, Apple App Store or Huawei App Gallery and register at MCash platform.
                  </td>
                </tr>
              </table>
              <br/>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div id="faq-page-2">
        <div class="faq-item-header">
         PURCHASE OF MGold
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">What are trading hours to perform my gold transactions?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              MGold can be performed from 8.30 am to 11.59 pm, 365 days a year.
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q2.</td>
              <td style="font-weight:bold">How can I be assured of the purity of the gold I buy?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              Bars of 999.9 fine gold are bearing the branded IGR™ logo and registered serial number imprinted in the gold bar to act as a Certificate. All the gold bars are meet the LBMA Good 
              Delivery Standard to ensure the highest levels of purity and quality.
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q3.</td>
              <td style="font-weight:bold">What is the live market price and how is the price of MGold set?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              ACE offers MGold at the current live market gold price, and the price is updated real time throughout the day. At checkout, the quoted price is what you would pay at that point of 
              time. This quote will be valid for 20 seconds and need to be refreshed after the rates expired.
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q4.</td>
              <td style="font-weight:bold">Is there a daily limit and how much money I can purchase?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              <table class="tbl-custom" style="width:100%;">
                <thead style="font-weight:bold;">
                  <tr style="background-color:#0cb14b;">
                    <td style="width:40%;">&nbsp;</td>
                    <td style="width:20%">Initial Purchase<br/>MYR</td>
                    <td style="width:40%">Subsequent Purchase<br/>MYR</td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Minimum Amount</td>
                    <td>25.00</td>
                    <td>25.00</td>
                  </tr>
                  <tr>
                    <td colspan="3" style="border-left:0px;border-right:0px">&nbsp;</td>
                  </tr>
                  <tr style="background-color:#0cb14b;">
                    <td>Maximum Daily Limit</td>
                    <td style="font-weight:bold">Gram</td>
                    <td style="font-weight:bold">Payment</td>
                  </tr>
                  <tr>
                    <td>
                      <table class="tbl-no-border">
                        <tr>
                          <td style="width:10%;font-weight:bold;">✓</td>
                          <td>MCash Basic Wallet Account</td>
                        </tr>
                      </table>
                    </td>
                    <td rowspan="3" style="vertical-align:middle;">1000.00</td>
                    <td>MYR1,000 per day</td>
                  </tr>
                  <!-- <tr>
                    <td>
                      <table class="tbl-no-border">
                        <tr>
                          <td style="width:10%;font-weight:bold;">✓</td>
                          <td>mCash Premium Wallet Account</td>
                        </tr>
                      </table>
                    </td>
                    <td>MYR5,000.00</td>
                  </tr> -->
                  <tr>
                    <td>
                      <table class="tbl-no-border">
                        <tr>
                          <td style="width:10%;font-weight:bold;">✓</td>
                          <td>FPX*</td>
                        </tr>
                      </table>
                    </td>
                    <td>MYR30,000 - MYR50,000</td>
                  </tr>
                </tbody>
              </table>
              <br/>
              <!-- Note: -->
              <!-- <br/> -->
              *The above limit is subject to your internet banking limit with your bank.
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q5.</td>
              <td style="font-weight:bold"> Do I need to maintain a minimum balance in my MGold account?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                Yes, you need to maintain minimum balance of MYR25.00 in your MGold account at all time for payment of your storage fees. 
                <br/><br/>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div id="faq-page-3">
        <div class="faq-item-header">
          BUY BACK OF MGold
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">Can I sell back my MGold Digital gold to ACE?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              Yes, you can sell your Digital gold back to us anytime at no charge and money will be credited into 
              your MGold platform account using the available payment methods.
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q2.</td>
              <td style="font-weight:bold">When will I get my fund after I sell my MGold?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              <table class="tbl-custom" style="width:100%">
                <tr style="background-color:#0cb14b;">
                  <td style="font-weight:bold">Payment Method</td>
                  <td style="font-weight:bold;text-align:center;">Time-frame<br/>(business days)</td>
                </tr>
                <tr>
                  <td>Credited to Bank Account</td>
                  <td style="text-align:center;">1-2</td>
                </tr>
                <tr>
                  <td>MCash E-wallet Account</td>
                  <td style="text-align:center;">2-4</td>
                </tr>
              </table>
              <br/>
              Note:
              <br/>
              *Business days referring to Federal Territory
              <br/><br/>
              </td>
            </tr>
          </table>
        </div>
        <div class="faq-item-header">
          STORAGE
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">Is my Digital gold safe?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              Absolutely!
              <br>
              <table class="tbl-body">
                <tr>
                  <!-- <td style="width:13%;font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>Gold bars stored with SG4S are covered by insurance for its full replacement value. </td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>Metal account statements are issued daily by SG4S to confirm the details of your holdings. </td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>Daily reconciliations by ACE to continuously ensure the efficient accuracy and accountability. </td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>Annual audit is conducted by Salihin at the vault facility to confirm the compliance, accuracy and transparency. </td>
                </tr>
                <tr>
                  <!-- <td style="font-weight:bold;">✓</td> -->
                  <td style="width:13%;font-weight:bold;"><IMG src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAANCAYAAABLjFUnAAACtklEQVQokY2SWWyMYRSGn3/pbErNtE0tjVrSCEpELY3GEksibhAkiCVIKoQQid0N4qaRcCWxXNhChFBLi6S0qFqnVUuVmtZUN0M77bT9Z+Zf5nNjCUo8l+ecPHnf5EhCCEE3CG0+1sNK6kJuPrhWMWXGShRF6e70B1J3srcV60jzPOJDfoDqOo3eLgmXx8PgxU9wu91/lcm/D0ruXSM9rYKmu19oaA7TGjRo0RR6OKDq7BSePC79P5lpmmRnHqTwZC3hYITSV2HCwmJUejwXi9qISr34fH8zhzaO4FbBlX/XjLRMxf6+EX9FiJIXXTx4GeVjIIa/xaItIrF+LKzZN4ui4iYamjuJmJA6cQuLluX8muzO9Q3YyuuoKW/HW6VxuyzC2DEOqposGkMwsp/EkaeCbTk3iMgd9HLBA28j725sJ+/oRgBUgNqa94zTr/KqWsNXH6WsWmdCthOrXWBXwC5DWn8X7eEunKpgb66P8kuDmJ2ZikAipBf/lNlLplL8upN39TqGIXheY7BqThJVbzWClopTMblQ0okE2BUZS8QIt5qosoymyxiGxcwxych5ezK4Xxai4bOBaQgq/SaHc/uiSLDhaJDclR4G9Ldjk2F0EpT6Y4waIBPqkDl+s4v6T1ESexic2JqC+qiino5gFIcNdEMwLNNB0BfjVEEb00c6kSSBZRikJ0JKP5VtWTaWz/Jgi1PZf8ZPztkhhHXQoiAPzRhG6tDBxLvj8daajO/jINBq4PPpLNh1nvN3O4mzKwQ0OL47mR3Lkkh2q1gxQdYQmUKvxrk3WQxcUo46Z1M+qxdOwkkcGcNTkDCobY5x6lkLCQkJZE8KsGJaCp/sFook42syKK3UqQ724bK3C4fD8fPRxDd0XRdL500WO+f2FAd2rRC/s2neQHFsbaLIu3D6j913vgJ9MWn0SdosSAAAAABJRU5ErkJggg=="></td>
                  <td>You can log into your MGold account at any time to view your purchase and current gold bar holding including your full order history status via statement download.</td>
                </tr>
              </table>
              <br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q2.</td>
              <td style="font-weight:bold">How long I can hold on to my gold?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              There is no any minimum or maximum period imposed
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q3.</td>
              <td style="font-weight:bold">What are the storage fees?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                Storage fees is 1% per annum based on your daily gold holding. It will be auto deducted from the 
                available gold balance in your MGold account at the month end or MYR1.00 equivalent in grams 
                subject to whichever is higher. MGold has most willing to pass the saving of the storage fee to 
                you when MGold reach higher AUM and economy of scales.
                <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q4.</td>
              <td style="font-weight:bold"> Do I have personal access to my gold?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                The gold bars are stored in high-security vault and there is no public access to any of SG4S vaults.
                <br/><br/>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div id="faq-page-4">
        <div class="faq-item-header">
         PHYSICAL GOLD FULFILMENT
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">How do I convert my Digital gold to Minted gold bars?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              Yes, you have an option to convert available Digital gold balance in the MGold account to physical Minted gold bars. Each minted bar is bearing the exclusive 
              branded IGR™ logo and sealed in protective Certificate or in IGR security packaging that guarantee the 999.9 purity and weight.
              <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q2.</td>
              <td style="font-weight:bold"> What is the cost of my fulfilment fee?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                <br/>
                <span style="font-weight:bold">Making Charges</span>
                <table class="tbl-custom" style="width:100%">
                  <tr style="background-color:#0cb14b;">
                    <td style="width:40%;font-weight:bold">Denomination<br/>(Gram)</td>
                    <td style="width:60%;font-weight:bold">Making Charges/pc<br/>(MYR)</td>
                  </tr>
                  <tr>
                    <td>0.5</td>
                    <td>46.50</td>
                  </tr>
                  <tr>
                    <td>1</td>
                    <td>43.00</td>
                  </tr>
                  <tr>
                    <td>2.5</td>
                    <td>77.50</td>
                  </tr>
                  <tr>
                    <td>5</td>
                    <td>80.00</td>
                  </tr>
                  <tr>
                    <td>10</td>
                    <td>125.00</td>
                  </tr>
                  <tr>
                    <td>4.25</td>
                    <td>82.75</td>
                  </tr>
                  <tr>
                    <td>21.25</td>
                    <td>233.75</td>
                  </tr>
                  <tr>
                    <td>50</td>
                    <td>510.00</td>
                  </tr>
                  <tr>
                    <td>100 </td>
                    <td>860.00</td>
                  </tr>
                </table>
                <br/>
                <span style="font-weight:bold">Courier Charges:</span>
                <br/>
                <table class="tbl-custom" style="width:100%;text-align:center">
                  <thead style="font-weight:bold">
                    <tr style="background-color:#0cb14b;">
                      <td>Courier Charges</td>
                      <td>Cost</td>
                      <td>Packing Limit</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>WM/EM</td>
                      <td>MYR15.00 per parcel</td>
                      <td>30pcs/100g</td>
                    </tr>
                  </tbody>
                  </table>
                <br/>
                Note:
                <br/>
                <table>
                  <tr>
                    <td style="width:13%">1)</td>
                    <td style="text-align:justify">
                      The fulfilment fee is referring to Making Charges: (Premium/Pcs + Handling Charges/Pcs + Insurance/Pcs) + Packaging & Shipping;
                    </td>
                  </tr>
                  <tr>
                    <td>2)</td>
                    <td style="text-align:justify">
                      Packaging and shipment cost charge once only;
                    </td>
                  </tr>
                  <tr>
                    <td>3)</td>
                    <td style="text-align:justify">
                      ACE reserves the right to revise the fulfilment cost from time to time without prior notice. The changes will base on the charges imposed by the vendor and service 
                      providers;
                    </td>
                  </tr>
                  <tr>
                    <td>4)</td>
                    <td style="text-align:justify">
                      Maximum for physical gold fulfilment of minted gold bar is 100 gram or maximum 30 pieces each time for each parcel;
                    </td>
                  </tr>
                  <tr>
                    <td>5)</td>
                    <td style="text-align:justify">
                      It applied to both West Malaysia and East Malaysia, delivery is available in Malaysia only.
                    </td>
                  </tr>
                </table>
                <br/>
                <u>For illustration physical gold fulfilment only.</u>
                <br/>
                <table class="tbl-custom" style="width:100%;text-align:center">
                  <thead style="font-weight:bold;background-color:#0cb14b">
                    <tr>
                      <td rowspan="2">Scenario</td>
                      <td colspan="2">Calculation</td>
                      <td rowspan="2">Total Customer to Pay</td>
                    </tr>
                    <tr>
                      <td>Making Charges</td>
                      <td>Packaging Shipment</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>2 grams of physical gold (same denomination - 1 gram)</td>
                      <td>MYR 43.00 x 2 pcs</td>
                      <td>MYR 15.00</td>
                      <td>MYR 101.00</td>
                    </tr>
                    <tr>
                      <td>100 grams of physical gold (multiple denomination)</td>
                      <td>
                        50 gram - MYR 510.00 x 1 pcs
                        <br/>
                        10 gram - MYR 125.00 x 3 pcs
                        <br/>
                        5 gram - MYR 80.00 x 4 pcs</td>
                      <td>MYR 15.00</td>
                      <td>MYR 1,220.00</td>
                    </tr>
                  </tbody>
                </table>
                <!-- <span style="font-weight:bold">Scenario 1:</span>&nbsp;Customer convert 2 gram of physical gold – 2 pieces 1 gram
                <br/>
                Calculation :
                <br/>
                (Making Charges MYR43.00) x 2 pieces + (Packaging & Shipment MYR15.00)
                <br/>
                Total customer to pay : MYR101.00
                <br/><br/>
                <span style="font-weight:bold">Scenario 2:</span>&nbsp;Customer convert multiple of denomination - (in total 100 grams) 50 gram x 1pc, 10 gram x 3pcs, 5 gram x 4pcs
                <br/>
                Calculation:
                <br/>
                Deno 50 gram – (Making Charges MYR510.00) x 1 piece = MYR510.00
                <br/>
                Deno 10 gram – (Making Charges MYR125.00) x 3 pieces = MYR375.00
                <br/>
                Deno 5 gram – (Making Charges MYR80.00) x 4 pieces = MYR320.00
                <br/>
                Total = MYR1,205.00 + (Packaging & Shipment MYR15.00)
                <br/>
                Total customer to pay : MYR1,220.00
                <br/><br/> -->
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div id="faq-page-5">
        <div class="faq-item-header">
          DOORSTEP DELIVERY
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">What is the estimated delivery time?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                <table class="tbl-custom" style="width:100%">
                  <tr style="background-color:#0cb14b;">
                    <td style="width:50%;font-weight:bold">Destination</td>
                    <td style="width:50%;font-weight:bold">Standard Delivery (business days)</td>
                  </tr>
                  <tr>
                    <td>Peninsular Malaysia</td>
                    <td>3-5</td>
                  </tr>
                  <tr>
                    <td>East Malaysia</td>
                    <td>5-7</td>
                  </tr>
                </table>
                <br/>
                Note:
                <br/>
                <table class="tbl-body">
                  <tr>
                    <td style="width:13%">1)</td>
                    <td>
                      Business days is for Federal Territory.
                    </td>
                  </tr>
                  <tr>
                    <td>2)</td>
                    <td>
                    Please bear with us in case we are spending extra time in checking/ perfecting your items, particularly during the peak season.
                    </td>
                  </tr>
                </table>
                <br/>
              </td>
            </tr>
          </table>
        </div>
        <div class="faq-item-header">
          RETURNS AND REPLACEMENT
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">Can my Minted gold bar be returned?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                We do not provide this as a general service. If you believe your case is under exceptional circumstances, please contact our customer services team.
                <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q2.</td>
              <td style="font-weight:bold">What if my gold is lost during transit?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                Your gold is fully insured. If you do NOT receive your minted gold bar within the normal stipulated business days after the date of delivery application, please contact our 
                customer services team to initiate the necessary procedure.
                <br/><br/>
              </td>
            </tr>
          </table>
        </div>
        <br/>
        <div class="faq-item-header">
          PAYMENT METHODS
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">What are the available payment channels?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                <table class="tbl-body">
                  <tr>
                    <td style="width:13%;font-weight:bold;">✓</td>
                    <td>FPX*</td>
                  </tr>
                  <tr>
                    <td style="font-weight:bold;">✓</td>
                    <td>MCash E-wallet Account</td>
                  </tr>
                </table>
                Note:
                <br/>
                *The service charge imposed for FPX is RM1.20 per transaction.
                <br/><br/>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div id="faq-page-6">
        <div class="faq-item-header">
          ORDER AND PAYMENT CANCELLATION
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">How do I cancel my order and payment?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                No cancellation can be done once the order are paid and confirmed. Please contact our customer services team for any further assistance.
                <br/><br/>
              </td>
            </tr>
          </table>
        </div>
        <div class="faq-item-header">
          ACCOUNT CLOSING
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">What is a dormant account?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                When there is nil balance in the gold balance and there has not been any debit or credit entry in the MGold's account in the 6 months after the date of nil gold balance.
                <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q2.</td>
              <td style="font-weight:bold">How do I reactivate my dormant account?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                You are required to contact our customer services team.
                <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q3.</td>
              <td style="font-weight:bold">How do I close my MGold account?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                An account may only be closed if there are no outstanding gold balances remaining in that account. You may contact our customer services team to close your account.
                <br/><br/>
              </td>
            </tr>
          </table>
        </div>
        <div class="faq-item-header">
          FOR MORE ENQUIRIES
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">Contact Us Anytime</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                <table class="tbl-custom">
                  <tr style="background-color:#0cb14b;">
                    <td style="font-weight:bold;width:50%">Call Us</td>
                    <td style="font-weight:bold">Digital Connect With Us</td>
                  </tr>
                  <tr>
                    <td>Customer Services Team</td>
                    <td rowspan="4" style="vertical-align:middle;">Email us at<br/><a href="mailto:support@mcash.my">support@mcash.my</a></td>
                  </tr>
                  <tr>
                    <td>Monday - Friday: 09:00 am to 18:00 pm (Excluding Public Holiday)</td>
                  </tr>
                  <tr>
                    <td>03-9134 7455</td>
                  </tr>
                  <tr>
                    <td>010-320 3948(What’s App)</td>
                  </tr>
                </table>
                <br/><br/>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div id="faq-page-7">
        <div class="faq-item-header">
          OTHER INFORMATION
        </div>
        <div class="faq-item-body">
          <table class="tbl-body">
            <tr>
              <td style="font-weight:bold;width:10%">Q1.</td>
              <td style="font-weight:bold">Is my MGold Account protected by Perbadanan Insurans Deposit Malaysia (PIDM)?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
              MGold account is a non-principal guaranteed product, non-interest-bearing account and it is NOT protected by PIDM. 
                <br/><br/>
              </td>
            </tr>
            <tr>
              <td style="font-weight:bold;">Q2.</td>
              <td style="font-weight:bold">Is there any risk exposed to MGold account?</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                <br/>
                <table class="tbl-body">
                  <tr>
                    <td style="width:13%"><span style="font-weight:bold">1)</span></td>
                    <td>
                      <span style="font-weight:bold">Investment Risk</span>
                      <br/>
                      <p>
                        The net return on MGold shall depend on the market conditions of the gold market which is volatile. The returns in gold trading are uncertain and there is 
                        a risk of earning no returns and/or the possibility of incurring losses. 
                      </p>
                    </td>
                  </tr>
                  <tr>
                    <td><span style="font-weight:bold">2)</span></td>
                    <td>
                      <span style="font-weight:bold">Pricing Risk</span>
                      <br/>
                      <p>
                        A decline in the value of a security or an investment portfolio excluding a downturn in the market could be happen, due to multiple factors. Investors can employ 
                        a number of tools and techniques to hedge price risk, ranging from relatively conservative decisions.
                      </p>
                    </td>
                  </tr>
                  <tr>
                    <td><span style="font-weight:bold">3)</span></td>
                    <td>
                      <span style="font-weight:bold">Market Risk</span>
                      <br/>
                      <p>
                      MGold customer are advice to manage the risk of losses on financial investments caused by adverse price movements. Examples of market risk are: changes in gold prices, 
                        interest rate moves or foreign exchange fluctuations.
                      </p>
                    </td>
                  </tr>
                </table>
                Note:
                <br/>
                *You are advised to read and understand the Terms and Conditions of MGold Account and register the MGold based on your own judgment and/or on independent advice obtained.
                <br/><br/>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div id="faq-footer">
        <div class="ace-group-logo">
        </div>
      </div>
      <br/><br/><br/>
    </div>
		  <div id="faq-pager2" style="width:100;text-align:center;display:none;">
        <table style="width:100%;">
          <tr>
            <td style="float:right;padding-right:5px">
              <button id="btn-previous2" class="btn-pager btn-previous" disabled="false"><span style="font-weight:bold"><</span></button>
            </td>
            <td style="width:30%;vertical-align:middle;background-color:white;">
              <label id="lbl-pager2" class="lbl-pager" style="min-width:50px"></label>
            </td>
            <td style="float:left;padding-left:5px">
              <button id="btn-next2" class="btn-pager btn-next"><span style="font-weight:bold">></span></button>
            </td>
          </tr>
        </table>
      </div>
      <div class="link-paging2" style="text-align:center">
        <label>Page:</label>
        &nbsp;
        <u><a class="link-number2" href="javascript:void(0)">1</a></u>
        &nbsp;&nbsp;
        <a class="link-number2" href="javascript:void(0)">2</a>
        &nbsp;&nbsp;
        <a class="link-number2" href="javascript:void(0)">3</a>
        &nbsp;&nbsp;
        <a class="link-number2" href="javascript:void(0)">4</a>
        &nbsp;&nbsp;
        <a class="link-number2" href="javascript:void(0)">5</a>
        &nbsp;&nbsp;
        <a class="link-number2" href="javascript:void(0)">6</a>
        &nbsp;&nbsp;
        <a class="link-number2" href="javascript:void(0)">7</a>
      </div>
      <br/><br/>
    <div id="my_pdf_viewer" style="display:none">
        <div id="canvas_container">
            <canvas id="pdf_renderer"></canvas>
        </div>
 
        <div id="navigation_controls">
            <button id="go_previous">Previous</button>
            <input id="current_page" value="1" type="number"/>
            <button id="go_next">Next</button>
        </div>
 
        <div id="zoom_controls">  
            <button id="zoom_in">+</button>
            <button id="zoom_out">-</button>
        </div>
    </div>
	<script>
    $("a[class=link-number]").click(function(e){
        if($(this).parent().is("u")){
          //do nothing
        }
        else{
          $("a[class=link-number]").each(function(e){
            if($(this).parent().is("u")){
              $(this).unwrap();
            }
          });

          $(this).wrap("<u></u>");
          var x = $(this).html();
          $("div[id*=faq-page-]").attr("style","display:none");
          $("#faq-page-"+x).attr("style","display:block");

          $("a[class=link-number2]").each(function(e){
            if($(this).parent().is("u")){
              $(this).unwrap();
            }

            if($(this).html() == x){
              $(this).wrap("<u></u>");
            }
          });


        }      
    });

    $("a[class=link-number2]").click(function(e){
        if($(this).parent().is("u")){

        }
        else{
          $("a[class=link-number2]").each(function(e){
            if($(this).parent().is("u")){
              $(this).unwrap();
            }
          });

          $(this).wrap("<u></u>");
          var x = $(this).html();
          $("div[id*=faq-page-]").attr("style","display:none");
          $("#faq-page-"+x).attr("style","display:block");

          $("a[class=link-number]").each(function(e){
            if($(this).parent().is("u")){
              $(this).unwrap();
            }

            if($(this).html() == x){
              $(this).wrap("<u></u>");
            }
          });
        }      
    });
  </script>			
			
		

		
	</div>
	
</body>
</html>