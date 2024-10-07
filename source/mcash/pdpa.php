<!doctype html>
<html lang="en-gb" dir="ltr">
<head>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_PDPA'];?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/style-mcash.css">
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
      
        pdfjsLib.getDocument('GoGoldPDPAfinal.pdf').then((pdf) => {
      
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
  /*$("#hidpager").val(1);
  $("[id*=pdpa-page-]").attr("style","display:none");
  $("#pdpa-page-"+$("#hidpager").val()).attr("style","display:block");
  $(".btn-previous").attr("disabled","true");*/
  $("#pdpa-pager").attr("style","display:none");
  $("#pdpa-pager2").attr("style","display:none");

 $(".btn-next").click(function(e){
   var i = $("#hidpager").val();
   $("#pdpa-page-"+i).attr("style","display:none");
   i++;
   $("#hidpager").val(i);
   $("#pdpa-page-"+i).attr("style","display:block");

   $(".lbl-pager").html(i+"/2");
   
   if($(this).attr("id")=="btn-next2"){
    window.scroll({
      top: 0, 
      left: 0, 
      behavior: 'smooth'
    });
   }

   if(i == 2){
    $(".btn-next").attr("disabled","true");
    $(".btn-previous").removeAttr("disabled");
   }
   else{
    $(".btn-next").removeAttr("disabled");
    $(".btn-previous").removeAttr("disabled");
   }
 });

 $(".btn-previous").click(function(e){
   var i = $("#hidpager").val();
   $("#pdpa-page-"+i).attr("style","display:none");
   i--;
   $("#hidpager").val(i);
   $("#pdpa-page-"+i).attr("style","display:block");

   $(".lbl-pager").html(i+"/2");

   if($(this).attr("id")=="btn-previous2"){
    window.scroll({
      top: 0, 
      left: 0, 
      behavior: 'smooth'
    });
   }

   if(i == 1){
    $(".btn-previous").attr("disabled","true");
    $(".btn-next").removeAttr("disabled");
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
        #html-pdpa-wrapper{
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
        #html-pdpa-wrapper{
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
      
      .tbl-body td {
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
		<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="pdpa.php?lang=en">Eng</a></li>
        <li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="pdpa.php?lang=bm">BM</a></li>
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
		<div class="inside-title" style="margin-left: 7%;"><?php echo $lang['PDPA_HEADER'] ?></div>
    <a class="back" href="index.php"></a>
	</div>
	<div class="page-content" style="margin-left:5%;margin-right:5%">
    <br/>
    <div id="pdpa-pager" style="width:100;text-align:center">
      <input type="hidden" id="hidpager" name="hidpager" value="1" />
      <table style="width:100%;">
        <tr>
          <td style="float:right;width:40%">
            <button id="btn-previous1" class="btn-pager btn-previous" style="min-width:50px" disabled="false"><span style="font-weight:bold"><</span></button>
          </td>
          <td style="width:20%;vertical-align:middle;background-color:white;">
            <label id="lbl-pager" class="lbl-pager" style="min-width:50px">1/2</label>
          </td>
          <td style="float:left;width:40%">
            <button id="btn-next1" class="btn-pager btn-next" style="min-width:50px"><span style="font-weight:bold">></span></button>
          </td>
        </tr>
      </table>
    </div>
  <div id="html-pdpa-wrapper">
    <div id="pdpa-header">
      <div class="gogold-logo">
      </div>
      <br/><br/>
      <div id="pdpa-title" style="width:100%;text-align:center">
        <p style="font-weight:bold;">
          <u></u>
        </p>
      </div>
    </div>
    <br/>
    <div id="pdpa-page-1">
      <div id="pdpa-body-1">
        <table class="tbl-body">
          <tr>
            <td style="width:10%">1.</td>
            <td>
            By communicating with us, using our services, purchasing products or services from us, opening of MGold account or by virtue of your engagement and/or transactions or 
            dealings with us, you acknowledge that you have read and understood this Notice and agree and consent to the collection, use, processing and transfer of your Personal Data 
            by us or on your behalf and for the purpose as specified in this Notice.
            </td>
          </tr>
          <tr>
            <td>2.</td>
            <td>
            In this Notice, “Personal Data” means information about you, from which you are identifiable, including but not limited to your name, identification card number, birth 
            certificate number, passport number, nationality, address, telephone number, fax number, bank details, credit card details, race, gender, date of birth, marital status, 
            resident status, education background, financial background, personal interests, email address, your occupation, your designation/job title in your company, your company 
            details, salary range, the industry in which you work in, any information about you which you have provided to us in electronic registration forms, application forms or 
            any other similar forms and/or any information about you that has been or may be collected, stored, used and processed by us from time to time and includes sensitive 
            personal data such as data relating to health, political opinion, religious or other similar beliefs or commission or alleged commission of any offence.
            </td>
          </tr>
          <tr>
            <td>3.</td>
            <td>
            Disclosure to Third Parties
            <br/>
            In order to process your application and subsequently to continue performing the contractual agreements entered, we may need to transfer, access or disclose your 
            personal data to other entities external third parties for the Purpose. The external third parties we disclose your personal data to may include but not limited 
            to (the “Third Parties”)
            <br/>
            <table class="tbl-body" style="margin-left:5%">
              <tr>
                <td style="width:13%">3.1)</td>
                <td>
                MRuncit Commerce Sdn Bhd
                </td>
              </tr>
              <tr>
                <td>3.2)</td>
                <td>
                  any associated, related, holdings and/or subsidiaries of MRuncit Commerce Sdn Bhd, including those incorporated in the future;
                </td>
              </tr>
              <tr>
                <td>3.3)</td>
                <td>
                our business partners and affiliates;
                </td>
              </tr>
              <tr>
                <td>3.4)</td>
                <td>
                our auditors, consultants, lawyers, agents, accountants and/or advisors;
                </td>
              </tr>
              <tr>
                <td>3.5)</td>
                <td>
                marketing research companies;
                </td>
              </tr>
              <tr>
                <td>3.6)</td>
                <td>
                our third-party service providers such as information technology (IT) service providers for infrastructure, software and development work, third party 
                management companies, sub-contractors or other parties as may be deemed necessary by us to facilitate your dealings with us.
                </td>
              </tr>
            </table>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div id="pdpa-page-2">
      <div id="pdpa-body-2">
        <table class="tbl-body">
          <tr>
            <td style="width:10%">4.</td>
            <td>
            These entities or Third Parties may locate, store, maintain and/or process your Personal Data within or outside of Malaysia
            </td>
          </tr>
          <tr>
            <td>5.</td>
            <td>
              The customer(s) also consent and agree to conducting credit checks and verification of information given by the customer in the customer’s application for the MGold Accounts 
              set up for the purpose of collecting and providing credit or other information. The customer(s) also consent to disclosure of the customer’s financial condition, details of 
              accounts, account relationship to:
              <br>
              <table class="tbl-body" style="margin-left:5%">
                <tr>
                  <td style="width:13%">5.1)</td>
                  <td>
                  party(ies) providing services (including outsourcing vendors, lawyers, nominees,
custodians, centralized securities depository or registrar, debt collection agents) to the
MGold;
                  </td>
                </tr>
                <tr>
                  <td>5.2)</td>
                  <td>
                  Agents, consultants and professional advisers;
                  </td>
                </tr>
                <tr>
                  <td>5.3)</td>
                  <td>
                  the police or any investigating officer conducting any investigation; and
                  </td>
                </tr>
              </table>
              any person to whom disclosure is permitted or required by any law, regulation, governmental 
              directive or request) the applicable regulatory rules or guidelines, use or apply any information 
              relating to the customer’s collected, compiled, or obtained by MRuncit Commerce Sdn Bhd 
              through or by whatever means and methods for such purposes as determined by the MGold 
              Platform.
            </td>
          </tr>
          <tr>
            <td style="width:10%">6.</td>
            <td>
            The customer(s) also declare that all personal information and data set forth herein is/are all
true, up to date and accurate and should there be any changes to any personal information or
data set forth herein, shall notify the MGold App immediately.
            </td>
          </tr>
        </table>
        <br /><br />
        <div style="border:1px solid black;width:100%;padding-left:2%;padding-right:2%;">
          <br/>
            <p style="text-align:center"><u><i>Why Consent Is needed and how The Information will be used</i></u></p>
          <br/>
          <span style="margin-left:5%">
            <i>
            1.&nbsp;&nbsp;&nbsp;&nbsp;The information provided will be shared and retained in accordance with applicable law concerning data security and privacy protections.
            </i>
          </span>
          <br/><br/>
          <span style="margin-left:5%">
            <i>
            2.&nbsp;&nbsp;&nbsp;&nbsp;The information you authorize us to obtain and share will be used to determine your eligibility for the product
            <br/><br/>
            </i>
          </span>
        </div>
      </div>
    </div>
    <br/>
    <div id="pdpa-footer">
      <div class="ace-group-logo">
      </div>
    </div>
    <br/><br/><br/>
  </div>
      <div id="pdpa-pager2" style="width:100;text-align:center">
        <table style="width:100%;">
          <tr>
            <td style="float:right;width:40%">
              <button id="btn-previous2" class="btn-pager btn-previous" style="min-width:50px" disabled="false"><span style="font-weight:bold"><</span></button>
            </td>
            <td style="width:20%;vertical-align:middle;background-color:white;">
              <label id="lbl-pager2" class="lbl-pager" style="min-width:50px">1/2</label>
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