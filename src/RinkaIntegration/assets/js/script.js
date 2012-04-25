function multiSelectCheckAll(o) {
    if (jQuery(o).val() === 'Pažymėti visus') {
        jQuery(o).parent().parent().find('.multiselect input[type=checkbox]').each(function(key, value) {
            jQuery(this).attr('checked', 'checked');
        });
        jQuery(o).val('Atžymėti visus');
    } else {
        jQuery(o).parent().parent().find('.multiselect input[type=checkbox]').each(function(key, value) {
            jQuery(this).attr('checked', '');
        });
        jQuery(o).val('Pažymėti visus');
    }
}