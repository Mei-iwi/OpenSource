# Submission checklist

- [ ] Không tracked `.env`, APP_KEY, DB password hoặc R2 credentials.
- [ ] `composer validate`, test và `npm run build` pass.
- [ ] Dockerfile multi-stage, start script migrate an toàn, không seed mặc định.
- [ ] Đã cấu hình MySQL/R2/Render thủ công và lưu screenshot/evidence nếu cần.
- [ ] Không ghi nhận `DEPLOYED SUCCESSFULLY` khi chưa smoke test URL thật.
