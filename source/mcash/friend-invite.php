<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/login.php');  ?>
<?php include('common.php');  ?>

<?php 
	$txt_1 = 'Invitation to GOGOLD! Referral Code : '.$_SESSION['accountcodereferral']."%0A";
	$txt_2 = 'Check out this app at'."%0D%0A%0A";
	$txt_3 = 'Android: '.$playstore."%0D%0A";
	$txt_4 = 'IOS: '.$iosstore."%0A%0A";
	$txt_5 = 'with the referral code *'.$_SESSION['accountcodereferral'].'*';

	// Encode urls to remove = symbols
	$encoded_playstore = str_replace("=", "%3d", $playstore);
	$encoded_iosstore = str_replace("=", "%3d", $iosstore);

	$txt_mail_2 = 'Check out this app at'."%0D%0A%0A";
	$txt_mail_3 = 'Android: '.$encoded_playstore."%0D%0A";
	$txt_mail_4 = 'IOS: '.$encoded_iosstore."%0A%0A";
	$txt_mail_5 = 'with the referral code '.$_SESSION['accountcodereferral'];

	$txt_html_1 = 'Invitation to GOGOLD! Referral Code : '.$_SESSION['accountcodereferral'];
	$txt_html_2 = 'Check out this app at ';
	$txt_html_3 = 'Android: '.$playstore."  ";
	$txt_html_4 = 'IOS: '.$iosstore."  ";
	$txt_html_5 = 'with the referral code '.$_SESSION['accountcodereferral'];

	$msg= $txt_1.$txt_2.$txt_3.$txt_4.$txt_5."%0A";
	$msgmail= $txt_mail_2.$txt_mail_3.$txt_mail_4.$txt_mail_5."%0A";
	$msghtml= $txt_html_1.$txt_html_2.$txt_html_3.$txt_html_4.$txt_html_5." ";
	$msgshare= $txt_html_2.$txt_html_3.$txt_html_4.$txt_html_5." ";
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_FRIEND_INVITE'];?></title>
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
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<script>

	$(document).ready(function(){
		// $('#invitefriendbtn').hide();
		// $('#cancelinvitefriendbtn').hide();
		$('#page-2').hide();

	});

	function toShareUrl(text) {

		if (navigator.share) {
		navigator.share({
			title: '<?php echo $txt_html_1;?>',
			text: '<?php echo $msgshare;?>',
			url: '<?php echo $msgshare?>',
		})
			.then(() => console.log('Successful share'))
			.catch((error) => console.log('Error sharing', error));
		}

		let url = document.location.href;
		const canonicalElement = document.querySelector('link[rel=canonical]');
		if (canonicalElement !== null) {
			url = canonicalElement.href;
		}
		navigator.share({url});
	}


	function copyToClipboardURL(text) {
		var inputc = document.body.appendChild(document.createElement("input"));
	
		inputc.value = window.location.href;
		inputc.focus();
		inputc.select();
		document.execCommand('copy');
		inputc.parentNode.removeChild(inputc);
		alert("URL Copied.");
		
		// Check if hidden, unhide
		if($('#invitefriendbtn').is(":hidden") == true){
			$('#invitefriendbtn').show();
		}
	
	}

	function copyToClipboard(text) {
		const elem = document.createElement('textarea');
		var text = '<?php echo $msghtml;?>';

		elem.value = text;
		document.body.appendChild(elem);
		elem.select();
		document.execCommand('copy');
		document.body.removeChild(elem);

		Swal.fire({
			title: 'Copied!',
			text: 'Your referral link has been copied! Paste to send your unique link to your friends and family!',
			timer: 5000, // Close at 0.5 sec
			icon: 'info',
			confirmButtonText: 'OK',
			confirmButtonColor: '#53C4CC',
			showCancelButton: false,
  			showConfirmButton: false
		}).then(
		function () {},
			// handling the promise rejection
			function (dismiss) {
				if (dismiss === 'timer') {
				//console.log('I was closed by the timer')
				}
		}
		);

		// // Check if hidden, unhide
		// if($('#invitefriendbtn').is(":hidden") == true){
		// 	$('#invitefriendbtn').show();
		// }
	}

	function inviteButton(text) {
		// Show page
		$('#page-1').hide();
		$('#page-2').show();

		// Toggle buttons
		// $('#invitefriendbtn').hide();
		// $('#cancelinvitefriendbtn').show();
	}

	function cancelButton(text) {
		// Show page
		$('#page-1').show();
		$('#page-2').hide();

		// Toggle buttons
		// $('#invitefriendbtn').show();
		// $('#cancelinvitefriendbtn').hide();
	}

</script>

<body class="gogold">
	<!--<div class="language-switcher"> 
		<ul>
			<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="transfer.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="transfer.php?lang=bm">BM</a></li>
			<li id="logout"class="nav-item">
				<a id="logout_button" href="controllers/logout.php" class="nav-link">
					<i class="fa fa-sign-out"></i><input class="search_input" type="text" name="" readonly placeholder="<?php echo strtoupper($lang['Logout']); ?>">
					
				</a>
				
			</li>
		</ul>
	</div>-->
	
	
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['Invite_A_Friend']; ?></div>
		<a class="back" href="index.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
					
			<br>
			<div class="form-title center" style="background-image: linear-gradient(to top, #fbccb1, #faccb0, #facbaf, #f9cbae, #f8cbad, #f8cbac, #f7cbaa, #f7cba9, #f7cba7, #f8caa5, #f8caa2, #f8caa0);">

				<img src="img/bg/gift-move-small.gif" alt="this slowpoke moves"  width="auto" />
			</div>
			<div class="friend-invite-bottom" id="page-1">
				<div class="header-inside">

					<div class="inside-left"><?php echo $lang['ShareLink']; ?></div>
				</div>

				
				<div class="link-button-row">
					<div class="col">
						<a href="https://api.whatsapp.com/send?text=<?php echo $msg;?> ">
							<button class="icon-button-share" ><img src="img/icon/whatsapp.svg"  height ="40" width="38" /></button>
						</a>
						<div class="title"><?php echo 'WhatsApp' ?></div>
					</div>
					<div class="col">
						<a href="mailto:?subject=Invitation to GOGOLD! Referral Code : <?php echo $_SESSION['accountcodereferral'];?>&amp;body=<?php echo $msgmail;?>">
							<button class="icon-button-share"><img src="img/icon/email.svg" height ="22" width="21" /></button>
						</a>
						<div class="title"><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Email' ?></div>
					</div>
					<div class="col">
						<button class="icon-button-share" onclick="toShareUrl()"><img src="img/icon/share.svg" height ="22" width="21" /></button>
						<div class="title"><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;Share' ?></div>
					</div>
				</div>
				<div class="page-content-form-shaded">
					<div class="inside-content">
						<span class="text-left"><?php echo $lang['ReferralLink']; ?></span>
						<span class="text-right-clip"><?php echo $_SESSION['accountcodereferral']  ?></span>
						<button class="icon-button-click" onclick="copyToClipboard()"><img src="img/icon/icon-copy-final.svg" height ="22" width="21" /></button>
					</div>
				</div>
				<div class="form-row submit">
						<div class="form-col">
							<a href="index.php"><input id='gobacktopreviouspage' type="submit" class="btn" name="account" onclick="backButton()" formaction="" value="<?php echo $lang['Cancel']; ?>"/></a>
							
						</div>
					</div>
				<!-- <div class="form-row submit">
					<div class="form-col">
						<input id='invitefriendbtn' type="submit" class="btn" name="account" onclick="inviteButton()" formaction="" value="<?php echo $lang['InviteFriend']; ?>"/>
						
					</div>
				</div> -->
			</div>

			<div class="friend-invite-bottom" id="page-2">
				<div class="header-inside">

					<!-- <div class="link-button-row">
						<div class="col">
							<a href="https://api.whatsapp.com/send?text=<?php echo $msg;?> ">
								<button class="icon-button-share" ><img src="img/icon/whatsapp.svg"  height ="40" width="38" /></button>
							</a>
							<div class="title"><?php echo 'WhatsApp' ?></div>
						</div>
						<div class="col">
							<a href="mailto:?subject=Invitation to GOGOLD! Referral Code : <?php echo $_SESSION['accountcodereferral'];?>&amp;body=<?php echo $msgmail;?>">
								<button class="icon-button-share"><img src="img/icon/email.svg" height ="22" width="21" /></button>
							</a>
							<div class="title"><?php echo 'Email' ?></div>
						</div>
						<div class="col">
							<button class="icon-button-share" onclick="toShareUrl()"><img src="img/icon/share.svg" height ="22" width="21" /></button>
							<div class="title"><?php echo 'Share' ?></div>
						</div>
					</div> -->
					<br><br>
					<div class="form-row submit">
						<div class="form-col">
							<input id='cancelinvitefriendbtn' type="submit" class="btn" name="account" onclick="cancelButton()" formaction="" value="<?php echo $lang['Cancel']; ?>"/>
							
						</div>
					</div>
				</div>
			
			</div>
		
		</div>
		
	</div>
	
</body>
</html>