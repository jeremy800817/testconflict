<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;
/**
 * Captcha:: A visual and audio CAPTCHA generation class library
 * @package snap.base
 */

// class defaults - change to effect globally
define('CAPTCHA_SESSION_ID', 'php_captcha');
define('CAPTCHA_WIDTH', 200); // max 500
define('CAPTCHA_HEIGHT', 50); // max 200
define('CAPTCHA_NUM_CHARS', 5);
define('CAPTCHA_NUM_LINES', 70);
define('CAPTCHA_CHAR_SHADOW', false);
define('CAPTCHA_OWNER_TEXT', '');
define('CAPTCHA_CHAR_SET', ''); // defaults to A-Z
define('CAPTCHA_CASE_INSENSITIVE', true);
define('CAPTCHA_BACKGROUND_IMAGES', '');
define('CAPTCHA_MIN_FONT_SIZE', 16);
define('CAPTCHA_MAX_FONT_SIZE', 25);
define('CAPTCHA_USE_COLOUR', false);
define('CAPTCHA_FILE_TYPE', 'jpeg');
define('CAPTCHA_FLITE_PATH', '/usr/bin/flite');
define('CAPTCHA_AUDIO_PATH', '/tmp/'); // must be writeable by PHP process

class Captcha {
	private $oImage;
	private $aFonts;
	private $iWidth;
	private $iHeight;
	private $iNumChars;
	private $iNumLines;
	private $iSpacing;
	private $bCharShadow;
	private $sOwnerText;
	private $aCharSet;
	private $bCaseInsensitive;
	private $vBackgroundImages;
	private $iMinFontSize;
	private $iMaxFontSize;
	private $bUseColour;
	private $sFileType;
	private $sCode = '';
	private $cacher = null;
	private $cacheKey = '';

	/**
	* Constructor function
	*
	* @param array $aFonts array of TrueType fonts to use - specify full path
	* @param integer $iWidth width of image (Default: CAPTCHA_WIDTH)
	* @param integer $iHeight height of image (Default: CAPTCHA_HEIGHT)
	*
	* @access public
	* @return Captcha
	*/
	function __construct($aFonts, $iWidth = CAPTCHA_WIDTH, $iHeight = CAPTCHA_HEIGHT) {
		// get parameters
		$this->aFonts = $aFonts;
		$this->setNumChars(CAPTCHA_NUM_CHARS);
		$this->setNumLines(CAPTCHA_NUM_LINES);
		$this->setShadow(CAPTCHA_CHAR_SHADOW);
		$this->setOwnerText(CAPTCHA_OWNER_TEXT);
		$this->setCharSet(CAPTCHA_CHAR_SET);
		$this->setCaseInsensitive(CAPTCHA_CASE_INSENSITIVE);
		$this->setBackgroundImages(CAPTCHA_BACKGROUND_IMAGES);
		$this->setMinFontSize(CAPTCHA_MIN_FONT_SIZE);
		$this->setMaxFontSize(CAPTCHA_MAX_FONT_SIZE);
		$this->setUseColour(CAPTCHA_USE_COLOUR);
		$this->setFileType(CAPTCHA_FILE_TYPE);
		$this->setWidth($iWidth);
		$this->setHeight($iHeight);
	}

	function setCacher($cacher) {
		$this->cacher = $cacher;
	}

	function setCacheKey($key) {
		$this->cacheKey = $key;
	}

	function doCalculateSpacing() {
		$this->iSpacing = (int)($this->iWidth / $this->iNumChars);
	}

	function setWidth($iWidth) {
		$this->iWidth = $iWidth;
		if ($this->iWidth > 500) $this->iWidth = 500; // to prevent perfomance impact
		$this->doCalculateSpacing();
	}

	function setHeight($iHeight) {
		$this->iHeight = $iHeight;
		if ($this->iHeight > 200) $this->iHeight = 200; // to prevent performance impact
	}

	function setNumChars($iNumChars) {
		$this->iNumChars = $iNumChars;
		$this->doCalculateSpacing();
	}

	function setNumLines($iNumLines) {
		$this->iNumLines = $iNumLines;
	}

	function setShadow($bCharShadow) {
		$this->bCharShadow = $bCharShadow;
	}

	function setOwnerText($sOwnerText) {
		$this->sOwnerText = $sOwnerText;
	}

	function setCharSet($vCharSet) {
		// check for input type
		if (is_array($vCharSet)) {
			$this->aCharSet = $vCharSet;
		} else {
			if ($vCharSet != '') {
			   // split items on commas
			   $aCharSet = explode(',', $vCharSet);

			   // initialise array
			   $this->aCharSet = array();

			   // loop through items
			   foreach ($aCharSet as $sCurrentItem) {
			      // a range should have 3 characters, otherwise is normal character
			      if (strlen($sCurrentItem) == 3) {
			         // split on range character
			         $aRange = explode('-', $sCurrentItem);

			         // check for valid range
			         if (count($aRange) == 2 && $aRange[0] < $aRange[1]) {
			            // create array of characters from range
			            $aRange = range($aRange[0], $aRange[1]);

			            // add to charset array
			            $this->aCharSet = array_merge($this->aCharSet, $aRange);
			         }
			      } else {
			         $this->aCharSet[] = $sCurrentItem;
			      }
			   }
			}
		}
	}

	function setCaseInsensitive($bCaseInsensitive) {
		$this->bCaseInsensitive = $bCaseInsensitive;
	}

	function setBackgroundImages($vBackgroundImages) {
		$this->vBackgroundImages = $vBackgroundImages;
	}

	function setMinFontSize($iMinFontSize) {
		$this->iMinFontSize = $iMinFontSize;
	}

	function setMaxFontSize($iMaxFontSize) {
		$this->iMaxFontSize = $iMaxFontSize;
	}

	function setUseColour($bUseColour) {
		$this->bUseColour = $bUseColour;
	}

	function setFileType($sFileType) {
		// check for valid file type
		if (in_array($sFileType, array('gif', 'png', 'jpeg'))) {
			$this->sFileType = $sFileType;
		} else {
			$this->sFileType = 'jpeg';
		}
	}

	function drawLines() {
		for ($i = 0; $i < $this->iNumLines; $i++) {
			// allocate colour
			if ($this->bUseColour) {
			   $iLineColour = imagecolorallocate($this->oImage, rand(100, 250), rand(100, 250), rand(100, 250));
			} else {
			   $iRandColour = rand(100, 250);
			   $iLineColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
			}

			// draw line
			imageline($this->oImage, rand(0, $this->iWidth), rand(0, $this->iHeight), rand(0, $this->iWidth), rand(0, $this->iHeight), $iLineColour);
		}
	}

	function drawOwnerText() {
		// allocate owner text colour
		$iBlack = imagecolorallocate($this->oImage, 0, 0, 0);
		// get height of selected font
		$iOwnerTextHeight = imagefontheight(2);
		// calculate overall height
		$iLineHeight = $this->iHeight - $iOwnerTextHeight - 4;

		// draw line above text to separate from CAPTCHA
		imageline($this->oImage, 0, $iLineHeight, $this->iWidth, $iLineHeight, $iBlack);

		// write owner text
		imagestring($this->oImage, 2, 3, $this->iHeight - $iOwnerTextHeight - 3, $this->sOwnerText, $iBlack);

		// reduce available height for drawing CAPTCHA
		$this->iHeight = $this->iHeight - $iOwnerTextHeight - 5;
	}

	function generateCode() {
		// reset code
		$this->sCode = '';

		// loop through and generate the code letter by letter
		for ($i = 0; $i < $this->iNumChars; $i++) {
			if (count($this->aCharSet) > 0) {
			   // select random character and add to code string
			   $this->sCode .= $this->aCharSet[array_rand($this->aCharSet)];
			} else {
			   // select random character and add to code string
			   $this->sCode .= chr(rand(65, 90));
			}
		}

		// save code in session variable
		if ($this->bCaseInsensitive) {
			$_SESSION[CAPTCHA_SESSION_ID] = strtoupper($this->sCode);
		} else {
			$_SESSION[CAPTCHA_SESSION_ID] = $this->sCode;
		}

		// store code in cache repository if available
		if ($this->cacher != null && strlen($this->cacheKey) > 0) {
			$this->cacher->set($this->cacheKey, $this->sCode, 600);
		}
	}


	function drawCharacters() {
		// loop through and write out selected number of characters
		$iPrevTextColour = false;
		for ($i = 0; $i < strlen($this->sCode); $i++) {
			// select random font
			$sCurrentFont = $this->aFonts[array_rand($this->aFonts)];
			$sCurrentFont='C:\Apache24\htdocs\gtp2\source\snaplib\resource\fonts\arialbi.ttf';
			
			$char = $this->sCode[$i];
			// select random colour
			if ($this->bUseColour) {
				//$iTextColour = false;
				//while (!$iTextColour) {
					$iTextColour = imagecolorallocate($this->oImage, rand(50, 150), rand(50, 150), rand(50, 150));
				//}
				//$char = $iTextColour;
				if ($iTextColour == '') $iTextColour = $iPrevTextColour;
				if ($this->bCharShadow) {
					// shadow colour
					//$iShadowColour = false;
					//while ($iShadowColour === false) {
						$iShadowColour = imagecolorallocate($this->oImage, rand(50, 150), rand(50, 150), rand(50, 150));
						if ($iShadowColour == '') $iShadowColour = $iPrevTextColour;
					//}
				}
			} else {
				$iRandColour = rand(0, 100);
				$iTextColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);

				if ($this->bCharShadow) {
					// shadow colour
					$iRandColour = rand(0, 100);
					$iShadowColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
				}
			}

			// select random font size
			$iFontSize = rand($this->iMinFontSize, $this->iMaxFontSize);

			// select random angle
			$iAngle = rand(-45, 45);

			// get dimensions of character in selected font and text size
			$aCharDetails = imageftbbox($iFontSize, $iAngle, $sCurrentFont, $char, array());

			// calculate character starting coordinates
			$iX = $this->iSpacing / 4 + $i * $this->iSpacing;
			$iCharHeight = $aCharDetails[2] - $aCharDetails[5];
			$iY = $this->iHeight / 2 + $iCharHeight / 4;

			// write text to image
			imagefttext($this->oImage, $iFontSize, $iAngle, $iX, $iY, $iTextColour, $sCurrentFont, $char, array());

			if ($this->bCharShadow) {
			   $iOffsetAngle = rand(-30, 30);

			   $iRandOffsetX = rand(-5, 5);
			   $iRandOffsetY = rand(-5, 5);

			   imagefttext($this->oImage, $iFontSize, $iOffsetAngle, $iX + $iRandOffsetX, $iY + $iRandOffsetY, $iShadowColour, $sCurrentFont, $char, array());
			}
			$iPrevTextColour = $iTextColour;
		}
	}

	function writeFile($sFilename) {
		if ($sFilename == '') {
			// tell browser that data is jpeg
			header("Content-type: image/$this->sFileType");
		}

		switch ($this->sFileType) {
			case 'gif':
			   $sFilename != '' ? imagegif($this->oImage, $sFilename) : imagegif($this->oImage);
			   break;
			case 'png':
			   $sFilename != '' ? imagepng($this->oImage, $sFilename) : imagepng($this->oImage);
			   break;
			default:
			   $sFilename != '' ? imagejpeg($this->oImage, $sFilename) : imagejpeg($this->oImage);
		}
	}

	function create($sFilename = '') {
		// check for required gd functions
		if (!function_exists('imagecreate') || ($this->vBackgroundImages != '' && !function_exists('imagecreatetruecolor'))) {
			return false;
		}

		// get background image if specified and copy to CAPTCHA
		if (is_array($this->vBackgroundImages) || $this->vBackgroundImages != '') {
			// create new image
			$this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);

			// create background image
			if (is_array($this->vBackgroundImages)) {
			   $iRandImage = array_rand($this->vBackgroundImages);
			   $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages[$iRandImage]);
			} else {
			   $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages);
			}

			// copy background image
			imagecopy($this->oImage, $oBackgroundImage, 0, 0, 0, 0, $this->iWidth, $this->iHeight);

			// free memory used to create background image
			imagedestroy($oBackgroundImage);
		} else {
			// create new image
			$this->oImage = imagecreate($this->iWidth, $this->iHeight);
		}

		// allocate white background colour
		imagecolorallocate($this->oImage, 255, 255, 255);

		// check for owner text
		if ($this->sOwnerText != '') {
			$this->drawOwnerText();
		}

		// check for background image before drawing lines
		if (!is_array($this->vBackgroundImages) && $this->vBackgroundImages == '') {
			$this->drawLines();
		}

		$this->generateCode();
		$this->drawCharacters();

		// write out image to file or browser
		$this->writeFile($sFilename);

		// free memory used in creating image
		imagedestroy($this->oImage);

		return true;
	}

	// call this method statically
	static public function validate($sUserCode, $bCaseInsensitive = true) {
		if ($bCaseInsensitive) {
			$sUserCode = strtoupper($sUserCode);
		}

		if (!empty($_SESSION[CAPTCHA_SESSION_ID]) && $sUserCode == $_SESSION[CAPTCHA_SESSION_ID]) {
			// clear to prevent re-use
			unset($_SESSION[CAPTCHA_SESSION_ID]);

			return true;
		}

		return false;
	}
}

// this class will only work correctly if a visual CAPTCHA has been created first using Captcha
class CaptchaAudio {
	private $sFlitePath;
	private $sAudioPath;
	private $sCode;

	/**
	* Constructor function
	*
	* @param string $sFlitePath path to flite binary (Default: CAPTCHA_FLITE_PATH)
	* @param string $sAudioPath the location to temporarily store the generated audio CAPTCHA (Default: CAPTCHA_AUDIO_PATH)
	*
	* @access public
	* @return CaptchaAudio
	*/
	function __construct($sFlitePath = CAPTCHA_FLITE_PATH, $sAudioPath = CAPTCHA_AUDIO_PATH) {
		$this->setFlitePath($sFlitePath);
		$this->setAudioPath($sAudioPath);

		// retrieve code if already set by previous instance of visual PhpCaptcha
		if (isset($_SESSION[CAPTCHA_SESSION_ID])) {
			$this->sCode = $_SESSION[CAPTCHA_SESSION_ID];
		}
	}

	function setFlitePath($sFlitePath) {
		$this->sFlitePath = $sFlitePath;
	}

	function setAudioPath($sAudioPath) {
		$this->sAudioPath = $sAudioPath;
	}

	function mask($sText) {
		$iLength = strlen($sText);

		// loop through characters in code and format
		$sFormattedText = '';
		for ($i = 0; $i < $iLength; $i++) {
			// comma separate all but first and last characters
			if ($i > 0 && $i < $iLength - 1) {
			   $sFormattedText .= ', ';
			} elseif ($i == $iLength - 1) { // precede last character with "and"
			   $sFormattedText .= ' and ';
			}
			$sFormattedText .= $sText[$i];
		}

		$aPhrases = array("The %1\$s characters are as follows: %2\$s",
			"%2\$s, are the %1\$s letters",
			"Here are the %1\$s characters: %2\$s",
			"%1\$s characters are: %2\$s",
			"%1\$s letters: %2\$s"
		);

		$iPhrase = array_rand($aPhrases);

		return sprintf($aPhrases[$iPhrase], $iLength, $sFormattedText);
	}

	function create() {
		$sText = $this->mask($this->sCode);
		$sFile = md5($this->sCode.time());

		// create file with flite
		shell_exec("$this->sFlitePath -t \"$sText\" -o $this->sAudioPath$sFile.wav");

		// set headers
		header('Content-type: audio/x-wav');
		header("Content-Disposition: attachment;filename=$sFile.wav");

		// output to browser
		echo file_get_contents("$this->sAudioPath$sFile.wav");

		// delete temporary file
		@unlink("$this->sAudioPath$sFile.wav");
	}
}
?>
