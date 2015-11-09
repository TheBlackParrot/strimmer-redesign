// http://stackoverflow.com/a/6445104
// it's there, why not use it?

/**
 * Converts an RGB color value to HSL. Conversion formula
 * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
 * Assumes r, g, and b are contained in the set [0, 255] and
 * returns h, s, and l in the set [0, 1].
 *
 * @param   Number  r       The red color value
 * @param   Number  g       The green color value
 * @param   Number  b       The blue color value
 * @return  Array           The HSL representation
 */
function rgbToHsl(r, g, b){
	r /= 255, g /= 255, b /= 255;
	var max = Math.max(r, g, b), min = Math.min(r, g, b);
	var h, s, l = (max + min) / 2;

	if(max == min){
		h = s = 0; // achromatic
	}else{
		var d = max - min;
		s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
		switch(max){
			case r: h = (g - b) / d + (g < b ? 6 : 0); break;
			case g: h = (b - r) / d + 2; break;
			case b: h = (r - g) / d + 4; break;
		}
		h /= 6;
	}

	return [h, s, l];
}

/**
 * Converts an HSL color value to RGB. Conversion formula
 * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
 * Assumes h, s, and l are contained in the set [0, 1] and
 * returns r, g, and b in the set [0, 255].
 *
 * @param   Number  h       The hue
 * @param   Number  s       The saturation
 * @param   Number  l       The lightness
 * @return  Array           The RGB representation
 */
function hslToRgb(h, s, l){
	var r, g, b;

	if(s == 0){
		r = g = b = l; // achromatic
	}else{
		function hue2rgb(p, q, t){
			if(t < 0) t += 1;
			if(t > 1) t -= 1;
			if(t < 1/6) return p + (q - p) * 6 * t;
			if(t < 1/2) return q;
			if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
			return p;
		}

		var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
		var p = 2 * l - q;
		r = hue2rgb(p, q, h + 1/3);
		g = hue2rgb(p, q, h);
		b = hue2rgb(p, q, h - 1/3);
	}

	return [r * 255, g * 255, b * 255];
}

function colorBrightness(rgbcode, percent) {
	var r = parseInt(rgbcode.slice(0, 2), 16),
		g = parseInt(rgbcode.slice(2, 4), 16),
		b = parseInt(rgbcode.slice(4, 6), 16),
		HSL = rgbToHsl(r, g, b),
		newBrightness = HSL[2] + HSL[2] * (percent / 100), 
		RGB;

	RGB = hslToRgb(HSL[0], HSL[1], newBrightness);
	rgbcode = convertToTwoDigitHexCodeFromDecimal(RGB[0])
		+ convertToTwoDigitHexCodeFromDecimal(RGB[1])
		+ convertToTwoDigitHexCodeFromDecimal(RGB[2]);

	return rgbcode;
}

function convertToTwoDigitHexCodeFromDecimal(decimal){
	var code = Math.round(decimal).toString(16);

	(code.length > 1) || (code = '0' + code);
	return code;
}

function sendColor() {
	var color;
	var l;
	var l1;
	var l2;
	if($(".color-boxes").length) {
		color = $("#color-input").val().replace("#","");
		l = $("#perc-bright").val().replace("%","");
		l1 = $("#perc-dark").val().replace("%","");
		l2 = $("#perc-darker").val().replace("%","");
	}

	if(typeof(l) === "undefined") {
		l = getCookie("perc-bright");
		if(l == "") {
			l = 15;
		}
	} else if(l == null || l == "") {
		l = getCookie("perc-bright");
		if(l == "") {
			l = 15;
		}
	}
	if(typeof(l1) === "undefined") {
		l1 = getCookie("perc-dark");
		if(l1 == "") {
			l1 = -15;
		}
	} else if(l1 == null || l1 == "") {
		l1 = getCookie("perc-dark");
		if(l1 == "") {
			l1 = -15;
		}
	}
	if(typeof(l2) === "undefined") {
		l2 = getCookie("perc-darker");
		if(l2 == "") {
			l2 = -30;
		}
	} else if(l2 == null || l2 == "") {
		l2 = getCookie("perc-darker");
		if(l2 == "") {
			l2 = -30;
		}
	}
	if(typeof(color) === "undefined") {
		color = getCookie("color-main");
		if(color == "") {
			color = "3F51B5";
		}
	} else if(color == null || color == "") {
		color = getCookie("color-main");
		if(color == "") {
			color = "3F51B5";
		}
	}

	updateColor(color,l,l1,l2);
	setCookie("color-main",color,365);
	setCookie("perc-bright",l,365);
	setCookie("perc-dark",l1,365);
	setCookie("perc-darker",l2,365);
}

// http://stackoverflow.com/a/11868398
function getContrastYIQ(hexcolor){
	if(hexcolor.length == 3) {
		var tmp = hexcolor;
		hexcolor = tmp[0] + tmp[0] + tmp[1] + tmp[1] + tmp[2] + tmp[2];
	}

    var r = parseInt(hexcolor.substr(0,2),16);
    var g = parseInt(hexcolor.substr(2,2),16);
    var b = parseInt(hexcolor.substr(4,2),16);
    var yiq = ((r*299)+(g*587)+(b*114))/1000;
    return (yiq >= 128) ? 'black' : 'white';
}

function updateColor(color,l,l1,l2) {
	var css_rules = null;
	for(var i=0;i<document.styleSheets.length;i++) {
		if(document.styleSheets[i].ownerNode == $("#maincss")[0]) {
			css_rules = document.styleSheets[i].cssRules;
			break;
		}
	}

	if(css_rules != null) {
		var elements_reg = [".menu-item:active, .menu-item-toggled", ".song_row_toggled td", ".user-menu-item:hover", ".dialog .button:hover", ".user-header:hover"];
		var elements_dark = [".user-wrapper", ".dialog .button"];
		var elements_darker = ".user-menu-item";
		var elements_bright = [".user-menu-item:active", ".dialog .button:active", ".user-header:active"];

		var color_bright = colorBrightness(color,l);
		var color_dark = colorBrightness(color,l1)
		var color_darker = colorBrightness(color,l2);

		$(".color-box.standard").css("background-color","#" + color);
		$(".color-box.bright").css("background-color","#" + color_bright);
		$(".color-box.dark").css("background-color","#" + color_dark);
		$(".color-box.darker").css("background-color","#" + color_darker);

		console.log(color + " : " + color_dark + " : " + color_darker + " : " + color_bright);

		for(var i=0;i<css_rules.length;i++) {
			if(elements_reg.indexOf(css_rules[i].selectorText) != -1) {
				css_rules[i].style["background-color"] = "#" + color;
				css_rules[i].style["color"] = getContrastYIQ(color);
				continue;
			}
			if(elements_darker == css_rules[i].selectorText) {
				css_rules[i].style["background-color"] = "#" + color_darker
				css_rules[i].style["color"] = getContrastYIQ(color_darker);
				continue;
			}
			if(elements_dark.indexOf(css_rules[i].selectorText) != -1) {
				css_rules[i].style["background-color"] = "#" + color_dark;
				css_rules[i].style["color"] = getContrastYIQ(color_dark);
				continue;
			}
			if(elements_bright.indexOf(css_rules[i].selectorText) != -1) {
				css_rules[i].style["background-color"] = "#" + color_bright;
				css_rules[i].style["color"] = getContrastYIQ(color_bright);
				continue;	
			}
		}
	} else {
		console.log("Couldn't find CSS rules?");
	}
}