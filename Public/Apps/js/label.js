function GetStringLen(msg) {
	var len = 0;
	for(var i = 0; i < msg.length; i++) {
		len += GetCharLen(msg.charCodeAt(i))
	}
	return len;
}

function GetCharLen(code) {
	var ret = 0;
	if(code > 127 || code == 94 || (code >= 64 && code <= 90)) {
		ret += 2;
	} else {
		ret++;
	}
	return ret;
}

function label(id, auto) {
	this.cav = document.getElementById(id);
	this.auto = auto;

	this.sx = this.cav.width / 2;
	this.sy = 0;
	this.sw = 0;
	this.sh = 40;

	this.text = new Array();
	this.stext = "";
	this.fonttype = "微软雅黑";
	this.fontsize = "12";
	this.color = "white";
	this.bold = "";
	this.oblique = "";
	this.linewidth = 1;
	this.strokecolor = "";
	this.gradient = false;
	this.gradientstart = "#000000";
	this.gradientend = "#000000";
	this.shadow = false;
	this.shadowcolor = "#000000";
	this.shadowblur = 0;
	this.shadowx = 0;
	this.shadowy = 0;
	this.spacing = 0;
	this.linespacing = 0;
	this.vertical = 0;

	this.salign = "center";
	this.sbaseline = "top";

	this.makeData = function(text, fonttype, fontsize, color, bold, oblique, linewidth, strokecolor, gradient, gradientstart, gradientend, shadow, shadowcolor, shadowblur, shadowx, shadowy, spacing, linespacing, vertical) {
		this.stext = text;
		this.fonttype = fonttype;
		this.fontsize = parseInt(fontsize);
		this.color = "#" + color;
		this.bold = bold;
		this.oblique = oblique;
		this.linewidth = parseInt(linewidth);
		this.strokecolor = "#" + strokecolor;
		this.gradient = gradient;
		this.gradientstart = "#" + gradientstart;
		this.gradientend = "#" + gradientend;
		this.shadow = shadow;
		this.shadowcolor = "#" + shadowcolor;
		this.shadowblur = parseInt(shadowblur);
		this.shadowx = parseInt(shadowx);
		this.shadowy = parseInt(shadowy);
		this.spacing = parseInt(spacing);
		this.linespacing = parseInt(linespacing);
		this.vertical = parseInt(vertical);

		var sc = parseInt(this.cav.width * 2 / (this.fontsize + this.spacing * 2) - 1);
		if(this.vertical == 1) {
			sc = parseInt(this.cav.height * 2 / (this.fontsize + this.spacing * 2) - 1);
		}
		this.text.splice(0, this.text.length);
		var index = 0;
		while(index < this.stext.length) {
			var tlen = 0;
			var sindex = index;
			while(tlen < sc) {
				var len = GetCharLen(this.stext.charCodeAt(index));
				tlen += len;
				index++;
			}
			var showtext = this.stext.substr(sindex, index - sindex);
			this.text.push(showtext);
		}
		if(auto) {
			this.cav.height = this.text.length * (this.fontsize+this.linespacing);
		}
		this.ctx = this.cav.getContext("2d");
		this.draw(this.ctx);
	}
	this.getwidth = function() {
		var max = 0;
		for(var i = 0; i < this.text.length; i++) {
			var t = this.text[i];
			var len = GetStringLen(t);
			if(len > max)
				max = len;
		}
		return max * (this.spacing + this.fontsize);
	}
	this.drawone = function(ctx, txt, os) {
		if(this.vertical != 1) {
			if(this.spacing == null || this.spacing == 0) {
				ctx.fillText(txt, this.sx, os);
				if(this.linewidth > 0) {
					ctx.strokeText(txt, this.sx, os);
				}
			} else {
				var size = parseInt(this.fontsize);
				var len = GetStringLen(txt);
				var x = this.sx - len * size / 4 - size / 2;
				var nowl = -len / 2;
				for(var i = 0; i < txt.length; i++) {
					var t = txt[i];
					var l = GetCharLen(txt.charCodeAt(i));
					x += size * l / 2;
					nowl += l;
					var offset = 0;
					if(l == 1)
						offset = size / 4;
					var s = nowl * this.spacing;
					ctx.fillText(t, x + offset + s, os);
					if(this.linewidth > 0) {
						ctx.strokeText(t, x + offset + s, os);
					}
				}
			}
		} else {
			var size = parseInt(this.fontsize);
			var len = txt.length;
			var y = 0;
			var spacing = this.spacing | 0;
			for(var i = 0; i < txt.length; i++) {
				var t = txt[i];
				ctx.fillText(t, this.sx + os, y + i * (size + spacing));
				if(this.linewidth > 0) {
					ctx.strokeText(t, this.sx + os, y + i * (size + spacing));
				}
			}
		}
	}
	this.draw = function(ctx) {
		ctx.clearRect(0, 0, this.cav.width, this.cav.height);
		ctx.save();
		var font = "";
		if(this.oblique)
			font += "oblique ";
		if(this.bold)
			font += "bold ";
		font += this.fontsize + "px " + this.fonttype;
		ctx.font = font;
		ctx.fillStyle = this.color;
		if(this.gradient) {
			var w = this.getwidth();
			var start = (this.cav.width - w) / 2;
			var gradient = ctx.createLinearGradient(start, 0, start + w, 0);
			gradient.addColorStop(0, this.gradientstart);
			gradient.addColorStop(1, this.gradientend);
			ctx.fillStyle = gradient;
		}
		ctx.textAlign = this.salign;
		ctx.textBaseline = this.sbaseline;
		if(this.linewidth > 0) {
			ctx.lineWidth = this.linewidth;
			ctx.strokeStyle = this.strokecolor;
		}
		if(this.shadow) {
			ctx.shadowColor = this.shadowcolor;
			ctx.shadowBlur = this.shadowblur;
			ctx.shadowOffsetX = this.shadowx;
			ctx.shadowOffsetY = this.shadowy;
		}
		for(var i = 0; i < this.text.length; i++) {
			this.drawone(ctx, this.text[i], i * (this.fontsize + this.linespacing));
		}
		ctx.restore();
	}
}