/******************************************************************************
 *
 * Pebble Watch Open Device Locator App
 * pebble.js API
 * @author Brian Rowe - Fort Wayne Open Device Lab
 *
 * @url http://fwdevicelab.com
 *
 * @email: brian.rowe@fwdevicelab.com
 *
 ******************************************************************************/
/******************************************************************************
 * 
 * Open Device Lab Directory API - http://opendevicelab.com/
 * 
 * For More information about Open Device Labs visit
 * 
 * http://opendevicelab.com/
 * http://lab-up.org/
 * 
 ******************************************************************************/


var UI = require('ui'),
	Vector2 = require('vector2'),
	ajax = require('ajax'),
	Settings = require('settings'),
	crd,
	gUnit = window.localStorage.getItem('gUnit'),
	gUrl = "[ http://yourserver.com/Pebble-ODL/index.php ]";

// Handle App Config/Settings
if(gUnit === ''){
	gUnit = 'miles';
	window.localStorage.setItem('gUnit', gUnit);
}

Pebble.addEventListener('showConfiguration', function() {
	Pebble.openURL(gUrl + 'settings.html?unit=' + gUnit);
});

Pebble.addEventListener('webviewclosed', function(e) {
	var options = JSON.parse(decodeURIComponent(e.response));
	window.localStorage.setItem('gUnit', encodeURIComponent(options.gUnit));
	gUnit = encodeURIComponent(options.gUnit);
});
///////////////

// GEO Location
function GetGeoLocation(){
	navigator.geolocation.getCurrentPosition(GeoSuccess, GeoError);

	function GeoSuccess(pos) {
		crd = pos.coords;
	}

	function GeoError() {
		console.log('Geo Error');
	}
	return crd;
}
GetGeoLocation();
///////////////

// Instructions/Splash Screen
var instructions = new UI.Window({});

var TitleField = new UI.Text({
	position: new Vector2(0, 0),
	size: new Vector2(144, 60),
	font: 'gothic-28-bold',
	text: 'Open Device Lab Locator',
	textAlign: 'center',
	color: 'white'
});
instructions.add(TitleField);

var BodyField = new UI.Text({
	position: new Vector2(0, 60),
	size: new Vector2(144, 100),
	font: 'gothic-18',
	text: 'Press the "select" button to locate Device Labs within a selected radius of your current location.',
	textAlign: 'center',
	color: 'white'
});
instructions.add(BodyField);

var splashScreen = new UI.Window({ fullscreen: true });

var image = new UI.Image({
	position: new Vector2(0, 0),
	size: new Vector2(144, 168),
	image: 'images/SplashScreen.png'
});
image.compositing("invert");
splashScreen.add(image);

splashScreen.show();

setTimeout(function() {
	var HasViewedInstructions = window.localStorage.getItem('ViewedInstructions');
	if(HasViewedInstructions  !== 'true'){
		HasViewedInstructions  = 'true';
		window.localStorage.setItem('ViewedInstructions', HasViewedInstructions );
		instructions.show();
	} else {
		RadiusMenu();
	}
	splashScreen.hide();
}, 1500);

instructions.on('click', 'select', function(e) {
	RadiusMenu();
});
///////////////

// Select Distance Menu
function RadiusMenu() {

	var menu = new UI.Menu({	
		sections: [{
			title: 'Select Search Radius',
			items: [
			{'title': '25 ' + gUnit, 'value': '25'},
			{'title': '50 ' + gUnit, 'value': '50'},
			{'title': '100 ' + gUnit, 'value': '100'},
			{'title': '200 ' + gUnit, 'value': '200'},
			{'title': '300 ' + gUnit, 'value': '300'},
			{'title': '400 ' + gUnit, 'value': '400'},
			{'title': '500 ' + gUnit, 'value': '500'}
			]
		}]
	});

	menu.on('select', function(e) {
		ajax({
			url: gUrl + '?action=list&lat=' + crd.latitude + '&lng=' + crd.longitude + '&dist=' + e.item.value + '&unit=' + gUnit + '',
			type: 'json'
			},
			function(data) {
				ListLabs(data);
			},
			function(error) {
				console.log('The ajax request failed: ' + error);
		});
	});

	menu.show();

}
///////////////


// List Device Labs in radius
function ListLabs(data) {

		var LabList = [];
		for (var i = 0, len = data.labs.length; i < len; i++) {
			var lab = {
				id: data.labs[i].id,
				title: data.labs[i].name,
				subtitle: "Dist: " + data.labs[i].distance
			};
			LabList.push(lab);
		}

		var menuList = new UI.Menu({
			sections: [{
				title: data.count + ' Lab(s) Found',
				items: LabList
			}]
		});

		menuList.on('select', function(e) {
			var gUnit = Settings.option('gUnit');
			ajax({
				url: gUrl + '?action=detail&lat=' + crd.latitude + '&lng=' + crd.longitude + '&id=' + e.item.id + '&unit=' + gUnit + '',
				type: 'json'
			},
			function(data) {
				LabDetail(data);
			},
			function(error) {
				console.log('The ajax request failed: ' + error);
			});
        });

		menuList.show();
    }
///////////////


// Detail View for selected lab
function LabDetail(data) {
	
	var DeviceBrands = "";
	for (var i = 0, len = data.lab[0].brands_available.length; i < len; i++) {
		DeviceBrands += data.lab[0].brands_available[i].brand + "\n";
	}

	var DetailView = new UI.Card({
		title: data.lab[0].name,
		subtitle: ' ',
		scrollable: true,
		style: 'mono',
		body: 'Type: ' + data.lab[0].type + '\n\nStatus:\n' + data.lab[0].status + '\n\nDescription:\n'+ data.lab[0].description + '\n\nOrganization:\n' + data.lab[0].organization + '\n\nAddress:\n' + data.lab[0].street_address + '\n' + data.lab[0].city + ' ' + data.lab[0].state + ', ' + data.lab[0].zip + '\n' + data.lab[0].country + '\n\n' + 'Website:\n' + data.lab[0].url + '\n\n' + 'Devices: ' + data.lab[0].number_of_devices + '\n' + 'Brands:\n' + DeviceBrands + '\n'
	});

	DetailView.show();
}
///////////////