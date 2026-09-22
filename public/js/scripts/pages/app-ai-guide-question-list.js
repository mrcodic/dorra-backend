$(function () {
    'use strict';

    const tableElement = $('.ai-question-list-table');
    const productSelect = $('#filter-ai-category');
    const conditionsModalElement = document.getElementById(
        'question-conditions-modal'
    );

    let productConfig = {};
    let currentConditionPayload = null;
    let conditionRuleIndex = 0;
    let searchTimeout = null;
    let sortSaveTimers = {};

    function toast(message, error = true) {
        Toastify({
            text: message,
            duration: 3500,
            close: true,
            gravity: 'top',
            position: 'right',
            backgroundColor: error ? '#EA5455' : '#28C76F'
        }).showToast();
    }

    function escapeHtml(value) {
        return $('<div>')
            .text(value ?? '')
            .html();
    }

    function selectedAiCategoryId() {
        return Number(
            productSelect.val() || 0
        );
    }

    function routeFor(
        template,
        questionId
    ) {
        return template.replace(
            '__QUESTION_ID__',
            String(questionId)
        );
    }

    function assignmentFor(
        questionId
    ) {
        return (
            productConfig[String(questionId)]
            ?? productConfig[Number(questionId)]
            ?? null
        );
    }

    function refreshProductHint() {
        $('#product-config-hint')
            .toggleClass(
                'd-none',
                !!selectedAiCategoryId()
            );
    }

    function initSelect2() {
        if (
            typeof $.fn.select2
            !== 'function'
        ) {
            return;
        }

        if (
            !productSelect.hasClass(
                'select2-hidden-accessible'
            )
        ) {
            productSelect.select2({
                width: '100%',
                placeholder:
                    'Select AI Product',
                allowClear: true
            });
        }

        $('.condition-answer-select')
            .each(function () {
                const select = $(this);

                if (
                    select.hasClass(
                        'select2-hidden-accessible'
                    )
                ) {
                    return;
                }

                select.select2({
                    width: '100%',
                    placeholder:
                        'Select one or more answers',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent:
                        $('#question-conditions-modal')
                });
            });
    }

    function loadProductConfig(
        done = null
    ) {
        const aiCategoryId =
            selectedAiCategoryId();

        productConfig = {};

        if (!aiCategoryId) {
            refreshProductHint();

            if (
                $.fn.DataTable
                    .isDataTable(
                        tableElement
                    )
            ) {
                tableElement
                    .DataTable()
                    .draw(false);
            }

            if (
                typeof done
                === 'function'
            ) {
                done();
            }

            return;
        }

        $.ajax({
            url:
            aiGuideQuestionProductConfigUrl,

            type: 'GET',

            data: {
                ai_category_id:
                aiCategoryId
            },

            success:
                function (response) {
                    const data =
                        response?.data
                        ?? response
                        ?? {};

                    productConfig =
                        data.assignments
                        ?? data
                        ?? {};
                },

            error:
                function (xhr) {
                    productConfig = {};

                    toast(
                        xhr.responseJSON
                            ?.message
                        ?? 'Unable to load AI Product question configuration.'
                    );
                },

            complete:
                function () {
                    refreshProductHint();

                    if (
                        $.fn.DataTable
                            .isDataTable(
                                tableElement
                            )
                    ) {
                        tableElement
                            .DataTable()
                            .draw(false);
                    }

                    if (
                        typeof done
                        === 'function'
                    ) {
                        done();
                    }
                }
        });
    }

    const dt =
        tableElement.DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            order: [],

            ajax: {
                url:
                aiGuideQuestionsDataUrl,

                type: 'GET',

                data:
                    function (data) {
                        data.search_value =
                            $(
                                '#search-ai-question-form'
                            ).val();

                        data.type =
                            $(
                                '.filter-type'
                            ).val();

                        data.is_active =
                            $(
                                '.filter-status'
                            ).val();
                    }
            },

            columns: [
                {
                    data: 'title',
                    name: 'title',

                    render:
                        function (data) {
                            return `
                                <span class="fw-bolder">
                                    ${escapeHtml(data)}
                                </span>
                            `;
                        }
                },

                {
                    data: 'type_label',
                    name: 'type',
                    orderable: false,
                    searchable: false
                },

                {
                    data: 'prompt_label',
                    name: 'prompt_label',
                    defaultContent: '—',

                    render:
                        function (data) {
                            return data
                                ? escapeHtml(
                                    data
                                )
                                : '—';
                        }
                },

                {
                    data:
                        'options_count',

                    name:
                        'options_count',

                    orderable: false,
                    searchable: false,
                    className:
                        'text-center'
                },

                {
                    data: 'required',
                    name: 'required',
                    orderable: false,
                    searchable: false,
                    className:
                        'text-center',

                    render:
                        function (value) {
                            return value
                                ? `
                                    <span class="badge bg-light-success text-success">
                                        Yes
                                    </span>
                                `
                                : `
                                    <span class="badge bg-light-secondary text-secondary">
                                        No
                                    </span>
                                `;
                        }
                },

                {
                    data: 'id',
                    orderable: false,
                    searchable: false,

                    render:
                        function (
                            questionId
                        ) {
                            const
                                aiCategoryId =
                                    selectedAiCategoryId();

                            if (
                                !aiCategoryId
                            ) {
                                return `
                                    <span class="text-muted">
                                        Select product
                                    </span>
                                `;
                            }

                            const assignment =
                                assignmentFor(
                                    questionId
                                );

                            if (
                                !assignment
                                    ?.attached
                            ) {
                                return `
                                    <span class="badge bg-light-secondary text-secondary">
                                        Not attached
                                    </span>
                                `;
                            }

                            return `
                                <input
                                    type="number"
                                    min="0"
                                    value="${Number(
                                assignment
                                    .sort_order
                                ?? 0
                            )}"
                                    class="form-control form-control-sm product-question-order"
                                    data-question-id="${Number(
                                questionId
                            )}"
                                    style="width:80px"
                                >
                            `;
                        }
                },

                {
                    data: 'id',
                    orderable: false,
                    searchable: false,

                    render:
                        function (
                            questionId
                        ) {
                            const
                                aiCategoryId =
                                    selectedAiCategoryId();

                            if (
                                !aiCategoryId
                            ) {
                                return `
                                    <span class="text-muted">
                                        Select product
                                    </span>
                                `;
                            }

                            const assignment =
                                assignmentFor(
                                    questionId
                                );

                            if (
                                !assignment
                                    ?.attached
                            ) {
                                return `
                                    <span class="badge bg-light-secondary text-secondary">
                                        Not attached
                                    </span>
                                `;
                            }

                            const count =
                                Number(
                                    assignment
                                        .condition_count
                                    ?? 0
                                );

                            const badge =
                                count
                                    ? `
                                        <span class="badge bg-light-warning text-warning ms-50">
                                            ${count}
                                            Rule${count === 1
                                        ? ''
                                        : 's'}
                                        </span>
                                    `
                                    : `
                                        <span class="badge bg-light-secondary text-secondary ms-50">
                                            Always
                                        </span>
                                    `;

                            return `
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary configure-question-conditions"
                                    data-question-id="${Number(
                                questionId
                            )}"
                                >
                                    <i data-feather="git-branch"></i>
                                    Conditional
                                </button>

                                ${badge}
                            `;
                        }
                },

                {
                    data: 'is_active',
                    name: 'is_active',
                    orderable: false,
                    searchable: false,

                    render:
                        function (value) {
                            return value
                                ? `
                                    <span class="badge bg-light-success text-success">
                                        Active
                                    </span>
                                `
                                : `
                                    <span class="badge bg-light-danger text-danger">
                                        Inactive
                                    </span>
                                `;
                        }
                },

                {
                    data: null,
                    orderable: false,
                    searchable: false,

                    render:
                        function (row) {
                            const action =
                                row.action
                                ?? {};

                            const buttons =
                                [];

                            if (
                                action
                                    .can_edit
                            ) {
                                buttons.push(`
                                    <a
                                        href="${aiGuideQuestionsBaseUrl}/${row.id}/edit"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Edit"
                                    >
                                        <i data-feather="edit-2"></i>
                                    </a>
                                `);
                            }

                            if (
                                action
                                    .can_delete
                            ) {
                                buttons.push(`
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger delete-ai-question"
                                        data-id="${row.id}"
                                        title="Delete"
                                    >
                                        <i data-feather="trash-2"></i>
                                    </button>
                                `);
                            }

                            return `
                                <div class="d-flex gap-50">
                                    ${buttons.join('')}
                                </div>
                            `;
                        }
                }
            ],

            drawCallback:
                function () {
                    if (
                        window.feather
                    ) {
                        feather.replace();
                    }
                }
        });

    $('#search-ai-question-form')
        .on(
            'keyup',
            function () {
                clearTimeout(
                    searchTimeout
                );

                searchTimeout =
                    setTimeout(
                        function () {
                            dt.ajax.reload();
                        },
                        300
                    );
            }
        );

    $('#clear-search')
        .on(
            'click',
            function () {
                $(
                    '#search-ai-question-form'
                ).val('');

                dt.ajax.reload();
            }
        );

    $(
        '.filter-type, .filter-status'
    ).on(
        'change',
        function () {
            dt.ajax.reload();
        }
    );

    productSelect.on(
        'change',
        function () {
            loadProductConfig();
        }
    );

    $(document).on(
        'change',
        '.product-question-order',
        function () {
            const input =
                $(this);

            const questionId =
                Number(
                    input.data(
                        'question-id'
                    )
                );

            const aiCategoryId =
                selectedAiCategoryId();

            const sortOrder =
                Number(
                    input.val()
                    || 0
                );

            if (
                !questionId
                || !aiCategoryId
            ) {
                return;
            }

            clearTimeout(
                sortSaveTimers[
                    questionId
                    ]
            );

            sortSaveTimers[
                questionId
                ] = setTimeout(
                function () {
                    input.prop(
                        'disabled',
                        true
                    );

                    $.ajax({
                        url:
                            routeFor(
                                aiGuideQuestionSortOrderUrlTemplate,
                                questionId
                            ),

                        type:
                            'POST',

                        data: {
                            _method:
                                'PUT',

                            _token:
                            csrfToken,

                            ai_category_id:
                            aiCategoryId,

                            sort_order:
                            sortOrder
                        },

                        success:
                            function (
                                response
                            ) {
                                const data =
                                    response?.data
                                    ?? {};

                                const assignment =
                                    assignmentFor(
                                        questionId
                                    );

                                if (
                                    assignment
                                ) {
                                    assignment
                                        .sort_order =
                                        Number(
                                            data
                                                .sort_order
                                            ?? sortOrder
                                        );
                                }

                                toast(
                                    'Question order updated.',
                                    false
                                );
                            },

                        error:
                            function (
                                xhr
                            ) {
                                toast(
                                    xhr
                                        .responseJSON
                                        ?.message
                                    ?? 'Unable to update question order.'
                                );
                            },

                        complete:
                            function () {
                                input.prop(
                                    'disabled',
                                    false
                                );
                            }
                    });
                },
                350
            );
        }
    );

    function parentQuestionOptionsHtml(
        selectedParentId = null
    ) {
        const currentQuestionId =
            Number(
                $(
                    '#condition-question-id'
                ).val()
                || 0
            );

        const parents =
            currentConditionPayload
                ?.parent_questions
            ?? [];

        return parents
            .filter(
                parent =>
                    Number(
                        parent.id
                    )
                    !== currentQuestionId
            )
            .map(
                parent => `
                    <option
                        value="${Number(
                    parent.id
                )}"
                        ${
                    Number(
                        selectedParentId
                    )
                    === Number(
                        parent.id
                    )
                        ? 'selected'
                        : ''
                }
                    >
                        ${escapeHtml(
                    parent.title
                )}
                    </option>
                `
            )
            .join('');
    }

    function answerOptionsHtml(
        parentQuestionId,
        selectedIds = []
    ) {
        const parents =
            currentConditionPayload
                ?.parent_questions
            ?? [];

        const parent =
            parents.find(
                item =>
                    Number(
                        item.id
                    )
                    === Number(
                        parentQuestionId
                    )
            );

        const selected =
            new Set(
                (
                    Array.isArray(
                        selectedIds
                    )
                        ? selectedIds
                        : [selectedIds]
                ).map(String)
            );

        if (!parent) {
            return '';
        }

        return (
            parent.options
            ?? []
        )
            .map(
                option => `
                    <option
                        value="${Number(
                    option.id
                )}"
                        ${
                    selected.has(
                        String(
                            option.id
                        )
                    )
                        ? 'selected'
                        : ''
                }
                    >
                        ${escapeHtml(
                    option.label
                )}
                    </option>
                `
            )
            .join('');
    }

    function buildConditionRule(
        condition = {}
    ) {
        const index =
            conditionRuleIndex++;

        const parentId =
            Number(
                condition
                    .parent_question_id
                || 0
            );

        const optionIds =
            condition
                .parent_option_ids
            ?? [];

        return `
            <div
                class="condition-rule border rounded p-1 mb-1"
                data-index="${index}"
            >
                <div class="row align-items-end">

                    <div class="col-md-5 mb-1">

                        <label class="form-label">
                            Parent Question *
                        </label>

                        <select
                            class="form-select condition-parent-question"
                        >
                            <option value="">
                                Select parent question
                            </option>

                            ${parentQuestionOptionsHtml(
            parentId
        )}
                        </select>

                    </div>

                    <div class="col-md-5 mb-1">

                        <label class="form-label">
                            Answers *
                            <small class="text-muted">
                                (OR)
                            </small>
                        </label>

                        <select
                            class="form-select condition-answer-select"
                            multiple
                            style="width:100%"
                        >
                            ${answerOptionsHtml(
            parentId,
            optionIds
        )}
                        </select>

                    </div>

                    <div class="col-md-2 mb-1">

                        <button
                            type="button"
                            class="btn btn-outline-danger w-100 remove-condition-rule"
                        >
                            <i data-feather="trash-2"></i>
                        </button>

                    </div>

                </div>
            </div>
        `;
    }

    function appendConditionRule(
        condition = {}
    ) {
        $(
            '#condition-rules-container'
        ).append(
            buildConditionRule(
                condition
            )
        );

        initSelect2();

        if (window.feather) {
            feather.replace();
        }
    }

    function resetConditionModal() {
        currentConditionPayload =
            null;

        conditionRuleIndex = 0;

        $(
            '#condition-question-id'
        ).val('');

        $(
            '#condition-question-title'
        ).text('');

        $(
            '#condition-rules-container'
        ).empty();
    }

    $(document).on(
        'click',
        '.configure-question-conditions',
        function () {
            const questionId =
                Number(
                    $(this).data(
                        'question-id'
                    )
                );

            const aiCategoryId =
                selectedAiCategoryId();

            if (
                !questionId
                || !aiCategoryId
            ) {
                toast(
                    'Select an AI Product first.'
                );

                return;
            }

            resetConditionModal();

            $.ajax({
                url:
                    routeFor(
                        aiGuideQuestionConditionsUrlTemplate,
                        questionId
                    ),

                type:
                    'GET',

                data: {
                    ai_category_id:
                    aiCategoryId
                },

                success:
                    function (
                        response
                    ) {
                        currentConditionPayload =
                            response?.data
                            ?? response
                            ?? {};

                        $(
                            '#condition-question-id'
                        ).val(
                            questionId
                        );

                        $(
                            '#condition-question-title'
                        ).text(
                            currentConditionPayload
                                .question
                                ?.title
                            ?? ''
                        );

                        const conditions =
                            currentConditionPayload
                                .conditions
                            ?? [];

                        if (
                            conditions.length
                        ) {
                            conditions
                                .forEach(
                                    appendConditionRule
                                );
                        } else {
                            appendConditionRule();
                        }

                        bootstrap
                            .Modal
                            .getOrCreateInstance(
                                conditionsModalElement
                            )
                            .show();
                    },

                error:
                    function (
                        xhr
                    ) {
                        toast(
                            xhr
                                .responseJSON
                                ?.message
                            ?? 'Unable to load conditional settings.'
                        );
                    }
            });
        }
    );

    $('#add-condition-rule')
        .on(
            'click',
            function () {
                appendConditionRule();
            }
        );

    $(document).on(
        'click',
        '.remove-condition-rule',
        function () {
            const rule =
                $(this)
                    .closest(
                        '.condition-rule'
                    );

            const select =
                rule.find(
                    '.condition-answer-select'
                );

            if (
                select.hasClass(
                    'select2-hidden-accessible'
                )
            ) {
                select.select2(
                    'destroy'
                );
            }

            rule.remove();

            if (
                !$(
                    '#condition-rules-container .condition-rule'
                ).length
            ) {
                appendConditionRule();
            }
        }
    );

    $(document).on(
        'change',
        '.condition-parent-question',
        function () {
            const rule =
                $(this)
                    .closest(
                        '.condition-rule'
                    );

            const parentId =
                Number(
                    $(this).val()
                    || 0
                );

            const answerSelect =
                rule.find(
                    '.condition-answer-select'
                );

            if (
                answerSelect.hasClass(
                    'select2-hidden-accessible'
                )
            ) {
                answerSelect
                    .select2(
                        'destroy'
                    );
            }

            answerSelect.html(
                answerOptionsHtml(
                    parentId,
                    []
                )
            );

            initSelect2();
        }
    );

    function collectConditionRules() {
        const rules = [];
        let error = null;

        $(
            '#condition-rules-container .condition-rule'
        ).each(
            function () {
                if (error) {
                    return;
                }

                const rule =
                    $(this);

                const parentQuestionId =
                    Number(
                        rule
                            .find(
                                '.condition-parent-question'
                            )
                            .val()
                        || 0
                    );

                const parentOptionIds =
                    (
                        rule
                            .find(
                                '.condition-answer-select'
                            )
                            .val()
                        ?? []
                    )
                        .map(
                            Number
                        )
                        .filter(
                            Boolean
                        );

                if (
                    !parentQuestionId
                    || !parentOptionIds
                        .length
                ) {
                    error =
                        'Choose a parent question and at least one answer for every condition rule.';

                    return;
                }

                rules.push({
                    parent_question_id:
                    parentQuestionId,

                    parent_option_ids:
                    parentOptionIds
                });
            }
        );

        return {
            rules,
            error
        };
    }

    $(
        '#save-question-conditions'
    ).on(
        'click',
        function () {
            const button =
                $(this);

            const questionId =
                Number(
                    $(
                        '#condition-question-id'
                    ).val()
                    || 0
                );

            const aiCategoryId =
                selectedAiCategoryId();

            const collected =
                collectConditionRules();

            if (
                collected.error
            ) {
                toast(
                    collected.error
                );

                return;
            }

            button.prop(
                'disabled',
                true
            );

            $.ajax({
                url:
                    routeFor(
                        aiGuideQuestionConditionsUpdateUrlTemplate,
                        questionId
                    ),

                type:
                    'POST',

                data: {
                    _method:
                        'PUT',

                    _token:
                    csrfToken,

                    ai_category_id:
                    aiCategoryId,

                    conditions:
                    collected.rules
                },

                success:
                    function () {
                        bootstrap
                            .Modal
                            .getOrCreateInstance(
                                conditionsModalElement
                            )
                            .hide();

                        loadProductConfig();

                        toast(
                            'Conditional rules updated.',
                            false
                        );
                    },

                error:
                    function (
                        xhr
                    ) {
                        const response =
                            xhr
                                .responseJSON
                            ?? {};

                        if (
                            xhr.status
                            === 422
                            && response
                                .errors
                        ) {
                            Object
                                .values(
                                    response
                                        .errors
                                )
                                .flat()
                                .forEach(
                                    message =>
                                        toast(
                                            message
                                        )
                                );

                            return;
                        }

                        toast(
                            response
                                .message
                            ?? 'Unable to save conditional rules.'
                        );
                    },

                complete:
                    function () {
                        button.prop(
                            'disabled',
                            false
                        );
                    }
            });
        }
    );

    $(
        '#clear-question-conditions'
    ).on(
        'click',
        function () {
            const questionId =
                Number(
                    $(
                        '#condition-question-id'
                    ).val()
                    || 0
                );

            const aiCategoryId =
                selectedAiCategoryId();

            if (
                !questionId
                || !aiCategoryId
            ) {
                return;
            }

            Swal.fire({
                title:
                    'Clear conditions?',

                text:
                    'This question will always be visible for the selected AI Product.',

                icon:
                    'warning',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Yes, clear',

                cancelButtonText:
                    'Cancel',

                customClass: {
                    confirmButton:
                        'btn btn-danger',

                    cancelButton:
                        'btn btn-outline-secondary ms-1'
                },

                buttonsStyling:
                    false

            }).then(
                result => {
                    if (
                        !result
                            .isConfirmed
                    ) {
                        return;
                    }

                    $.ajax({
                        url:
                            routeFor(
                                aiGuideQuestionConditionsUpdateUrlTemplate,
                                questionId
                            ),

                        type:
                            'POST',

                        data: {
                            _method:
                                'PUT',

                            _token:
                            csrfToken,

                            ai_category_id:
                            aiCategoryId,

                            conditions:
                                []
                        },

                        success:
                            function () {
                                bootstrap
                                    .Modal
                                    .getOrCreateInstance(
                                        conditionsModalElement
                                    )
                                    .hide();

                                loadProductConfig();

                                toast(
                                    'Conditions cleared.',
                                    false
                                );
                            },

                        error:
                            function (
                                xhr
                            ) {
                                toast(
                                    xhr
                                        .responseJSON
                                        ?.message
                                    ?? 'Unable to clear conditions.'
                                );
                            }
                    });
                }
            );
        }
    );

    $(document).on(
        'click',
        '.delete-ai-question',
        function () {
            const id =
                Number(
                    $(this)
                        .data('id')
                );

            Swal.fire({
                title:
                    'Delete question?',

                text:
                    'This action cannot be undone.',

                icon:
                    'warning',

                showCancelButton:
                    true,

                confirmButtonText:
                    'Delete',

                cancelButtonText:
                    'Cancel',

                customClass: {
                    confirmButton:
                        'btn btn-danger',

                    cancelButton:
                        'btn btn-outline-secondary ms-1'
                },

                buttonsStyling:
                    false

            }).then(
                result => {
                    if (
                        !result
                            .isConfirmed
                    ) {
                        return;
                    }

                    $.ajax({
                        url:
                            `${aiGuideQuestionsBaseUrl}/${id}`,

                        type:
                            'POST',

                        data: {
                            _method:
                                'DELETE',

                            _token:
                            csrfToken
                        },

                        success:
                            function () {
                                dt.ajax.reload(
                                    null,
                                    false
                                );

                                loadProductConfig();

                                toast(
                                    'Question deleted successfully.',
                                    false
                                );
                            },

                        error:
                            function (
                                xhr
                            ) {
                                toast(
                                    xhr
                                        .responseJSON
                                        ?.message
                                    ?? 'Unable to delete question.'
                                );
                            }
                    });
                }
            );
        }
    );

    $('#question-conditions-modal')
        .on(
            'hidden.bs.modal',
            function () {
                $(
                    '#condition-rules-container .condition-answer-select'
                ).each(
                    function () {
                        const select =
                            $(this);

                        if (
                            select
                                .hasClass(
                                    'select2-hidden-accessible'
                                )
                        ) {
                            select
                                .select2(
                                    'destroy'
                                );
                        }
                    }
                );

                resetConditionModal();
            }
        );

    initSelect2();
    refreshProductHint();

    if (window.feather) {
        feather.replace();
    }
});
