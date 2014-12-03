# Open Device Lab Locator App for the Pebble Watch

## Overview

This app is nothing fancy, but gave me a chance to play with pebble.js

When I learned about the pebble.js framework I wanted to check it out and give it a try.
I operate the Fort Wayne Indiana - Open Device Lab and thought a Device Lab locator would be a great project to use as a test.

The Directory of Open Device Labs is maintained by 'The Open Device Lab' http://opendevicelab.com/ and they provide a API to list the registered labs.

The API provided a great starting point, but did not allow me to list Labs based on GEO distance from a LAT/LNG location. My solution was to create a CRON-JOB to clone the lab directory on a nightly basis to a MySQL database.
This allowed me to list the labs based on GEO distance and manipulate the data how ever I needed to.

### Files
* appinfo.json (pebble app config)

* /dist
  * ODL_Locator.pbw (the compliled pebble app)

* README.md (this doccument)

* /resources
  * /images (supporting images for the app)

* /src
  * app.js (the main code for the pebble app)

* /web
  * db.php (MySQL Database Connection Script)
  * index.php (PHP file that handles the data import, lab listing and lab detail screens for the app)
  * odl.sql (MySQL data/structure dump)
  * settings.html (Settings screen for the app, viewed inside the Pebble Mobile App on your Cell Phone - Select Miles or Kilometers as unit of distance)


## Pebble.js

At JsConf 2014 Pebble announced the pebble.js framework for creating apps for the Pebble Watch using JavaScript.
The pebble.js framework is currently still in BETA.

## Links
* [The App on the Pebble Store](https://apps.getpebble.com/applications/547bbb34d9b3a90e1100009c)
* [Fort Wayne open Device lab](http://fwdevicelab.com/)
* [The Open Device Lab](http://opendevicelab.com/)
* [Lab-Up](http://lab-up.org/)
* [JSConf](http://jsconf.com/)
* [CloudPebble IDE](https://cloudpebble.net/)
* [Pebble Developer - pebble.js](http://developer.getpebble.com/guides/js-apps/pebble-js/)
* [GitHub - pebble.js](https://github.com/pebble/pebblejs)
