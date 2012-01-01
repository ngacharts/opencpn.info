/* <![CDATA[ */
var ss = false;

(function($){$(document).ready(function(){
	$("a.cal_a").click(function(event) {
		event.preventDefault();
	});
	ss = supports_html5_storage();
})})(jQuery);

// checks in browser supports session storage
function supports_html5_storage() {
	try {
		return 'sessionStorage' in window && window['sessionStorage'] !== null;
	}
	catch (e) {
		return false;
	}
}


function editChartData(link) {
	//alert(link);
	var number = link.substr(5);
	// check if Cookie exists - if so => 'calibrationWindow' is opened
	if(jQuery.cookie('chartnumber')) {
		//alert("Cookie exists!");
		if(typeof(calibrationWindow) != 'undefined') {
			calibrationWindow.gp_focus(number);
		}
		else {
			calibrationWindow = window.open("", "calibrationWindow");
			if(calibrationWindow.location.href == 'about:blank') {
				calibrationWindow.close();
				alert("Window or tab already open!\nPlease end (and save) your edit on this chart first.");
			}
			else calibrationWindow.gp_focus(number);
			
		}
	}
	else {
		jQuery.cookie('chartnumber', number, { path: '/', domain: 'opencpn.info', secure: false });
		calibrationWindow = window.open("http://opencpn.info/nga/", "calibrationWindow");
		calibrationWindow.focus();
	}
}

function editChartInsets(link) {
	var number = link.toString();
	var url = "http://opencpn.info/nga/insets.php?chart=";
	insetsWindow = window.open(url.concat(number), "insetsWindow");
	insetsWindow.focus();
}

/* ]]> */
