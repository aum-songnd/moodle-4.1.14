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
 * Chuỗi ngôn ngữ tiếng Việt cho local_th_slideshow
 *
 * @package   local_th_slideshow
 * @copyright 2024 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Tên plugin.
$string['pluginname'] = 'TH Slideshow';

// Tiêu đề trang cài đặt.
$string['settings_slides']  = 'Các slide';
$string['settings_options'] = 'Tùy chọn';

// Tiêu đề chung của slideshow.
$string['slideshowheadingsub'] = 'Cài đặt Slideshow';
$string['slideshowdesc']       = 'Chỉ tải lên ảnh có tỉ lệ khung hình 3x4.';

// Tiêu đề từng slide.
$string['slideshow_slide1'] = 'Slide 1';
$string['slideshow_slide2'] = 'Slide 2';
$string['slideshow_slide3'] = 'Slide 3';
$string['slideshow_slide4'] = 'Slide 4';
$string['slideshow_slide5'] = 'Slide 5';

// Các trường của slide.
$string['slideimage']     = 'Hình ảnh banner';
$string['slideimagedesc'] = 'Tải lên hình ảnh hiển thị cho slide này. Kích thước khuyến nghị: 1600 × 600 px.';

$string['slidetitle']     = 'Tiêu đề slide';
$string['slidetitledesc'] = 'Nhập văn bản tiêu đề hiển thị trên slide này.';

$string['slidecaption']     = 'Chú thích slide';
$string['slidecaptiondesc'] = 'Nhập chú thích / văn bản phụ cho slide này.';

$string['slide_url']      = 'URL liên kết slide';
$string['slide_url_desc'] = 'URL tùy chọn mà slide sẽ dẫn đến khi được nhấp vào.';

// Tiêu đề tùy chọn.
$string['slideshow_options'] = 'Tùy chọn Slideshow';

// Chiều cao.
$string['slideshow_height']      = 'Chiều cao slideshow';
$string['slideshow_height_desc'] = 'Chọn chiều cao của slideshow. Chọn "Tự động" để chiều cao thích ứng theo tỉ lệ hình ảnh.';
$string['responsive']            = 'Tự động';

// Ẩn chú thích trên thiết bị di động.
$string['slideshow_hide_captions']      = 'Ẩn chú thích trên thiết bị di động';
$string['slideshow_hide_captions_desc'] = 'Chọn để ẩn tiêu đề và chú thích slide trên màn hình nhỏ.';

// Lớp phủ hoa văn.
$string['slideshowpattern']     = 'Lớp phủ hoa văn';
$string['slideshowpatterndesc'] = 'Chọn lớp phủ hoa văn trang trí cho khu vực slideshow.';

// Tự động chuyển slide.
$string['slideshow_advance']      = 'Tự động chuyển slide';
$string['slideshow_advance_desc'] = 'Chọn để tự động chuyển sang slide tiếp theo.';

// Nút điều hướng.
$string['slideshow_nav']      = 'Hiển thị điều hướng';
$string['slideshow_nav_desc'] = 'Chọn để hiển thị các mũi tên điều hướng trước/sau và các chấm chỉ báo.';

// Kiểu thanh tải.
$string['slideshow_loader']      = 'Kiểu thanh tải';
$string['slideshow_loader_desc'] = 'Chọn kiểu thanh tiến trình hiển thị giữa các lần chuyển slide.';

// Hiệu ứng hình ảnh.
$string['slideshow_imgfx']      = 'Hiệu ứng chuyển đổi hình ảnh';
$string['slideshow_imgfx_desc'] = 'Nhập tên hiệu ứng chuyển đổi cho hình ảnh (ví dụ: <code>random</code>, <code>fade</code>, <code>slideLeft</code>). Để <code>random</code> nhằm sử dụng hiệu ứng ngẫu nhiên mỗi lần.';

// Hiệu ứng văn bản.
$string['slideshow_txtfx']      = 'Hiệu ứng chuyển đổi văn bản';
$string['slideshow_txtfx_desc'] = 'Nhập tên hiệu ứng chuyển đổi cho văn bản slide (ví dụ: <code>moveFromLeft</code>, <code>fadeIn</code>).';

// Chuỗi web service / API.
$string['ws_get_slides']      = 'Lấy danh sách slide';
$string['ws_get_slides_desc'] = 'Trả về tất cả các slide đã cấu hình (URL hình ảnh, tiêu đề, chú thích, liên kết) và các tùy chọn hiển thị dành cho ứng dụng di động.';
$string['privacy:metadata']   = 'Plugin TH Slideshow chỉ lưu trữ cấu hình toàn site (hình ảnh slide, tiêu đề, chú thích, URL). Plugin không lưu trữ bất kỳ dữ liệu cá nhân nào của người dùng.';

$string['th_slideshow:manage'] = 'Quản lý slideshow';