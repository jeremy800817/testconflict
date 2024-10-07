<!doctype html>
<html lang="en-gb" dir="ltr">
<head>

<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo 'Support Center | EASIGOLD';?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="easigold/css/nice-select.css">
<link rel="stylesheet" type="text/css" href="easigold/js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="easigold/css/style.css">
<link rel="stylesheet" type="text/css" href="easigold/css/style-custom.css">
<script src="easigold/js/jquery.js"></script>
<script type="text/javascript" src="easigold/js/jquery-ui/jquery-ui.min.js"></script>
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="easigold/js/jquery.nice-select.min.js"></script>
<script src="easigold/js/script.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"> </script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>

<script>
  $(document).ready(function () {  

      $("#submitbt").click(function (e) {
        if(true == $("#form_support").valid()) {
              e.preventDefault(); // stops the default action
            $("#loader").show(); // shows the loading screen
            Swal.fire({
              title: "Submitting...",
              text: "Please wait",
              imageUrl: "easigold/img/ajaxloader.gif",
              showConfirmButton: false,
              allowOutsideClick: false
            });
            $("#form_support").submit(); 
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
          background: transparent url(./easigold/img/bg/bg-pdf.jpg) top left/contain repeat-y;
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
          background: transparent url(./easigold/img/bg/bg-ace-group-pdf.png) center right/contain no-repeat;
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
          background: transparent url(./easigold/img/bg/bg-pdf.jpg) top left/contain repeat-y;
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
          background: transparent url(.easigold//img/bg/bg-ace-group-pdf.png) center right/contain no-repeat;
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
	<div class="header">
    
		<div id="faq-switcher-small">
		<ul>
			<li>
			<!--<a  href="index.php" style="font-weight: bold;font-size:small;color:black;font-size:small;color:black;font: normal 12px / 1.4em 'Nunito', Arial, sans-serif;">< BACK</a>-->
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
		<div class="inside-title"><?php echo 'Support Center' ?></div>
   	 		<!-- <a class="back" href="/"></a> -->
		
	</div>
	<div class="page-content" style="margin-left:5%;margin-right:5%">

    <div id="html-faq-wrapper">
      <div class="gogold-logo">
      </div>
      <br/><br/>
      <div id="faq-header" style="width:100%">
		<form id="form_support" method="post" action="easigold/controllers/contactus.php" >
				<div class="form-row">
					<div class="form-col">
						<label><?php echo 'Submit a request'; ?></label>
						<select id="subject" name="subject" >
<!-- This is default define value using php variable $r -->
					<!-- Other options values -->
                    <option value="COMPLAINT">Complaint</option>
                    <option value="FEEDBACK">Feedback</option>
                    <option value="NEED ASSISTANCE">Need assistance</option>
    
                    </select>
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo 'Email' ?></label>
						<input id="email" value="" type="email"  name="email" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo 'Mobile'; ?></label>
						<input id="contact" value="" type="text" name="contact" required  />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo 'Issue'; ?></label>
						<textarea name="message" cols="20" rows="5" required></textarea>
						<div class="error"></div>
					</div>
				</div>
				
				<div class="form-row submit">
					<div class="form-col">
						<input id="submitbt" type="submit" class="btn" value="Submit" />
					</div>
					<div class="form-col">
            <a href="">
            <input id="cancelbt" type="text" readonly class="btn" value="Cancel" />
            </a>
				
					</div>
				</div>
				</form>
      </div>
      <div id="faq-page-1">
        <div class="faq-item-header">
          <!-- <p style="font-weight:bold"><u>ONEGOLD PRODUCT</u></p> -->
        </div>
        
      </div>
     
      <div id="faq-footer">
        <div class="ace-group-logo">
        </div>
      </div>
      <br/><br/><br/>
    </div>
		
	</div>
	
</body>
</html>