// link
var banner_link = "http://www.gakuma.net/";
// 320x50
var img1 = "../fb/banner/320-50.jpg";
// 468x60
var img2 = "../fb/banner/468-60.jpg";
// 728x90
var img3 = "../fb/banner/728-90.jpg";
// 970x90
var img4 = "../fb/banner/970-90.jpg";

window.onload = function() {

	var eleP = document.createElement('p');
	eleP.setAttribute('class', 'footer-banner');
	
	var eleC = document.createElement('a');
	eleC.setAttribute('class', 'footer-banner-close');
	eleC.setAttribute('href', '#');
	eleC.onclick = function(){var class_name = document.getElementsByClassName('footer-banner')[0];var dom_obj_parent=class_name.parentNode;dom_obj_parent.removeChild(class_name);};
	eleP.appendChild(eleC);
	
	var link = document.createElement('a');
	link.setAttribute('href', banner_link);
	link.setAttribute('target', '_blank');
	
	if (img1) {
		var eleImg1 = document.createElement('img');
		eleImg1.setAttribute('class', 'banner-1');
		eleImg1.setAttribute('src', img1);
		link.appendChild(eleImg1);
	}
	
	if (img2) {
		var eleImg2 = document.createElement('img');
		eleImg2.setAttribute('class', 'banner-2');
		eleImg2.setAttribute('src', img2);
		link.appendChild(eleImg2);
	}

	if (img3) {
		var eleImg3 = document.createElement('img');
		eleImg3.setAttribute('class', 'banner-3');
		eleImg3.setAttribute('src', img3);
		link.appendChild(eleImg3);
	}

	if (img4) {
		var eleImg4 = document.createElement('img');
		eleImg4.setAttribute('class', 'banner-4');
		eleImg4.setAttribute('src', img4);
		link.appendChild(eleImg4);
	}

	eleP.appendChild(link);
	
	document.body.appendChild(eleP);
	
	var link = document.createElement('link');
	link.href = '../fb/footer_banner.css';
	link.rel = 'stylesheet';
	link.type = 'text/css';
	var h = document.getElementsByTagName('head')[0];
	h.appendChild(link);
}
