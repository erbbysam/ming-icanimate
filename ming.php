<?
/* ming.php
 * (C) 2006-2013 Samuel Erb
 * 
 * This is a generic script to flash converter that uses ming which is a PHP plugin that renders flash.
 * This file takes a script in the format found here http://www.icanimate.com/create/filespec.php
 * To install ming (with php5 & ubuntu): sudo apt-get install php5-ming
 *
 * This was one of the first things I ever coded and put online and have recently cleaned it up open source it, hopefully it will be useful.
*/
 
/* basic setup */
$movie = new SWFMovie();
$movie->setDimension(550,400);
$movie->setBackground("0","0","51");
$movie->setrate (16);

/* instead of connecting to the database, we are going to show an example animation instead */
/* your database query to get animation information would go here */
$animation = get_example_animation();
$user_createdby = 'magicman'; //credit to the original author

/* background color loaded at beginning format: "bRRGGBB|" */
if (substr($animation,0,1) == 'b'){
	$backcolorr = hexdec(substr($animation,1,2));
	$backcolorg = hexdec(substr($animation,3,2));
	$backcolorb = hexdec(substr($animation,5,2));
}else{
	$backcolorr = "0";
	$backcolorg = "0";
	$backcolorb = "51";
}

/* explode all of the frame data */
$frame = explode("|", $animation);
$f = 0;

/* load font */
$font = new SWFFont("_sans");


/* main loop */
while ( $f != sizeof($frame) ){

	/* we are going to use an array to hold objects in this frame that will be deleted after the frame has been rendered */
	$textarray=array();
	
	/* set the frame's background */
	$r_background=rect(0,0,550,400,$backcolorr,$backcolorg,$backcolorb);
	$textarray[] = $movie->add($r_background);

	/* reset default color */
	$colorred="0";
	$colorgreen="255";
	$colorblue="0";

	/* explode the peices of this frame */
	$pieces = explode(";", $frame[$f]);
	$i=0;
	
	/* now draw each peice */
	while ($i != sizeof($pieces)){
		/* change color */
		if (substr($pieces[$i],0,1) == 'c'){
			$colorred = hexdec(substr($pieces[$i],1,2));
			$colorgreen = hexdec(substr($pieces[$i],3,2));
			$colorblue = hexdec(substr($pieces[$i],5,2));
		}

		/* draw line */
		if (substr($pieces[$i],0,1) == 'l'){
			$command = explode(",",substr($pieces[$i],1,strlen($pieces[$i])));
			$line = drawline($command['0'],$command['1'],$command['2'],$command['3'],$colorred,$colorgreen,$colorblue);
			$textarray[] = $movie->add($line);
		}
		
		/* draw rectangle */
		if (substr($pieces[$i],0,1) == 'r'){
			$command = explode(",",substr($pieces[$i],1,strlen($pieces[$i])));
			$rect = rect($command['0'],$command['1'],$command['2'],$command['3'],$colorred,$colorgreen,$colorblue);
			$textarray[] = $movie->add($rect);
		}

		/* draw ovals */
		if (substr($pieces[$i],0,1) == 'o'){
			$command = explode(",",substr($pieces[$i],1,strlen($pieces[$i])));
			$rect = drawOval($command['0'],$command['1'],$command['2'],$command['3'],$colorred,$colorgreen,$colorblue);
			$textarray[] = $movie->add($rect);
		}

		/* draw text */
		if (substr($pieces[$i],0,1) == 't'){
			$command = explode(",",substr($pieces[$i],1,strlen($pieces[$i])),5);
			$t = new SWFTextField();
			$t->setFont($font);
			$t->setColor($colorred,$colorgreen,$colorblue);
			$t->setHeight($command['3']);
			$t->addString($command['2']);
			$ttwo = $movie->add($t);
			$ttwo->moveTo($command['0'], $command['1']);
			$textarray[] = $ttwo;
		}
		
		$i++;
	}
	
	/* pause button */
	$b = new SWFButton();
	$b->addShape(rect(0,368,32,32,0xFF,0x20,0x00), SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
	$b->addAction(new SWFAction("stop();"),SWFBUTTON_MOUSEDOWN);
	$i = $movie->add($b);

	/* play button */
	$b = new SWFButton();
	$b->addShape(rect(32,368,32,32,0x00,0xFF,0x00), SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
	$b->addAction(new SWFAction("play();"),SWFBUTTON_MOUSEDOWN);
	$i = $movie->add($b);

	/* sets up the next frame */
	$movie->nextframe();

	/* deletion squence (otherwise there is persistance between frames amoung objects, wicked slow) */
	for ( $i = 0; $i<sizeof($textarray); $i++) {
		$movie->remove($textarray[$i]);
	}

	$f++;
}

/* replay stuff */
$movie->add(drawline("95","292", "54","355","0","255","0"));
$movie->add(drawline("54","355", "56","325","0","255","0"));
$movie->add(drawline("54","355", "78","344","0","255","0"));
$t = new SWFTextField();
$t->setFont($font);
$t->setColor($colorred,$colorgreen,$colorblue);
$t->setHeight(20);
$t->addString("REPLAY?");
$ttwo = $movie->add($t);
$ttwo->moveTo(99,269);

/* created by stuff (all text uppercase because "y" doesn't show below text box...) */
$t = new SWFTextField();
$t->setFont($font);
$t->setColor($colorred,$colorgreen,$colorblue);
$t->setHeight(20);
$t->addString("THIS ANIMATION WAS CREATED AT ICANIMATE.COM");
$ttwo=$movie->add($t);
$ttwo->moveTo(10,85);
$t = new SWFTextField();
$t->setFont($font);
$t->setColor($colorred,$colorgreen,$colorblue);
$t->setHeight(20);
$t->addString("BY:".strtoupper($user_createdby));
$ttwo=$movie->add($t);
$ttwo->moveTo(10,105);


/* stop at the end of the movie! */
$movie->add(new SWFAction("stop();"));
$movie->nextframe();

/* output is flash! */
header("Content-type: application/x-shockwave-flash"); 
$movie->output(9); 



/* oval function based on MovieClip.prototype.drawOval @
 * http://todbot.com/ming/tsts/flash6/drawMethods/drawOval.as */
function drawOval($x, $y, $radius,$yRadius,$r,$g,$b) {
	$sh = new SWFShape();
	$radius = $radius/2;
	$yRadius = $yRadius/2;
	$sh->setLine(5,$r,$g,$b);
	$theta = M_PI_4;
	$xrCtrl = $radius/cos($theta/2);
	$yrCtrl = $yRadius/cos($theta/2);
	$angle = 0;
	$sh->movePenTo($x+$radius, $y);
	for ( $i = 0; $i<8; $i++) {
		$angle += $theta;
		$angleMid = $angle-($theta/2);
		$cx = $x+cos($angleMid)*$xrCtrl;
		$cy = $y+sin($angleMid)*$yrCtrl;
		$px = $x+cos($angle)*$radius;
		$py = $y+sin($angle)*$yRadius;
		$sh->drawCurveTo($cx, $cy, $px, $py);
	}
	return $sh;
};

/* draw a rectangle */
function rect($x,$y,$xdis,$ydis,$r, $g, $b) {
	$s = new SWFShape();
	$ff = $s->addFill($r, $g, $b);
	$s->setRightFill($ff);
	$s->movePenTo($x, $y);
	$s->drawLineTo($x, $y+$ydis);
	$s->drawLineTo($x+$xdis, $y+$ydis);
	$s->drawLineTo($x+$xdis, $y);
	$s->drawLineTo($x, $y);
	return $s;
};

/* draw a line */
function drawline($x, $y, $xtwo,$ytwo,$r,$g,$b) {
	$sh = new SWFShape();
	$sh->setLine(5,$r,$g,$b); 
	$sh->movePenTo($x, $y);
	$sh->drawLineTo($xtwo, $ytwo);
	return $sh;
};

function get_example_animation() {
	return 'b000000|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l245,209,185,291;l246,211,300,295|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,211,178,282|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,211,171,271|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l246,211,164,259|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l246,215,159,246|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,214,155,231|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,212,153,212|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l179,201,154,184|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l179,201,163,177|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l178,201,176,174|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l181,200,188,174|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l179,199,201,181|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l178,200,187,175|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l177,200,176,173|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l177,201,160,179|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l175,200,148,191|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l175,201,159,178|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l178,202,174,172|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l178,202,188,172|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l179,202,203,180|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l181,200,185,174|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l179,200,171,173|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l178,201,157,182|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,210,179,201;l180,201,148,195|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,209,150,214|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,208,153,229|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l243,210,161,244|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l244,213,165,268|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,216,176,283|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;o245,150,80,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o247,150,64,18|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,150,74,34|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o249,149,38,32|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o249,148,20,18|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o249,148,38,18|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o249,148,72,14|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;o216,95,10,8;o270,94,10,8;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8;o269,93,18,14;o216,96,16,14|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8;o215,96,24,20;o269,93,22,22|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8;o216,96,30,26;o270,94,28,28|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,22,18;o269,94,18,18|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l247,330,288,398;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,330,302,390|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,331,314,378|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,332,328,370|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,331,325,351|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,331,336,339|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,331,334,317|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l248,328,321,296|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l249,327,305,273|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,330,286,262|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,332,267,255|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,332,251,254|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,331,236,248|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,331,222,255|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,331,202,266|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,331,183,280|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,167,297|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l245,334,153,324|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l244,333,152,344|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l245,333,160,367|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l244,333,167,384|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l245,331,185,394|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,332,211,397|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,330,240,398|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,333,260,398|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,331,287,398|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,331,300,395|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,330,318,383|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,329,329,361|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l244,330,320,376|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l244,331,307,388|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l245,334,294,399|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l245,334,309,392|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,333,320,380|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l248,332,328,366|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l247,330,318,379|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,332,307,387|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|cFFFFFF;o245,117,132,140;o217,95,38,38;o271,95,38,40;l246,187,246,332;l246,332,191,397;l246,211,300,295;l245,212,191,287;o248,149,84,8;o217,95,10,8;o270,94,10,8;l246,333,300,398;l208,57,175,45;l231,48,232,28;l277,65,283,40;l253,54,261,36;l223,56,211,41;l190,79,177,67;l297,77,320,65;l176,66,163,59;l175,43,168,27;l212,41,195,29;l232,27,246,18;l261,34,259,22;l281,38,294,29;l321,66,322,43;l164,57,143,61;l169,27,144,26;l195,27,195,11;l248,17,245,6;l257,19,268,7;l294,30,297,16;l322,41,338,38;l143,61,139,81;l143,25,136,42;l195,8,181,4;l245,5,228,10;l268,6,281,7;l298,14,314,18;l339,37,346,58;l138,79,144,108;l136,41,128,74;l180,4,170,19;l228,10,224,28;l281,8,279,23;l315,18,313,42;l345,57,337,80;l129,74,128,95;l144,110,144,126;l172,22,172,39;l223,28,223,39;l281,23,275,40;l315,42,310,57;l336,81,341,106;t65,301,I  ,20;t66,305,   was ,20;t119,313,bored,20|';
}


?>