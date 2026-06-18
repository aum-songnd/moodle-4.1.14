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

namespace mod_thresource;

/**
 * PHPUnit data generator testcase.
 *
 * @package    mod_thresource
 * @category phpunit
 * @covers \mod_thresource_generator
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generator_test extends \advanced_testcase {

    public function test_generator() {
        global $DB, $SITE;

        $this->resetAfterTest(true);

        // Must be a non-guest user to create thresources.
        $this->setAdminUser();

        // There are 0 thresources initially.
        $this->assertEquals(0, $DB->count_records('thresource'));

        // Create the generator object and do standard checks.
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_thresource');
        $this->assertInstanceOf('mod_thresource_generator', $generator);
        $this->assertEquals('thresource', $generator->get_modulename());

        // Create three instances in the site course.
        $generator->create_instance(array('course' => $SITE->id));
        $generator->create_instance(array('course' => $SITE->id));
        $thresource = $generator->create_instance(array('course' => $SITE->id));
        $this->assertEquals(3, $DB->count_records('thresource'));

        // Check the course-module is correct.
        $cm = get_coursemodule_from_instance('thresource', $thresource->id);
        $this->assertEquals($thresource->id, $cm->instance);
        $this->assertEquals('thresource', $cm->modname);
        $this->assertEquals($SITE->id, $cm->course);

        // Check the context is correct.
        $context = \context_module::instance($cm->id);
        $this->assertEquals($thresource->cmid, $context->instanceid);

        // Check that generated thresource module contains a file.
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_thresource', 'content', false, '', false);
        $file = array_values($files)[0];
        $this->assertCount(1, $files);
        $this->assertEquals('thresource3.txt', $file->get_filename());
        $this->assertEquals('Test thresource thresource3.txt file', $file->get_content());

        // Create a new thresource specifying the file name.
        $thresource = $generator->create_instance(['course' => $SITE->id, 'defaultfilename' => 'myfile.pdf']);

        // Check that generated thresource module contains a file with the specified name.
        $cm = get_coursemodule_from_instance('thresource', $thresource->id);
        $context = \context_module::instance($cm->id);
        $files = $fs->get_area_files($context->id, 'mod_thresource', 'content', false, '', false);
        $file = array_values($files)[0];
        $this->assertCount(1, $files);
        $this->assertEquals('myfile.pdf', $file->get_filename());
        $this->assertEquals('Test thresource myfile.pdf file', $file->get_content());

        // Create a new thresource uploading a file.
        $thresource = $generator->create_instance([
            'course' => $SITE->id,
            'uploaded' => true,
            'defaultfilename' => 'mod/thresource/tests/fixtures/samplefile.txt',
        ]);

        // Check that generated thresource module contains the uploaded samplefile.txt.
        $cm = get_coursemodule_from_instance('thresource', $thresource->id);
        $context = \context_module::instance($cm->id);
        $files = $fs->get_area_files($context->id, 'mod_thresource', 'content', false, '', false);
        $file = array_values($files)[0];
        $this->assertCount(1, $files);
        $this->assertEquals('samplefile.txt', $file->get_filename());
        $this->assertEquals('Hello!', $file->get_content());

        // Try to generate a thresource with uploaded file without specifying the file.
        try {
            $thresource = $generator->create_instance([
                'course' => $SITE->id,
                'uploaded' => true,
            ]);
            $this->assertTrue(false, 'coding_exception expected, defaultfilename is required');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\coding_exception::class, $e);
            $this->assertStringContainsString('defaultfilename option is required', $e->getMessage());
        }

        // Try to generate a thresource with uploaded file pointing to non-existing file.
        try {
            $thresource = $generator->create_instance([
                'course' => $SITE->id,
                'uploaded' => true,
                'defaultfilename' => 'mod/thresource/tests/fixtures/doesnotexist.txt',
            ]);
            $this->assertTrue(false, 'coding_exception expected, defaultfilename must point to an existing file');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\coding_exception::class, $e);
            $this->assertStringContainsString('defaultfilename option must point to an existing file', $e->getMessage());
        }
    }
}