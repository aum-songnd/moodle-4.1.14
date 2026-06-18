moodle-quiz_thexport
==================

This is a quiz report plugin for [moodle](https://moodle.org/) to thexport quiz attempts as pdf. Single quiz attempt thexports are possible as well as bulk thexports as zip files.
It has been designed to work with mpdf package.

## Dependencies:
* mpdf (https://mpdf.github.io/) must be installed. Run "composer install" in the plugin's path to install it.

## Installation:
* switch to /path/to/moodle/mod/quiz/report/
* execute git clone <this_github_link> thexport
* composer install 
* on moodle page go to "Site Administration" -> "Notifications" and follow the instructions

## Technical Information:
* this plugin saves html, pdf and zip files temporary in the folder returned by [sys_get_temp_dir()](http://www.php.net/manual/en/function.sys-get-temp-dir.php) PHP function.
	* this files are created with [tempnam()](http://www.php.net/manual/en/function.tempnam.php) function
	* and modified with [rename()](http://mx2.php.net/manual/en/function.rename.php) and [chmod()](http://mx2.php.net/manual/en/function.chmod.php)
* zip archives are generated with the [ZipArchive](http://mx2.php.net/manual/en/class.ziparchive.php) class

## Practical information:
* Open a quiz page. Then click on the cog and choose "Quiz thexporting" (or "Thexport de quiz" in french)
* On this page, you can choose the thexport options, then select one (or more) attempts to thexport (it will be downloaded as zip if more than one attempt has been selected)

## Copyright
This plugin is strongly based on [this plugin](https://github.com/elccHTWBerlin/moodle-quiz-thexport). It has been modified to use mpdf instead of wkhtmltopdf.
