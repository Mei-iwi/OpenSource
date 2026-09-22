# Thiết Kế Chi Tiết Tính Năng Chat Nội Bộ (Internal Department Chat)

## 1. Tổng Quan & Kiến Trúc Hệ Thống (Architecture)

Module **Chat Nội Bộ** cung cấp giải pháp trao đổi thông tin thời gian thực giữa các thành viên, phòng ban và toàn bộ công ty trong hệ sinh thái Laravel 12 Quản lý Nhân sự.

### 1.1 Nguyên Tắc Thiết Kế
- **Tương thích & Tái sử dụng**: Tận dụng triệt để kiến trúc hiện tại của project (Laravel 12, Blade, Tailwind CSS 3, Alpine.js, MySQL 8.4, Eloquent ORM).
- **Phân quyền chặt chẽ (Zero-Trust Authorization)**: Server-side authorization qua Laravel Policy và Middleware; chống rò rỉ dữ liệu hoặc vượt quyền (IDOR).
- **Trải nghiệm mượt mà**: UI 2 cột (Danh sách kênh & Vùng hội thoại) đồng bộ chuẩn giao diện Dark/Light mode và bảng màu hiện tại.
- **Sẵn sàng Realtime**: Giai đoạn đầu sử dụng Polling/AJAX hiệu năng cao với JSON endpoints; kiến trúc sẵn sàng kết nối Laravel Reverb / Laravel Echo khi kích hoạt broadcasting.

---

## 2. Thiết Kế Cơ Sở Dữ Liệu (Database Design)

### 2.1 Bảng `chat_channels`
Lưu trữ thông tin các kênh chat (Toàn công ty, theo Phòng ban, hoặc Nhóm dự án/riêng tư).

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | Primary Key, Auto Increment | ID định danh kênh |
| `name` | `VARCHAR(100)` | NOT NULL | Tên hiển thị của kênh |
| `slug` | `VARCHAR(120)` | NOT NULL, UNIQUE | Định danh URL / mã kênh (ví dụ: `cong-ty`, `ke-toan`) |
| `description` | `VARCHAR(255)` | NULLABLE | Mô tả mục đích kênh |
| `type` | `ENUM('company', 'department', 'group')` | NOT NULL, DEFAULT `'group'` | Phân loại kênh |
| `department_id` | `BIGINT UNSIGNED` | NULLABLE, Foreign Key -> `departments(id)` ON DELETE SET NULL | Phòng ban liên kết (nếu có) |
| `created_by` | `BIGINT UNSIGNED` | NULLABLE, Foreign Key -> `users(id)` ON DELETE SET NULL | Người tạo kênh |
| `is_default` | `BOOLEAN` | NOT NULL, DEFAULT `FALSE` | Kênh mặc định toàn công ty (không thể xóa) |
| `created_at` | `TIMESTAMP` | NULLABLE | Thời điểm tạo |
| `updated_at` | `TIMESTAMP` | NULLABLE | Thời điểm cập nhật |

**Indexes**:
- `chat_channels_slug_unique` (`slug`)
- `chat_channels_type_index` (`type`)
- `chat_channels_department_id_index` (`department_id`)
- `chat_channels_is_default_index` (`is_default`)

### 2.2 Bảng `chat_channel_members`
Lưu danh sách thành viên tham gia từng kênh và mốc thời gian đọc tin nhắn gần nhất.

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | Primary Key, Auto Increment | ID bản ghi thành viên |
| `channel_id` | `BIGINT UNSIGNED` | NOT NULL, Foreign Key -> `chat_channels(id)` ON DELETE CASCADE | Kênh chat |
| `user_id` | `BIGINT UNSIGNED` | NOT NULL, Foreign Key -> `users(id)` ON DELETE CASCADE | Người dùng |
| `joined_at` | `TIMESTAMP` | NOT NULL, DEFAULT `CURRENT_TIMESTAMP` | Thời điểm tham gia kênh |
| `last_read_at` | `TIMESTAMP` | NULLABLE | Thời điểm đọc tin nhắn gần nhất (tính unread) |
| `created_at` | `TIMESTAMP` | NULLABLE | Thời điểm tạo |
| `updated_at` | `TIMESTAMP` | NULLABLE | Thời điểm cập nhật |

**Indexes & Constraints**:
- `chat_channel_members_channel_user_unique` (`channel_id`, `user_id`)
- `chat_channel_members_user_id_index` (`user_id`)

### 2.3 Bảng `chat_messages`
Lưu trữ nội dung tin nhắn trao đổi trong kênh.

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | Primary Key, Auto Increment | ID tin nhắn |
| `channel_id` | `BIGINT UNSIGNED` | NOT NULL, Foreign Key -> `chat_channels(id)` ON DELETE CASCADE | Kênh chứa tin nhắn |
| `user_id` | `BIGINT UNSIGNED` | NOT NULL, Foreign Key -> `users(id)` ON DELETE CASCADE | Người gửi |
| `message` | `TEXT` | NOT NULL | Nội dung văn bản |
| `edited_at` | `TIMESTAMP` | NULLABLE | Thời điểm chỉnh sửa gần nhất |
| `deleted_at` | `TIMESTAMP` | NULLABLE | Thời điểm xóa mềm (Soft Deletes) |
| `created_at` | `TIMESTAMP` | NULLABLE | Thời điểm gửi |
| `updated_at` | `TIMESTAMP` | NULLABLE | Thời điểm cập nhật |

**Indexes**:
- `chat_messages_channel_created_index` (`channel_id`, `created_at`)
- `chat_messages_user_id_index` (`user_id`)

---

## 3. Quan Hệ Mô Hình (Model Relationships)

### 3.1 Model `ChatChannel`
- `department()`: `belongsTo(Department::class)`
- `creator()`: `belongsTo(User::class, 'created_by')`
- `members()`: `hasMany(ChatChannelMember::class, 'channel_id')`
- `users()`: `belongsToMany(User::class, 'chat_channel_members', 'channel_id', 'user_id')->withPivot('joined_at', 'last_read_at')`
- `messages()`: `hasMany(ChatMessage::class, 'channel_id')`
- Scopes & Helpers:
  - `isCompany()`: kiểm tra xem kênh có phải toàn công ty không (`type === 'company' || is_default`).
  - `hasMember(int $userId)`: kiểm tra xem user có phải là thành viên kênh hay không.
  - `unreadCountFor(int $userId)`: tính số tin nhắn chưa đọc đối với user cụ thể.

### 3.2 Model `ChatChannelMember`
- `channel()`: `belongsTo(ChatChannel::class, 'channel_id')`
- `user()`: `belongsTo(User::class, 'user_id')`

### 3.3 Model `ChatMessage`
- `use SoftDeletes;`
- `channel()`: `belongsTo(ChatChannel::class, 'channel_id')`
- `user()`: `belongsTo(User::class, 'user_id')`
- `isEdited()`: kiểm tra `$message->edited_at !== null`

### 3.4 Bổ sung vào Model Hiện Tại
- `User`:
  - `chatChannels()`: `belongsToMany(ChatChannel::class, 'chat_channel_members')`
  - `chatMessages()`: `hasMany(ChatMessage::class)`
- `Department`:
  - `chatChannels()`: `hasMany(ChatChannel::class)`

---

## 4. Phân Quyền & Bảo Mật (Authorization & Security)

### 4.1 Ma Trận Phân Quyền

| Thao tác | Admin | HR | Employee |
|---|---|---|---|
| Xem danh sách kênh được cấp quyền | Có | Có | Chỉ kênh là thành viên + Toàn công ty |
| Xem nội dung kênh Toàn công ty | Có | Có | Có |
| Xem nội dung kênh Phòng ban / Nhóm | Khi là thành viên | Khi là thành viên | Khi là thành viên |
| Gửi tin nhắn trong kênh được phép | Có | Có | Có |
| Sửa tin nhắn của chính mình | Có | Có | Có |
| Sửa tin nhắn của người khác | Không | Không | Không |
| Xóa tin nhắn của chính mình | Có | Có | Có |
| Xóa tin nhắn vi phạm của người khác | Có | Có | Không |
| Tạo kênh mới | Có | Có | Không |
| Quản lý kênh (Sửa tên, mô tả) | Có (Trừ Toàn công ty) | Có (Trừ Toàn công ty) | Không |
| Xóa kênh | Có (Trừ Toàn công ty) | Có (Trừ Toàn công ty) | Không |
| Thêm/Xóa thành viên kênh | Có (Trừ Toàn công ty) | Có (Trừ Toàn công ty) | Không |

### 4.2 Triển khai Policy Chống IDOR
- `ChatChannelPolicy`:
  - `view(User $user, ChatChannel $channel)`: trả về `true` nếu `$channel->isCompany()`, hoặc nếu user có trong `chat_channel_members`. Nếu không, trả về `403 Forbidden`.
  - `sendMessage(User $user, ChatChannel $channel)`: bắt buộc `$channel->isCompany() || $channel->hasMember($user->id)`.
  - `manage(User $user, ChatChannel $channel)`: kiểm tra `$user->isAdmin() || $user->isHr()`, đồng thời ngăn chặn thao tác phá vỡ kênh `#cong-ty`.
  - `manageMembers(User $user, ChatChannel $channel)`: kiểm tra `$user->isAdmin() || $user->isHr()` và `$channel->type !== 'company'`.
- `ChatMessagePolicy`:
  - `update(User $user, ChatMessage $message)`: `$user->id === $message->user_id`.
  - `delete(User $user, ChatMessage $message)`: `$user->id === $message->user_id || $user->isAdmin() || $user->isHr()`.

### 4.3 Phòng Chống Lỗ Hổng Web (OWASP Checklist)
- **IDOR**: Mọi request đến `/chat/channels/{channel}/*` đều chạy qua middleware xác thực và `authorize()` tại controller/service.
- **XSS**: Dữ liệu tin nhắn lưu nguyên văn bản thuần (trim whitespace) và render an toàn qua Blade `{{ $message->message }}` hoặc JSON encode an toàn với escaping. Tuyệt đối không dùng `{!! !!}` cho nội dung chat.
- **CSRF**: Mọi request POST/PATCH/DELETE yêu cầu token CSRF.
- **Mass Assignment**: Toàn bộ model khai báo `$fillable` chặt chẽ.

---

## 5. Danh Sách Tuyến Đường (Routes)

Nhóm route được bảo vệ bởi middleware `['auth', 'account.active']`:

```php
Route::middleware(['auth', 'account.active'])->prefix('chat')->name('chat.')->group(function () {
    // Giao diện chính
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/channels/{channel:slug}', [ChatController::class, 'show'])->name('channels.show');

    // Quản lý kênh (Admin & HR)
    Route::middleware('role:admin,hr')->group(function () {
        Route::get('/channels/create', [ChatChannelController::class, 'create'])->name('channels.create');
        Route::post('/channels', [ChatChannelController::class, 'store'])->name('channels.store');
        Route::get('/channels/{channel:slug}/edit', [ChatChannelController::class, 'edit'])->name('channels.edit');
        Route::patch('/channels/{channel:slug}', [ChatChannelController::class, 'update'])->name('channels.update');
        Route::delete('/channels/{channel:slug}', [ChatChannelController::class, 'destroy'])->name('channels.destroy');

        // Quản lý thành viên
        Route::get('/channels/{channel:slug}/members', [ChatMemberController::class, 'index'])->name('channels.members.index');
        Route::post('/channels/{channel:slug}/members', [ChatMemberController::class, 'store'])->name('channels.members.store');
        Route::delete('/channels/{channel:slug}/members/{user}', [ChatMemberController::class, 'destroy'])->name('channels.members.destroy');
    });

    // Tin nhắn & Trạng thái đọc (API & Web)
    Route::get('/channels/{channel:slug}/messages', [ChatMessageController::class, 'index'])->name('channels.messages.index');
    Route::post('/channels/{channel:slug}/messages', [ChatMessageController::class, 'store'])->name('channels.messages.store');
    Route::patch('/messages/{message}', [ChatMessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/{message}', [ChatMessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/channels/{channel:slug}/read', [ChatController::class, 'markAsRead'])->name('channels.read');
    Route::get('/unread-summary', [ChatController::class, 'unreadSummary'])->name('unread-summary');
});
```

---

## 6. Thiết Kế Giao Diện Người Dùng (UI/UX)

### 6.1 Layout Hai Cột Tương Thích Responsive
1. **Cột Trái (Sidebar Kênh Chat)**:
   - Thanh tìm kiếm kênh (lọc trực tiếp bằng Alpine.js).
   - Nút `+ Tạo kênh` (chỉ hiển thị cho Admin và HR).
   - Nhóm 1: `🌐 Kênh Công Ty` (`#cong-ty` gắn biểu tượng toàn cầu và nhãn mặc định).
   - Nhóm 2: `🏢 Kênh Phòng Ban` (`#ke-toan`, `#nhan-su`, `#kinh-doanh`,...).
   - Nhóm 3: `👥 Kênh Nhóm / Riêng Tư`.
   - Huy hiệu (Badge) số tin nhắn chưa đọc nổi bật khi có tin nhắn mới.
2. **Cột Phải (Vùng Chat)**:
   - **Header**: Tên kênh `#slug`, mô tả, số thành viên, nút mở danh sách thành viên (cho Admin/HR quản lý hoặc xem danh sách), nút cài đặt kênh (cho Admin/HR).
   - **Message Feed**: Phân trang / tải lịch sử tin nhắn; hiển thị avatar, tên người gửi, chức vụ / phòng ban, thời gian gửi (định dạng thân thiện `H:i` hoặc ngày tháng); phân biệt rõ bóng chat của bản thân và người khác. Nhãn "Đã chỉnh sửa" nếu `edited_at` có giá trị.
   - **Thanh nhập tin nhắn (Composer)**: Ô nhập tin nhắn hỗ trợ phím Enter để gửi, Shift+Enter xuống dòng, nút Gửi với icon trực quan.

---

## 7. Chiến Lược Realtime & Thông Báo (Realtime & Notification Strategy)

- **Hiện tại**: Project chưa tích hợp Reverb/Pusher. Do đó, hệ thống sử dụng **Long-polling / High-frequency AJAX polling** kết hợp Alpine.js:
  - Khi người dùng đang mở một kênh: Polling tin nhắn mới (`/chat/channels/{slug}/messages?after_id={lastId}`) mỗi 3-4 giây.
  - Polling trạng thái chưa đọc tổng thể (`/chat/unread-summary`) mỗi 10-15 giây để cập nhật số lượng unread trên sidebar.
- **Sẵn sàng nâng cấp**:
  - Tạo event `App\Events\ChatMessageSent` triển khai `ShouldBroadcastNow` / `ShouldBroadcast`.
  - Khi triển khai Laravel Reverb và Echo, chỉ cần bật broadcast driver mà không cần viết lại nghiệp vụ hay API.

---

## 8. Chiến Lược Seeder & Migration (Migration Strategy)

### 8.1 Migration
- Viết migration an toàn:
  - Khóa ngoại với `onDelete('cascade')` cho thành viên và tin nhắn khi kênh bị xóa.
  - Khóa ngoại `onDelete('set null')` cho `department_id` và `created_by`.
  - Unique index `['channel_id', 'user_id']` để đảm bảo tính duy nhất.
- Hỗ trợ đầy đủ phương thức `down()`.

### 8.2 Seeder
- Bổ sung `ChatChannelSeeder` hoặc tích hợp vào `DatabaseSeeder`:
  - Tự động tạo kênh `#cong-ty` (type: `company`, `is_default: true`) bằng `firstOrCreate` để đảm bảo chạy lại nhiều lần không tạo trùng lặp.
  - Tự động tạo các kênh theo phòng ban mẫu: `#hanh-chinh-nhan-su`, `#cong-nghe-thong-tin`, `#kinh-doanh`, `#tai-chinh-ke-toan`, `#cham-soc-khach-hang`.
  - Tự động gán nhân sự các phòng ban tương ứng vào các kênh phòng ban.
  - Tự động gán toàn bộ người dùng đang hoạt động vào kênh `#cong-ty`.

---

## 9. Kế Hoạch Kiểm Thử (Test Strategy)

Bộ test toàn diện sử dụng MySQL `hr_management_testing`:
1. **InternalChatChannelTest**:
   - Admin và HR có thể tạo kênh mới.
   - Employee không có quyền tạo kênh (nhận 403).
   - Kiểm tra ràng buộc duy nhất tên/slug kênh.
   - Kênh `#cong-ty` mặc định không thể bị xóa hoặc sửa trái phép.
2. **InternalChatMembershipTest**:
   - Thêm thành viên vào kênh (ngăn chặn trùng lặp).
   - Xóa thành viên khỏi kênh.
   - Thành viên sau khi bị xóa lập tức bị chặn truy cập (403).
3. **InternalChatMessageTest**:
   - Thành viên gửi tin nhắn thành công.
   - Tin nhắn rỗng hoặc chỉ có khoảng trắng bị từ chối validation.
   - Xử lý độ dài tin nhắn tối đa.
   - Kiểm tra XSS: script tag được mã hóa an toàn, không bị thực thi.
   - Người gửi có thể chỉnh sửa tin nhắn của mình; không thể sửa tin nhắn của người khác.
   - Người gửi có thể xóa tin nhắn của mình.
4. **InternalChatAuthorizationIdorTest**:
   - Người dùng ngoài kênh cố truy cập URL `/chat/channels/{slug}` nhận mã lỗi 403.
   - Người dùng ngoài kênh cố gọi API gửi tin nhắn hoặc xem tin nhắn nhận mã lỗi 403.
   - Chống tráo đổi ID kênh và tin nhắn.
5. **InternalChatUnreadTest**:
   - Tin nhắn mới từ người khác làm tăng số đếm unread.
   - Tin nhắn của chính mình không làm tăng unread.
   - Khi truy cập kênh, `last_read_at` được cập nhật và số đếm unread trở về 0.
