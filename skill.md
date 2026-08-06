# 立法院 API（LYAPI） — `tw.openfun~api~legislation`

給 AI 閱讀的使用指引。人類可在 https://data.openfun.tw/datasets/tw.openfun~api~legislation 看到資料集說明。

---

## ⚠️ 開始之前（AI agent 必讀）

**Base URL**：`https://ly.govapi.tw/v2`
**認證**：使用 data.openfun.tw 核發的 Bearer Token 可解除流量限制；不帶 Token 仍可呼叫，但可能因超過流量門檻被擋（門檻隨時調整，不保證特定數字）。
**回應格式**：一律 `application/json`，CORS 完全開放（前端可直接呼叫）。

最簡查詢範例：
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/bills?limit=1"
```

**取得 Token 方式（Device Authorization Grant）：**
```bash
# 步驟一：取得驗證連結
curl -X POST https://data.openfun.tw/api/v1/auth/device

# 步驟二：在瀏覽器開啟回應中的 verification_uri_complete，用 Google 帳號登入授權

# 步驟三：輪詢取得 Token（約 10-30 秒後成功）
curl -X POST https://data.openfun.tw/api/v1/auth/token \
  -d "device_code=DEVICE_CODE_FROM_STEP1"
```
若無 Token，也可在 https://data.openfun.tw/user 登入後從 Dashboard 取得長效 API 金鑰。

禁止用 WebFetch 抓 HTML 頁面，請直接呼叫 API。

---

## ⚠️ 分頁深度限制（Elasticsearch result window）

底層是 Elasticsearch，`(page - 1) * limit + limit` 超過 **10000** 時會回 `413`：

```json
{"error": true, "message": "錯誤，請縮小查詢範圍或調整分頁參數後重試"}
```

例如 `limit=100` 時，`page` 最多只能到 100（`https://ly.govapi.tw/v2/bills?limit=100&page=101` 會 413）。想深入瀏覽超過一萬筆以後的資料時，**用篩選參數縮小範圍**，不要單靠加大 `page`。

`limit` 預設 100（未帶 `limit` 時的實測值）。

---

## 資料型別與端點

一共 12 種資料型別，每種都有「列表」與「單筆」兩種端點：

| 型別 | 列表端點 | 單筆端點 | ID 欄位 |
|------|----------|----------|---------|
| 議案 | `GET /bills` | `GET /bill/{議案編號}` | `議案編號` |
| 立法委員 | `GET /legislators` | `GET /legislator/{屆}/{委員姓名}` | `屆`, `委員姓名` |
| 法律 | `GET /laws` | `GET /law/{法律編號}` | `法律編號` |
| 法律版本 | `GET /law_versions` | `GET /law_version/{版本編號}` | `版本編號` |
| 法條 | `GET /law_contents` | `GET /law_content/{法條編號}` | `法條編號` |
| 委員會 | `GET /committees` | `GET /committee/{委員會代號}` | `委員會代號` |
| 會議 | `GET /meets` | `GET /meet/{會議代碼}` | `會議代碼` |
| 公報 | `GET /gazettes` | `GET /gazette/{公報編號}` | `公報編號` |
| 公報目錄 | `GET /gazette_agendas` | `GET /gazette_agenda/{公報議程編號}` | `公報議程編號` |
| 質詢 | `GET /interpellations` | `GET /interpellation/{質詢編號}` | `質詢編號` |
| 影片（IVOD） | `GET /ivods` | `GET /ivod/{IVOD_ID}` | `IVOD_ID` |
| 表決 | `GET /votes` | `GET /vote/{表決代碼}` | `表決代碼` |

> 注意：URL 路徑一律用底線（`law_versions`、`gazette_agendas`），**不是**駝峰或無底線；例如 `/lawversions` 會 404，必須用 `/law_versions`。

---

## 通用查詢參數

所有列表端點共用這組參數：

| 參數 | 說明 | 範例 |
|------|------|------|
| `page` / `limit` | 分頁（`limit` 預設 100） | `?page=2&limit=50` |
| `q` | 全文搜尋（各型別搜尋欄位不同，見下方 `supported_filter_fields`） | `?q=食品安全` |
| `sort` | 排序欄位，`-` 前綴表示反向 | `?sort=-最新進度日期` |
| `agg` | 對某欄位做聚合統計（回傳於 `aggs`） | `?agg=議案類別` |
| （型別專屬篩選欄位） | 每種型別不同，實際可用欄位會列在回應的 `supported_filter_fields` | `?屆=11&會期=5` |

**列表回應格式：**
```json
{
  "total": 147241,
  "total_page": 2945,
  "page": 1,
  "limit": 50,
  "bills": [ ... ],
  "filter": {},
  "aggs": [],
  "supported_filter_fields": ["屆", "會期", "議案類別", ...]
}
```

**單筆回應格式：**
```json
{
  "error": false,
  "id": ["202110228820000"],
  "data": { ... },
  "supported_relations": {
    "meets": { "type": "meet", "subject": "議案相關會議" }
  }
}
```
找不到資料時回 `{"error": true, "message": "找不到資料"}`。

**關聯資源（relations）**：單筆端點回應的 `supported_relations` 列出該筆資料可查詢哪些關聯資源，用 `GET /{型別}/{id}/{relation名稱}` 呼叫，回應格式跟該關聯型別的列表端點相同（如下方範例）。

---

## 查詢範例（皆為實測可用）

**1. 議案列表，篩選屆別 + 會期：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/bills?屆=11&會期=5&limit=5"
```

**2. 議案全文搜尋：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/bills?q=食品安全衛生管理法&limit=5"
```

**3. 議案類別聚合統計：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/bills?agg=議案類別&limit=0"
# => {"aggs": [{"agg": "議案類別", "buckets": [{"議案類別": "法律案", "count": 33559}, ...]}]}
```

**4. 立委單筆查詢（以屆+姓名為 ID）：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/legislator/11/韓國瑜"
```

**5. 表決查詢，篩選特定委員投贊成票的記錄：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/votes?贊成=傅崐萁&limit=5"
```

**6. 議案的關聯會議（relation）：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/bill/202110228820000/meets"
```

---

## Swagger / OpenAPI 文件

完整的欄位定義、每種型別實際支援的篩選欄位，都可以從動態產生的 OpenAPI spec 查到：

```
GET https://ly.govapi.tw/v2/swagger.yaml
```

（人類可讀的 Swagger UI 在 https://ly.govapi.tw/swagger）

---

## 快速參考

| 項目 | 說明 |
|------|------|
| Base URL | `https://ly.govapi.tw/v2` |
| 認證 | `Authorization: Bearer {token}` 可解除流量限制（不帶也能呼叫） |
| 取得 Token | https://data.openfun.tw/user |
| 分頁深度上限 | `(page-1)*limit + limit <= 10000`，超過回 413 |
| `limit` 預設值 | 100 |
| 全文搜尋參數 | `q` |
| 聚合統計參數 | `agg` |
| 排序參數 | `sort`（`-` 前綴反向） |
| OpenAPI spec | `https://ly.govapi.tw/v2/swagger.yaml` |
| 資料型別數 | 12（議案／立委／法律／法律版本／法條／委員會／會議／公報／公報目錄／質詢／影片／表決） |
