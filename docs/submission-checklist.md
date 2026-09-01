# Submission checklist

- [ ] Không tracked `.env`, APP_KEY hoặc DB password.
- [ ] `composer validate`, test, view cache và `npm run build` pass.
- [ ] Dockerfile multi-stage, có `pdo_pgsql`/`pdo_mysql`, startup migrate an toàn.
- [ ] Render Postgres và Persistent Disk đã được cấu hình thủ công nếu cần.
- [ ] Không ghi `DEPLOYED SUCCESSFULLY` khi chưa smoke test URL thật.
