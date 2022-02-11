<?php
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
 * Steps definitions related to local_invitation.
 *
 * @package     local_invitation
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\DriverException as DriverException;

/**
 * Steps definitions related to mod_feedback.
 *
 * @copyright 2022 Andreas Grabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_invitation extends behat_base {
    /**
     * Press tab key on a specific element.
     *
     * @When /^I visit "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)" after logout$/
     * @param string $element Element we look for
     * @param string $selectortype The type of what we look for
     * @throws DriverException
     * @throws ExpectationException
     */
    public function i_visit_after_logout($element, $selectortype) {
        if (!$this->running_javascript()) {
            throw new DriverException('Tab press step is not available with Javascript disabled');
        }
        // Gets the node based on the requested selector type and locator.
        $node = $this->get_selected_node($selectortype, $element);
        $url = $node->getAttribute('href');
        $this->getSession()->visit($url);
    }

}
