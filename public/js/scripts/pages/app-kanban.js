$(function () {
    'use strict';

    // ---------- DOM refs ----------
    var kanban, boards = [];
    var kanbanWrapper = $('.kanban-wrapper');
    var sidebar = $('.update-item-sidebar');
    var datePicker = $('#due-date');
    var select2 = $('.select2');
    var commentEditor = $('.comment-editor');
    var addNewForm = $('.add-new-board');
    var addNewInput = $('.add-new-board-input');
    var updateItemSidebar = $('.update-item-sidebar');

    // ---------- asset path ----------
    var assetPath = ($('body').attr('data-framework') === 'laravel')
        ? $('body').attr('data-asset-path')
        : '../../../app-assets/';

    // ---------- helpers ----------
    function renderBoardDropdown() {
        return (
            "<div class='dropdown'>" +
            feather.icons['more-vertical'].toSvg({
                class: 'dropdown-toggle cursor-pointer font-medium-3 me-0',
                id: 'board-dropdown',
                'data-bs-toggle': 'dropdown',
                'aria-haspopup': 'true',
                'aria-expanded': 'false'
            }) +
            "<div class='dropdown-menu dropdown-menu-end' aria-labelledby='board-dropdown'>" +
            "<a class='dropdown-item delete-board' href='#'> " +
            feather.icons['trash'].toSvg({ class: 'font-medium-1 align-middle' }) +
            "<span class='align-middle ms-25'>Delete</span></a>" +
            "<a class='dropdown-item rename-board' href='#'>" +
            feather.icons['edit'].toSvg({ class: 'font-medium-1 align-middle' }) +
            "<span class='align-middle ms-25'>Rename</span></a>" +
            "<a class='dropdown-item archive-board' href='#'>" +
            feather.icons['archive'].toSvg({ class: 'font-medium-1 align-middle' }) +
            "<span class='align-middle ms-25'>Archive</span></a>" +
            '</div>' +
            '</div>'
        );
    }

    function renderDropdown() {
        return (
            "<div class='dropdown item-dropdown px-1'>" +
            feather.icons['more-vertical'].toSvg({
                class: 'dropdown-toggle cursor-pointer me-0 font-medium-1',
                id: 'item-dropdown',
                'data-bs-toggle': 'dropdown',
                'aria-haspopup': 'true',
                'aria-expanded': 'false'
            }) +
            "<div class='dropdown-menu dropdown-menu-end' aria-labelledby='item-dropdown'>" +
            "<a class='dropdown-item copy-link' href='#'>Copy task link</a>" +
            "<a class='dropdown-item duplicate-task' href='#'>Duplicate task</a>" +
            "<a class='dropdown-item delete-task' href='#'>Delete</a>" +
            '</div>' +
            '</div>'
        );
    }

    function renderHeader(color, text) {
        return (
            "<div class='d-flex justify-content-between flex-wrap align-items-center mb-1'>" +
            "<div class='item-badges'>" +
            "<div class='badge rounded-pill badge-light-" + color + "'>" + text + "</div>" +
            "</div>" +
            renderDropdown() +
            "</div>"
        );
    }

    function renderAvatar(images, pullUp, margin, members, size) {
        var $transition = pullUp ? ' pull-up' : '';
        var member = members ? members.split(',') : [];
        if (!images) return '';
        return images.split(',').map(function (img, i, arr) {
            var $margin = (margin && i !== arr.length - 1) ? ' me-' + margin : '';
            return (
                "<li class='avatar kanban-item-avatar" + $transition + $margin + "'" +
                " data-bs-toggle='tooltip' data-bs-placement='top' title='" + (member[i] || '') + "'>" +
                "<img src='" + assetPath + "images/portrait/small/" + img + "' alt='Avatar' height='" + size + "'>" +
                "</li>"
            );
        }).join(' ');
    }

    function renderFooter(attachments, comments, assigned, members) {
        return (
            "<div class='d-flex justify-content-between align-items-center flex-wrap mt-1'>" +
            "<div><span class='align-middle me-50'>" +
            feather.icons['paperclip'].toSvg({ class: 'font-medium-1 align-middle me-25' }) +
            "<span class='attachments align-middle'>" + (attachments || 0) + "</span>" +
            "</span><span class='align-middle'>" +
            feather.icons['message-square'].toSvg({ class: 'font-medium-1 align-middle me-25' }) +
            "<span>" + (comments || 0) + "</span>" +
            "</span></div>" +
            "<ul class='avatar-group mb-0'>" + renderAvatar(assigned, true, 0, members, 28) + "</ul>" +
            "</div>"
        );
    }

    function enhanceRenderedItems() {
        $('.kanban-item').each(function () {
            var $this = $(this);
            var $text = "<span class='kanban-text'>" + $this.text() + "</span>";

            if ($this.attr('data-badge') && $this.attr('data-badge-text')) {
                $this.html(renderHeader($this.attr('data-badge'), $this.attr('data-badge-text')) + $text);
            }

            if ($this.attr('data-comments') || $this.attr('data-due-date') || $this.attr('data-assigned')) {
                $this.append(
                    renderFooter(
                        $this.attr('data-attachments'),
                        $this.attr('data-comments'),
                        $this.attr('data-assigned'),
                        $this.attr('data-members')
                    )
                );
            }

            if ($this.attr('data-image')) {
                $this.html(
                    renderHeader($this.attr('data-badge'), $this.attr('data-badge-text')) +
                    "<img class='img-fluid rounded mb-50' src='" + assetPath + "images/slider/" + $this.attr('data-image') + "' height='32'/>" +
                    $text +
                    renderFooter(
                        $this.attr('data-due-date'),
                        $this.attr('data-comments'),
                        $this.attr('data-assigned'),
                        $this.attr('data-members')
                    )
                );
            }

            $this.on('mouseenter', function () {
                $this.find('.item-dropdown, .item-dropdown .dropdown-menu.show').removeClass('show');
            });
        });

        // Add board dropdowns (only once per header)
        $('.kanban-board-header').each(function () {
            var $h = $(this);
            if (!$h.find('.dropdown').length) $h.append(renderBoardDropdown());
        });

        // Re-render feather icons if needed
        if (window.feather) feather.replace({ width: 16, height: 16 });
    }

    // ---------- init plugins ----------
    addNewInput.toggle();

    if (datePicker.length) {
        datePicker.flatpickr({
            monthSelectorType: 'static',
            altInput: true,
            altFormat: 'j F, Y',
            dateFormat: 'Y-m-d'
        });
    }

    if (select2.length) {
        function renderLabels(option) {
            if (!option.id) return option.text;
            return "<div class='badge " + $(option.element).data('color') + " rounded-pill'>" + option.text + "</div>";
        }
        select2.each(function () {
            var $this = $(this);
            $this.wrap("<div class='position-relative'></div>").select2({
                placeholder: 'Select Label',
                dropdownParent: $this.parent(),
                templateResult: renderLabels,
                templateSelection: renderLabels,
                escapeMarkup: function (es) { return es; }
            });
        });
    }

    if (commentEditor.length) {
        new Quill('.comment-editor', {
            modules: { toolbar: '.comment-toolbar' },
            placeholder: 'Write a Comment... ',
            theme: 'snow'
        });
    }

    // ---------- Kanban init (called after data) ----------
    function initKanban(boardsData) {
        kanban = new jKanban({
            element: '.kanban-wrapper',
            gutter: '15px',
            widthBoard: '250px',
            dragItems: true,
            dragBoards: true,
            addItemButton: true,
            itemAddOptions: {
                enabled: true,
                content: '+ Add New Item',
                class: 'kanban-title-button btn btn-default btn-xs',
                footer: false
            },
            boards: boardsData,

            click: function (el) {
                var $el = $(el);
                var flag = false;

                var title = $el.attr('data-eid') ? $el.find('.kanban-text').text() : $el.text();
                var date = $el.attr('data-due-date');
                var dateObj = new Date();
                var year = dateObj.getFullYear();
                var dateToUse = date
                    ? (date + ', ' + year)
                    : (dateObj.getDate() + ' ' + dateObj.toLocaleString('en', { month: 'long' }) + ', ' + year);

                var label = $el.attr('data-badge-text');
                var avatars = $el.attr('data-assigned');

                if ($el.find('.kanban-item-avatar').length) {
                    $el.find('.kanban-item-avatar').on('click', function (e) { e.stopPropagation(); });
                }
                $(document).one('click', '.item-dropdown', function () { flag = true; });

                setTimeout(function () {
                    if (!flag) sidebar.modal('show');
                }, 50);

                // prepare sidebar fields (no duplicate submit handlers)
                var $form = sidebar.find('.update-item-form');
                $form.off('submit').on('submit', function (e) { e.preventDefault(); sidebar.modal('hide'); });

                sidebar.find('#title').val(title);
                sidebar.find(datePicker).next('.form-control').val(dateToUse);
                sidebar.find(select2).val(label).trigger('change');
                sidebar.find('.assigned').empty().append(
                    renderAvatar(avatars, false, '50', $el.attr('data-members'), 32) +
                    "<li class='avatar avatar-add-member ms-50'><span class='avatar-content'>" +
                    feather.icons['plus'].toSvg({ class: 'avatar-icon' }) +
                    "</span></li>"
                );
            },

            buttonClick: function (el, boardId) {
                var addNew = document.createElement('form');
                addNew.setAttribute('class', 'new-item-form');
                addNew.innerHTML =
                    '<div class="mb-1">' +
                    '<textarea class="form-control add-new-item" rows="2" placeholder="Add Content" required></textarea>' +
                    '</div>' +
                    '<div class="mb-2">' +
                    '<button type="submit" class="btn btn-primary btn-sm me-1">Add</button>' +
                    '<button type="button" class="btn btn-outline-secondary btn-sm cancel-add-item">Cancel</button>' +
                    '</div>';

                kanban.addForm(boardId, addNew);
                addNew.querySelector('.add-new-item').focus();

                addNew.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var val = (addNew.querySelector('.add-new-item').value || '').trim();
                    if (!val) return;
                    var currentBoard = $(".kanban-board[data-id='" + boardId + "']");
                    kanban.addElement(boardId, {
                        title: "<span class='kanban-text'>" + $('<div>').text(val).html() + "</span>",
                        id: boardId + '-' + (currentBoard.find('.kanban-item').length + 1)
                    });
                    currentBoard.find('.kanban-item:last-child .kanban-text').before(renderDropdown());
                    addNew.remove();
                });

                $(document).one('click', '.cancel-add-item', function () {
                    $(addNew).remove();
                });
            },

            dragEl: function (el) {
                $(el).find('.item-dropdown, .item-dropdown .dropdown-menu.show').removeClass('show');
            }
        });

        // Post-init UI
        if (kanbanWrapper.length && typeof PerfectScrollbar !== 'undefined') {
            new PerfectScrollbar(kanbanWrapper[0]);
        }
        $('.kanban-container').append(addNewForm);

        $.each($('.kanban-title-button'), function () {
            $(this).removeClass().addClass('kanban-title-button btn btn-flat-secondary btn-sm ms-50');
            if (window.Waves) { Waves.init(); Waves.attach("[class*='btn-flat-']"); }
        });

        enhanceRenderedItems();
    }

    // ---------- bindings (once) ----------
    // Toggle add new board input
    $('.add-new-btn, .cancel-add-new').on('click', function () {
        addNewInput.toggle();
    });

    // Create board (client-side add; wire to API later if needed)
    addNewForm.on('submit', function (e) {
        e.preventDefault();
        var value = (addNewForm.find('.form-control').val() || '').trim();
        if (!value) return;
        var id = value.replace(/\s+/g, '-').toLowerCase();

        kanban.addBoards([{ id: id, title: value }]);
        $('.kanban-board:last-child .kanban-board-header').append(renderBoardDropdown());

        addNewInput.val('').hide();
        $('.kanban-container').append(addNewForm);

        $.each($('.kanban-title-button'), function () {
            $(this).removeClass().addClass('kanban-title-button btn btn-flat-secondary btn-sm ms-50');
            if (window.Waves) { Waves.init(); Waves.attach("[class*='btn-flat-']"); }
        });
    });

    // Delete board
    $(document).on('click', '.delete-board', function (e) {
        e.preventDefault();
        var id = $(this).closest('.kanban-board').data('id');
        if (id) kanban.removeBoard(id);
    });

    // Delete task
    $(document).on('click', '.dropdown-item.delete-task', function (e) {
        e.preventDefault();
        var id = $(this).closest('.kanban-item').data('eid');
        if (id) kanban.removeElement(id);
    });

    // Sidebar housekeeping
    sidebar.on('hidden.bs.modal', function () {
        var ed = sidebar.find('.ql-editor')[0];
        if (ed) ed.innerHTML = '';
        sidebar.find('.nav-link-activity').removeClass('active');
        sidebar.find('.tab-pane-activity').removeClass('show active');
        sidebar.find('.nav-link-update').addClass('active');
        sidebar.find('.tab-pane-update').addClass('show active');
    });

    sidebar.on('shown.bs.modal', function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    if (updateItemSidebar.length) {
        updateItemSidebar.on('hidden.bs.modal', function () {
            updateItemSidebar.find('.file-attachments').val('');
        });
    }

    // ---------- fetch boards then init ----------
    $.ajax({
        url: '/board',
        method: 'GET',
        dataType: 'json',
        headers: { 'Accept': 'application/json' },
        success: function (data) {
            boards = Array.isArray(data) ? data : [];
            initKanban(boards);
        },
        error: function (xhr) {
            console.error('Failed to load kanban boards:', xhr);
            boards = [];
            initKanban(boards); // render empty lanes so UI isn't blank
        }
    });
});
