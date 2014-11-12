<?php
/* 
AMAZON5.PHP: Get album covers from Amazon for use in AmpJuke.
By: Michael H. Iversen. 
Email: michael /AT/ ampjuke /DOT/ org

Version 5:
20-10-2007 / Migrated to PHP5 + cleaned up in the code (reduced to 33% of prev. version!!)


Version 4: 
07-07-2006 / Migrated to ECS4.
Note that the code below _does_ _not_ rely on PHP's built-in XML-stuff (PHP5).
In other words: You should be able to run this on a box w. PHP4 also...

Input (defined ahead - we're INCLUDING this code):
amazon_string = Basically the album we're looking for, f.ex.: "Rick Springfield - Rock Of Life".
amazon_key = AWS ID (see http://www.ampjuke.org/faq.php for more info. about this)
amazon_album_id = ID of album from AmpJuke's album-table -> USED AS FILENAME
*/

error_reporting(0); // Just in case...

function print_cover($amazon_string) {
	echo '<table class="cover_table"><tr><td align="center">';
	echo '<img src="./covers/'.$amazon_string.'.jpg" border="0">';
	echo '</td></tr>';
}	
	
function ampjuke_save_cover($cover,$amazon_string) {
    $handle=fopen($cover,"r");
	$out_handle=fopen('./covers/'.$amazon_string.'.jpg', "w");
	while (!feof($handle)) {
		$data=fread($handle,4096);
		fwrite($out_handle,$data);
	}
	fclose($handle);
	fclose($out_handle);
}


function amazon_web_service($amazon_country,$amazon_key,$amazon_string) {
	$data="";
	// 1: Construct query:
	switch ($amazon_country) {
		case "us": $xml_server="http://ecs.amazonaws.com"; $ass_id="ampjuke-20"; break;
		case "uk": $xml_server="http://ecs.amazonaws.co.uk"; $ass_id="ampjuke-21"; break;
		case "de": $xml_server="http://ecs.amazonaws.de"; $ass_id="ampjuke08-21"; break;
	}
	// THIS is where ECS4 really comes into play (=different parameternames compared to ECS3):
	$file="$xml_server/onca/xml?Service=AWSECommerceService"
	. '&Version=2007-07-16'
	. '&Operation=ItemSearch'
	. '&ContentType=text%2Fxml'
	. '&SubscriptionId='.$amazon_key
	. '&AssociateTag='.$ass_id
	. '&Validate=False'
	. '&XMLEscaping=Double'
	. '&SearchIndex=Music'
	. '&Keywords='.$amazon_string
	. '&ResponseGroup=Images,Medium,Tracks';
      
	// 2: Get results of query:
	if ($hf=fopen($file,'r')) {                //open the file from Amazon
		//read the complete file (binary safe) 20040715: $sfile='' added	
    	for ($sfile='';$buf=fread($hf,1024);) { 
        	$sfile.=$buf;
	}
    fclose($hf);
    $a=utf8_decode($sfile);
    
	$data = simplexml_load_string($a);
	}
	
	return $data;
}


$cover_found=0;
// 0.6.5: For backward compatibility: Save 'old' covers: "name.jpg" as "album_id.jpg":
if (file_exists('./covers/'.$amazon_string.'.jpg')) { 
	ampjuke_save_cover('./covers/'.$amazon_string.'.jpg',$amazon_album_id); 
	if (is_writable('./covers/'.$amazon_string.'.jpg')) { // get rid of 'old' cover ("name.jpg"):
		unlink('./covers/'.$amazon_string.'.jpg');
	}
}


if (file_exists('./covers/'.$amazon_album_id.'.jpg')) { 
	print_cover($amazon_album_id); 
	$cover_found=1;
} else { // We don't have a cover, - try Amazon USA first:
	$data=amazon_web_service('us',$amazon_key,urlencode($amazon_string));
	if (isset($data->Items->Item[0]->MediumImage->URL)) {		
		ampjuke_save_cover($data->Items->Item[0]->MediumImage->URL,$amazon_album_id);
		$cover_found=1;
	}
	if ($cover_found==0) { // No cover in USA, - try UK instead:
		$data=amazon_web_service('uk',$amazon_key,urlencode($amazon_string));
		if (isset($data->Items->Item[0]->MediumImage->URL)) {		
			ampjuke_save_cover($data->Items->Item[0]->MediumImage->URL,$amazon_album_id);
			$cover_found=1;
		}
	}
	if ($cover_found==0) { // Still no cover, - last try will be Amazon Germany:
		$data=amazon_web_service('de',$amazon_key,urlencode($amazon_string));		
		if (isset($data->Items->Item[0]->MediumImage->URL)) {		
			ampjuke_save_cover($data->Items->Item[0]->MediumImage->URL,$amazon_album_id);
			$cover_found=1;
		}
	}
	
	if ($cover_found==0) { // Amazon could no provide a cover...
	// ...use the default "_blank.jpg" from AmpJuke:
		ampjuke_save_cover("./covers/_blank.jpg",$amazon_album_id); 
	}	
	// Finally print it:
	print_cover($amazon_album_id); 
}