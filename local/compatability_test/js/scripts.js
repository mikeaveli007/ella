function checkJava(minJava) {

	// Mac no longer supports Java.
	if (navigator.platform.substring(0, 3) == "Mac") {
		return true;
	}

	switch(PluginDetect.isMinVersion("Java", minJava)) {
		case 1:
			//console.log("Java is good enough");
			return true;
		break;
		case 0:
			//console.log("case 0 - plugin installed & enabled but version is unknown unable to determine if version >= minVersion).");
			return false;
		break;
		case -0.1:
			//console.log("Java is not good enough");
			return false;
		break;
		case -0.2:
			//console.log("case -0.2  plugin installed but not enabled. Some browsers occasionally reveal enough info to make this determination.");
			return false;
		break;
		case -0.5:
			//console.log(".");
			return false;
		break;
		case -1:
			//console.log("case -1 plugin is not installed or not enabled.");
			return false;
		break;
		case -1.5:
			//console.log("case -1.5  plugin status is unknown. This only occurs for certain plugins or certain browsers.");
			return false;
		break;
		case -3:
			//console.log("case -3 you supplied a bad input argument to the isMinVersion( ) method.");
			return false;
		break;
	}
}

function checkFlash(minFlash) {
	switch(PluginDetect.isMinVersion("Flash", minFlash)) {
	case 1:
		//console.log("Flash is good enough");
		return true;
	break;
	case 0:
		//console.log("case 0 - plugin installed & enabled but version is unknown (unable to determine if version >= minVersion).");
		return false;
	break;
	case -0.1:
		//console.log("Flash is not good enough");
		return false;
	break;
	case -0.2:
		//console.log("case -0.2  plugin installed but not enabled. Some browsers occasionally reveal enough info to make this determination.");
	break;
	case -0.5:
		//console.log(".");
		return false;
	break;
	case -1:
		//console.log("case -1 plugin is not installed or not enabled.");
		return false;
	break;
	case -1.5:
		//console.log("case -1.5  plugin status is unknown. This only occurs for certain plugins or certain browsers.");
		return false;
	break;
	case -3:
		//console.log("case -3 you supplied a bad input argument to the isMinVersion( ) method.");
		return false;
	break;
	}
}

function checkQuicktime(minQuicktime) {
	switch(PluginDetect.isMinVersion("Quicktime", minQuicktime)) {
	case 1:
		//console.log("Quicktime is good enough");
		return true;
	break;
	case 0:
		//console.log("case 0 - plugin installed & enabled but version is unknown (unable to determine if version >= minVersion).");
		return false;
	break;
	case -0.1:
		//console.log("Quicktime is not good enough");
		return false;
	break;
	case -0.2:
		//console.log("case -0.2  plugin installed but not enabled. Some browsers occasionally reveal enough info to make this determination.");
	break;
	case -0.5:
		//console.log(".");
		return false;
	break;
	case -1:
		//console.log("case -1 plugin is not installed or not enabled.");
		return false;
	break;
	case -1.5:
		//console.log("case -1.5  plugin status is unknown. This only occurs for certain plugins or certain browsers.");
		return false;
	break;
	case -3:
		//console.log("case -3 you supplied a bad input argument to the isMinVersion( ) method.");
		return false;
	break;
	}
}

function checkSilverlight(minSilverlight) {
	switch(PluginDetect.isMinVersion("Silverlight", minSilverlight)) {
	case 1:
		//console.log("Silverlight is good enough");
		return true;
	break;
	case 0:
		//console.log("case 0 - plugin installed & enabled but version is unknown (unable to determine if version >= minVersion).");
		return false;
	break;
	case -0.1:
		//console.log("Silverlight is not good enough");
		return false;
	break;
	case -0.2:
		//console.log("case -0.2  plugin installed but not enabled. Some browsers occasionally reveal enough info to make this determination.");
	break;
	case -0.5:
		//console.log(".");
		return false;
	break;
	case -1:
		//console.log("case -1 plugin is not installed or not enabled.");
		return false;
	break;
	case -1.5:
		//console.log("case -1.5  plugin status is unknown. This only occurs for certain plugins or certain browsers.");
		return false;
	break;
	case -3:
		//console.log("case -3 you supplied a bad input argument to the isMinVersion( ) method.");
		return false;
	break;
	}
}

/*
 * This function outputs the data for the table on the view.php page.
 */
function updateUserView(enabled) {
	if (window.location.href.indexOf("/compatability_test/view.php") > -1) {
		var tablebody = '';

		if (enabled["java"][0]) {
			var myJava = PluginDetect.getVersion("java");

			if (myJava == null) {
				myJava = lang_strings['failure_java_not_installed'];
			}

			// Mac no longer supports Java.
			if (navigator.platform.substring(0, 3) == "Mac") {
				myJava = lang_strings['failure_java_mac'];
			}

			tablebody += buildRow("Java", myJava.replace(/,/g, "."), enabled["java"][1], "http://java.com/download/", lang_strings['visit_website_java']);
		}
		if (enabled["flash"][0]) {
			var myFlash = PluginDetect.getVersion("flash");

			if (myFlash == null) {
				myFlash = lang_strings['failure_flash_not_installed'];
			}

			tablebody += buildRow("Flash", myFlash.replace(/,/g, "."), enabled["flash"][1], "http://get.adobe.com/flashplayer/", lang_strings['visit_website_flash']);
		}
		if (enabled["quicktime"][0]) {
			var myQuicktime = PluginDetect.getVersion("quicktime");

			if (myQuicktime == null) {
				myQuicktime = lang_strings['failure_quicktime_not_installed'];
			}

			tablebody += buildRow("Quicktime", myQuicktime.replace(/,/g, "."), enabled["quicktime"][1], "https://www.apple.com/nz/quicktime/download/", lang_strings['visit_website_quicktime']);
		}
		if (enabled["silverlight"][0]) {
			var mySilverlight = PluginDetect.getVersion("silverlight");

			if (mySilverlight == null) {
				mySilverlight = lang_strings['failure_silverlight_not_installed'];
			}

			tablebody += buildRow("Silverlight", mySilverlight.replace(/,/g, "."), enabled["silverlight"][1], "http://www.microsoft.com/getsilverlight/", lang_strings['visit_website_silverlight']);
		}
		if (enabled["browser"]) {

			if (PluginDetect.browser.isChrome && enabled["chrome"][0]) {
				var myChrome = PluginDetect.browser.verChrome.replace(/,/g, ".");

				tablebody += buildRow("Chrome", myChrome, enabled["chrome"][1], "http://www.google.com/chrome/browser/", lang_strings['visit_website_chrome']);
			}

			if (PluginDetect.browser.isGecko && enabled["gecko"][0]) {
				var myGecko = PluginDetect.browser.verGecko.replace(/,/g, ".");

				tablebody += buildRow("Firefox", myGecko, enabled["gecko"][1], "https://www.mozilla.org/en-US/firefox/new/", lang_strings['visit_website_gecko']);
			}

			if (PluginDetect.browser.isOpera && enabled["opera"][0]) {
				var myOpera = PluginDetect.browser.verOpera.replace(/,/g, ".");

				tablebody += buildRow("Opera", myOpera, enabled["opera"][1], "http://www.opera.com/computer/", lang_strings['visit_website_opera']);
			}

			if (PluginDetect.browser.isSafari && enabled["safari"][0]) {
				var mySafari = PluginDetect.browser.verSafari.replace(/,/g, ".");;

				tablebody += buildRow("Safari", mySafari, enabled["safari"][1], "http://support.apple.com/downloads/#safari", lang_strings['visit_website_safari']);
			}
		}

		var table = document.getElementById("generaltable");
		table.innerHTML = tablebody;
	}
}

/*
 * This function builds the structure of a table row for the view.php page.
 */
function buildRow(name, current, min, site, visit) {
	if (min == false) {
		min = "";
	}

	return '<tr><td>' + name + '</td><td>' + current + '</td><td>' + min + '</td><td><a href="' + site + '" target="_blank">' + visit + '</a></td></tr>';
}

/*
 * This function builds and outputs a banner that notifies the users that their browser is not ready.
 */
function displayBanner(check, bannerfailure, link, bannerlink) {
	if (check == false) {  //&& document.body.id == "page-admin-setting-local_compatability_test") {
		var banner = document.createElement("div");
		banner.className = "alert alert-fail";
		banner.style.textAlign = "center";
		banner.innerHTML = "" + bannerfailure + " <a href=\"" + link + "\">" + bannerlink + "</a>";
		document.getElementById("page").insertBefore(banner, document.getElementById("page").firstChild);
	}else{
		var banner = document.createElement("div");
		banner.className = "alert alert-success";
		banner.style.textAlign = "center";
		banner.innerHTML = lang_strings['banner_success'];
		document.getElementById("page").insertBefore(banner, document.getElementById("page").firstChild);
	}
}

/*
 * This function forces the status page to be displayed upon failing one of the required browser-plugin tests.
 */
function forceStatusPage(url) {
    if (upToDate == false){
			if (!(window.location.href.indexOf("/compatability_test/view.php") > -1)) {
				window.location.replace(url);
			}
		}
}

function checkDisplayBanner(bannerfailure, link, bannerlink) {
    if (upToDate == false) {
	displayBanner(false, bannerfailure, link, bannerlink);
		} else {
			if (window.location.href.indexOf("/compatability_test/view.php") > -1) {
				if (upToDate == true) {displayBanner(true);}
			}
		}
}

/*
 * This function checks that the currently used browser is up to date and if sets a flag if it is not at
 * the required minimum version.
 */
function isMinBrowser(browser, minVersion) {
	var currentVersion;
	minVersion = minVersion.split('.');
    switch (browser){
	    case "chrome":
			currentVersion = PluginDetect.browser.verChrome.split(',');

			for (var i = 0; i < minVersion.length; i++){
				if (minVersion[i] <= currentVersion[i]){
					return true;
				}
			}
		break;
		case "gecko":
			var currentVersion = PluginDetect.browser.verGecko.split(',');

			for (var i = 0; i < minVersion.length; i++){
				if (minVersion[i] <= currentVersion[i]){
					return true;
				}
			}

		break;
		case "opera":
			var currentVersion = PluginDetect.browser.verOpera.split(',');

			for (var i = 0; i < minVersion.length; i++){
				if (minVersion[i] <= currentVersion[i]){
					return true;
				}
			}
		break;
		case "safari":
			var currentVersion = PluginDetect.browser.verSafari.split(',');

			for (var i = 0; i < minVersion.length; i++){
				if (minVersion[i] <= currentVersion[i]){
					return true;
				}
			}
		break;
    }

    return false;
}

/*
 * Determines the current browser to be tested and calls isMinbrowser() with the correct parameters.
 */
function minBrowserCheck(enabled) {

	if (enabled["chrome"][0] && PluginDetect.browser.isChrome) {
		return isMinBrowser("chrome", enabled["chrome"][1]);
	}

	if (enabled["gecko"][0] && PluginDetect.browser.isGecko) {
		return isMinBrowser("gecko", enabled["gecko"][1]);
	}

	if (enabled["opera"][0] && PluginDetect.browser.isOpera) {
		return isMinBrowser("opera", enabled["opera"][1]);
	}

	if (enabled["safari"][0] && PluginDetect.browser.isSafari) {
		return isMinBrowser("safari", enabled["safari"][1]);
	}
}

/*
 * Checks all enabled browser and plugin versions that are required to be checked, based on the administrators compatibility-test settings.
 */
function isUpToDate(enabled) {
	if (enabled["browser"] && !minBrowserCheck(enabled)) {
		upToDate = false;
	}
	else if (enabled["java"][0] && !checkJava(enabled["java"][1])) {
		upToDate = false;
	}
	else if (enabled["flash"][0] && !checkFlash(enabled["flash"][1])) {
		upToDate = false;
	}
	else if (enabled["quicktime"][0] && !checkQuicktime(enabled["quicktime"][1])) {
		upToDate = false;
	}
	else if (enabled["silverlight"][0] && !checkSilverlight(enabled["silverlight"][1])) {
		upToDate = false;
	}
}

var upToDate = true;