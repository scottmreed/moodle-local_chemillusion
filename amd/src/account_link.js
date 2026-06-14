// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Light enhancement for account-link CTAs: confirm before leaving Moodle.
 *
 * @module     local_chemillusion/account_link
 * @copyright  2026 MolLogic / Scott Reed
 * @license    GPL-3.0-or-later
 */
define(['core/notification', 'core/str'], function(Notification, Str) {

    'use strict';

    return {
        /**
         * Wire any anchors marked data-action="chemillusion-link".
         */
        init: function() {
            var links = document.querySelectorAll('[data-action="chemillusion-link"]');
            Array.prototype.forEach.call(links, function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var href = link.getAttribute('href');
                    Str.get_strings([
                        {key: 'link_heading', component: 'local_chemillusion'},
                        {key: 'cta_continue_chemillusion', component: 'local_chemillusion'},
                        {key: 'cancel', component: 'moodle'}
                    ]).then(function(s) {
                        Notification.confirm(s[0], s[1], s[1], s[2], function() {
                            window.location.href = href;
                        });
                        return null;
                    }).catch(function() {
                        window.location.href = href;
                    });
                });
            });
        }
    };
});
