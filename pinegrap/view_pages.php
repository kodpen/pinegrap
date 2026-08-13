<?php
/**
 * PineGrap - Enterprise Website Platform
 *
 * Originally developed as LiveSite by Camelback Web Architects.
 * Since 2017, maintained and evolved by Erdal Güral (Kodpen) under the name PineGrap.
 * The final LiveSite update (2019) has been integrated into PineGrap.
 * LiveSite remains available as a separate downloadable legacy version.
 *
 * @author      Camelback Web Architects
 *              Erdal Güral (Kodpen)
 * @link        https://livesite.com
 *              https://kodpen.com
 * @copyright   2001–2019 Camelback Consulting, Inc.
 *              2016–2025 Kodpen
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 */

include('init.php');

$liveform = new liveform('view_pages');
$user = validate_user();
validate_area_access($user, 'user');

$output_clear_button = '';

// If there is a filter set.
if (isset($_GET['filter']) == true) {
    // Send the filter to the search form.
    $filter = $_GET['filter'];
} else {
    $filter = 'default';
}

$filter_for_links = '&filter=' . $filter;
$output_filter_for_links = h($filter_for_links);

// build filters array
$filters_in_array = 
    array(
        'all_my_pages'=>lang('All My Pages'),
        'all_my_archived_pages'=>lang('All My Archived Pages'),
        'my_home_pages'=>lang('My Home Pages'),
        'my_searchable_pages'=>lang('My Searchable Pages'),
        'my_unsearchable_pages'=>lang('My Unsearchable Pages'),
        'my_sitemap_pages'=>lang('My Site Map Pages'),
        'my_rss_enabled_pages'=>lang('My RSS Enabled Pages'),
        'my_standard_pages'=>lang('My Standard Pages'),
        'my_photo_gallery_pages'=>lang('My Photo Gallery Pages'),
        'my_calendar_pages'=>lang('My Calendar Pages'),
        'my_custom_form_pages'=>lang('My Custom Form Pages'),
        'my_form_view_pages'=>lang('My Form View Pages'),
        'my_commerce_pages'=>lang('My Commerce Pages'),
        'my_account_pages'=>lang('My Account Pages'),
        'my_login_pages'=>lang('My Login Pages'),
        'my_affiliate_pages'=>lang('My Affiliate Pages'),
        'my_miscellaneous_pages'=>lang('My Miscellaneous Pages'),
        'my_public_pages'=>lang('My Public Access Pages'),
        'my_guest_pages'=>lang('My Guest Access Pages'),
        'my_registration_pages'=>lang('My Registration Access Pages'),
        'my_membership_pages'=>lang('My Member Access Pages'),
        'my_private_pages'=>lang('My Private Access Pages')
    );

// if sort was set, update session
if (isset($_REQUEST['sort'])) {
    // store sort in session
    $_SESSION['software']['pages']['sort'] = $_REQUEST['sort'];

    // clear order
    $_SESSION['software']['pages']['order'] = '';
}

// if order was set, update session
if (isset($_REQUEST['order'])) {
    $_SESSION['software']['pages']['order'] = $_REQUEST['order'];
}

// If a screen was passed and it is a positive integer, then use it.
// These checks are necessary in order to avoid SQL errors below for a bogus screen value.
if (
    isset($_REQUEST['screen'])
    and $_REQUEST['screen']
    and is_numeric($_REQUEST['screen'])
    and $_REQUEST['screen'] > 0
    and $_REQUEST['screen'] == round($_REQUEST['screen'])
) {
    $screen = (int) $_REQUEST['screen'];

// Otherwise, use the default, which is the first screen.
} else {
    $screen = 1;
}

// If the sort session is set to Form Name or Form Enabled and the view filter is not set to custom form set the sort session to default
if (((isset($_SESSION['software']['pages']) && $_SESSION['software']['pages']['sort'] == lang('Form Name') ) || (isset($_SESSION['software']['pages']) && $_SESSION['software']['pages']['sort'] == lang('Form Enabled') )) && ($filter != 'my_custom_form_pages')) {
    $_SESSION['software']['pages']['sort'] = '';
}

// If a theme is being previewed then get the activated theme.
// We will use in several places below.
if (isset($_SESSION['software']['preview_theme_id']) && $_SESSION['software']['preview_theme_id']) {
    $activated_theme_id = db_value("SELECT id FROM files WHERE activated_" . $_SESSION['software']['device_type'] . "_theme = '1'");
}

// If the sort is set to Desktop Page Style or Mobile Page Style,
// and the user is previewing a theme that is not the activated theme,
// then reset the sort to the default, because we can't easily sort for preview styles.
if (
    (
        (isset($_SESSION['software']['pages']) && $_SESSION['software']['pages']['sort'] == 'Desktop Page Style')
        || (isset($_SESSION['software']['pages']) && $_SESSION['software']['pages']['sort'] == 'Mobile Page Style')
    )
    && (isset($_SESSION['software']['preview_theme_id']))
    && ($_SESSION['software']['preview_theme_id'])
    && ($_SESSION['software']['preview_theme_id'] != $activated_theme_id)
) {
    unset($_SESSION['software']['pages']['sort']);
    unset($_SESSION['software']['pages']['order']);
}

$style_join_field = "";
$style_column_heading_label = '';
$output_device_type_toggle = '';

// do some different things based on the device type
switch ($_SESSION['software']['device_type']) {
    case 'desktop':
    default:
        // set style join field to desktop style field
        $style_join_field = "page.page_style";

        // set the style column heading
        $style_column_heading_label = lang('Desktop Page Style');

        // output toggle to show mobile
        $output_device_type_toggle = '<a class="btn btn-sm btn-secondary p-1 material-icons" href="update_device_type.php?device_type=mobile&amp;send_to=' . h(urlencode(get_request_uri())) . get_token_query_string_field() . '" title="' . lang('Show mobile Page Styles') . '">phone_iphone</a>';

        // if the sort is mobile page style, then set it to desktop page style,
        // so that the column will still be sorted correctly
        if (isset($_SESSION['software']['pages']) && $_SESSION['software']['pages']['sort'] == 'Mobile Page Style') {
            $_SESSION['software']['pages']['sort'] = 'Desktop Page Style';
        }

        break;
    
    case 'mobile':
        // set style join field to mobile style field
        $style_join_field = "page.mobile_style_id";

        // set the style column heading
        $style_column_heading_label = lang('Mobile Page Style');

        // output toggle to show desktop
        $output_device_type_toggle = '<a class="btn btn-sm btn-secondary p-1 material-icons" href="update_device_type.php?device_type=desktop&amp;send_to=' . h(urlencode(get_request_uri())) . get_token_query_string_field() . '" title="' . lang('Show desktop Page Styles') . '">computer</a>';

        // if the sort is desktop page style, then set it to mobile page style,
        // so that the column will still be sorted correctly
        if ($_SESSION['software']['pages']['sort'] == 'Desktop Page Style') {
            $_SESSION['software']['pages']['sort'] = 'Mobile Page Style';
        }

        break;
}
if(isset($_SESSION['software']['pages'])){
    switch ($_SESSION['software']['pages']['sort']) {
        case lang('Name'):
            $sort_column = 'page_name';
            break;
    
        case lang('Form Name'):
            $sort_column = 'form_name';
            break;
    
        case lang('Form Enabled'):
            $sort_column = 'enabled';
            break;
    
        case lang('Folder'):
            $sort_column = 'folder_name';
            break;
    
        case lang('Desktop Page Style'):
        case lang('Mobile Page Style'):
            $sort_column = 'style_name';
            break;
    
        case lang('Searchable'):
            $sort_column = 'page_search';
            break;
    
        case lang('Comments'):
            $sort_column = 'comments';
            break;
    
        case lang('Page Type'):
            $sort_column = 'page_type';
            break;
            
        case lang('SEO'):
            $sort_column = 'seo_score';
            break;
    
        case lang('Site Map'):
            $sort_column = 'sitemap';
            break;
    
        case lang('Last Modified'):
            $sort_column = 'page_timestamp';
            break;
    
        default:
            $sort_column = 'page_timestamp';
            $_SESSION['software']['pages']['sort'] = lang('Last Modified');
            break;
    }
}else{
    $sort_column = 'page_timestamp';
    $_SESSION['software']['pages']['sort'] = lang('Last Modified');
}


if (isset($_SESSION['software']['pages']['order']) && $_SESSION['software']['pages']['order']) {
    $asc_desc = $_SESSION['software']['pages']['order'];
} elseif ($sort_column == 'page_timestamp') {
    $asc_desc = 'desc';
    $_SESSION['software']['pages']['order'] = 'desc';
} else {
    $asc_desc = 'asc';
    $_SESSION['software']['pages']['order'] = 'asc';
}

$folders_that_user_has_access_to = array();

// if user is a basic user, then get folders that user has access to
if ($user['role'] == 3) {
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
}


$output_button_bar = '';

// if the user is at least a manager or has create pages turned on, then output the create page button and button bar
if (($user['role'] < '3') || ($user['create_pages'] == TRUE)) {
    $output_button_bar .=
        '<nav id="button_bar" class="navigation " aria-label="Button Bar">
        <a class="btn btn-sm btn-primary m-1" href="add_page.php" data-loading-content="' . lang(array('string'=>'Loading')) . '"><span class="bi bi-plus-circle me-2"></span>' . lang('Create') . '</a>
        <a class="btn btn-sm btn-outline-secondary m-1" href="add_system_style.php?from=pages" data-loading-content="' . lang(array('string'=>'Loading')) . '"><span class="bi bi-plus-circle me-2"></span>' . lang('Create') . ' (Visual Page Editor)</a>
        ';

    // If advanced search is enabled and the user is a manager or above then output "Update Search Index" button.
    if ((SEARCH_TYPE == 'advanced') && (USER_ROLE != 3)) {
        $output_button_bar .= '
        <div class=" btn-group btn-group-sm flex-wrap">
            <button type="button" class="btn btn-link link-secondary py-0 m-1" onclick="window.open(\'update_search_index.php\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=500,height=500\');"><span class="material-icons me-1">manage_search</span>' . lang(array('string'=>'Update Search Index') ) . '</button>
        </div>';
    }
    $output_button_bar .= '</nav>';
}

// Set the name heading to the default.
// This may be changed by different filters
$output_name_heading = lang('Name');
$page_access_control_filter = '';
$output_form_name_heading  = '';
$output_form_enabled_heading = '';
$where = '';
$join_table = '';
$group_column_by = '';
$select_column = '';
// Switch between the subnav filters
switch ($filter) {
    case 'all_my_archived_pages':
        // Change the heading and subheading.
        $heading = lang('All My Archived Pages');
        $subheading = lang('All archived pages that I can edit & duplicate.');
        break;
    
    case 'my_home_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_home = "yes")';

        // Change the heading and subheading.
        $heading = lang('My Home Pages');
        $subheading = lang('These pages are rotated as the website\'s home page.');
        break;

    case 'my_searchable_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_search = "1")';
        // Change the heading and subheading.
        $heading = lang('My Searchable Pages');
        $subheading = lang('Depending on the page\'s access control, these pages can be found using the built-in site search feature.');
        break;

    case 'my_unsearchable_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_search = "0")';
        // Change the heading and subheading.
        $heading = lang('My Unsearchable Pages');
        $subheading = lang('These pages cannot be found using the built-in site search.');
        break;
        
    case 'my_sitemap_pages':
        // set page access control filter to public so only public pages show up in the results
        $page_access_control_filter = 'public';
        
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }
        
        // Set the query filter
        $where .= ' (page.sitemap = "1")';
        
        // Change the heading and subheading.
        $heading = lang('My Site Map Pages');
        $subheading = lang('These are Public Pages that appear in the sitemap.xml file so search engines can find them.');
        break;
        
    case 'my_rss_enabled_pages':
        // set page acces control filter to public so only public pages show up in the results
        $page_access_control_filter = 'public';
    
        // Join tables to query
        $join_table = 
            "LEFT JOIN form_list_view_pages ON
                (form_list_view_pages.page_id = page.page_id)
                AND (form_list_view_pages.collection = 'a')
             LEFT JOIN form_fields ON form_fields.page_id = form_list_view_pages.custom_form_page_id";
        
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }
        
        // Set the query filter
        $where .= '(((page.page_type = "form list view") AND (form_fields.rss_field != "")) OR (page.page_type = "calendar view") OR (page.page_type = "catalog") OR (page.page_type = "catalog detail") OR (page.page_type = "order form"))';
        
        // Group the pages based on their id
        $group_column_by = 'GROUP BY (page.page_id)';
            
        // Change the heading and subheading.
        $heading = lang('My RSS Enabled Pages');
        $subheading = lang('These are Public Pages that are able to broadcast RSS feeds.');
        break;

    case 'my_standard_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "standard")';

        // Change the heading and subheading.
        $heading = lang('My Standard Pages');
        $subheading = lang('These pages only contain content and do not contain any built-in interactive features.');
        break;

    case 'my_photo_gallery_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "photo gallery")';

        // Change the heading and subheading.
        $heading = lang('My Photo Gallery Pages');
        $subheading = lang('These pages display a photo gallery slideshow of all photos located in the same folder.');
        break;

    case 'my_calendar_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "calendar event view" OR page_type = "calendar view")';

        // Change the heading and subheading.
        $heading = lang('My Calendar Pages');
        $subheading = lang('These pages overlay one or more calendars, calendar details, and published events.');
        break;
    case 'my_custom_form_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        $select_column = ',
            custom_form_pages.form_name,
            custom_form_pages.enabled as enabled';

        $join_table = 'LEFT JOIN custom_form_pages ON custom_form_pages.page_id = page.page_id';

        // Set the query filter.
        $where .= ' (page_type = "custom form" OR page_type = "custom form" OR page_type = "custom form confirmation")';

        // Output additional table headings
        $output_form_name_heading = '<th>' . get_column_heading(lang('Form Name'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>';
        $output_form_enabled_heading = '<th>' . get_column_heading(lang('Form Enabled'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>';

        // Change the heading.
        $heading = lang('My Custom Form Pages');
        $subheading = lang('These pages either gather data through customizable forms or display submitted form confirmations.');
        break;
    case 'my_form_view_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }
        // Set the query filter.
        $where .= ' ((page_type = "form list view") OR (page_type = "form item view") OR (page_type = "form view directory"))';
        // Change the heading.
        $heading = lang('My Form View Pages');
        // Change the subheading.
        $subheading = lang('These pages display submitted form data in customizable views.');
        break;
    case 'my_commerce_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "order form" OR page_type = "view order" OR page_type = "catalog" OR page_type = "catalog detail" OR page_type = "express order" OR page_type = "shopping cart" OR page_type = "shipping address and arrival" OR page_type = "shipping method" OR page_type = "billing information" OR page_type = "order preview" OR page_type = "order receipt" OR page_type = "update address book")';

        // Change the heading and subheading.
        $heading = lang('My Commerce Pages');
        $subheading = lang('These pages provide the built-in ecommerce features.');
        break;

    case 'my_account_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "my account" OR page_type = "my account profile" OR page_type = "email preferences" OR page_type = "view order" OR page_type = "update address book" OR page_type = "change password")';

        // Change the heading and subheading.
        $heading = lang('My Account Pages');
        $subheading = lang('These pages provide the built-in account self-management for all site users.');
        break;

    case 'my_login_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "registration entrance" OR page_type = "registration confirmation" OR page_type = "membership entrance" OR page_type = "membership confirmation" OR page_type = "forgot password" OR page_type = "login" OR page_type = "logout" OR page_type = "login" OR page_type = "set password")';

        // Change the heading and subheading.
        $heading = lang('My Login Pages');
        $subheading = lang('These pages provide the built-in user account creation and login capabilities for all site users.');
        break;

    case 'my_affiliate_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "affiliate sign up form" OR page_type = "affiliate sign up confirmation" OR page_type = "affiliate welcome")';

        // Change the heading and subheading.
        $heading = lang('My Affiliate Pages');
        $subheading = lang('These pages provide the built-in affiliate sign up features.');
        break;

    case 'my_miscellaneous_pages':
        // If where is blank
        if ($where == '') {
            $where .= 'WHERE ';

        // else where is not blank, so add and
        } else {
            $where .= 'AND ';
        }

        // Set the query filter.
        $where .= ' (page_type = "email a friend" OR page_type = "error" OR page_type = "folder view" OR page_type = "search results")';

        // Change the heading and subheading.
        $heading = lang('My Miscellaneous Pages');
        $subheading = lang('These pages provide responses to other site-wide built-in features.');
        break;

    case 'my_public_pages':
        // Set the page access control filter
        $page_access_control_filter = 'public';

        // Change the heading and subheading.
        $heading = lang('My Public Access Pages');
        $subheading = lang('These pages are visible by any website visitor.');
        break;

    case 'my_guest_pages':
        // Set the page access control filter
        $page_access_control_filter = 'guest';

        // Change the heading and subheading.
        $heading = lang('My Guest Access Pages');
        $subheading = lang('These pages are visible to any website visitor after they are offered the option to login or register.');
        break;

    case 'my_registration_pages':
        // Set the page access control filter
        $page_access_control_filter = 'registration';

        // Change the heading and subheading.
        $heading = lang('My Registration Access Pages');
        $subheading = lang('These pages are visible to any website visitor, but only after they login or register.');
        break;

    case 'my_membership_pages':
        // Set the page access control filter
        $page_access_control_filter = 'membership';

        // Change the heading and subheading.
        $heading = lang('My Member Access Pages');
        $subheading = lang('These pages are visible to any website user, but only after they have either registered with a valid member id, purchased a membership product, completed a membership trial custom form, or logged in as an unexpired member.');
        break;

    case 'my_private_pages':
        // Set the page access control filter
        $page_access_control_filter = 'private';

        // Change the heading and subheading.
        $heading = lang('My Private Access Pages');
        $subheading = lang('These pages are visible only to website users who have been granted view access to the parent folder, or who have purchased a product that grants private access to the parent folder.');
        break;

    default:
        // Change the heading and subheading.
        $heading = lang('All My Pages');
        $subheading = lang('All pages that I can edit &amp; duplicate.');
        break;
}

// If the user is previewing a theme that is not the activated theme,
// then output unsortable column heading for style column.
if (isset($_SESSION['software']['preview_theme_id']) && $_SESSION['software']['preview_theme_id'] && $_SESSION['software']['preview_theme_id'] != $activated_theme_id ) {
    $output_style_column_heading = $style_column_heading_label;

// Otherwise output sortable column.
} else {
    $output_style_column_heading = get_column_heading($style_column_heading_label, $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links);
}

// If where is blank
if ($where == '') {
    $where .= 'WHERE ';

// else where is not blank, so add and
} else {
    $where .= 'AND ';
}

// if the filter is not all my archived pages, then add the sql where statement to prevent getting archived pages
// NULL check covers pages with no parent folder (page_folder = 0) — LEFT JOIN produces NULL
// for folder columns in that case, and NULL = "0" evaluates to false in SQL.
if ($filter != 'all_my_archived_pages') {
    $where .= '(folder.folder_archived = "0" OR folder.folder_archived IS NULL)';

// else this is the all my archived pages filter, so only get archived pages
} else {
    $where .= '(folder.folder_archived = "1")';
}

$all_pages = 0;
$my_pages = 0;

// Get file's id and folder number from files.
$query =
    "SELECT
       page.page_id,
       page.page_folder
    FROM page
    LEFT JOIN folder ON page.page_folder = folder.folder_id
    LEFT JOIN style ON $style_join_field = style.style_id
    LEFT JOIN user ON page.page_user = user.user_id
    " . $join_table . "
    $where
    " . $group_column_by . "
    ORDER BY $sort_column $asc_desc";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// Loop through the results
while ($row = mysqli_fetch_assoc($result)) {

    // If the folder access control filter is set.
    if ($page_access_control_filter) {

        // Compare the filter to the folder's access control type and set the row if they match.
        if (get_access_control_type($row['page_folder']) == $page_access_control_filter) {

            // Add one to all files.
            $all_pages++;

            // if user has access to file then add one to my files.
            if (check_folder_access_in_array($row['page_folder'], $folders_that_user_has_access_to) == true) {
                $my_pages++;
            }
       }

    // If the filder access control filter is not set.
    } else {
        // Add one to all files.
        $all_pages++;

        // if user has access to file then add one to my files.
        if (check_folder_access_in_array($row['page_folder'], $folders_that_user_has_access_to) == true) {
            $my_pages++;
        }
    }
}




// get all pages
$query =
    "SELECT
       page.page_id,
       page.page_name,
       page.page_folder,
       page.page_style,
       page.layout_type,
       folder.folder_name,
       folder.folder_access_control_type,
       style.style_name,
       style.style_type,
       page.page_search,
       page.page_home,
       page.page_type,
       page.comments,
       page.seo_score,
       page.sitemap,
       user.user_username,
       page.page_timestamp" . $select_column . "
    FROM page
    LEFT JOIN folder ON page.page_folder = folder.folder_id
    LEFT JOIN style ON $style_join_field = style.style_id
    LEFT JOIN user ON page.page_user = user.user_id
    " . $join_table . "
    $where
    " . $group_column_by . "
    ORDER BY $sort_column $asc_desc";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

while ($row = mysqli_fetch_assoc($result)) {
    // if user has access to page then add page to pages array
    if (check_folder_access_in_array($row['page_folder'], $folders_that_user_has_access_to) == true) {

        // If there is a folder access control filter.
        if ($page_access_control_filter) {

            // Compare the filter to the pages folder's access control type and set the row if they match.
            if (get_access_control_type($row['page_folder']) == $page_access_control_filter) {
                $pages[] = $row;
            }

        // Else If the folder access control filter is not set.
        } else {
            // Set the row.
            $pages[] = $row;
        }
    }
}
$output_form_name_row = '';
$output_form_enabled_row = '';
$output_rows = '';
// if there is at least one result to display
if ($pages) {
    foreach ($pages as $page) {
        $query_string_from = '';

        // if page type is a certain page type, then prepare from
        switch ($page['page_type']) {
            case 'view order':
            case 'custom form':
            case 'custom form confirmation':
            case 'calendar event view':
            case 'catalog detail':
            case 'shipping address and arrival':
            case 'shipping method':
            case 'logout':
                $query_string_from = '?from=control_panel';
                break;
        }

        $output_link_url = h(escape_javascript(PATH)) . h(escape_javascript(encode_url_path($page['page_name']))) . $query_string_from;

        $output_style_name = '';

        // If the user is previewing a theme, and it is not the activated theme,
        // then get preview style if one exists.
        if (isset($_SESSION['software']['preview_theme_id']) && $_SESSION['software']['preview_theme_id'] && $_SESSION['software']['preview_theme_id'] != $activated_theme_id ) {
            $style_name = db_value(
                "SELECT style.style_name
                FROM preview_styles
                LEFT JOIN style ON preview_styles.style_id = style.style_id
                WHERE
                    (preview_styles.page_id = '" . $page['page_id'] . "')
                    AND (preview_styles.theme_id = '" . escape($_SESSION['software']['preview_theme_id']) . "')
                    AND (device_type = '" . escape($_SESSION['software']['device_type']) . "')");

            // If a preview style was found, then prepare to output style name.
            if ($style_name != '') {
                $output_style_name = '[P] ' . h($style_name);
            }
        }

        // If a style has not been found yet, then that means user is not previewing themes
        // or there is no preview style, so just output activated style.
        if ($output_style_name == '') {
            $output_no_mobile_style_warning_class = '';

            // if the page has a style specifically defined for it, then prepare to output style name
            if ($page['style_name'] != '') {
                $output_style_name = h($page['style_name']);

            // else the page does not have a style specifically defined for it, so get inherited style name
            } else {
                // get inherited style id
                $style_id = get_style($page['page_folder'], $_SESSION['software']['device_type']);

                // if the device type is set to mobile and a mobile style id could not be found, then get desktop style id
                // because we fallback to a desktop style when a mobile style cannot be found for a page
                if (
                    ($_SESSION['software']['device_type'] == 'mobile')
                    && ($style_id == 0)
                ) {
                    $style_id = get_style($page['page_folder'], 'desktop');

                    $output_no_mobile_style_warning_class = ' text-danger';
                }

                // if a style was found, then get style name
                if ($style_id != 0) {
                    // get inherited style name
                    $query = "SELECT style_name FROM style WHERE style_id = '$style_id'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);

                    $output_style_name = 'Default: ' . h($row['style_name']);
                }
            }

            // If a theme is being previewed, then add an "[A]" prefix before style name,
            // to show that it is the activated style.
            if (isset($_SESSION['software']['preview_theme_id']) && $_SESSION['software']['preview_theme_id']) {
                $output_style_name = '[A] ' . $output_style_name;
            }
        }

        $output_search_check_mark = '';

        // if page is searchable, then prepare to output check mark image
        if ($page['page_search'] == 1) {
            $output_search_check_mark = '<span class="material-icons">task_alt</span>';
        }

        $output_home_icon = '';
        $output_comments_check_mark = '';

        // if page is a home page, then prepare to output home icon
        if ($page['page_home'] == 'yes') {
            $output_home_icon = '<span class="material-icons">home</span>';
        }
        
        // if page has comments enabled, then prepare to output check mark image
        if ($page['comments'] == '1') {
            $output_comments_check_mark = '<span class="material-icons">task_alt</span>';
        }
        
        $output_sitemap_check_mark = '';
        
        // if page has sitemap enabled, then prepare to output check mark image
        if ($page['sitemap'] == '1') {
            $output_sitemap_check_mark = '<span class="material-icons">task_alt</span>';
        }

        // If custom form filter is on.
        if ($filter == 'my_custom_form_pages') {

            // if page is a home page, then prepare to output check mark image
            if ($page['enabled'] == 1) {
                $output_form_enabled_mark = '<span class="material-icons">task_alt</span>';
            }

            // output form rows
            $output_form_name_row = '<td class="align-middle">' . h($page['form_name']) . '</td>';
            $output_form_enabled_row = '<td class="align-middle text-center">' . $output_form_enabled_mark . '</td>';
        }

        $output_edit_url = 'edit_page.php?id=' . $page['page_id'];
        
        if($page['user_username'] != ''){
            $output_last_modifier_user = ' ' . lang(array('string'=>'by {var:1}','vars'=>array( h($page['user_username']) ) ) );
        }else{
            $output_last_modifier_user = '';
        }
        
        $output_rows .=
            '<tr>
                <td class="select-all align-middle text-start"><input class="form-check-input " type="checkbox" name="pages[]" value="' . $page['page_id'] . '" class="checkbox" /></td>
			    <td class="align-middle text-start action-buttons">
                    <button type="button" class="m-1 btn-data-control btn btn-outline-primary border-2 " data-loading-content=" " title="' . lang('Edit') . '" onclick="window.location.href=\'' . $output_edit_url . '\'"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="m-1 btn-data-control btn btn-outline-secondary border-2 " data-loading-content=" " title="' . lang('Preview') . '" onclick="window.location.href=\'' . $output_link_url . '\'"><i class="bi bi-link"></i></button>
                </td>
                <td class="align-middle chart_label position-relative">' . $output_home_icon . h($page['page_name']) . '</td>
                ' . $output_form_name_row . '
                ' . $output_form_enabled_row . '
                <td class="align-middle text-start ' . $page['folder_access_control_type'] . '" title="' . h($page['folder_name']) . '"><span class="d-block overflow-hidden text-truncate" style="width: 100px;max-width:100%;"><span class="material-icons d-inline">folder</span><span class="badge fw-light text-reset d-inline">' . h($page['folder_name']) . '</span></span></td>
                <td class="align-middle ' . $output_no_mobile_style_warning_class . '">' . $output_style_name . '</td>
                <td class="align-middle">' . h(get_page_type_name($page['page_type'])) . '</td>
                <td class="align-middle text-center">' . $output_search_check_mark . '</td>
                <td class="align-middle text-center">' . $output_comments_check_mark . '</td>
                <td class="align-middle text-center">' . $output_sitemap_check_mark . '</td>
                <td class="align-middle text-center">' . h($page['seo_score']) . '</td>
                <td class="align-middle" >' . get_relative_time(array('timestamp' => $page['page_timestamp'])) . $output_last_modifier_user . ' </td>
            </tr>';
    }
}

$output_delete_selected_button = '';

// if the user is at least a manager or has access to delete pages, then output the delete selected button
if (($user['role'] < '3') || ($user['delete_pages'] == TRUE)) {
    $output_delete_selected_button = '<button type="button" value="Delete Selected" class=" btn mb-1 mt-1 btn-danger disabled" data-loading-content="' . lang(array('string'=>'Deleting') ) . '" data-confirm-content="' . lang(array('string'=>'WARNING: Selected {var:1} will be permanently deleted.','vars'=>array(lang('pages')))) . '"><span class="material-icons me-2">delete</span>' . lang(array('string'=>'Delete Selected') ) . '</button>';
}

echo
pg_page_shell(
    array(
        'title'=> lang('Pages'),
        'extra classes'=>'page',
        'icon'=>'page', 
        'heading'=>lang('Pages'),
        
    )
) . '
    <div class="row">
        <div class="col-12">
            ' . $liveform->output_errors() . '
            ' . $liveform->get_warnings() . '
            ' . $liveform->output_notices() . '
            <div class="row mb-2  flex-wrap">
                <div class="col-12 col-sm-12 col-md-6 col-xl-9 text-center text-md-start">
                    <h2 class="d-inline-block " data-bs-content="' . $subheading . '" title="' . $heading . '">' . $heading . '</h2>
                    ' . $output_button_bar . '
                </div>
                <div class="col-12 col-sm-12 col-md-6 col-xl-3 ">
                    <div class="row justify-content-center justify-content-md-end">
                        <form id="search_form" action="view_pages.php" method="get" class="search_form col-auto">
                            <input type="hidden" name="filter" value="' . h($filter) . '" />
                            <div class="input-group input-group-sm">
                                <label class="input-group-text mt-1 mb-1 material-icons" title="' . lang('Content that viewed') . '" for="filter_select">visibility</label>
                                <select id="filter_select" name="filter" class="form-select mt-1 mb-1" title="' . lang('Content that viewed') . '" onchange="submit_form(\'search_form\')">' . get_filter_options($filters_in_array, $filter) . '</select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card my-4">
                <div class="card-body p-0 position-relative">
                    <form name="form"  action="edit_pages.php" method="post"> 
                        ' . get_token_field() . '
                        <input type="hidden" name="action" />
                        <input type="hidden" name="move_to_folder" />
                        <input type="hidden" name="edit_page_style" />
                        <input type="hidden" name="edit_mobile_style_id" />
                        <input type="hidden" name="edit_site_search" />
                        <input type="hidden" name="edit_sitemap" />
                        <input type="hidden" name="send_to" value="' . h(get_request_uri()) . '" />
                        <table class="chart table-hover table " style="width:100%;display:none">
                            <thead>
                                <tr>
                                    <th class="noVis">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" title="' . lang(array('string'=>'Select/Deselect All') ) . '" type="checkbox" id="select_all">
                                        </div>
                                    </th>
                                    <th class="noVis">' . lang(array('string'=>'Action') ) . '</th> 
                                    <th>' . get_column_heading($output_name_heading, $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                                    ' . $output_form_name_heading . '
                                    ' . $output_form_enabled_heading . '
                                    <th>' . get_column_heading(lang('Folder'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                                    <th>' . $output_style_column_heading . '' . $output_device_type_toggle . '</th>
                                    <th>' . get_column_heading(lang('Page Type'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                                    <th  style="text-align: center;">' . get_column_heading(lang('Searchable'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                                    <th style="text-align: center;">' . get_column_heading(lang('Comments'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                                    <th style="text-align: center;">' . get_column_heading(lang('Site Map'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                                    <th style="text-align: center;">' . get_column_heading(lang('SEO'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                                    <th>' . get_column_heading(lang('Last Modified'), $_SESSION['software']['pages']['sort'], $_SESSION['software']['pages']['order'], $output_filter_for_links) . '</th>
                                </tr>
                            </thead>
                            <tbody>
                                ' . $output_rows . '
                            </tbody>
                        </table>

                        <nav class="buttons navigation text-center position-sticky" style="bottom:.5rem;" aria-label="data edit buttons ">
                            <div class="container">
                                <div class=" btn-group btn-group-sm flex-wrap justify-content-center mb-0 enable-on-selected">                                   
                                    <button type="button" value="Modify Selected" class=" btn mb-1 mt-1 btn-primary disabled" onclick="window.open(\'edit_pages.php\', \'popup\', \'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,copyhistory=no,scrollbars=yes,width=500,height=500\'); edit_chart_content(\'edit\',\'product\')"><span class="material-icons me-2">edit</span>' . lang(array('string'=>'Modify Selected') ) . '</button>
                                    ' . $output_delete_selected_button . '
                                </div>
                            </div>
                        </nav>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>' .
output_footer();

$liveform->remove_form('view_pages');