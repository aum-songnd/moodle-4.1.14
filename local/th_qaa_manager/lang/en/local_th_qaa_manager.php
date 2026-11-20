<?php
$string['pluginname'] = 'TH QAA Manager';

$string['th_qaa_manager:manage'] = 'Manage Q&A';
$string['th_qaa_manager:approvequestion'] = 'Approve questions';
$string['th_qaa_manager:assign'] = 'Assign questions to collaborators';
$string['th_qaa_manager:answer'] = 'Answer assigned questions';
$string['th_qaa_manager:approveanswer'] = 'Approve collaborator answers';

$string['qaa_manager'] = 'Q&A Manager';
$string['manage'] = 'Q&A Manager';
$string['listquestion'] = 'Question list';
$string['questiondetail'] = 'Question details';

$string['course'] = 'Course';
$string['student'] = 'Student';
$string['assignee'] = 'Collaborator';
$string['status'] = 'Status';
$string['question'] = 'Question';
$string['answer'] = 'Answer';
$string['actions'] = 'Actions';
$string['timecreated'] = 'Created date';

$string['approvequestion'] = 'Approve question';
$string['approve'] = 'Approve';
$string['rejectquestion'] = 'Reject';
$string['assign'] = 'Assign';
$string['submitanswer'] = 'Submit answer';
$string['approveanswer'] = 'Approve answer';
$string['rejectanswer'] = 'Reject answer';

$string['status_new'] = 'Unassigned';
$string['status_approved'] = 'Assign CTV';
$string['status_assigned'] = 'Assigned - Unanswered';
$string['status_answered'] = 'Pending answer approval';
$string['status_closed'] = 'Completed';
$string['status_rejected'] = 'Rejected';

$string['nothingtodisplay'] = 'Nothing to display.';
$string['invalidticket'] = 'Invalid ticket.';
$string['notassignedtoyou'] = 'This question is not assigned to you.';

$string['selectall'] = 'Select all';
$string['bulkapprovequestion'] = 'Approve selected questions';
$string['bulkassign'] = 'Assign selected questions';
$string['assigneeuserid'] = 'Collaborator user ID';
$string['noselectedtickets'] = 'No questions selected.';
$string['invalidassignee'] = 'Invalid collaborator.';
$string['bulkapprovedone'] = '{$a} question(s) approved.';
$string['bulkassigndone'] = '{$a} question(s) assigned.';
$string['confirmbulkapprove'] = 'Are you sure you want to approve the selected questions?';
$string['confirmbulkassign'] = 'Are you sure you want to assign the selected questions?';

$string['selectassignee'] = 'Select collaborator';
$string['noassignees'] = 'No collaborators with answer permission were found.';

$string['confirmapprovequestion'] = 'Are you sure you want to approve this question?';
$string['confirmrejectquestion'] = 'Are you sure you want to reject this question?';
$string['confirmassignquestion'] = 'Are you sure you want to assign this question to the selected collaborator?';
$string['confirmapproveanswer'] = 'Are you sure you want to approve this answer?';
$string['confirmrejectanswer'] = 'Are you sure you want to reject this answer?';

$string['task_weekly_question_notify'] = 'Send weekly new question notifications';

$string['email_weekly_new_questions_subject'] = 'New questions require review';
$string['email_weekly_new_questions_intro'] = 'The following new questions were submitted this week and require administrator review:';

$string['email_assignment_subject'] = 'You have been assigned a new question';
$string['email_assignment_body'] = 'Hello!

You have been assigned to answer a question, please check the Question List and complete the request.
Link: {$a->url}

If you need support, please contact technical support (hotline 0948.293.794 ).
Thank you.';

$string['email_answer_approved_subject'] = 'Answer notification';

$string['email_answer_approved_student_body'] = 'Hello {$a->student}!

Your question has received a response.
Answer quote:
{$a->answer}

Wish you all the best in your studies!
Details at: {$a->studenturl}';

$string['email_answer_approved_ctv_body'] = 'Hello {$a->ctv},

Your answer has been approved by the administrator.

Course: {$a->course}
Student: {$a->student}
Question: {$a->question}

Response:
{$a->answer}

You can review it here:
{$a->ctvurl}';

$string['email_daily_answered_subject'] = 'Notification: Collaborator answered questions';
$string['email_daily_answered_intro'] = 'Hello!
Collaborators have completed answering the questions, please check and complete answer approvals:';
$string['email_daily_answered_outro'] = 'If you need support, please contact technical support (hotline 0948.293.794 ).
Thank you.';

$string['email_recalled_subject'] = 'Notification: Question recalled';
$string['email_recalled_body'] = 'Hello!
The question [{$a->question}] has been recalled because there was no response after {$a->days} days. The question will be reassigned to another collaborator.
If you need support, please contact hotline 0948.293.794. Thank you.';

$string['task_daily_answer_notify'] = 'Send daily answered questions digest to managers';
$string['rejectreason'] = 'Reject reason';
$string['rejectreasonrequired'] = 'Please enter a reject reason.';
$string['questionrejectdone'] = 'The question has been rejected.';
$string['error_answer_too_short'] = 'The answer must be at least 20 characters long (excluding formatting/images).';
$string['cancelrejectq'] = 'Cancel rejection';
$string['confirmcancelrejectq'] = 'Are you sure you want to cancel the rejection of this question and revert it back to pending approval?';
$string['cancelreject_success'] = 'Question rejection successfully cancelled.';
$string['assigneerole'] = 'Role assigned to answer questions';
$string['assigneerole_desc'] = 'Select the system role of users who will be displayed in the question assignment list.';
$string['manageremails'] = 'Manager Notification Emails';
$string['manageremails_desc'] = 'List of email addresses to receive manager notifications, separated by commas.';
$string['autorecalldays'] = 'Recall Threshold (Days)';
$string['autorecalldays_desc'] = 'The number of days to wait before automatically recalling an assignment if it remains unanswered.';
$string['setting_heading_general'] = '1. General Settings';
$string['setting_heading_general_desc'] = 'Configure collaborator role, manager email list, and auto recall days.';
$string['setting_heading_weekly_notify'] = '2. Notification: Questions Needing Assignment';
$string['setting_heading_weekly_notify_desc'] = 'Configure email subject and body for pending question assignment notifications.';
$string['setting_heading_daily_notify'] = '3. Notification: CTV Answered Questions';
$string['setting_heading_daily_notify_desc'] = 'Configure email subject and body for answered question approval notifications.';
$string['email_weekly_subject'] = 'Email Notification Subject for Pending Questions';
$string['email_weekly_subject_desc'] = 'Subject of the email sent to managers when there are questions needing assignment/approval.';
$string['email_weekly_body'] = 'Email Notification Body for Pending Questions';
$string['email_weekly_body_desc'] = 'Body of the notification email. Placeholders supported: {$a->url} (Management page link), {$a->count} (Question count).';
$string['email_daily_subject'] = 'Email Notification Subject for Answered Questions';
$string['email_daily_subject_desc'] = 'Subject of the email sent to managers when CTV answers need approval.';
$string['email_daily_body'] = 'Email Notification Body for Answered Questions';
$string['email_daily_body_desc'] = 'Body of the notification email. Placeholders supported: {$a->url} (Approval page link), {$a->count} (Question count).';
$string['questiontodo'] = 'Questions to process';
$string['questioncompleted'] = 'Answered questions';
$string['status_question_rejected'] = 'Question rejected';
$string['status_answer_rejected'] = 'Answer rejected';
$string['status_recalled'] = 'Recalled';
$string['answerrejectreason'] = 'Answer rejection reason: {$a}';
$string['resubmitanswer'] = 'Edit and resubmit answer';
$string['answercolumn'] = 'Answer';
$string['answercontainimage'] = '[Answer contains image]';
$string['viewdetail'] = 'View details';

$string['tab_qaa_manage'] = 'Q&A Management';
$string['filter_ctv'] = 'Select CTV';
$string['filter_course'] = 'Select course';
$string['filter_time_range'] = 'Select CTV response date range';
$string['filter_startdate'] = 'From date';
$string['filter_enddate'] = 'To date';
$string['error_startdate_after_enddate'] = 'From date cannot be greater than To date.';
$string['filter_status'] = 'Status';
$string['filter_status_all'] = 'All';
$string['filter_status_completed'] = 'Completed';
$string['filter_status_incomplete'] = 'Incomplete';
$string['filter_button'] = 'Filter';
$string['filter_reset'] = 'Reset filters';
$string['col_no'] = 'No.';
$string['col_ctv'] = 'CTV Name';
$string['col_course_name'] = 'Course Name';
$string['col_course_code'] = 'Course Code';
$string['col_question_link'] = 'Question Link';
$string['col_student'] = 'Student';
$string['col_student_username'] = 'AUM Code';
$string['col_date_question'] = 'Question Date';
$string['col_date_assign'] = 'Assignment Time';
$string['col_date_answer'] = 'Answer Date';
$string['col_approval'] = 'Approval';
$string['col_approver'] = 'Approver';
$string['col_delete_assignment'] = 'Delete assignment';
$string['delete_assignment_confirm'] = 'Are you sure you want to delete the assignment for this question?';
$string['delete_assignment_success'] = 'Assignment successfully deleted.';
$string['bulk_delete_assignment_confirm'] = 'Are you sure you want to delete assignments for the selected questions?';
$string['bulk_delete_assignment_success'] = 'Assignments successfully deleted for {$a} question(s).';
$string['status_approved_short'] = 'Approved';
$string['status_unapproved_short'] = 'Pending';
$string['view'] = 'View';

$string['tab_qaa_stats'] = 'Q&A Statistics';
$string['filter_time_range_stats'] = 'Select statistics date range';
$string['stats_total_new'] = 'Total questions';
$string['stats_total_new_desc'] = 'All questions created in the period';
$string['stats_pending_assign'] = 'Pending assignment';
$string['stats_pending_assign_desc'] = 'Not assigned to any CTV yet';
$string['stats_pending_approve'] = 'Pending approval';
$string['stats_pending_approve_desc'] = 'CTV answered, pending approval';
$string['stats_completed'] = 'Completed';
$string['stats_completed_desc'] = 'Approved and sent to student';
$string['stats_auto_recalled'] = 'Auto-recalled';
$string['stats_auto_recalled_desc'] = 'Over {$a} days, no answer from CTV';

$string['col_assigned_count'] = 'Assigned';
$string['col_answered_count'] = 'Answered';
$string['col_pending_count'] = 'Pending approval';
$string['col_completed_count'] = 'Completed';
$string['col_recalled_count'] = 'Recalled';

$string['task_auto_recall_assignments'] = 'Auto-recall question assignments overdue';
$string['col_answer_status'] = 'Answer status';
$string['col_approval_status'] = 'Approval status';
$string['answer_status_unanswered'] = 'Assigned - Unanswered';
$string['answer_status_answered'] = 'Answered';
$string['approval_status_pending'] = 'Pending';
$string['approval_status_approved'] = 'Approved';
$string['approval_status_rejected'] = 'Rejected';
$string['bulkapproveanswer'] = 'Approve selected answers';
$string['confirmbulkapproveanswer'] = 'Are you sure you want to approve the selected answers?';
$string['bulk_approve_answer_success'] = 'Successfully approved {$a} answer(s).';
$string['exportexcel'] = 'Export Excel';
$string['ctv_status_detail'] = 'CTV Status Details';
$string['select_filter_info'] = 'Please select filter options to display data.';
$string['from_ctv'] = 'from CTV: {$a}';
$string['select_course_placeholder'] = 'Select course...';
$string['not_exist'] = 'Not exist';
$string['administrator'] = 'Administrator';
$string['question_content'] = 'Question Content';
$string['col_answer_content'] = 'Answer Content';
$string['col_crm_code'] = 'CRM Code';
$string['count_questions_unit'] = '{$a} question(s)';
$string['show'] = 'View';
$string['items'] = 'items';
$string['search_keyword'] = 'Search keyword';
$string['search_keyword_placeholder'] = 'Enter search keyword';
$string['search_table_placeholder'] = 'Enter search keyword';
$string['search_button'] = 'Search';
$string['assign_success'] = 'Successfully assigned question.';
$string['company'] = 'Company';
$string['filter_company'] = 'Company';
$string['allcompanies'] = 'All companies';
$string['col_company'] = 'Company';
$string['select_company'] = 'Select company';
