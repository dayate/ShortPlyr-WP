jQuery(document).ready(function($) {
    'use strict';
    function updateRowIndexes() {
        $('#episode_repeater_tbody .episode-row').each(function(index) {
            $(this).find('input').each(function() {
                var name = $(this).attr('name');
                if (name) { name = name.replace(/\[\d+\]|\[__INDEX__\]/, '[' + index + ']'); $(this).attr('name', name); }
            });
        });
    }
    $('#add_episode_row_button').on('click', function(e) {
        e.preventDefault();
        var newRow = $('#episode_repeater_template').clone();
        newRow.removeAttr('id');
        $('#episode_repeater_tbody').append(newRow);
        updateRowIndexes();
    });
    $('#episode_repeater_tbody').on('click', '.remove-episode-row', function(e) {
        e.preventDefault();
        $(this).closest('.episode-row').remove();
        updateRowIndexes();
    });
});
