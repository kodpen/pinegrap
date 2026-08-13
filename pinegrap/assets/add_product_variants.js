/* Variant product wizard — add_product_variants.php */
(function ($) {
    'use strict';

    var cfg = window.PinegrapVariants || {};
    var currencySymbol = cfg.currencySymbol || '';

    /* -------------------------------------------------- helpers */

    function escHtml(str) {
        return $('<div>').text(str || '').html();
    }

    function getEditorValue(id) {
        if (window.tinymce && tinymce.get(id)) {
            return tinymce.get(id).getContent();
        }
        var el = document.getElementById(id);
        return el ? el.value : '';
    }

    /* Image tile (thumbnail + hidden input) */
    function buildImageTile(filename) {
        var imgPath = (typeof path !== 'undefined' ? path : '') + (filename || '');
        return '<div class="v-image-tile card d-inline-flex me-1 mb-1 position-relative" style="width:90px;vertical-align:top;">' +
            '<button type="button" class="btn btn-link link-danger p-0 position-absolute top-0 end-0 v-image-remove" ' +
            'style="line-height:1;font-size:.75rem;z-index:1;" tabindex="-1">✕</button>' +
            '<div class="card-body p-1 text-center">' +
            '<img src="' + escHtml(imgPath) + '" class="v-image-thumb" ' +
            'style="height:55px;width:100%;object-fit:contain;" ' +
            'onerror="this.style.display=\'none\'" />' +
            '<div class="text-muted mt-1" title="' + escHtml(filename || '') + '" ' +
            'style="font-size:.65rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">' +
            escHtml(filename || '') + '</div>' +
            '<input type="hidden" class="v-image-input" value="' + escHtml(filename || '') + '" />' +
            '</div></div>';
    }

    /* "+" add tile — opens image picker */
    function buildImageAddTile() {
        return '<div class="v-image-add-tile card d-inline-flex me-1 mb-1" ' +
            'style="width:90px;cursor:pointer;vertical-align:top;" title="Add Image">' +
            '<div class="card-body p-1 d-flex align-items-center justify-content-center" style="height:90px;">' +
            '<span class="bi bi-plus-circle text-secondary" style="font-size:1.5rem;"></span>' +
            '</div></div>';
    }

    /* -------------------------------------------------- software_image_picker patch */

    var _variantPickerTarget  = null;  /* .v-images-list jQuery element */
    var _variantPickerJustSet = false;

    (function () {
        if (typeof window.software_image_picker !== 'function') return;
        var _orig = window.software_image_picker;
        window.software_image_picker = function (props) {
            if (props.initialize !== undefined) {
                /* If not called from a variant button, reset target (product group button) */
                if (!_variantPickerJustSet) {
                    _variantPickerTarget = null;
                }
                _variantPickerJustSet = false;
                _orig.call(this, props);
            } else if (props.return !== undefined && _variantPickerTarget) {
                /* Route selected image to the active variant's image list */
                var filename = decodeURIComponent(props.image_name || '');
                if (filename) {
                    var isDupe = false;
                    _variantPickerTarget.find('.v-image-input').each(function () {
                        if ($(this).val() === filename) { isDupe = true; return false; }
                    });
                    if (!isDupe) {
                        _variantPickerTarget.find('.v-image-add-tile').before(buildImageTile(filename));
                    }
                }
            } else {
                _orig.call(this, props);
            }
        };
    }());

    /* -------------------------------------------------- collectDefaults */

    function collectDefaults() {
        return {
            pg_name:          $('#pg_name').val() || '',
            price:            $('#base_price').val() || '0',
            image_name:       $('input[name="selected_images[]"]').first().val() || '',
            full_description: getEditorValue('pg_full_description'),
            details:          getEditorValue('pg_details'),
            keywords:         $('#pg_keywords').val() || '',
            title:            $('#pg_title').val() || '',
            meta_description: $('#pg_meta_description').val() || ''
        };
    }

    /* -------------------------------------------------- cartesian + sku */

    function cartesianProduct(arrays) {
        if (!arrays || arrays.length === 0) return [[]];
        var result = [[]];
        for (var i = 0; i < arrays.length; i++) {
            var newResult = [];
            for (var j = 0; j < result.length; j++) {
                for (var k = 0; k < arrays[i].length; k++) {
                    newResult.push(result[j].concat([arrays[i][k]]));
                }
            }
            result = newResult;
        }
        return result;
    }

    /* Returns just the combo part (from template + attributes) */
    function generateComboSku(template, combo) {
        var sku = template;
        for (var i = 0; i < combo.length; i++) {
            sku = sku.replace('{' + combo[i].attr_name + '}', combo[i].label);
        }
        if (sku === template && template !== '') return sku;
        return sku !== '' ? sku : combo.map(function (c) { return c.label; }).join('-');
    }

    /* Full SKU = pg_name + comboSku */
    function buildFullSku(pgName, comboSku) {
        return pgName ? (pgName + ' ' + comboSku).trim() : comboSku;
    }

    /* -------------------------------------------------- card renderer */

    function renderVariantCard(index, combo, comboSku, label, defaults) {
        defaults = defaults || {};

        var attrJsonArr = combo.map(function (c) {
            return { attribute_id: c.attr_id, option_id: c.option_id };
        });
        var attrJson = JSON.stringify(attrJsonArr);

        var sku       = buildFullSku(defaults.pg_name || '', comboSku);
        var shortDesc = sku; /* Short description = same as SKU */

        var html =
            '<div class="accordion-item variant-card"' +
            ' data-attr-json=\'' + attrJson.replace(/'/g, '&#39;') + '\'' +
            ' data-combo-sku="' + escHtml(comboSku) + '">' +

            '<h2 class="accordion-header">' +
            '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"' +
            ' data-bs-target="#variant_body_' + index + '">' +
            '<span class="fw-semibold me-2">#' + (index + 1) + '</span> ' + escHtml(label) +
            '</button></h2>' +

            '<div id="variant_body_' + index + '" class="accordion-collapse collapse" data-bs-parent="">' +
            '<div class="accordion-body"><div class="row">' +

            /* SKU */
            '<div class="col-12 col-sm-4 my-2">' +
            '<label class="form-label">Product ID / SKU</label>' +
            '<input type="text" class="form-control v-name" value="' + escHtml(sku) + '" required />' +
            '</div>' +

            /* Short description */
            '<div class="col-12 col-sm-8 my-2">' +
            '<label class="form-label">Short Description</label>' +
            '<input type="text" class="form-control v-short-desc" value="' + escHtml(shortDesc) + '" />' +
            '</div>' +

            /* Price */
            '<div class="col-12 col-sm-4 my-2">' +
            '<label class="form-label">Unit Price</label>' +
            '<div class="input-group">' +
            '<input type="number" step="0.01" min="0" class="form-control v-price"' +
            ' value="' + escHtml(defaults.price || '0') + '" />' +
            '<span class="input-group-text">' + currencySymbol + '</span>' +
            '</div></div>' +

            /* Images — tile picker */
            '<div class="col-12 col-sm-8 my-2">' +
            '<label class="form-label">Images <small class="text-muted fw-normal">(first = cover)</small></label>' +
            '<div class="v-images-list">' +
            (defaults.image_name ? buildImageTile(defaults.image_name) : '') +
            buildImageAddTile() +
            '</div></div>' +

            /* Description & SEO collapsible */
            '<div class="col-12 my-2">' +
            '<button class="btn btn-sm btn-outline-secondary" type="button"' +
            ' data-bs-toggle="collapse" data-bs-target="#desc_seo_' + index + '">' +
            '<span class="bi bi-chevron-down me-1"></span>Description &amp; SEO</button>' +
            '<div class="collapse mt-2" id="desc_seo_' + index + '">' +
            '<div class="row">' +
            '<div class="col-12 my-1"><label class="form-label">Full Description</label>' +
            '<textarea class="form-control v-full-desc" rows="3">' + escHtml(defaults.full_description || '') + '</textarea></div>' +
            '<div class="col-12 my-1"><label class="form-label">Details</label>' +
            '<textarea class="form-control v-details" rows="2">' + escHtml(defaults.details || '') + '</textarea></div>' +
            '<div class="col-12 my-1"><label class="form-label">Keywords</label>' +
            '<input type="text" class="form-control v-keywords" value="' + escHtml(defaults.keywords || '') + '" /></div>' +
            '<div class="col-12 col-sm-6 my-1"><label class="form-label">Web Browser Title</label>' +
            '<input type="text" class="form-control v-title" value="' + escHtml(defaults.title || '') + '" /></div>' +
            '<div class="col-12 col-sm-6 my-1"><label class="form-label">Web Browser Description</label>' +
            '<input type="text" class="form-control v-meta-desc" value="' + escHtml(defaults.meta_description || '') + '" /></div>' +
            '</div></div></div>' +

            '</div></div></div></div>';

        return html;
    }

    /* -------------------------------------------------- generate matrix */

    $('#generate_matrix_btn').on('click', function () {
        var dimensions = {};
        $('.attribute-row').each(function () {
            var attrId   = $(this).data('attr-id');
            var attrName = $(this).data('attr-name');
            var checked  = [];
            $(this).find('.option-checkbox:checked').each(function () {
                checked.push({
                    attr_id:   attrId,
                    attr_name: attrName,
                    option_id: $(this).val(),
                    label:     $(this).data('label')
                });
            });
            if (checked.length > 0) {
                dimensions[attrId] = checked;
            }
        });

        var dimArrays = Object.values(dimensions);
        if (dimArrays.length === 0) {
            alert('Please select at least one attribute and value.');
            return;
        }

        var defaults = collectDefaults();
        var combos   = cartesianProduct(dimArrays);
        var template = $('#sku_template').val().trim();
        var html     = '';

        for (var i = 0; i < combos.length; i++) {
            var combo    = combos[i];
            var label    = combo.map(function (c) { return c.label; }).join(' / ');
            var comboSku = generateComboSku(template, combo);
            html += renderVariantCard(i, combo, comboSku, label, defaults);
        }

        $('#variant_matrix').html(html);
        $('#variant_count_badge').text(combos.length);
        $('#variant_matrix_wrapper').removeClass('d-none');
    });

    /* -------------------------------------------------- live updates */

    /* When pg_name changes, rebuild SKU + short description in all cards */
    $('#pg_name').on('input', function () {
        var pgName = $(this).val();
        $('#variant_matrix .variant-card').each(function () {
            var comboSku = $(this).data('combo-sku') || '';
            var newSku   = buildFullSku(pgName, comboSku);
            $(this).find('.v-name').val(newSku);
            $(this).find('.v-short-desc').val(newSku);
        });
    });

    /* -------------------------------------------------- image picker (variant) */

    /* Open picker for a specific variant card */
    $('#variant_matrix').on('click', '.v-image-add-tile', function () {
        _variantPickerTarget  = $(this).closest('.v-images-list');
        _variantPickerJustSet = true;
        window.software_image_picker({ initialize: true });
    });

    /* Remove an image tile */
    $('#variant_matrix').on('click', '.v-image-remove', function () {
        $(this).closest('.v-image-tile').remove();
    });

    /* -------------------------------------------------- attribute row ordering */

    function updateAttrMoveButtons() {
        var rows = $('.attribute-row');
        rows.find('.attr-move-up, .attr-move-down').prop('disabled', false);
        if (rows.length <= 1) {
            rows.find('.attr-move-up, .attr-move-down').prop('disabled', true);
        } else {
            rows.first().find('.attr-move-up').prop('disabled', true);
            rows.last().find('.attr-move-down').prop('disabled', true);
        }
    }

    $(document).on('click', '.attr-move-up', function () {
        var row = $(this).closest('.attribute-row');
        var prev = row.prev('.attribute-row');
        if (prev.length) { row.insertBefore(prev); updateAttrMoveButtons(); }
    });

    $(document).on('click', '.attr-move-down', function () {
        var row = $(this).closest('.attribute-row');
        var next = row.next('.attribute-row');
        if (next.length) { row.insertAfter(next); updateAttrMoveButtons(); }
    });

    updateAttrMoveButtons();

    /* -------------------------------------------------- apply first image to all */

    $('#apply_image_all').on('click', function () {
        var firstFilenames = [];
        $('#variant_matrix .variant-card').first().find('.v-image-input').each(function () {
            var val = $(this).val().trim();
            if (val) firstFilenames.push(val);
        });
        if (!firstFilenames.length) return;

        $('#variant_matrix .variant-card').each(function (i) {
            if (i === 0) return;
            var list = $(this).find('.v-images-list');
            list.find('.v-image-tile').remove();
            $.each(firstFilenames, function (j, filename) {
                list.find('.v-image-add-tile').before(buildImageTile(filename));
            });
        });
    });

    /* -------------------------------------------------- form submit */

    $('#variant_form').on('submit', function (e) {
        var cards = $('#variant_matrix .variant-card');
        if (cards.length === 0) {
            e.preventDefault();
            alert('Please generate the variant matrix first.');
            return false;
        }

        var allFilled = true;
        cards.each(function () {
            if (!$(this).find('.v-name').val().trim()) {
                allFilled = false;
                return false;
            }
        });
        if (!allFilled) {
            e.preventDefault();
            alert('Please fill in the Product ID / SKU for all variants.');
            return false;
        }

        var variants = [];
        cards.each(function () {
            var card   = $(this);
            var images = [];
            card.find('.v-image-input').each(function () {
                var val = $(this).val().trim();
                if (val) images.push(val);
            });
            variants.push({
                name:              card.find('.v-name').val(),
                short_description: card.find('.v-short-desc').val(),
                price:             card.find('.v-price').val(),
                images:            images,
                full_description:  card.find('.v-full-desc').val(),
                details:           card.find('.v-details').val(),
                keywords:          card.find('.v-keywords').val(),
                title:             card.find('.v-title').val(),
                meta_description:  card.find('.v-meta-desc').val(),
                attributes:        card.data('attrJson')
            });
        });

        $('#variants_json').val(JSON.stringify(variants));

        /* Collect attribute default options + sort order (DOM order = display order) */
        var attrMeta = [];
        $('.attribute-row').each(function () {
            if (!$(this).find('.option-checkbox:checked').length) return;
            attrMeta.push({
                id:                $(this).data('attr-id'),
                default_option_id: $(this).find('.attr-default-select').val() || ''
            });
        });
        $('<input type="hidden" name="attributes_meta_json">')
            .val(JSON.stringify(attrMeta))
            .appendTo('#variant_form');
    });

}(jQuery));
