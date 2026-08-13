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
$user = validate_user();
validate_ecommerce_access($user);

include_once('liveform.class.php');
$liveform = new liveform('edit_product');

if (!$_POST) {
    $query = "SELECT *
             FROM products
             WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);

    $name = $row['name'];
    $enabled = $row['enabled'];
    $short_description = $row['short_description'];
    $full_description = $row['full_description'];
    $details = $row['details'];
    $code = $row['code'];
    $keywords = $row['keywords'];
    $image_name = $row['image_name'];
    $price = sprintf("%01.2lf", $row['price'] / 100);
    $taxable = $row['taxable'];
    $contact_group_id = $row['contact_group_id'];
    $order_receipt_bcc_email_address = $row['order_receipt_bcc_email_address'];
    $email_page = $row['email_page'];
    $email_bcc = $row['email_bcc'];
    $order_receipt_message = $row['order_receipt_message'];
    $required_product = $row['required_product'];
    $selection_type = $row['selection_type'];
    $default_quantity = $row['default_quantity'];
    $minimum_quantity = $row['minimum_quantity'];
    $maximum_quantity = $row['maximum_quantity'];
    $address_name = $row['address_name'];
    $title = $row['title'];
    $meta_description = $row['meta_description'];
    $meta_keywords = $row['meta_keywords'];
    $inventory = $row['inventory'];
    $inventory_quantity = $row['inventory_quantity'];
    $backorder = $row['backorder'];
    $out_of_stock_message = $row['out_of_stock_message'];
    $shippable = $row['shippable'];

    $weight = '';

    if ($row['weight'] > 0) {
        $weight = $row['weight']+0;
    }

    $primary_weight_points = $row['primary_weight_points'];
    $secondary_weight_points = $row['secondary_weight_points'];

    $length = '';

    if ($row['length'] > 0) {
        $length = $row['length']+0;
    }

    $width = '';

    if ($row['width'] > 0) {
        $width = $row['width']+0;
    }

    $height = '';

    if ($row['height'] > 0) {
        $height = $row['height']+0;
    }
    $container_required = $row['container_required'];
    $preparation_time = $row['preparation_time'];
    $free_shipping = $row['free_shipping'];
    $extra_shipping_cost = sprintf("%01.2lf", $row['extra_shipping_cost'] / 100);
    $commissionable = $row['commissionable'];
    $commission_rate_limit = $row['commission_rate_limit'];
    $recurring = $row['recurring'];
    $recurring_schedule_editable_by_customer = $row['recurring_schedule_editable_by_customer'];
    $start = $row['start'];
    $number_of_payments = $row['number_of_payments'];
    $payment_period = $row['payment_period'];
    $recurring_profile_disabled_perform_actions = $row['recurring_profile_disabled_perform_actions'];
    $recurring_profile_disabled_expire_membership = $row['recurring_profile_disabled_expire_membership'];
    $recurring_profile_disabled_revoke_private_access = $row['recurring_profile_disabled_revoke_private_access'];
    $recurring_profile_disabled_email = $row['recurring_profile_disabled_email'];
    $recurring_profile_disabled_email_subject = $row['recurring_profile_disabled_email_subject'];
    $recurring_profile_disabled_email_page_id = $row['recurring_profile_disabled_email_page_id'];
    $sage_group_id = $row['sage_group_id'];
    $membership_renewal = $row['membership_renewal'];
    $grant_private_access = $row['grant_private_access'];
    $private_folder = $row['private_folder'];
    $private_days = $row['private_days'];
    $send_to_page = $row['send_to_page'];
    $reward_points = $row['reward_points'];
    $gift_card = $row['gift_card'];
    $gift_card_email_subject = $row['gift_card_email_subject'];
    $gift_card_email_format = $row['gift_card_email_format'];
    $gift_card_email_body = $row['gift_card_email_body'];
    $gift_card_email_page_id = $row['gift_card_email_page_id'];
    $submit_form = $row['submit_form'];
    $submit_form_custom_form_page_id = $row['submit_form_custom_form_page_id'];
    $submit_form_quantity_type = $row['submit_form_quantity_type'];
    $submit_form_create = $row['submit_form_create'];
    $submit_form_update = $row['submit_form_update'];
    $submit_form_update_where_field = $row['submit_form_update_where_field'];
    $submit_form_update_where_value = $row['submit_form_update_where_value'];
    $add_comment = $row['add_comment'];
    $add_comment_page_id = $row['add_comment_page_id'];
    $add_comment_message = $row['add_comment_message'];
    $add_comment_name = $row['add_comment_name'];
    $add_comment_only_for_submit_form_update = $row['add_comment_only_for_submit_form_update'];
    $product_form = $row['form'];
    $form_name = $row['form_name'];
    $form_label_column_width = $row['form_label_column_width'];
    $form_quantity_type = $row['form_quantity_type'];
    $custom_field_1 = $row['custom_field_1'];
    $custom_field_2 = $row['custom_field_2'];
    $custom_field_3 = $row['custom_field_3'];
    $custom_field_4 = $row['custom_field_4'];
    $notes = $row['notes'];
    $google_product_category = $row['google_product_category'];
    $gtin = $row['gtin'];
    $brand = $row['brand'];
    $mpn = $row['mpn'];
    $seo_score = $row['seo_score'];

    $output_enabled_checked = '';

    if ($enabled == 1) {
        $output_enabled_checked = ' checked="checked"';
    }

    // if taxable is on
    if ($taxable == 1) {
        $taxable_checked = 'checked="checked"';
    // else taxable is not on
    } else {
        $taxable_checked = '';
    }

    // if shippable is on
    if ($shippable == 1) {
        $shippable_checked = 'checked="checked"';
    // else shippable is not on
    } else {
        $shippable_checked = '';
    }

    $container_required_checked = '';

    if ($container_required) {
        $container_required_checked = ' checked="checked"';
    }

    // if free shipping is on
    if ($free_shipping == 1) {
        $free_shipping_checked = 'checked="checked"';
    // else free shipping is not on
    } else {
        $free_shipping_checked = '';
    }

    // if shipping is not on, hide shippable row
    if (ECOMMERCE_SHIPPING == false) {
        $shippable_row_class = 'd-none';
    }
   
    $zones = array();

    // get all zones for zones selection
    $query = "SELECT id, name FROM zones ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $zones[] = array('id'=>$row['id'], 'name'=>$row['name']);
    }
    
    $allowed_zones = array();
    $disallowed_zones = array();

    // foreach zone, check if zone is allowed or disallowed for this product
    foreach ($zones as $key => $value) {
        $query = "SELECT zone_id FROM products_zones_xref WHERE product_id = '" . escape($_GET['id']) . "' AND zone_id = '" . $zones[$key]['id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if product and zone were found
        if (mysqli_num_rows($result)) {
            $allowed_zones[] = $zones[$key];
        } else {
            $disallowed_zones[] = $zones[$key];
        }
    }
    $output_allowed_zones = '';
    // foreach allowed zone prepare option
    foreach ($allowed_zones as $key => $value) {
        $output_allowed_zones .= '<option selected="selected" value="' . $allowed_zones[$key]['id'] . '">' . h($allowed_zones[$key]['name']) . '</option>';
    }

    // foreach disallowed zone prepare option
    foreach ($disallowed_zones as $key => $value) {
        $output_allowed_zones .= '<option value="' . $disallowed_zones[$key]['id'] . '">' . h($disallowed_zones[$key]['name']) . '</option>';
    }
    
    // if the affiliate program is enabled, prepare affiliate program output
    if (AFFILIATE_PROGRAM == true) {
        if ($commissionable == 1) {
            $commissionable_checked = 'checked="checked"';
        } else {
            $commissionable_checked = '';
        }
        
        // clear affiliate commission rate if it is 0
        if ($commission_rate_limit == 0) {
            $commission_rate_limit = '';
        }
        $output_commissionable =
            '<div class="col-12 my-1">
                <div class="form-check form-switch">
                    <input value="1" ' . $commissionable_checked . ' id="commissionable" name="commissionable" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#commission_rate_limit_row" />
                    <label class="form-check-label" for="commissionable">' . lang('Commissionable') . '</label>
                </div>
                <div class="collapse border-start border-3 border-secondary-subtle ps-3 ms-2 mb-2" id="commission_rate_limit_row">
                    <div class="row">
                        <div class="col-12 my-2">
                            <label for="commission_rate_limit" class="form-label">' . lang('Commission Rate Limit') . '</label>
                            <div class="input-group">
                                <input value="' . $commission_rate_limit . '" type="text" name="commission_rate_limit" id="commission_rate_limit" class="form-control" size="3" maxlength="3" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-placeholder="0"  style="text-align: right;" />
                                <label for="commission_rate_limit"  class="input-group-text">%</label>
                            </div>
                            <div class="form-text text-end">(' . lang('leave blank for no limit') . ')</div>
                        </div>
                    </div>
                </div>
            </div>';
    }
    
    // assume that recurring check box should not be checked until we find out otherwise
    $recurring_checked = '';
    // if recurring is on, then show recurring payment options
    if ($recurring == 1) {
        $recurring_checked = ' checked="checked"';
    }
    
    // assume that the recurring_schedule_editable_by_customer check box should not be checked until we find out otherwise
    $recurring_schedule_editable_by_customer_checked = '';
    
    // if recurring_schedule_editable_by_customer is on, then check check box
    if ($recurring_schedule_editable_by_customer == 1) {
        $recurring_schedule_editable_by_customer_checked = ' checked="checked"';
    }
    
    // determine if start row should be outputted
    $output_start_row = '';
    
    // if payment gateway is not ClearCommerce, then prepare to output start row
    if (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce') {
        $output_start_row =
        '<div class="col-12 col-sm-6 col-lg-4 my-1">
            <label for="start" class="form-label">' . lang('Start (days)') . '</label>
            <div class="input-group">
                <input type="text" name="start" id="start" class="form-control" value="' . $start . '" size="7" maxlength="7" inputmode="numeric" data-inputmask-alias="decimal"  style="text-align: right;" />
                <span class="input-group-text" title="' . lang('day(s) from order date') . '">' . lang('day(s)') . '</span>
            </div>
            <div class="form-text text-end">' . lang('0 to start immediately') . '</div>
        </div>';
    }
    
    // set number of payments to empty string if value is 0
    if ($number_of_payments == 0) {
        $number_of_payments = '';
    }
    
    // determine if recurring profile disabled rows should be outputted
    $output_recurring_profile_disabled_rows = '';
    
    // if credit/debit card payment method is enabled and payment gateway is PayPal Payments Pro, then prepare to output recurring profile disabled rows
    if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && (ECOMMERCE_PAYMENT_GATEWAY == 'PayPal Payments Pro')) {
        $recurring_profile_disabled_perform_actions_checked = '';
        
        // if perform actions is on, then check check box
        if ($recurring_profile_disabled_perform_actions == 1) {
            $recurring_profile_disabled_perform_actions_checked = ' checked="checked"';
        }
        
        $recurring_profile_disabled_expire_membership_checked = '';
        
        // if expire membership is on, then check check box
        if ($recurring_profile_disabled_expire_membership == 1) {
            $recurring_profile_disabled_expire_membership_checked = ' checked="checked"';
        }
        
        $recurring_profile_disabled_revoke_private_access_checked = '';
        
        // if revoke private access is on, then check check box
        if ($recurring_profile_disabled_revoke_private_access == 1) {
            $recurring_profile_disabled_revoke_private_access_checked = ' checked="checked"';
        }
        
        $recurring_profile_disabled_email_checked = '';
        
        // if e-mail is on, then check check box
        if ($recurring_profile_disabled_email == 1) {
            $recurring_profile_disabled_email_checked = ' checked="checked"';
        }
        $output_recurring_profile_disabled_rows =
            '<div class="col-12 my-3">
                <div class="form-check form-switch">
                    <input value="1"' . $recurring_profile_disabled_perform_actions_checked . ' id="recurring_profile_disabled_perform_actions" name="recurring_profile_disabled_perform_actions" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#recurring_profile_disabled_perform_actions_row"/>
                    <label class="form-check-label" for="recurring_profile_disabled_perform_actions">' . lang('Perform action(s) if profile is disabled') . '</label>
                </div>
                <div class="collapse border-start border-3 border-secondary-subtle ps-3 ms-2 w-100" id="recurring_profile_disabled_perform_actions_row">
                    <div class="row">
                        <div class="alert alert-warning">' . lang('requires recurring payment job') . '</div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input value="1"' . $recurring_profile_disabled_expire_membership_checked . ' id="recurring_profile_disabled_expire_membership" name="recurring_profile_disabled_expire_membership" class="form-check-input" type="checkbox" role="switch"/>
                                <label class="form-check-label" for="recurring_profile_disabled_expire_membership">' . lang('Expire Membership') . '</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input value="1"' . $recurring_profile_disabled_revoke_private_access_checked . ' id="recurring_profile_disabled_revoke_private_access" name="recurring_profile_disabled_revoke_private_access" class="form-check-input" type="checkbox" role="switch"/>
                                <label class="form-check-label" for="recurring_profile_disabled_revoke_private_access">' . lang('Revoke Private Access') . '</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input value="1"' . $recurring_profile_disabled_email_checked . ' id="recurring_profile_disabled_email" name="recurring_profile_disabled_email" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#recurring_profile_disabled_email_row"/>
                                <label class="form-check-label" for="recurring_profile_disabled_email">' . lang('Send E-mail to Customer') . '</label>
                            </div>
                        </div>
                        <div class="collapse border-start border-3 border-secondary-subtle ps-3 ms-2 w-100" id="recurring_profile_disabled_email_row">
                            <div class="row">
                                <div class="col-12 col-sm-6 my-1">
                                    <label for="recurring_profile_disabled_email_subject" class="form-label">' . lang('Subject') . '</label>
                                    <input type="text" value="' . h($recurring_profile_disabled_email_subject) . '" name="recurring_profile_disabled_email_subject" id="recurring_profile_disabled_email_subject" class="form-control mb-2" maxlength="255"/>
                                </div>
                                <div class="col-12 col-sm-6 my-1">
                                    <label for="recurring_profile_disabled_email_page_id" class="form-label">' . lang('Page') . '</label>
                                    <select name="recurring_profile_disabled_email_page_id" id="recurring_profile_disabled_email_page_id" class="form-select"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Page')) )) . '-</option>' .  select_page($recurring_profile_disabled_email_page_id) . '</select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }
    
    // determine if Sage group ID row should be outputted
    $output_sage_group_id_row = '';
    
    // if credit/debit card payment method is enabled and payment gateway is Sage, then output Sage group ID row
    if ((ECOMMERCE_CREDIT_DEBIT_CARD == TRUE) && (ECOMMERCE_PAYMENT_GATEWAY == 'Sage')) {
        $output_sage_group_id_row =
        '<div class="col-12 col-sm-6 col-md-4 my-2">
            <label for="sage_group_id" class="form-label">Sage Group ID</label>
                <input type="text" value="' . $sage_group_id . '" name="sage_group_id" id="sage_group_id" class="form-control" size="7" maxlength="9"/>
        </div>';
    }

    if ($minimum_quantity == 0) {
        $minimum_quantity = '';
    }

    if ($maximum_quantity == 0) {
        $maximum_quantity = '';
    }

    // Get product images from xref.
    $query = "SELECT product,file_name FROM products_images_xref WHERE product = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $xref_image_names = '';
    $output_xref_image_names ='';
    if (mysqli_num_rows($result) != 0){
        $xref_image_names = array();

        while ($row = mysqli_fetch_assoc($result)){
            $xref_image_names[]= $row['file_name'];
        }
        foreach($xref_image_names as $xref_image_name) {
            $output_xref_images .= '
            <div class="item col">
                <div class="card bg-transparent border-0 shadow-none cursor-pointer image">
                    <div class="card-header d-flex justify-content-end p-1 border-0 bg-transparent"><button type="button"  class="btn btn-link link-danger bi bi-x-lg p-0" title="remove" onclick=" $(this).closest(\'.item\').remove();"></button></div>
                    <div class="card-body overflow-hidden position-relative rounded ratio ratio-2x1 w-100" style="--bs-aspect-ratio: 80%;background: radial-gradient(transparent, #00000024);" title="' . $xref_image_name . '">
                        <input type="hidden" name="selected_images[]" value="' . $xref_image_name . '"/>
                        <img class="lazy object-fit-contain w-100 h-100"  src="' . OUTPUT_PATH . SOFTWARE_DIRECTORY . '/assets/images/loading.gif" data-src="' . PATH . $xref_image_name . '" />
                    </div>
                </div>
            </div>';
        }
    }

    $output_thumbnail ='';
    if($image_name == true){
        $output_thumbnail ='<div class="col-12 col-md-auto"><a href="' . OUTPUT_PATH . $image_name . '" target="_blank"><img style="width: 100px;height:100px;" class="img-fluid img-thumbnail lazy" src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/assets/images/loading.gif" data-src="' . OUTPUT_PATH . $image_name . '" /></a></div>';
        $output_selected_image = '
        <div class="item col">
            <div class="card bg-transparent border-0 shadow-none cursor-pointer image">
                <div class="card-header d-flex justify-content-end p-1 border-0 bg-transparent"><button type="button"  class="btn btn-link link-danger bi bi-x-lg p-0" title="remove" onclick=" $(this).closest(\'.item\').remove();"></button></div>
                <div class="card-body overflow-hidden position-relative rounded ratio ratio-2x1 w-100" style="--bs-aspect-ratio: 80%;background: radial-gradient(transparent, #00000024);" title="' . $image_name . '">
                    <input type="hidden" name="selected_images[]" value="' . $image_name . '"/>
                    <img class="lazy object-fit-contain w-100 h-100"  src="' . OUTPUT_PATH . SOFTWARE_DIRECTORY . '/assets/images/loading.gif" data-src="' . PATH . $image_name . '" />
                </div>
            </div>
        </div>';
    }

    $output_selected_images =  $output_selected_image . $output_xref_images;

    // ── Barcode card ─────────────────────────────────────────────────────
    $output_barcode_card = '';
    if (defined('BARCODE_ENABLED') && BARCODE_ENABLED) {
        $bc_row = db_item(
            "SELECT barcode, barcode_type FROM product_barcodes WHERE product_id = '" . e($_GET['id']) . "' LIMIT 1");
        $bc_value = $bc_row['barcode']      ?? '';
        $bc_type  = $bc_row['barcode_type'] ?? BARCODE_DEFAULT_TYPE;

        // Attributes for label preview
        $attr_labels = array();
        $attr_rows = db_items(
            "SELECT product_attribute_options.label
             FROM products_attributes_xref
             JOIN product_attribute_options
               ON products_attributes_xref.option_id = product_attribute_options.id
             WHERE products_attributes_xref.product_id = '" . e($_GET['id']) . "'");
        foreach ($attr_rows as $ar) { $attr_labels[] = $ar['label']; }
        $bc_attributes = implode(', ', $attr_labels);

        $output_barcode_card =
            '<div class="col-12 col-md-auto ms-md-auto" id="pg-barcode-card" style="min-width:220px;max-width:270px;">' .
                '<div class="card border-0 shadow-sm h-100">' .
                    // Header: Edit Template (left) + Type select (right)
                    '<div class="card-header bg-transparent border-bottom py-2 d-flex align-items-center justify-content-between gap-1">' .
                        '<button type="button" class="btn btn-link btn-sm p-0 text-secondary text-nowrap" title="' . lang('Edit Template') . '" onclick="editBarcodeTemplate(window._pgBarcodeOpts)">' .
                            '<i class="bi bi-pencil-square me-1"></i><small>' . lang('Edit Template') . '</small></button>' .
                        '<select id="pg-barcode-type" class="form-select form-select-sm" style="max-width:105px;">' .
                            '<option value="CODE128"' . ($bc_type === 'CODE128' ? ' selected' : '') . '>Code 128</option>' .
                            '<option value="EAN13"'   . ($bc_type === 'EAN13'   ? ' selected' : '') . '>EAN-13</option>' .
                            '<option value="CODE39"'  . ($bc_type === 'CODE39'  ? ' selected' : '') . '>Code 39</option>' .
                            '<option value="UPC"'     . ($bc_type === 'UPC'     ? ' selected' : '') . '>UPC-A</option>' .
                        '</select>' .
                    '</div>' .
                    '<div class="card-body p-2">' .
                        // Barcode SVG preview
                        '<div class="text-center mb-2" style="min-height:55px;">' .
                            '<svg id="pg-barcode-svg" style="max-width:100%;height:55px;"></svg>' .
                        '</div>' .
                        // Input group: pencil icon (left) + barcode input + save icon (right)
                        '<div class="input-group input-group-sm mb-1">' .
                            '<span class="input-group-text"><i class="bi bi-pencil"></i></span>' .
                            '<input type="text" id="pg-barcode-input" class="form-control font-monospace" ' .
                                'value="' . h($bc_value) . '" placeholder="' . lang('Barcode value') . '" maxlength="100" />' .
                            '<button type="button" id="pg-btn-save-barcode" class="btn btn-outline-primary" title="' . lang('Save') . '">' .
                                '<i class="bi bi-floppy"></i></button>' .
                        '</div>' .
                        '<div id="pg-barcode-status" class="form-text" style="min-height:1.2em;"></div>' .
                        // Bottom row: Generate | List | Print
                        '<div class="d-flex gap-1 mt-2">' .
                            '<button type="button" id="pg-btn-generate" class="btn btn-outline-secondary btn-sm flex-fill" title="' . lang('Generate') . '">' .
                                '<i class="bi bi-magic me-1"></i>' . lang('Generate') . '</button>' .
                            '<button type="button" id="pg-btn-barcode-list" class="btn btn-outline-secondary btn-sm position-relative" title="' . lang('Barcodes') . '">' .
                                '<i class="bi bi-list-ul"></i>' .
                                '<span id="pg-barcode-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:9px;display:none;"></span>' .
                            '</button>' .
                            '<button type="button" id="pg-btn-print-barcode" class="btn btn-outline-success btn-sm" title="' . lang('Print') . '">' .
                                '<i class="bi bi-printer"></i></button>' .
                        '</div>' .
                    '</div>' .
                '</div>' .
                // Inline JS init data for this product
                '<script>
                window._pgBarcodeOpts = {
                    productId:        ' . (int)$_GET['id'] . ',
                    barcodeValue:     ' . json_encode($bc_value) . ',
                    barcodeType:      ' . json_encode($bc_type)  . ',
                    shortDescription: ' . json_encode($short_description) . ',
                    sku:              ' . json_encode($name) . ',
                    price:            ' . json_encode(number_format($row['price'] / 100, 2, '.', ',')) . ',
                    attributes:       ' . json_encode($bc_attributes) . ',
                    labelTemplate:    ' . json_encode(BARCODE_LABEL_TEMPLATE) . ',
                    productImageSrc:  ' . json_encode($image_name ? OUTPUT_PATH . $image_name : '') . ',
                    apiToken:         ' . json_encode($_SESSION['software']['token'] ?? '') . '
                };
                </script>' .
            '</div>';
    }

    $output_attributes = '';

    // Get product attributes.
    $attributes = db_items(
        "SELECT
            id,
            name
        FROM product_attributes
        ORDER BY name", 'id');

    // If there are attributes, then get options, selected attributes, and output attribute area.
    if ($attributes) {
        $attribute_options = db_items(
            "SELECT
                id,
                product_attribute_id,
                label
            FROM product_attribute_options
            ORDER BY
                product_attribute_id,
                sort_order");

        // Loop through the options in order to add them to the attributes array.
        foreach ($attribute_options as $attribute_option) {
            $attributes[$attribute_option['product_attribute_id']]['options'][] = $attribute_option;
        }
 
        // Get the selected attributes for this product.
        $selected_attributes = db_items(
            "SELECT
                attribute_id,
                option_id
            FROM products_attributes_xref
            WHERE product_id = '" . e($_GET['id']) . "'
            ORDER BY sort_order");

        // We use array_values() below so that the array is treated as an array
        // and not an object in js, in order to maintain order of the attributes.
        $output_attributes =
            '<div class="card my-4">
                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                    ' . lang('Attribute Options') . '
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mt-1 mb-2">
                            <label class="form-label" >' . lang('Product Attributes') . '</label>
                            <div class="attributes">
                                <div class="attribute_list row"></div>
                                <button type="button" class="add_attribute btn btn-primary mt-2"><span class="bi bi-plus-circle me-2"></span>' . lang('Add Attribute') . '</button>
                            </div>
                            <script>
                                init_product_attributes({
                                    attributes: ' . encode_json(array_values($attributes)) . ',
                                    selected_attributes: ' . encode_json($selected_attributes) . ',
                                    labels:{
                                        "Select Attribute":"' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Attribute')) )) . '",
                                        "Select Option":"' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Option')) )) . '",
                                        "Move Up":"' . lang('Move Up') . '",
                                        "Move Down":"' . lang('Move Down') . '",
                                        "Remove":"' . lang('Remove') . '"
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>';
    }

    $output_groups = '';
	
    // Get product groups.
    $groups = db_items(
        "SELECT
            id,
            name,
            image_name
        FROM product_groups
        ORDER BY name", 'id');

    // If there are groups, then get options and output group area.
    if ($groups) {  

        // Get the selected groups for this product.
        $selected_groups = db_items(
        "SELECT
            product_group
        FROM products_groups_xref
        WHERE product = '" . e($_GET['id']) . "'");
    
        $group_options_html = get_product_group_options(0, 0, 0, 0, array(), TRUE);
        $output_groups =
        '<div class="card my-4">
            <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                ' . lang('Catalog Options') . '
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mt-1 mb-2">
                        <label class="form-label" >' . lang('Include product in to selected groups') . '</label>
                        <div class="groups">
                            <div class="group_list row"></div>
                            <button type="button" class="add_group btn btn-primary mt-2"><span class="bi bi-plus-circle me-2"></span>' . lang('Add Group') . '</button>
                        </div>
                        <script>
                            init_product_groups({
                                group_options: ' . json_encode($group_options_html) . ',
                                selected_groups: ' . encode_json($selected_groups) . ',
                                labels:{
                                    "Select Group":"' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Group')) )) . '",
                                    "Remove":"' . lang('Remove') . '"
                                }
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>';
    }
  
    $inventory_checked = '';
    
    // if inventory is enabled, then check check box and show other fields
    if ($inventory == 1) {
        $inventory_checked = ' checked="checked"';
    }
    
    $backorder_checked = '';
    
    // if backorder is enabled, then check check box
    if ($backorder == 1) {
        $backorder_checked = ' checked="checked"';
    }

    $grant_private_access_checked = '';

    // if grant private access is set for the product, then show grant private access options
    if ($grant_private_access == 1) {
        $grant_private_access_checked = 'checked="checked"';
    }

    // If private days is 0, then set value to blank.
    if ($private_days == 0) {
        $private_days = '';
    }

    $gift_card_checked = '';

    // If gift card is enabled, then check check box and show related rows.
    if ($gift_card == 1) {
        $gift_card_checked = ' checked="checked"';
    }


    if ($gift_card_email_format == 'plain_text') {
        $gift_card_email_format_plain_text_checked = ' checked="checked"';
    } else {
        $gift_card_email_format_html_checked = ' checked="checked"';
    }

    $submit_form_checked = '';

    // If submit form is enabled, then check check box and show related rows.
    if ($submit_form == 1) {
        $submit_form_checked = ' checked="checked"';
    }

    if ($submit_form_quantity_type == 'One Form per Quantity') {
        $submit_form_quantity_type_one_form_per_quantity_checked = ' checked="checked"';
        $submit_form_quantity_type_one_form_per_product_checked = '';
        
    } else {
        $submit_form_quantity_type_one_form_per_product_checked = ' checked="checked"';
        $submit_form_quantity_type_one_form_per_quantity_checked = '';
    }

    $submit_form_create_checked = '';

    // If submit form create is enabled, then check check box.
    if ($submit_form_create == 1) {
        $submit_form_create_checked = ' checked="checked"';
    }

    $submit_form_update_checked = '';

    // If submit form update is enabled, then check check box.
    if ($submit_form_update == 1) {
        $submit_form_update_checked = ' checked="checked"';
    }

    // Get submit form fields, in order to prepare JavaScript that will add fields to screen.
    $submit_form_fields = db_items(
        "SELECT
            action,
            form_field_id,
            value
        FROM product_submit_form_fields
        WHERE product_id = '" . escape($_GET['id']) . "'
        ORDER BY id");

    $output_submit_form_create_javascript = '';
    $output_submit_form_update_javascript = '';

    foreach ($submit_form_fields as $submit_form_field) {
        if ($submit_form_field['action'] == 'create') {
            $output_submit_form_create_javascript .= 'product_submit_form_add_field(' . encode_json($submit_form_field) . ');' . "\n";
        } else {
            $output_submit_form_update_javascript .= 'product_submit_form_add_field(' . encode_json($submit_form_field) . ');' . "\n";
        }
    }

    $add_comment_checked = '';

    // If add comment is enabled, then check check box and show related rows.
    if ($add_comment == 1) {
        $add_comment_checked = ' checked="checked"';
    }

    $add_comment_only_for_submit_form_update_checked = '';

    // If only for submit form is enabled, then check check box.
    if ($add_comment_only_for_submit_form_update == 1) {
        $add_comment_only_for_submit_form_update_checked = ' checked="checked"';
    }
    
    // if the product_form checkbox is checked, display rows
    if ($product_form == 1) {
        $product_form_checked = 'checked="checked"';
        $output_product_form_designer_button = '<a class="btn btn-link link-secondary py-0 mb-2" href="view_fields.php?product_id=' . h(urlencode($_REQUEST['id'])) . '"><span class="material-icons me-1">edit_note</span>' . lang('Edit Product Form') . '</a>';
        $product_form_alert_class = 'd-none';
    // else, do not display the rows
    } else {
        $product_form_checked = '';
        $output_product_form_designer_button = '';
        $product_form_alert_class = '';
    }
    
    // if form_quantity_type is set to One Form per Quantity, select it
    if ($form_quantity_type == 'One Form per Quantity') {
        $form_quantity_type_one_form_per_quantity_checked = 'checked="checked"';
        $form_quantity_type_one_form_per_product_checked = '';
        
    // else, form_quantity_type must be set to One Form per Product
    } else {
        $form_quantity_type_one_form_per_product_checked = 'checked="checked"';
        $form_quantity_type_one_form_per_quantity_checked = '';
    }

    $output_custom_product_field_rows = '';

    // If there is at least one active custom product field, then output area for that.
    if (
        (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '')
        || (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '')
        || (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '')
        || (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '')
    ) {  
        $output_custom_product_field_rows .=
            '<div class="col-12">
                <h6 class="text-muted">' . lang('Custom Product Fields') . '</h6>
            </div>
            <div class="col-12 col-sm-6 col-md-4 my-2">';

        // If the first custom product field is active, then output row for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
            $output_custom_product_field_rows .=
                '<label for="custom_field_1" class="form-label">' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL) . '</label>
                <input class="form-control" type="text" id="custom_field_1" value="' . h($custom_field_1) . '" name="custom_field_1" maxlength="255" />';
        }

        // If the second custom product field is active, then output row for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
            $output_custom_product_field_rows .=
            '<label for="custom_field_2" class="form-label">' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL) . '</label>
            <input class="form-control" type="text" id="custom_field_2" value="' . h($custom_field_2) . '" name="custom_field_2" maxlength="255" />';
        }

        // If the third custom product field is active, then output row for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
            $output_custom_product_field_rows .=
            '<label for="custom_field_3" class="form-label">' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL) . '</label>
            <input class="form-control" type="text" id="custom_field_3" value="' . h($custom_field_3) . '" name="custom_field_3" maxlength="255" />';
        }

        // If the fourth custom product field is active, then output row for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
            $output_custom_product_field_rows .=
            '<label for="custom_field_4" class="form-label">' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL) . '</label>
            <input class="form-control" type="text" id="custom_field_4" value="' . h($custom_field_4) . '" name="custom_field_4" maxlength="255" />';
        }
        $output_custom_product_field_rows .='</div>';
    }

    print
    pg_page_shell([
        'title'=> lang('Edit Product'),
        'extra classes'=>'products',
        'icon'=>'store',
        'heading'=>lang('Edit Product'),
        'cancel'=>array('enable'=>'true','url'=>'view_products.php'),
        'breadcrumb' => array(
            array('label' => lang('All Products'), 'url' => OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_products.php'),
            array('label' => lang('Edit Product')),
        ),
        'head' => (defined('BARCODE_ENABLED') && BARCODE_ENABLED ?
            '<script src="assets/jsbarcode/JsBarcode.all.min.js"></script>' : '')
    ]) . '
            ' . get_wysiwyg_editor_code(array('order_receipt_message', 'full_description', 'details', 'out_of_stock_message')) . '
        <div class="row">
            <div class="col-12">
                ' . $liveform->output_errors() . '
                ' . $liveform->get_warnings() . '
                ' . $liveform->output_notices() . '
                <div class="row mb-2  flex-wrap">
                    <div class="col-12 col-sm-12 text-center text-md-start">
                        <div class="row mb-2 align-items-start">
                            ' . $output_thumbnail . '
                            <div class="col-12 col-md">
                                <h2 class="d-inline-block text-break header-content-for-add-page" data-bs-content="' . lang('Edit shippable product, downloadable product, donation, recurring fee, membership dues, or payment.') . '" title="' . lang('Edit Product') . '">[' . h($short_description) . ']</h2>
                                <p class="p-0 m-0">' . lang('Product ID / SKU') . ': ' . h($name) . '</p>
                            </div>
                            ' . $output_barcode_card . '
                        </div>
                        <nav id="button_bar" class="navigation " aria-label="Button Bar">
                            <div class=" btn-group btn-group-sm flex-wrap">
                                ' . $output_product_form_designer_button . '
                                <a class="btn btn-link link-secondary py-0 mb-2 " data-loading-content="' . lang('Duplicating') . '" href="duplicate_product.php?id=' . h($_GET['id']) . get_token_query_string_field() . '"><span class="material-icons me-1">control_point_duplicate</span>' . lang('Duplicate') . '</a>
                            </div>
                        </nav>
                        
                    </div>
                </div>
                <form name="form" action="edit_product.php" method="post" class="product_form">
                    ' . get_token_field() . '
                    <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                    <div class="row">
                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Main Informations') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-sm-4 my-2">
                                            <label for="name" class="form-label">' . lang('Product ID / SKU') . '</label>
                                            <input value="' . h($name) . '" type="text" name="name" id="name" class="form-control"/>
                                        </div>
                                        <div class="col-12 col-sm-8 my-2">
                                            <label for="short_description" class="form-label">' . lang('Short Description') . '</label>
                                            <input value="' . h($short_description) . '" type="text" name="short_description" placeholder="' . lang('Product Name') . '" id="short_description" class="form-control add-header-content-updater" />
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-4 my-2">
                                            <label for="price" class="form-label">' . lang('Unit Price') . '</label>
                                            <div class="input-group">
                                                <input value="' . $price . '" type="text" name="price" id="price" class="form-control" maxlength="12" inputmode="numeric" data-inputmask-alias="currency" data-inputmask-groupSeparator="," data-inputmask-digits="2" data-inputmask-digitsOptional="false" data-inputmask-placeholder="0"  style="text-align: right;" />
                                                <label class="input-group-text" for="price">' . BASE_CURRENCY_SYMBOL . '</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-6 my-2">
                                            <div class="row">
                                                <div class="col-12 col-sm-12 col-lg-8 offset-lg-8">
                                                    <label for="selection_type" class="form-label">' . lang('Selection Type') . '</label>
                                                    <select name="selection_type" id="selection_type" class="form-select"  >' .  select_selection_type($selection_type) . '</select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-4 my-2">
                                            <label for="default_quantity" class="form-label">' . lang('Default Quantity') . '</label>
                                            <div class="input-group number-controls">
                                                <button class="btn material-icons minus border border-end-0" type="button">remove</button>
                                                <input value="' . $default_quantity . '" class="form-control text-center border-start-0 border-end-0" type="text" name="default_quantity" id="default_quantity" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-placeholder="0"/>
                                                <button class="btn material-icons plus border border-start-0" type="button">add</button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-4 my-2">
                                            <label for="minimum_quantity" class="form-label">' . lang('Min. Quantity') . '</label>
                                            <div class="input-group number-controls">
                                                <button class="btn material-icons minus border border-end-0" type="button">remove</button>
                                                <input value="' . $minimum_quantity . '" class="form-control text-center border-start-0 border-end-0" type="text" name="minimum_quantity" id="minimum_quantity" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-placeholder="0"/>
                                                <button class="btn material-icons plus border border-start-0" type="button">add</button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-4 my-2">
                                            <label for="maximum_quantity" class="form-label">' . lang('Max. Quantity') . '</label>
                                            <div class="input-group number-controls">
                                                <button class="btn material-icons minus border border-end-0" type="button">remove</button>
                                                <input value="' . $maximum_quantity . '" class="form-control text-center border-start-0 border-end-0" type="text" name="maximum_quantity" id="maximum_quantity" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal"  data-inputmask-placeholder="0"/>
                                                <button class="btn material-icons plus border border-start-0" type="button">add</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>       
                            </div> 
                        </div>
                        <div class="col-12">          
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Product Options') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input value="1"' . $output_enabled_checked . ' class="form-check-input" type="checkbox" id="enabled" name="enabled"/>
                                                <label class="form-check-label" for="enabled">' . lang('Enabled') . '</label>
                                            </div>
                                        </div>
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input  value="1" ' . $taxable_checked . ' class="form-check-input" type="checkbox" id="taxable" name="taxable"/>
                                                <label class="form-check-label" for="taxable">' . lang('Taxable') . '</label>
                                            </div>
                                        </div>
                                        ' . $output_commissionable . '
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input value="1" ' . $recurring_checked . ' id="recurring" name="recurring" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#recurring_schedule_editable_by_customer_row" />
                                                <label class="form-check-label" for="recurring">' . lang('Recurring Payment') . '</label>
                                            </div>
                                            <div class="collapse popover  fade bs-popover-bottom p-0 mb-2" id="recurring_schedule_editable_by_customer_row">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 my-1">
                                                            <div class="form-check form-switch">
                                                                <input value="1" ' . $recurring_schedule_editable_by_customer_checked . ' id="recurring_schedule_editable_by_customer" name="recurring_schedule_editable_by_customer" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#recurring_schedule_editable_by_customer_message"/>
                                                                <label class="form-check-label" for="recurring_schedule_editable_by_customer">' . lang('Allow customer to set schedule') . '</label>
                                                            </div>
                                                            <div class="collapse alert alert-primary" id="recurring_schedule_editable_by_customer_message">' . lang('You may select default values for the schedule below') . '</div>
                                                        </div>
                                                        ' . $output_start_row . '
                                                        <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                            <label for="number_of_payments" class="form-label">' . lang('Number of Payments') . '</label>
                                                                <input type="text" value="' . $number_of_payments . '" name="number_of_payments" id="number_of_payments" class="form-control" size="7" maxlength="7" inputmode="numeric" data-inputmask-alias="decimal"  style="text-align: left;" />
                                                            <div class="form-text text-end">' . get_number_of_payments_message() . '</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                            <label for="payment_period" class="form-label">' . lang('Payment Period') . '</label>
                                                            <select name="payment_period" id="payment_period" class="form-select">' .  select_payment_period($payment_period) . '</select>
                                                        </div>
                                                        ' . $output_recurring_profile_disabled_rows . '
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input value="1" ' . $inventory_checked . ' name="inventory" id="inventory" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#collapse_inventory">
                                                <label class="form-check-label" for="inventory">' . lang('Track Inventory') . '</label>
                                            </div>
                                            <div class="collapse popover  fade bs-popover-bottom p-0 mb-2" id="collapse_inventory">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 col-md-6 col-lg-4 my-1">
                                                            <label for="inventory_quantity" class="form-label ">' . lang('Inventory Quantity') . '</label>
                                                            <div class="input-group number-controls">
                                                                <button class="btn material-icons minus border border-end-0" type="button">remove</button>
                                                                <input value="' . h($inventory_quantity) . '" class="form-control text-center border-start-0 border-end-0" type="text" name="inventory_quantity" id="inventory_quantity" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal"  data-inputmask-placeholder="0"/>
                                                                <button class="btn material-icons plus border border-start-0" type="button">add</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 my-3">
                                                            <div class="form-check form-switch">
                                                              <input value="1" ' . $backorder_checked . ' name="backorder" id="backorder" class="form-check-input" type="checkbox" role="switch" />
                                                              <label class="form-check-label" for="backorder">' . lang('Accept Backorders') . '</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 my-1">
                                                            <label for="out_of_stock_message" class="form-label">' . lang('Out of Stock Message') . '</label>
                                                            <textarea id="out_of_stock_message" name="out_of_stock_message">' . h(prepare_rich_text_editor_content_for_output($out_of_stock_message)) . '</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch ' . $shippable_row_class . '">
                                                <input value="1"' . $shippable_checked . ' id="shippable" name="shippable" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#shippable_row" />
                                                <label class="form-check-label" for="shippable">' . lang('Shippable') . '</label>
                                            </div>
                                            <div class="collapse popover   fade bs-popover-bottom p-0 mb-2" id="shippable_row">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 justify-content-center d-flex flex-wrap my-1">
                                                            <div class="form-check form-switch">
                                                              <input value="1" name="convert_to_metric_system" id="convert_to_metric_system" class="form-check-input" type="checkbox" role="switch" data-bs-target="#weight,#length,#width,#height" />
                                                              <label class="form-label" for="convert_to_metric_system">' . lang('Convert to metric system') . '</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12  col-lg-4 my-1">
                                                            <label for="weight" class="form-label">' . lang('Weight') . '</label>
                                                            <div class="input-group">
                                                                <input value="' . $weight . '" type="text" name="weight" id="weight" class="form-control" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="8" data-inputmask-digitsOptional="true" data-inputmask-placeholder="0"  style="text-align: right;" />
                                                                <label class="input-group-text unit" for="weight">lbs</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-sm-6  col-lg-4 my-1">
                                                            <label for="primary_weight_points" class="form-label">' . lang('Primary Weight Points') . '</label>
                                                            <input value="' . $primary_weight_points . '" type="text" name="primary_weight_points" id="primary_weight_points" class="form-control" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="1" data-inputmask-digitsOptional="false" data-inputmask-placeholder="0"  style="text-align: right;" />
                                                        </div>
                                                        <div class="col-12 col-sm-6  col-lg-4 my-1">
                                                            <label for="secondary_weight_points" class="form-label">' . lang('Secondary Weight Points') . '</label>
                                                            <input value="' . $secondary_weight_points . '" type="text" name="secondary_weight_points" id="secondary_weight_points" class="form-control" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="1" data-inputmask-digitsOptional="false" data-inputmask-placeholder="0"  style="text-align: right;" />
                                                        </div>
                                                        <div class="col-12 my-3">
                                                            
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <h6 class="text-muted">' . lang('Dimentions') . '</h6>
                                                                </div>
                                                                <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                                    <label class="form-label" for="length">' . lang('Length') . '</label>
                                                                    <div class="input-group my-1">
                                                                        <input value="' . $length . '" type="text" name="length" id="length" class="form-control"  inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="8" data-inputmask-digitsOptional="true"  data-inputmask-placeholder="0"  style="text-align: right;" />
                                                                        <label class="input-group-text unit" for="length">inc</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                                    <label class="form-label" for="width">' . lang('Width') . '</label>
                                                                    <div class="input-group my-1">
                                                                        <input value="' . $width . '" type="text" name="width" id="width" class="form-control" maxlength="12" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="8" data-inputmask-digitsOptional="true"  data-inputmask-placeholder="0"  style="text-align: right;" />
                                                                        <label class="input-group-text unit" for="width">inc</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                                    <label class="form-label" for="height">' . lang('Height') . '</label>
                                                                    <div class="input-group my-1">
                                                                        <input value="' . $height . '" type="text" name="height" id="height" class="form-control" maxlength="12" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="8" data-inputmask-digitsOptional="true"  data-inputmask-placeholder="0"  style="text-align: right;" />
                                                                        <label class="input-group-text unit" for="height">inc</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6 col-lg-4 my-1">
                                                            <label for="preparation_time" class="form-label">' . lang('Preparation Time') . '</label>
                                                            <div class="input-group">
                                                                <input value="' . $preparation_time . '" type="text"  name="preparation_time" id="preparation_time" class="form-control" size="7" maxlength="7" inputmode="numeric" data-inputmask-alias="decimal"  style="text-align: right;" />
                                                                <span class="input-group-text" title="' . lang('day(s) from order date') . '">' . lang('day(s)') . '</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 my-2">
                                                            <div class="form-check form-switch">
                                                                <input value="1" ' . $container_required_checked . ' class="form-check-input" type="checkbox" id="container_required" name="container_required" />
                                                                <label class="form-check-label" for="container_required">' . lang('Container Required') . '</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 my-2">
                                                            <div class="form-check form-switch">
                                                                <input value="1" ' . $free_shipping_checked . ' class="form-check-input collapse-switcher" type="checkbox" id="free_shipping" name="free_shipping" data-bs-target="#free_shipping_row" />
                                                                <label class="form-check-label" for="free_shipping">' . lang('Free Shipping') . '</label>
                                                            </div>
                                                        </div>
                                                        <div class="collapse popover fade bs-popover-bottom p-0 mb-2 show-reverse" id="free_shipping_row">
                                                            <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                            <div class="popover-body">
                                                                <div class="row">
                                                                    <div class="col-12 my-2">
                                                                        <label for="extra_shipping_cost" class="form-label">' . lang('Extra Shipping Cost') . '</label>
                                                                        <div class="input-group">
                                                                            <input value="' . $extra_shipping_cost . '" type="text" name="extra_shipping_cost" id="extra_shipping_cost" class="form-control" maxlength="12" inputmode="numeric" data-inputmask-alias="currency" data-inputmask-groupSeparator="," data-inputmask-digits="2" data-inputmask-digitsOptional="false" data-inputmask-placeholder="0"  style="text-align: right;" />
                                                                            <label class="input-group-text" for="extra_shipping_cost">' . BASE_CURRENCY_SYMBOL . '</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 my-1">
                                                            <label for="allowed_zones" class="form-label">' . lang('Allowed Zones') . '</label>
                                                            <select style="width:100%" class="select2 form-select" data-placeholder="' . lang('Click to select shipping zone(s)') . '" id="allowed_zones" name="allowed_zones[]" multiple="multiple">' . $output_allowed_zones . '</select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </div>
                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Image Options') . ' 
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mt-3">
                                            <div id="software_image_picker_container" ondblclick="software_image_picker({initialize:true});" class="user-select-none sortable-list img-list bg-body-tertiary rounded p-2 row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">' . $output_selected_images . '</div>
                                            <button type="button" class="btn btn-primary my-3 me-2" onclick="software_image_picker({initialize:true});" ><span class="bi bi-plus-circle me-2"></span>' . lang('Add Image') . '</button>
                                            <button type="button" class="btn " data-bs-toggle="modal" data-bs-target="#image_code"><span class="material-icons me-2">code</span>' . lang('Code') . '</button>

                                            <div class="modal fade" id="image_code" tabindex="-1" aria-labelledby="image_code" aria-hidden="true">
                                                <div class="modal-dialog modal-lg ">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">' . lang('Code') . '</h5>
                                                            <button type="button" title="' . lang('Close') . '" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body "> 
                                                            <div class="row">
                                                                <div class="col-12 my-2">
                                                                <div class="alert alert-primary">' . lang('Tags') . ':<span title="' . lang('Loop start') . '">^^image_loop_start^^</span>, <span title="' . lang('Short Description') . '">^^image_alt^^</span>, <span title="' . lang('Image Url') . '">^^image_url^^</span>, <span title="' . lang('Loop End') . '">^^image_loop_end^^</span></div>
                                                                </div>
                                                                <div class="col-12 my-2">
                                                                    <textarea id="code" name="code">' . h($code) . '</textarea>
                                                                    ' . get_codemirror_includes() . '
                                                                    ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'mixed')) . '
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <script>
                                                $(document).ready(function() {
                                                    $(".sortable-list").sortable({
                                                        items: "> div:not(.add_new_item)",
                                                        placeholder: "col",
                                                        handle: ".card .card-body",
                                                        revert: "100",
                                                        cursorAt: { left: 1 },
                                                        animation: 150,
                                                        forcePlaceholderSize: false,
                                                        forceHelperSize: true,
                                                        swapThreshold: 1,
                                                        tolerance: "pointer",
                                                        zIndex: 9999,
                                                        cursor: "move",
                                                        cancel: ".no-drag"
                                                    });
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 ">
                        ' . $output_attributes . '
                        </div>
                        <div class="col-12 col-md-6 ">
                        ' . $output_groups . '
                        </div>
                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Detail Informations') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="full_description" class="form-label">' . lang('Full Description') . '</label>
                                            <textarea id="full_description" name="full_description">' . h(prepare_rich_text_editor_content_for_output($full_description)) . '</textarea>
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="details" class="form-label">' . lang('Details') . '</label>
                                            <textarea id="details" name="details">' . h(prepare_rich_text_editor_content_for_output($details)) . '</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    '. lang('Checkout Options') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        ' . $output_sage_group_id_row . '
                                        <div class="col-12 col-sm-6 col-md-8 my-2">
                                            <label class="form-label" for="required_product">'. lang('Requires Product') . '</label>
                                            <select class="form-select" id="required_product" name="required_product"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Product')) )) . '-</option>' .  select_product($required_product) . '</select>
                                        </div>
                                        <div class="col-12 my-3">
                                            <div class="form-check form-switch">
                                                <input value="1" ' . $product_form_checked . ' id="product_form" name="product_form" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#product_form_row" />
                                                <input type="hidden" id="original_form_state" name="original_form_state" value="' . h($product_form) . '" />
                                                <input type="hidden" id="current_form_state" name="current_form_state" value="' . h($product_form) . '" />
                                                <script>
                                                    $("input.collapse-switcher#product_form").on("click", function() {
                                                        if(this.checked == true){
                                                            if($("#original_form_state").val() == 0 || $("#original_form_state").length < 1){
                                                                $("#create_button").value= "Save & Continue";
                                                                $("#create_button .btn-text").text("' . lang('Save & Continue') . '");
                                                                $("#current_form_state").val(1);
                                                            }
                                                        }else{
                                                            $("#create_button").val("Save");
                                                            $("#create_button .btn-text").text("' . lang('Save') . '");
                                                            $("#current_form_state").val(0);
                                                        } 
                                                    });
                                                </script>
                                                <label class="form-check-label" for="product_form">'. lang('Enable Product Form') . '</label>
                                            </div>
                                            <div class="collapse popover   fade bs-popover-bottom p-0 mb-2" id="product_form_row">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 my-1">
                                                            <div class="alert alert-primary ' . $product_form_alert_class . '">' . lang(array('string'=>'when ready, click "{var:1}" at the bottom of this screen to create the Product Form.','vars'=>array(lang('Save & Continue')) )) . '</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-8 my-1">
                                                            <label class="form-label" for="form_name">'. lang('Form Title for Display') . '</label>
                                                            <input type="text" value="' . $form_name . '" id="form_name" name="form_name" class="form-control" maxlength="100" >
                                                        </div>

                                                        <div class="col-12 col-sm-6 col-md-4 my-1">
                                                            <label class="form-label" for="form_label_column_width">'. lang('Label Column Width') . '</label>
                                                            <div class="input-group">
                                                                <input type="text" value="' . $form_label_column_width . '" id="form_label_column_width" name="form_label_column_width" class="form-control" size="3" maxlength="3" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-placeholder="0"  style="text-align: right;">
                                                                <label class="input-group-text" for="form_label_column_width">%</label>
                                                            </div>
                                                            <div class="form-text text-end">'. lang('leave blank for auto') . '</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4 my-1">
                                                            <label class="form-label" for="">'. lang('Quantity Type') . '</label>
                                                            <div class="form-check">
                                                                <input value="One Form per Quantity" class="form-check-input" type="radio" id="form_quantity_type_one_form_per_quantity" name="form_quantity_type" ' . $form_quantity_type_one_form_per_quantity_checked . '>
                                                                <label class="form-check-label" for="form_quantity_type_one_form_per_quantity">'. lang('One form per quantity') . '</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input value="One Form per Product" class="form-check-input" type="radio" id="form_quantity_type_one_form_per_product" name="form_quantity_type" ' . $form_quantity_type_one_form_per_product_checked . '>
                                                                <label class="form-check-label" for="form_quantity_type_one_form_per_product">'. lang('One form per product') . '</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Order Complete Options') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 my-1">
                                            <label class="form-label" for="order_receipt_message">' . lang('Order Receipt Page Message') . '</label>
                                            <textarea id="order_receipt_message" name="order_receipt_message">' . h(prepare_rich_text_editor_content_for_output($order_receipt_message)) . '</textarea>
                                        </div>
                                        <div class="col-12 col-sm-6 col-xl-4 my-1">
                                            <label class="form-label" for="order_receipt_bcc_email_address">' . lang('Order Receipt BCC E-mail Address') . '</label>
                                            <input type="text" value="' . h($order_receipt_bcc_email_address) . '" class="form-control text-end" id="order_receipt_bcc_email_address" name="order_receipt_bcc_email_address" maxlength="100" inputmode="email" data-inputmask-alias="email"/>
                                        </div>
                                        <div class="col-12 col-sm-6 col-xl-4 my-1">
                                            <label class="form-label" for="email_page">' . lang('E-mail Additional Page to Customer') . '</label>
                                            <select class="form-select" id="email_page" name="email_page"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Page')) )) . '-</option>' .  select_page($email_page) . '</select>
                                        </div>
                                        <div class="col-12 col-sm-6 col-xl-4 my-1">
                                            <label class="form-label" for="email_bcc">' . lang('BCC E-mail Address') . '</label>
                                            <input type="text" value="' . h($email_bcc) . '" class="form-control text-end" id="email_bcc" name="email_bcc" maxlength="100" inputmode="email" data-inputmask-alias="email"/>
                                        </div>
                                        <div class="col-12 col-sm-6 col-xl-4 my-1">
                                            <label class="form-label" for="contact_group_id">' . lang('Add to Contact Group') . '</label>
                                            <select class="form-select" id="contact_group_id" name="contact_group_id"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Contact Group')) )) . '-</option>' . select_contact_group($contact_group_id, $user) . '</select>
                                        </div>
                                        <div class="col-12 col-md-6 col-xl-4 my-1">
                                            <label for="membership_renewal" class="form-label">' . lang('Add Days to Customer\'s Membership') . '</label>
                                            <div class="input-group">
                                                <input type="text" value="' . $membership_renewal . '" name="membership_renewal" id="membership_renewal" class="form-control" size="7" maxlength="7" inputmode="numeric" data-inputmask-alias="decimal"  style="text-align: right;" />
                                                <span class="input-group-text">' . lang('day(s)') . '</span>
                                            </div>
                                            <div class="text-end form-text">' . lang('0 for none') . '</div>
                                        </div>
                                        <div class="col-12 my-3">
                                            <div class="form-check form-switch">
                                                <input value="1" ' . $grant_private_access_checked . ' id="grant_private_access" name="grant_private_access" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#grant_private_access_row" />
                                                <label class="form-check-label" for="grant_private_access">' . lang('Grant Private Access to Customer') . '</label>
                                            </div>
                                            <div class="collapse popover  fade bs-popover-bottom p-0 mb-2" id="grant_private_access_row">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 col-md-6 col-lg-4 my-1">
                                                            <label class="form-label" for="private_folder">' . lang('Set "View" Access to Folder') . '</label>
                                                            <select class="form-select" id="private_folder" name="private_folder"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Folder')) )) . '-</option>' .  select_folder($private_folder, 0, 0, 0, array(), array(), 'private') . '</select>
                                                        </div>
                                                        <div class="col-12 col-md-6 col-lg-4 my-1">
                                                            <label for="private_days" class="form-label">' . lang('Length') . '</label>
                                                            <div class="input-group">
                                                                <input type="text" value="' . $private_days . '" name="private_days" id="private_days" class="form-control" size="7" maxlength="7" inputmode="numeric" data-inputmask-alias="decimal"  style="text-align: right;" />
                                                                <span class="input-group-text">' . lang('day(s)') . '</span>
                                                            </div>
                                                            <div class="text-end form-text">' . lang('leave blank for no expiration') . '</div>
                                                        </div>
                                                        <div class="col-12 col-md-6 col-lg-4 my-1">
                                                            <label class="form-label" for="send_to_page">' . lang('Set Customer\'s Start Page to') . '</label>
                                                            <select class="form-select" id="send_to_page" name="send_to_page"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Page')) )) . '-</option>' .  select_page($send_to_page) . '</select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-4 my-1">
                                            <label for="reward_points" class="form-label">' . lang('Reward Points') . '</label>
                                            <input type="text" value="' . $reward_points . '" name="reward_points" id="reward_points" class="form-control" size="5" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal"  style="text-align: right;" />
                                        </div>
                                        <div class="col-12 mt-3 my-1">
                                            <div class="form-check form-switch">
                                                <input value="1" ' . $gift_card_checked . ' id="gift_card" name="gift_card" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#gift_card_row" />
                                                <label class="form-check-label" for="gift_card">' . lang('Email Gift Card') . '</label>
                                            </div>
                                            <div class="collapse popover  fade bs-popover-bottom p-0 mb-2 w-100" id="gift_card_row">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6 col-md-8 my-1">
                                                            <label class="form-label" for="gift_card_email_subject">' . lang('Subject') . '</label>
                                                            <input type="text" value="' . h($gift_card_email_subject) . '" id="gift_card_email_subject" name="gift_card_email_subject" class="form-control" maxlength="100" >
                                                        </div>
                                                        <div class="col-12 my-1">
                                                            <div class="col-12">
                                                                <label class="form-label">' . lang('Format') . '</label>
                                                            </div>
                                                            <div class="form-check  form-check-inline">
                                                                <input class="form-check-input collapse-switcher" type="radio" id="gift_card_email_format_plain_text" name="gift_card_email_format"  ' . $gift_card_email_format_plain_text_checked . ' value="plain_text" data-bs-target="#gift_card_email_format_plain_text_row" />
                                                                <label for="gift_card_email_format_plain_text">' . lang('Plain Text') . '</label> 
                                                            </div>
                                                            <div class="form-check  form-check-inline">
                                                                <input class="form-check-input collapse-switcher" type="radio" id="gift_card_email_format_html" name="gift_card_email_format" value="html" ' . $gift_card_email_format_html_checked . ' data-bs-target="#gift_card_email_format_html_row"/>
                                                                <label for="gift_card_email_format_html">' . lang('HTML') . '</label>
                                                            </div>
                                                            <div class="collapse popover fade bs-popover-bottom p-0 mb-2 w-100" id="gift_card_email_format_plain_text_row">
                                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(18px, 0px);"></div>
                                                                <div class="popover-body">
                                                                    <div class="row">
                                                                        <div class="col-12 my-1">
                                                                          <label for="gift_card_email_body" class="form-label">' . lang('Body') . '</label>
                                                                          <textarea class="form-control" id="gift_card_email_body" name="gift_card_email_body" rows="3">' . h($gift_card_email_body) . '</textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="collapse popover fade bs-popover-bottom p-0 mb-2" id="gift_card_email_format_html_row">
                                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(111px, 0px);"></div>
                                                                <div class="popover-body">
                                                                    <div class="row">
                                                                        <div class="col-12 col-sm-8 my-1">
                                                                            <label class="form-label" for="gift_card_email_page_id">' . lang('Page') . '</label>
                                                                            <select class="form-select" id="gift_card_email_page_id" name="gift_card_email_page_id"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Page')) )) . '-</option>' . select_page($gift_card_email_page_id) . '</select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input value="1" ' . $submit_form_checked . ' id="submit_form" name="submit_form" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#submit_form_row" />
                                                <label class="form-check-label" for="submit_form">' . lang('Create/Update Submitted Form') . '</label>
                                            </div>
                                            <div class="collapse popover  fade bs-popover-bottom p-0 mb-2" id="submit_form_row">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 my-1">
                                                            <label class="form-label" for="submit_form_custom_form_page_id">' . lang('Custom Form') . '</label>
                                                            <select class="form-select collapse-if-selected" id="submit_form_custom_form_page_id" name="submit_form_custom_form_page_id" onchange="product_submit_form_update_custom_form_fields()" data-bs-target="#submit_form_custom_form_page_row"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Form')) )) . '-</option>' .  select_page($submit_form_custom_form_page_id, 'custom form') . '</select>
                                                            <script>product_submit_form_update_custom_form_fields();</script>
                                                            <div class="collapse popover fade bs-popover-bottom p-0 mb-2 w-100" id="submit_form_custom_form_page_row">
                                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                                <div class="popover-body">
                                                                    <div class="row">
                                                                        <div class="col-12 my-1">
                                                                            <div class="form-check form-switch">
                                                                                <input value="1" ' . $submit_form_create_checked . ' id="submit_form_create" name="submit_form_create" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#submit_form_create_row" />
                                                                                <label class="form-check-label" for="submit_form_create">' . lang('Create Submitted Form') . '</label>
                                                                            </div>
                                                                            <div class="collapse popover  fade bs-popover-bottom p-0 mb-2" id="submit_form_create_row">
                                                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                                                <div class="popover-body">
                                                                                    <div class="row">
                                                                                        <div class="col-12 my-1">
                                                                                            <div class="alert alert-primary">' . lang(array('string'=>'Please configure the fields below that should be set when a Submitted Form is {var:1}.','vars'=>array( lang('created') ) )) . '</div>
                                                                                            <div id="submit_form_create_field"></div>
                                                                                            <button type="button" onclick="product_submit_form_add_field({action: \'create\'})" class="btn btn-sm btn-primary my-3"><span class="bi bi-plus-circle me-2"></span>' . lang('Add Field') . '</button>
                                                                                            <input type="hidden" id="last_submit_form_create_field_number" name="last_submit_form_create_field_number" value="0" />
                                                                                            <script>
                                                                                                var last_submit_form_field_number = [];                            
                                                                                                last_submit_form_field_number["create"] = 0;
                                                                                                ' . $output_submit_form_create_javascript . '
                                                                                            </script>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 my-1">
                                                                            <div class="form-check form-switch">
                                                                                <input value="1" ' . $submit_form_update_checked . ' id="submit_form_update" name="submit_form_update" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#submit_form_update_row" />
                                                                                <label class="form-check-label" for="submit_form_update">' . lang('Update Submitted Form') . '</label>
                                                                            </div>
                                                                            <div class="collapse popover  fade bs-popover-bottom p-0 mb-2" id="submit_form_update_row">
                                                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                                                <div class="popover-body">
                                                                                    <div class="row">
                                                                                        <div class="col-12 my-1">
                                                                                            <div class="alert alert-primary">' . lang(array('string'=>'Please configure the fields below that should be set when a Submitted Form is {var:1}.','vars'=>array( lang('updated') ) )) . '</div>
                                                                                            <div id="submit_form_update_field"></div>
                                                                                            <button type="button" onclick="product_submit_form_add_field({action: \'update\'})" class="btn btn-sm btn-primary my-3"><span class="bi bi-plus-circle me-2"></span>' . lang('Add Field') . '</button>
                                                                                            <input type="hidden" id="last_submit_form_update_field_number" name="last_submit_form_update_field_number" value="0" />
                                                                                            <script>
                                                                                                last_submit_form_field_number["update"] = 0;
                                                                                                ' . $output_submit_form_update_javascript . '
                                                                                            </script>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 my-1">
                                                                            <div class="alert alert-primary">' . lang('Please specify which Submitted Form should be updated.') . '</div>
                                                                            <div class="input-group w-100">
                                                                                <span class="input-group-text">
                                                                                    <label class="form-label" for="submit_form_update_where_field">' . lang('Where:') . '</label>
                                                                                    <select class="form-select" id="submit_form_update_where_field" name="submit_form_update_where_field"></select>
                                                                                </span>
                                                                                <span class="input-group-text">
                                                                                    <label class="form-label" for="submit_form_update_where_value">' . lang('is equal to:') . '</label>
                                                                                    <input class="form-control" type="text" id="submit_form_update_where_value" name="submit_form_update_where_value" value="' . h($submit_form_update_where_value) . '" maxlength="255">
                                                                                </span>
                                                                                <script>init_product_submit_form_update_where("' . escape_javascript($submit_form_update_where_field) . '")</script>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input value="1" ' . $add_comment_checked . ' id="add_comment" name="add_comment" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#add_comment_row" />
                                                <label class="form-check-label" for="add_comment">' . lang('Add Comment') . '</label>
                                            </div>
                                            <div class="collapse popover  fade bs-popover-bottom p-0 mb-2" id="add_comment_row">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                            <label class="form-label" for="add_comment_page_id">' . lang('Page') . '</label>
                                                            <select class="form-select" id="add_comment_page_id" name="add_comment_page_id"><option value="">-' . lang(array('string'=>'Select {var:1}','vars'=>array(lang('Page')) )) . '-</option>' .  select_page($add_comment_page_id) . '</select>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                            <label class="form-label" for="add_comment_name">' . lang('Added by') . '</label>
                                                            <input value="' . h($add_comment_name) . '" class="form-control"  id="add_comment_name" name="add_comment_name"/>
                                                        </div>
                                                        <div class="col-12  my-1">
                                                            <label class="form-label" for="add_comment_message">' . lang('Comment') . '</label>
                                                            <textarea class="form-control"  id="add_comment_message" name="add_comment_message">' . h($add_comment_message) . '</textarea>
                                                        </div>
                                                        <div class="col-12  my-3">
                                                            <div class="form-check form-switch">
                                                                <input value="1" ' . $add_comment_only_for_submit_form_update_checked . ' id="add_comment_only_for_submit_form_update" name="add_comment_only_for_submit_form_update" class="form-check-input" type="checkbox" role="switch" />
                                                                <label class="form-check-label" for="add_comment_only_for_submit_form_update">' . lang('Only add Comment if Submitted Form was updated') . '</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 my-3">
                                            <label class="form-label">' . lang('Form/Comment Quantity Type') . '</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="submit_form_quantity_type" id="submit_form_quantity_type_quantity" value="One Form per Quantity" ' . $submit_form_quantity_type_one_form_per_quantity_checked . '>
                                                <label class="form-check-label" for="submit_form_quantity_type_quantity">' . lang('One form/comment per quantity') . '</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="submit_form_quantity_type" id="submit_form_quantity_type_product" value="One Form per Product" ' . $submit_form_quantity_type_one_form_per_product_checked . '>
                                                <label class="form-check-label" for="submit_form_quantity_type_product">' . lang('One form/comment per product') . '</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Site Search & Tag Cloud') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mt-3 mb-2">
                                            <label for="keywords" class="form-label">' . lang('Search Keywords') . '</label>
                                            <input type="text" value="' . h($keywords) . '" name="keywords" id="keywords" class="form-control tagin min-height-tagin" data-placeholder="' . lang('Add tags') . '"  maxlength="255"/>
                                            <script>
                                                if(document.body.contains(document.querySelector("input#keywords"))){
                                                    tagin( document.querySelector("#keywords") );
                                                }
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Helpful Contents') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        ' . $output_custom_product_field_rows . '
                                        <div class="col-12 my-3">
                                            <label for="notes" class="form-label">' . lang('Notes') . '</label>
                                            <textarea id="notes" name="notes" class="form-control">' . $notes . '</textarea>
                                            <div class="form-text text-end">' . lang('Product Notes for Order Exporting') . '</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 ">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('SEO') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="address_name" class="form-label">' . lang('Catalog Name') . '</label>
                                            <div class="input-group ">
                                                <label for="address_name" class="input-group-text material-icons" title="' . lang('This option determines the url address of the product. Automatically assigned if left blank.') . '" data-bs-content="' . URL_SCHEME . HOSTNAME . OUTPUT_PATH . 'example-catalog/{' . lang('Catalog Name') . '}">public</label>
                                                <input type="text" value="' . h($address_name) . '" name="address_name" id="address_name" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="title" class="form-label">' . lang('Web Browser Title') . '</label>
                                            <input type="text" value="' . h($title) . '" name="title" id="title" class="form-control" />
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="meta_description" class="form-label">' . lang('Web Browser Description') . '</label>
                                            <textarea name="meta_description" id="meta_description" class="form-control" maxlength="255" >' . h($meta_description) . '</textarea>
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="meta_keywords" class="form-label">' . lang('Web Browser Keywords') . '</label>
                                            <input type="text" value="' . h($meta_keywords) . '" name="meta_keywords" id="meta_keywords" class="form-control tagin min-height-tagin" data-placeholder="' . lang('Add tags') . '"  maxlength="255"/>
                                            <script>
                                                if(document.body.contains(document.querySelector("input#meta_keywords"))){
                                                    tagin(document.querySelector("#meta_keywords"));
                                                }
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 ">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('RSS Feed') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="google_product_category" class="form-label popover-click" title="' . lang('Google Product Category') . '" data-bs-content="' . lang('Google product category <a href=\'https://kodpen.com/blog/evrimi-i-google-r-n-kategori-tablosu\' target=\'_blank\'>[google_product_category]</a> All products are automatically assigned a product category from Google’s continuously evolving product taxonomy. Providing high-quality, on-topic titles and descriptions, as well as accurate pricing, brand, and GTIN information will help ensure your products are correctly categorized.The Google product category [google_product_category] attribute is optional, and can be used to override Google’s automatic categorization in specific cases.') . '">
                                                ' . lang('GPC') . ' (' . lang('what is this?') . ')
                                            </label>
                                            <input type="text" value="' . h($google_product_category) . '" name="google_product_category" id="google_product_category" size="100" class="form-control" maxlength="255" />
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="gtin" class="form-label">' . lang('GTIN') . '</label>
                                            <input type="text" value="' . h($gtin) . '" name="gtin" id="gtin" size="30" class="form-control" maxlength="50" placeholder="' . lang('e.g. UPC') . '" />
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="brand" class="form-label">' . lang('Brand') . '</label>
                                            <input type="text" value="' . h($brand) . '" name="brand" id="brand" size="30" class="form-control" maxlength="100" />
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="mpn" class="form-label">' . lang('MPN') . '</label>
                                            <input type="text" value="' . h($mpn) . '" name="mpn" id="mpn" size="30" class="form-control" maxlength="50" placeholder="' . lang('i.e. manufacturer product number') . '" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="submitted_button_field" name="submitted_button_field" value="submit" />          
                    <nav class="buttons navigation text-center position-sticky mb-4" style="bottom:.5rem;" aria-label="data edit buttons ">
                        <div class="container">
                            <div class=" btn-group flex-wrap justify-content-center">
                                <button type="submit" id="save_button" name="submit_save" value="Save" class="btn my-1  btn-success " data-loading-content="' . lang(array('string'=>'Saving') ) . '"><span class="material-icons me-2">save</span><span class="btn-text" >' . lang(array('string'=>'Save') ) . '</span></button>
                                <button type="submit" name="submit_delete" value="Delete" class="btn my-1  btn-danger " data-loading-content="' . lang(array('string'=>'Deleting') ) . '" data-confirm-content="' . lang(array('string'=>'WARNING: This {var:1} will be permanently deleted.','vars'=>array(lang('product')))) . '"><span class="material-icons me-2">delete</span><span class="btn-text" >' . lang(array('string'=>'Delete') ) . '</span></button>
                            </div>
                        </div>
                    </nav>
                    <input type="hidden" name="id" value="' . h($_GET['id']) . '">
                </form>
            </div>
        </div>
    </main>' .
        (defined('BARCODE_ENABLED') && BARCODE_ENABLED ? '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.PgBarcode && window._pgBarcodeOpts) {
                PgBarcode.initProductBarcode(window._pgBarcodeOpts);
            }
        });
        </script>' : '') .
        output_footer();

    $liveform->remove_form();

} else {
    validate_token_field();

    //we convert metric system to inch and pound. we store like this.
    $weight = 0;
    $length = 0;
    $width = 0;
    $height = 0;
    if ($_POST['convert_to_metric_system'] == 1){
        $weight = round($_POST['weight'] * 2.20462262185,2);
        $length = round($_POST['length'] * 0.39370078740158,2);
        $width = round($_POST['width'] * 0.39370078740158,2);
        $height = round($_POST['height'] * 0.39370078740158,2);
    }else{
        $weight = $_POST['weight'];
        $length = $_POST['length'];
        $width = $_POST['width'];
        $height = $_POST['height'];
    }

    // delete product references in products_zones_xref (we do this reguardless of whether we are deleting the product or updating the product)
    $query = "DELETE FROM products_zones_xref ".
             "WHERE product_id = '" . escape($_POST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    // Delete product attribute references (we do this for both delete and update).
    db("DELETE FROM products_attributes_xref WHERE product_id = '" . escape($_POST['id']) . "'");

    // Delete product images references (we do this for both delete and update).
    db("DELETE FROM products_images_xref WHERE product = '" . escape($_POST['id']) . "'");

   
    // Delete submit form fields references for this product (we do this for both delete and update).
    db("DELETE FROM product_submit_form_fields WHERE product_id = '" . escape($_POST['id']) . "'");

    // if product was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete product
        $query =    "DELETE FROM products ".
                    "WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // delete product references in products_groups_xref
        $query =    "DELETE FROM products_groups_xref ".
                    "WHERE product = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete form fields for this product
        $query = "DELETE FROM form_fields WHERE (product_id = '" . escape($_POST['id']) . "') AND (product_id != '0')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete form field options for this product
        $query = "DELETE FROM form_field_options WHERE (product_id = '" . escape($_POST['id']) . "') AND (product_id != '0')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // Delete target options for this product.
        db("DELETE FROM target_options WHERE (product_id = '" . e($_POST['id']) . "') AND (product_id != '0')");
        
        // delete all of the keywords for this product
        $query = "DELETE FROM tag_cloud_keywords WHERE (item_id = '" . escape($_POST['id']) . "') AND (item_type = 'product')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete all of the keywords xref records for this product
        $query = "DELETE FROM tag_cloud_keywords_xref WHERE (item_id = '" . escape($_POST['id']) . "') AND (item_type = 'product')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // Check if this product has short links, in order to determine if we need to delete them and update rewrite file.
        $query =
            "SELECT COUNT(*)
            FROM short_links
            WHERE
                (destination_type = 'product')
                AND (product_id = '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);

        // If a short link exists, then delete them and update short links in rewrite file.
        if ($row[0] != 0) {
            $query =
                "DELETE FROM short_links
                WHERE
                    (destination_type = 'product')
                    AND (product_id = '" . escape($_POST['id']) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        // Delete offer rule associations with product.
        db("DELETE FROM offer_rules_products_xref WHERE product_id = '" . e($_POST['id']) . "'");

        log_activity("product ($_POST[name]) was deleted", $_SESSION['sessionusername']);
    
    // else product was not selected for delete
    } else {
        // if user has a user role, then verify that user has access to contact group that was selected
        if ($user['role'] == 3) {
            $new_contact_group_id = $_POST['contact_group_id'];
            
            // get current contact group id
            $query =
                "SELECT contact_group_id
                FROM products
                WHERE id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            $current_contact_group_id = $row['contact_group_id'];
            
            // if contact group is trying to be changed
            // and a contact group was selected
            // and user does not have access to contact group,
            // then don't allow contact group to be changed
            if (($new_contact_group_id != $current_contact_group_id) && ($new_contact_group_id) && (validate_contact_group_access($user, $new_contact_group_id) == false)) {
                log_activity("access denied because user does not have access to contact group that user selected for product", $_SESSION['sessionusername']);
                output_error(lang('Access denied. <a href="javascript:history.go(-1)">Go back</a>.'));
            }
        }
        
        // remove commas and spaces from price
        $price = str_replace(',', '', $_POST['price']);
        $price = str_replace(' ', '',$price); 
        // convert price from dollars to cents
        $price = $price * 100;
        
        // remove commas from extra shipping cost
        $extra_shipping_cost = str_replace(',', '', $_POST['extra_shipping_cost']);
        
        // convert extra shipping cost from dollars to cents
        $extra_shipping_cost = $extra_shipping_cost * 100;

        $_POST['order_receipt_bcc_email_address'] = trim($_POST['order_receipt_bcc_email_address']);
        
        // if a order receipt bcc email address was supplied, validate the e-mail address
        if ($_POST['order_receipt_bcc_email_address']) {
            if (validate_email_address($_POST['order_receipt_bcc_email_address']) == FALSE) {
                output_error(lang('The e-mail address is invalid. <a href="javascript:history.go(-1);">Go back</a>.'));
            }
        }

        $_POST['email_bcc'] = trim($_POST['email_bcc']);
        
        // if a bcc e-mail address was supplied, validate bcc e-mail address
        if ($_POST['email_bcc']) {
            if (validate_email_address($_POST['email_bcc']) == FALSE) {
                output_error(lang('The e-mail address is invalid. <a href="javascript:history.go(-1);">Go back</a>.'));
            }
        }
        
        // if the affiliate program is enabled, prepare affiliate program SQL
        if (AFFILIATE_PROGRAM == true) {
            $sql_commissionable =
                    "commissionable = '" . escape($_POST['commissionable']) . "',
                    commission_rate_limit = '" . escape($_POST['commission_rate_limit']) . "',";
        }

        // determine if recurring profile disabled fields should be updated
        $sql_recurring_profile_disabled = '';
        
        // if credit/debit card payment method is enabled and payment gateway is PayPal Payments Pro, then prepare to update recurring profile disabled fields
        if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && (ECOMMERCE_PAYMENT_GATEWAY == 'PayPal Payments Pro')) {
            $sql_recurring_profile_disabled =
                "recurring_profile_disabled_perform_actions = '" . escape($_POST['recurring_profile_disabled_perform_actions']) . "',
                recurring_profile_disabled_expire_membership = '" . escape($_POST['recurring_profile_disabled_expire_membership']) . "',
                recurring_profile_disabled_revoke_private_access = '" . escape($_POST['recurring_profile_disabled_revoke_private_access']) . "',
                recurring_profile_disabled_email = '" . escape($_POST['recurring_profile_disabled_email']) . "',
                recurring_profile_disabled_email_subject = '" . escape($_POST['recurring_profile_disabled_email_subject']) . "',
                recurring_profile_disabled_email_page_id = '" . escape($_POST['recurring_profile_disabled_email_page_id']) . "',";
        }
        
        // determine if Sage group ID field should be updated
        $sql_sage_group_id = '';
        
        // if credit/debit card payment method is enabled and payment gateway is Sage, then prepare to update Sage group ID field
        if ((ECOMMERCE_CREDIT_DEBIT_CARD == TRUE) && (ECOMMERCE_PAYMENT_GATEWAY == 'Sage')) {
            $sql_sage_group_id = "sage_group_id = '" . escape($_POST['sage_group_id']) . "',";
        }

        // get current product information
        $query =
            "SELECT
                form,
                title,
                meta_description,
                full_description,
                details,
                seo_analysis_current,
                address_name
            FROM products
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $original_state = $row['form'];
        $title = $row['title'];
        $meta_description = $row['meta_description'];
        $full_description = $row['full_description'];
        $details = $row['details'];
        $seo_analysis_current = $row['seo_analysis_current'];
        $current_address_name = $row['address_name'];
        
        // if the address name is NOT blank then use that value for the address name
        if ($_POST['address_name'] != '') {
            $address_name = $_POST['address_name'];
            
        // else if the short description is NOT blank then use that value
        } elseif ($_POST['short_description'] != '') {
            $address_name = $_POST['short_description'];
            
        // else if the name is NOT blank then use that value
        } elseif ($_POST['name'] != '') {
            $address_name = $_POST['name'];
            
        // else use id
        } else {
            $address_name = $_POST['id'];
        }
        
        // prepare the address name for the database
        $address_name = prepare_catalog_item_address_name($address_name, $_POST['id']);

        // If this product is enabled, then deal with adding/updating keywords for tag clouds.
        if ($_POST['enabled'] == 1) {
            // get the tag cloud keywords xref records for this product
            $query = "SELECT item_id FROM tag_cloud_keywords_xref WHERE (item_id = '" . escape($_POST['id']) . "') AND (item_type = 'product')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if there is an xref record, then update the keywords in the tag cloud
            if (mysqli_num_rows($result) > 0) {
                $new_keywords = array();
                
                // get the new keywords
                $new_keywords = explode(',', $_POST['keywords']);
                
                // loop through the keywords to remove any extra spaces before and after the keyword
                foreach ($new_keywords as $key => $new_keyword) {
                    if ($new_keyword != '') {
                        $new_keywords[$key] = trim($new_keyword);
                    }
                }
                
                // remove duplicate entries from the array
                $new_keywords = array_unique($new_keywords);
                
                $original_keywords = array();
                
                // get the original meta keywords for this product
                $query = "SELECT keyword FROM tag_cloud_keywords WHERE (item_id = '" . escape($_POST['id']) . "') AND (item_type = 'product')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                while($row = mysqli_fetch_assoc($result)) {
                    $original_keywords[] = $row['keyword'];
                }
                
                // if there are original keywords, then compare them to the new keywords and remove any keywords that are in both arrays from the new keywords array,
                // and remove any original keywords that are not in the new keywords array from the database
                if (count($original_keywords) > 0) {
                    // loop through the old and new keywords arrays to remove any keywords that are in both, and to remove old keywords from the database that are not in the new keywords array
                    foreach ($original_keywords as $original_keyword) {
                        $found_keyword = FALSE;
                        
                        foreach ($new_keywords as $key => $new_keyword) {
                            // if the original keyword matches the new keyword, then remove it from the new keywords array and indicate that a keyword was found
                            if ($original_keyword == $new_keyword) {
                                unset($new_keywords[$key]);
                                $found_keyword = TRUE;
                            }
                        }
                        
                        // if a keyword was not found, then remove it from the database
                        if ($found_keyword == FALSE) {
                            $query = "DELETE FROM tag_cloud_keywords WHERE ((keyword = '" . escape($original_keyword) . "') AND (item_id = '" . escape($_POST['id']) . "') AND (item_type = 'product'))";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                    }
                }
                
                // loop through the new keywords and add them to the database
                foreach ($new_keywords as $key => $new_keyword) {
                    // if the new keyword is not blank, then insert the keyword
                    if ($new_keyword != '') {
                        $query = 
                            "INSERT INTO tag_cloud_keywords 
                            (
                                keyword, 
                                item_id, 
                                item_type
                            ) VALUES (
                                '" . escape($new_keyword) . "',
                                '" . escape($_POST['id']) . "',
                                'product'
                            )";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }
                }
            }

        // Otherwise the product is disabled, so delete tag cloud keywords.
        } else {
            db("DELETE FROM tag_cloud_keywords WHERE (item_id = '" . escape($_POST['id']) . "') AND (item_type = 'product')");
        }
        
        $sql_seo_analysis_current = "";
        
        // if the seo analysis is current and the title, meta description, full description, or details has changed, the prepare to clear current status
        if (
            ($seo_analysis_current == 1)
            &&
            (
                (trim($title) != trim($_POST['title']))
                || (trim($meta_description) != trim($_POST['meta_description']))
                || (trim($full_description) != trim($_POST['full_description']))
                || (trim($details) != trim($_POST['details']))
            )
        ) {
            $sql_seo_analysis_current = "seo_analysis_current = '0',";
        }

        $selected_images = array();
        foreach ($_POST['selected_images'] as $selected_image ) {
            $selected_images[] = $selected_image ;
        }
        
        $selected_count = 0;
        foreach ($selected_images as $value) {
            $selected_count++;
        }
        if($selected_count >= 1){
            $selected_cover_image = reset($selected_images);
            array_shift($selected_images);
            if($selected_cover_image){
                $sql_imagename = 
                "image_name = '" . escape($selected_cover_image) . "',";
            }
        }else{
            $sql_imagename = 
            "image_name = '',";
        }

        $sql_out_of_stock = '';
        
        if ( $_POST['inventory_quantity'] > 0 || $_POST['inventory'] == 0) {
            $sql_out_of_stock = "out_of_stock = '0',";
        }


        // update the product
        $query =
            "UPDATE products
            SET
                name = '" . escape($_POST['name']) . "',
                enabled = '" . escape($_POST['enabled']) . "',
                short_description = '" . escape($_POST['short_description']) . "',
                full_description = '" . escape(prepare_rich_text_editor_content_for_input($_POST['full_description'])) . "',
                details = '" . escape(prepare_rich_text_editor_content_for_input($_POST['details'])) . "',
                code = '" . escape($_POST['code']) . "',
                keywords = '" . escape($_POST['keywords']) . "',
				$sql_imagename
                price = '" . escape($price) . "',
                taxable = '" . escape($_POST['taxable']) . "',
                contact_group_id = '" . escape($_POST['contact_group_id']) . "',
                order_receipt_bcc_email_address = '" . escape($_POST['order_receipt_bcc_email_address']) . "',
                email_page = '" . escape($_POST['email_page']) . "',
                email_bcc = '" . escape($_POST['email_bcc']) . "',
                order_receipt_message = '" . escape(prepare_rich_text_editor_content_for_input($_POST['order_receipt_message'])) . "',
                required_product = '" . escape($_POST['required_product']) . "',
                shippable = '" . escape($_POST['shippable']) . "',
                weight = '" . escape($weight) . "',
                primary_weight_points = '" . escape($_POST['primary_weight_points']) . "',
                secondary_weight_points = '" . escape($_POST['secondary_weight_points']) . "',
                length = '" . e($length) . "',
                width = '" . e($width) . "',
                height = '" . e($height) . "',
                container_required = '" . e($_POST['container_required']) . "',
                preparation_time = '" . escape($_POST['preparation_time']) . "',
                free_shipping = '" . escape($_POST['free_shipping']) . "',
                extra_shipping_cost = '" . escape($extra_shipping_cost) . "',
                $sql_commissionable
                selection_type = '" . escape($_POST['selection_type']) . "',
                default_quantity = '" . escape($_POST['default_quantity']) . "',
                minimum_quantity = '" . escape($_POST['minimum_quantity']) . "',
                maximum_quantity = '" . escape($_POST['maximum_quantity']) . "',
                address_name = '" . escape($address_name) . "',
                title = '" . escape($_POST['title']) . "',
                meta_description = '" . escape($_POST['meta_description']) . "',
                meta_keywords = '" . escape($_POST['meta_keywords']) . "',
                inventory = '" . escape($_POST['inventory']) . "',
                inventory_quantity = '" . escape($_POST['inventory_quantity']) . "',
                backorder = '" . escape($_POST['backorder']) . "',
                out_of_stock_message = '" . escape(prepare_rich_text_editor_content_for_input($_POST['out_of_stock_message'])) . "',
                $sql_out_of_stock
                recurring = '" . escape($_POST['recurring']) . "',
                recurring_schedule_editable_by_customer = '" . escape($_POST['recurring_schedule_editable_by_customer']) . "',
                start = '" . escape($_POST['start']) . "',
                number_of_payments = '" . escape($_POST['number_of_payments']) . "',
                payment_period = '" . escape($_POST['payment_period']) . "',
                $sql_recurring_profile_disabled
                $sql_sage_group_id
                membership_renewal = '" . escape($_POST['membership_renewal']) . "',
                grant_private_access = '" . escape($_POST['grant_private_access']) . "',
                private_folder = '" . escape($_POST['private_folder']) . "',
                private_days = '" . escape($_POST['private_days']) . "',
                send_to_page = '" . escape($_POST['send_to_page']) . "',
                reward_points = '" . escape($_POST['reward_points']) . "',
                gift_card = '" . escape($_POST['gift_card']) . "',
                gift_card_email_subject = '" . escape($_POST['gift_card_email_subject']) . "',
                gift_card_email_format = '" . escape($_POST['gift_card_email_format']) . "',
                gift_card_email_body = '" . escape($_POST['gift_card_email_body']) . "',
                gift_card_email_page_id = '" . escape($_POST['gift_card_email_page_id']) . "',
                submit_form = '" . escape($_POST['submit_form']) . "',
                submit_form_custom_form_page_id = '" . escape($_POST['submit_form_custom_form_page_id']) . "',
                submit_form_quantity_type = '" . e($_POST['submit_form_quantity_type']) . "',
                submit_form_create = '" . escape($_POST['submit_form_create']) . "',
                submit_form_update = '" . escape($_POST['submit_form_update']) . "',
                submit_form_update_where_field = '" . e($_POST['submit_form_update_where_field']) . "',
                submit_form_update_where_value = '" . e($_POST['submit_form_update_where_value']) . "',
                add_comment = '" . escape($_POST['add_comment']) . "',
                add_comment_page_id = '" . escape($_POST['add_comment_page_id']) . "',
                add_comment_message = '" . escape($_POST['add_comment_message']) . "',
                add_comment_name = '" . escape($_POST['add_comment_name']) . "',
                add_comment_only_for_submit_form_update = '" . escape($_POST['add_comment_only_for_submit_form_update']) . "',
                form = '" . escape($_POST['product_form']) . "',
                form_name = '" . escape($_POST['form_name']) . "',
                form_label_column_width = '" . escape($_POST['form_label_column_width']) . "',
                form_quantity_type = '" . escape($_POST['form_quantity_type']) . "',
                custom_field_1 = '" . escape($_POST['custom_field_1']) . "',
                custom_field_2 = '" . escape($_POST['custom_field_2']) . "',
                custom_field_3 = '" . escape($_POST['custom_field_3']) . "',
                custom_field_4 = '" . escape($_POST['custom_field_4']) . "',
                notes = '" . escape($_POST['notes']) . "',
                google_product_category = '" . escape($_POST['google_product_category']) . "',
                gtin = '" . escape($_POST['gtin']) . "',
                brand = '" . escape($_POST['brand']) . "',
                mpn = '" . escape($_POST['mpn']) . "',
                $sql_seo_analysis_current
                user = '" . $user['id'] . "',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');

        if($selected_count > 1){
            foreach ($selected_images as $value) {db("INSERT INTO products_images_xref (product,file_name)VALUES ('" . escape($_POST['id']) . "','" . escape($value) . "')");}
        }

        // load all allowed zones in array by exploding string that has allowed zone ids separated by commas
        $allowed_zones = $_POST['allowed_zones'];

        // foreach allowed zone insert row in products_zones_xref table
        foreach ($allowed_zones as $zone_id) {
            // if zone id is not blank, insert row
            if ($zone_id) {
                $query = "INSERT INTO products_zones_xref (product_id, zone_id) VALUES ('" . escape($_POST['id']) . "', '" . escape($zone_id) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        // If the user added attributes, then save them.
        if ($_POST['attributes']) {
            $attributes = decode_json($_POST['attributes']);

            $sort_order = 0;

            foreach ($attributes as $attribute) {
                $sort_order++;
                
                db(
                    "INSERT INTO products_attributes_xref (
                        product_id,
                        attribute_id,
                        option_id,
                        sort_order)
                    VALUES (
                        '" . e($_POST['id']) . "',
                        '" . e($attribute['attribute_id']) . "',
                        '" . e($attribute['option_id']) . "',
                        '$sort_order')");
            }
        }

        
    
        if ($_POST['groups']) {
            $groups = decode_json($_POST['groups']);
            $get_products_groups_xrefs = db_items("SELECT product_group FROM products_groups_xref WHERE product = '" . e($_POST['id']) . "'");
          
            //if not equal we need to update product group xrefs
            if($get_products_groups_xrefs != $groups){
                // we get old data, need when insert
                $get_products_groups_xref_datas = db_items("SELECT product,product_group,sort_order,featured,featured_sort_order,new_date FROM products_groups_xref WHERE product = '" . e($_POST['id']) . "'");
                
                // we delete old products_groups_xref connected this product
                db("DELETE FROM products_groups_xref WHERE  product = '" . e($_POST['id']) . "'");

                foreach ($groups as $group) {

                   foreach($get_products_groups_xref_datas as $get_products_groups_xref_data){
                        $group_sort_order = '0';
                        $group_featured  = '0';
                        $group_featured_sort_order  = '0';
                        $group_new_date  = '0000-00-00';
                        // product_groups are match
                        if($get_products_groups_xref_data['product_group'] == $group['product_group']){
                            //if there is old sort_order we prepare add to products_groups_xref.
                            if($get_products_groups_xref_data['sort_order']){
                                $group_sort_order = $get_products_groups_xref_data['sort_order'];
                            }
                            //if there is old featured we prepare add to products_groups_xref.
                            if($get_products_groups_xref_data['featured']){
                                $group_featured = $get_products_groups_xref_data['featured'];
                            }
                            //if there is old featured_sort_order we prepare add to products_groups_xref.
                            if($get_products_groups_xref_data['featured_sort_order']){
                                $group_featured_sort_order = $get_products_groups_xref_data['featured_sort_order'];
                            }
                            //if there is old new_date we prepare add to products_groups_xref.
                            if($get_products_groups_xref_data['new_date']){
                                $group_new_date = $get_products_groups_xref_data['new_date'];
                            }
                        }
                   }
                    db(
                        "INSERT INTO products_groups_xref (
                            product,
                            sort_order,
                            featured,
                            featured_sort_order,
                            new_date,
                            product_group)
                        VALUES (
                            '" . e($_POST['id']) . "',
                            '" . e($group_sort_order) . "',
                            '" . e($group_featured) . "',
                            '" . e($group_featured_sort_order) . "',
                            '" . e($group_new_date) . "',
                            '" . e($group['product_group']) . "'
                        )
                        
                        
                        ");
                }
            }
        }else{
            // we delete products_groups_xref connected this product
            db("DELETE FROM products_groups_xref WHERE  product = '" . e($_POST['id']) . "'");
        }

        // If a custom form was selected for submit form feature, then check if we need to add fields to database.
        if ($_POST['submit_form_custom_form_page_id']) {
            // Create array for storing submit form fields that have a value set, so if a user tried
            // to set multiple values for the same field, we don't add the extras.
            $added_submit_form_fields = array();
            
            // Loop through all submit form create fields in order to insert them into database.
            for ($field_number = 1; $field_number <= $_POST['last_submit_form_create_field_number']; $field_number++) {
                // If a field was selected, and the field has not already been added,
                // then continue to check if field should be added to database.
                if (
                    ($_POST['submit_form_create_field_' . $field_number . '_form_field_id'])
                    && (in_array($_POST['submit_form_create_field_' . $field_number . '_form_field_id'], $added_submit_form_fields) == false)
                ) {
                    // Check to make sure that selected field actually exists on the custom form
                    // in order to make sure that user is not trying to do something funny like trying to
                    // set a field on a different form from the one they selected.
                    $field_id = db_value(
                        "SELECT id
                        FROM form_fields
                        WHERE
                            (id = '" . escape($_POST['submit_form_create_field_' . $field_number . '_form_field_id']) . "')
                            AND (page_id = '" . escape($_POST['submit_form_custom_form_page_id']) . "')");

                    // If a field was found for the selected field and selected custom form,
                    // then continue to add field to database.
                    if ($field_id) {
                        db(
                            "INSERT INTO product_submit_form_fields (
                                product_id,
                                action,
                                form_field_id,
                                value)
                            VALUES (
                                '" . escape($_POST['id']) . "',
                                'create',
                                '" . escape($_POST['submit_form_create_field_' . $field_number . '_form_field_id']) . "',
                                '" . escape(trim($_POST['submit_form_create_field_' . $field_number . '_value'])) . "')");

                        // Remember that the field has been added so we don't add multiple records for the same field.
                        $added_submit_form_fields[] = $_POST['submit_form_create_field_' . $field_number . '_form_field_id'];
                    }
                }
            }

            $added_submit_form_fields = array();
            
            // Loop through all submit form update fields in order to insert them into database.
            for ($field_number = 1; $field_number <= $_POST['last_submit_form_update_field_number']; $field_number++) {
                // If a field was selected, and the field has not already been added,
                // then continue to check if field should be added to database.
                if (
                    ($_POST['submit_form_update_field_' . $field_number . '_form_field_id'])
                    && (in_array($_POST['submit_form_update_field_' . $field_number . '_form_field_id'], $added_submit_form_fields) == false)
                ) {
                    // Check to make sure that selected field actually exists on the custom form
                    // in order to make sure that user is not trying to do something funny like trying to
                    // set a field on a different form from the one they selected.
                    $field_id = db_value(
                        "SELECT id
                        FROM form_fields
                        WHERE
                            (id = '" . escape($_POST['submit_form_update_field_' . $field_number . '_form_field_id']) . "')
                            AND (page_id = '" . escape($_POST['submit_form_custom_form_page_id']) . "')");

                    // If a field was found for the selected field and selected custom form,
                    // then continue to add field to database.
                    if ($field_id) {
                        db(
                            "INSERT INTO product_submit_form_fields (
                                product_id,
                                action,
                                form_field_id,
                                value)
                            VALUES (
                                '" . escape($_POST['id']) . "',
                                'update',
                                '" . escape($_POST['submit_form_update_field_' . $field_number . '_form_field_id']) . "',
                                '" . escape(trim($_POST['submit_form_update_field_' . $field_number . '_value'])) . "')");

                        // Remember that the field has been added so we don't add multiple records for the same field.
                        $added_submit_form_fields[] = $_POST['submit_form_update_field_' . $field_number . '_form_field_id'];
                    }
                }
            }
        }

        log_activity(lang(array('string'=>'product ({var:1}) was modified','vars'=>$_POST['name'])), $_SESSION['sessionusername']);
    }
    
    // if the product was not deleted and the original product form state was set to 0 (off), and if the new product form state is different than the original.
    if (($_POST['submit_delete'] != 'Delete') && (($original_state == 0) && ($_POST['current_form_state'] != $original_state))) {
        // forward user to view form designer
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_fields.php?product_id=' . $_POST['id']);
    } else {
        // if there is a send to set, then forward user to send to
        if ($_POST['send_to'] != '') {
            header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
            
        // else there is not a send to set, so forward user to view products screen.
        } else {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_products.php');
        }
    }
}