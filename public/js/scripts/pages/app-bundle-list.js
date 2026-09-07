$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

(function () {
    'use strict';

    const table = $('.bundle-list-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        orderable: false,

        ajax: {
            url: bundlesDataUrl,
            type: 'GET',
            data: function (d) {
                d.search_value = $('#search-bundle-form').val();
                d.status = $('.filter-bundle-status').val();
                d.application_type = $('.filter-bundle-application').val();
                return d;
            }
        },

        columns: [
            {
                data: null,
                defaultContent: '',
                orderable: false,
                render: function (data, type, row) {
                    return row?.action?.can_delete
                        ? `<input type="checkbox" class="form-check-input bundle-checkbox" value="${row.id}">`
                        : '';
                }
            },
            {
                data: 'name',
                orderable: false
            },
            {
                data: 'trigger_data',
                orderable: false,
                render: function (data) {
                    if (!data) return '-';

                    const qty = data.quantity_rule === 'any'
                        ? 'Any qty'
                        : `Min ${data.quantity}`;

                    return `${escapeHtml(data.item_name)}<br><small class="text-muted">${qty}</small>`;
                }
            },
            {
                data: 'rewards_count',
                orderable: false,
                render: data => `${data || 0} item(s)`
            },
            {
                data: 'application_type_data',
                orderable: false,
                render: data => data?.label ?? '-'
            },
            {
                data: 'repeat_type_data',
                orderable: false,
                render: data => data?.label ?? '-'
            },
            {
                data: 'status_data',
                orderable: false,
                render: function (data) {
                    const label = data?.label ?? '-';
                    const cls = label === 'Active'
                        ? 'bg-light-success'
                        : label === 'Expired'
                            ? 'bg-light-danger'
                            : label === 'Scheduled'
                                ? 'bg-light-info'
                                : 'bg-light-secondary';

                    return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
                }
            },
            {
                data: 'id',
                orderable: false,
                render: function (id, type, row) {
                    const buttons = [];

                    if (row?.action?.can_show) {
                        buttons.push(`
                            <a
                                href="#"
                                class="view-bundle-details"
                                data-bs-toggle="modal"
                                data-bs-target="#showBundleModal"
                            >
                                <i data-feather="eye"></i>
                            </a>
                        `);
                    }

                    if (row?.action?.can_edit) {
                        buttons.push(`
                            <a
                                href="#"
                                class="edit-bundle-details"
                                data-bs-toggle="modal"
                                data-bs-target="#editBundleModal"
                            >
                                <i data-feather="edit-3"></i>
                            </a>
                        `);
                    }

                    if (row?.action?.can_delete) {
                        buttons.push(`
                            <a
                                href="#"
                                class="text-danger open-delete-bundle-modal"
                                data-id="${id}"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteBundleModal"
                            >
                                <i data-feather="trash-2"></i>
                            </a>
                        `);
                    }

                    return `<div class="d-flex gap-1 align-items-center">${buttons.join('')}</div>`;
                }
            }
        ],

        order: [[1, 'asc']],

        drawCallback: function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            resetBulkState();
        }
    });

    let searchTimer = null;

    $('#search-bundle-form').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => table.draw(), 300);
    });

    $('#clear-bundle-search').on('click', function () {
        $('#search-bundle-form').val('');
        table.draw();
    });

    $('.filter-bundle-status, .filter-bundle-application')
        .on('change', () => table.draw());

    /*
     * =====================================================================
     * Shared modal form logic
     * =====================================================================
     */

    function initBundleModal($modal) {
        if ($modal.data('bundle-initialized')) {
            return;
        }

        $modal.data('bundle-initialized', true);

        initSelect2($modal);
        bindTrigger($modal);
        bindRewards($modal);
        bindBehavior($modal);

        if (!$modal.find('.bundle-reward-card').length) {
            addReward($modal);
        }

        applyTriggerScope($modal);
        applyTriggerQuantityRule($modal);
        applyApplicationType($modal);
    }

    function initSelect2($root) {
        $root.find('.select2').each(function () {
            const $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            $select.select2({
                dropdownParent: $root,
                width: '100%'
            });
        });
    }

    $('#addBundleModal, #editBundleModal').on('shown.bs.modal', function () {
        initBundleModal($(this));
        initSelect2($(this));
    });

    function bindTrigger($modal) {
        $modal.on('change', '.bundle-trigger-scope', function () {
            applyTriggerScope($modal);
        });

        $modal.on('change', '.bundle-trigger-parent', function () {
            const parentId = $(this).val();
            const $child = $modal.find('.bundle-trigger-child');

            loadProductsByCategory(parentId, $child);
        });

        $modal.on('change', '.bundle-trigger-child, .bundle-trigger-direct', function () {
            refreshTriggerFlow($modal);
        });

        $modal.on('change', '.bundle-trigger-quantity-rule', function () {
            applyTriggerQuantityRule($modal);
        });
    }

    function applyTriggerScope($modal) {
        const scope = $modal.find('.bundle-trigger-scope:checked').val();
        const $with = $modal.find('.bundle-trigger-with-category');
        const $without = $modal.find('.bundle-trigger-without-category');

        if (scope === 'with_category') {
            $with.removeClass('d-none');
            $without.addClass('d-none');

            $with.find('select').prop('disabled', false);

            $without.find('select')
                .prop('disabled', true)
                .val(null)
                .trigger('change');

            return;
        }

        $with.addClass('d-none');
        $without.removeClass('d-none');

        $with.find('select')
            .prop('disabled', true)
            .val(null)
            .trigger('change');

        $without.find('select').prop('disabled', false);
    }

    function applyTriggerQuantityRule($modal) {
        const rule = $modal.find('.bundle-trigger-quantity-rule').val();

        $modal
            .find('.bundle-trigger-quantity-wrapper')
            .toggleClass('d-none', rule !== 'minimum');
    }

    async function refreshTriggerFlow($modal) {
        const scope = $modal.find('.bundle-trigger-scope:checked').val();

        const itemId = scope === 'with_category'
            ? $modal.find('.bundle-trigger-child').val()
            : $modal.find('.bundle-trigger-direct').val();

        const parentId = scope === 'with_category'
            ? $modal.find('.bundle-trigger-parent').val()
            : null;

        renderFlow(
            $modal.find('.bundle-trigger-flow'),
            await fetchItemMeta(scope, itemId, parentId)
        );
    }

    function bindRewards($modal) {
        $modal.on('click', '.bundle-add-reward', function () {
            addReward($modal);
        });

        $modal.on('click', '.bundle-remove-reward', function () {
            const cards = $modal.find('.bundle-reward-card');

            if (cards.length <= 1) {
                Toastify({
                    text: 'A bundle must have at least one reward.',
                    duration: 2500,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#EA5455',
                    close: true
                }).showToast();

                return;
            }

            $(this).closest('.bundle-reward-card').remove();
            renumberRewards($modal);
        });

        $modal.on('change', '.bundle-reward-scope', function () {
            const $card = $(this).closest('.bundle-reward-card');
            applyRewardScope($card);
        });

        $modal.on('change', '.bundle-reward-parent', function () {
            const $card = $(this).closest('.bundle-reward-card');
            const parentId = $(this).val();

            loadProductsByCategory(
                parentId,
                $card.find('.bundle-reward-child')
            );
        });

        $modal.on('change', '.bundle-reward-child, .bundle-reward-direct', async function () {
            const $card = $(this).closest('.bundle-reward-card');
            await refreshRewardFlow($card);
        });

        $modal.on('change', '.bundle-reward-discount-type', function () {
            const $card = $(this).closest('.bundle-reward-card');
            applyRewardDiscountType($card);
        });
    }

    function addReward($modal, data = null) {
        const index = nextRewardIndex($modal);
        const number = $modal.find('.bundle-reward-card').length + 1;

        let html = $modal
            .find('.bundle-reward-template')
            .html()
            .replaceAll('__INDEX__', String(index))
            .replaceAll('__NUMBER__', String(number));

        const $card = $(html);

        $modal.find('.bundle-rewards-container').append($card);

        initSelect2($modal);
        applyRewardScope($card);
        applyRewardDiscountType($card);

        if (data) {
            fillRewardCard($modal, $card, data);
        }
    }

    function nextRewardIndex($modal) {
        const indexes = $modal
            .find('.bundle-reward-card')
            .map(function () {
                return parseInt($(this).attr('data-reward-index'), 10);
            })
            .get()
            .filter(Number.isFinite);

        return indexes.length ? Math.max(...indexes) + 1 : 0;
    }

    function renumberRewards($modal) {
        $modal.find('.bundle-reward-card').each(function (i) {
            $(this).find('.bundle-reward-number').text(i + 1);
        });
    }

    function applyRewardScope($card) {
        const scope = $card.find('.bundle-reward-scope:checked').val();
        const $with = $card.find('.bundle-reward-with-category');
        const $without = $card.find('.bundle-reward-without-category');

        if (scope === 'with_category') {
            $with.removeClass('d-none');
            $without.addClass('d-none');

            $with.find('select').prop('disabled', false);
            $without.find('select')
                .prop('disabled', true)
                .val(null)
                .trigger('change');

            return;
        }

        $with.addClass('d-none');
        $without.removeClass('d-none');

        $with.find('select')
            .prop('disabled', true)
            .val(null)
            .trigger('change');

        $without.find('select').prop('disabled', false);
    }

    function applyRewardDiscountType($card) {
        const type = $card.find('.bundle-reward-discount-type').val();

        $card
            .find('.bundle-reward-discount-wrapper')
            .toggleClass('d-none', type !== 'percentage');

        if (type !== 'percentage') {
            $card.find('.bundle-reward-discount-value').val('');
        }
    }

    async function refreshRewardFlow($card) {
        const scope = $card.find('.bundle-reward-scope:checked').val();

        const itemId = scope === 'with_category'
            ? $card.find('.bundle-reward-child').val()
            : $card.find('.bundle-reward-direct').val();

        const parentId = scope === 'with_category'
            ? $card.find('.bundle-reward-parent').val()
            : null;

        renderFlow(
            $card.find('.bundle-reward-flow'),
            await fetchItemMeta(scope, itemId, parentId)
        );
    }

    function bindBehavior($modal) {
        $modal.on('change', '.bundle-application-type', function () {
            applyApplicationType($modal);
        });
    }

    function applyApplicationType($modal) {
        const type = $modal.find('.bundle-application-type').val();

        $modal
            .find('.bundle-auto-ready-wrapper')
            .toggleClass('d-none', type !== 'automatic');
    }

    /*
     * =====================================================================
     * Product loading + purchase flow
     * =====================================================================
     */

    function loadProductsByCategory(parentId, $child, selectedId = null) {
        $child
            .empty()
            .append(new Option('Loading...', '', false, false))
            .prop('disabled', true)
            .trigger('change');

        if (!parentId) {
            $child
                .empty()
                .append(new Option('Select Category', '', false, false))
                .prop('disabled', false)
                .trigger('change');

            return $.Deferred().resolve().promise();
        }

        return $.ajax({
            url: bundleProductsByCategoriesUrl,
            type: 'POST',
            data: {
                _token: bundleCsrfToken,
                category_ids: [parentId]
            }
        }).done(function (response) {
            const products = response.data || [];

            $child
                .empty()
                .append(new Option('Select Category', '', false, false));

            products.forEach(function (product) {
                const selected =
                    selectedId !== null &&
                    String(product.id) === String(selectedId);

                $child.append(
                    new Option(
                        product.name,
                        product.id,
                        selected,
                        selected
                    )
                );
            });

            $child
                .prop('disabled', false)
                .trigger('change');
        }).fail(function (xhr) {
            console.error('Error loading bundle products', xhr.responseText);

            $child
                .empty()
                .append(new Option('Select Category', '', false, false))
                .prop('disabled', false)
                .trigger('change');
        });
    }

    async function fetchItemMeta(scope, itemId, parentCategoryId) {
        if (!scope || !itemId) {
            return null;
        }

        try {
            const response = await $.ajax({
                url: bundleItemMetaUrl,
                type: 'GET',
                data: {
                    scope,
                    item_id: itemId,
                    parent_category_id: parentCategoryId || ''
                }
            });

            return response?.data ?? null;
        } catch (xhr) {
            return {
                error: xhr?.responseJSON?.message || 'Could not load product flow.'
            };
        }
    }

    function renderFlow($target, meta) {
        if (!meta) {
            $target.addClass('d-none').empty();
            return;
        }

        if (meta.error) {
            $target
                .removeClass('d-none alert-light')
                .addClass('alert-danger')
                .text(meta.error);

            return;
        }

        const flow = meta.flow || {};

        if (!flow.can_purchase) {
            $target
                .removeClass('d-none alert-light')
                .addClass('alert-danger')
                .html(
                    '<strong>Cannot use this item.</strong><br>' +
                    'Both Add To Cart and Customize Design are disabled.'
                );

            return;
        }

        const labels = {
            choose_options: 'Choose Options',
            customize_design: 'Customize Design',
            choose_purchase_path: 'Add To Bundle or Customize Design',
            include: 'Included',
            ready: 'Ready'
        };

        const steps = (flow.steps || [])
            .map(step => labels[step] || step);

        let text = steps.length
            ? steps.join(' → ')
            : 'Ready';

        if (
            flow.requires_custom_design &&
            flow.needs_options
        ) {
            text = 'Choose Options → Customize Design → Included';
        }

        $target
            .removeClass('d-none alert-danger')
            .addClass('alert-light')
            .html(
                `<strong>Customer Flow:</strong><br>${escapeHtml(text)}`
            );
    }

    /*
     * =====================================================================
     * Edit
     * =====================================================================
     */

    $(document).on('click', '.edit-bundle-details', function (e) {
        e.preventDefault();

        const row = table
            .row($(this).closest('tr'))
            .data();

        if (!row) return;

        const $modal = $('#editBundleModal');
        const $form = $('#editBundleForm');

        initBundleModal($modal);

        $form.attr(
            'action',
            bundleUpdateUrlTemplate.replace('__ID__', row.id)
        );

        const names = row.name_translate || {};
        const descriptions = row.description_translate || {};

        $modal.find('#editBundleNameEn').val(names.en || '');
        $modal.find('#editBundleNameAr').val(names.ar || '');

        $modal.find('#editBundleDescriptionEn').val(descriptions.en || '');
        $modal.find('#editBundleDescriptionAr').val(descriptions.ar || '');

        $modal.find('#editBundleStatus').val(
            row.status_data?.value || 'active'
        );

        $modal.find('#editBundleStartAt').val(row.start_at || '');
        $modal.find('#editBundleEndAt').val(row.end_at || '');

        $modal.find('#editShowOnWebsite').prop(
            'checked',
            Boolean(row.show_on_website)
        );

        $modal.find('#editShowOnProductPage').prop(
            'checked',
            Boolean(row.show_on_product_page)
        );

        $modal.find('.bundle-application-type')
            .val(row.application_type_data?.value || 'manual')
            .trigger('change');

        $modal.find('select[name="repeat_type"]')
            .val(row.repeat_type_data?.value || 'once')
            .trigger('change');

        $modal.find('#editAutoAddReadyRewards').prop(
            'checked',
            Boolean(row.auto_add_ready_rewards)
        );

        fillTrigger($modal, row.trigger_data || null);

        $modal.find('.bundle-rewards-container').empty();

        (row.rewards_data || []).forEach(reward => {
            addReward($modal, reward);
        });

        if (!(row.rewards_data || []).length) {
            addReward($modal);
        }

        applyApplicationType($modal);
    });

    async function fillTrigger($modal, data) {
        if (!data) return;

        $modal
            .find(`.bundle-trigger-scope[value="${data.scope}"]`)
            .prop('checked', true)
            .trigger('change');

        if (data.scope === 'with_category') {
            $modal
                .find('.bundle-trigger-parent')
                .val(String(data.parent_category_id))
                .trigger('change.select2');

            await loadProductsByCategory(
                data.parent_category_id,
                $modal.find('.bundle-trigger-child'),
                data.item_id
            );
        } else {
            $modal
                .find('.bundle-trigger-direct')
                .val(String(data.item_id))
                .trigger('change');
        }

        $modal
            .find('.bundle-trigger-quantity-rule')
            .val(data.quantity_rule || 'any')
            .trigger('change');

        $modal
            .find('.bundle-trigger-quantity')
            .val(data.quantity || 1);

        refreshTriggerFlow($modal);
    }

    async function fillRewardCard($modal, $card, data) {
        $card
            .find(`.bundle-reward-scope[value="${data.scope}"]`)
            .prop('checked', true)
            .trigger('change');

        if (data.scope === 'with_category') {
            $card
                .find('.bundle-reward-parent')
                .val(String(data.parent_category_id))
                .trigger('change.select2');

            await loadProductsByCategory(
                data.parent_category_id,
                $card.find('.bundle-reward-child'),
                data.item_id
            );
        } else {
            $card
                .find('.bundle-reward-direct')
                .val(String(data.item_id))
                .trigger('change');
        }

        $card
            .find('input[name$="[quantity]"]')
            .val(data.quantity || 1);

        $card
            .find('.bundle-reward-discount-type')
            .val(data.discount_type || 'free')
            .trigger('change');

        $card
            .find('.bundle-reward-discount-value')
            .val(
                data.discount_type === 'percentage'
                    ? data.discount_value
                    : ''
            );

        $card
            .find('input[name$="[max_discount_amount]"]')
            .val(data.max_discount_amount || '');

        refreshRewardFlow($card);
    }

    /*
     * =====================================================================
     * Show
     * =====================================================================
     */

    $(document).on('click', '.view-bundle-details', function (e) {
        e.preventDefault();

        const row = table
            .row($(this).closest('tr'))
            .data();

        if (!row) return;

        $('#showBundleName').val(row.name || '');

        const trigger = row.trigger_data;

        $('#showBundleTrigger').html(
            trigger
                ? `${escapeHtml(trigger.item_name)} × ${trigger.quantity}`
                : '<span class="text-muted">—</span>'
        );

        const rewards = row.rewards_data || [];

        $('#showBundleRewards').html(
            rewards.length
                ? rewards.map(reward => {
                    const discount = reward.discount_type === 'free'
                        ? 'FREE'
                        : `${reward.discount_value}% OFF`;

                    return `
                        <div class="mb-1">
                            ${escapeHtml(reward.item_name)}
                            × ${reward.quantity}
                            <strong>${escapeHtml(discount)}</strong>
                        </div>
                    `;
                }).join('')
                : '<span class="text-muted">—</span>'
        );

        $('#showBundleApplication').val(
            row.application_type_data?.label || ''
        );

        $('#showBundleRepeat').val(
            row.repeat_type_data?.label || ''
        );
    });

    /*
     * =====================================================================
     * Create / Update
     * =====================================================================
     */

    $('#addBundleForm, #editBundleForm').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $form.find('.saveChangesButton');
        const $loader = $form.find('.saveLoader');
        const $text = $form.find('.btn-text');

        $button.prop('disabled', true);
        $loader.removeClass('d-none');
        $text.addClass('d-none');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false
        }).done(function () {
            Toastify({
                text: $form.attr('id') === 'addBundleForm'
                    ? 'Bundle added successfully!'
                    : 'Bundle updated successfully!',
                duration: 2500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#28C76F',
                close: true
            }).showToast();

            $form.closest('.modal').modal('hide');

            if ($form.attr('id') === 'addBundleForm') {
                resetBundleForm($('#addBundleModal'));
            }

            table.ajax.reload(null, false);
        }).fail(function (xhr) {
            showValidationErrors(xhr);
        }).always(function () {
            $button.prop('disabled', false);
            $loader.addClass('d-none');
            $text.removeClass('d-none');
        });
    });

    function resetBundleForm($modal) {
        const form = $modal.find('form')[0];

        if (form) {
            form.reset();
        }

        $modal.find('.select2')
            .val(null)
            .trigger('change');

        $modal.find('.bundle-rewards-container').empty();
        addReward($modal);

        $modal
            .find('.bundle-trigger-scope[value="with_category"]')
            .prop('checked', true);

        applyTriggerScope($modal);
        applyTriggerQuantityRule($modal);
        applyApplicationType($modal);
    }

    /*
     * =====================================================================
     * Delete
     * =====================================================================
     */

    let deletingBundleId = null;

    $(document).on('click', '.open-delete-bundle-modal', function () {
        deletingBundleId = $(this).data('id');
    });

    $(document).on('submit', '#deleteBundleForm', function (e) {
        e.preventDefault();

        if (!deletingBundleId) return;

        $.ajax({
            url: bundleDeleteUrlTemplate.replace('__ID__', deletingBundleId),
            type: 'DELETE'
        }).done(function () {
            $('#deleteBundleModal').modal('hide');

            Toastify({
                text: 'Bundle deleted successfully!',
                duration: 2500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#28C76F',
                close: true
            }).showToast();

            table.ajax.reload(null, false);
        }).fail(showValidationErrors);
    });

    $(document).on('submit', '#deleteBundlesForm', function (e) {
        e.preventDefault();

        const ids = $('.bundle-checkbox:checked')
            .map(function () {
                return $(this).val();
            })
            .get();

        if (!ids.length) return;

        $.ajax({
            url: bundleBulkDeleteUrl,
            type: 'DELETE',
            data: { ids }
        }).done(function () {
            $('#deleteBundlesModal').modal('hide');

            Toastify({
                text: 'Bundles deleted successfully!',
                duration: 2500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#28C76F',
                close: true
            }).showToast();

            table.ajax.reload(null, false);
        }).fail(showValidationErrors);
    });

    /*
     * =====================================================================
     * Bulk selection
     * =====================================================================
     */

    $('#select-all-bundles').on('change', function () {
        $('.bundle-checkbox').prop('checked', this.checked);
        updateBulkState();
    });

    $(document).on('change', '.bundle-checkbox', function () {
        updateBulkState();
    });

    function updateBulkState() {
        const count = $('.bundle-checkbox:checked').length;

        $('#bundle-selected-count-text').text(
            `${count} bundle${count === 1 ? ' is' : 's are'} selected`
        );

        $('#bundle-bulk-delete-container')
            .toggle(count > 0);
    }

    function resetBulkState() {
        $('#select-all-bundles').prop('checked', false);
        $('#bundle-bulk-delete-container').hide();
    }

    /*
     * =====================================================================
     * Helpers
     * =====================================================================
     */

    function showValidationErrors(xhr) {
        const errors = xhr.responseJSON?.errors || {};

        if (Object.keys(errors).length) {
            Object.values(errors).forEach(messages => {
                Toastify({
                    text: Array.isArray(messages)
                        ? messages[0]
                        : messages,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#EA5455',
                    close: true
                }).showToast();
            });

            return;
        }

        Toastify({
            text: xhr.responseJSON?.message || 'Something went wrong.',
            duration: 4000,
            gravity: 'top',
            position: 'right',
            backgroundColor: '#EA5455',
            close: true
        }).showToast();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
