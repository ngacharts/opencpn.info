// ### START Global Variables ###
var corner_base = "http://opencpn.xtr.cz/nga-charts/";
var corner = "sw";
var img1_top = 0;
var img1_left = 0;
var img1_cx;
var img1_cy;
var img2_top = 0;
var img2_left = 0;
var img2_cx;
var img2_cy;
var img2_zoomfactor = 10;
var cont_width;
var cont_height;
var crop_width;
var crop_height;
var crop_top = 0;
var crop_left = 0;
var def_w;
var def_h;
var def_sw_x;
var def_sw_y;
var def_se_x;
var def_se_y;
var def_ne_x;
var def_ne_y;
var def_nw_x;
var def_nw_y;
var sw_lat;
var sw_lng;
var se_lat;
var se_lng;
var ne_lat;
var ne_lng;
var nw_lat;
var nw_lng;
var scrollPos;
var session_active;
var exitConfirmed;
var changed = 0;
var showBT = false;
var led = new Array('images/led_red.png', 'images/led_yellow.png', 'images/led_green.png', 'images/led_gray.png');
var ledTitle = new Array('missing', 'incomplete', 'complete', 'not available');
var id2Name = new Object();
var ls = supports_html5_storage();
id2Name['status'] = 'Chart Status';
id2Name['chart_type'] = 'Chart Type';
id2Name['projection'] = 'Projection';
id2Name['datum'] = 'Datum';
id2Name['soundings'] = 'Soundings Unit';
id2Name['soundings_datum'] = 'Soundings Datum';
// ### END Global Variables ###

$.extend({URLEncode:function(c){var o='';var x=0;c=c.toString();var r=/(^[a-zA-Z0-9_.]*)/;
  while(x<c.length){var m=r.exec(c.substr(x));
    if(m!=null && m.length>1 && m[1]!=''){o+=m[1];x+=m[1].length;
    }else{if(c[x]==' ')o+='+';else{var d=c.charCodeAt(x);var h=d.toString(16);
    o+='%'+(h.length<2?'0':'')+h.toUpperCase();}x++;}}return o;},
URLDecode:function(s){var o=s;var binVal,t;var r=/(%[^%]{2})/;
  while((m=r.exec(o))!=null && m.length>1 && m[1]!=''){b=parseInt(m[1].substr(1),16);
  t=String.fromCharCode(b);o=o.replace(m[1],t);}return o;}
});

(function($){
$.unserialise = function(Data){
		var Data = Data.split("&");
        var Serialised = new Array();
		arr_keys = new Array();
        $.each(Data, function(){
            var Properties = this.split("=");
			if(Properties[1] == '') Properties[1] = null;
            Serialised[Properties[0]] = Properties[1];
			arr_keys.push(Properties[0]);
        });
        return Serialised;
    };
})(jQuery);

Storage.prototype.setObject = function(key, value) {
	this.setItem(key, JSON.stringify(value));
}
Storage.prototype.getObject = function(key) {
	return this.getItem(key) && JSON.parse(this.getItem(key));
}

// ### START document.ready function ###
$(document).ready(function() {
	session_active = window.setInterval("keepSessionActive()", 1000 * 60 * 4); // 1000 * 60 * 4
	
	// show browser info
	//$("#response").text(supports_html5_storage() ? "Local storage available" : "Local storage NOT available");

	if($("div#checklist").height() > (window.innerHeight - 55)) $("div#checklist").height((window.innerHeight - 55) + "px");
	$("table#cecklist_table").click(function() {checklist();});
	
	cont_width = $("#container_1").width();
	cont_height = $("#container_1").height();
	crop_width = Math.round(cont_width / img1_zoomfactor / img2_zoomfactor);
	crop_height = Math.round(cont_height / img1_zoomfactor / img2_zoomfactor);
	def_w = cont_width / 2 / img2_zoomfactor;
	def_h = cont_height / 2 / img2_zoomfactor;
	def_sw_x = def_w;
	def_sw_y = chart_height - img_height + def_h;
	def_nw_x = def_w;
	def_nw_y = def_h;
	def_ne_x = chart_width - img_width + def_w;
	def_ne_y = def_h;
	def_se_x = chart_width - img_width + def_w;
	def_se_y = chart_height - img_height + def_h;
//alert(def_sw_x+", "+def_sw_y+"\n"+def_nw_x+", "+def_nw_y+"\n"+def_ne_x+", "+def_ne_y+"\n"+def_se_x+", "+def_se_y);
	$("#crop").width(crop_width).height(crop_height);

	$("#crop").draggable({ containment: 'parent', cursor: 'move' }).bind( "dragstop", cropDragEnd);

	var offset = $("#container_2").offset();
	var diff_width =  Math.round(img_width * img2_zoomfactor - cont_width);
	var diff_height =  Math.round(img_height * img2_zoomfactor - cont_height);
	var x1 = Math.round(offset.left - diff_width);
	var y1 = Math.round(offset.top - diff_height);
	var x2 = x1 + diff_width;
	var y2 = y1 + diff_height;
	$("#img2").draggable({ cursor: 'move', containment: [x1, y1, x2, y2] }).bind( "dragstop", updatePosition2);

	img1_cx = (img_width / img1_zoomfactor) - (cont_width / 2);
	if(img1_left < 0) img1_cx = img1_cx + (img1_left * -1);
	img1_cy = (img_height / img1_zoomfactor) - (cont_height / 2);
	if(img1_top < 0) img1_cy = img1_cy + (img1_top * -1);

	$(".toggle_container").hide();
	
	$("h2.trigger").click(function(){
		var test = $(this).attr("id");
		var pos = $(this).position();
		$(this).toggleClass("active").next().slideToggle("slow", function() {
			if($("#"+test).hasClass('active')) {
				$.scrollTo($(this), 800, {offset: -98});
			}
			else {
				var clos = $("#"+test).siblings('h2[class*="active"]').attr('id');
				if(typeof(clos) != 'undefined') $.scrollTo("h2#"+clos, 800, {offset: -52});
				else $.scrollTo("h2#a1", 300, {offset: - 52});
			}
			
		});
		if(test == 'a3') {
				var tabHeight = $("table#cal_corner_tab").height();
				$("table#cal_corner_instruct_tab").height((tabHeight + 12) + "px"); // 12: value depends on margin + padding values
				$("table#cal_corner_instruct_tab").find('td').height((tabHeight - 16) + "px"); // 16: value depends on margin + padding values
		}
	});
	
	$("a.trigger").click(function(event){
		event.preventDefault();
	});
	
	$("input[name='cal_corner']").change( function() {
		var sel = $(this).val();
		$("table#cal_corner_tab").find("tr").removeClass("highlight");
		$('input[value="'+sel+'"]').parents("tr").addClass("highlight");
		switch(sel) {
			case "SW":
				corner = "sw";
				break;
			case "SE":
				corner = "se";
				break;
			case "NE":
				corner = "ne";
				break;
			case "NW":
				corner = "nw";
				break;
		}
		corner_src = corner_base + id + "/" + id + "_" + corner + ".png";
		$("#img1").attr("src",corner_src);
		$("#img2").attr("src",corner_src);
		setImg2Pos();
		updateCoordinates();
		//statusLED($(this).attr('id'));
	});

	if(ls) {
		var lsItemCount = localStorage.length;
		if(lsItemCount > 0) {
			for (var i = 0; i < lsItemCount; i++) {
				var lsItem = localStorage.key(i);
				lsItemID = lsItem.substr(10,5);
				if(lsItemID != id) localStorage.removeItem(lsItem);
			}
		}
	}
	
	if(ls && lastEdit) {
		var prevData = localStorage.getItem("chartdata_"+id);
		if(prevData != null) {
			var restoreData = new Array();
			restoreData = $.unserialise(prevData);
			var restoreDataKeys = arr_keys;
			arr_keys = null;
			if(restoreData.ts == lastEdit) {
				var answer = confirm("You have unsaved changes\nof a previous session for this chart.\n\nClick 'OK' to restore these values or 'Cancel' to proceed without restoring them.");
				if(answer) {
					for (var i = 0; i < (restoreDataKeys.length - 2); i++) {
						if(restoreData[restoreDataKeys[i]] !== null) {
							if(restoreDataKeys[i] == 'cal_corner' || restoreDataKeys[i] == 'ts') {
								continue;
							}
							else if(restoreDataKeys[i].indexOf("coordh_") != -1) {
								$('input[name="'+restoreDataKeys[i]+'"]').val(restoreData[restoreDataKeys[i]]);
								var tmp = restoreDataKeys[i].replace(/coordh/g, "coord");
								$('input#'+tmp).val(restoreData[restoreDataKeys[i]]);
								corner = restoreDataKeys[i].substr(8,2);
								if(restoreDataKeys[i].indexOf("xcoordh_") != -1) {
									chart['X'+corner] = restoreData[restoreDataKeys[i]];
								}
								else if(restoreDataKeys[i].indexOf("ycoordh_") != -1) {
									chart['Y'+corner] = restoreData[restoreDataKeys[i]];
									$('input#cal_corner_'+corner).attr("checked", true);
									$("input[name='cal_corner']").trigger('change');
									setImg2Pos();
									//updateCoordinates();
								}
							}
							else if(restoreDataKeys[i] == 'status_other' || restoreDataKeys[i] == 'comment') restoreData[restoreDataKeys[i]] = $.URLDecode(restoreData[restoreDataKeys[i]]);
							else $(':input[name="'+restoreDataKeys[i]+'"]').val(restoreData[restoreDataKeys[i]]);
						}
					}
				}
			}
			else localStorage.removeItem("chartdata_"+id);
		}
	}

	$("input[class='digit2']").change(function() {
		if($(this).val().length == 1) {
			var newVal = '0' + $(this).val();
			$(this).val(newVal);
		}
	});
	$("input[class='digit2']").trigger('change');

	if(chart['Xsw'] !== null && chart['Ysw'] !== null) {
		setImg2Pos();
	}

	$('form#chart_calibration_form').submit(function() {
		// perform validation
		var check = validateInputs(); // @boolean
		if(!check) {
			$(window).scrollTop(0);
			alert("Unfortunately there are some validation errors - see error messages at the top of the page!\nPlease correct the errors and then again click 'Save & Exit'.");
			return false;
		}
		else {
//alert("No validation errors!");
		if($("input#datum_adj_x").val().toString() == '0.0') $("input#datum_adj_x").val('');
		if($("input#datum_adj_y").val().toString() == '0.0') $("input#datum_adj_y").val('');
//alert($(this).serialize());
		var dataString = $(this).serialize();
		formSubmit(dataString);
		return false;
		}
	});
	
	window.setTimeout("window.scrollTo(0,0)", 150);
	
	$('select').each(function() {
		var selID = $(this).attr('id');
		var other = $(this).val();
		if(other == "other" || ($(this).attr('id') == 'status' && other == '12')) {
			var sib = $(this).parent("td").next();
			sib.removeClass("hidden");
		}
		if(selID == 'datum' && other != 'WGS84' && other != -9999) $('tr.datum_adjust_tr').show();
	});
	
	$('input[class^="reset_but_"]').each(function(index) {
		$(this).attr("id", "reset_but_"+(index + 1));
		var title;
		switch($(this).attr("class")) {
			case "reset_but_input":
				title = $(this).parent('td').next().next().find('input').attr('id');
				if(title == 'scale') $(this).attr('title', 'Scale at');
				break;
			case "reset_but_select":
				title = $(this).parent('td').next().next().find('select').attr('id');
				if(title == 'status') $(this).attr('title', 'Chart Status');
				else if(title == 'chart_type') $(this).attr('title', 'Chart Type');
				else if(title == 'projection') $(this).attr('title', 'Projection');
				else if(title == 'datum') $(this).attr('title', 'Datum');
				else if(title == 'soundings') $(this).attr('title', 'Soundings Unit');
				else if(title == 'soundings_datum') $(this).attr('title', 'Soundings Datum');
				break;
			case "reset_but_adj_gd":
				$(this).attr('title', 'Adjustments to Chart Datum');
				break;
			case "reset_but_coords_sw":
				$(this).attr('title', 'Coordinates of the SW corner');
				break;
			case "reset_but_coords_ne":
				$(this).attr('title', 'Coordinates of the NE corner');
				break;
			case "reset_but_corner":
				title = $(this).parent('td').prev().find('input').attr('id').substr(7, 2);
				if(title == 'sw') $(this).attr('title', 'SW corner');
				else if(title == 'nw') $(this).attr('title', 'NW corner');
				else if(title == 'ne') $(this).attr('title', 'NE corner');
				else if(title == 'se') $(this).attr('title', 'SE corner');
				break;
			case "reset_but_comment":
				$(this).attr('title', 'Comment');
				break;
			default:
				title = "empty";
				break;
		}
	});

	// ### TOOL-TIPS ###
	// Override Default values
	//jQuery.bt.options.trigger = 'hover';
	jQuery.bt.options.showTip = function(box){$(box).fadeIn(500);}
	jQuery.bt.options.hideTip = function(box, callback){$(box).animate({opacity: 0}, 500, callback);}
	jQuery.bt.options.shadow = true;
	jQuery.bt.options.shadowOffsetX = 3;
	jQuery.bt.options.shadowOffsetY = 3;
	jQuery.bt.options.shadowBlur = 8;
	jQuery.bt.options.shadowColor = 'rgba(0,0,0,.9)';
	jQuery.bt.options.shadowOverlap = false;
	jQuery.bt.options.noShadowOpts = {strokeStyle: '#000', strokeWidth: 1};
	jQuery.bt.options.closeWhenOthersOpen = true;
	jQuery.bt.options.preShow = function(box){
		if(showBT === false) this.btOff();
	}
	
	$('h3#chart_no').bt({
		trigger: 'none', //['focus mouseover', 'blur mouseout'],
		positions: ['right'],
		postShow: function(box){$(box).css("position", "fixed");}
	});
	/*
	$('table#cecklist_table').bt('Click to close', {
		positions: ['bottom']
	});
	*/
	$('img#img_zl2').bt({
		positions: ['most']
	});
	$('select#status').bt({
		trigger: ['focus', 'blur'],
		positions: ['left']
	});
	$('select#chart_type').bt({
		trigger: ['focus', 'blur'],
		positions: ['left']
	});
	$('input#noPP').bt({
		positions: ['left']
	});
	$('select#projection').bt({
		trigger: ['focus', 'blur'],
		positions: ['left']
	});
	$('select#datum').bt({
		trigger: ['focus', 'blur'],
		positions: ['left']
	});
	$('input#noDTM').bt({
		positions: ['left']
	});
	
	$('input[id$="_other"]').bt({
		trigger: ['focus mouseover', 'blur mouseout'],
		positions: ['right']
	});
	
	$('img#coord_sw').bt('Enter the coordinates<br /><strong>(digits only)</strong><br />of the SW corner in the table below exactly as printed on the chart<br />and select the hemisphere!<br /><br />To zoom the images in and out, please use<br /><strong>CTRL</strong> + <strong>Num +</strong><br />and<br /><strong>CTRL</strong> + <strong>Num -</strong><br />or use<br /><strong>CTRL</strong> + <strong>mouse wheel</strong><br /><br /><strong>CTRL</strong> + <strong>0</strong><br />resets to the default size.', {
		cssStyles: {textAlign: 'center'},
		hoverIntentOpts: {interval: 500, timeout: 1000}
	});
	$('img#coord_ne').bt('Enter the coordinates<br /><strong>(digits only)</strong><br />of the NE corner in the table below exactly as printed on the chart<br />and select the hemisphere!<br /><br />To zoom the images in and out, please use<br /><strong>CTRL</strong> + <strong>Num +</strong><br />and<br /><strong>CTRL</strong> + <strong>Num -</strong><br />or use<br /><strong>CTRL</strong> + <strong>mouse wheel</strong><br /><br /><strong>CTRL</strong> + <strong>0</strong><br />resets to the default size.', {
		hoverIntentOpts: {interval: 500, timeout: 1000},
		cssStyles: {textAlign: 'center'},
		
	});
	
	$('input[id^="lat_deg_"]').bt('Enter the latitude degrees<br />as unsigned value<br />as printed on the chart.', {
		trigger: ['focus', 'blur'],
		positions: ['top'],
		cssStyles: {textAlign: 'center'}
	});
	$('input[id^="lat_min_"]').bt('Enter the latitude minutes<br />as printed on the chart.', {
		trigger: ['focus', 'blur'],
		positions: ['top'],
		cssStyles: {textAlign: 'center'}
	});
	$('input[id^="lat_sec_"]').bt('Enter the latitude seconds<br />as printed on the chart.', {
		trigger: ['focus', 'blur'],
		positions: ['top'],
		cssStyles: {textAlign: 'center'}
	});
	$('input[id^="lng_deg_"]').bt('Enter the longitude degrees<br />as unsigned value<br />as printed on the chart.', {
		trigger: ['focus', 'blur'],
		positions: ['top'],
		cssStyles: {textAlign: 'center'}
	});
	$('input[id^="lng_min_"]').bt('Enter the longitude minutes<br />as printed on the chart.', {
		trigger: ['focus', 'blur'],
		positions: ['top'],
		cssStyles: {textAlign: 'center'}
	});
	$('input[id^="lng_sec_"]').bt('Enter the longitude seconds<br />as printed on the chart.', {
		trigger: ['focus', 'blur'],
		positions: ['top'],
		cssStyles: {textAlign: 'center'}
	});
	$('select[id^="lat_ns_"]').bt('Select the latitude hemisphere.', {
		trigger: ['focus', 'blur'],
		positions: ['left']
	});
	$('select[id^="lng_we_"]').bt('Select the longitude hemisphere.', {
		trigger: ['focus', 'blur'],
		positions: ['left']
	});
	
	$('li#selection_square_li').bt('<img src="images/crop_square.png" width="23" height="23" />', {
		positions: ['left'],
		width: '33px'
	});
	$('li#scale_selection_li').bt('<img src="images/scale_buttons.png" width="268" height="65" />', {
		positions: ['left'],
		width: '278px'
	});
	$('li#move_buttons_li').bt('<img src="images/move_buttons.png" width="206" height="147" />', {
		positions: ['left'],
		width: '216px'
	});
	
	groupScaleAt = new Array('pp_deg', 'pp_min', 'noPP');
	
	groupDatumAdjust = new Array();
	$("table#header_data_tab").find("tr.datum_adjust_tr").each(function(){
		$(this).find(':input').each(function(){
			if($(this).attr('id').indexOf("reset_but") == -1) { // && $(this).attr('id').indexOf("_other") == -1  && $(this).attr('id') != 'noDTM'
				groupDatumAdjust.push($(this).attr('id'));
			}
		});
	});
	
	groupCoordsSW = new Array();
	$("table#coords_sw_tab").find(':input').each(function(){
		if($(this).attr('id').indexOf("reset_but") == -1) {
			groupCoordsSW.push($(this).attr('id'));
		}
	});
	
	groupCoordsNE = new Array();
	$("table#coords_ne_tab").find(':input').each(function(){
		if($(this).attr('id').indexOf("reset_but") == -1) {
			groupCoordsNE.push($(this).attr('id'));
		}
	});
	/*
	for (var i = 0; i < groupDatumAdjust.length; i++) {
		alert(groupDatumAdjust[i]);
	}
	*/

	$('input[name="cal_corner"][value="SW"]').attr("checked", true).trigger('change');

	$.validity.setup({ outputMode:'custom' });

	if(!$.cookie('chartnumber')) $.cookie('chartnumber', id, { path: '/', domain: 'opencpn.info', secure: false });

	$('input[type="checkbox"]').trigger('change');
	$("select#datum").trigger('change');

	$("input").change(toggleResetButton);
	$("select").change(toggleResetButton);
	$("textarea").change(toggleResetButton);
	
	if($.cookie('tooltips')) {
		if($.cookie('tooltips') == '0') $("input#bt").attr("checked", false);
	}
	$("input#bt").change(function() {
		if($(this).attr("checked") == true) {
			showBT = true;
			$.cookie('tooltips', '1', { expires: 30, path: '/', domain: 'opencpn.info', secure: false });
		}
		else {
			showBT = false;
			$.cookie('tooltips', '0', { expires: 30, path: '/', domain: 'opencpn.info', secure: false });
		}
	});
	$("input#bt").trigger("change");
	
	$('form#chart_calibration_form').find(':input').each(function(){
		$(this).change(function(){
			if(changed !== 1) {
				changed = 1;
			}
			statusLED($(this).attr('id'));
			updateLocalStorage();
		});
		if($(this).attr('id')) {
			statusLED($(this).attr('id'));
		}
	});

	$('h3#chart_no').btOn();
});
// ### END document.ready function ###


// ### START page/ helper functions ###
function updateLocalStorage() {
	if(ls) {
//alert($('form#chart_calibration_form').serialize());
		var data = "ts=" + lastEdit + "&" + $('form#chart_calibration_form').serialize();
//alert(data);
		localStorage.setItem('chartdata_'+id, data);
	}
}

function statusLED(id) {
//alert("statusLED: "+id);
	if(!id) alert("ERROR: No ID!");
	var group = '';
	
	// group: Comment
	if(id == 'comment') {
		group = 'comment';
		var status = checkDataStatus(id, group);
		if(status) var titleText = 'Comment present';
		else {
			status = 3;
			var titleText = 'No comment';
		}
		$("img#led_comment").attr("src", led[status]).attr("title", titleText);
	}
	
	// group: Calibration corners
	else if(id.substr(0,11) == 'cal_corner_') {
		group = 'cal_corner';
		var status = checkDataStatus(id, group);
		if(status >= 0) $("img#led_"+id).attr("src", led[status]).attr("title", "Values for '"+id.substr(11,2).toUpperCase()+" corner' are "+ledTitle[status]+".");
	}
	
	//group: SW/NE coordinates
	else if(id.substr(0,4) == 'lat_' || id.substr(0,4) == 'lng_') {
		var parentTable = $('#'+id).parents('table').attr('id');
		if(typeof(parentTable) != 'undefined') {
			if(parentTable == 'coords_sw_tab') {
				group = 'coords_sw';
				var status = checkDataStatus(id, group);
				if(status >= 0) $("img#led_coords_sw").attr("src", led[status]).attr("title", "Values for 'SW coordinates' are "+ledTitle[status]+".");
			}
			else if(parentTable == 'coords_ne_tab') {
				group = 'coords_ne';
				var status = checkDataStatus(id, group);
				if(status >= 0) $("img#led_coords_ne").attr("src", led[status]).attr("title", "Values for 'NE coordinates' are "+ledTitle[status]+".");
			}
		}
	}
	
	//group: General chart and header data
	else {
		var parentTable = $('#'+id).parents('table').attr('id');
		if(typeof(parentTable) != 'undefined') {
			if(parentTable == 'header_data_tab') {
				if($.inArray(id, groupScaleAt) != -1) {
					group = 'scale_at';
					var status = checkDataStatus(id, group);
					if(status >= 0) $("img#led_scale_at").attr("src", led[status]).attr("title", "Values for 'Scale at' are "+ledTitle[status]+".");
				}
				else if($.inArray(id, groupDatumAdjust) != -1) {
					group = 'datum_adjust';
					var status = checkDataStatus(id, group);
					if(status >= 0) $("img#led_datum_adjust").attr("src", led[status]).attr("title", "Values for 'Adjustments to Chart Datum' are "+ledTitle[status]+".");
				}
				else {
					group = 'a1';
					if(id.indexOf("_other") != -1) id = id.substring(0,id.length - 6);
					var status = checkDataStatus(id, group);
//alert("ID: "+id);
					if(status >= 0) $("img#led_"+id).attr("src", led[status]).attr("title", "Value for '"+id2Name[id]+"' is "+ledTitle[status]+".");
				}
			}
		}
	}
}

function checkDataStatus(id, group) {
//alert("checkDataStatus");
	var val = false;
	var stat = false;
	var valCount = 0;
	switch(group) {
		case"scale_at":
			var check = inputVal('noPP');
			if(check) stat = 2;
			else {
				var check1 = inputVal('pp_deg');
				var check2 = inputVal('pp_min');
				if(check1 && check2) stat = 2;
				else if(check1 || check2) stat = 1;
				else stat = 0;
			}
			break;
		case"datum_adjust":
			var check_datum = $('select#datum').val();
			if(check_datum == -9999 || check_datum == 'WGS84') stat = 3;
			else {
				var check = inputVal('noDTM');
				if(check) stat = 2;
				else {
					for (var i = 0; i < groupDatumAdjust.length; i++) {
						val = inputVal(groupDatumAdjust[i]);
						if(val && val != -9999) valCount++;
					}
					var a = 1;
					if($('select#datum_correction').val() != 'other') a++;
					if(valCount == (groupDatumAdjust.length - a)) stat = 2;
					else if(valCount > 0) stat = 1;
					else  stat = 0;
				}
			}
			break;
		case"a1":
			var check = inputVal(id);
			if(check && check != -9999) stat = 2;
			else  stat = 0;
			break;
		case"coords_sw":
			for (var i = 0; i < groupCoordsSW.length; i++) {
				val = inputVal(groupCoordsSW[i]);
				if(val && val != -9999) valCount++;
			}
			if(valCount == groupCoordsSW.length) stat = 2;
			else if(valCount > 0) stat = 1;
			else  stat = 0;
			break;
		case"coords_ne":
			for (var i = 0; i < groupCoordsNE.length; i++) {
				val = inputVal(groupCoordsNE[i]);
				if(val && val != -9999) valCount++;
			}
			if(valCount == groupCoordsNE.length) stat = 2;
			else if(valCount > 0) stat = 1;
			else  stat = 0;
			break;
		case"cal_corner":
			var corner = id.substr(11,2);
			var check_x = inputVal('xcoord_'+corner);
			var check_y = inputVal('ycoord_'+corner);
			switch(corner) {
				case"sw":
					var x = def_sw_x;
					var y = def_sw_y;
					break;
				case"nw":
					var x = def_nw_x;
					var y = def_nw_y;
					break;
				case"ne":
					var x = def_ne_x;
					var y = def_ne_y;
					break;
				case"se":
					var x = def_se_x;
					var y = def_se_y;
					break;
			}
			if(!check_x && !check_y) stat = 0;
			else if(check_x == x && check_y == y) stat = 0;
			else stat = 2;
			break;
		case"comment":
			var check = inputVal(id);
			if(check) stat = 2;
			else stat = 3;
			break;
		default:
			break;
	}
	return stat;
}

function inputVal(id) {
	var retVal = false;
	var tag = $('#'+id).get(0).tagName.toLowerCase();
	switch(tag) {
		case"select":
			retVal = $('#'+id).val();
			if(retVal == 'other' || retVal == 12) {
				retVal = $('#'+id+'_other').val();
			}
			break;
		case"input":
			var type = $('#'+id).attr("type");
			if(type == 'text' && id != 'datum_adj_x' && id != 'datum_adj_y') retVal = $('#'+id).val();
			else if(type == 'text' && (id == 'datum_adj_x' || id == 'datum_adj_y')) {
				retVal = $('#'+id).val();
				if(retVal == '0.0') retVal = '';
			}
			else if(type == 'checkbox' || type == 'radio') {
				retVal = $('#'+id).attr("checked");
			}
			break;
		case"textarea":
			retVal = $("textarea#"+id).val();
			break;
		default:
			break;
	}
	return retVal;
}

function ngaViewer(link) {
	if(typeof(ngaViewerWindow) != 'undefined') {
		if(!ngaViewerWindow.closed) ngaViewerWindow.focus();
		else ngaViewerWindow = window.open(link, "ngaViewerWindow");
	}
	else {
		ngaViewerWindow = window.open(link, "ngaViewerWindow");
		ngaViewerWindow.focus();
	}
}


function checklist() {
//if(tar) alert($(tar).parents("tr.hl").first().children("td").first().text());
	var up = '▲';
	var down = '▼';
	var t = $("h3#chart_no").text();
	var up_down = t.substr(-1,1);
	if($("div#checklist").css("display") == 'none') {
		scrollPos = $(window).scrollTop();
		$("div#group_container").fadeTo("fast", "0", "linear", function() {$(this).hide()});
	}
	else {
		$("div#group_container").show().fadeTo("fast", "1", "linear");
		$(window).scrollTop(scrollPos);
	}
	$("div#checklist").slideToggle(400, "swing", function() {
		if(up_down == '▲') {
			var newText = t.replace(/▲/, "▼");
			//$("h3#chart_no").text(newText);
		}
		else {
			var newText = t.replace(/▼/, "▲");
			//$("h3#chart_no").text(newText);
		}
		$("h3#chart_no").text(newText);
	});
}


function selChanged(selID) {
	var sel = $('select#'+selID);
	var selVal = sel.val();
	if(selVal == "other" || (selID == 'status' && selVal == '12')) {
		var sib = sel.parent("td").next();
		sib.removeClass("hidden");
		sib.find("input").focus();
	}
	else {
		var sib = sel.parent("td").next();
		sib.addClass("hidden");
	}
	if(selID == 'datum') {
		if(selVal != 'WGS84' && selVal != -9999 && $("input#noDTM").attr("checked") === false) {
			$('tr.datum_adjust_tr').show();
		}
		else if(selVal != 'WGS84' && selVal != -9999 && $("input#noDTM").attr("checked") === true) {
			$('tr.datum_adjust_tr').first().show();
		}
		else {
			$('tr.datum_adjust_tr').hide();
		}
//alert("selChanged\nselID == 'datum'\nselVal: "+selVal);
		$("select#datum_correction").trigger('change');
	}
	$('select#'+selID).blur();
}


function cbChanged(cbID) {
//alert("cbChanged ID: "+cbID);
	switch(cbID) {
		case"noPP":
			if($("input#"+cbID).attr("checked") == true) {
				chart['PPdeg'] = $("input#pp_deg").val();
				chart['PPmin'] = $("input#pp_min").val();
				$("input#pp_deg").val('').attr("readonly","readonly").attr("disabled","disabled");
				$("input#pp_min").val('').attr("readonly","readonly").attr("disabled","disabled");
			}
			else {
				$("input#pp_deg").val(chart['PPdeg']);
				$("input#pp_min").val(chart['PPmin']);
				$("input#pp_deg").removeAttr("readonly").removeAttr("disabled");
				$("input#pp_min").removeAttr("readonly").removeAttr("disabled");
			}
			break;
		case"noDTM":
			if($("input#"+cbID).attr("checked") == true) {
				$("input#"+cbID).parent('td').parent('tr').siblings("tr.datum_adjust_tr").hide();
			}
			else {
				$("input#"+cbID).parent('td').parent('tr').siblings("tr.datum_adjust_tr").show();
			}
			break;
	}
}


function resetInitial(butID, butTitle, name) {
//alert("reset_but_id: "+butID+"\nbutTitle: "+butTitle+"\nname: "+name);
	if(name == 'adjust_gd' || name == 'coords_sw' || name == 'coords_ne')
		var answer = confirm("Do you really want to reset the fields '"+butTitle+"' to their initial values?");
	else var answer = confirm("Do you really want to reset the field '"+butTitle+"' to its initial value?");
	if(answer == false) return;
	var chartKey = '';
	var fieldType = '';
	var optOther = false;
	var triggerChange = false;
	switch(name) {
		case "status":
			chartKey = 'status_id';
			fieldType = 'select';
			optOther = true;
			triggerChange = true;
			break;
		case "chart_type":
			chartKey = 'bsb_chf';
			fieldType = 'select';
			optOther = true;
			triggerChange = true;
			break;
		case "scale":
			if(chart['PPdeg_initial'] !== undefined) $("input#pp_deg").attr("value", chart['PPdeg_initial']);
			else $("input#pp_deg").attr("value", "");
			chart['PPdeg'] = $("input#pp_deg").val();
			if(chart['PPmin_initial'] !== undefined) $("input#pp_min").attr("value", chart['PPmin_initial']);
			else $("input#pp_min").attr("value", "");
			chart['PPmin'] = $("input#pp_min").val();
			if(chart['noPP_initial'] !== undefined) $("input#noPP").attr("checked", true).trigger('change');
			else $("input#noPP").attr("checked", false).trigger('change');
			break;
		case "projection":
			chartKey = 'PR';
			fieldType = 'select';
			optOther = true;
			triggerChange = true;
			break;
		case "datum":
			chartKey = 'GD';
			fieldType = 'select';
			optOther = true;
			triggerChange = true;
			break;
		case "adjust_gd":
			if(chart['DTMdat_initial'] !== undefined) $("select#datum_correction").val(chart['DTMdat_initial']);
			else $("select#datum_correction").val("&lt;select&gt;");
			chart['DTMdat'] = $("select#datum_correction").val();
			if(chart['DTMy_abs_initial'] !== undefined) $("input#datum_adj_y").attr("value", chart['DTMy_abs_initial']);
			else $("input#datum_adj_y").attr("value", "0.0");
			chart['DTMy_abs'] = $("input#datum_adj_y").val();
			if(chart['DTMy_dir_initial'] !== undefined) $("select#datum_adj_ns").val(chart['DTMy_dir_initial']);
			else $("select#datum_adj_ns").val("&lt;select&gt;");
			chart['DTMy_dir'] = $("select#datum_adj_ns").val();
			if(chart['DTMx_abs_initial'] !== undefined) $("input#datum_adj_x").attr("value", chart['DTMx_abs_initial']);
			else $("input#datum_adj_x").attr("value", "0.0");
			chart['DTMx_abs'] = $("input#datum_adj_x").val();
			if(chart['DTMx_dir_initial'] !== undefined) $("select#datum_adj_we").val(chart['DTMx_dir_initial']);
			else $("select#datum_adj_we").val("&lt;select&gt;");
			chart['DTMx_dir'] = $("select#datum_adj_we").val();
			if(chart['noDTM_initial'] !== undefined) $("input#noDTM").attr("checked", true).trigger('change');
			else $("input#noDTM").attr("checked", false).trigger('change');
			break;
		case "soundings":
			chartKey = 'UN';
			fieldType = 'select';
			optOther = true;
			triggerChange = true;
			break;
		case "soundings_datum":
			chartKey = 'SD';
			fieldType = 'select';
			optOther = true;
			triggerChange = true;
			break;
		case "coords_sw":
			if(chart['Sdeg_initial'] || chart['Sdeg_initial'] === 0) $("input#lat_deg_sw").attr("value", chart['Sdeg_initial']);
			else $("input#lat_deg_sw").attr("value", "");
			chart['Sdeg'] = $("input#lat_deg_sw").val();
			if(chart['Smin_initial'] || chart['Smin_initial'] === 0) $("input#lat_min_sw").attr("value", chart['Smin_initial']);
			else $("input#lat_min_sw").attr("value", "");
			chart['Smin'] = $("input#lat_min_sw").val();
			if(chart['Ssec_initial'] || chart['Ssec_initial'] === 0) $("input#lat_sec_sw").attr("value", chart['Ssec_initial']);
			else $("input#lat_sec_sw").attr("value", "");
			chart['Ssec'] = $("input#lat_sec_sw").val();
			if(chart['Snhemi_initial'] !== undefined) $("select#lat_ns_sw").val(chart['Snhemi_initial']);
			else $("select#lat_ns_sw").val("&lt;select&gt;");
			chart['Snhemi'] = $("select#lat_ns_sw").val();
			if(chart['Wdeg_initial'] || chart['Wdeg_initial'] === 0) $("input#lng_deg_sw").attr("value", chart['Wdeg_initial']);
			else $("input#lng_deg_sw").attr("value", "");
			chart['Wdeg'] = $("input#lng_deg_sw").val();
			if(chart['Wmin_initial'] || chart['Wmin_initial'] === 0) $("input#lng_min_sw").attr("value", chart['Wmin_initial']);
			else $("input#lng_min_sw").attr("value", "");
			chart['Wmin'] = $("input#lng_min_sw").val();
			if(chart['Wsec_initial'] || chart['Wsec_initial'] === 0) $("input#lng_sec_sw").attr("value", chart['Wsec_initial']);
			else $("input#lng_sec_sw").attr("value", "");
			chart['Wsec'] = $("input#lng_sec_sw").val();
			if(chart['Wehemi_initial'] !== undefined) $("select#lng_we_sw").val(chart['Wehemi_initial']);
			else $("select#lng_we_sw").val("&lt;select&gt;");
			chart['Wehemi'] = $("select#lng_we_sw").val();
			break;
		case "coords_ne":
			if(chart['Ndeg_initial'] || chart['Ndeg_initial'] === 0) $("input#lat_deg_ne").attr("value", chart['Ndeg_initial']);
			else $("input#lat_deg_ne").attr("value", "");
			chart['Ndeg'] = $("input#lat_deg_ne").val();
			if(chart['Nmin_initial'] || chart['Nmin_initial'] === 0) $("input#lat_min_ne").attr("value", chart['Nmin_initial']);
			else $("input#lat_min_ne").attr("value", "");
			chart['Nmin'] = $("input#lat_min_ne").val();
			if(chart['Nsec_initial'] || chart['Nsec_initial'] === 0) $("input#lat_sec_ne").attr("value", chart['Nsec_initial']);
			else $("input#lat_sec_ne").attr("value", "");
			chart['Nsec'] = $("input#lat_sec_ne").val();
			if(chart['Nnhemi_initial'] !== undefined) $("select#lat_ns_ne").val(chart['Nnhemi_initial']);
			else $("select#lat_ns_ne").val("&lt;select&gt;");
			chart['Nnhemi'] = $("select#lat_ns_ne").val();
			if(chart['Edeg_initial'] || chart['Edeg_initial'] === 0) $("input#lng_deg_ne").attr("value", chart['Edeg_initial']);
			else $("input#lng_deg_ne").attr("value", "");
			chart['Edeg'] = $("input#lng_deg_ne").val();
			if(chart['Emin_initial'] || chart['Emin_initial'] === 0) $("input#lng_min_ne").attr("value", chart['Emin_initial']);
			else $("input#lng_min_ne").attr("value", "");
			chart['Emin'] = $("input#lng_min_ne").val();
			if(chart['Esec_initial'] || chart['Esec_initial'] === 0) $("input#lng_sec_ne").attr("value", chart['Esec_initial']);
			else $("input#lng_sec_ne").attr("value", "");
			chart['Esec'] = $("input#lng_sec_ne").val();
			if(chart['Eehemi_initial'] !== undefined) $("select#lng_we_ne").val(chart['Eehemi_initial']);
			else $("select#lng_we_ne").val("&lt;select&gt;");
			chart['Eehemi'] = $("select#lng_we_ne").val();
			break;
		case "corner_sw":
			if(chart['Xsw_initial'] !== undefined) $("input#xcoord_sw").attr("value", chart['Xsw_initial']);
			else $("input#xcoord_sw").attr("value", cont_width/img2_zoomfactor/2);
			chart['Xsw'] = $("input#xcoord_sw").val();
			$("input#xcoordh_sw").attr("value", chart['Xsw']);
			if(chart['Ysw_initial'] !== undefined) $("input#ycoord_sw").attr("value", chart['Ysw_initial']);
			else $("input#ycoord_sw").attr("value", chart['height'] - img_height + cont_height/img2_zoomfactor/2);
			chart['Ysw'] = $("input#ycoord_sw").val();
			$("input#ycoordh_sw").attr("value", chart['Ysw']);
			//setImg2Pos();
			$('input[name="cal_corner"][value="SW"]').attr("checked", true).trigger('change');
			break;
		case "corner_nw":
			if(chart['Xnw_initial'] !== undefined) $("input#xcoord_nw").attr("value", chart['Xnw_initial']);
			else $("input#xcoord_nw").attr("value", cont_width/img2_zoomfactor/2);
			chart['Xnw'] = $("input#xcoord_nw").val();
			$("input#xcoordh_nw").attr("value", chart['Xnw']);
			if(chart['Ynw_initial'] !== undefined) $("input#ycoord_nw").attr("value", chart['Ynw_initial']);
			else $("input#ycoord_nw").attr("value", cont_height/img2_zoomfactor/2);
			chart['Ynw'] = $("input#ycoord_nw").val();
			$("input#ycoordh_nw").attr("value", chart['Ynw']);
			$('input[name="cal_corner"][value="NW"]').attr("checked", true).trigger('change');
			break;
		case "corner_ne":
			if(chart['Xne_initial'] !== undefined) $("input#xcoord_ne").attr("value", chart['Xne_initial']);
			else $("input#xcoord_ne").attr("value", chart['width'] - img_width + cont_width/img2_zoomfactor/2);
			chart['Xne'] = $("input#xcoord_ne").val();
			$("input#xcoordh_ne").attr("value", chart['Xne']);
			if(chart['Yne_initial'] !== undefined) $("input#ycoord_ne").attr("value", chart['Yne_initial']);
			else $("input#ycoord_ne").attr("value", cont_height/img2_zoomfactor/2);
			chart['Yne'] = $("input#ycoord_ne").val();
			$("input#ycoordh_ne").attr("value", chart['Yne']);
			$('input[name="cal_corner"][value="NE"]').attr("checked", true).trigger('change');
			break;
		case "corner_se":
			if(chart['Xse_initial'] !== undefined) $("input#xcoord_se").attr("value", chart['Xse_initial']);
			else $("input#xcoord_se").attr("value", chart['width'] - img_width + cont_width/img2_zoomfactor/2);
			chart['Xse'] = $("input#xcoord_se").val();
			$("input#xcoordh_se").attr("value", chart['Xse']);
			if(chart['Yse_initial'] !== undefined) $("input#ycoord_se").attr("value", chart['Yse_initial']);
			else $("input#ycoord_se").attr("value", chart['height'] - img_height + cont_height/img2_zoomfactor/2);
			chart['Yse'] = $("input#ycoord_se").val();
			$("input#ycoordh_se").attr("value", chart['Yse']);
			$('input[name="cal_corner"][value="SE"]').attr("checked", true).trigger('change');
			break;
		case"comment":
			$("textarea#comment").val(chart['comments_initial']).trigger('change');
			break;
	}

	if(fieldType == 'select') {
		if(chart[chartKey+'_initial'] !== undefined) {
			$("select#"+name).val(chart[chartKey+'_initial']);
			if(optOther == true) {
				if(chart[chartKey+'_initial'] == 'other') $("input#"+name+"_other").val(chart[chartKey+'_other_initial']);
			}
		}
		else {
			$("select#"+name).val("&lt;select&gt;");
		}
		chart[chartKey] = $("select#"+name).val();
		if(triggerChange) $("select#"+name).trigger('change');
	}
	$("#"+butID).attr("disabled", "disabled");
}

function toggleResetButton() {
	var input_id = $(this).attr('id');
	if(input_id.substr(0, 10) == 'cal_corner' || input_id == 'bt') return false;
	var resetButID = $("#"+input_id).parent("td").parent("tr").find("td").find('input[id^="reset_but_"]').attr("id");
	if(typeof(resetButID) == "undefined") {
		var resetButID = $("#"+input_id).parentsUntil("table").children("tr.datum_adjust_tr").find("td").find('input[id^="reset_but_"]').attr("id");
	}
	if(typeof(resetButID) == "undefined") {
		var resetButID = $("#"+input_id).parentsUntil("table").children("tr").find("td").find('input[id^="reset_but_"]').attr("id");
	}
	if(typeof(resetButID) == "undefined") {
		var resetButID = $("#"+input_id).next().find('input[id^="reset_but_"]').attr("id");
	}
	if(typeof(resetButID) == "undefined") {
		alert("ERROR: Couldn't identify the appropriate 'Reset' button.");
		return false;
	}
	$("#"+resetButID).attr("disabled", false);
	$(this).blur();
}

function toggleResetButtonCorner() {
	var input_id = $("input#cal_corner_"+corner).attr('id');
	var resetButID = $("#"+input_id).parent("td").parent("tr").find("td").find('input[id^="reset_but_"]').attr("id");
	if(typeof(resetButID) == "undefined") {
		alert("ERROR: Couldn't identify the appropriate 'Reset' button.");
		return false;
	}
	$("#"+resetButID).attr("disabled", false);
}
// ### END page/ helper functions ###

// ### START calibration/ image functions ###
function updatePosition2() {
    var pos = $('#img2').position();
    img2_top = Math.abs(pos.top);
    img2_left = Math.abs(pos.left);
    move('');
	toggleResetButtonCorner();
}

function move(dir) {
    switch (dir) {
      case "up":
        img2_top--;
        break;
      case "down":
        img2_top++;
        break;
      case "left":
        img2_left--;
        break;
      case "right":
        img2_left++;
        break;
      default:
        break;
    }
    img2_cx = (Math.abs(img2_left) + cont_width / 2) / img2_zoomfactor;
    img2_cy = (Math.abs(img2_top) + cont_height / 2) / img2_zoomfactor;

    img1_top = (Math.round(img2_cy / img1_zoomfactor - cont_height / 2)) * -1;
    img1_left = (Math.round(img2_cx / img1_zoomfactor - cont_width / 2)) * -1;
    
//alert("Top: "+img1_top+"  Left: "+img1_left);
    var diff_x = 0;
    var diff_y = 0;
    
    if(img1_top < (img_height / img1_zoomfactor - cont_height) * -1) {
        diff_y = (Math.abs(img1_top) - (img_height / img1_zoomfactor - cont_height));
        img1_top = (img_height / img1_zoomfactor - cont_height) * -1;
        //alert("top1: "+diff_y);
	}
	else if(img1_top > 0) {
        diff_y = -img1_top;
        img1_top = 0;
        //alert("top2: "+diff_y);
	}    

	if(img1_left < (img_width / img1_zoomfactor - cont_width) * -1) {
        diff_x = (Math.abs(img1_left) - (img_width / img1_zoomfactor - cont_width));
        img1_left = (img_width / img1_zoomfactor - cont_width) * -1;
        //alert("left1: "+diff_x);
    }
	else if(img1_left > 0) {
        diff_x = -img1_left;
        img1_left = 0;
        //alert("left2: "+diff_x);
	}

	$("#img1").css("left", img1_left).css("top", img1_top);

	crop_top = Math.round(cont_height / 2 - crop_height / 2) + diff_y;
	crop_left = Math.round(cont_width / 2 - crop_width / 2) + diff_x;

	$("#crop").css("left", crop_left).css("top", crop_top);
	$("#img2").css("left", -img2_left).css("top", -img2_top);
	updateCoordinates();
}

function changeimg1Zoom() {
    var val_old = "1:"+img1_zoomfactor;
	var val_new = "1:"+factor;
//alert(val_old+" / " +val_new);
	$('input[value="'+val_old+'"]').removeAttr("disabled");
	$('input[value="'+val_new+'"]').attr("disabled", "disabled");
	$("#img1").width(img_width/factor).height(img_height/factor);
	$("#img1").attr("height", (img_height/factor));
	var pos = $("#img1").position();
	img1_top = pos.top;
	img1_left = pos.left;

	var offset = $("#container_1").offset();
	var testPos = $("#container_1").position();
//alert('offset.top: '+offset.top+' offset.left: '+offset.left+'\ntestPos.top: '+testPos.top+' testPos.left: '+testPos.left);
	var diff_width =  Math.round(img_width / factor - cont_width);
	var diff_height =  Math.round(img_height / factor - cont_height);
	var x1 = Math.round(offset.left - diff_width);
	var y1 = Math.round(offset.top - diff_height);
	var x2 = x1 + diff_width;
	var y2 = y1 + diff_height;
//alert('X1: '+x1+' Y1: '+y1+'\nX2: '+x2+' Y2: '+y2);
	
	$("#img1").draggable({ cursor: 'move', containment: [x1, y1, x2, y2] }).bind( "dragstop", cropDragEnd);
	if(img_width/factor == cont_width && img_height/factor == cont_height) $("#img1").draggable( "destroy" );

	img1_top = Math.floor((((Math.abs(img1_top) + cont_height / 2) * img1_zoomfactor / factor)  - cont_height / 2) * -1);
	img1_left = Math.floor((((Math.abs(img1_left) + cont_width / 2) * img1_zoomfactor / factor) - cont_width / 2) * -1);
	if(img1_top < (img_height / factor - cont_height)* -1) img1_top = (img_height / factor - cont_height) * -1;
	if(img1_left < (img_width / factor - cont_width) * -1) img1_left = (img_width / factor - cont_width) * -1;
	if(img1_top > 0) img1_top = 0;
	if(img1_left > 0) img1_left = 0;
//alert("img1_top: "+img1_top+" / img1_left: "+img1_left);
	$("#img1").css("top", img1_top).css("left", img1_left).css("position", "absolute !important");

	$("#img1").css("top", img1_top).css("left", img1_left);
	old_zoom = img1_zoomfactor;
	img1_zoomfactor = factor;
	cropResize(pos.top, pos.left);
	updatePosition2();
}

function cropResize(pos_t, pos_l) {
    var crop_pos = $("#crop").position();
    var wold = crop_width;
    var hold = crop_height;
    var crop_cx_old = (Math.floor(crop_pos.left + wold / 2) + Math.abs(pos_l)) * old_zoom;
    var crop_cy_old = (Math.floor(crop_pos.top + hold / 2) + Math.abs(pos_t)) * old_zoom;

    var pos = $("#img1").position();
    var crop_cx_new = crop_cx_old / factor - Math.abs(pos.left);
    var crop_cy_new = crop_cy_old / factor - Math.abs(pos.top);
    
    crop_width = Math.round(cont_width / factor / img2_zoomfactor);
    crop_height = Math.round(cont_height / factor / img2_zoomfactor);
	
	var crop_bg_pos = Math.floor(crop_width / 2 - 5);
//alert("crop_bg_pos: "+crop_bg_pos);
	$("#crop").css("background-position", crop_bg_pos+"px "+crop_bg_pos+"px");
    
    crop_left = Math.round(crop_cx_new - crop_width / 2);
    crop_top = Math.round(crop_cy_new - crop_height / 2);
  
    if(crop_top < 0) crop_top = 0; 
    if(crop_top > (cont_height - crop_height)) crop_top = cont_height - crop_height;

    if(crop_left < 0) crop_left = 0;
    if(crop_left > (cont_width - crop_width)) crop_left = cont_width - crop_width;

    $("#crop").width(crop_width).height(crop_height).css("top", crop_top).css("left", crop_left);
    //cropDragEnd();
	updateCoordinates();
}

function cropDragEnd() {
    var pos = $('#crop').position();
    crop_top = pos.top;
    crop_left = pos.left;
    
    var pos = $('#img1').position();
    img1_top = pos.top;
    img1_left = pos.left;

    img2_top = Math.round((crop_top + Math.abs(img1_top)) * img1_zoomfactor * img2_zoomfactor);
    img2_left = Math.round((crop_left + Math.abs(img1_left)) * img1_zoomfactor * img2_zoomfactor);
//alert("1.: "+ img2_top+", "+img2_left);
    img2_top = Math.round(img2_top + old_zoom/factor * 0.5);
    img2_left = Math.round(img2_left + old_zoom/factor * 0.5);
//alert("2.: "+ img2_top+", "+img2_left);

    $("#img2").css("left", -img2_left).css("top", -img2_top);
    updateCoordinates();
	toggleResetButtonCorner();
}


function updateCoordinates() {
    var cx = Math.round(img2_left/img2_zoomfactor)+(cont_width/img2_zoomfactor/2);
	var cy = Math.round(img2_top/img2_zoomfactor)+(cont_height/img2_zoomfactor/2);
	switch(corner) {
		case "sw":
			//cx = cx + chart_width - img_width;
			cy = cy + chart_height - img_height;
			break;
		case "se":
			cx = cx + chart_width - img_width;
			cy = cy + chart_height - img_height;
			break;
		case "ne":
			cx = cx + chart_width - img_width;
			//cy = cy + chart_height - img_height;
			break;
		case "nw":
			break;
		default:
			break;
	}
	switch(corner) {
		case"sw":
			var xdef = def_sw_x;
			var ydef = def_sw_y;
			break;
		case"nw":
			var xdef = def_nw_x;
			var ydef = def_nw_y;
			break;
		case"ne":
			var xdef = def_ne_x;
			var ydef = def_ne_y;
			break;
		case"se":
			var xdef = def_se_x;
			var ydef = def_se_y;
			break;
		default:
			break;
	}
	if(cx == xdef && cy == ydef) {
		$("#xcoord_"+corner).attr("value", '');
		$("#ycoord_"+corner).attr("value", '');
		$("#xcoordh_"+corner).attr("value", '');
		$("#ycoordh_"+corner).attr("value", '');
	}
	else {
		$("#xcoord_"+corner).attr("value", cx);
		$("#ycoord_"+corner).attr("value", cy);
		$("#xcoordh_"+corner).attr("value", cx);
		$("#ycoordh_"+corner).attr("value", cy);

	}
	chart['X'+corner] = cx;
	chart['Y'+corner] = cy;
	statusLED('cal_corner_'+corner);
	$('input#xcoord_'+corner).trigger('change');
}

function setImg2Pos() {
	var cx;
	var cy;
	//if(isNaN(chart['X'+corner]) || isNaN(chart['Y'+corner])) {
	if(chart['X'+corner] === null || chart['Y'+corner] === null) {
		cx = 0;
		cy = 0;
	}
	else {
		switch(corner) {
			case "sw":
				cx = (chart['Xsw'] * img2_zoomfactor) * -1 + cont_width/2;
				cy = (chart['Ysw'] - chart_height + img_height) * img2_zoomfactor - cont_height/2;
				break;
			case "se":
				cx = (chart['Xse'] - chart_width + img_width) * img2_zoomfactor - cont_width/2;
				cy = (chart['Yse'] - chart_height + img_height) * img2_zoomfactor - cont_height/2;
				break;
			case "ne":
				cx = (chart['Xne'] - chart_width + img_width) * img2_zoomfactor - cont_width/2;
				cy = (chart['Yne'] * img2_zoomfactor) * -1 + cont_height/2;
				break;
			case "nw":
				cx = (chart['Xnw'] * img2_zoomfactor) * -1 + cont_width/2;
				cy = (chart['Ynw'] * img2_zoomfactor) * -1 + cont_height/2;
				break;
			default:
				break;
		}
	}
//alert("cx: "+cx+"\ncy: "+cy);
	img2_top = Math.abs(cy);
	img2_left = Math.abs(cx);
    move('');
}
// ### END calibration/ image functions ###


// ### START document.unload functions ###
window.onbeforeunload = function (e) {
	if(exitConfirmed != 1) {
		var e = e || window.event;
		if (e) e.returnValue = 'Any string';
		return 'Please do not close or reload this window\nuntil you finished and saved the data!\n\nTo leave the page, please click either the\n\'Exit without Saving\' or the \'Save & Exit\' button.';
	}
};

function deleteSession() {
	$.cookie('chartnumber', '', { expires: -7, path: '/', domain: 'opencpn.info', secure: false });
	if(typeof(ngaViewerWindow) != 'undefined') ngaViewerWindow.close();
};

function exitNoSave() {
	var answer = confirm("Are you sure you want to exit without saving the data?\n\nAll your changes and inputs will be lost.");
	if(answer == false) return;
	exitConfirmed = 1;
	$.cookie('chartnumber', '', { expires: -7, path: '/', domain: 'opencpn.info', secure: false });
	if(typeof(ngaViewerWindow) != 'undefined') ngaViewerWindow.close();
	if(window.opener) {
		window.opener.focus();
		deleteSession();
		window.close();
	}
	else {
		//window.location.replace('http://opencpn.info/en/nga-charts-status');
		deleteSession();
		window.close();
	}
}
// ### END document.unload functions ###


// ### START form/ session functions ###
function keepSessionActive() {
	var session = $('input#sessionID').val();
	$.ajax({
		type: "POST",
		url: "session-keep-alive.php",
		data: {cid: id, sess: session},
		success: function(data) {
			if(data) alert("The following error occurred while trying to renew your active session:\n"+data+"Please try to 'Save & Exit' the page.\nWe apologize for the inconvenience!");
		}
	});
	return false;
}

function formSubmit(dataString) {
	$.ajax({
		type: "POST",
		url: "form-process.php",
		dataType: "text",
		data: dataString,
		error: function(jqXHR, textStatus, errorThrown) {
			alert("Your changes could not be saved to the DB due to the following reason(s):\n"+errorThrown+"\n\nPlease retry to 'Save & Exit' the page.");
		},
		success: function(data, textStatus, jqXHR) {
//alert("Form send!");
			/*$("#response").html(data);
			alert("textStatus: "+textStatus); // success
			alert("jqXHR.responseText: "+jqXHR.responseText);
			alert("Data: "+data);
			*/
			if(ls) localStorage.removeItem("chartdata_"+id);
			alert("Your changes have been successfully saved to the DB!\nThank you very much for your support.\n\nThis window/tab will be closed now.");
			exitConfirmed = 1;
			deleteSession();
			window.close();
		}
	});
	return false;
}
// ### END form/ session functions ###

// checks in browser supports local storage
function supports_html5_storage() {
	try {
		return 'localStorage' in window && window['localStorage'] !== null;
	}
	catch (e) {
		return false;
	}
}

// ### START Validation ###
function validateInputs() {
    // Start validation:
	$.validity.setup({ outputMode:"summary" });
	$.validity.start();
	errorBlocks = new Array();
    // Validator methods go here:
    
	// Chart Status: Check if 'Broken in other way'(12) is selected. If so, check textarea 'status_other' for input.
	if($("select#status").val() == 12) {
		$("textarea#status_other").require("Chart Status is set to 'Broken in other way' - please enter a short description (required) of the way it is broken.").nonHtml().minLength(5, "Chart Status: You need to give more details - at least 5 characters.").maxLength(255, "Chart Status: Please shorten your text to 255 characters.");
	}
	
	// Chart Type: Check if 'other' is selected. If so, check input 'chart_type_other' for input.
	if($("select#chart_type").val() == 'other') {
		$("input#chart_type_other").require("Chart Type is set to 'other' - please enter the type of the chart (required).").nonHtml().minLength(5, "Chart Type: You need to give more details - at least 5 characters.").maxLength(50, "Chart Type: Please shorten your input to 50 characters.");
	}
	
	// Scale at: First check if a Projection is selected. If not, refuse any input. If so, check input depending on the projection type.
	if($("input#noPP").attr("checked") == false) {
		if($("input#pp_deg").val() != '' || $("input#pp_min").val() != '') {
			$("input#pp_deg").require().match("number");
			$("input#pp_min").require().match("number");
		}
		if($("input#pp_deg").val() != '' && $("input#pp_min").val() != '' && $("select#projection").val() == -9999) {
			$('select#projection').assert((false),"Scale at: Please select also the 'Projection' of the chart or leave the 'Scale at' fields empty.");
		}
		if($("input#pp_deg").val() != '' && $("input#pp_min").val() != '' && ($("select#projection").val() == 'MERCATOR' || $("select#projection").val() == 'GNOMONIC')) {
			$("input#pp_deg").require().match("number").range(0,89);
			$("input#pp_min").require().match("number").range(0,59);
		}
		if($("input#pp_deg").val() != '' && $("input#pp_min").val() != '' && ($("select#projection").val() == 'TRANSVERSE MERCATOR')) {
			$("input#pp_deg").require().match("number").range(0,179);
			$("input#pp_min").require().match("number").range(0,59);
		}
		if($("input#pp_deg").val() != '' && $("input#pp_min").val() != '' && ($("select#projection").val() != -9999 && $("select#projection").val() != 'MERCATOR' && $("select#projection").val() != 'TRANSVERSE MERCATOR' && $("select#projection").val() != 'GNOMONIC' && $("select#projection").val() != 'POLYCONIC')) {
			$("input#pp_deg").require().match("number").range(0,179);
			$("input#pp_min").require().match("number").range(0,59);
		}
	}

	// Projection: Check if 'other' is selected. If so, check input 'projection_other' for input.
	if($("select#projection").val() == 'other') {
		$("input#projection_other").require("Projection is set to 'other' - please enter the projection type of the chart (required).").nonHtml().minLength(5, "Projection: You need to give more details - at least 5 characters.").maxLength(50, "Projection: Please shorten your input to 50 characters.");
	}
	
	// Datum: Check if 'other' is selected. If so, check input 'datum_other' for input.
	if($("select#datum").val() == 'other') {
		$("input#datum_other").require("Datum is set to 'other' - please enter the datum of the chart (required).").nonHtml().minLength(5, "Datum: You need to give more details - at least 5 characters.").maxLength(50, "Datum: Please shorten your input to 50 characters.");
	}
	
	// Datum Adjustments: First check if 'Datum' is set to other than '<select>' and 'WGS84'. If so, check for any given value in 'Adjustments to Chart Datum' and check for validity.
	if($("select#datum").val() != -9999 && $("select#datum").val() != 'WGS84') {
		if($("input#noDTM").attr("checked") == false) {
			if($("select#datum_correction").val() != -9999 || $("input#datum_adj_y").val().toString() != '0.0' || $("input#datum_adj_x").val().toString() != '0.0' || $("select#datum_adj_ns").val() != -9999 || $("select#datum_adj_we").val() != -9999) {
				if($("select#datum_correction").val() == -9999) {
					$("select#datum_correction").assert((false), "Corrects Chart Datum to: Please select the correct datum the chart is corrected to.")
				}
				if($("select#datum_correction").val() == 'other') {
					$("input#datum_correction_other").require("'Corrects Chart Datum to' is set to 'other' - please enter the datum the chart is corrected to (required).").nonHtml().minLength(5, "Datum: You need to give more details - at least 5 characters.").maxLength(50, "Datum: Please shorten your input to 50 characters.");
				}
				$("input#datum_adj_y").require().match("number").greaterThanOrEqualTo(0);
				$("input#datum_adj_y").assert(($("input#datum_adj_y").val().toString() != '0.0'), "Adjustment north-/southward: If the value is '0', please enter '0'.");
				if($("select#datum_adj_ns").val() == -9999) {
					$("select#datum_adj_ns").assert((false), "Adjustment north-/southward: Please select the direction (required).");
				}
				$("input#datum_adj_x").require().match("number").greaterThanOrEqualTo(0);
				$("input#datum_adj_x").assert(($("input#datum_adj_x").val().toString() != '0.0'), "Adjustment east-/westward: If the value is '0', please enter '0'.");
				if($("select#datum_adj_we").val() == -9999) {
					$("select#datum_adj_we").assert((false), "Adjustment east-/westward: Please select the direction (required).");
				}
			}
		}
	}
	
	// Soundings Unit: Check if 'other' is selected. If so, check input 'soundings_other' for input.
	if($("select#soundings").val() == 'other') {
		$("input#soundings_other").require("Soundings Unit is set to 'other' - please enter the name of the soundings unit of the chart (required).").nonHtml().minLength(5, "Projection: You need to give more details - at least 5 characters.").maxLength(50, "Projection: Please shorten your input to 50 characters.");
	}
	
	// Soundings Datum: Check if 'other' is selected. If so, check input 'soundings_datum_other' for input.
	if($("select#soundings").val() == 'other') {
		$("input#soundings_datum_other").require("Soundings Datum is set to 'other' - please enter the name of the soundings datum of the chart (required).").nonHtml().minLength(2, "Projection: You need to give more details - at least 2 characters.").maxLength(50, "Projection: Please shorten your input to 50 characters.");
	}
	
	// SW coordinates: If one value is present all others have to be entered/selected, too. Input values have to be numbers within their appropriate range.
    if($("#lat_deg_sw").val() != '' || $("#lat_min_sw").val() != '' || $("#lat_sec_sw").val() != '' || $("#lat_ns_sw").val() != -9999 || $("#lng_deg_sw").val() != '' || $("#lng_min_sw").val() != '' || $("#lng_sec_sw").val() != '' || $("#lng_we_sw").val() != -9999) {
		var selVal = $("#lat_ns_sw").val();
		$("#lat_deg_sw").require().match("number").range(0,89);
		$("#lat_min_sw").require().match("number").range(0,59);
		$("#lat_sec_sw").require().match("number").greaterThanOrEqualTo(0).lessThan(60);
		$("#lat_ns_sw").require().match(/[N|S]/, "Hemisphere for the latitude of the SW corner needs to be selected.");
		$("#lng_deg_sw").require().match("number").range(0,179);
		$("#lng_min_sw").require().match("number").range(0,59);
		$("#lng_sec_sw").require().match("number").greaterThanOrEqualTo(0).lessThan(60);
		$("#lng_we_sw").require().match(/[E|W]/, "Hemisphere for the longitude of the SW corner needs to be selected.");
    }
	
	// NE coordinates: If one value is present all others have to be entered/selected, too. Input values have to be numbers within their appropriate range.
	if($("#lat_deg_ne").val() != '' || $("#lat_min_ne").val() != '' || $("#lat_sec_ne").val() != '' || $("#lat_ns_ne").val() != -9999 || $("#lng_deg_ne").val() != '' || $("#lng_min_ne").val() != '' || $("#lng_sec_ne").val() != '' || $("#lng_we_ne").val() != -9999) {
		var selVal = $("#lat_ns_ne").val();
		$("#lat_deg_ne").require().match("number").range(0,89);
		$("#lat_min_ne").require().match("number").range(0,59);
		$("#lat_sec_ne").require().match("number").greaterThanOrEqualTo(0).lessThan(60);
		$("#lat_ns_ne").require().match(/[N|S]/, "Hemisphere for the latitude of the NE corner needs to be selected.");
		$("#lng_deg_ne").require().match("number").range(0,179);
		$("#lng_min_ne").require().match("number").range(0,59);
		$("#lng_sec_ne").require().match("number").greaterThanOrEqualTo(0).lessThan(60);
		$("#lng_we_ne").require().match(/[E|W]/, "Hemisphere for the longitude of the NE corner needs to be selected.");
    }
	
	// Comment Box: Check for input
	if($("textarea#comment").val() != '') $("textarea#comment").nonHtml();
    
    // All of the validator methods have been called:
    // End the validation session:
    var result = $.validity.end();
//alert("validateInputs done!");    
    // Return whether it's okay to proceed with the Ajax:
    return result.valid;
}
// ### END Validation ###

function gp_focus(number) {
	window.focus();
	if(number != id) alert("You are already editing chart number "+id+"\nPlease finish this one first and\ndo not forget to 'Save & Exit'\nbefore you start editing chart "+number+" - thanks!");
	else if($.browser.mozilla) alert("You are already editing chart number "+id+". ;-)");
}
