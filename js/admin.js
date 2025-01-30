jQuery(document).ready(function($) {
    // Make list sortable
    $('#sortable-list').sortable({
        handle: '.myadd-item',
        placeholder: 'myadd-placeholder',
        opacity: 0.7
    });

    // Add new item
    $('.add-item').on('click', function() {
        var newItem = $('<div class="myadd-item">' +
            '<input type="text" name="myadd_items[]" value="">' +
            '<button type="button" class="remove-item button">Remove</button>' +
            '</div>');
        $('#sortable-list').append(newItem);
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).parent('.myadd-item').remove();
    });
});