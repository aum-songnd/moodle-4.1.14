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
 * @package     local_th_vmc_alert
 * @category    string
 * @copyright   2020 phamleminh1812@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'TH th_vmc_alert';

/*Email (1):
Thông Báo Tài Khoản
 */

$string['subject_alertlogin'] = 'Thông Báo Tài Khoản';
$string['email_alertlogin'] = '{$a->userfullname} thân mến!

Tài khoản của bạn để truy cập các khóa học Chăm Sóc Sức Khỏe Chủ Động của Trung tâm OM\'E Việt Nam tại hệ thống học trực tuyến {$a->url} đã được tạo, thông tin tài khoản đã được gửi đến email của bạn rồi đấy!
Hãy kiểm tra email, đăng nhập, đổi mật khẩu và bắt đầu thực hành theo các chỉ dẫn của giảng viên Trung tâm OM\'E nhé!

Sức khỏe của bạn là niềm vui của chúng tôi.

Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
-   Hotline: 0988 622 194 / 096 600 0643
-   Email: trungtamome@gmail.com

Trân trọng cảm ơn!
Trung tâm OM\'E Việt Nam
';

/*Email (2):
Thông Báo Học Tập và Hoàn Thành Khóa Học
 */

$string['subject_alertlearning'] = '{$a->siteprefix} Thông báo học tập và hoàn thành khóa học';
$string['email_alertlearning'] = '{$a->userfullname} thân mến!

Chắc hẳn bạn đã biết được rất nhiều điều hữu ích thông qua khóa học {$a->coursefullname}.
=> Nếu bạn vẫn chưa học xong khóa học, hãy tiếp tục hoàn thành khóa học trước khi kết thúc thời hạn học tập 90 ngày.
=> Nếu đã hoàn thành xong khóa học, bạn hoàn toàn có thể ôn tập lại nội dung khóa học lần nữa và tiếp tục thực hành.
Ngoài việc thực hành theo các bài hướng dẫn, hãy làm các bài luyện tập trắc nghiệm và các bài ôn tập để kiểm tra lại những điều mình đã lĩnh hội nhé!
Để đủ điều kiện được cấp “Chứng nhận hoàn thành khóa học” của Trung tâm OM\'E và tham gia các lớp chia sẻ chuyên sâu trực tiếp với giảng viên thì việc hoàn thành bài kiểm tra trắc nghiệm cuối khóa là BẮT BUỘC.
Chúng tôi còn rất nhiều Khóa học hấp dẫn khác tại địa chỉ: {$a->url};
Hẹn gặp lại bạn ở các Khóa học sau!
OM\'E gửi đến bạn lời chúc sức khỏe và bình an!

Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
-   Thông qua diễn đàn trao đổi trên hệ thống
-   Thông qua cán bộ quản lý học tập
-   Hotline: 096 600 0643
-   Email: trungtamome@gmail.com

Trân trọng cảm ơn!
Trung tâm OM\'E Việt Nam';

/*
Email (3):
Thông Báo Thời Điểm Kết Thúc Khoá Học
 */

$string['subject_alertaccountduedate'] = '{$a->siteprefix} Thông báo thời điểm kết thúc khóa học';

$string['email_alertaccountduedate'] = '{$a->userfullname} thân mến!

Chúc mừng bạn đã và đang đồng hành cùng với khóa học {$a->coursefullname}.
Bạn còn {$a->dayremain} ngày nữa để học trước khi kết thúc (vào ngày {$a->duedate}). Hãy chắc chắn rằng bạn đã học tập xong và nắm vững tất cả những kĩ năng được giảng viên hướng dẫn nhé.
Trong quá trình thực hành, nếu có bất kì thắc mắc gì về hệ thống hoặc kiến thức chuyên môn, đừng ngần ngại mà hãy phản hồi lại cho chúng tôi qua:
-   Diễn đàn thảo luận chung
-   Cán bộ quản lý học tập: 0903428169
-   Hotline: 096 600 0643
-   Email: trungtamome@gmail.com

Trung tâm OM\'E Việt Nam luôn luôn đồng hành cùng bạn.
Gửi tới bạn và gia đình lời chúc sức khỏe, bình an và hạnh phúc!
Trân trọng cảm ơn!
Trung tâm OM\'E Việt Nam';

/*Email (4):
Thông Báo Kết Thúc Khóa Học
 */

$string['subject_alertketthuckhoahoc'] = '{$a->siteprefix} Thông báo Kết thúc khóa học';

$string['email_alertketthuckhoahoc'] = '{$a->userfullname} thân mến!
Chúc mừng bạn đã đồng hành một thời gian dài với khóa học {$a->coursefullname}.
Khóa học của bạn đã đến thời hạn kết thúc (vào ngày {$a->duedate}).
Qua quá trình bạn học và thực hành, nếu có bất kì thắc mắc cũng như ý kiến đóng góp, đừng ngại ngần phản hồi lại với chúng tôi qua:
-   Diễn đàn thảo luận chung
-   Cán bộ quản lý học tập: 0903428169
-   Hotline: 096 600 0643
-   Email: trungtamome@gmail.com

Trung tâm OM\'E Việt Nam luôn luôn đồng hành cùng bạn.
Gửi tới bạn và gia đình lời chúc sức khỏe, bình an và hạnh phúc!
Trân trọng cảm ơn!
Trung tâm OM\'E Việt Nam
';

/*Email (5):
Thông báo tài khoản đã được gán khóa học
 */

$string['subject_alert_account_enrol_course'] = '{$a->siteprefix} Thông báo tài khoản đã được gán khóa học';

$string['email_alert_account_enrol_course'] = '{$a->userfullname} thân mến!
Trung tâm OM\'E trân trọng thông báo bạn đã đăng ký thành công khóa học {$a->coursefullname}.

Bạn hãy đăng nhập tài khoản tại trang {$a->url} để kích hoạt khóa học của mình nhé!
Sau khi kích hoạt khóa học, bạn sẽ có 90 ngày để học tập trên hệ thống.
Hãy tham gia học tập và thực hành thường xuyên để đạt kết quả tốt nhất nhé!

OM\'E gửi đến bạn lời chúc sức khỏe và bình an!
----------------------------------------------------------------------
Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
-   Hotline:  096 600 0643
-   Thông qua cán bộ quản lý học tập: 0903428169
-   Email: trungtamome@gmail.com
Trân trọng cảm ơn!
Trung tâm OM\'E Việt Nam
';

/*Email (6):
Nhắc nhở học tập khóa học đã kích hoạt
 */

$string['subject_alert_active_course'] = '{$a->siteprefix} Nhắc nhở học tập khóa học đã kích hoạt';

$string['email_alert_active_course'] = '{$a->userfullname} thân mến!

Trong 14 ngày vừa qua, hệ thống OM\'E chưa ghi nhận bạn vào học tập và hoàn thiện khóa học {$a->coursefullname}.
Khóa học của bạn đã kích hoạt sẽ có thời hạn trong 90 ngày nên bạn hãy cố gắng sắp xếp công việc để dành thời gian học tập và thực hành thường xuyên hơn nhé.

Các Giảng viên và Quản lý học tập của Trung tâm OM\'E sẽ luôn đồng hành giúp đỡ bạn để hoàn thành khóa học.

Nếu có bất cứ thắc mắc gì, vui lòng liên hệ:
-   Hotline:  096 600 0643
-   Thông qua cán bộ quản lý học tập: 0903428169
-   Email: trungtamome@gmail.com
Trân trọng cảm ơn!
Trung tâm OM\'E Việt Nam
';

$string['alertlogindesc'] = 'Gửi email nhắc đối với học viên chưa login, nếu tick tính năng này sẽ được bật';
$string['alertlearningdesc'] = 'Nhắc nhở đôn đốc học tập, làm bài kiểm tra, nếu tick tính năng này sẽ được bật';
$string['alertaccountduedatedesc'] = 'Thông báo thời hạn duy trì tài khoản trong khóa học sắp kết thúc, nếu tick tính năng này sẽ được bật';
$string['alertketthuckhoahocdesc'] = 'Thông báo đã hết thời gian tham gia khóa học, nếu tick tính năng này sẽ được bật';
$string['setting_alertlogin'] = 'Nhắc đối với học viên chưa login ';
$string['setting_alertlearning'] = 'Nhắc nhở đôn đốc học tập, làm bài kiểm tra';
$string['setting_alertaccountduedate'] = 'Thông báo sắp kết thúc tài khoản trong khóa học';
$string['setting_alertketthuckhoahoc'] = 'Thông báo tài khoản trong khóa học kết thúc';
$string['setting_dayalertlogin'] = 'Ngày nhắc nhở học viên chưa login';
$string['setting_dayalertlearning'] = 'Ngày nhắc nhở đôn đốc học tập';
$string['setting_dayalertaccountduedate'] = 'Ngày nhắc nhở thông báo tài khoản sắp kết thúc, tính từ thời điểm hạn ghi danh khóa học lùi về';
$string['setting_dayspanincourses'] = 'Số ngày học tập trong course';
$string['setting_dayalertketthuckhoahoc'] = 'Ngày nhắc nhở thông báo tài khoản trong khóa học kết thúc, tính từ thời điểm hạn ghi danh khóa học lùi về';


$string['siteprefix'] = 'Site prefix';
$string['siteprefix_des'] = 'Enter the prefix to identify the site (e.g., [OME], [TNU])';