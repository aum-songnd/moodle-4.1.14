<?php

defined('MOODLE_INTERNAL') || die();

$observers = array(
	array(
		'eventname' => '\mod_qaa\event\qaapairs_created',
		'callback' => 'mod_qaa_observer::on_question_created',
		'internal' => false, // This means that we get events only after transaction commit.
		'priority' => 1000,
	),

	array(
		'eventname' => '\mod_qaa\event\qaapairs_question_edited',
		'callback' => 'mod_qaa_observer::on_question_updated',
		'internal' => false, // This means that we get events only after transaction commit.
		'priority' => 1000,
	),

	array(
		'eventname' => '\mod_qaa\event\qaapairs_answered',
		'callback' => 'mod_qaa_observer::on_answer_created',
		'internal' => false, // This means that we get events only after transaction commit.
		'priority' => 1000,
	),

	array(
		'eventname' => '\mod_qaa\event\qaapairs_answer_edited',
		'callback' => 'mod_qaa_observer::on_answer_updated',
		'internal' => false, // This means that we get events only after transaction commit.
		'priority' => 1000,
	),

	array(
		'eventname' => '\mod_qaa\event\qaapairs_reject',
		'callback' => 'mod_qaa_observer::on_question_reject',
		'internal' => false, // This means that we get events only after transaction commit.
		'priority' => 1000,
	),
);
