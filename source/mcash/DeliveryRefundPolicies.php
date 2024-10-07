<!doctype html>
<html lang="en-gb" dir="ltr">
<head>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_DELIVERY_REFUND_POLICY'] ?></title>
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
        $("#back_btn").click(function (){
            window.history.back();
        });
        /*$("#hidpager").val(1);
        $("[id*=drp-page-]").attr("style","display:none");
        $("#drp-page-"+$("#hidpager").val()).attr("style","display:block");
        $(".btn-previous").attr("disabled","true");*/
        $("#drp-pager").attr("style","display:none");
        $("#drp-pager2").attr("style","display:none");
        
        $(".btn-next").click(function(e){
            var i = $("#hidpager").val();
            $("#drp-page-"+i).attr("style","display:none");
            i++;
            
            $("#hidpager").val(i);
            $("#drp-page-"+i).attr("style","display:block");
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
            $("#drp-page-"+i).attr("style","display:none");
            i--;
            $("#hidpager").val(i);
            $("#drp-page-"+i).attr("style","display:block");
            
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
        .tbl-body{
          text-align:justify;
        }

        .tbl-custom thead{
          font-weight:bold;
          text-align:center;
        }
        .tbl-custom td{
          border:1px solid black;
          text-align:justify;
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

        #html-drp-wrapper{
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
      }

      /*for big device eg laptop, pc, etc*/
      @media only screen and (min-width: 661px) {
        .tbl-body{
          text-align:justify;
        }

        .tbl-custom thead{
          font-weight:bold;
          text-align:center;
        }
        .tbl-custom td{
          border:1px solid black;
          text-align:justify;
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

        #html-drp-wrapper{
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
  </style>
</head>
<body class="gogold">
  <div id="faq-switcher" class="faq-switcher">
		<ul>
			<li>
				<a href="FAQ.php" rel="noopener noreferrer"><?php echo $lang['FAQ'] ?></a>
			</li>
     		<li id="deliveryrefundpolicies">
				<a href="DeliveryRefundPolicies.php"><?php echo $lang['RefundPolicy'] ?></a>
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
       			<a href="DeliveryRefundPolicies.php"><?php echo $lang['RefundPolicy'] ?></a>
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
		<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="DeliveryRefundPolicies.php?lang=en">Eng</a></li>
        <li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="DeliveryRefundPolicies.php?lang=bm">BM</a></li>
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
    <div id="faq-switcher-small">
      <ul>
        <li>
          <div class="header-inside">
            <!-- <a id="something" href="index.php"></a> -->
          </div>
        </li>
      </ul>
    </div>
		<div class="hero">
      <div class="client-logo"></div>
			<div class="main-logo"></div>
    </div>
	</div>
    <div class="header-inside">
		<div class="inside-title"><?php echo $lang['RefundPolicy'] ?></div>
    <a class="back" href="index.php"></a>
		
	</div>
	<div class="page-content" style="margin-left:5%;margin-right:5%">
    <div id="drp-pager" style="width:100;text-align:center">
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
    <div id="html-drp-wrapper">
      <div class="gogold-logo">
      </div>
      <br/><br/>
      <div id="drp-header" style="width:100%">
        <p style="font-weight:bold;text-align:center">Insert Delivery and Refund Policy here</p>
      </div>
        <!-- <div id="drp-page-1">
            <div class="drp-item-header">
                <p style="font-weight:bold"></p>
            </div>
            <div class="drp-item-body">
                <table style="width:100%;">
                    <tr>
                        <td style="width:5%;font-weight:bold;">1.</td>
                        <td><u style="font-weight:bold">DELIVERY POLICIES</u><br/><br/></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <table class="tbl-body">
                                <tr>
                                    <td style="font-weight:bold;width:10%">1.1)</td>
                                    <td style="font-weight:bold"><u>DELIVERED BY SELLER SERVICE LEVEL AGREEMENT (SLA)</u></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <br/>
                                        <table class="tbl-custom">
                                            <tr>
                                                <td style="font-weight:bold;background-color:#f2f2f2;width:30%;text-align:left;padding-left:2%">Order Fulfillment</td>
                                                <td>
                                                    <table class="tbl-body">
                                                        <tr>
                                                            <td style="width:7%;border:0px none">•</td>
                                                            <td style="border:0px none;text-align:justify;padding-right:5%;">
                                                                Commitment of order to be delivered to customers within the Marketplace delivery Service-Level Agreement SLA (14days) from the order creation date (“Pending” in Seller Center)
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:bold;background-color:#f2f2f2;text-align:left;padding-left:2%">Product Quality</td>
                                                <td>
                                                    <table class="tbl-body">
                                                        <tr>
                                                            <td style="width:7%;border:0px none">•</td>
                                                            <td style="border:0px none;text-align:justify;padding-right:5%;">
                                                            Accurate delivery of goods in good condition as per describe on Ace Capital Growth Sdn. Bhd. (ACE) product description page
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:bold;background-color:#f2f2f2;text-align:left;padding-left:2%">Seller Service</td>
                                                <td>
                                                    <table class="tbl-body">
                                                        <tr>
                                                            <td style="width:7%;border:0px none">•</td>
                                                            <td style="border:0px none;text-align:justify;padding-right:5%;">
                                                                Taking full responsibility in providing satisfactory customer experience, and in addressing any customer order related queries 
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:bold;background-color:#f2f2f2;text-align:left;padding-left:2%">Proper Use of System</td>
                                                <td>
                                                    <table class="tbl-body">
                                                        <tr>
                                                            <td style="width:7%;border:0px none">•</td>
                                                            <td style="border:0px none;text-align:justify;padding-right:5%;">
                                                                Valid proof of delivery (POD) uploading onto SOFP (previously)
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="border:0px none">•</td>
                                                            <td style="border:0px none;text-align:justify;padding-right:5%;">
                                                                Falsifying delivery status of shipment/ update information/ fake/ empty parcel to SOFP/DBS is strictly prohibited. Read more on Fulfillment Fraud. 
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <br/>
                                                </td>
                                            </tr>
                                        </table>
                                        ACE reserves the right to unilaterally cancel the order or remove the DBS option from your seller account for any breach of the above-mentioned SLA.<br/>
                                        <br/>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">1.2)</td>
                                    <td style="font-weight:bold"><u>SHIPPING FEES CHARGES</u></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        Customers will be charge the standard shipping fees with ACE current shipping fee rates.
                                        <br/>
                                        Seller is fully responsible to bear the shipping fees between Delivered by Seller logistic cost with shipping fees collected from the customer. ACE will not provide any additional reimbursement for this cost difference. Read How is Shipping Fee Calculated to learn more.
                                        <br/><br/>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="dtp-page-2">
            <div class="drp-item-header">
                <p style="font-weight:bold"></p>
            </div>
            <div class="drp-item-body">
                <table style="width:100%">
                    <tr>
                        <td style="width:5%;font-weight:bold;">2.</td>
                        <td><u style="font-weight:bold"><u>REFUND POLICIES</u><br/><br/></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="text-align:justify;">
                            Returns is a process that involves a customer/buyer who intends to return a purchase item under acceptable reasons that can be found in the PDP of an item before purchase. 
                            This process will be validated under designated conditions depending on the type of product and according to the terms tied to it.
                            <br/><br/>
                            Requirements for a valid return:
                            <table style="width:100%">
                                <tr>
                                    <td style="width:10%">1.</td>
                                    <td>Valid order (present order number)</td>
                                </tr>
                                <tr>
                                    <td>2.</td>
                                    <td>Printed return label after return process</td>
                                </tr>
                                <tr>
                                    <td>3.</td>
                                    <td>Selecting valid return reason</td>
                                </tr>
                            </table>
                            <br/>
                            These are the return reasons that will appear for a customer to choose from when initiating a return request:
                            <table style="width:100%">
                                <tr>
                                    <td style="width:10%">1.</td>
                                    <td>
                                        Don’t want the item:
                                        <table style="width:100%">
                                            <tr>
                                                <td style="width:10%">•</td>
                                                <td>Customer has a change of mind after purchasing</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2.</td>
                                    <td>
                                        Item doesn’t match description/pictures:
                                        <table style="width:100%">
                                            <tr>
                                                <td style="width:10%">•</td>
                                                <td>Product delivered does not resemble what was advertised on the website</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3.</td>
                                    <td>
                                        Received wrong item:
                                        <table style="width:100%">
                                            <tr>
                                                <td style="width:10%">•</td>
                                                <td>Wrong product was delivered to the customer</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4.</td>
                                    <td>
                                        Missing accessory/freebie:
                                        <table style="width:100%">
                                            <tr>
                                                <td style="width:10%">•</td>
                                                <td>Product was not complete or what was delivered does not match the “what’s in the box” description</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>5.</td>
                                    <td>
                                        Damage/faulty item:
                                        <table style="width:100%">
                                            <tr>
                                                <td style="width:10%">•</td>
                                                <td>Product packaging and/or product was damage during delivery</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <br/>
                            The return reasons above have been revamped from the old one to the new one as below:
                            <table class="tbl-custom" style="width:100%">
                                <tr>
                                    <td style="font-weight:bold;background-color:#f2f2f2;width:50%;text-align:left;padding-left:2%"><u>OLD RETURN REASONS</u></td>
                                    <td style="font-weight:bold;text-align:left;padding-left:2%"><u>NEW RETURN REASON</u></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;background-color:#f2f2f2;width:50%;text-align:left;padding-left:2%">
                                        Missing accessories
                                        <br/><br/>
                                        Missing freebies
                                    </td>
                                    <td style="text-align:left;padding-left:2%">Missing accesory/Freebie</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;background-color:#f2f2f2;width:50%;text-align:left;padding-left:2%">
                                        Damaged
                                        <br/><br/>
                                        Defective
                                    </td>
                                    <td style="text-align:left;padding-left:2%">Damage/Faulty item</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;background-color:#f2f2f2;width:50%;text-align:left;padding-left:2%">
                                        Change of mind
                                        <br/><br/>
                                        Wrong size
                                    </td>
                                    <td style="text-align:left;padding-left:2%">Don’t want item</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;background-color:#f2f2f2;width:50%;text-align:left;padding-left:2%">
                                        Wrong item
                                    </td>
                                    <td style="text-align:left;padding-left:2%">Received wrong item</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;background-color:#f2f2f2;width:50%;text-align:left;padding-left:2%">
                                        Not as advertised
                                    </td>
                                    <td style="text-align:left;padding-left:2%">Item doesn’t match description/pictures</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br/>
            </div>
        </div> -->
      <div id="drp-footer">
        <div class="ace-group-logo">
        </div>
      </div>
      <br/><br/><br/>
    </div>
		  <div id="drp-pager2" style="width:100;text-align:center">
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
      <br/><br/>
    </div>
				
			
		

		
	</div>
	
</body>
</html>