# Workly

**Workly** là một nền tảng tuyển dụng trực tuyến giúp kết nối giữa **nhà tuyển dụng** và **ứng viên**. Dự án được xây dựng bằng PHP, MySQL và sử dụng Bootstrap để tạo giao diện thân thiện, dễ sử dụng.

---

## 🚀 Tính Năng Chính

- 👤 Quản lý hồ sơ ứng viên (avatar, ngành nghề, tiểu sử)
- 🏢 Hồ sơ nhà tuyển dụng (logo, tên công ty, thông tin liên hệ)
- 📝 Đăng và quản lý tin tuyển dụng
- 💼 Ứng tuyển công việc và theo dõi trạng thái đơn ứng tuyển
- 🔐 Hệ thống đăng ký / đăng nhập cho cả ứng viên và nhà tuyển dụng
- 📁 Tải lên logo công ty, avatar cá nhân

---

## 🛠️ Công Nghệ Sử Dụng

- **PHP**
- **MySQL**
- **Bootstrap 5**
- **HTML5 / CSS3 / JavaScript**
- **XAMPP**

---

## ⚙️ Hướng Dẫn Cài Đặt

### Bước 1: Tải mã nguồn về máy
```bash
git clone https://github.com/ChauThLong/workly.git
Bước 2: Đưa vào thư mục XAMPP
Giải nén hoặc di chuyển thư mục workly vào:

bash
Copy
Edit
C:/xampp/htdocs/workly_website
Bước 3: Tạo cơ sở dữ liệu
Mở phpMyAdmin tại http://localhost/phpmyadmin

Tạo database tên: workly

Import file workly_db.sql

Bước 4: Cấu hình kết nối CSDL
Mở file config.php và chỉnh sửa thông tin:

php
Copy
Edit
$host = "localhost";
$user = "root";
$pass = "";
$db   = "workly";
Bước 5: Chạy ứng dụng
Truy cập trình duyệt tại:

arduino
Copy
Edit
http://localhost/workly_website/index.php

✍️ Tác Giả
Châu Thuyên Long

GitHub: github.com/ChauThLong

📌 Ghi Chú
Dự án là đồ án học phần và vẫn đang trong quá trình phát triển, có thể được cập nhật thêm nhiều tính năng nâng cao như: lọc công việc, gửi email thông báo, phân quyền người dùng, v.v.

yaml
Copy
Edit