ming-icanimate
==============

### about
Ming is a PHP plugin that renders flash. This is the drawing script used by http://www.icanimate.com which I placed in a read-only state due to inactivity.

To install ming (with php5 & ubuntu): sudo apt-get install php5-ming

### ming.php
This script creates flash animations from a text script using PHP.

### example
Copy example.html & ming.php into a webserver with PHP & the ming plugin installed and navigate to example.html!

I have also hosted it myself at http://www.icanimate.com/watch/github/example.html

### How to use
Create an animation or means of creating an animation with the script format below and embed it with ming.php using the following html:
```html
<object codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="550" height="400" id="home" align="">
	<param name="movie" value='ming.php'>
	<param name="quality" value="high">
	<param name="bgcolor" value="#000033">
	<embed src='ming.php' quality="high" bgcolor="#000033" width="550" height="400" name="home" align="" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">
</object>
```
Note that you could also call ming.php here with a $_GET variable, ie. 'ming.php?animation=32'

### script format
This file takes a script in the format found here http://www.icanimate.com/create/filespec.php or below:

tag	             -  what it does
```
bRRGGBB          -  sets a new background color(can only be declared at the start of the data)
|                - creates a space in between frames
;                - is placed in between individual peices of content within the frame
lX1,Y1,X2,Y2     - creates a line(l) between the points X1,Y1,X2,Y2
tX,Y,TEXT,SIZE   - create an unique text object with text TEXT of size SIZE at X,Y
rX,Y,W,H         - this will create a rectangle at X,Y with a width W and height H(x1,y1 always has to be greater than x2,y2)
oX,Y,W,H         - this will create a oval at X,Y with a width W and height H
cRRGGBB          - defines a new color on that frame, everything after that on the frame will be this color. Defaults to a green color and can be used a infinite amount vs. the background which can only be used once. ALL color is in hex codes i.e. what html uses.
```


### License
Copyright (C) 2006-2013 [Samuel Erb] (http://erbbysam.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.