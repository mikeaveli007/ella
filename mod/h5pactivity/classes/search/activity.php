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
// phpcs:disable moodle.Files.RequireLogin.Missing
// phpcs:disable moodle.PHP.ForbiddenFunctions.Found

/**
<<<<<<<< HEAD:auth/tests/behat/logout.php
 * Login end point for Behat tests only.
 *
 * @package    core_auth
 * @category   test
 * @copyright  Andrew Lyons <andrew@nicols.co.uk>
========
 * Search area for mod_h5pactivity activities.
 *
 * @package    mod_h5pactivity
 * @copyright  2022 Carlos Escobedo <carlos@moodle.com>
>>>>>>>> MOODLE_400_STABLE:mod/h5pactivity/classes/search/activity.php
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__.'/../../../config.php');

<<<<<<<< HEAD:auth/tests/behat/logout.php
$behatrunning = defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING;
if (!$behatrunning) {
    redirect(new moodle_url('/login/logout.php'));
}

require_logout();

$login = optional_param('loginpage', 0, PARAM_BOOL);
if ($login) {
    redirect(get_login_url());
} else {
    redirect(new moodle_url('/'));
========
namespace mod_h5pactivity\search;

/**
 * Search area for mod_h5pactivity activities.
 *
 * @package    mod_h5pactivity
 * @copyright  2022 Carlos Escobedo <carlos@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity extends \core_search\base_activity {

    /**
     * Returns true if this area uses file indexing.
     *
     * @return bool
     */
    public function uses_file_indexing() {
        return true;
    }
>>>>>>>> MOODLE_400_STABLE:mod/h5pactivity/classes/search/activity.php
}
