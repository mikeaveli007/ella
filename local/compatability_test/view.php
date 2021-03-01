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
 * Moodle Plugin
 *
 * View file
 *
 * @package    local
 * @subpackage compatability_test
 * @copyright  2014 Chris Clark, LearningWorks Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../config.php');
global $PAGE;

// Set page variables.
$PAGE->set_url('/local/compatability_test/view.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Compatability Test');
$PAGE->navbar->add('Compatability Test');

echo $OUTPUT->header();
?>
	<div class='content'>
		<table class="generaltable">
			<thead>
				<th class="header c0">
					<?php echo get_string('header_1', 'local_compatability_test'); ?>
				</th>
				<th class="header c1">
					<?php echo get_string('header_2', 'local_compatability_test'); ?>
				</th>
				<th class="header c2">
					<?php echo get_string('header_3', 'local_compatability_test'); ?>
				</th>
				<th class="header c3">
					<?php echo get_string('header_4', 'local_compatability_test'); ?>
				</th>
			</thead>
			<tbody id="generaltable">
			</tbody>
		</table>
		<p>
			<?php echo local_compatability_test_admin_feedback(); ?>
		</p>
	</div>
<?php

echo $OUTPUT->footer();
