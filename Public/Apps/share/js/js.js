var yqmWidth = 356;
var yqmHeight=50;
var yqmTop = 515;
var yqmLeft = 142;

var bbWidth = 356;
var bbHeight=30;
var bbTop = 680;
var bbLeft = 142;

var cWidth = 356;
var cHeight=86;
var cTop = 583;
var cLeft = 142;

var bgiWidth = 640;
var bgiHeight = 730;
function resizeLocation(){
	var bgPicObj = document.getElementById('bgPic');
	var yqmTopPercentage = yqmTop/bgiHeight;
    var yqmLeftPercentage = yqmLeft/bgiWidth;
	var yqmWidthPercentage = yqmWidth/bgiWidth;
	var yqmHeightPercentage = yqmHeight/bgiHeight;
    var bbTopPercentage = bbTop/bgiHeight;
    var bbLeftPercentage = bbLeft/bgiWidth;
	var bbWidthPercentage = bbWidth/bgiWidth;
	var bbHeightPercentage = bbHeight/bgiHeight;
	
	var tlObj = document.getElementById('div_yqm');
    tlObj.style.top = bgPicObj.offsetHeight*yqmTopPercentage+'px';
    tlObj.style.left = bgPicObj.offsetWidth*yqmLeftPercentage+'px';
    tlObj.style.width = bgPicObj.offsetWidth*yqmWidthPercentage+'px';
	tlObj.style.height = bgPicObj.offsetWidth*yqmHeightPercentage+'px';
    
    var trObj = document.getElementById('div_bb');
    trObj.style.top = bgPicObj.offsetHeight*bbTopPercentage+'px';
    trObj.style.left = bgPicObj.offsetWidth*bbLeftPercentage+'px';
    trObj.style.width = bgPicObj.offsetWidth*bbWidthPercentage+'px';
	trObj.style.height = bgPicObj.offsetWidth*bbHeightPercentage+'px';
	
	var centerObj = document.getElementById('div_center');
    var widthPercentage = cWidth/bgiWidth;
	var heightPercentage = cHeight/bgiHeight;
	var topPercentage = cTop/bgiHeight;
    var leftPercentage = cLeft/bgiWidth;
    centerObj.style.top = bgPicObj.offsetHeight*topPercentage+'px';
    centerObj.style.left = bgPicObj.offsetWidth*leftPercentage+'px';
    centerObj.style.width = bgPicObj.offsetWidth*widthPercentage+'px';
    centerObj.style.height = bgPicObj.offsetHeight*heightPercentage+'px';
}
window.onresize = resizeLocation;
window.onload = resizeLocation;