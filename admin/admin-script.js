/**
 * Admin Settings JavaScript
 */

jQuery(document).ready(function ($) {
    'use strict';

    // Currency tab switching
    $('.sola-tab-btn').on('click', function () {
        const currency = $(this).data('currency');

        // Update button states
        $('.sola-tab-btn').removeClass('active');
        $(this).addClass('active');

        // Update tab content
        $('.sola-tab-content').removeClass('active');
        $(`.sola-tab-content[data-currency="${currency}"]`).addClass('active');
    });
});
