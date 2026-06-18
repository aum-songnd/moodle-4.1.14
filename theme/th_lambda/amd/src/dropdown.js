import $ from 'jquery';

function set_cookies(name) {
  var now = new Date();
  var time = now.getTime();
  var expireTime = time + 604800 * 60;
  now.setTime(expireTime);
  document.cookie = 'option='+name+';expires='+now.toUTCString()+';path=/';
}

const select = document.getElementById("th123");

// Tạo cấu trúc dropdown bằng JavaScript từ select ban đầu
const wrapper = document.createElement('div');
wrapper.className = 'custom-dropdown';

const display = document.createElement('a');
display.className = 'dropdown-display';
var lang = document.documentElement.lang;
if (lang == 'vi') {
  display.textContent = 'Chọn phương thức xác minh khác'; // luôn hiển thị "Lựa chọn"
} else {
  display.textContent = 'Choose other authentication methods';
}

const list = document.createElement('div');
list.className = 'dropdown-list';

// Tạo các option từ select gốc
Array.from(select.options).forEach(option => {
  if (option.value) { // bỏ qua option rỗng ban đầu
    const item = document.createElement('div');
    item.textContent = option.textContent;
    item.dataset.value = option.value;
    list.appendChild(item);

    item.addEventListener('click', function(){
      select.value = this.dataset.value; // chỉ đổi value select, KHÔNG thay đổi text hiển thị
      list.style.display = 'none';

      var value = select.value;
      set_cookies(value);

      if (value == 'cloudflare') {
          $('#fitem_id_recaptcha_element').css("display", "none");
          $('#fitem_id_recaptcha_element').prop('disabled', true);
          $('#fitem_id_email2').css("display", "none");
          $('#fitem_id_email2').prop('disabled', true);
          $('#fgroup_id_button_email').css("display", "none");
          $('#fgroup_id_button_email').prop('disabled', true);
          $('.login-form-recaptcha').css("display", "none");
          $('.login-form-recaptcha').prop('disabled', true);
          $('.login-form-email').css("display", "none");
          $('.login-form-email').prop('disabled', true);
          $('#sendotp').css("display", "none");
          $('#sendotp').prop('disabled', true);
          $('#otp').css("display", "none");
          $('#otp').prop('disabled', true);
          $('#fitem_id_sendotp').css("display", "none");
          $('#fitem_id_sendotp').prop('disabled', true);
          $('#fitem_id_otp').css("display", "none");
          $('#fitem_id_otp').prop('disabled', true);

          $('.login-form-cloudflare').css("display", "block");
          $('.login-form-cloudflare').prop('disabled', false);
          $('#fitem_id_cloudflare_element').css("display", "block");
          $('#fitem_id_cloudflare_element').prop('disabled', false);
      } else if (value == 'recaptcha') {
          $('#fitem_id_email2').css("display", "none");
          $('#fitem_id_email2').prop('disabled', true);
          $('#fgroup_id_button_email').css("display", "none");
          $('#fgroup_id_button_email').prop('disabled', true);
          $('#fitem_id_cloudflare_element').css("display", "none");
          $('#fitem_id_cloudflare_element').prop('disabled', true);
          $('.login-form-cloudflare').css("display", "none");
          $('.login-form-cloudflare').prop('disabled', true);
          $('.login-form-email').css("display", "none");
          $('.login-form-email').prop('disabled', true);
          $('#sendotp').css("display", "none");
          $('#sendotp').prop('disabled', true);
          $('#otp').css("display", "none");
          $('#otp').prop('disabled', true);
          $('#fitem_id_sendotp').css("display", "none");
          $('#fitem_id_sendotp').prop('disabled', true);
          $('#fitem_id_otp').css("display", "none");
          $('#fitem_id_otp').prop('disabled', true);

          $('.login-form-recaptcha').css("display", "block");
          $('.login-form-recaptcha').css("disabled", false);
          $('#fitem_id_recaptcha_element').css("display", "block");
          $('#fitem_id_recaptcha_element').prop('disabled', false);
      } else if (value == 'email') {
          $('#fitem_id_recaptcha_element').css("display", "none");
          $('#fitem_id_recaptcha_element').prop('disabled', true);
          $('#fitem_id_cloudflare_element').css("display", "none");
          $('#fitem_id_cloudflare_element').prop('disabled', true);
          $('.login-form-cloudflare').css("display", "none");
          $('.login-form-cloudflare').prop('disabled', true);
          $('.login-form-recaptcha').css("display", "none");
          $('.login-form-recaptcha').prop('disabled', true);

          $('.login-form-email').css("display", "block");
          $('.login-form-email').css("disabled", false);

          // if ($('#sendotp').is(':disabled')) {
          //   $('#sendotp').css("display", "block");
          // } else {
            $('#sendotp').css("display", "block");
            $('#sendotp').prop('disabled', false);
          // }
          $('#fitem_id_email2').css("display", "block");
          $('#fitem_id_email2').prop('disabled', false);
          $('#fgroup_id_button_email').css("display", "block");
          $('#fgroup_id_button_email').prop('disabled', false);
          $('#fitem_id_sendotp').css("display", "block");
          $('#fitem_id_sendotp').prop('disabled', false);
      } else {

      }
    });
  }
});

// Append các thành phần vào wrapper và thay vào DOM
wrapper.appendChild(display);
wrapper.appendChild(list);
select.parentNode.insertBefore(wrapper, select);
list.style.maxWidth = "500px";
// Click hiển thị dropdown
display.addEventListener('click', function(e){
  e.stopPropagation();
  list.style.display = list.style.display === 'block' ? 'none' : 'block';
});

// Click ngoài dropdown thì đóng lại
document.addEventListener('click', function(){
  list.style.display = 'none';
});
