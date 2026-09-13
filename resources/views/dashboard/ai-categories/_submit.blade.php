<script>
    $(document).ready(function () {
        feather.replace();

        const form = $('#ai-product-form');
        const button = $('#submit-button');
        let submitting = false;

        function initStudioQuestionsSelect2() {
            const select = $('#quick-studio-question-ids');

            if (!select.length) {
                return;
            }

            if (typeof $.fn.select2 !== 'function') {
                console.error('Select2 library is not loaded.');
                return;
            }

            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }

            select.select2({
                width: '100%',
                placeholder: 'Select Questions',
                allowClear: true,
                closeOnSelect: false,
                dropdownParent: $('#quick-studio-item-modal')
            });
        }

        $('#quick-studio-item-modal').on('shown.bs.modal', function () {
            initStudioQuestionsSelect2();

            const id = Number($('#quick-studio-item-id').val() || 0);

            if (id && typeof studioItemData !== 'undefined' && studioItemData[id]) {
                $('#quick-studio-question-ids')
                    .val((studioItemData[id].question_ids ?? []).map(String))
                    .trigger('change');
            }
        });

        $('#quick-studio-item-modal').on('hidden.bs.modal', function () {
            const select = $('#quick-studio-question-ids');

            if (select.hasClass('select2-hidden-accessible')) {
                select.val([]).trigger('change');
            }
        });

        function setLoading(state) {
            if (state) {
                if (!button.data('html')) {
                    button.data('html', button.html());
                }

                button.prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm me-50"></span>
                    Saving...
                `);

                return;
            }

            button.prop('disabled', false).html(button.data('html'));
            feather.replace();
        }

        function toast(message, error = true) {
            Toastify({
                text: message,
                duration: 4000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: error ? '#EA5455' : '#28C76F'
            }).showToast();
        }

        function showErrors(xhr) {
            const response = xhr.responseJSON ?? {};

            if (xhr.status === 422 && response.errors) {
                Object.values(response.errors).forEach(messages => {
                    (Array.isArray(messages) ? messages : [messages]).forEach(message => toast(message));
                });

                return;
            }

            toast(response.message ?? 'Something went wrong.');
        }

        form.off('submit.aiProduct').on('submit.aiProduct', function (e) {
            e.preventDefault();

            if (submitting) {
                return;
            }

            submitting = true;
            setLoading(true);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),

                success: function () {
                    toast('AI Product saved successfully.', false);

                    setTimeout(() => {
                        window.location.href = '{{ route('ai-categories.index') }}';
                    }, 500);
                },

                error: function (xhr) {
                    showErrors(xhr);
                    submitting = false;
                    setLoading(false);
                }
            });
        });
    });
</script>
