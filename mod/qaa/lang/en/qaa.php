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
 * Plugin strings are defined here.
 *
 * @package     mod_qa
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['pluginname'] = 'TH Question and Answer';
$string['modulename'] = 'Question and Answer';
$string['qaa:addinstance'] = 'Add a new question and answer instance';
$string['qaa:view'] = 'Capability QAA View';
$string['qaa:deletequest'] = 'Capability QAA delete question';
$string['qaa:approval'] = 'Capability QAA approval question';
$string['qaa:manage'] = 'QAA Management';
$string['modulenameplural'] = 'Question and Answers';

$string['qaname'] = 'Name';
$string['qaname_help'] = 'Name of the Question and Answer activity';
$string['pluginadministration'] = 'QAA administration';

$string['qaa:makequest'] = 'capability makequest';
$string['addquestion'] = 'Add new question';
$string['addanswer'] = 'Add answer';
$string['editanswer'] = 'Edit answer';
$string['editquestion'] = 'Edit question';
$string['pagetitlequestion'] = 'Question:';
$string['textfieldsquestion'] = 'Add Question';
$string['pagetitleanswer'] = 'Answer:';
$string['textfieldsanswer'] = 'Answer';
$string['submit'] = 'Submit';

$string['question'] = 'Question';
$string['answer'] = 'Answer';

$string['questiondetail'] = 'Question ({$a->student} - {$a->datetime})';
$string['answerdetail'] = 'Answer ({$a->teacher} - {$a->datetime})';

$string['detailmode'] = 'detail question';

$string['answered'] = 'Answered {$a}';
$string['unanswered'] = 'Unanswered {$a}';
$string['week'] = 'Week: {$a->weekbegin_humandate} -> {$a->weekend_humandate}';
$string['strftimedaydate'] = '%A, %d/%m/%Y'; //strftime format

$string['choice'] = 'Choice';
$string['qaasaved'] = 'qaasaved';
$string['members/'] = 'Members';
$string['members/max'] = 'Members / Capacity';
$string['removemyqaa'] = 'removemyqaa';
$string['savemyqaa'] = 'savemyqaa';
$string['postincontext'] = 'See this post in context';
$string['mailsubject'] = '{$a->coursname}: {$a->type} - {$a->subject}';
$string['delete_question'] = 'delete this question';
$string['deleteconfirm'] = 'are you sure want to delete this question?';
$string['sessionerror'] = 'Your session has timed out. Please reload your page!';
$string['deleteerror'] = 'delete error';
$string['min_character'] = 'Min question\'s characters';
$string['min_characterdesc'] = 'This is the min character of student\'s question';
$string['err_questionform'] = 'The number of characters must be at least {$a}';

$string['event_qaapairs_created'] = 'qaapairs question created';
$string['event_qaapairs_reject'] = 'Question rejected';
$string['event_qaapairs_deleted'] = 'qaapairs question deleted';
$string['event_qaapairs_answered'] = 'qaapairs question answered';
$string['event_qaapairs_answer_edited'] = 'qaapairs answer edited';
$string['event_qaapairs_question_edited'] = 'qaapairs question edited';
$string['event_qaapairs_viewed'] = 'qaapairs viewed';

$string['submitnewquestion'] = 'Submit new question';
$string['submitnewanswer'] = 'Submit new answer';

$string['placeholderquestion'] = 'new question...';
$string['placeholderanswer'] = 'new answer...';
$string['monitoremailaddress'] = 'Qaa Monitor email address';

$string['typequestion'] = 'question';
$string['typequestionedited'] = 'question edited';
$string['typeanswer'] = 'answer';
$string['typeansweredited'] = 'answer edited';

$string['maxfileupload'] = 'Max file upload';
$string['qaahelpmaximumupload'] = 'Define the largest size of file that can be uploaded in this qaa, limited by the site-wide setting.';
$string['typefileupload'] = 'Type file upload';
$string['limittypefileupload'] = 'Limit type file upload';
$string['limitmaxfiles'] = 'Limit max files';
$string['limitmaxfilesdesc'] = 'Limit max files upload';
$string['limittimecreatequestion'] = 'Limit time create question';
$string['des_limittimecreatequestion'] = 'Limit time create question(second)';

$string['user_edited_answer'] = 'The user with id {$a->userid} has edited the answer with id {$a->objectid} in the qaa activity with course module id {$a->contextinstanceid}.';
$string['user_answered_question'] = 'The user with id {$a->userid} has answered the question with id {$a->objectid} in the qaa activity with course module id {$a->contextinstanceid}.';
$string['user_created_question'] = 'The user with id {$a->userid} has created a question with id {$a->objectid} in the qaa activity with course module id {$a->contextinstanceid}.<br>
<b>Other:</b> {$a->other}';
$string['user_reject_question'] = 'User with ID {$a->userid} has rejected the question with ID {$a->objectid} in the Q&A activity with module ID {$a->contextinstanceid}.<br><b>Other:</b> {$a->other}';
$string['user_deleted_question'] = 'The user with id {$a->userid} has deleted the question with id {$a->objectid} in the qaa activity with course module id {$a->contextinstanceid}.';
$string['user_edited_question'] = 'The user with id {$a->userid} has edited the question with id {$a->objectid} in the qaa activity with course module id {$a->contextinstanceid}.';
$string['user_viewed_qaa'] = 'The user with id {$a->userid} has viewed the question and answer with id {$a->objectid} in the qaa activity with course module id {$a->contextinstanceid}.';
$string['runtask'] = 'My ad-hoc task started';
$string['question_not_exist_or_no_teacher'] = "The question does not exist or there is no teacher.";
$string['email_send_success'] = 'Email sent successfully to: {$a}';
$string['email_send_fail'] = 'Failed to send email to: {$a}';
$string['enable_sendmailcreatequestion'] = 'Send email to teacher when student creates a question';
$string['des_enable_sendmailcreatequestion'] = 'Enable or disable sending email notification to teacher when a student submits a question.';

$string['creator']    = 'Asker';
$string['discussion'] = 'Question';
$string['latestpost'] = 'Latest post';
$string['replies']    = 'Replies';

$string['approval']           = 'Approve and answer questions';
$string['approvalqueue']      = 'Pending questions';
$string['approveandanswer']   = 'Approve & answer';
$string['youranswer']         = 'Your answer';
$string['rejectwithreason']   = 'Reject & give reason';
$string['reason']             = 'Rejection reason';
$string['approvequestion']    = 'Approve and answer questions';
$string['rejectquestion']     = 'Reject question';
$string['rejectreason']       = 'Rejection reason';
$string['rejected']           = 'Rejected';

$string['confirmdeletequestion'] = 'Are you sure you want to delete this question?';
$string['deletewarning'] = 'This action cannot be undone.';
$string['deletechildwarning'] = '⚠️ This question has replies. Deleting it will also remove all related replies.';
$string['timecreated'] = 'Created time';
$string['err_content_too_short'] = 'The content is too short (minimum {$a} characters).';
// mail
$string['typereply']   = 'New reply';
$string['replyinline'] = 'There is a new reply in the thread';
$string['reply'] = 'Reply';
$string['unansweredquestions'] = 'Unanswered questions';
$string['typequestionapproved'] = 'Question approved';
$string['questionapproved'] = 'Your question has been approved';
$string['typequestionreject'] = 'Question rejected';
$string['questionreject'] = 'Your question has been rejected';
$string['tag_pending'] = '🕓 Pending approval';
$string['rejected_tag'] = 'Rejected';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['badge_author'] = 'Author';
$string['question_close_time'] = 'Q&A closing time';