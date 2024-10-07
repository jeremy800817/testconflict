<!doctype html>
<html lang="en-gb" dir="ltr">
<head>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_PRODUCT_DISCLOSURE'] ?></title>
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

    $("#back_btn").click(function (){
  window.history.back();
});

  var myState = {
            pdf: null,
            currentPage: 1,
            zoom: 1
        }
      
        pdfjsLib.getDocument('GoGoldPDSfinal.pdf').then((pdf) => {
      
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

  /*$("#hidpager").val(1);
  $("[id*=pd-page-]").attr("style","display:none");
  $("#pd-page-"+$("#hidpager").val()).attr("style","display:block");
  $(".btn-previous").attr("disabled","true");*/
  $("#pd-pager").attr("style","display:none");
  $("#pd-pager2").attr("style","display:none");

 $(".btn-next").click(function(e){
   var i = $("#hidpager").val();
   $("#pd-page-"+i).attr("style","display:none");
   i++;
   $("#hidpager").val(i);
   $("#pd-page-"+i).attr("style","display:block");

   $(".lbl-pager").html(i+"/4");
   
   if($(this).attr("id")=="btn-next2"){
    window.scroll({
      top: 0, 
      left: 0, 
      behavior: 'smooth'
    });
   }

   if(i == 4){
    $(".btn-next").attr("disabled","true");
   }
   else{
    $(".btn-next").removeAttr("disabled");
    $(".btn-previous").removeAttr("disabled");
   }
 });

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

 $(".btn-previous").click(function(e){
   var i = $("#hidpager").val();
   $("#pd-page-"+i).attr("style","display:none");
   i--;
   $("#hidpager").val(i);
   $("#pd-page-"+i).attr("style","display:block");

   $(".lbl-pager").html(i+"/4");

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
      
      @media only screen and (max-width: 660px) {
        .pd-item-header {
          text-align:center;
          font-weight:bold;
          border:5px solid #0cb14b;
          padding-top:5px;
          padding-bottom:5px;
          background-color:#0cb14b;
        }

        .pd-item-body {
          border:5px solid transparent;
          padding-left:2%;
          padding-right:2%;
        }

        .pd-item-body-2 {
          border:5px solid transparent;
        }

        .tbl-common {
          margin-left:3%;
          margin-right:3%;
          text-align:justify;
        }

        #tbl-prod-spec{
          width:100%;
        }

        #tbl-prod-spec thead {
          font-weight:bold;
          text-align:center;
        }

        #tbl-prod-spec td {
          border:1px solid black;
          padding-left:1%;
          padding-right:1%;
        }

        .tbl-custom td{
          border:1px solid black;
          text-align:center;
        }

        #html-pd-wrapper{
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

      @media only screen and (min-width: 661px) {
        .pd-item-header {
          text-align:center;
          font-weight:bold;
          border:5px solid #0cb14b;
          padding-top:5px;
          padding-bottom:5px;
          background-color:#0cb14b;
        }

        .pd-item-body {
          border:5px solid transparent;
          padding-left:2%;
          padding-right:10%;
        }

        .pd-item-body-2 {
          border:5px solid transparent;
        }

        .tbl-common {
          width:100%;
          margin-left:3%;
          margin-right:3%;
          text-align:justify;
        }

        #tbl-prod-spec{
          width:100%;
        }

        #tbl-prod-spec thead {
          font-weight:bold;
          text-align:center;
        }

        #tbl-prod-spec td {
          border:1px solid black;
          padding-left:1%;
          padding-right:1%;
        }

        .tbl-custom {
          width:100%;
        }
        .tbl-custom td{
          border:1px solid black;
          text-align:center;
        }

        #html-pd-wrapper{
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
      }

      .btn-pager {
        min-width:50px;
        /*background-color:#a3e0e3;*/
        background-color:#ffc000;
        border:5px solid #ffc000;
      }

      .btn-pager:active {
        opacity:0.8;
        transform: translateY(0.5px);
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
				<a href="DeliveryRefundPolicies.php"><?php echo $lang['RefundPolicy'] ?></a>
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
       	<!-- <a href="DeliveryRefundPolicies.php"><?php echo $lang['RefundPolicy'] ?></a> -->
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
		<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="productDisclosure.php?lang=en">Eng</a></li>
        <li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="productDisclosure.php?lang=bm">BM</a></li>
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
		<div class="inside-title"><?php echo $lang['ProductDisclosure_HEADER'] ?></div>
    <a class="back" href="index.php"></a>
		
	</div>
	<div class="page-content" style="margin-left:5%;margin-right:5%">
      <div id="pd-pager" style="width:100;text-align:center">
        <input type="hidden" id="hidpager" name="hidpager" value="1" />
        <br/>
        <table style="width:100%;">
          <tr>
            <td style="float:right;width:40%">
              <button id="btn-previous1" class="btn-pager btn-previous" style="min-width:50px" disabled="false"><span style="font-weight:bold"><</span></button>
            </td>
            <td style="width:20%;vertical-align:middle;background-color:white;">
              <label id="lbl-pager" class="lbl-pager" style="min-width:50px">1/4</label>
            </td>
            <td style="float:left;width:40%">
              <button id="btn-next1" class="btn-pager btn-next" style="min-width:50px"><span style="font-weight:bold">></span></button>
            </td>
          </tr>
        </table>
      </div>
    <div id="html-pd-wrapper">
      <br/>
      <div class="gogold-logo">
      </div>
      <!--<br/><br/><br/><br/>-->
      <div id="pd-header">
        <div id="pd-reminder">
          <p style="text-align:center;font-size:10px;">
            <span style="font-weight:bold;"></span>
            <br/><br/>
          </p>
          <p style="text-align:justify;font-size:10px">
            Read this Product Disclosure Sheet before you decide to take the MGold Account product. Be sure to also read the Terms and Conditions of the product. Seek clarification 
            from us if you do not understand any part of this document or the general terms.
          </p>
        </div>
      </div>
      <br/>
      <div id="pd-page-1">
        <div id="pd-the-product">
          <div class="pd-item-header" style="margin-left:10%;margin-right:10%;">
            THE PRODUCT
          </div>
          <br/>
          <div class="pd-item-body">
            <br/>
            <table class="tbl-common">
              <tr>
                <td style="width:10%">1.</td>
                <td>
                MGold is a Shariah compliant digital gold product which offers gold in small
distributable quantity for customer to buy and sell back with the actual physical gold
is stored in a well-established security vault in Malaysia.
                </td>
              </tr>
              <tr>
                <td>2.</td>
                <td>
                MGold provides live gold price for customer to buy and sell.
                </td>
              </tr>
              <tr>
                <td>3.</td>
                <td>
                Customer would be able to request for a physical gold in minted gold bar form by
paying necessary fulfillment cost.
                </td>
              </tr>
              <tr>
                <td>4.</td>
                <td>
                  Parties involved in the Gold transaction:
                  <table class="tbl-common">
                    <tr>
                      <td style="width:10%">a)</td>
                      <td>
                        <span style="font-weight:bold;">As Buyer</span> - The customer purchases the gold from the MGold
                      </td>
                    </tr>
                    <tr>
                      <td>b)</td>
                      <td>
                        <span style="font-weight:bold;">As Seller</span> - The customer sells the gold to the MGold
                      </td>
                    </tr>
                  </table>
                  <br/>
                  <span style="font-weight:bold">Ace Capital Growth Sdn Bhd (The Company)</span>
                  <table class="tbl-common">
                    <tr>
                      <td style="width:10%">a)</td>
                      <td>
                        <span style="font-weight:bold;">As Seller</span> - The Company sells the gold to the customer.
                      </td>
                    </tr>
                    <tr>
                      <td>b)</td>
                      <td>
                        <span style="font-weight:bold;">As Buyer</span> - The Company buys back the gold from the customer.
                      </td>
                    </tr>
                    <tr>
                      <td>c)</td>
                      <td>
                        <span style="font-weight:bold;">As Custodian</span> - The Company serves as a custodian of the MGold purchased by
the clients from the MCash App. MGold customer’s representative to custody
the gold that being purchase by the customer
                      </td>
                    </tr>
                  </table>
                  <br/>
                  <span style="font-weight:bold">As Gold Supplier</span>
                  <br/>
                  The company procure 999.9% LBMA accredited Gold Bars for sale via MGold. – MGold is
the product – selling platform is MCash App – ACE please check as it should be
consistent
                </td>
              </tr>
            </table>
            <br/></br/>
          </div>
        </div>
        <br/><br/>
        <div id="pd-eligibility">
          <div class="pd-item-header" style="margin-left:10%;margin-right:10%;">
            ELIGIBILITY
          </div>
          <br/>
          <div class="pd-item-body" style="text-align:center">
            <br/>
            Individual/Malaysian 18-year old and above (Malaysian and non-Malaysian)
            <br/><br/>
          </div>
        </div>
      </div>
      <br/><br/>
      <div id="pd-page-2">
        <div id="pd-product-specification">
          <div class="pd-item-header" style="margin-left:10%;margin-right:10%;">
            PRODUCT SPECIFICATION
          </div>
          <br/>
          <div class="pd-item-body-2" style="text-align:justify">
            <table id="tbl-prod-spec">
              <thead style="background-color:#0cb14b;">
                <tr>
                  <td style="width:50%;">Items</td>
                  <td>Description</td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-weight:bold;background-color:#bfbfbf;">Initial Purchase</td>
                  <td>MYR25.00</td>
                </tr>
                <tr>
                  <td style="font-weight:bold;background-color:#bfbfbf;">Subsequent Purchase</td>
                  <td>MYR25.00</td>
                </tr>
                <tr>
                  <td style="font-weight:bold;background-color:#bfbfbf;">Minimum Balance</td>
                  <td>MYR25.00</td>
                </tr>
                <tr>
                  <td style="font-weight:bold;background-color:#bfbfbf;">Gold Purity</td>
                  <td>999.9%</td>
                </tr>
                <tr>
                  <td style="font-weight:bold;background-color:#bfbfbf;">Currency</td>
                  <td>Ringgit Malaysia (MYR)</td>
                </tr>
                <tr>
                  <td style="font-weight:bold;background-color:#bfbfbf;">Method of Purchase & Sell</td>
                  <td>Buy and sell can be performed in MGold – please
check for consistency – MGold is product name;
platform is MCash App
</td>
                </tr>
                <tr>
                  <td style="font-weight:bold;text-align:left;background-color:#bfbfbf;">Physical Gold Denomination (Fulfillment)</td>
                  <td>0.5 gram, 1 gram, 2.5 gram, 5 gram, 10 gram, 50gram, 100 gram, 1 Dinar (4.25 gram), 5 Dinar (21.25 gram)</td>
                </tr>
                <tr>
                  <td style="font-weight:bold;background-color:#bfbfbf;">Transaction Hour</td>
                  <td>8.30am to 11.59pm Monday to Sunday, including national public holidays in accordance to Federal Territory calendar</td>
                </tr>
                <tr>
                  <td style="font-weight:bold;background-color:#bfbfbf;">Statement/History</td>
                  <td>By Monthly</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <br/><br/>
        <div id="pd-fees-and-charges">
          <div class="pd-item-header" style="margin-left:10%;margin-right:10%;">
            FEES AND CHARGES
          </div>
          <br/>
          <div class="pd-item-body" style="text-align:justify">
            <br/>
            <table class="tbl-common">
              <tr>
                <td style="width:10%">
                  <span style="font-weight:bold">1.</span>
                </td>
                <td>
                  <span style="font-weight:bold">Storage Fees</span>
                  <table class="tbl-common">
                    <tr>
                      <td style="width:10%">a)</td>
                      <td>
                      Storage fee is 1% per annum based on your daily gold holding. The storage
fee will be accrued daily and debited from the account monthly at the end of
the month or MYR1.00 equivalent in gram subject to whichever is higher or
upon closure of account. For account closure, the storage fee would be
calculated on a pro-rata basis.
                      </td>
                    </tr>
                    <tr>
                      <td>b)</td>
                      <td>
                      This storage fee is calculated at such rate and subject to such minimum
charges per month, as may be determine from time to time. ACE will notify
you by posting on MCash Platform by at least five (5) business days prior to
the effective date of change.
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <span style="font-weight:bold">2.</span>
                </td>
                <td>
                  <span style="font-weight:bold">Payment Cost</span> : MYR1.20 per transaction for (FPX) only for successful transaction.
                </td>
              </tr>
            <!--</table>
            <br/><br/>
          </div>
        </div>
      </div>
      <div id="pd-page-3">
        <div id="pd-fees-and-charges-2">
          <div class="pd-item-body" style="text-align:justify">
            <table class="tbl-common">-->
              <tr>
                <td style="width:10%">
                  <span style="font-weight:bold">3.</span>
                </td>
                <td>
                  <span style="font-weight:bold">Physical Gold Fulfilment Cost :</span>
                  <br/><br/>
                  Making Charges
                  <br/>
                    <table class="tbl-custom" style="width:100%;">
                      <tr style="background-color:#0cb14b">
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
                        <td style="width:10%">a)</td>
                        <td style="text-align:left">
                          The fulfilment fee is referring to Making Charges: (Premium/Pcs + Handling Charges/Pcs + Insurance/Pcs) + Packaging & Shipping;
                        </td>
                      </tr>
                      <tr>
                        <td style="width:10%">b)</td>
                        <td style="text-align:justify">
                          Packaging and shipment cost charge once only;
                        </td>
                      </tr>
                      <tr>
                        <td>c)</td>
                        <td style="text-align:justify">
                          ACE reserves the right to revise the fulfilment cost from time to time without prior notice. The changes will base on the charges imposed by the vendor and service 
                          providers;
                        </td>
                      </tr>
                      <tr>
                        <td>d)</td>
                        <td style="text-align:justify">
                          Maximum for physical gold fulfilment of minted gold bar is 100 gram or maximum 30 pieces each time for each parcel;
                        </td>
                      </tr>
                      <tr>
                        <td>e)</td>
                        <td style="text-align:justify">
                          It applied to both West Malaysia and East Malaysia delivery is available in Malaysia only.
                        </td>
                      </tr>
                    </table>
                </td>
              </tr>
            </table>
            <br/>
            <!--<br/><br/>
          </div>
        </div>
      </div>
      <div id="pd-page-4">
        <div id="pd-fees-and-charges-3">
          <div class="pd-item-body" style="text-align:justify">-->
            
            <u>For illustration physical gold fulfilment only.</u>
            <br/>
            <span style="font-weight:bold">Scenario 1:</span>&nbsp;Customer convert 2 gram of physical gold – 2 pieces 1 gram
            <br/>
            Calculation :
            <br/>
            (Making Charges MYR43.00) x 2 pieces + (Packaging & Shipment MYR15.00)
            <br/>
            Total customer to pay : MYR101.00
            <br/><br/>
            <span style="font-weight:bold">Scenario 2:</span>&nbsp;Customer convert multiple of demolition - (in total 100 grams) 50 gram x 1pc, 10 gram x 3pcs, 5 gram x 4pcs
            <br/>
            Calculation:
            <br/>
            Denominations 50 gram – (Making Charges MYR510.00) x 1 piece = MYR510.00
            <br/>
            Denominations 10 gram – (Making Charges MYR125.00) x 3 pieces = MYR375.00
            <br/>
            Denominations 5 gram – (Making Charges MYR80.00) x 4 pieces = MYR320.00
            <br/>
            Total = MYR1,205.00 + (Packaging & Shipment MYR15.00)
            <br/>
            Total customer to pay : MYR1,220.00
            <br/><br/><br/>
          </div>
          <div style="text-align:center;"><span style="font-weight:bold">Note:</span> The price displayed is includes Making Charges, Courier, Takaful/Insurance</div>
        </div>
        <br/><br/>
        <div id="pd-contact-us">
          <div class="pd-item-header" style="margin-left:10%;margin-right:10%">
            CONTACT US
          </div>
          <br/>
          <div class="pd-item-body" style="text-align:center;width:100%">
          <!-- Contact Number: 03-9213 0687; What’s App Number: 011-3593 6090 / 011-3594 4056 (Monday to Friday excludes Public Holiday - 9am to 6pm)
            <br/>
            Email: careline@MCash.my
            <br/><br/> -->
            <table class="tbl-custom" style="width:100%">
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
          </div>
        </div>
        <br/><br/>
      </div>
      <br/><br/>
      <div id="pd-footer">
        <div class="ace-group-logo">
        </div>
      </div>
      <br/><br/><br/>
    </div>
		  <div id="pd-pager2" style="width:100;text-align:center">
        <table style="width:100%;">
          <tr>
            <td style="float:right;width:40%">
              <button id="btn-previous2" class="btn-pager btn-previous" style="min-width:50px" disabled="false"><span style="font-weight:bold"><</span></button>
            </td>
            <td style="width:20%;vertical-align:middle;background-color:white;">
              <label id="lbl-pager2" class="lbl-pager" style="min-width:50px">1/4</label>
            </td>
            <td style="float:left;width:40%">
              <button id="btn-next2" class="btn-pager btn-next" style="min-width:50px"><span style="font-weight:bold">></span></button>
            </td>
          </tr>
        </table>
      </div>
      <br/><br/><br/>
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
				
			
		

		
	</div>
	
</body>
</html>