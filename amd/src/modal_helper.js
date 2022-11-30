// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * unilabel modal helper
 *
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    return {
        'init': function(modalselector) {
            $(modalselector).on('show.bs.modal', function() {
                $(this).appendTo('body');
            });

            // Hack for stacked modals to show the backdrop with the right z-index.
            $(document).on('show.bs.modal', '.modal', function() {
                var zIndex = 1040 + (10 * $('.modal:visible').length);
                $(this).css('z-index', zIndex);
                setTimeout(function() {
                    $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
                }, 100);
            });

            // Hack to enable stacked modals by making sure the .modal-open class
            // is set to the <body> when there is at least one modal open left.
            $(document).on('hidden.bs.modal', function() {
                if ($('.modal.show').length > 0) {
                    $('body').addClass('modal-open');
                }
            });

        },
        'show': function(modalselector) {
            $(modalselector).modal('show');
        }
    };
});