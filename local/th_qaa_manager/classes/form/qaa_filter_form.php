<?php

namespace local_th_qaa_manager\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class qaa_filter_form extends \moodleform
{

    public function definition()
    {
        $mform = $this->_form;

        // Form method GET is better for filters so URLs can be bookmarked/shared.
        $mform->disable_form_change_checker();
        $this->_form->setAttributes([
            'method' => 'get',
        ]);

        // Hidden fields.
        $mform->addElement('hidden', 'status', 10);
        $mform->setType('status', PARAM_INT);

        // 1. Chọn CTV
        $ctvoptions = $this->_customdata['ctvoptions'] ?? [];
        $mform->addElement('autocomplete', 'filter_ctv', get_string('filter_ctv', 'local_th_qaa_manager'), $ctvoptions, [
            'placeholder' => get_string('selectassignee', 'local_th_qaa_manager'),
            'multiple' => true,
        ]);
        $mform->setType('filter_ctv', PARAM_INT);

        // 2. Chọn khóa học (AJAX)
        $course_options = [
            'multiple' => true,
            'ajax' => 'local_th_qaa_manager/load_courses',
            'placeholder' => get_string('select_course_placeholder', 'local_th_qaa_manager'),
            'valuehtmlcallback' => function($value) {
                global $DB;
                $course = $DB->get_record('course', ['id' => (int)$value], 'id, shortname, fullname', IGNORE_MISSING);
                if (!$course) return '<span>' . get_string('not_exist', 'local_th_qaa_manager') . '</span>';
                return '<span>' . s($course->shortname) . ', ' . s($course->fullname) . '</span>';
            },
        ];
        $mform->addElement('autocomplete', 'filter_course', get_string('filter_course', 'local_th_qaa_manager'), [], $course_options);
        $mform->setType('filter_course', PARAM_INT);

        // 3. Chọn khoảng thời gian CTV trả lời câu hỏi
        $mform->addElement('date_selector', 'filter_startdate', get_string('filter_startdate', 'local_th_qaa_manager'), ['optional' => true]);
        $mform->addElement('date_selector', 'filter_enddate', get_string('filter_enddate', 'local_th_qaa_manager'), ['optional' => true]);

        // 4. Trạng thái
        $statusoptions = $this->_customdata['statusoptions'] ?? [];
        $mform->addElement('select', 'filter_status', get_string('filter_status', 'local_th_qaa_manager'), $statusoptions);
        $mform->setType('filter_status', PARAM_ALPHA);

        $this->add_action_buttons(false, get_string('submit'));

        $js = "
            document.addEventListener('DOMContentLoaded', function() {
                function setupDateSelectorToggle() {
                    var checkboxes = document.querySelectorAll('input[type=\"checkbox\"][name$=\"[enabled]\"]');
                    checkboxes.forEach(function(cb) {
                        if (cb.dataset.toggleSetup) return;
                        cb.dataset.toggleSetup = '1';
                        var prefix = cb.name.replace('[enabled]', '');
                        var updateState = function() {
                            var selects = document.querySelectorAll('select[name^=\"' + prefix + '\"], button[name^=\"' + prefix + '\"], input[name^=\"' + prefix + '\"]:not([type=\"checkbox\"])');
                            selects.forEach(function(sel) {
                                if (cb.checked) {
                                    sel.removeAttribute('disabled');
                                    sel.style.opacity = '1';
                                    sel.style.pointerEvents = 'auto';
                                } else {
                                    sel.setAttribute('disabled', 'disabled');
                                    sel.style.opacity = '0.5';
                                    sel.style.pointerEvents = 'none';
                                }
                            });
                        };
                        cb.addEventListener('change', updateState);
                        updateState();
                    });
                }
                setupDateSelectorToggle();
                setTimeout(setupDateSelectorToggle, 500);
                setTimeout(setupDateSelectorToggle, 1000);

                var filterForm = document.querySelector('form[action*=\"status=10\"], form:has(input[name=\"status\"][value=\"10\"])');
                if (!filterForm) {
                    var input = document.querySelector('input[name=\"status\"][value=\"10\"]');
                    if (input) filterForm = input.closest('form');
                }
                if (filterForm) {
                    filterForm.addEventListener('submit', function(e) {
                        var startCb = filterForm.querySelector('input[name=\"filter_startdate[enabled]\"]');
                        var endCb = filterForm.querySelector('input[name=\"filter_enddate[enabled]\"]');
                        if (startCb && startCb.checked && endCb && endCb.checked) {
                            var sDay = parseInt(filterForm.querySelector('select[name=\"filter_startdate[day]\"]').value, 10);
                            var sMonth = parseInt(filterForm.querySelector('select[name=\"filter_startdate[month]\"]').value, 10) - 1;
                            var sYear = parseInt(filterForm.querySelector('select[name=\"filter_startdate[year]\"]').value, 10);

                            var eDay = parseInt(filterForm.querySelector('select[name=\"filter_enddate[day]\"]').value, 10);
                            var eMonth = parseInt(filterForm.querySelector('select[name=\"filter_enddate[month]\"]').value, 10) - 1;
                            var eYear = parseInt(filterForm.querySelector('select[name=\"filter_enddate[year]\"]').value, 10);

                            var startDate = new Date(sYear, sMonth, sDay, 0, 0, 0);
                            var endDate = new Date(eYear, eMonth, eDay, 23, 59, 59);

                            if (startDate > endDate) {
                                e.preventDefault();
                                e.stopPropagation();
                                alert('" . addslashes(get_string('error_startdate_after_enddate', 'local_th_qaa_manager')) . "');
                                return false;
                            }
                        }
                    });
                }
            });
        ";
        $mform->addElement('html', \html_writer::script($js));
    }
}
