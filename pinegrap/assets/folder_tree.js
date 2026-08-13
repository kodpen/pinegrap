
function init_folder_tree() {
    update_folder_tree(0);
}

function update_folder_tree(folder_id, expand_all) {
    var expanded_folders_cookie = get_cookie_value('software[view_folders][expanded_folders]');
    expanded_folders = new Array();

    if (expanded_folders_cookie) {
        expanded_folders = expanded_folders_cookie.split(',');
    }

    // if folder is collapsed, expand folder
    if ((document.getElementById('ul_' + folder_id).style.display == 'none') || (expand_all == true)) {
        expanded_folders[expanded_folders.length] = folder_id;

        document.getElementById('ul_' + folder_id).innerHTML = '<li class="loading"><img src="assets/images/loading.gif" width="16" height="16" border="0" alt="" />&nbsp;&nbsp;'+Loading+'...</li>';
        document.getElementById('ul_' + folder_id).style.display = 'block';

        var requester = createXMLHttpRequest();

        requester.onreadystatechange =
            function () {
                // if XMLHttpRequest communication is complete
                if (requester.readyState == 4) {
                    var temp = requester.responseXML.getElementsByTagName("root");
                    var root = temp[0];

                    document.getElementById('ul_' + folder_id).innerHTML = get_folder_content(root, expand_all);

                    if (document.getElementById('image_' + folder_id)) {
                        document.getElementById('image_' + folder_id).classList.remove('bi-folder2');
                        document.getElementById('image_' + folder_id).classList.add('bi-folder2-open');
                    }

                    save_expanded_folders_cookie();
                }
            };

        if (expand_all == true) {
            expand_all_value = 'true';
        } else {
            expand_all_value = 'false';
        }

        requester.open("GET", "get_folder_tree.php?folder_id=" + folder_id + "&expand_all=" + expand_all_value);
        requester.send(null);

        // else folder is not expanded, so collapse folder
    } else {
        // remove items in this folder
        document.getElementById('ul_' + folder_id).innerHTML = '';

        // collapse folder
        document.getElementById('ul_' + folder_id).style.display = 'none';

        // change status image to plus icon
        if (document.getElementById('image_' + folder_id)) {
            document.getElementById('image_' + folder_id).classList.remove('bi-folder2-open');
            document.getElementById('image_' + folder_id).classList.add('bi-folder2');
        }

        // loop through all expanded folders, so we can remove collapsed folder
        for (var i = 0; i < expanded_folders.length; i++) {
            // if this folder is the collapsed folder, remove folder from array
            if (expanded_folders[i] == folder_id) {
                expanded_folders.splice(i, 1);
            }
        }

        save_expanded_folders_cookie();
    }

    function get_folder_content(parent, expand_all) {
        var content = '';

        for (var i = 0; i < parent.childNodes.length; i++) {
            switch (parent.childNodes[i].tagName) {
                case 'folder':
                    var archived = '';

                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'style') {
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                style = " || "+Page_Style+": " + parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'access_control_type') {
                            access_control_type = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'archived') {
                            archived = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                    }

                    // Get user friendly access control type names
                    access_control_type_name = get_access_control_name(access_control_type)

                    // if folder should be expanded
                    if ((expand_all == true) || (in_array(id, expanded_folders) == true) || (id == 1)) {
                        expanded_collapsed_icon = '<span id="image_' + id + '" class="icon_folder bi me-2 bi-folder2-open"></span>';
                        display = 'block';
                        expanded_folders[expanded_folders.length] = id;
                    } else {
                        expanded_collapsed_icon = '<span id="image_' + id + '" class="icon_folder bi bi-folder2 me-2"></span>';
                        display = 'none';
                    }

                    var last_class;

                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' last';
                    } else {
                        last_class = '';
                    }

                    var archived_class = '';

                    // if archived is true, then output the archived styling
                    if (archived == 'true') {
                        archived_class = ' archived';
                    }

                    content += '<li class="' + access_control_type + last_class + ' heading"><span class="row"><span onclick="update_folder_tree(' + id + ')" onmouseover="this.className=\'icon\'" onmouseout="this.className=\'\'">' + expanded_collapsed_icon + '</span><span onmouseover="this.className=\'active\'" onmouseout="this.className=\'\'"><span id="folder_' + id + '" class="object' + archived_class + '" onmouseover="document.getElementById(\'folder_properties_' + id + '\').style.visibility = \'visible\';" onmouseout="document.getElementById(\'folder_properties_' + id + '\').style.visibility = \'hidden\';"><span onclick="window.location.href=\'edit_folder.php?id=' + id + '\'"><span class="folder">' + prepare_content_for_html(name) + '</span><span id="folder_properties_' + id + '" style="visibility: hidden"> || ' + Access_Control + ': ' + access_control_type_name + style + '</span></span></span></span></span><ul id="ul_' + id + '" style="display: ' + display + '">';
                    content += get_folder_content(parent.childNodes[i], expand_all);
                    content += '</ul></li>';
                    break;

                case 'page':
                    var archived = '';

                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'style') {
                            if (parent.childNodes[i].childNodes[j].firstChild && (parent.childNodes[i].childNodes[j].firstChild.nodeValue != '&nbsp;')) {
                                style = " || "+Page_Style_Override+": " + parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            } else {
                                style = '';
                            }
                        } 

                        if (parent.childNodes[i].childNodes[j].tagName == 'home') {
                            home = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'type') {
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                type = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            } else {
                                type = '';
                            }
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'archived') {
                            archived = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                    }

                    if (home == 'true') {
                        page_icon = '<span class="bi bi-house text-success me-2"></span>';
                    } else {
                        page_icon = '<span class="bi bi-window me-2"></span>';
                    }

                    var last_class;

                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' class="last"';
                    } else {
                        last_class = '';
                    }

                    var query_string_from = '';

                    // if page type is a certain page type, then prepare from
                    switch (type) {
                        case 'view order':
                        case 'custom form':
                        case 'custom form confirmation':
                        case 'calendar event view':
                        case 'catalog detail':
                        case 'shipping address and arrival':
                        case 'shipping method':
                        case 'logout':
                            query_string_from = '?from=control_panel';
                            break;
                    }

                    var archived_class = '';

                    // if archived is true, then output the italic styling
                    if (archived == 'true') {
                        archived_class = ' archived';
                    }

                    content += '<li' + last_class + '><span class="row"><span onclick="window.location.href=\'' + prepare_content_for_html(path) + name + query_string_from + '\'" onmouseover="this.className=\'active\'" onmouseout="this.className=\'\'"><span id="page_' + id + '" class="object' + archived_class + '" onmouseover="document.getElementById(\'page_properties_' + id + '\').style.visibility = \'visible\';" onmouseout="document.getElementById(\'page_properties_' + id + '\').style.visibility = \'hidden\';">' + page_icon + '<span class="file">' + prepare_content_for_html(name) + '</span><span id="page_properties_' + id + '" style="visibility: hidden"> ' + style + '</span></span></span></span></li>';
                    break;

                case 'file':
                    var design = '';
                    var access = '';
                    var archived = '';
                    var name = '';
                    var type = '';
                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'design') {
                            design = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'access') {
                            access = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'type') {
                            type = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        if (parent.childNodes[i].childNodes[j].tagName == 'archived') {
                            archived = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                        if (parent.childNodes[i].childNodes[j].tagName == 'type') {
                            type = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }
                       
                    }

                    
                    var link = '';

                    // if the user has access to this file (i.e. not a design file or user is a designer or administrator),
                    // then output link
                    if (access == 'true') {
                        link = ' onclick="window.location.href=\'edit_file.php?id=' + id + '\'" onmouseover="this.className=\'active\'" onmouseout="this.className=\'\'"';
                    }

                    var last_class;

                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' class="last"';
                    } else {
                        last_class = '';
                    }

                    var design_class = '';

                    // if file is a design file then output design class for lighter color
                    if (design == 'true') {
                        design_class = ' design';
                    }

                    var archived_class = '';

                    // if archived is true, then output the italic styling
                    if (archived == 'true') {
                        archived_class = ' archived';
                    }

                    let type_class = filetypeMap[type] || 'bi-images';

                    content += '<li' + last_class + '><span class="row"><span' + link + '><span id="file_' + id + '" class="object' + design_class + archived_class + '"><span class="bi ' + type_class + ' me-2"></span><span class="no_style">' + prepare_content_for_html(name) + '</span></span></span></span></li>';
                    break;
            }
        }

        return content;
    }
}

// Initialize product group tree (call on page load)
function init_product_group_tree() {
  update_product_group_tree(0);
}

function update_product_group_tree(product_group_id, expand_all) {

    var expanded_product_groups_cookie = get_cookie_value('software[product_group_tree][expanded_product_groups]');
    expanded_product_groups = new Array();

    if (expanded_product_groups_cookie) {
        expanded_product_groups = expanded_product_groups_cookie.split(',');
    }

    // if product_group is collapsed, expand product_group
    if ((document.getElementById('ul_' + product_group_id).style.display == 'none') || (expand_all == true)) {
        expanded_product_groups[expanded_product_groups.length] = product_group_id;

        document.getElementById('ul_' + product_group_id).innerHTML = '<li class="loading"><img src="assets/images/loading.gif" width="16" height="16" border="0" alt="" />&nbsp;&nbsp;'+Loading+'...</li>';
        document.getElementById('ul_' + product_group_id).style.display = 'block';

        var requester = createXMLHttpRequest();

        requester.onreadystatechange = function () {
            // if XMLHttpRequest communication is complete
            if (requester.readyState == 4) {
                var temp = requester.responseXML.getElementsByTagName("root");
                var root = temp[0];

                document.getElementById('ul_' + product_group_id).innerHTML = get_product_group_content(root, expand_all);

                if (document.getElementById('image_' + product_group_id)) {
                    document.getElementById('image_' + product_group_id).classList.remove('bi-box-fill');
                    document.getElementById('image_' + product_group_id).classList.add('bi-boxes');
                }

                $('#ul_' + product_group_id + ' .status_button').each(function () {

                    var status_button = $(this);

                    status_button.click(function () {

                        // Prepare the new status that should be set for this item.
                        if (status_button.hasClass('enable')) {
                            var status = 'enabled';
                        } else {
                            var status = 'disabled';
                        }

                        // If this item is a product group, then update status
                        // in a certain way.
                        if (status_button.hasClass('product_group_status_button')) {

                            $.ajax({
                                contentType: 'application/json',
                                url: 'api.php',
                                data: JSON.stringify({
                                    action: 'update_product_group_status',
                                    token: software_token,
                                    id: status_button.data('id'),
                                    status: status
                                }),
                                type: 'POST',
                                success: function (response) {

                                    $.each(response.items, function (index, item) {

                                        if (item.type == 'product_group') {

                                            if (item.status == 'enabled') {
                                                $('.product_group_' + item.id).removeClass('status_disabled');
                                                $('.product_group_' + item.id).addClass('status_enabled');

                                                $('.product_group_' + item.id + '_status_button').removeClass('enable btn-sm btn d-inline py-0 btn-success');
                                                $('.product_group_' + item.id + '_status_button').addClass('disable btn-sm btn d-inline py-0 btn-danger');
                                                $('.product_group_' + item.id + '_status_button').html(Disable);

                                            } else {
                                                $('.product_group_' + item.id).removeClass('status_enabled');
                                                $('.product_group_' + item.id).addClass('status_disabled');

                                                $('.product_group_' + item.id + '_status_button').removeClass('disable btn-sm btn d-inline py-0 btn-danger');
                                                $('.product_group_' + item.id + '_status_button').addClass('enable btn-sm btn d-inline py-0 btn-success');
                                                $('.product_group_' + item.id + '_status_button').html(Enable);
                                            }

                                            // Otherwise the item is a product.
                                        } else {

                                            if (item.status == 'enabled') {
                                                $('.product_' + item.id).removeClass('status_disabled');
                                                $('.product_' + item.id).addClass('status_enabled');

                                                $('.product_' + item.id + '_status_button').removeClass('enable btn-sm btn d-inline py-0 btn-success');
                                                $('.product_' + item.id + '_status_button').addClass('disable btn-sm btn d-inline py-0 btn-danger');
                                                $('.product_' + item.id + '_status_button').html(Disable);

                                            } else {
                                                $('.product_' + item.id).removeClass('status_enabled');
                                                $('.product_' + item.id).addClass('status_disabled');

                                                $('.product_' + item.id + '_status_button').removeClass('disable btn-sm btn d-inline py-0 btn-danger');
                                                $('.product_' + item.id + '_status_button').addClass('enable btn-sm btn d-inline py-0 btn-success');
                                                $('.product_' + item.id + '_status_button').html(Enable);
                                            }

                                        }

                                    });

                                }
                            });

                            // Otherwise this item is a product, so update status,
                            // in a different way.
                        } else {

                            var id = status_button.data('id');

                            $.ajax({
                                contentType: 'application/json',
                                url: 'api.php',
                                data: JSON.stringify({
                                    action: 'update_product_status',
                                    token: software_token,
                                    id: id,
                                    status: status
                                }),
                                type: 'POST',
                                success: function (response) {

                                    if (status == 'enabled') {
                                        $('.product_' + id).removeClass('status_disabled');
                                        $('.product_' + id).addClass('status_enabled');

                                        $('.product_' + id + '_status_button').removeClass('enable');
                                        $('.product_' + id + '_status_button').addClass('disable');
                                        $('.product_' + id + '_status_button').html(Disable);

                                    } else {
                                        $('.product_' + id).removeClass('status_enabled');
                                        $('.product_' + id).addClass('status_disabled');

                                        $('.product_' + id + '_status_button').removeClass('disable');
                                        $('.product_' + id + '_status_button').addClass('enable');
                                        $('.product_' + id + '_status_button').html(Enable);
                                    }

                                }
                            });

                        }

                    });

                });

                save_expanded_product_groups_cookie();
            }
        };

        if (expand_all == true) {
            expand_all_value = 'true';
        } else {
            expand_all_value = 'false';
        }

        requester.open("GET", "get_product_group_tree.php?product_group_id=" + product_group_id + "&expand_all=" + expand_all_value);
        requester.send(null);

        // else product_group is expanded, so collapse product_group
    } else {
        // remove items in this product_group
        document.getElementById('ul_' + product_group_id).innerHTML = '';

        // collapse product_group
        document.getElementById('ul_' + product_group_id).style.display = 'none';

        // change status image to plus icon
        if (document.getElementById('image_' + product_group_id)) {
            
            document.getElementById('image_' + product_group_id).classList.add('bi-box-fill');
            document.getElementById('image_' + product_group_id).classList.remove('bi-boxes');
        }

        // loop through all expanded product_groups, so we can remove collapsed product_group
        for (var i = 0; i < expanded_product_groups.length; i++) {
            // if this product_group is the collapsed product_group, remove product_group from array
            if (expanded_product_groups[i] == product_group_id) {
                expanded_product_groups.splice(i, 1);
            }
        }

        save_expanded_product_groups_cookie();
    }

    function get_product_group_content(parent, expand_all) {
        var content = '';

        for (var i = 0; i < parent.childNodes.length; i++) {
            switch (parent.childNodes[i].tagName) {
                case 'product_group':
                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            name = '';

                            // if there is a name, then set name
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'enabled') {
                            var enabled = '';

                            // If there is an enabled value, then set it.
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                enabled = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }
                    }

                    // if product_group should be expanded
                    if ((expand_all == true) || (in_array(id, expanded_product_groups) == true) || (id == 1)) {
                        expanded_collapsed_icon = '<span id="image_' + id + '" class="bi bi-boxes icon_product_group me-2"></span>';
                        display = 'block';
                        expanded_product_groups[expanded_product_groups.length] = id;
                    } else {
                        expanded_collapsed_icon = '<span id="image_' + id + '" class="bi bi-box-fill icon_product_group me-2"></span>';
                        display = 'none';
                    }

                    if (enabled == '1') {
                        var status_class = ' status_enabled';
                        var status_button_class = 'disable btn-sm btn d-inline py-0 btn-danger';
                        var status_button_content = Disable;
                    } else {
                        var status_class = ' status_disabled';
                        var status_button_class = 'enable btn-sm btn d-inline py-0 btn-success';
                        var status_button_content = Enable;
                    }

                    var last_class;

                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' last';
                    } else {
                        last_class = '';
                    }

                    content += '<li class="product_group_' + id + status_class + last_class + '"><span class="row"><span onclick="update_product_group_tree(' + id + ')" onmouseover="this.className=\'icon\'" onmouseout="this.className=\'\'">' + expanded_collapsed_icon + '</span><span onmouseover="this.className=\'active\'" onmouseout="this.className=\'\'"><span id="product_group_' + id + '" class="object" ><span onclick="window.location.href=\'edit_product_group.php?id=' + id + '\'"><span class="product_group">' + prepare_content_for_html(name) + '</span></span></span></span><span class="product_group_' + id + '_status_button product_group_status_button status_button ' + status_button_class + '" data-id="' + id + '">' + status_button_content + '</span></span><ul id="ul_' + id + '" style="display: ' + display + '">';
                    content += get_product_group_content(parent.childNodes[i], expand_all);
                    content += '</ul></li>';
                    break;

                case 'product':
                    for (var j = 0; j < parent.childNodes[i].childNodes.length; j++) {
                        if (parent.childNodes[i].childNodes[j].tagName == 'id') {
                            id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'parent_id') {
                            parent_id = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'name') {
                            var name = '';

                            // if there is a name, then set name
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                name = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'enabled') {
                            var enabled = '';

                            // If there is an enabled value, then set it.
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                enabled = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }

                        if (parent.childNodes[i].childNodes[j].tagName == 'short_description') {
                            short_description = '';

                            // if there is a short description, then set short description
                            if (parent.childNodes[i].childNodes[j].firstChild) {
                                short_description = parent.childNodes[i].childNodes[j].firstChild.nodeValue;
                            }
                        }
                    }

                    if (enabled == '1') {
                        var status_class = ' status_enabled';
                        var status_button_class = 'disable btn-sm btn d-inline py-0 btn-danger';
                        var status_button_content = Disable;
                    } else {
                        var status_class = ' status_disabled';
                        var status_button_class = 'enable btn-sm btn d-inline py-0 btn-success';
                        var status_button_content = Enable;
                    }

                    var last_class;

                    // if this is the last li in this ul
                    if (i == (parent.childNodes.length - 1)) {
                        last_class = ' last';
                    } else {
                        last_class = '';
                    }

                    content += '<li class="product_' + id + status_class + last_class + '"><span class="row"><span onclick="window.location.href=\'edit_product.php?id=' + id + '&send_to=' + encodeURIComponent(path + software_directory + '/') + 'view_product_groups.php\'" onmouseover="this.className=\'active\'" onmouseout="this.className=\'\'"><span id="product_' + id + '" class="object" ><span class="bi bi-box2-heart icon_product me-2"></span>' + prepare_content_for_html(name) + ' - ' + prepare_content_for_html(short_description) + '</span></span><span class="product_' + id + '_status_button product_status_button status_button ' + status_button_class + ' product" data-id="' + id + '">' + status_button_content + '</span></span></li>';
                    break;
            }
        }

        return content;
    }
}




// Create user friendly access control names.
function get_access_control_name(access_control_type) {
    switch (access_control_type) {
        case 'public':
            return Public;
            break;

        case 'guest':
            return Guest;
            break;

        case 'private':
            return Private;
            break;

        case 'registration':
            return Registration;
            break;

        case 'membership':
            return Membership;
            break;
    }
}

function collapse_folder_tree() {
    alluls = document.querySelectorAll('#content UL');
    for (i = 0; i < alluls.length; i++) {
        ul = alluls[i];
        if (ul.parentNode.tagName == 'LI') {
            id = ul.id.substr(3);

            image_id = 'image_' + id;
            image = document.getElementById(image_id);

            ul.style.display = 'none';
            if (image != null) {
                image.classList.remove('bi-folder2-open');
                image.classList.add('bi-folder2');

            }

        }
    }

    expanded_folders = new Array();

    // set cookie to remember that this folder is collapsed
    document.cookie = "software[view_folders][expanded_folders]=0; expires=Tue, 01 Jan 2030 06:00:00 GMT";
}

function collapse_product_group_tree() {
    alluls = document.querySelectorAll('#content UL');
    for (i = 0; i < alluls.length; i++) {
        ul = alluls[i];
        if (ul.parentNode.tagName == 'LI') {
            id = ul.id.substr(3);

            image_id = 'image_' + id;
            image = document.getElementById(image_id);

            ul.style.display = 'none';
            image.classList.add('bi-box-fill');image.classList.remove('bi-boxes');
        }
    }

    expanded_product_groups = new Array();

    // set cookie to remember that this product_group is collapsed
    document.cookie = "software[product_group_tree][expanded_product_groups]=0; expires=Tue, 01 Jan 2030 06:00:00 GMT";
}

function get_cookie_value(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function in_array(value, array) {
    for (var i = 0; i < array.length; i++) {
        if (array[i] == value) {
            return true;
        }
    }

    return false;
}

function save_expanded_folders_cookie() {
    // sort expanded folders
    expanded_folders.sort();

    var expanded_folders_cookie = '';

    // loop through all expanded folders
    for (var i = 0; i < expanded_folders.length; i++) {
        // if this folder is not a duplicate then add to cookie value
        if (expanded_folders[i] != expanded_folders[i - 1]) {
            expanded_folders_cookie += expanded_folders[i] + ',';
        }
    }

    // remove last comma
    expanded_folders_cookie = expanded_folders_cookie.substring(0, expanded_folders_cookie.length - 1);

    // save cookie
    document.cookie = "software[view_folders][expanded_folders]=" + expanded_folders_cookie + "; expires=Tue, 01 Jan 2030 06:00:00 GMT";
}

function save_expanded_product_groups_cookie() {
    // sort expanded product_groups
    expanded_product_groups.sort();

    var expanded_product_groups_cookie = '';

    // loop through all expanded product_groups
    for (var i = 0; i < expanded_product_groups.length; i++) {
        // if this product_group is not a duplicate then add to cookie value
        if (expanded_product_groups[i] != expanded_product_groups[i - 1]) {
            expanded_product_groups_cookie += expanded_product_groups[i] + ',';
        }
    }

    // remove last comma
    expanded_product_groups_cookie = expanded_product_groups_cookie.substring(0, expanded_product_groups_cookie.length - 1);

    // save cookie
    document.cookie = "software[product_group_tree][expanded_product_groups]=" + expanded_product_groups_cookie + "; expires=Tue, 01 Jan 2030 06:00:00 GMT";
}

/* =========================
   Filetype map & helpers
   ========================= */
const filetypeMap = {
  aac:'bi-filetype-aac', ai:'bi-filetype-ai', bmp:'bi-filetype-bmp', cs:'bi-filetype-cs',
  css:'bi-filetype-css', csv:'bi-filetype-csv', doc:'bi-filetype-doc', docx:'bi-filetype-docx',
  exe:'bi-filetype-exe', gif:'bi-filetype-gif', heic:'bi-filetype-heic', html:'bi-filetype-html',
  java:'bi-filetype-java', jpg:'bi-filetype-jpg', js:'bi-filetype-js', json:'bi-filetype-json',
  jsx:'bi-filetype-jsx', key:'bi-filetype-key', m4p:'bi-filetype-m4p', md:'bi-filetype-md',
  mdx:'bi-filetype-mdx', mov:'bi-filetype-mov', mp3:'bi-filetype-mp3', mp4:'bi-filetype-mp4',
  otf:'bi-filetype-otf', pdf:'bi-filetype-pdf', php:'bi-filetype-php', ppt:'bi-filetype-ppt',
  pptx:'bi-filetype-pptx', psd:'bi-filetype-psd', py:'bi-filetype-py', raw:'bi-filetype-raw',
  rb:'bi-filetype-rb', sass:'bi-filetype-sass', scss:'bi-filetype-scss', sh:'bi-filetype-sh',
  sql:'bi-filetype-sql', svg:'bi-filetype-svg', tiff:'bi-filetype-tiff', tsx:'bi-filetype-tsx',
  ttf:'bi-filetype-ttf', txt:'bi-filetype-txt', wav:'bi-filetype-wav', woff:'bi-filetype-woff',
  xls:'bi-filetype-xls', xlsx:'bi-filetype-xlsx', xml:'bi-filetype-xml', yml:'bi-filetype-yml'
};