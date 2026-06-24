<?php
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

namespace local_chemillusion\phpunit;

use local_chemillusion\form\graphical_card_form;

/**
 * Exposes QuickForm for focused editor assertions.
 *
 * @package    local_chemillusion
 */
final class testable_graphical_card_form extends graphical_card_form {
    /**
     * Return the underlying QuickForm instance.
     *
     * @return \MoodleQuickForm
     */
    public function get_quickform(): \MoodleQuickForm {
        return $this->_form;
    }
}
