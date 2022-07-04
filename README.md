<h1 align="center">Đồ Án Thực Tập<br/>
    Đề tài: ứng dụng mua bán máy tính/Laptop
</h1>

<p align="center">
    <img src="./photo/steel-eagle.png" width="1280" />
</p>


# [**Table Of Content**](#table-of-content)
- [**Table Of Content**](#table-of-content)
- [**Database**](#database)
- [**Mentor**](#mentor)
  - [**Nguyễn Thị Bích Nguyên**](#nguyễn-thị-bích-nguyên)
  - [**Nguyễn Anh Hào**](#nguyễn-anh-hào)
  - [**Lê Hồng Sơn**](#lê-hồng-sơn)
  - [**Phan Thanh Hy**](#phan-thanh-hy)
  - [**Lưu Nguyễn Kì Thư**](#lưu-nguyễn-kì-thư)
- [**Controller Timeline**](#controller-timeline)
- [**Timeline**](#timeline)
  - [**Phase 1: 29-06-2022 to xx-xx-2022**](#phase-1-29-06-2022-to-xx-xx-2022)
- [**Special Thank**](#special-thank)
- [**Made with 💘 and PHP <img src="https://www.vectorlogo.zone/logos/php/php-horizontal.svg" width="60">**](#made-with--and-php-)

# [**Database**](#database)

<p align="center">
    <img src="./photo/mySQL-diagram-database2.png" width="640" />
</p>
<h3 align="center">

***Bản thiết kế tiêu chuẩn cơ sở dữ liệu cho đồ án thực tập***
</h3>

# [**Mentor**](#mentor)

Vài dòng mình để đây không có chủ đích xúc phạm giáo viên nào nhưng mình bức xúc quá nên đánh phá luận vậy

## [**Nguyễn Thị Bích Nguyên**](#nguyen-thi-bich-nguyen)

Nói thẳng luôn nhé ! Bạn nào mà trúng cô hướng dẫn làm đồ án thì cứ phải gọi là đen vãi cả l*n. 
Vì mình là người bị phân trúng vào cô Nguyên nên mình cực khó chịu vì lý do sau: Thông thường, 
một giáo viên hướng dẫn sẽ chủ động liên hệ với bạn để giao đề tài tốt nghiệp và chỉnh sửa thông tin các kiểu.
Nhưng cô Nguyên thì lại ở cái thể loại hãm l*l hơn, cô éo bao giờ chủ động liên lạc với các bạn đâu nhé.

Nói chung là nằm mơ cũng chưa chắc 🙂☺ cô đã liên lạc với bạn. Lúc nhà trường công cố danh sách giáo viên 
hướng dẫn đồ án thực tập, mình đã muốn đổi ngay khi biết người hướng dẫn mình là cô Nguyên do từ khi
mình học năm 2 - 3, mình đã nghe các anh chị kể về cô Nguyên với một tâm trạng **ÚI GIỜI ƠI** rùi.

Và quả nhiên là đúng cmn luôn. Mình chủ động liên lạc với cô từ lúc danh sách công bố tới gần sát ngày chốt sổ để 
công bố danh sách chính thức. Liên lạc với cô qua e-mail, số điện thoại, Zalo, Facebook,.... (nói chung là hết
tất cả các phương thức mà các bạn có thể nghĩ ra 😫). Gỉa sử, số phone nhà trường cung cấp trong e-mail tới sinh
viên chẳng hạn, gọi 10 cuộc thì cả 10 cuộc toàn... thuê bao.

Một điều hãi hùng nữa là cô Nguyên có đam mê họp lúc 1-2h sáng. Trong khi thời điểm này, các bạn sinh viên 
đi thực tập về đã mỏi mệt lắm rùi + cố gắng làm đồ án thực tập nữa. Theo mình nghe ngóng thì hầu 
như họp với cô xong chả giải quyết vấn đề gì, chỉ có sự áp lực từ việc bị chửi mắng là tăng lên rõ rệt 😡😡.

## [**Nguyễn Anh Hào**](#nguyen-anh-hao)

## [**Lê Hồng Sơn**](#le-hong-son)

Là giám đốc của Học viện mình. Đứng ở cương vị là người quản lý cấp cao nhất của nhà trường, ông thầy 
này ra rất nhiều yêu cầu nhưng cực kì mơ hồ, mông lung. Nếu bạn muốn làm đồ án cẩn thận thì nên né ông này luôn 

## [**Phan Thanh Hy**](#phan-thanh-hy)

Dĩ nhiên, mình làm đồ án dưới sự hướng dẫn của thầy. Nhưng tiếng lành đồn xa😝😝, là `người hướng dẫn đồ án 
tào lao bí đao nhất Học viện` nên hàng năm có cực kì nhiều sinh viên liên hệ với thầy để hướng 
dẫn làm đồ án tốt nghiệp. Tại do `tào lao bí đao` quá nên thầy này chấm đồ án thực tập rất dễ. 

Mình khuyến khích bạn nào học yếu nên liên hệ trước với thầy này.

## [**Lưu Nguyễn Kì Thư**](#luu-nguyen-ki-thu)

# [**Controller Timeline**](#controller-timeline)

Trình tự xây dựng các controller của đồ án này, cái này các bạn tham khảo để nhận biết cái controller nào sẽ `ưu tiên xây dựng trước`.

> Note: quy ước ở cái số 3 và 4 áp dụng cho tất cả Controller nào có dạng số ít và số nhiều như `3` và `4`

> Note: các controller có tiền tố Admin ở đầu. Ví dụ: AdminProductsController, AdminCategoryController,.. là thuộc về quản trị viên. Những cái không có tiền tố ở đầu là của khách hàng

1. Login Controller

2. Sign Up Controller

3. Admin Users Controller - cái này để lấy danh sách toàn bộ user ngoại trừ chính người đang đăng nhập và thêm mới một user

4. Admin User Controller - các chức năng sửa-xóa-lấy thông tin 1 user.

5. Admin Product Controller 

6. Admin Product Controller

7. Admin Products Photos Controller - lấy ra danh sách ảnh theo ID và upload ảnh cho một sản phẩm


# [**Timeline**](#timeline)

## [**Phase 1: 29-06-2022 to xx-xx-2022**](#phase-1-29-06-2022-to-xx-xx-2022)

- **29-06-2022** - khởi tạo dự án, chức năng đăng nhập và tạo mã JWT token

- **30-06-2022** - sửa lỗi JWT token do mã hóa sai, thêm Product & Products Model

- **02-07-2022** 
  
1. C.R.U.D cho bảng `Users`
2. Thêm model ProductCategories | ProductCategory | ProductsPhoto | ProductsPhotos
3. Tạo ProductsController & chức năng truy vấn dữ liệu có bộ lọc nâng cao( Không khó nhưng lâu vãi *beep* 😖)

- **03-07-2022**
1. Sửa lỗi truy vấn trong ProductsController getAll()
2. Create cho ProductsController()

- **04-07-2022**
1. R.U.D cho ProductController
2. Thêm model OrdersContent | OrdersContents Model
3. Upload ảnh cho sản phẩm
   
# [**Special Thank**](#our-team)

<table>
        <tr>
            <td align="center">
                <a href="https://github.com/ngdanghau">
                    <img src="./photo/Hau.jpg" width="100px;" alt=""/>
                    <br />
                    <sub><b>Nguyễn Đăng Hậu</b></sub>
                </a>
            </td>
            <td align="center">
                <a href="https://github.com/Phong-Kaster">
                    <img src="./photo/swastika2.jpg" width="100px;" alt=""/>
                    <br />
                    <sub><b>Nguyễn Thành Phong</b></sub>
                </a>
            </td>
        </tr>    
</table>
 
# [**Made with 💘 and PHP <img src="https://www.vectorlogo.zone/logos/php/php-horizontal.svg" width="60">**](#made-with-love-and-php)