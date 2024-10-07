#!/bin/sh

## Usage:
## ./printScript.sh {filename_inside_word_directory}
##
## Example (assume Confirmation.docx exists inside /usr/local/nginx/html/gtp/source/snapapp_otc/word directory):
## ./prinScript.sh 'Confirmation.docx'

FILENAME=$1
FULLPATH=/usr/local/nginx/html/gtp/source/snapapp_otc

/usr/bin/libreoffice7.5 --headless --convert-to pdf $FULLPATH/word/$FILENAME --outdir $FULLPATH/pdf/
chmod 644 $FULLPATH/pdf/*.pdf
