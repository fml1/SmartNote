$(document).ready(function () {
    $(document).on('show.bs.modal', '#userEditModal', function () {
        let modal = $(this);
        let form = $(this).find('form');
        doajaxget('/note/edit', form.serialize(), function (e) {
            modal.find('.modal-content').html(e);
        });
    });

    $('.note-edit__summernote').on('summernote.blur', function() {
        $('#note_content, #note_attr_content').val(
            $('.note-edit__summernote').summernote('code')
        );
    });

});
