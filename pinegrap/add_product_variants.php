<?php
include('init.php');
$user = validate_user();
validate_ecommerce_access($user);

include_once('liveform.class.php');
$liveform = new liveform('add_product_variants');

if (!$_POST) {

    // if tax is on, check tax checkbox
    if (defined('ECOMMERCE_TAX') && ECOMMERCE_TAX == true) {
        $tax_checked = 'checked="checked"';
    }

    // if shipping is on, check shippable checkbox
    if (defined('ECOMMERCE_SHIPPING') && ECOMMERCE_SHIPPING == true) {
        $shippable_checked = 'checked="checked"';
    }

    // Get all product attributes with their options.
    $attributes = db_items(
        "SELECT
            id,
            name
        FROM product_attributes
        ORDER BY name", 'id');

    if ($attributes) {
        $attribute_options = db_items(
            "SELECT
                id,
                product_attribute_id,
                label,
                sort_order
            FROM product_attribute_options
            ORDER BY
                product_attribute_id,
                sort_order");

        foreach ($attribute_options as $attribute_option) {
            $attributes[$attribute_option['product_attribute_id']]['options'][] = $attribute_option;
        }
    }

    // Get all zones ordered by name.
    $zones = db_items(
        "SELECT
            id,
            name
        FROM zones
        ORDER BY name");

    // Get image code template from config for the image picker modal.
    $pg_image_code_template = db_value("SELECT product_image_code_template FROM config");

    // Build parent group options; pre-select first root group if any exist.
    $any_product_groups_for_pg = (bool)db_value("SELECT COUNT(*) FROM product_groups");
    $default_pg_parent_id = $any_product_groups_for_pg ? (int)db_value("SELECT id FROM product_groups WHERE parent_id = 0 ORDER BY sort_order, name LIMIT 1") : 0;
    if ($any_product_groups_for_pg) {
        $output_pg_parent_field =
            '<div class="col-12 col-md-4 my-2">' .
            '<label for="pg_parent_id" class="form-label">*' . lang('Parent Product Group') . '</label>' .
            '<select required style="width:100%" class="select2 form-select" id="pg_parent_id" name="pg_parent_id" data-placeholder="' . lang('Select Parent Group') . '">' .
            get_product_group_options($default_pg_parent_id, 0, 0, 0, array(), FALSE) .
            '</select></div>';
    } else {
        $output_pg_parent_field = '<input type="hidden" name="pg_parent_id" value="0" />';
    }

    // Build attribute dimension rows.
    $output_attribute_dimensions = '';
    if ($attributes) {
        foreach ($attributes as $attr) {
            if (empty($attr['options'])) {
                continue;
            }
            $output_default_options = '<option value="">-' . lang('None') . '-</option>';
            foreach ($attr['options'] as $opt) {
                $output_default_options .= '<option value="' . h($opt['id']) . '">' . h($opt['label']) . '</option>';
            }
            $output_attribute_dimensions .=
                '<div class="attribute-row border rounded mb-2 p-2" data-attr-id="' . h($attr['id']) . '" data-attr-name="' . h($attr['name']) . '">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">' . h($attr['name']) . '</div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary attr-move-up" title="' . lang('Move Up') . '"><span class="bi bi-arrow-up"></span></button>
                            <button type="button" class="btn btn-outline-secondary attr-move-down" title="' . lang('Move Down') . '"><span class="bi bi-arrow-down"></span></button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-2">';
            foreach ($attr['options'] as $opt) {
                $output_attribute_dimensions .=
                    '<div class="form-check me-3">
                        <input class="form-check-input option-checkbox" type="checkbox"
                            id="opt_' . h($opt['id']) . '"
                            value="' . h($opt['id']) . '"
                            data-label="' . h($opt['label']) . '" />
                        <label class="form-check-label" for="opt_' . h($opt['id']) . '">' . h($opt['label']) . '</label>
                    </div>';
            }
            $output_attribute_dimensions .=
                    '</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">' . lang('Default Option') . ':</span>
                        <select class="form-select form-select-sm attr-default-select" style="max-width:220px;">
                            ' . $output_default_options . '
                        </select>
                    </div>
                </div>';
        }
    } else {
        $output_attribute_dimensions =
            '<div class="alert alert-info">
                ' . lang('No product attributes found.') . '
                <a href="add_product_attribute.php">' . lang('Add Attribute') . '</a>
            </div>';
    }

    // Build common zones select options.
    $output_allowed_zones = '';
    if ($zones) {
        foreach ($zones as $zone) {
            $output_allowed_zones .= '<option value="' . h($zone['id']) . '">' . h($zone['name']) . '</option>';
        }
    }

    $liveform->remove_form();

    print
    pg_page_shell([
        'title'=> lang('Create Variant Products'),
        'extra classes'=>'products',
        'icon'=>'store',
        'heading'=>lang('Create Variant Products'),
        'cancel'=>array('enable'=>'true','url'=>'view_products.php')
    ,
            'breadcrumb' => array(array('label' => lang('All Products'), 'url' => OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_products.php'), array('label' => lang('Create Variant Products'))),
        ]) .
    get_wysiwyg_editor_code(array('pg_full_description', 'pg_details')) . '
            <div class="row">
            <div class="col-12">
                <div class="row mb-2 flex-wrap">
                    <div class="col-12 col-sm-12 text-center text-md-start">
</div>
                </div>
                <form name="form" id="variant_form" action="add_product_variants.php" method="post">
                    ' . get_token_field() . '
                    <input type="hidden" id="variants_json" name="variants_json" value="" />
                    <div class="row">

                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('New Product Group Information') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 my-2">
                                            <label for="pg_name" class="form-label">*' . lang('Product Group Name') . '</label>
                                            <input type="text" name="pg_name" id="pg_name" class="form-control" required />
                                            <div class="invalid-feedback">' . lang('Required Area') . '</div>
                                        </div>
                                        ' . $output_pg_parent_field . '
                                        <div class="col-12 col-md-4 my-2">
                                            <label for="pg_short_description" class="form-label">' . lang('Short Description') . '</label>
                                            <input type="text" name="pg_short_description" id="pg_short_description" maxlength="100" class="form-control" />
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="pg_full_description" class="form-label">' . lang('Full Description') . '</label>
                                            <textarea id="pg_full_description" name="pg_full_description"></textarea>
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="pg_details" class="form-label">' . lang('Details') . '</label>
                                            <textarea id="pg_details" name="pg_details"></textarea>
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="pg_keywords" class="form-label">' . lang('Search Keywords') . '</label>
                                            <input type="text" name="pg_keywords" id="pg_keywords" class="form-control tagin min-height-tagin" data-placeholder="' . lang('Add tags') . '" maxlength="255" />
                                            <script>if(document.body.contains(document.querySelector("#pg_keywords"))){tagin(document.querySelector("#pg_keywords"));}</script>
                                        </div>
                                        <div class="col-12 my-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="pg_enabled" name="pg_enabled" value="1" checked="checked" />
                                                <label class="form-check-label" for="pg_enabled">' . lang('Enabled') . '</label>
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
                                            <div id="software_image_picker_container" ondblclick="software_image_picker({initialize:true});" class="user-select-none sortable-list img-list bg-body-tertiary rounded p-2 row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4"></div>
                                            <button type="button" class="btn btn-primary my-3 me-2" onclick="software_image_picker({initialize:true});"><span class="bi bi-plus-circle me-2"></span>' . lang('Add Image') . '</button>
                                            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#image_code"><span class="material-icons me-2">code</span>' . lang('Code') . '</button>
                                            <div class="modal fade" id="image_code" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">' . lang('Code') . '</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-12 my-2">
                                                                    <div class="alert alert-primary">' . lang('Tags') . ': <span>^^image_loop_start^^</span>, <span>^^image_alt^^</span>, <span>^^image_url^^</span>, <span>^^image_loop_end^^</span></div>
                                                                </div>
                                                                <div class="col-12 my-2">
                                                                    <textarea id="pg_code" name="pg_code">' . h($pg_image_code_template) . '</textarea>
                                                                    ' . get_codemirror_includes() . '
                                                                    ' . get_codemirror_javascript(array('id' => 'pg_code', 'code_type' => 'mixed')) . '
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
                                                        animation: 150,
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

                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('SEO') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="pg_address_name" class="form-label">' . lang('Catalog Name') . '</label>
                                            <div class="input-group">
                                                <label for="pg_address_name" class="input-group-text material-icons" title="' . lang('This option determines the url address of the product. Automatically assigned if left blank.') . '">public</label>
                                                <input type="text" name="pg_address_name" id="pg_address_name" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="pg_title" class="form-label">' . lang('Web Browser Title') . '</label>
                                            <input type="text" name="pg_title" id="pg_title" class="form-control" />
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="pg_meta_description" class="form-label">' . lang('Web Browser Description') . '</label>
                                            <textarea name="pg_meta_description" id="pg_meta_description" class="form-control" maxlength="255"></textarea>
                                        </div>
                                        <div class="col-12 mt-1 mb-2">
                                            <label for="pg_meta_keywords" class="form-label">' . lang('Web Browser Keywords') . '</label>
                                            <input type="text" name="pg_meta_keywords" id="pg_meta_keywords" class="form-control tagin min-height-tagin" data-placeholder="' . lang('Add tags') . '" maxlength="255" />
                                            <script>if(document.body.contains(document.querySelector("#pg_meta_keywords"))){tagin(document.querySelector("#pg_meta_keywords"));}</script>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Variant Dimensions') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            ' . $output_attribute_dimensions . '
                                        </div>
                                        <div class="col-12 col-sm-8 col-md-6 my-2">
                                            <label for="sku_template" class="form-label">' . lang('SKU Template') . '</label>
                                            <input type="text" id="sku_template" name="sku_template" class="form-control" placeholder="' . lang('e.g. {Color}-{Size}') . '" />
                                            <div class="form-text">' . lang('Use attribute names in curly braces. e.g.: {Color}-{Size}') . '</div>
                                        </div>
                                        <div class="col-12 col-sm-4 col-md-3 my-2">
                                            <label for="base_price" class="form-label">' . lang('Base Price for All Variants') . '</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" id="base_price" class="form-control" value="0" />
                                                <span class="input-group-text">' . BASE_CURRENCY_SYMBOL . '</span>
                                            </div>
                                            <div class="form-text">' . lang('Applies to all generated variants') . '</div>
                                        </div>
                                        <div class="col-12 my-3">
                                            <button type="button" id="generate_matrix_btn" class="btn btn-primary">
                                                <span class="bi bi-grid me-2"></span>' . lang('Generate Matrix') . '
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="variant_matrix_wrapper">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold d-flex align-items-center flex-wrap gap-2">
                                    ' . lang('Variants to Create') . '
                                    <span id="variant_count_badge" class="badge bg-primary">0</span>
                                    <div class="d-flex flex-wrap gap-1 ms-auto">
                                        <button type="button" id="apply_image_all" class="btn btn-sm btn-outline-secondary">
                                            <span class="bi bi-image me-1"></span>' . lang('Apply First Image to All') . '
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="accordion" id="variant_matrix">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card my-4">
                                <div class="card-header bg-reset border-0 text-uppercase h5 text-primary fw-bold">
                                    ' . lang('Common Settings') . '
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" checked="checked" />
                                                <label class="form-check-label" for="enabled">' . lang('Enabled') . '</label>
                                            </div>
                                        </div>
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="taxable" name="taxable" value="1" ' . $tax_checked . ' />
                                                <label class="form-check-label" for="taxable">' . lang('Taxable') . '</label>
                                            </div>
                                        </div>
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input value="1" ' . $shippable_checked . ' id="shippable" name="shippable" class="form-check-input collapse-switcher" type="checkbox" role="switch" data-bs-target="#shippable_row" />
                                                <label class="form-check-label" for="shippable">' . lang('Shippable') . '</label>
                                            </div>
                                            <div class="collapse popover fade bs-popover-bottom p-0 mb-2" id="shippable_row">
                                                <div class="popover-arrow" style="position: absolute; left: 0px; transform: translate(59px, 0px);"></div>
                                                <div class="popover-body">
                                                    <div class="row">
                                                        <div class="col-12 justify-content-center d-flex flex-wrap my-1">
                                                            <div class="form-check form-switch">
                                                                <input value="1" name="convert_to_metric_system" id="convert_to_metric_system" class="form-check-input" type="checkbox" role="switch" />
                                                                <label class="form-label" for="convert_to_metric_system">' . lang('Convert to metric system') . '</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-lg-4 my-1">
                                                            <label for="weight" class="form-label">' . lang('Weight') . '</label>
                                                            <div class="input-group">
                                                                <input value="0" type="text" name="weight" id="weight" class="form-control" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="8" data-inputmask-digitsOptional="true" data-inputmask-placeholder="0" style="text-align: right;" />
                                                                <label class="input-group-text unit" for="weight">lbs</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                            <label for="primary_weight_points" class="form-label">' . lang('Primary Weight Points') . '</label>
                                                            <input value="0" type="text" name="primary_weight_points" id="primary_weight_points" class="form-control" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="1" data-inputmask-digitsOptional="false" data-inputmask-placeholder="0" style="text-align: right;" />
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-lg-4 my-1">
                                                            <label for="secondary_weight_points" class="form-label">' . lang('Secondary Weight Points') . '</label>
                                                            <input value="0" type="text" name="secondary_weight_points" id="secondary_weight_points" class="form-control" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-digits="1" data-inputmask-digitsOptional="false" data-inputmask-placeholder="0" style="text-align: right;" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 my-1">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input collapse-switcher" type="checkbox" id="default_inventory" name="default_inventory" value="1" role="switch" data-bs-target="#default_inv_qty_row" />
                                                <label class="form-check-label" for="default_inventory">' . lang('Default Inventory Tracking') . '</label>
                                            </div>
                                            <div class="collapse" id="default_inv_qty_row">
                                                <div class="mt-2" style="max-width:200px">
                                                    <label for="default_inventory_quantity" class="form-label">' . lang('Default Inventory Quantity') . '</label>
                                                    <input type="number" min="0" name="default_inventory_quantity" id="default_inventory_quantity" class="form-control" value="" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-4 my-2">
                                            <label for="default_quantity" class="form-label">' . lang('Default Quantity') . '</label>
                                            <div class="input-group number-controls">
                                                <button class="btn material-icons minus border border-end-0" type="button">remove</button>
                                                <input class="form-control text-center border-start-0 border-end-0" value="1" type="text" name="default_quantity" id="default_quantity" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-placeholder="0" />
                                                <button class="btn material-icons plus border border-start-0" type="button">add</button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-4 my-2">
                                            <label for="minimum_quantity" class="form-label">' . lang('Min. Quantity') . '</label>
                                            <div class="input-group number-controls">
                                                <button class="btn material-icons minus border border-end-0" type="button">remove</button>
                                                <input class="form-control text-center border-start-0 border-end-0" value="" type="text" name="minimum_quantity" id="minimum_quantity" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-placeholder="0" />
                                                <button class="btn material-icons plus border border-start-0" type="button">add</button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-4 my-2">
                                            <label for="maximum_quantity" class="form-label">' . lang('Max. Quantity') . '</label>
                                            <div class="input-group number-controls">
                                                <button class="btn material-icons minus border border-end-0" type="button">remove</button>
                                                <input class="form-control text-center border-start-0 border-end-0" value="" type="text" name="maximum_quantity" id="maximum_quantity" maxlength="9" inputmode="numeric" data-inputmask-alias="decimal" data-inputmask-placeholder="0" />
                                                <button class="btn material-icons plus border border-start-0" type="button">add</button>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-4 my-2">
                                            <label for="gtin" class="form-label">' . lang('GTIN') . '</label>
                                            <input type="text" name="gtin" id="gtin" class="form-control" maxlength="50" placeholder="' . lang('e.g. UPC') . '" />
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-4 my-2">
                                            <label for="brand" class="form-label">' . lang('Brand') . '</label>
                                            <input type="text" name="brand" id="brand" class="form-control" maxlength="100" />
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-4 my-2">
                                            <label for="selection_type" class="form-label">' . lang('Selection Type') . '</label>
                                            <select name="selection_type" id="selection_type" class="form-select">' . select_selection_type() . '</select>
                                        </div>
                                        ' . ($zones ? '
                                        <div class="col-12 my-2">
                                            <label for="allowed_zones" class="form-label">' . lang('Allowed Zones') . '</label>
                                            <select style="width:100%" class="select2 form-select" data-placeholder="' . lang('Click to select shipping zone(s)') . '" id="allowed_zones" name="allowed_zones[]" multiple="multiple">' . $output_allowed_zones . '</select>
                                        </div>' : '') . '
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <nav class="buttons navigation text-center position-sticky mb-4" style="bottom:.5rem;" aria-label="data edit buttons">
                        <div class="container">
                            <div class="btn-group flex-wrap justify-content-center">
                                <button type="submit" id="create_button" name="submit_create" value="Create" class="btn my-1 btn-success" data-loading-content="' . lang('Creating') . '"><span class="bi bi-plus-circle me-2"></span><span class="btn-text">' . lang('Create Variant Products') . '</span></button>
                            </div>
                        </div>
                    </nav>
                </form>
            </div>
        </div>
    </main>
    <script>
    var PinegrapVariants = {
        currencySymbol: ' . encode_json(BASE_CURRENCY_SYMBOL) . '
    };
    </script>
    <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/assets/add_product_variants.js?v=' . @filemtime(dirname(__FILE__) . '/assets/add_product_variants.js') . '"></script>' .
    output_footer();

} else {

    validate_token_field();

    // Validate product group name.
    $pg_name = trim($_POST['pg_name']);
    if (!$pg_name) {
        output_error(lang(array('string' => '{var:1} is required', 'vars' => array(lang('Product Group Name')))) . '. <a href="javascript:history.go(-1)">' . lang('Go back') . '</a>.');
    }

    // If product groups exist, parent_id is required
    if (db_value("SELECT COUNT(*) FROM product_groups") && !$_POST['pg_parent_id']) {
        output_error(lang(array('string' => '{var:1} is required', 'vars' => array(lang('Parent Product Group')))) . '. <a href="javascript:history.go(-1)">' . lang('Go back') . '</a>.');
    }
    if (!$_POST['pg_parent_id']) {
        $_POST['pg_parent_id'] = 0;
    }

    // Determine product group address name.
    if (trim($_POST['pg_address_name']) != '') {
        $pg_address_name = prepare_catalog_item_address_name(trim($_POST['pg_address_name']));
    } elseif (trim($_POST['pg_short_description']) != '') {
        $pg_address_name = prepare_catalog_item_address_name(trim($_POST['pg_short_description']));
    } else {
        $pg_address_name = prepare_catalog_item_address_name($pg_name);
    }

    // Handle selected images.
    $pg_selected_images = isset($_POST['selected_images']) ? $_POST['selected_images'] : array();
    $pg_cover_image = '';
    $pg_extra_images = array();
    if (!empty($pg_selected_images)) {
        $pg_cover_image = reset($pg_selected_images);
        $pg_extra_images = array_slice($pg_selected_images, 1);
    }

    // Create the product group (always display_type='select').
    db(
        "INSERT INTO product_groups (
            name,
            enabled,
            parent_id,
            short_description,
            full_description,
            details,
            code,
            keywords,
            image_name,
            display_type,
            address_name,
            title,
            meta_description,
            meta_keywords,
            attributes,
            user,
            timestamp)
        VALUES (
            '" . e($pg_name) . "',
            '" . e($_POST['pg_enabled'] ? '1' : '') . "',
            '" . e($_POST['pg_parent_id']) . "',
            '" . e(trim($_POST['pg_short_description'])) . "',
            '" . e(prepare_rich_text_editor_content_for_input($_POST['pg_full_description'])) . "',
            '" . e(prepare_rich_text_editor_content_for_input($_POST['pg_details'])) . "',
            '" . e($_POST['pg_code']) . "',
            '" . e($_POST['pg_keywords']) . "',
            '" . e($pg_cover_image) . "',
            'select',
            '" . e($pg_address_name) . "',
            '" . e(trim($_POST['pg_title'])) . "',
            '" . e(trim($_POST['pg_meta_description'])) . "',
            '" . e(trim($_POST['pg_meta_keywords'])) . "',
            '1',
            '" . USER_ID . "',
            UNIX_TIMESTAMP())");

    $group_id   = db_value("SELECT LAST_INSERT_ID()");
    $group_name = $pg_name;

    // Insert extra product group images.
    foreach ($pg_extra_images as $pg_extra_image) {
        db("INSERT INTO product_groups_images_xref (product_group, file_name) VALUES ('$group_id', '" . e($pg_extra_image) . "')");
    }

    // Save attribute default options and sort order for the product group.
    if (!empty($_POST['attributes_meta_json'])) {
        $attr_meta = decode_json($_POST['attributes_meta_json']);
        if (is_array($attr_meta)) {
            $attr_sort = 0;
            foreach ($attr_meta as $am) {
                $attr_sort++;
                $attr_id = (int)$am['id'];
                if ($attr_id) {
                    db("INSERT INTO product_groups_attributes_xref (product_group_id, attribute_id, default_option_id, sort_order) VALUES ('$group_id', '$attr_id', '" . e($am['default_option_id']) . "', '$attr_sort')");
                }
            }
        }
    }

    // Update image code template in config if code was provided with template tags.
    if (
        !empty($_POST['pg_code']) &&
        strpos($_POST['pg_code'], '^^image_url^^') !== false &&
        strpos($_POST['pg_code'], '^^image_loop_start^^') !== false &&
        strpos($_POST['pg_code'], '^^image_loop_end^^') !== false
    ) {
        $existing_code_template = db_value("SELECT product_image_code_template FROM config");
        if ($existing_code_template != $_POST['pg_code']) {
            db("UPDATE config SET product_image_code_template = '" . e($_POST['pg_code']) . "'");
        }
    }

    $variants_json = trim($_POST['variants_json']);

    if (!$variants_json) {
        output_error(lang('No variants to create.') . ' <a href="javascript:history.go(-1)">' . lang('Go back') . '</a>.');
    }

    $variants = decode_json($variants_json);

    if (!$variants || !is_array($variants) || count($variants) === 0) {
        output_error(lang('No variants to create.') . ' <a href="javascript:history.go(-1)">' . lang('Go back') . '</a>.');
    }

    // Common fields.
    $enabled = $_POST['enabled'] ? '1' : '';
    $taxable = $_POST['taxable'] ? '1' : '';
    $shippable = $_POST['shippable'] ? '1' : '';
    $selection_type = $_POST['selection_type'];
    $default_quantity = $_POST['default_quantity'];
    $minimum_quantity = $_POST['minimum_quantity'];
    $maximum_quantity = $_POST['maximum_quantity'];
    $gtin = $_POST['gtin'];
    $brand = $_POST['brand'];
    $primary_weight_points = $_POST['primary_weight_points'];
    $secondary_weight_points = $_POST['secondary_weight_points'];
    $common_zones             = isset($_POST['allowed_zones']) ? $_POST['allowed_zones'] : array();
    $common_inventory         = $_POST['default_inventory'] ? '1' : '';
    $common_inventory_quantity = trim($_POST['default_inventory_quantity']);

    // Weight with metric conversion.
    $weight = 0;
    if ($_POST['convert_to_metric_system'] == 1) {
        $weight = round($_POST['weight'] * 2.20462262185, 2);
    } else {
        $weight = $_POST['weight'];
    }

    $created_count = 0;

    foreach ($variants as $variant) {
        $name = get_unique_name(['name' => trim($variant['name']), 'type' => 'product']);
        $price = (int)round(str_replace([',', ' '], '', $variant['price']) * 100);
        $short_description = trim($variant['short_description']);
        $full_description = trim($variant['full_description']);
        $details = trim($variant['details']);
        $keywords = trim($variant['keywords']);
        $meta_keywords = $keywords;
        $title = trim($variant['title']);
        $meta_description = trim($variant['meta_description']);
        // Build images array (cover + extras).
        $variant_images = array();
        if (!empty($variant['images']) && is_array($variant['images'])) {
            foreach ($variant['images'] as $img) {
                $img = trim($img);
                if ($img !== '') {
                    $variant_images[] = $img;
                }
            }
        }
        $image_name        = !empty($variant_images) ? $variant_images[0] : '';
        $inventory         = $common_inventory;
        $inventory_quantity = $common_inventory_quantity;

        db(
            "INSERT INTO products (
                name,
                enabled,
                short_description,
                full_description,
                details,
                keywords,
                image_name,
                price,
                taxable,
                shippable,
                weight,
                primary_weight_points,
                secondary_weight_points,
                default_quantity,
                minimum_quantity,
                maximum_quantity,
                title,
                meta_description,
                meta_keywords,
                inventory,
                inventory_quantity,
                selection_type,
                gtin,
                brand,
                address_name,
                user,
                timestamp)
            VALUES (
                '" . escape($name) . "',
                '" . escape($enabled) . "',
                '" . escape($short_description) . "',
                '" . escape($full_description) . "',
                '" . escape($details) . "',
                '" . escape($keywords) . "',
                '" . escape($image_name) . "',
                '" . escape($price) . "',
                '" . escape($taxable) . "',
                '" . escape($shippable) . "',
                '" . escape($weight) . "',
                '" . escape($primary_weight_points) . "',
                '" . escape($secondary_weight_points) . "',
                '" . escape($default_quantity) . "',
                '" . escape($minimum_quantity) . "',
                '" . escape($maximum_quantity) . "',
                '" . escape($title) . "',
                '" . escape($meta_description) . "',
                '" . escape($meta_keywords) . "',
                '" . escape($inventory) . "',
                '" . escape($inventory_quantity) . "',
                '" . escape($selection_type) . "',
                '" . escape($gtin) . "',
                '" . escape($brand) . "',
                '',
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");

        $product_id = db_value("SELECT LAST_INSERT_ID()");

        // Determine address_name source.
        if ($short_description != '') {
            $address_name_src = $short_description;
        } else {
            $address_name_src = $name;
        }

        $address_name = prepare_catalog_item_address_name($address_name_src, $product_id);

        db("UPDATE products SET address_name = '" . escape($address_name) . "' WHERE id = '$product_id'");

        // Insert into product group.
        db(
            "INSERT INTO products_groups_xref (
                product,
                product_group)
            VALUES (
                '$product_id',
                '$group_id')");

        // Insert attribute xref rows.
        if (!empty($variant['attributes']) && is_array($variant['attributes'])) {
            $attrs = $variant['attributes'];
            $sort_order = 0;
            foreach ($attrs as $attr) {
                $sort_order++;
                db(
                    "INSERT INTO products_attributes_xref (
                        product_id,
                        attribute_id,
                        option_id,
                        sort_order)
                    VALUES (
                        '$product_id',
                        '" . e($attr['attribute_id']) . "',
                        '" . e($attr['option_id']) . "',
                        '$sort_order')");
            }
        }

        // Insert extra images (index 1+) into products_images_xref.
        $extra_images = array_slice($variant_images, 1);
        foreach ($extra_images as $extra_image) {
            db("INSERT INTO products_images_xref (product, file_name) VALUES ('$product_id', '" . e($extra_image) . "')");
        }

        // Insert common zone xref rows (same for all products).
        foreach ($common_zones as $zone_id) {
            $zone_id = (int)$zone_id;
            if ($zone_id) {
                db("INSERT INTO products_zones_xref (product_id, zone_id) VALUES ('$product_id', '$zone_id')");
            }
        }

        $created_count++;
    }

    log_activity(
        lang([
            'string' => '{var:1} variant product{suffix:1} were created in group ({var:2})',
            'vars' => [$created_count, $group_name],
            'suffix' => $created_count == 1 ? '' : 's'
        ]),
        $_SESSION['sessionusername']);

    $liveform->remove_form();
    $liveform_view_products = new liveform('view_products');
    $liveform_view_products->add_notice(
        lang([
            'string' => '{var:1} variant product{suffix:1} were created.',
            'vars' => [$created_count],
            'suffix' => $created_count == 1 ? '' : 's'
        ]));

    // Forward user to view products page.
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_products.php');

}
?>
