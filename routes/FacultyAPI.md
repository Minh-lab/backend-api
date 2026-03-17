# Faculty API

Base: `http://localhost:8000/api/v1/faculty`

Headers:
- `Authorization: Bearer <token>`
- `Accept: application/json`
- `Content-Type: application/json` (chỉ khi có body)

## Semesters

GET `/semesters` - Danh sách học kỳ (phân trang)

POST `/semesters` - Tạo học kỳ mới

```json
{
  "year_name": "2026-2027",
  "semester_name": "HK1",
  "start_date": "2026-09-01",
  "end_date": "2027-01-15"
}
```

GET `/semesters/{id}` - Chi tiết học kỳ

## Milestones

GET `/semesters/{id}/milestones` - Danh sách milestone theo học kỳ (phân trang)

GET `/semesters/{id}/milestones/{milestoneId}` - Chi tiết milestone theo học kỳ

POST `/semesters/{id}/milestones` - Tạo milestone cho học kỳ (lấy `semester_id` từ `{id}`)

```json
{
  "phase_name": "M1",
  "description": "Mốc 1",
  "type": "CAPSTONE",
  "start_date": "2026-03-01",
  "end_date": "2026-03-15"
}
```

POST `/milestones` - Tạo milestone (cần `semester_id` trong body)

```json
{
  "semester_id": 1,
  "phase_name": "M1",
  "description": "Mốc 1",
  "type": "CAPSTONE",
  "start_date": "2026-03-01",
  "end_date": "2026-03-15"
}
```

PUT `/milestones/{milestone}` - Cập nhật milestone

```json
{
  "phase_name": "M1 (updated)",
  "description": "Mốc 1",
  "type": "CAPSTONE",
  "start_date": "2026-03-02",
  "end_date": "2026-03-16"
}
```
