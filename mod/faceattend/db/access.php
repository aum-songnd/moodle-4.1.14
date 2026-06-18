<?php
// Định nghĩa quyền hạn (capabilities) cho plugin
// Moodle dùng file này để quản lý phân quyền theo vai trò
defined('MOODLE_INTERNAL') || die();

$capabilities = [

    // Quyền tạo activity (dành cho giảng viên, admin)
    'mod/faceattend:addinstance' => [
        'riskbitmask'  => RISK_XSS,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => [
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
        ],
    ],

    // Quyền xem activity (tất cả người trong course)
    'mod/faceattend:view' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => [
            'student'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
        ],
    ],

    // Quyền thực hiện điểm danh (chỉ sinh viên)
    'mod/faceattend:attend' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => [
            'student' => CAP_ALLOW,
        ],
    ],

    // Quyền xem báo cáo điểm danh (giảng viên trở lên)
    'mod/faceattend:viewreport' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => [
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
        ],
    ],

    // Quyền quản lý cài đặt (admin)
    'mod/faceattend:manage' => [
        'riskbitmask'  => RISK_CONFIG,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => [
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
        ],
    ],
];
