let updateOutput = function (e) {
    let list = e.length ? e : $(e.target),
        output = list.data('output');
    if (window.JSON) {
        let items = window.JSON.stringify(list.nestable('serialize'));
        doajaxpost('note/sort', 'items=' + items);
    } else {
        output.val('JSON browser support required.');
    }
};

$(document).ready(function () {
    // activate Nestable for list 1
    $('.note__nestable').nestable({
        group: 1
    });
    $('.dd').nestable('collapseAll');
//    updateOutput($('.note__nestable').data('output', $('.note__nestable-output')));

    $(document).on('change', '.note__nestable', updateOutput);

    $(document).on('mouseover', '.note__item-content', function () {
        $('.note__item-menu').addClass('d-none');
        $(this).find('.note__item-menu').removeClass('d-none')
    });

    $(document).on('mouseout', '.note__item-content', function () {
        $('.note__item-menu').addClass('d-none');
    });

    // click expand button
    $(document).on('click', '.dd-item button[data-action="expand"]', function () {
        let item = $(this).closest('.dd-item');
        let ol = item.find('ol:first');
        let id = item.data('id');
        doajaxpost('/note/children/' + id, '', function (e) {
            ol.html(e);
        });
    });

    // click collapse button
    $(document).on('click', '.dd-item button[data-action="collapse"]', function () {
        let ol = $(this).find('.dd-list:first');
        if (!ol.parent().hasClass('dd')) {
            ol.html('');
            let item = ol.closest('.dd-item');
            item.find('[data-action="collapse"]:first').css('display', 'none');
            item.find('[data-action="expand"]:first').css('display', 'block');
        }
    });

    $(document).on('click', '.note-remove__button, .note__item-remove-button', function () {
        if (confirm('Удалить заметку, Вы уверены?')) {
            let note = $(this).closest('.note__item');
            let id = note.data('id');
            doajaxpost('/note/remove/' + id, '', function (e) {
                if (e.success) {
                    toastr.success('Удаление', 'Заметка ' + id + ' успешно удалена');
                    note.remove();
                } else {
                    toastr.error('Удаление', e.error);

                }
            });
        }
    });

    $(document).on('click', '.note__item-show', function () {
        let note = $(this).closest('.note__item');
        let id = note.data('id');
        doajaxpost('/note/show/' + id, '', function (e) {
            $('.note__show-container').html(e);
        });

    });

    $(document).on('click', '.note-search__item-show', function () {
        let note = $(this);
        let id = note.data('id');
        doajaxpost('/note/show/' + id, '', function (e) {
            $('.note__show-container').html(e);
        });

    });


});