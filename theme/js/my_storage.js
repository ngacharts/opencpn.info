/*
// extend browser Storage object with
// setObject() and getObject() methods
// as suggested at
// http://stackoverflow.com/questions/2010892/storing-objects-in-html5-localstorage
*/
Storage.prototype.setObject = function(key, value) {
	this.setItem(key, JSON.stringify(value));
}
Storage.prototype.getObject = function(key) {
	return this.getItem(key) && JSON.parse(this.getItem(key));
}


// display array data as unordered list
function show()
{
	var array_data = get_array_data();
	$("#show").text("");
	for(var item in array_data)
		$("#show").append($("<li />").text(array_data[item]));
}

// get array data from session storage
function get_array_data()
{
	var array_data;
	try
	{
		array_data = sessionStorage.getObject("array_data");
	}
	catch (e) {
		// object may contain string data which can't
		// be parsed into object
	}
	// get empty array if data is not available
	if(!array_data) array_data = [];
	return array_data;
}