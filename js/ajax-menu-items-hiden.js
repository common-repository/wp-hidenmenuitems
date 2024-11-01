
/**
 * Description: Interface control using Ajax technology.
 *
 * @category ajax-menu-items-hiden.js
 * @package  WP-HidenMenuItems
 * @author   Oleg Klenitsky <klenitskiy.oleg@mail.ru>
 * @version  1.0.0
 * @license  GPLv2 or later
 */

/**
 * Shared array Menus.
 *
 * @type array
 */
var all_menus = {};

"use strict";

if (window.attachEvent) {
	window.attachEvent("onload", hmi_onload);
} else if (window.addEventListener) {
	window.addEventListener("load", hmi_onload, false);
} else {
	document.addEventListener("load", hmi_onload, false);
}

/**
 * Main function onload.
 */
function hmi_onload() {
	hmi_menu_select();
}
/**
 * Creates main array on frontend.
 */
function hmi_creates_main_array( all_data_menu ) {
	let all_data_menu_parse = JSON.parse( all_data_menu );
	let arr_pages_id  = all_data_menu_parse[1] ? all_data_menu_parse[1]["pages_id"] : [];
	let arr_pages_pwd = all_data_menu_parse[1] ? all_data_menu_parse[1]["pages_pwd"] : [];
	let arr_users_id  = all_data_menu_parse[1] ? all_data_menu_parse[1]["users_id"] : [];

	let selected_menu = document.getElementById("all-menus").value;

	if ( !all_menus["menu_id_" + selected_menu] ) {
		all_menus["menu_id_" + selected_menu] = {};
		all_menus["menu_id_" + selected_menu]["pages_id"]  = arr_pages_id;
		all_menus["menu_id_" + selected_menu]["pages_pwd"] = arr_pages_pwd;
		all_menus["menu_id_" + selected_menu]["users_id"]  = arr_users_id;
	}
}
/**
 * Rebuilding the table of registered users.
 *
 * @param integer $item_menu_id  Id of item menu.
 */
function hmi_item_menu_select( item_menu_id, menu_item_title ) {
	let selected_menu = document.getElementById("all-menus").value;
	let item_pwd      = document.getElementById("pwd_" + item_menu_id);
	let users_site    = document.getElementsByName("hmi_users_site");
	let arr_menu      = all_menus["menu_id_" + selected_menu];
	let arr_item_menu = arr_menu["pages_id"];
	let index         = -1;

	for (let key of arr_item_menu.keys()) {
		if ( arr_item_menu[key] == item_menu_id ) {
			index = key;
			break;
		}
	}

	let arr_users_site = arr_menu["users_id"][index];

	if ( "" !== item_pwd.value ) {
		for( let user_site of users_site ) {
			user_site.disabled = false;
			user_site.checked  = false;
			for (let value of arr_users_site.values()) {
  			if ( value == user_site.id ) {
  				user_site.checked  = true;
  				break;
  			}
			}
		}
	} else {
		for( let user_site of users_site ) {
			user_site.disabled = true;
			user_site.checked  = false;
		}
	}
	item_pwd.focus();

	let users_site_checked = document.querySelectorAll("input[name=hmi_users_site]:checked").length;
	let report2            = document.getElementById("report2");
	report2.innerHTML      = `Selected menu item: ${menu_item_title}, Selected users: (${users_site_checked})`;
}
/**
 * Select users who are allowed access to hidden items menu.
 *
 * @param integer Id of user.
 */
function hmi_items_users_select( user_id ) {
	let selected_menu      = document.getElementById("all-menus").value;
	let items_menu         = document.getElementsByName("hmi_items_menu");
	let item_menu_chk_id   = document.querySelector("input[name=hmi_items_menu]:checked").id;
	let users_site_checked = document.querySelectorAll("input[name=hmi_users_site]:checked");
	let index              = -1;

	let arr_item_menu = all_menus["menu_id_" + selected_menu]["pages_id"];

	for (let key of arr_item_menu.keys()) {
		if ( arr_item_menu[key] == item_menu_chk_id ) {
			index = key;
			break;
		}
	}

	// for item users_site.
	let arr = [], i = 0;
	for( let key of users_site_checked ) {
		arr[i++] = key.id;
	}
	all_menus["menu_id_" + selected_menu]["users_id"][index] = arr;

	let report2 = document.getElementById("report2");
	report2.innerHTML = report2.innerHTML.split(",")[0] + `, Selected users: (${users_site_checked.length})`;
}
/**
 * [hmi_item_pwd_click description]
 * @param  {[type]} item_menu_id [description]
 */
function hmi_item_pwd_click( item_menu_id, menu_item_title ) {
	let item_menu     = document.getElementById(item_menu_id);
	item_menu.checked = true;

	hmi_item_menu_select( item_menu_id, menu_item_title );
}
/**
 * Save changes of hmi_items_pwd to main array.
 *
 * @param string $item_pwd_id  Id of item menu.
 */
function hmi_items_pwd_change( item_pwd_id, menu_item_title ) {
	let selected_menu = document.getElementById("all-menus").value;
	let item_pwd      = document.getElementById(item_pwd_id);
	let item_menu_id  = item_pwd.id.split("_")[1];
	let index         = -1;

	let arr_item_menu = all_menus["menu_id_" + selected_menu]["pages_id"];

	for (let key of arr_item_menu.keys()) {
		if ( arr_item_menu[key] == item_menu_id ) {
			index = key;
			break;
		}
	}

	arr_pwd = all_menus["menu_id_" + selected_menu]["pages_pwd"];
	arr_pwd[index] = item_pwd.value;

	hmi_item_menu_select( item_menu_id, menu_item_title );
}
/**
 * Build items of selected menu.
 *
 * @param array $arr_items_menu  An array of arguments used to retrieve menu item objects (2-item on page setup).
 */
function hmi_items_menu( all_data_menu ) {
	var arrs            = JSON.parse(all_data_menu);
	var all_menu_select = document.getElementById("all-menus");
	var all_menu_txt    = all_menu_select.options[all_menu_select.selectedIndex].text;
	var menu_items_body = document.getElementById("body_menu_items");
	var report1         = document.getElementById("report1");

	report1.innerHTML = `Selected menu: ${all_menu_txt} Menu items: (${arrs[0].length})`;

	while ( menu_items_body.rows[0] ) {
		menu_items_body.deleteRow(0);
	}

	for (var item in arrs[0]) {
		var tr_item_menu  = document.createElement("tr");
		var td1           = document.createElement("td");
			td1.style="width: 15%;";
		var td2           = document.createElement("td");
			td2.style="width: 50%;";
		var td3           = document.createElement("td");

		var chkbox        = document.createElement("input");
			chkbox.type     = "radio";
			chkbox.id       = arrs[0][item].object_id;
			chkbox.name     = "hmi_items_menu";
			chkbox.title    = arrs[0][item].title;
			chkbox.checked  = ( "0" !== item ) ? false : true;
			chkbox.setAttribute( "onclick", "hmi_item_menu_select( id, title )" );

			td1.appendChild(chkbox);
			tr_item_menu.appendChild(td1);

		var alink         = document.createElement("a");
			alink.href      = arrs[0][item].url;
			alink.target    = "_blank";
			alink.innerHTML = arrs[0][item].title;

			// Если дочерний item menu - сдвигаем в право
			if ( '0' !== arrs[0][item].menu_item_parent ) {
				alink.style = "margin-left: 15px;";
			}
			td2.appendChild(alink);
			tr_item_menu.appendChild(td2);

		var inText         = document.createElement("input");
			inText.type      = "text";
			inText.className = "hmi_items_pwd";
			inText.name      = "hmi_items_pwd";
			inText.id        = "pwd_" + arrs[0][item].object_id;
			inText.value     = arrs[0][item].post_password;
			inText.size      = "12";
			let chkbox_title = chkbox.title;
			let chkbox_id = chkbox.id;
			inText.setAttribute( "onchange", `hmi_items_pwd_change( id, "${chkbox_title}" )` );
			inText.setAttribute( "onclick", `hmi_item_pwd_click( ${chkbox_id}, "${chkbox_title}" )` );

			td3.appendChild(inText);
			tr_item_menu.appendChild(td3);

			menu_items_body.appendChild(tr_item_menu);
	}
}
/**
 * Build items users.
 *
 * @param array $arr_items_menu  An array of arguments used to retrieve menu item objects (3-item on page setup).
 */
function hmi_items_users( all_data_menu ) {
	var arrs           = JSON.parse(all_data_menu);
	var selected_menu  = document.getElementById("all-menus").value;
	var arr_users_site = ( arrs[2] ) ? arrs[2][selected_menu] : null;
	var users_site     = document.getElementsByName("hmi_users_site");

	if ( arr_users_site ) {
		for (var i = 0; i < users_site.length; ++i) {
		  users_site[i].checked = arr_users_site["user_" + users_site[i].id];
		}
	} else {
		for (var i = 0; i < users_site.length; ++i) {
		  users_site[i].checked = false;
		}
	}
}
/**
 * Receive itemsObjectID of selected menu from server.
 */
function hmi_menu_select() {
	let selected_menu = document.getElementById("all-menus").value;
	let btns_radio = document.getElementsByName("hmi_items_menu");
	let params = "selected_menu=" + JSON.stringify( selected_menu ) + "&action=hmi" + "&nonce=" + hmi_nonce;

	hmi_makeRequest(params)
	.then(all_data_menu => (
		hmi_creates_main_array( all_data_menu ),
		hmi_items_menu( all_data_menu ),
		hmi_items_users( all_data_menu ),
		btns_radio[0].click())
	)
	.catch(err => hmi_msg( err.statusText ));
}
/**
 * Save selectMenuTermID, itemsObjectID on server.
 */
function hmi_save_settings() {
	let params = "all_menus=" + JSON.stringify(all_menus) + "&action=hmi" + "&nonce=" + hmi_nonce;

	hmi_makeRequest(params)
	.then(msg => hmi_msg( msg ))
	.catch(err => hmi_msg( err.statusText ));
}
/**
 * Message from server.
 *
 * @param string msg Message from server.
 */
function hmi_msg( msg  ) {
	var hmiHead = document.getElementById("hmi-head");
	var hmiBtn  = document.createElement('button');
	var divMsg  = document.createElement('div');

	hmiBtn.className = "notice-dismiss";
	hmiBtn.onclick = function() {
		divMsg.remove();
	}
	if ( 'ok' == msg ) {
		divMsg.id        = "message";
		divMsg.innerHTML = "<p><strong>Message: Settings data saved successful. " + Date() + "</strong></p>";
		divMsg.className = "notice notice-success is-dismissible";
	} else {
		divMsg.id        = "error";
		divMsg.innerHTML = "<p><strong>Error: " + msg + "</strong></p>";
		divMsg.className = "notice notice-error is-dismissible";
	}
	hmiHead.appendChild(divMsg);
	divMsg.appendChild(hmiBtn);
}
/**
 * Make XMLHttpRequest.
 *
 * @param string params Parameter for request.
 */
function hmi_makeRequest (params) {
	return new Promise( function(resolve, reject) {
	    var xhr = new XMLHttpRequest();
	    xhr.open("POST", hmi_ajax_url, true);
	    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	    xhr.onload = function() {
				if (this.readyState == 4 && this.status == 200) {
					resolve(this.response);
				}else{
					reject({
						status: this.status,
						statusText: this.statusText
					});
				}
	    };
	    xhr.onerror = function() {
				reject({
					status: this.status,
					statusText: this.statusText
				});
	    };
	    xhr.send(params);
	});
}
