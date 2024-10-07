<!doctype html>
<html lang="en-gb" dir="ltr">
<head>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_TNC'];?></title>
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
      
        pdfjsLib.getDocument('GoGoldTnCfinal.pdf').then((pdf) => {
      
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
     // Set FAQ Setting
   var login = '<?php echo $_SESSION['login'] ?? 'no';?>';
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
  /*$("#hidpager").val(1);
  $("[id*=tnc-page-]").attr("style","display:none");
  $("#tnc-page-"+$("#hidpager").val()).attr("style","display:block");
  $(".btn-previous").attr("disabled","true");*/
  $("#tnc-pager").attr("style","display:none");
  $("#tnc-pager2").attr("style","display:none");

 $(".btn-next").click(function(e){
   var i = $("#hidpager").val();
   $("#tnc-page-"+i).attr("style","display:none");
   i++;
   $("#hidpager").val(i);
   $("#tnc-page-"+i).attr("style","display:block");

   $(".lbl-pager").html(i+"/8");
   
   if($(this).attr("id")=="btn-next2"){
    window.scroll({
      top: 0, 
      left: 0, 
      behavior: 'smooth'
    });
   }

   if(i == 8){
    $(".btn-next").attr("disabled","true");
   }
   else{
    $(".btn-next").removeAttr("disabled");
    $(".btn-previous").removeAttr("disabled");
   }
 });

 $(".btn-previous").click(function(e){
   var i = $("#hidpager").val();
   $("#tnc-page-"+i).attr("style","display:none");
   i--;
   $("#hidpager").val(i);
   $("#tnc-page-"+i).attr("style","display:block");

   $(".lbl-pager").html(i+"/8");

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
        #html-tnc-wrapper{
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
        #html-tnc-wrapper{
          border:1px solid black;
          padding-left: 5%;
          padding-right: 5%;
          padding-top: 2%;
          margin-top:2%;
          margin-bottom:5%;
          background: transparent url(./img/bg/bg-pdf.jpg) top left/contain repeat-y;
          background-color:white;
          background-size:20px ;
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
      .tbl-bordered td {
        border:1px solid black;
        padding-right:2%;
        padding-left:1%;
        text-align:justify;
      }

      .tbl-bordered-2 td {
        border:1px solid black;
        padding-right:2%;
        padding-left:1%;
        text-align:center;
      }

      .tbl-non-bordered td {
        border: none;
        text-align:justify;
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
		<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?? 'User'; ?></li>
		<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="Tnc.php?lang=en">Eng</a></li>
        <li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="Tnc.php?lang=bm">BM</a></li>
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
		<div class="inside-title"><?php echo $lang['Terms_and_Conditions'] ?></div>
    <a class="back" href="index.php"></a>	
	</div>
	<div class="page-content" style="margin-left:5%;margin-right:5%">
      <br/>
      <div id="tnc-pager" style="width:100;text-align:center">
        <input type="hidden" id="hidpager" name="hidpager" value="1" />
        <table style="width:100%;">
          <tr>
            <td style="float:right;width:40%">
              <button id="btn-previous1" class="btn-pager btn-previous" disabled="false"><span style="font-weight:bold"><</span></button>
            </td>
            <td style="width:20%;vertical-align:middle;background-color:white;">
              <label id="lbl-pager" class="lbl-pager" style="min-width:50px">1/8</label>
            </td>
            <td style="float:left;width:40%">
              <button id="btn-next1" class="btn-pager btn-next"><span style="font-weight:bold">></span></button>
            </td>
          </tr>
        </table>
      </div>
  <div id="html-tnc-wrapper">
    <br/>
      <div id="tnc-header">
        <div class="gogold-logo">
        </div>
        <br/><br/>
        <div id="tnc-title" style="width:100%;text-align:center">
          <p style="color:#ffc000;font-weight:bold"></p>
        </div>
      </div>
      <div id="tnc-page-1">
        <div id="tnc-intro">
          <div id="tnc-intro-header" style="font-weight:bold">
            <p>1. INTRODUCTION</p>
          </div>
          <div id="tnc-intro-body">
            <p id="tnc-intro-item" style="margin-left:5%">
              <table style="width:100%">
                <tr>
                  <td style="width:13%">1.1)</td>
                  <td style="text-align:justify;padding-bottom:5%;">
                    Welcome to the “Platform”. The customers are advised to read and understand these Terms and Conditions before using the services.
                  </td>
                </tr>
                <tr>
                  <td>1.2)</td>
                  <td style="text-align:justify">
                    Ace Capital Growth Sdn. Bhd. may revise these Terms and Conditions of use from time to time, the changes will be effective when posted on the Platform with no other 
                    notices provided. If you continue to use the Platform or communicate with us, you will 
                    be deemed to have agreed to the changes upon our publication on the Platform.
                  </td>
                </tr>
              </table>
            </p>
          </div>
        </div>
        <br />
        <div id="tnc-interpretation">
          <div id="tnc-interpretation-header" style="font-weight:bold">
            <p>2. INTERPRETATION</p>
          </div>
          <div id="tnc-interpretation-body">
            <p id="tnc-interpretation-item">
              In these Conditions:
              <br/>
              <table style="width:100%;" class="tbl-bordered">
                <tr>
                  <td style="width:30%;background-color:#0cb14b;font-weight:bold">Items<br /><br/></td>
                  <td style="background-color:#0cb14b;font-weight:bold">Meaning</td>
                </tr>
                <tr>
                  <td style="background-color:#0cb14b;font-weight:bold">Currency</td>
                  <td>
                    Transaction in Malaysia Ringgit (MYR) only.
                  </td>
                </tr>
                <tr>
                    <td style="background-color:#0cb14b;font-weight:bold;">Customer (you/your)</td>
                    <td>
                      Any individual who transacts using the Platform for buying and/or selling 
                      back the Digital gold or any other party as outlined in these Terms.
                    </td>
                </tr>
                <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Custodian</td>
                    <td>
                      ACE acts as the custodian the partner’s gold product to identify, arrange and pay to 
                      security vault service provider to safe keep the physical gold bars.
                    </td>
                </tr>
                <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Force Majeure</td>
                    <td>
                      Any events or situation that is beyond the reasonable control of the ACE 
                      and shall include, without limitation:
                      <br />
                      <table style="margin-left:5%" class="tbl-non-bordered">
                        <tr>
                          <td style="width:10%">a.</td>
                          <td>
                            Earthquakes, flood, fire, plague, pandemic and other natural
                            disaster;
                          </td>
                        </tr>
                        <tr>
                          <td>b.</td>
                          <td>
                            Terrorism, riots, civil commotion or disturbance, war (whether 
                            declare or not) and strikes;
                          </td>
                        </tr>
                        <tr>
                          <td>c.</td>
                          <td>
                            Unauthorized access of computer data and storage device, virus 
                            attacks, breach of security and encryption and any other similar 
                            events.
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Security</td>
                    <td>
                      The Digital gold sold under the platform will be backed by casted gold bars weighing 1,000 gram/1 kg will be kept in the dedicate security vault on behalf of the customers.
                    </td>
                  </tr>
              <!--</table>
            </p>
          </div>
        </div>
      </div>
      <div id="tnc-page-2">
        <div id="tnc-interpretation-2">
          <div id="tnc-interpretation-header-2" style="display:none;">
            <p></p>
          </div>
          <div id="tnc-interpretation-body-2">
            <p id="tnc-interpretation-item-2" style="margin-left:5%">
              <table style="width:100%" class="tbl-bordered">
                  <tr>
                    <td style="width:30%;background-color:#39bac1;font-weight:bold">Items (Cont'd)<br /><br /></th>
                    <td style="background-color:#39bac1;font-weight:bold">Meaning (Cont'd)</th>
                  </tr>-->
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Gold Account</td>
                    <td>
                      Account created on the Platform for purposes of purchase and sale of the Digital gold. 
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Gold Bar Fulfillment</td>
                    <td>
                      Refers to a redemption of your ownership in the digital gold account to the Physical minted gold bar and it does not involve any buy and/or sell transactions.
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Gold Supplier (we/us)</td>
                    <td>
                      Ace Capital Growth Sdn. Bhd. Company Registration No. 200901037559 (ACE), a subsidiary of Ace Innovate Asia Berhad.
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Gold Amount</td>
                    <td>
                      All transaction is in Ringgit Malaysia which shall be equal to the quantity of gold to be transacted multiplied by the live market gold price quoted by ACE.
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Initial Purchase</td>
                    <td>
                      First-time purchase of Digital gold that made by you during the gold account opening.
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">LBMA</td>
                    <td>
                      The London Bullion Market Association (LBMA), established in 1987, is the international trade association representing the global Over the Counter 
                      (OTC) bullion market, and defines itself as "the global authority on precious metals". It has a membership of approximately 150 firms globally, including traders, 
                      refiners, producers, miners, fabricators as well as those providing storage and secure carrier services.
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Transaction History </td>
                    <td>
                      Displays transaction history of purchase and sale of Digital gold and Physical minted gold bar fulfilment.
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Security Vault</td>
                    <td>
                      Safeguards G4S Malaysia (SG4S) appointed by ACE to safe keep the physical gold bars.
                    </td>
                  </tr>
                  <tr>
                    <td style="background-color:#0cb14b;font-weight:bold">Terms</td>
                    <td>
                      Terms and Conditions.
                    </td>
                  </tr>
              </table>
            </p>
          </div>
        </div>
        <br />
        <div id="tnc-gogold-account">
          <div id="tnc-gogold-account-header" style="font-weight:bold">
            <p>3. DIGITAL GOLD ACCOUNT</p>
          </div>
          <div id="tnc-gogold-account-body">
            <p id="tnc-gogold-account-item">
              <table style="width:100%;text-align:justify">
                <tr>
                  <td style="width:13%;">
                    <span style="font-weight:bold;">3.1)</span>
                  </td>
                  <td>
                    <span style="font-weight:bold;">Account Opening</span>
                    <br />
                    To be eligible to register a customer account, you must meet the following criteria and represent and warrant that:
                    <br /><br />
                    If you are an individual, you:
                    <br />
                    <table style="margin-left:5%" class="tbl-non-bordered">
                      <tr>
                        <td style="width:10%">a)</td>
                        <td>
                          are 18 years old and above;
                        </td>
                      </tr>
                      <tr>
                        <td>b)</td>
                        <td>
                          Malaysia Citizen and Non-citizen;
                        </td>
                      </tr>
                      <tr>
                        <td>c)</td>
                        <td>
                          agreed to us carrying out personal identity and residency checks for the purposes of anti-money laundering; you shall provide us with documentation 
                          such as NRIC/valid passport during registration;
                        </td>
                      </tr>
                      <tr>
                        <td>d)</td>
                        <td>
                        	have an existing account;
                        </td>
                      </tr>
                      <tr>
                        <td>e)</td>
                        <td>
                          will only maintain one (1) digital gold account at any given time; 
                        </td>
                      </tr>
                      <tr>
                        <td>f)</td>
                        <td>
                          will not infringe any rights of our intellectual property rights.
                        </td>
                      </tr>
                    </table>
                    <br />
                    We reserve the right to reject an application and freeze or close a customer account at our sole discretion and without reason.<br /><br/>
                  </td>
                </tr>
                <tr>
                  <td>
                    <span style="font-weight:bold;">3.2)</span>
                  </td>
                  <td>
                    <span style="font-weight:bold;">Third Party Access</span>
                    <br />
                    You are encouraged to fill up your personal representative/successor details when registering your account in case of unforeseen event happened. 
                    You should inform your personal representative/successor, he/she may be subject to the same criteria as a customer including being subject to personal identity and residency checks.
                  </td>
                </tr>
              </table>
            </p>
          </div>
        </div>
      </div>
      <div id="tnc-page-3">
        <div id="tnc-gogold-account-2">
          <div id="tnc-gogold-account-header-2">
            <p></p>
          </div>
          <div id="tnc-gogold-account-body-2">
            <p id="tnc-gogold-account-item-2">
              <table style="width:100%;text-align:justify">
                <tr>
                  <td style="width:13%;">
                    <span style="font-weight:bold;">3.3)</span>
                  </td>
                  <td>
                    <span style="font-weight:bold;">Dormant Account</span>
                    <br />
                    A dormant customer account is the one that has a nil gold balance in the account and has not been any debit or credit entry in the customer’s account in 
                    the last 6 months, excluding of storage fees. Customer will receive a notification as a reminder to active the account or option to close the account.
                    <br /><br />
                  </td>
                </tr>
                <tr>
                  <td>
                    <span style="font-weight:bold;">3.4)</span>
                  </td>
                  <td>
                    <span style="font-weight:bold;">Account Closing</span>
                    <br />
                    <table style="margin-left:5%" class="tbl-non-bordered">
                      <tr>
                        <td style="width:10%">a)</td>
                        <td>
                          An account may only be closed if there are no outstanding gold balances remaining in that account.
                        </td>
                      </tr>
                      <tr>
                        <td>b)</td>
                        <td>
                          Any pending storage fees for settlement will deduct from gold account before account closure.
                        </td>
                      </tr>
                      <tr>
                        <td>c)</td>
                        <td>
                          In the case of deceased customers, the closing account can only be carrying out by the personal representative/successor.
                        </td>
                      </tr>
                      <tr>
                        <td>d)</td>
                        <td>
                          To close account, please contact our customer services team.
                        </td>
                      </tr>
                      <tr>
                        <td>e)</td>
                        <td>
                          ACE reserves the right to close the customer’s account if ACE do not receive any feedback from customer or there are dormant more than 6 months.
                        </td>
                      </tr>
                      <tr>
                        <td>f)</td>
                        <td>
                          The process for account closure will take 2-3 business days.
                        </td>
                      </tr>
                      <tr>
                        <td>g)</td>
                        <td>
                          Customer will receive a notification from ACE in the event of account closure.
                        </td>
                      </tr>
                    </table>
                    <br />
                  </td>
                </tr>
                <tr>
                  <td>
                    <span style="font-weight:bold;">3.5)</span>
                  </td>
                  <td>
                    <span style="font-weight:bold;">Personal Data</span>
                    <br />
                    Customer hereby express consent and authorize ACE to disclose your financial condition, details of digital gold account and relationship for the following 
                    uses of your personal data:
                    <br />
                    <table style="margin-left:5%" class="tbl-non-bordered">
                      <tr>
                        <td style="width:10%">a)</td>
                        <td>
                          Authorities in Malaysia and elsewhere for the purpose of complying with legal, regulatory, compliance and risk management requirements including for compliance 
                          with US’ Foreign Account Tax Compliance Act (“FATCA”) and Organization for 
                          Economic Cooperation and Development’s Common ReportingStandards (“CRS”);
                        </td>
                      </tr>
                      <tr>
                        <td>b)</td>
                        <td>
                          BNM’s Credit Bureau, Cagamas Berhad, Credit Guarantee Corporation Malaysia Berhad and credit bureaus established under the Credit Reporting Agencies Act 2010;
                        </td>
                      </tr>
                      <tr>
                        <td>c)</td>
                        <td>
                          Parties, centralized securities depository, registrar, debt collection agents;
                        </td>
                      </tr>
                      <tr>
                        <td>d)</td>
                        <td>
                          Any other person or entity to whom disclosure is permitted or required by any law, regulation or governmental directive.
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </p>
          </div>
        </div>
      </div>
      <br/>
      <div id="tnc-page-4">
        <div id="tnc-prohibited-uses">
          <div id="tnc-prohibited-uses-header" style="font-weight:bold">
            <p>4. PROHIBITED USES</p>
          </div>
          <div id="tnc-prohibited-uses-body">
            <p id="tnc-prohibited-uses-item">
            Customer undertake that you will not breaches any applicable law or regulation:
            <br>
            <table style="width:100%">
              <tr>
                <td style="width:13%">4.1)</td>
                <td style="text-align:justify;">
                  Use digital gold account directly or indirectly for transaction involving any unlawful activity;
                </td>
              </tr>
              <tr>
                <td>4.2)</td>
                <td style="text-align:justify">
                  Concealing or disguising activities that are in fact unlawful;
                </td>
              </tr>
              <tr>
                <td>4.3)</td>
                <td style="text-align:justify">
                  Utilize any fund from digital gold account related money laundering or unlawful purpose activity.
                </td>
              </tr>
            </table>
            </p>
          </div>
        </div>
        <br/>
        <div id="tnc-force-majure">
          <div id="tnc-force-majure-header" style="font-weight:bold">
          <p>5. FORCE MAJURE</p>
          </div>
          <div id="tnc-force-majure-body">
            <p id="tnc-force-majure-item">
              ACE will not be liable to any customer or any third party for any loss or damage in connection with inability use of Platform, loss of anticipated savings/earnings, 
              any indirect or consequential loss or damage, liability, injury, expenses if ACE is unable to perform to provide any services due to Force Majeure.
            </p>
          </div>
        </div>
        <br/>
        <div id="tnc-changes-in-terms-and-conditions">
          <div id="tnc-changes-in-terms-and-conditions-header" style="font-weight:bold">
          <p>6. CHANGES IN TERMS AND CONDITIONS</p>
          </div>
          <div id="tnc-changes-in-terms-and-conditions-body">
            <p id="tnc-changes-in-terms-and-conditions-item">
              ACE reserves the right to change any or all the above Terms whenever deemed necessary.
            </p>
          </div>
        </div>
        <br />
        <div id="tnc-applica-cable-law">
          <div id="tnc-applica-cable-law-header" style="font-weight:bold">
          <p>7. APPLICABLE LAW</p>
          </div>
          <div id="tnc-applica-cable-law-body">
            <p id="tnc-applica-cable-law-item">
              These Terms shall be subject to, governed by and construed inaccordance with the Laws of Malaysia.
            </p>
          </div>
        </div>
        <br />
      </div>
      <div id="tnc-page-5">
        <div id="tnc-purchase-of-gogold">
          <div id="tnc-purchase-of-gogold-header" style="font-weight:bold">
          <p>8. PURCHASE OF DIGITAL GOLD</p>
          </div>
          <div id="tnc-purchase-of-gogold-body">
            <p id="tnc-purchase-of-gogold-item" style="text-align:justify">
              These terms apply in relation to the purchase of Digital gold, the customer should read and understand the Terms prior to start the purchase of Digital gold.
              <br />
              <table style="width:100%" class="tbl-non-bordered">
                <tr>
                  <td style="width:13%">8.1)</td>
                  <td style="text-align:justify;padding-bottom:5%;">
                    You acknowledge that, with regards to Digital Gold account:
                    <br />
                    <table style="margin-left:5%" class="tbl-non-bordered">
                      <tr>
                        <td style="width:10%">a)</td>
                        <td>
                          you are required to leave a minimum amount of MYR25.00 worth of gold balance at all time;
                        </td>
                      </tr>
                      <tr>
                        <td>b)</td>
                        <td>
                        the gold market is volatile;
                        </td>
                      </tr>
                      <tr>
                        <td>c)</td>
                        <td>
                        losses can be incurred from such an investment;
                        </td>
                      </tr>
                      <tr>
                        <td>d)</td>
                        <td>
                        an investment in gold provides no dividend yield or interest;
                        </td>
                      </tr>
                      <tr>
                        <td>e)</td>
                        <td>
                        gold price would have to rise sufficiently over the investment period in order to yield a profit
or profit;
                        </td>
                      </tr>
                      <tr>
                        <td>f)</td>
                        <td>
                        value of investments and storage fees can vary depending on the fluctuations in international and/or local gold foreign exchange market; and
                        </td>
                      </tr>
                      <tr>
                        <td>g)</td>
                        <td>
                        investment in gold is not guaranteed by ACE nor protected by Perbadanan Insurans Deposit Malaysia (PIDM).
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td>8.2)</td>
                  <td>
                  You are offered to purchase Digital gold worth MYR25.00 (Twenty Five Ringgit Malaysia) or equivalent to approximately 0.1 grams at the live market gold price shown on the 
                  Platform. Live market gold price means that these quotes are linked to the prices of gold in the LBMA in London.
                  </td>
                </tr>
                <tr>
                  <td>8.3)</td>
                  <td>
                    The purchase price quoted on the Platform will be determined by the ACE at its discretion by taking into account all the relevant factors.
                  </td>
                </tr>
                <tr>
                  <td>8.4)</td>
                  <td>
                    You are offered an option to purchase the Digital gold in either Gram or Ringgit Malaysia.
                  </td>
                </tr>
                <tr>
                  <td>8.5)</td>
                  <td>
                  Transaction may be made on a business day from 8.30 am to 11.59 pm, 365 days a year.
                  </td>
                </tr>
                <tr>
                  <td>8.6)</td>
                  <td>
                    Your daily purchase transaction limit is up to 1000 gram/1 kg during the trading hours and/or business day. ACE has the right to cancel and reverse any 
                    of your purchase which is exceed the daily transaction limit.
                  </td>
                </tr>
                <tr>
                  <td>8.7)</td>
                  <td>
                  Ace reserves the rights to vary the daily transaction limit as may be reasonably necessary by posting notices on the Platform.
                  </td>
                </tr>
                <tr>
                  <td>8.8)</td>
                  <td>
                  You are responsible for ensuring that the Price and Transaction are in order before committing to the purchase. Cancellation and refunds will not apply once 
                  a transaction is confirmed.
                  </td>
                </tr>
                <tr>
                  <td>8.9)</td>
                  <td>
                    You acknowledge that the initial purchase and subsequent purchase is subject to the ACE’s discretion from time to time by posting notices on the Platform. 
                    These are the minimum amount of gold you need to purchase in order to start a digital gold account.
                    <br/>
                    <u style="font-weight:bold">Initial Purchase</u>
                    <br/>
                    MYR25.00 or approximately 0.1 grams (its equivalent in gram)
                    <br/>
                    <u style="font-weight:bold">Subsequence Purchase</u>
                    <br/>
                    MYR25.00 or approximately 0.1 grams (its equivalent in gram)
                  </td>
                </tr>
              </table>
            </p>
            <br/>
          </div>
        </div>
      </div>
      <div id="tnc-page-6">
        <div id="tnc-storage">
          <div id="tnc-storage-header" style="font-weight:bold">
            <p>9. STORAGE</p>
          </div>
          <div id="tnc-storage-body">
            <p id="tnc-storage-item">
              <table style="width:100%">
                <tr>
                  <td style="width:13%">9.1)</td>
                  <td style="text-align:justify;">
                  Your legal right, title, interest and property in the digital gold shall be vested with you at all times.
                  </td>
                </tr>
                <tr>
                  <td>9.2)</td>
                  <td style="text-align:justify">
                  You hereby agree to appoint ACE, and we agree to act, as the custodian of the digital gold to identify, arrange and pay with Security vault service provider to safe keep 
                  your physical gold bars.
                  </td>
                </tr>
                <tr>
                  <td>9.3)</td>
                  <td style="text-align:justify">
                  As part of the ACE’s safe-custodian role, the ACE shall ensure the security of the gold bars is protected either through insurance/Takaful.
                  </td>
                </tr>
                <tr>
                  <td>9.4)</td>
                  <td style="text-align:justify">
                  You will not hold liable the ACE liable for any loss, charge and liability resulting from the negligence of the security vault service provider.
                  </td>
                </tr>
                <tr>
                  <td>9.5)</td>
                  <td style="text-align:justify">
                  Ace may at any time in our absolute discretion prescribe the relevant procedures in relation to the deposit and/or withdrawal of the physical gold bars whenever ACE 
                  deems reasonably fit and appropriate.
                  </td>
                </tr>
                <tr>
                  <td>9.6)</td>
                  <td style="text-align:justify">
                  In the event, ACE cease its business or operations or liquidated, ACE would assign a legal entity to act as an administrator to liquidate the gold owned by 
                  customers in Ringgit Malaysia according to market value which the cost of liquidation shall be borne by customers.
                  </td>
                </tr>
              </table>
            </p>
          </div>
        </div>
        <br/>
        <div id="tnc-storage-fee">
          <div id="tnc-storage-fee-header" style="font-weight:bold">
            <p>10. STORAGE FEE</p>
          </div>
          <div id="tnc-storage-fee-body">
            <p id="tnc-storage-fee-item">
              <table style="width:100%">
                <tr>
                  <td style="width:13%">10.1)</td>
                  <td style="text-align:justify;">
                  You agree and undertake to pay, in grams of gold, monthly storage fee of the digital gold account.
                  </td>
                </tr>
                <tr>
                  <td>10.2)</td>
                  <td style="text-align:justify">
                  Storage fee is 1% per annum based on your daily gold holding. The storage fee will be accrued daily and debited from the account monthly at the end of the month or RM1 
                  equivalent in gram subject to whichever is higher or upon closure of account. For account closure, the storage fee would be calculated on a pro-rata basis.
                  <br/><br/>
                  <u>For illustration purpose only.</u>
                  <br/>
                  If you are holding on to 100 grams of gold throughout the month of January, the storage fee would be the higher of:
                  <br/>
                  <table>
                    <tr>
                      <td style="width:5%">-</td>
                      <td>
                      1% x 100 grams x 31/365 days = 0.085 grams; or
                      </td>
                    </tr>
                    <tr>
                      <td>-</td>
                      <td>
                      0.004 grams or RM1.00 (or its equivalent in gram)
                      </td>
                    </tr>
                  </table>
                  <br/>
                  This means that the month storage fee for 100 grams would be 0.085 grams of gold.
                  <br/><br/>
                  This storage fee is calculated at such rate and subject to such minimum charges per month, as may be determine from time to time. ACE will notify you by posting on the 
                  Platform by atleast five (5) business days prior to the effective date of change.
                  </td>
                </tr>
                <tr>
                  <td>10.3)</td>
                  <td style="text-align:justify">
                  The arrangement of storage fee of the Digital gold is terminated if a request to buy back the Digital gold and/or physical gold fulfilment has been made by you pursuant to Terms.
                  </td>
                </tr>
              </table>
            </p>
            <br/>
          </div>
        </div>
      </div>
      <div id="tnc-page-7">
        <div id="tnc-buy-back-of-gogold">
          <div id="tnc-buy-back-of-gogold-header" style="font-weight:bold">
            <p>11. BUY BACK OF DIGITAL GOLD</p>
          </div>
          <div id="tnc-buy-back-of-gogold-body">
            <p id="tnc-buy-back-of-gogold-item">
              <table style="width:100%">
                <tr>
                  <td style="width:13%">11.1)</td>
                  <td style="text-align:justify;">
                  You must be the legal owner of the digital gold and acting on his/her own behalf, not as the agent, representative of another person, individual and organization.
                  </td>
                </tr>
                <tr>
                  <td>11.2)</td>
                  <td style="text-align:justify">
                  The price which the customer is offered is the live market gold price shown on the digital gold Platform.
                  </td>
                </tr>
                <tr>
                  <td>11.3)</td>
                  <td style="text-align:justify">
                    The proceed of sale will be made into the digital gold account using the available payment methods.
                    <br/>
                    <u style="font-weight:bold">Personal Bank Account (FPX)</u>
                    <br/>
                    The amount will be reflected T+2 business day. You are undertaking to pay the processing fee MYR1.20 for each successful transaction if you elect the FPX services.
                    <br/>
                    <u style="font-weight:bold">Digital Gold E-wallet Account</u>
                    <br/>
                    The amount will be reflected in T+4 business days.
                  </td>
                </tr>
                <tr>
                  <td>11.4)</td>
                  <td style="text-align:justify">
                  You shall be responsible to pay:
                  <br/>
                  <table style="width:100%">
                    <tr>
                      <td style="width:10%">a)</td>
                      <td>
                      all taxes or levies payable; and
                      </td>
                    </tr>
                    <tr>
                      <td style="width:10%">b)</td>
                      <td>
                      Zakat for Muslim customer subject to the requirement of nisab and haul.
                      </td>
                    </tr>
                  </table>
                  </td>
                </tr>
                <tr>
                  <td>11.5)</td>
                  <td style="text-align:justify">
                  ACE reserves the right to reject any sale request from any of its customers if the said sale of gold transaction cause the daily aggregate sale of gold transaction 
                  from all of its customers to exceed the Daily aggregated limit or whenever the ACE deems appropriate. The maximum Daily aggregated limit is 1,000 grams and is 
                  subject to the ACE’s discretion from time to time.
                  </td>
                </tr>
              </table>
            </p>
          </div>
        </div>
        <br />
        <div id="tnc-physical-gold-fulfilment">
          <div id="tnc-physical-gold-fulfilment-header" style="font-weight:bold">
            <p>12. PHYSICAL GOLD FULFILMENT</p>
          </div>
          <div id="tnc-physical-gold-fulfilment-body">
            <p id="tnc-physical-gold-fulfilment-item">
              <table style="width:100%">
                <tr>
                  <td style="width:13%">12.1)</td>
                  <td style="text-align:justify;">
                  You are offered to convert available gold balance in the digital gold account to Physical minted gold bars.
                  </td>
                </tr>
                <tr>
                  <td>12.2)</td>
                  <td style="text-align:justify">
                  Each minted bar is bearing the exclusive branded IGR™ logo and sealed in protective Certificate 
                  or certiIGR security packaging that guarantee the 999.9 purity and weight. All the IGR bars are 
                  meet the LBMA Good Delivery Standard to ensure the highest levels of purity and quality.
                  </td>
                </tr>
                <tr>
                  <td>12.3)</td>
                  <td style="text-align:justify">
                  You acknowledge the role of LBMA, the global authority for digital gold physical:
                    <br/>
                    <table>
                      <tr>
                        <td style="width:10%">a)</td>
                        <td style="text-align:justify">
                        LBMA is the fulcrum of the global principle-to-principle precious metalmarket. LBMA 
                        sets standard from the purity, from and provenance of the bars to the way in which gold 
                        are traded; in The Good Delivery Rules for Gold and Silver Bar Specification for Good 
                        Delivery Bars and Application Procedures for Listing March 2015;
                        </td>
                      </tr>
                      <tr>
                        <td>b)</td>
                        <td style="text-align:justify">
                        All LBMA accredited gold bars are certified by the Chief Assayer of the Gold producer which 
                        includes information but not limited to gold content, fineness, minimum gross weight and bar 
                        number.
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td>12.4)</td>
                  <td style="text-align:justify">
                  ACE shall not be liable to the specification of the IGR gold distributed if sold in the secondary 
                  market to 3rd party (other than the ACE).
                  </td>
                </tr>
              </table>
            </p>
          </div>
        </div>
      </div>
      <div id="tnc-page-8">
        <div id="tnc-physical-gold-fulfilment-2">
          <div id="tnc-physical-gold-fulfilment-header-2">
            <p></p>
          </div>
          <div id="tnc-physical-gold-fulfilment-body-2">
            <p id="tnc-physical-gold-fulfilment-item-2">
              <table style="width:100%">
                <tr>
                  <td style="width:13%">12.5)</td>
                  <td style="text-align:justify;">
                  Fulfilment of gold account to Physical minted gold bars may only be executed by customers who are 
                  registered the digital gold account:
                  <br/>
                    <table>
                      <tr>
                        <td style="width:10%">a)</td>
                        <td style="text-align:justify">
                        The denomination of minted gold bars and physical gold fulfilment fee will be 
                        imposed upon request of the service:
                        <br/><br/>
                        Making Charges
                        <table class="tbl-bordered-2" style="text-align:center">
                          <tr>
                            <td style="width:40%;background-color:#0cb14b;font-weight:bold;">Denomination (Gram)<br/><br/></td>
                            <td style="background-color:#0cb14b;font-weight:bold">Making Charges/pc (MYR)</td>
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
                        Courier Charges:
                        <br/>
                        <table class="tbl-bordered-2" style="width:100%;text-align:center">
                          <thead style="font-weight:bold">
                            <tr>
                              <td style="background-color:#0cb14b">Courier Charges</td>
                              <td style="background-color:#0cb14b">Cost</td>
                              <td style="background-color:#0cb14b">Packing Limit</td>
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
                            <td style="width:10%">(i)</td>
                            <td style="text-align:justify">
                              The fulfilment fee is referring to Making Charges: (Premium/Pcs + Handling Charges/Pcs + Insurance/Pcs) + Packaging & Shipping;
                            </td>
                          </tr>
                          <tr>
                            <td style="width:10%">(ii)</td>
                            <td style="text-align:justify">
                              Packaging and shipment cost charge once only;
                            </td>
                          </tr>
                          <tr>
                            <td>(iii)</td>
                            <td style="text-align:justify">
                              ACE reserves the right to revise the fulfilment cost from time to time without prior notice. The changes will base on the charges imposed by the vendor and service 
                              providers;
                            </td>
                          </tr>
                          <tr>
                            <td>(iv)</td>
                            <td style="text-align:justify">
                              Maximum for physical gold fulfilment of minted gold bar is 100 gram or maximum 30 pieces each time for each parcel;
                            </td>
                          </tr>
                          <tr>
                            <td>(v)</td>
                            <td style="text-align:justify">
                              It applied to both West Malaysia and East Malaysia, delivery is available in Malaysia only.
                            </td>
                          </tr>
                        </table>
                        <br/>
                        <u>For illustration physical gold fulfilment only.</u>
                        <br/><br/>
                        <table class="tbl-bordered-2" style="width:100%;text-align:center">
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
                        Total customer to pay : MYR1,220.00 -->
                        <br/><br/>
                        </td>
                      </tr>
                      <tr>
                        <td>b)</td>
                        <td style="text-align:justify">
                        Delivery time taken is 3-5 business days for Peninsular Malaysia and 3-7 business days for 
                        East Malaysia. You can always check the status of delivery in Platform.
                        </td>
                      </tr>
                      <tr>
                        <td>c)</td>
                        <td style="text-align:justify">
                        ACE shall contact customer upon receiving uncollected Minted gold bar from courier company, 
                        ACE shall coordinate with customer for 2nd time delivery and the delivery cost is required 
                        to be paid by the customer before ACE proceed for it.
                        </td>
                      </tr>
                      <tr>
                        <td>d)</td>
                        <td style="text-align:justify">
                        It is the responsibility of the customer to ensure that Physical minted gold bar is sent to 
                        the correct dispatch address. Failure to do so may result in loss of minted gold bar and the 
                        ACE shall not be held responsible for this. The method of delivery for the physical minted bar is by courier 
                        delivery only.
                        <br/><br/>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td>12.6)</td>
                  <td style="text-align:justify">
                  For the customer who complains that the delivered Physical minted gold bar is not authentic
(i.e., the gold is counterfeit), and/or loss, he/ she shall make a police report and lodge a
complaint to ACE customer services team. ACE is also legally permitted to contact the legal,
government or regulatory authorities on the said complains.
                  </td>
                </tr>
              </table>
            </p>
          </div>
        </div>
      </div>
      <br/>
      <div id="tnc-footer">
        <div class="ace-group-logo">
        </div>
      </div>
      <br/><br/><br/>
    </div>
      <div id="tnc-pager2" style="width:100;text-align:center">
        <table style="width:100%;">
          <tr>
            <td style="float:right;width:40%">
              <button id="btn-previous2" class="btn-pager btn-previous" disabled="false"><span style="font-weight:bold"><</span></button>
            </td>
            <td style="width:20%;vertical-align:middle;background-color:white;">
              <label id="lbl-pager2" class="lbl-pager" style="min-width:50px">1/8</label>
            </td>
            <td style="float:left;width:40%">
              <button id="btn-next2" class="btn-pager btn-next"><span style="font-weight:bold">></span></button>
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