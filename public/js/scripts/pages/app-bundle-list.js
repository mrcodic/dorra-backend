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

                    const qty = data.price_label
                        ? data.price_label
                        : data.quantity_rule === 'any'
                            ? 'Any qty'
                            : `Min ${data.quantity}`;

                    return `${escapeHtml(data.item_name)}<br><small class="text-muted">${escapeHtml(qty)}</small>`;
                }
            },
            {
                data: 'rewards_count',
                orderable: false,
                render: data => `${data || 0} item(s)`
            },
            {
                data: 'repeat_type_data',
                orderable: false,
                render: data => data?.label ?? '-'
            },
            {
                data: 'display_bundle_on_visit',
                orderable: false,
                render: function (data) {
                    const enabled = Boolean(data);
                    const cls = enabled ? 'bg-light-success' : 'bg-light-secondary';
                    const label = enabled ? 'Enabled' : 'Disabled';

                    return `<span class="badge ${cls}">${label}</span>`;
                }
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

    $('.filter-bundle-status').on('change', () => table.draw());

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

        if (!$modal.find('.bundle-reward-card').length) {
            addReward($modal);
        }

        applyTriggerScope($modal);
        applyTriggerQuantityRule($modal);
        resetTriggerPriceOptions($modal);
        applyDisplayBundleOnVisitAvailability($modal, getCurrentModalBundleId($modal));
    }

    function initSelect2($root) {
        $root
            .find('.bundle-select2')
            .filter(function () {
                return !$(this)
                    .closest('.bundle-reward-template')
                    .length;
            })
            .each(function () {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }

                const $modal = $select.closest('.modal');

                $select.select2({
                    dropdownParent: $modal.length ? $modal : $root,
                    width: '100%'
                });
            });
    }

    $('#addBundleModal, #editBundleModal').on('shown.bs.modal', function () {
        const $modal = $(this);

        initBundleModal($modal);
        initSelect2($modal);
        applyDisplayBundleOnVisitAvailability($modal, getCurrentModalBundleId($modal));
    });

    $('#addBundleModal').on('hidden.bs.modal', function () {
        $(this).removeData('current-bundle-id');
    });

    /*
     * =====================================================================
     * Trigger
     * =====================================================================
     */

    function bindTrigger($modal) {
        $modal.on('change', '.bundle-trigger-scope', function () {
            applyTriggerScope($modal);
            resetTriggerPriceOptions($modal);
            clearDuplicateRewardsForCurrentTrigger($modal);
        });

        $modal.on('change', '.bundle-trigger-parent', function () {
            const parentId = $(this).val();
            const $child = $modal.find('.bundle-trigger-child');

            resetTriggerPriceOptions($modal);
            loadProductsByCategory(parentId, $child).done(function () {
                clearDuplicateRewardsForCurrentTrigger($modal);
            });
        });

        $modal.on('change', '.bundle-trigger-child, .bundle-trigger-direct', function () {
            refreshTriggerFlow($modal);
            clearDuplicateRewardsForCurrentTrigger($modal);
        });

        $modal.on('change', '.bundle-trigger-quantity-rule', function () {
            applyTriggerQuantityRule($modal);
        });

        $modal.on('change', '.bundle-trigger-price-option', function () {
            syncTriggerSelectedPriceQuantity($modal);
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

    async function refreshTriggerFlow($modal, selectedPriceId = null) {
        const scope = $modal.find('.bundle-trigger-scope:checked').val();

        const itemId = scope === 'with_category'
            ? $modal.find('.bundle-trigger-child').val()
            : $modal.find('.bundle-trigger-direct').val();

        const parentId = scope === 'with_category'
            ? $modal.find('.bundle-trigger-parent').val()
            : null;

        const meta = await fetchItemMeta(scope, itemId, parentId);

        renderFlow(
            $modal.find('.bundle-trigger-flow'),
            meta
        );

        applyTriggerPriceOptions($modal, meta, selectedPriceId);
    }

    /*
     * =====================================================================
     * Rewards
     * =====================================================================
     */

    function bindRewards($modal) {
        $modal.on('click', '.bundle-add-reward', function () {
            addReward($modal);
        });

        $modal.on('click', '.bundle-remove-reward', function () {
            const cards = $modal.find('.bundle-reward-card');

            if (cards.length <= 1) {
                showErrorToast('A bundle must have at least one reward.');
                return;
            }

            $(this).closest('.bundle-reward-card').remove();
            renumberRewards($modal);
        });

        $modal.on('change', '.bundle-reward-scope', function () {
            const $card = $(this).closest('.bundle-reward-card');

            applyRewardScope($card);
            resetRewardPriceOptions($card);
        });

        $modal.on('change', '.bundle-reward-parent', function () {
            const $card = $(this).closest('.bundle-reward-card');
            const parentId = $(this).val();

            resetRewardPriceOptions($card);

            loadProductsByCategory(
                parentId,
                $card.find('.bundle-reward-child')
            );
        });

        $modal.on('change', '.bundle-reward-child, .bundle-reward-direct', async function () {
            const $changedSelect = $(this);
            const $card = $changedSelect.closest('.bundle-reward-card');

            if (isRewardSameAsTrigger($modal, $card)) {
                showErrorToast('Reward item cannot be the same as trigger item.');
                clearRewardItemSelection($card, $changedSelect);
                return;
            }

            await refreshRewardFlow($card);
        });

        $modal.on('change', '.bundle-reward-price-option', function () {
            const $card = $(this).closest('.bundle-reward-card');

            syncRewardSelectedPriceQuantity($card);
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

        initSelect2($card);
        applyRewardScope($card);
        applyRewardDiscountType($card);
        resetRewardPriceOptions($card);

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

    async function refreshRewardFlow($card, selectedPriceId = null) {
        const scope = $card.find('.bundle-reward-scope:checked').val();

        const itemId = scope === 'with_category'
            ? $card.find('.bundle-reward-child').val()
            : $card.find('.bundle-reward-direct').val();

        const parentId = scope === 'with_category'
            ? $card.find('.bundle-reward-parent').val()
            : null;

        const meta = await fetchItemMeta(scope, itemId, parentId);

        renderFlow(
            $card.find('.bundle-reward-flow'),
            meta
        );

        applyRewardPriceOptions($card, meta, selectedPriceId);
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
     * Trigger price / quantity options
     * =====================================================================
     */

    function applyTriggerPriceOptions($modal, meta, selectedPriceId = null) {
        const prices = extractPriceOptions(meta);

        const $manualWrapper = $modal.find('.bundle-trigger-manual-quantity-wrapper');
        const $manualInputs = $manualWrapper.find('input, select');
        const $priceWrapper = $modal.find('.bundle-trigger-price-wrapper');
        const $priceSelect = $modal.find('.bundle-trigger-price-option');
        const $priceQuantityRule = $modal.find('.bundle-trigger-price-quantity-rule');
        const $priceQuantity = $modal.find('.bundle-trigger-price-quantity');

        $priceSelect.empty().append(new Option('Select quantity', '', false, false));

        if (!prices.length) {
            $manualWrapper.removeClass('d-none');
            $manualInputs.prop('disabled', false);

            $priceWrapper.addClass('d-none');

            $priceSelect
                .prop('disabled', true)
                .val('')
                .trigger('change.select2');

            $priceQuantityRule.prop('disabled', true);

            $priceQuantity
                .val('')
                .prop('disabled', true);

            applyTriggerQuantityRule($modal);

            return;
        }

        $manualWrapper.addClass('d-none');
        $manualInputs.prop('disabled', true);

        $priceWrapper.removeClass('d-none');

        $priceQuantityRule
            .val('minimum')
            .prop('disabled', false);

        $priceSelect.prop('disabled', false);

        prices.forEach(function (price) {
            const id = String(getPriceOptionId(price));

            if (!id) {
                return;
            }

            const option = new Option(
                formatPriceOptionLabel(price),
                id,
                false,
                false
            );

            $(option).attr(
                'data-quantity',
                getPriceOptionQuantity(price)
            );

            $priceSelect.append(option);
        });

        const firstOptionId = $priceSelect.find('option[value!=""]').first().val();

        const valueToSelect = selectedPriceId
            ? String(selectedPriceId)
            : firstOptionId;

        $priceSelect
            .val(valueToSelect || '')
            .trigger('change')
            .trigger('change.select2');

        syncTriggerSelectedPriceQuantity($modal);
    }
    function syncTriggerSelectedPriceQuantity($modal) {
        const $priceSelect = $modal.find('.bundle-trigger-price-option');
        const $priceQuantity = $modal.find('.bundle-trigger-price-quantity');

        if ($priceSelect.prop('disabled')) {
            $priceQuantity
                .val('')
                .prop('disabled', true);

            return;
        }

        const quantity = $priceSelect
            .find('option:selected')
            .attr('data-quantity');

        $priceQuantity
            .val(quantity || '')
            .prop('disabled', !quantity);
    }
    function resetTriggerPriceOptions($modal) {
        applyTriggerPriceOptions($modal, null);
    }

    /*
     * =====================================================================
     * Reward price / quantity options
     * =====================================================================
     */

    function applyRewardPriceOptions($card, meta, selectedPriceId = null) {
        const prices = extractPriceOptions(meta);

        const $manualWrapper = $card.find('.bundle-reward-manual-quantity-wrapper');
        const $manualQuantity = $card.find('.bundle-reward-quantity');
        const $priceWrapper = $card.find('.bundle-reward-price-wrapper');
        const $priceSelect = $card.find('.bundle-reward-price-option');
        const $priceQuantity = $card.find('.bundle-reward-price-quantity');

        $priceSelect.empty().append(new Option('Select quantity', '', false, false));

        if (!prices.length) {
            $manualWrapper.removeClass('d-none');
            $manualQuantity.prop('disabled', false);

            $priceWrapper.addClass('d-none');

            $priceSelect
                .prop('disabled', true)
                .val('')
                .trigger('change.select2');

            $priceQuantity
                .val('')
                .prop('disabled', true);

            return;
        }

        $manualWrapper.addClass('d-none');
        $manualQuantity.prop('disabled', true);

        $priceWrapper.removeClass('d-none');
        $priceSelect.prop('disabled', false);

        prices.forEach(function (price) {
            const id = String(getPriceOptionId(price));

            if (!id) {
                return;
            }

            const option = new Option(
                formatPriceOptionLabel(price),
                id,
                false,
                false
            );

            $(option).attr(
                'data-quantity',
                getPriceOptionQuantity(price)
            );

            $priceSelect.append(option);
        });

        const firstOptionId = $priceSelect.find('option[value!=""]').first().val();

        const valueToSelect = selectedPriceId
            ? String(selectedPriceId)
            : firstOptionId;

        $priceSelect
            .val(valueToSelect || '')
            .trigger('change')
            .trigger('change.select2');

        syncRewardSelectedPriceQuantity($card);
    }

    function resetRewardPriceOptions($card) {
        applyRewardPriceOptions($card, null);
    }

    function syncRewardSelectedPriceQuantity($card) {
        const $priceSelect = $card.find('.bundle-reward-price-option');
        const $priceQuantity = $card.find('.bundle-reward-price-quantity');

        if ($priceSelect.prop('disabled')) {
            $priceQuantity
                .val('')
                .prop('disabled', true);

            return;
        }

        const quantity = $priceSelect
            .find('option:selected')
            .data('quantity');

        $priceQuantity
            .val(quantity || '')
            .prop('disabled', !quantity);
    }

    function extractPriceOptions(meta) {
        if (!meta || meta.error) {
            return [];
        }

        const possibleLists = [
            meta.prices,
            meta.price_options,
            meta.available_prices,
            meta.flow?.prices,
            meta.flow?.price_options
        ];

        const prices = possibleLists.find(Array.isArray);

        return prices || [];
    }

    function getPriceOptionId(price) {
        return price.id ?? price.price_id ?? price.value ?? '';
    }

    function getPriceOptionQuantity(price) {
        return price.quantity
            ?? price.qty
            ?? price.min_quantity
            ?? price.pieces
            ?? price.count
            ?? '';
    }

    function formatPriceOptionLabel(price) {
        const quantity = getPriceOptionQuantity(price);

        const label = price.label
            ?? price.name
            ?? price.title
            ?? (quantity ? `${quantity} pcs` : null);

        const amount = price.price
            ?? price.amount
            ?? price.value_price
            ?? price.final_price
            ?? null;

        if (label && amount !== null) {
            return `${label} - ${amount}`;
        }

        if (label) {
            return label;
        }

        if (amount !== null) {
            return `${amount}`;
        }

        return `Option #${getPriceOptionId(price)}`;
    }

    /*
     * =====================================================================
     * Duplicate trigger/reward validation
     * =====================================================================
     */

    function getSelectedBundleItemKey(scope, itemId) {
        if (!scope || !itemId) {
            return null;
        }

        return [scope, itemId].join(':');
    }

    function getTriggerSelectedItemKey($modal) {
        const scope = $modal.find('.bundle-trigger-scope:checked').val();

        const itemId = scope === 'with_category'
            ? $modal.find('.bundle-trigger-child').val()
            : $modal.find('.bundle-trigger-direct').val();

        return getSelectedBundleItemKey(scope, itemId);
    }

    function getRewardSelectedItemKey($card) {
        const scope = $card.find('.bundle-reward-scope:checked').val();

        const itemId = scope === 'with_category'
            ? $card.find('.bundle-reward-child').val()
            : $card.find('.bundle-reward-direct').val();

        return getSelectedBundleItemKey(scope, itemId);
    }

    function isRewardSameAsTrigger($modal, $card) {
        const triggerKey = getTriggerSelectedItemKey($modal);
        const rewardKey = getRewardSelectedItemKey($card);

        return Boolean(triggerKey && rewardKey && triggerKey === rewardKey);
    }

    function clearRewardItemSelection($card, $changedSelect = null) {
        const $select = $changedSelect && $changedSelect.length
            ? $changedSelect
            : $card.find('.bundle-reward-child, .bundle-reward-direct').filter(':enabled');

        $select
            .val(null)
            .trigger('change.select2');

        resetRewardPriceOptions($card);
        $card.find('.bundle-reward-flow').addClass('d-none').empty();
    }

    function clearDuplicateRewardsForCurrentTrigger($modal) {
        const triggerKey = getTriggerSelectedItemKey($modal);

        if (!triggerKey) {
            return;
        }

        let foundDuplicate = false;

        $modal.find('.bundle-reward-card').each(function () {
            const $card = $(this);
            const rewardKey = getRewardSelectedItemKey($card);

            if (rewardKey && rewardKey === triggerKey) {
                clearRewardItemSelection($card);
                foundDuplicate = true;
            }
        });

        if (foundDuplicate) {
            showErrorToast('Reward item cannot be the same as trigger item. Duplicate reward selection was removed.');
        }
    }

    function validateNoDuplicateTriggerAndRewards($modal) {
        const triggerKey = getTriggerSelectedItemKey($modal);

        if (!triggerKey) {
            return true;
        }

        let isValid = true;

        $modal.find('.bundle-reward-card').each(function () {
            const $card = $(this);
            const rewardKey = getRewardSelectedItemKey($card);

            if (rewardKey && rewardKey === triggerKey) {
                isValid = false;
                return false;
            }
        });

        if (!isValid) {
            showErrorToast('Reward item cannot be the same as trigger item.');
        }

        return isValid;
    }

    /*
     * =====================================================================
     * Display bundle on visit
     * =====================================================================
     */

    function getDisplayBundleOnVisitBundleId() {
        if (typeof window.bundleDisplayOnVisitBundleId !== 'undefined') {
            return window.bundleDisplayOnVisitBundleId;
        }

        if (typeof bundleDisplayOnVisitBundleId !== 'undefined') {
            return bundleDisplayOnVisitBundleId;
        }

        return null;
    }

    function setDisplayBundleOnVisitBundleId(bundleId) {
        window.bundleDisplayOnVisitBundleId = bundleId || null;
    }

    function getCurrentModalBundleId($modal) {
        return $modal.data('current-bundle-id') || null;
    }

    function applyDisplayBundleOnVisitAvailability($modal, currentBundleId = null) {
        const selectedBundleId = getDisplayBundleOnVisitBundleId();
        const $checkbox = $modal.find('.bundle-display-on-visit');
        const $warning = $modal.find('.bundle-display-on-visit-warning');

        const anotherBundleAlreadySelected =
            selectedBundleId &&
            String(selectedBundleId) !== String(currentBundleId || '');

        if (anotherBundleAlreadySelected) {
            $checkbox
                .prop('checked', false)
                .prop('disabled', true);

            $warning.removeClass('d-none');

            return;
        }

        $checkbox.prop('disabled', false);
        $warning.addClass('d-none');
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

        $modal.data('current-bundle-id', row.id);

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
        ).trigger('change');

        $modal.find('#editBundleStartAt').val(row.start_at || '');
        $modal.find('#editBundleEndAt').val(row.end_at || '');

        $modal.find('.bundle-display-on-visit').prop(
            'checked',
            Boolean(row.display_bundle_on_visit)
        );

        applyDisplayBundleOnVisitAvailability($modal, row.id);

        $modal.find('select[name="repeat_type"]')
            .val(row.repeat_type_data?.value || 'once')
            .trigger('change');

        fillTrigger($modal, row.trigger_data || null);

        $modal.find('.bundle-rewards-container').empty();

        (row.rewards_data || []).forEach(reward => {
            addReward($modal, reward);
        });

        if (!(row.rewards_data || []).length) {
            addReward($modal);
        }
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
                .trigger('change.select2');
        }

        $modal
            .find('.bundle-trigger-quantity-rule')
            .val(data.quantity_rule || 'any')
            .trigger('change');

        $modal
            .find('.bundle-trigger-quantity')
            .val(data.quantity || 1);

        await refreshTriggerFlow(
            $modal,
            data.price_id || data.product_price_id || null
        );
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
                .trigger('change.select2');
        }

        $card
            .find('.bundle-reward-quantity')
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

        await refreshRewardFlow(
            $card,
            data.price_id || data.product_price_id || null
        );
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
                ? `${escapeHtml(trigger.item_name)} × ${escapeHtml(trigger.price_label || trigger.quantity)}`
                : '<span class="text-muted">—</span>'
        );

        const rewards = row.rewards_data || [];

        $('#showBundleRewards').html(
            rewards.length
                ? rewards.map(reward => {
                    const discount = reward.discount_type === 'free'
                        ? 'FREE'
                        : `${reward.discount_value}% OFF`;

                    const quantityLabel = reward.price_label
                        ? reward.price_label
                        : `Qty: ${reward.quantity}`;

                    return `
                        <div class="mb-1">
                            ${escapeHtml(reward.item_name)}
                            <small class="text-muted">${escapeHtml(quantityLabel)}</small>
                            <strong>${escapeHtml(discount)}</strong>
                        </div>
                    `;
                }).join('')
                : '<span class="text-muted">—</span>'
        );

        $('#showBundleDisplayOnVisit').val(
            row.display_bundle_on_visit ? 'Enabled' : 'Disabled'
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
        const $modal = $form.closest('.modal');
        const $button = $form.find('.saveChangesButton');
        const $loader = $form.find('.saveLoader');
        const $text = $form.find('.btn-text');

        $button.prop('disabled', true);
        $loader.removeClass('d-none');
        $text.addClass('d-none');

        if (!validateNoDuplicateTriggerAndRewards($modal)) {
            $button.prop('disabled', false);
            $loader.addClass('d-none');
            $text.removeClass('d-none');
            return;
        }

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false
        }).done(function (response) {
            const currentBundleId = getCurrentModalBundleId($modal) || response?.data?.id || null;
            const displayOnVisitChecked = $modal.find('.bundle-display-on-visit').is(':checked');

            if (displayOnVisitChecked && currentBundleId) {
                setDisplayBundleOnVisitBundleId(currentBundleId);
            } else if (
                currentBundleId &&
                String(getDisplayBundleOnVisitBundleId() || '') === String(currentBundleId)
            ) {
                setDisplayBundleOnVisitBundleId(null);
            }

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

        $modal.removeData('current-bundle-id');

        $modal.find('.bundle-select2')
            .filter(function () {
                return !$(this)
                    .closest('.bundle-reward-template')
                    .length;
            })
            .val(null)
            .trigger('change');

        $modal.find('.bundle-rewards-container').empty();
        addReward($modal);

        $modal
            .find('.bundle-trigger-scope[value="with_category"]')
            .prop('checked', true);

        applyTriggerScope($modal);
        applyTriggerQuantityRule($modal);
        resetTriggerPriceOptions($modal);
        applyDisplayBundleOnVisitAvailability($modal, null);
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
            if (String(getDisplayBundleOnVisitBundleId() || '') === String(deletingBundleId)) {
                setDisplayBundleOnVisitBundleId(null);
            }

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
            if (ids.map(String).includes(String(getDisplayBundleOnVisitBundleId() || ''))) {
                setDisplayBundleOnVisitBundleId(null);
            }

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
                showErrorToast(
                    Array.isArray(messages)
                        ? messages[0]
                        : messages
                );
            });

            return;
        }

        showErrorToast(xhr.responseJSON?.message || 'Something went wrong.');
    }

    function showErrorToast(message) {
        Toastify({
            text: message,
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
