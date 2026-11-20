<?php
namespace local_th_qaa_manager\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class qaa_stats_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        // Form method GET is better for filters so URLs can be bookmarked/shared.
        $mform->disable_form_change_checker();
        $this->_form->setAttributes([
            'method' => 'get',
        ]);

        // Hidden fields.
        $mform->addElement('hidden', 'status', 20); // STATUS_QAA_STATS
        $mform->setType('status', PARAM_INT);

        // Date range.
        $mform->addElement('date_selector', 'stats_startdate', get_string('filter_startdate', 'local_th_qaa_manager'));
        $mform->setType('stats_startdate', PARAM_INT);

        $mform->addElement('date_selector', 'stats_enddate', get_string('filter_enddate', 'local_th_qaa_manager'));
        $mform->setType('stats_enddate', PARAM_INT);

        $this->add_action_buttons(false, get_string('submit'));

        $js = "
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.querySelector('form[action*=\"status=20\"], form:has(input[name=\"status\"][value=\"20\"])');
                if (!form) {
                    var input = document.querySelector('input[name=\"status\"][value=\"20\"]');
                    if (input) form = input.closest('form');
                }
                if (!form) return;

                form.addEventListener('submit', function(e) {
                    var sDay = parseInt(form.querySelector('select[name=\"stats_startdate[day]\"]').value, 10);
                    var sMonth = parseInt(form.querySelector('select[name=\"stats_startdate[month]\"]').value, 10) - 1;
                    var sYear = parseInt(form.querySelector('select[name=\"stats_startdate[year]\"]').value, 10);

                    var eDay = parseInt(form.querySelector('select[name=\"stats_enddate[day]\"]').value, 10);
                    var eMonth = parseInt(form.querySelector('select[name=\"stats_enddate[month]\"]').value, 10) - 1;
                    var eYear = parseInt(form.querySelector('select[name=\"stats_enddate[year]\"]').value, 10);

                    var startDate = new Date(sYear, sMonth, sDay, 0, 0, 0);
                    var endDate = new Date(eYear, eMonth, eDay, 23, 59, 59);

                    if (startDate > endDate) {
                        e.preventDefault();
                        e.stopPropagation();
                        alert('" . addslashes(get_string('error_startdate_after_enddate', 'local_th_qaa_manager')) . "');
                        return false;
                    }
                });
            });
        ";
        $mform->addElement('html', \html_writer::script($js));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $startts = 0;
        $endts = 0;

        if (!empty($data['stats_startdate'])) {
            if (is_array($data['stats_startdate'])) {
                $startts = make_timestamp(
                    (int)($data['stats_startdate']['year'] ?? 0),
                    (int)($data['stats_startdate']['month'] ?? 0),
                    (int)($data['stats_startdate']['day'] ?? 0)
                );
            } else {
                $startts = (int)$data['stats_startdate'];
            }
        }

        if (!empty($data['stats_enddate'])) {
            if (is_array($data['stats_enddate'])) {
                $endts = make_timestamp(
                    (int)($data['stats_enddate']['year'] ?? 0),
                    (int)($data['stats_enddate']['month'] ?? 0),
                    (int)($data['stats_enddate']['day'] ?? 0)
                );
            } else {
                $endts = (int)$data['stats_enddate'];
            }
        }

        if ($startts > 0 && $endts > 0 && $startts > $endts) {
            $errors['stats_startdate'] = get_string('error_startdate_after_enddate', 'local_th_qaa_manager');
        }

        return $errors;
    }
}
