# Thiết Kế Chi Tiết Tính Năng Chat Nội Bộ (Internal Department Chat) & Kế Hoạch Nâng Cấp Toàn Diện

## 1. Tổng Quan & Hiện Trạng Module Chat (Current Architecture Audit)

Module Chat nội bộ hiện tại đã thiết lập nền tảng cơ bản:
- **Cơ sở dữ liệu**:
  - `chat_channels`: Quản lý kênh chat, hỗ trợ loại kênh `company`, `department`, `group`, trường `is_default` cho kênh `#cong-ty`.
  - `chat_channel_members`: Quản lý hội viên kênh, lưu `last_read_at` phục vụ tính toán tin chưa đọc (unread count), ràng buộc `UNIQUE(channel_id, user_id)`.
  - `chat_messages`: Lưu tin nhắn văn bản, hỗ trợ `SoftDeletes` và mốc `edited_at`.
- **Phân quyền & Chống IDOR**:
  - `ChatChannelPolicy`: Kiểm soát chặt chẽ quyền `view`, `sendMessage`, `create`, `update`, `delete`, `manageMembers`.
  - `ChatMessagePolicy`: Kiểm soát quyền `update` (chỉ tác giả) và `delete` (tác giả hoặc Admin/HR).
- **Service Layer**:
  - `ChatService`: Đóng gói logic truy vấn kênh kèm unread counts chống N+1, tạo kênh, gửi tin và cập nhật mốc đọc tin.
- **Realtime / Polling**:
  - Polling AJAX qua Alpine.js, đồng thời phát event `ChatMessageSent` (implements `ShouldBroadcast`).
- **Kiểm thử**:
  - 27 test features trên MySQL `hr_management_testing` bao phủ toàn bộ luồng kênh, thành viên, tin nhắn, IDOR và unread.

---

## 2. Kế Hoạch Nâng Cấp Toàn Diện (Comprehensive Upgrade Plan)

### 2.1 Tinh Chỉnh Giao Diện — Loại Bỏ Lạm Dụng Icon & Sửa Hướng Nút Gửi
- **Vấn đề hiện tại**:
  - Sử dụng quá nhiều icon/emoji trang trí ở sidebar, tiêu đề và các nút chức năng gây rối mắt, mất tính chuyên nghiệp của hệ thống doanh nghiệp.
  - Icon máy bay giấy (Send icon) trong ô soạn thảo tin nhắn bị xoay góc 90 độ (`transform rotate-90`), khiến mũi tên chỉ lệch xuống dưới hoặc sang bên thay vì hướng gửi đi.
- **Giải pháp**:
  - Thiết kế lại giao diện tối giản, thanh lịch, hiện đại theo phong cách Slack / Microsoft Teams.
  - Ưu tiên nhãn chữ rõ nghĩa: "Gửi", "Tạo kênh", "Thành viên", "Đính kèm", "Thêm nhân sự".
  - Sửa lại SVG và class của nút gửi: biểu tượng máy bay gửi tin hướng lên trên / sang phải trực quan, đồng thời hỗ trợ nút "Gửi" rõ ràng.

### 2.2 Rich Text Message & Formatting An Toàn
- **Hỗ trợ định dạng văn bản**:
  - Đậm (`**nội dung**`) -> `<strong>nội dung</strong>`
  - Nghiêng (`*nội dung*`) -> `<em>nội dung</em>`
  - Gạch ngang (`~~nội dung~~`) -> `<del>nội dung</del>`
  - Danh sách không thứ tự (`- mục` hoặc `• mục`) -> `<ul><li>mục</li></ul>`
  - Danh sách có thứ tự (`1. mục`) -> `<ol><li>mục</li></ol>`
  - Trích dẫn (`> nội dung`) -> `<blockquote>nội dung</blockquote>`
  - Tự động nhận diện liên kết an toàn: URL `http://` hoặc `https://` được chuyển thành thẻ `<a>` có thuộc tính `target="_blank" rel="noopener noreferrer"`.
- **Cơ chế chống XSS tuyệt đối**:
  - Toàn bộ nội dung người dùng nhập vào được chuyển qua hàm escape HTML (`e()` / `htmlspecialchars`) trước khi phân tích các ký tự định dạng.
  - Chặn đứng mọi mã độc Javascript, thuộc tính sự kiện (`onload`, `onerror`), thẻ `<script>` hay giao thức nguy hiểm `javascript:`.

### 2.3 Bộ Chọn Emoji Nhanh (Emoji Picker)
- Tích hợp bảng chọn emoji gọn gàng, chia theo nhóm cảm xúc thông dụng: `😀 😃 😂 😊 😍 👍 ❤️ 🎉 🔥 👏 🚀 💯 🤝 😢 😮 💼 ✅ ❌`.
- Chèn trực tiếp emoji vào vị trí con trỏ của ô soạn thảo tin nhắn.
- Cơ sở dữ liệu và bảng `chat_messages` sử dụng `utf8mb4` đảm bảo lưu trữ và hiển thị emoji hoàn hảo.

### 2.4 Đính Kèm Tệp & Gửi Hình Ảnh An Toàn (File & Image Attachments)
- **Bảng mới `chat_message_attachments`**:
  - `id`, `message_id` (FK cascade), `file_path`, `file_name`, `file_size`, `mime_type`, `is_image` (boolean), timestamps.
- **Xác thực định dạng & kích thước**:
  - Tệp tài liệu: `pdf`, `doc`, `docx`, `xls`, `xlsx`, `csv`, `txt` (tối đa 20MB).
  - Tệp hình ảnh: `jpg`, `jpeg`, `png`, `webp`, `gif` (tối đa 10MB).
  - Chặn hoàn toàn các file thực thi nguy hại: `php`, `js`, `exe`, `sh`, `bat`, `html`.
- **Bảo mật lưu trữ**:
  - Lưu trữ trong thư mục riêng tư (`storage/app/private/chat_attachments` hoặc `storage/app/chat_attachments`). Tuyệt đối không public trực tiếp qua web root.
  - Route tải/xem tệp `GET /chat/attachments/{attachment}`: Bắt buộc kiểm tra quyền qua Policy (người dùng phải là thành viên kênh hoặc cuộc trò chuyện 1-1). Chống IDOR khi tải file.
- **Xem trước & Dọn dẹp**:
  - Cho phép người dùng xem trước ảnh hoặc tên file đính kèm trước khi nhấn gửi, có nút "Xóa đính kèm" để hủy.

### 2.5 Thả Cảm Xúc Tin Nhắn (Reactions)
- **Bảng mới `chat_message_reactions`**:
  - `id`, `message_id` (FK cascade), `user_id` (FK cascade), `reaction` (VARCHAR 32), timestamps.
  - Ràng buộc duy nhất: `UNIQUE(message_id, user_id, reaction)` chống trùng lặp.
- **Quy tắc nghiệp vụ**:
  - Người dùng có thể click vào icon cảm xúc (`👍`, `❤️`, `😂`, `😮`, `😢`, `🎉`) để thả cảm xúc.
  - Nếu click lại vào cùng emoji -> Bỏ reaction (Toggle off).
  - Hiển thị danh sách badge reaction kèm số lượng và trạng thái đã thả của chính mình.
  - API endpoint: `POST /chat/messages/{message}/reactions`.

### 2.6 Thẻ Thông Tin Nhân Sự (Profile Card / User Popover)
- Khi click vào avatar của người gửi trong tin nhắn:
  - Hiển thị popover/modal hồ sơ công việc nội bộ thay vì chuyển hướng trang.
  - Thông tin hiển thị: Ảnh đại diện, Họ tên, Vai trò (Admin/HR/Nhân viên), Phòng ban, Chức danh công việc, Email, Số điện thoại công việc.
  - **Bảo mật thông tin HR nhạy cảm**: Tuyệt đối KHÔNG hiển thị CCCD/CMND, mã số thuế cá nhân, mức lương, phụ cấp, bảo hiểm xã hội, ngày sinh chi tiết hoặc tài liệu riêng tư.
  - Có nút hành động nổi bật: **"Nhắn tin"**.

### 2.7 Tin Nhắn Riêng 1-1 (Direct Messaging)
- **Thiết kế định danh duy nhất (Deterministic DM Identification)**:
  - Bổ sung loại kênh `direct` vào `chat_channels`.
  - Mã slug quy ước: `dm-{minId}-{maxId}` (ví dụ: User 2 và User 5 chat riêng -> luôn có slug là `dm-2-5`).
  - Đảm bảo tính duy nhất tuyệt đối: Khi A click nhắn tin với B hoặc B click nhắn tin với A, hệ thống đều trỏ về đúng một cuộc trò chuyện duy nhất, không bao giờ tạo duplicate conversation.
  - Kênh `direct` luôn có đúng 2 thành viên trong `chat_channel_members`. Cấm thêm thành viên thứ 3.
- **Phân quyền IDOR**:
  - Chỉ duy nhất 2 người tham gia có quyền xem, gửi tin nhắn, tải đính kèm và nhận thông báo. Mọi người dùng thứ ba (kể cả đoán biết URL) đều nhận lỗi `403 Forbidden`.
- **Giao diện Sidebar**:
  - Nhóm 1: `Toàn Công Ty` (`#cong-ty`).
  - Nhóm 2: `Phòng Ban` (`#ke-toan`, `#nhan-su`,...).
  - Nhóm 3: `Tin Nhắn Riêng` (Danh sách các cuộc trò chuyện trực tiếp kèm avatar đối phương, tên, chức danh và badge số tin chưa đọc).

### 2.8 Lọc Phòng Ban & Xác Thực Nghiệp Vụ Chặt Chẽ (Department Channel Filtering & Strict Server-side Validation)
- **Khi tạo kênh phòng ban**:
  - Chọn `Loại kênh = Phòng ban` và chọn `Phòng ban = Kế toán`:
    - Danh sách nhân sự tự động lọc chỉ hiển thị nhân viên thuộc phòng ban Kế toán.
    - Ô tìm kiếm nhân viên trong danh sách chỉ tìm kiếm trong phạm vi phòng ban đã chọn.
    - Nút "Chọn tất cả" / "Bỏ chọn" chỉ áp dụng cho nhân sự thuộc phòng ban đó.
- **Khi quản lý thành viên kênh phòng ban đã có**:
  - Màn hình thêm thành viên của kênh `#ke-toan` chỉ liệt kê các nhân sự thuộc phòng Kế toán chưa tham gia kênh.
- **Kiểm tra nghiêm ngặt phía Server (Server-side Enforcement)**:
  - Trong `ChatMemberController@store` và `ChatChannelController@store`:
    - Nếu kênh thuộc loại `department` có `department_id`, server kiểm tra mọi `user_id` được gửi lên.
    - Nếu phát hiện bất kỳ nhân sự nào có `Employee.department_id !== Channel.department_id`, server lập tức từ chối với lỗi `422 Unprocessable Entity` hoặc `403 Forbidden`.
    - Ngăn chặn hoàn toàn việc can thiệp payload từ client để thêm trái phép nhân sự phòng ban khác.
- **Đồng bộ khi nhân sự đổi phòng ban (Requirement 25)**:
  - *Chính sách áp dụng*: Khi nhân viên chuyển phòng ban, hệ thống tự động loại nhân viên khỏi các kênh thuộc phòng ban cũ và thêm vào kênh phòng ban mới, đảm bảo tính riêng tư của thông tin phòng ban.

---

## 3. Thiết Kế Cơ Sở Dữ Liệu Bổ Sung (Database Schema Additions)

### 3.1 Bảng `chat_message_reactions`
```sql
CREATE TABLE chat_message_reactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    reaction VARCHAR(32) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (message_id) REFERENCES chat_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY chat_message_reactions_unique (message_id, user_id, reaction),
    INDEX chat_message_reactions_message_idx (message_id)
);
```

### 3.2 Bảng `chat_message_attachments`
```sql
CREATE TABLE chat_message_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    is_image BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (message_id) REFERENCES chat_messages(id) ON DELETE CASCADE,
    INDEX chat_message_attachments_message_idx (message_id)
);
```

### 3.3 Cập Nhật Bảng `chat_channels`
- Mở rộng cột `type` để hỗ trợ giá trị `'direct'` (thông qua migration sửa đổi enum hoặc varchar an toàn).

---

## 4. Kế Hoạch Kiểm Thử Mới (Feature Tests Suite)

1. **`InternalChatRichMessageTest`**:
   - Gửi tin nhắn có định dạng Bold, Italic, Strikethrough, List, Quote.
   - Kiểm tra mã độc `<script>` và event handler bị escape an toàn, không sinh XSS.
   - Nhận diện URL an toàn và render link đúng định dạng.
   - Tải lên tệp đính kèm và hình ảnh hợp lệ.
   - Từ chối tệp không đúng định dạng (ví dụ `.php`, `.exe`) hoặc vượt quá dung lượng.
   - Kiểm tra phân quyền tải tệp đính kèm: Người ngoài kênh bị từ chối `403`.
2. **`InternalChatMessageReactionTest`**:
   - Thêm reaction vào tin nhắn.
   - Toggle bỏ reaction khi click lại.
   - Ngăn chặn duplicate reaction.
   - Phân quyền: Người không thuộc kênh không thể thả reaction (403).
3. **`InternalChatDirectMessageTest`**:
   - User A nhắn tin cho User B tạo ra Direct Channel duy nhất (`dm-A-B`).
   - User B nhắn tin cho User A tái sử dụng đúng Direct Channel đó, không tạo duplicate.
   - User C cố tình truy cập vào cuộc trò chuyện giữa A và B nhận `403 Forbidden` (chống IDOR).
   - Unread count hiển thị chính xác theo từng người nhắn.
4. **`InternalChatDepartmentValidationTest`**:
   - Thêm nhân sự đúng phòng ban vào kênh phòng ban: Thành công.
   - Cố tình gửi payload thêm nhân sự phòng ban khác vào kênh phòng ban: Bị từ chối với lỗi 422.
   - Tìm kiếm nhân sự chỉ giới hạn trong phòng ban đã chọn.
5. **`InternalChatProfileCardTest`**:
   - API / Endpoint trả về thông tin hồ sơ rút gọn nội bộ.
   - Đảm bảo các thông tin nhạy cảm (CCCD, mã số thuế, lương) không bị rò rỉ.
