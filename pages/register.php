<?php 
// ============================================
// TRANG ĐĂNG KÝ - CHỈ CONTENT
// ============================================
$pageTitle = "Đăng ký - NHÓM 10 Fashion Shop";
?>

<div class="py-5">
    <div class="container">
     <div class="row justify-content-center">
         <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                  <h1 style="color:white ; ">Đăng ký</h1>
                </div>
                <div class="card-body">
                    <form action="./functions/authcode.php" method="POST" id="register-account">
                        <div class="mb-3">
                            <b><label class="form-label">Họ tên</label></b>
                            <input type="text" required name ="name" class="form-control" placeholder="Nhập họ tên của bạn" >
                        </div>
                        <div class="mb-3">
                            <b><label class="form-label">Số điện thoại</label></b>
                            <input type="number" required name ="phone" class="form-control" placeholder="Nhập số điện thoại của bạn" >
                        </div>
                        <div class="mb-3">
                            <b><label  class="form-label">Địa chỉ Email</label></b>
                            <input type="email" required name="email" class="form-control" placeholder="Nhập Email">
                        </div>
                        <div class="mb-3">
                            <b><label class="form-label">Mật khẩu</label></b>
                            <input type="password" required name="password" id="password" class="form-control"  placeholder="Nhập mật khẩu">
                        </div>
                        <div class="mb-3">
                            <b><label class="form-label">Xác nhận mật khẩu</label></b>
                            <input type="password" required name="cpassword" id="cpassword" class="form-control" placeholder="Xác nhận mật khẩu">
                        </div>
                        <div class="mb-3">
                            <b><label class="form-label">Địa chỉ</label></b>
                            <input type="text" required name="address" class="form-control"  placeholder="Nhập địa chỉ của bạn">
                        </div>
                        <button type="submit" name="register_btn" class="btn btn-primary">Đăng ký</button>
                    </form>
                </div>
            </div>
         </div>
     </div>
    </div> 
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#register-account').submit(function(e){
        var password1 = $('#password').val();
        var password2 = $('#cpassword').val();
        if(password1 != password2){
            alertify.set('notifier','position', 'top-right');
            alertify.success('Mật khẩu chưa khớp');
            e.preventDefault();
        }else if(password1.length <= 6){
            alertify.set('notifier','position', 'top-right');
            alertify.success('Vui lòng nhập mật khẩu nhiều hơn 6 kí tự');
            e.preventDefault();
        }
    })
</script>

