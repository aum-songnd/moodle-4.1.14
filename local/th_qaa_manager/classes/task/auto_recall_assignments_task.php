<?php
namespace local_th_qaa_manager\task;

defined('MOODLE_INTERNAL') || die();

class auto_recall_assignments_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('task_auto_recall_assignments', 'local_th_qaa_manager');
    }

    public function execute() {
        global $DB;

        // Query tickets that have status STATUS_ASSIGNED and were assigned more than the configured number of days ago.
        // Assigned time is timemodified of the ticket.
        $days = get_config('local_th_qaa_manager', 'auto_recall_days');
        if ($days === false || $days === '') {
            $days = 7;
        } else {
            $days = (int)$days;
        }

        $threshold_time = time() - $days * 24 * 3600;

        $sql = "SELECT id, assigneduserid 
                  FROM {local_th_qaa_ticket}
                 WHERE status = :status_assigned
                   AND assigneduserid IS NOT NULL
                   AND timemodified <= :threshold_time";

        $tickets = $DB->get_records_sql($sql, [
            'status_assigned' => \local_th_qaa_manager\service\ticket_service::STATUS_ASSIGNED,
            'threshold_time' => $threshold_time,
        ]);

        if (empty($tickets)) {
            mtrace("No tickets to auto-recall.");
            return;
        }

        mtrace("Found " . count($tickets) . " tickets to auto-recall.");

        foreach ($tickets as $ticket) {
            try {
                \local_th_qaa_manager\service\ticket_service::auto_recall_assignment($ticket->id);
                mtrace("Successfully recalled ticket ID: {$ticket->id} from CTV ID: {$ticket->assigneduserid}");
            } catch (\Throwable $e) {
                mtrace("Error recalling ticket ID: {$ticket->id}: " . $e->getMessage());
            }
        }
    }
}
