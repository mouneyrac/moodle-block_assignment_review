<?php
// This file is part of Assignment Review plugin for Moodle
//
// Assignment Review plugin for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Assignment Review plugin for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Assignment Review plugin for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block assignment_review is defined here.
 *
 * @package    block_assignment_review
 * @copyright  2016 onwards Church of England {@link http://www.churchofengland.org/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Jerome Mouneyrac <jerome@mouneyrac.com>
 */

require_once($CFG->dirroot . '/comment/lib.php');
require_once($CFG->dirroot . '/blocks/assignment_review/lib.php');

/**
 * assignment_review block.
 *
 * @package    block_assignment_review
 * @copyright  2016 onwards Church of England {@link http://www.churchofengland.org/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Jerome Mouneyrac <jerome@mouneyrac.com>
 */
class block_assignment_review extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_assignment_review');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $PAGE, $COURSE, $CFG;

        if (!has_capability('block/assignment_review:view', $PAGE->context)) {
            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        if (!$CFG->usecomments) {
            $this->content = new stdClass();
            $this->content->text = '';
            if ($this->page->user_is_editing()) {
                $this->content->text = get_string('disabledcomments');
            }
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->config->description['text'])) {
            $desc = '';
        } else {
            $desc = $this->config->description['text'];
        }

        // Description
        $this->content->text = $desc;

        // Markers
        if (empty($CFG->blockassignmentmarkertotal)) {
            $markertotal = DEFAULT_NUMBER_OF_MARKERS;
        } else {
            $markertotal = $CFG->blockassignmentmarkertotal;
        }
        $this->content->text .= '<form id="block_assignment_review_markers" class="block_assignment_review_markers" action="">';
        for ($i = 0; $i < $markertotal; $i++) {
            $configname = 'blockassignmentmarkertext' . $i;
            if (!empty($CFG->{$configname})) {
                $this->content->text .=
                    '<input type="radio" name="blockassignmentmarkershortname" 
                    value="blockassignmentmarkershortname' . $i . ' "> ' . $CFG->{$configname} . '</input><br/>';
            }
        }
        $this->content->text .= '</form>';


        // Comments
        $args = new stdClass;
        $args->context   = $PAGE->context;
        $args->course    = $COURSE;
        $args->area      = 'page_comments';
        $args->itemid    = 0;
        $args->component = 'block_assignment_review';
        $args->linktext  = get_string('showcomments');
        $args->notoggle  = true;
        $args->autostart = true;
        $args->displaycancel = false;
        $comment = new comment($args);
        $comment->set_view_permission(true);
        $comment->set_fullwidth();
        $this->content->text .= $comment->output(true);

        // Issues
        if (empty($CFG->blockassignmentissuetotal)) {
            $issuetotal = DEFAULT_NUMBER_OF_MARKERS;
        } else {
            $issuetotal = $CFG->blockassignmentissuetotal;
        }
        $this->content->text .= '<form id="block_assignment_review_issues" class="block_assignment_review_issues" action="">';
        for ($i = 0; $i < $issuetotal; $i++) {
            $configname = 'blockassignmentissuetext' . $i;
            if (!empty($CFG->{$configname})) {
                $this->content->text .=
                    '<input type="checkbox" name="blockassignmentissueshortname"' . $i . ' 
                    value="blockassignmentissueshortname' . $i . ' "> ' . $CFG->{$configname} . '</input><br/>';
            }
        }
        $this->content->text .= '</form>';

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediatly after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        // config_title is defined in the edit_form.php file.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_assignment_review');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array('all' => true);
    }
}