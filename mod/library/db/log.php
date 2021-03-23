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
 * Definition of log events
 *
 * @package    mod_library
 * @category   log
 * @copyright  2010 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module'=>'library', 'action'=>'add', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'update', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'view', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'view all', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'add entry', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'update entry', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'add category', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'update category', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'delete category', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'add epicver', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'update epicver', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'delete epicver', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'approve entry', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'disapprove entry', 'mtable'=>'library', 'field'=>'name'),
    array('module'=>'library', 'action'=>'view entry', 'mtable'=>'library_entries', 'field'=>'concept'),
);