<h1 align="center">Đồ Án Thực Tập<br/>
    Đề tài: ứng dụng mua bán máy tính/Laptop
</h1>

<p align="center">
    <img src="./photo/steel-eagle.png" width="1280" />
</p>


# [**Table Of Content**](#table-of-content)
- [**Table Of Content**](#table-of-content)
- [**Topic**](#topic)
- [**Database**](#database)
- [**Important Modules**](#important-modules)
  - [**1. Admin Order Controller**](#1-admin-order-controller)
    - [**1.1. Get Order By Id**](#11-get-order-by-id)
    - [**1.2 - Modify Order**](#12---modify-order)
    - [**1.3. Delete**](#13-delete)
  - [**2. Admin Orders Controller**](#2-admin-orders-controller)
  - [**3. Orders Controller**](#3-orders-controller)
- [**Mentor**](#mentor)
  - [**1. Nguyễn Thị Bích Nguyên**](#1-nguyễn-thị-bích-nguyên)
  - [**2. Nguyễn Anh Hào**](#2-nguyễn-anh-hào)
  - [**3. Lê Hồng Sơn**](#3-lê-hồng-sơn)
  - [**4. Phan Thanh Hy**](#4-phan-thanh-hy)
  - [**6. Lưu Nguyễn Kì Thư**](#6-lưu-nguyễn-kì-thư)
- [**Controller Timeline**](#controller-timeline)
- [**Timeline**](#timeline)
  - [**Phase 1: 29-06-2022 to 10-07-2022**](#phase-1-29-06-2022-to-10-07-2022)
  - [**Phase 2: 10-07-2022 to 13-07-2022**](#phase-2-10-07-2022-to-13-07-2022)
  - [**Phase 3: 14-07-2022 to xx-xx-2022**](#phase-3-14-07-2022-to-xx-xx-2022)
- [**Post Script**](#post-script)
- [**Special Thank**](#special-thank)
- [**Made with 💘 and PHP <img src="https://www.vectorlogo.zone/logos/php/php-horizontal.svg" width="60">**](#made-with--and-php-)

# [**Topic**](#topic)
<p align="center">
    <img src="./photo/topic.png" width="640" />
</p>
<h3 align="center">

***Đề tài thực tập do thầy Nguyễn Anh Hào soạn***
</h3>

# [**Database**](#database)

<p align="center">
    <img src="./photo/database-diagram.png" width="640" />
</p>
<h3 align="center">

***Bản thiết kế tiêu chuẩn cơ sở dữ liệu cho đồ án thực tập***
</h3>

# [**Important Modules**](#important-modules)

Có 2 controller quan trọng mà có giá trị cao nhất trong đồ án này. Đó là phần xử lý giỏ hàng. 
Xử lý giỏ hàng có lẽ sẽ là phần củ khoai nhất trong đề tài thương mại điện tử.

Hãy chú ý coi qua các controller sau đây: 

## [**1. Admin Order Controller**](#1-admin-order-controller)

Controller này có những chức năng chính như sau: tìm giỏ hàng theo Id, thay đổi nội dung của giỏ hàng & xóa giỏ món hàng trong giỏ hàng

### [**1.1. Get Order By Id**](#11-get-order-by-id)

Tìm kiếm theo Id - chức năng như tên gọi, truyền một ID vào thì sẽ tìm ra giỏ hàng và các món hàng trong giỏ hàng đó. 

### [**1.2 - Modify Order**](#12---modify-order-thay-đổi-nội-dung-giỏ-hàng) 

Tức là thay đổi nội dung giỏ hàng

**Bước 1** - Cần truyền cho controller này: OrderId, Receiver_phone, receiver_address & receiver_phone. Đây sẽ là những trường dữ liệu bắt buộc phải có. 

**Bước 2** - Giỏ hàng chỉ có thể được thay đổi tùy thích trừ khi trạng thái của nó là một trong hai trạng thái sau: delivered & cancel.

**Bước 3**

- Trường hợp 1 - Khi trạng thái giỏ đang là **processing** => **['verified', 'packed', 'being transported', 'delivered' ]** thì số lượng tồn của các sản phẩm sẽ giảm đi tương ứng với số lượng có trong giỏ hàng.

Ví dụ: Mình mua 3 sản phẩm A và 1 sản phẩm B thì số lượng tồn của nó sẽ bị trừ đi lần lượt là 3 và 2.

Trường hợp mua hàng nhưng có sản phẩm không đủ số lượng thì chương trình sẽ xuất ra thông báo.

> Oops ! Sản phẩm Laptop MSI, Laptop MSi 14 đã hết hàng

- Trường hợp 2 - Khi trạng thái giỏ đang là **["verified", "packed", "being transported"]** => **cancel** thì số lượng sản phẩm sẽ được hoàn trả về như cũ.

Ví dụ: Mình mua 1 sản phẩm A và 1 sản phẩm B, nếu mình hủy giỏ hàng thì số lượng tồn sẽ được cộng lên 1 đơn vị mỗi món hàng.

**Bước 4** - Các quá trình xử lý trên hoàn tất thì sẽ lưu dữ liệu vào cơ sở dữ liệu

### [**1.3. Delete**](#13-delete)

**Bước 1** - Truyền vào OrderID 

**Bước 2** - Nếu giỏ hàng đang ở các trạng thái **["being transported", "deliverd", "verified"]** thì sẽ không cho xóa.

**Bước 3** - Ta chỉ xóa giỏ hàng nếu nó không ở thuộc các trạng thái bước 2 + không có bất kì món hàng trong giỏ hàng này.

**Bước 4** - Lưu các thay đổi vào cơ sở dữ liệu nếu các bước 2 và 3 không bị vi phạm.

## [**2. Admin Orders Controller**](#2-admin-orders-controller)

## [**3. Orders Controller**](#3-orders-controller)


# [**Mentor**](#mentor)

Vài dòng mình để đây không có chủ đích xúc phạm giáo viên nào nhưng mình bức xúc quá nên đánh phá luận vậy

## [**1. Nguyễn Thị Bích Nguyên**](#nguyen-thi-bich-nguyen)

Nói thẳng luôn nhé ! Bạn nào mà trúng cô hướng dẫn làm đồ án thì cứ phải gọi là đen vãi cả l*n. Vì mình là người bị phân trúng vào cô Nguyên nên mình cực khó chịu vì lý do sau: Thông thường, 
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

## [**2. Nguyễn Anh Hào**](#nguyen-anh-hao)

Người thầy chọn mặt gửi vàng của Phong.

Do trúng cô Nguyên nên thực sự là mình không muốn đề người hướng dẫn là cô Nguyên. Qua sự tư vấn từ bạn `Huỳnh Phước Sang` nên mình quyết tâm liên 
lạc với thầy để nhận được sự giúp đỡ từ thầy.

Thật may mắn là sự nỗ lực đã được đền đáp xứng đáng. Thầy Hào cho đã chấp nhận sẽ hướng dẫn cho mình và đồng ý với đề tài mà mình tự 
chọn tại nơi thực tập thay vì phải làm đề tài của thầy soạn.

Từ lúc nhận đề tài(ngày 29-06-2022) tới khi viết những dòng lưu bút này(10-07-2022) thầy vẫn chưa có hồi âm gì về hướng dẫn cả. Có lẽ do thầy bận hoặc 
thầy muốn để sinh viên tự làm đúng hay không ? Mình cũng không biết nữa.

## [**3. Lê Hồng Sơn**](#le-hong-son)

Là giám đốc của Học viện mình. Đứng ở cương vị là người quản lý cấp cao nhất của nhà trường, ông thầy 
này ra rất nhiều yêu cầu nhưng cực kì mơ hồ, mông lung. Nếu bạn muốn làm đồ án cẩn thận thì nên né ông này luôn 

## [**4. Phan Thanh Hy**](#phan-thanh-hy)

Dĩ nhiên, mình không làm đồ án dưới sự hướng dẫn của thầy. Nhưng tiếng lành đồn xa😝😝, là `người hướng dẫn đồ án 
tào lao bí đao nhất Học viện` nên hàng năm có cực kì nhiều sinh viên liên hệ với thầy để hướng 
dẫn làm đồ án thực tập. Tại do `tào lao bí đao` quá nên thầy này chấm đồ án thực tập rất dễ. 

Mình khuyến khích bạn nào học yếu nên liên hệ trước với thầy luôn để ra trường sẽ dễ thở hơn.

## [**6. Lưu Nguyễn Kì Thư**](#luu-nguyen-ki-thu)

Giảng viên huyền thoại của trường PTIT. Người đã góp phần tạo nên bao nỗi ác mộng cho các thế hệ sinh viên Học viện. Tuy nhiên,
nếu bạn nào muốn có 1 người thấy chi tiết, hỗ trợ nhiệt tình tới tận răng thì nên chọn thầy. 

Lưu ý duy nhất là hãy suy nghĩ xem bạn học có đủ khá | giỏi không ? Vì thầy tuy chi tiết nhưng ra đề tài làm đồ án thực tập cũng tương đối phức tạp & chấm rất gắt 😨😨

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

8. Admin Orders Controller

9. Admin Order Controller

10. Admin Orders Content Controller

11. Admin Reviews Controller

12. Products Controller 

13. Product Controller

14. Orders Controller - lấy ra giỏ hàng mới nhất chưa thanh toán & tùy biến nội dung của giỏ hàng.

15. Profile Controller - lấy ra thông tin tài khoản bằng access Token.

# [**Timeline**](#timeline)

## [**Phase 1: 29-06-2022 to 10-07-2022**](#phase-1-29-06-2022-to-10-07-2022)

> Ý nghĩa: Giai đoạn này phát triển API cho phía người quản trị viên.

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
   
- **05-07-2022**
1. U.D cho sản phẩm 
2. Tinh chỉnh lại upload ảnh cho sản phẩm
3. Create cho AdminOrdersController()

- **06-07-2022**
1. C.R.U.D cho AdminOrders | Admin Order controller

- **07-07-2022**
1. Admin Orders Content Controller với đọc nội dung đơn hàng và cập nhật số lượng sản phẩm trong đơn hàng - chức năng gì 
mà loằng ngoằng quá 😴🥱. Mất mịa cả buổi tối rùi

- **09-07-2022**
1. Hoàn thiện các tính năng cập nhật, thay đổi thông tin của một đơn hàng
2. Liệt kê toàn bộ bình luận & thêm mới bình luận ở vai trò Admin

- **10-07-2022**
1. Xong toàn bộ tính năng quản lý bình luận

## [**Phase 2: 10-07-2022 to 13-07-2022**](#phase-2-10-07-2022-to-13-07-2022)

> Ý nghĩa: Giai đoạn này phát triển API cho phía người người dùng.

- **10-07-2022**
1. Products Controller - lấy danh sách sản phẩm 
2. Product Controller - chi tiết 1 sản phẩm theo ID truyền vào

- **11-07-2022**
1. Orders Controller - lấy ra giỏ hàng cho người sử dụng và thay đổi nội dung trong giỏ hàng

- **12-07-2022**
1. Tinh chỉnh lại cách xử lý giỏ hàng ở phía người dùng. Nếu không có đủ hàng sẽ không cho đơn hàng đó
tiếp tục

- **13-07-2022**
1. Tinh chỉnh lại cách xử lý giỏ hàng ở phía người quản trị. Nếu không đủ hàng sẽ không cho đơn hàng đó
tiếp tục
2. Tối ưu hóa quy trình kiểm tra dữ liệu đầu vào cho Sign Up Controller với bộ lọc cho firt_name và last_name.

## [**Phase 3: 14-07-2022 to xx-xx-2022**](#phase-3-14-07-2022-to-xx-xx-2022)

- **14-07-2022**
1. Dựng thư mục đồ án Android
2. Hoàn thành màn hình đăng nhập và hiệu ứng loading screen

- **16-07-2022**
1. Thêm Profile Controller để lấy ra thông tin người dùng
2. Màn hình intro, đăng nhập cho Android

- **17-07-2022**
1. Hoàn thiện trang chủ ứng dụng Android
2. Hoàn thiện màn hình kết quả tìm kiếm

- **18-07-2022**
1. Xong các thao tác tìm kiếm bằng SearchView và chọn theo nhu cầu ở trang chủ
2. Phác họa xong màn hình giao diện bộ lọc sản phẩm.

- **19-07-2022**
1. Xong xử lý logic cho màn hình giao diện bộ lọc sản phẩm
2. Dựng khung hình màn hình chi tiết sản phẩm

# [**Post Script**](#post-script)

[**11h41 PM Tuesday, 05-07-2022**](#)

Cả tối mới làm được 2 chức năng ảnh cho sản phẩm. Lo quá 😥 còn phía người dùng nữa. Sợ thực sự.

[**11:58 PM Thursday, 08-07-2022**](#)

Sài gòn hôm nay mưa to quá ☔. Đi làm về muộn mệt ghê. Thêm quả chứ năng chỉnh sửa nội dung đơn hàng cồng kềnh thật sự. 

[**12:11 AM Thursday, 10-07-2022**](#)

Nửa đêm rồi, sài gòn lại mưa thật hối hả ⛈. Phần API gần xong rùi, cháy hết mình nào 🔥. Chào chiến thắng ✌

À mai thứ 6 rồi 😍😍😎😎. Sắp tới ngày cuối tuần rùi😘😍. Mong thứ 6 qua thật nhanh

[**12:49 PM Tuesday, 12-07-2022**](#)

Buồn ngủ quá 😪. Cái giỏ hàng coi vậy mà phức tạp ra phết ấy chứ bộ !. Hên là đã xong được phía người dùng rồi.
Còn phía quản trị viên chưa làm. 

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