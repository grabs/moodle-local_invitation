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
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2022 Andreas Grabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// With this module we make sure that cascaded modal boxes and there backdrop have the right z-index.
define(['jquery'], function($) {

    var copyToClipboard = function(elem, id) {
        // Create hidden text element, if it doesn't already exist.
        var targetId = "hiddenCopyText-" + id;

        // Must use a temporary form element for the selection and copy.
        var target = document.getElementById(targetId);
        if (!target) {
            target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = elem.text();

        // Select the content.
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);

        // Copy the selection.
        var succeed;
        try {
            succeed = document.execCommand("copy");
        } catch (e) {
            succeed = false;
        }
        // Restore original focus.
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }

        // Clear temporary content.
        target.textContent = "";

        return succeed;
    };

    return {
        // Initialize our module.
        init: function(buttonid, txtid, id, title, msg) {
            $('#' + buttonid).on('click', function() {
                /* Get the text field */
                copyToClipboard($('#' + txtid), id);

                // Show a popover.
                $('#' + txtid).popover({
                    title: title,
                    content: msg,
                    placement: "top"
                });
                $('#' + txtid).popover('show');
                setTimeout(function() {
                    $('#' + txtid).popover('hide');
                }, 3000);

            });
        }
    };
});