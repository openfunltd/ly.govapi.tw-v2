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

一共 13 種資料型別，每種都有「列表」與「單筆」兩種端點：

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
| 公報發言 | `GET /speeches` | `GET /speech/{發言編號}` | `發言編號` |
| 會議段落 | `GET /meet_sections` | `GET /meet_section/{段落編號}` | `段落編號` |

> 注意：URL 路徑一律用底線（`law_versions`、`gazette_agendas`），**不是**駝峰或無底線；例如 `/lawversions` 會 404，必須用 `/law_versions`。
> 列表端點是單數加 s，只有公報發言是不規則複數 `/speeches`（`/speechs` 也接受）。

**ID 格式提醒：**
- `質詢編號` 是 `{屆}-{會期}-{編號}`（例 `11-3-22`），編號是該會期的連續流水號。同一則質詢可能在多次院會的關係文書重複刊登，`會議代碼列表` 列出全部，`刊登紀錄` 列出每次的印發日期與頁碼。舊的四段式 `{屆}-{會期}-{會次}-{編號}`（例 `11-3-8-22`）會 301 轉到新編號。
- `發言編號` 是 `{公報文件編號}-{順序}`（例 `1158101_00005-0711`）。

---

## 通用查詢參數

所有列表端點共用這組參數：

| 參數 | 說明 | 範例 |
|------|------|------|
| `page` / `limit` | 分頁（`limit` 預設 100） | `?page=2&limit=50` |
| `q` | 全文搜尋（各型別搜尋欄位不同，見下方 `supported_filter_fields`） | `?q=食品安全` |
| `sort` | 排序欄位，欄位名後接 `<` 升冪、`>` 降冪（不接預設降冪；**不是** `-` 前綴，`-` 會回錯誤） | `?sort=最新進度日期>`、`?sort=順序<` |
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

**7. 公報發言全文搜尋（只搜「發言」區塊、限定某位委員）：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/speeches?q=核能&屆=11&區塊種類=發言&發言者=林德福&limit=5"
```

**8. 某場會議的完整發言紀錄（依順序）：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/meet/委員會-11-5-20-18/speeches?limit=100&sort=順序<"
```

**9. 某支 IVOD 影片對應的發言（可拿 `影片.秒數` 做影片跳轉）：**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ly.govapi.tw/v2/ivod/17717/speeches?時間來源=clip&limit=20"
```

---

## 會議段落（`/meet_sections`）使用要點

公報發言之上的「單元 → 段」標註（段落目錄），一段一筆：`單元序`／`單元名稱`、`段序`／`段名`、`段種類`（person 人段／case 案段／stage 階段段／group 分組標題／procedure 過場／attachment 書面資料）、`標籤`（表決、三讀、逐條討論、協商結論…）、`表決`，以及這段涵蓋的公報發言 `文件編號`＋`發言起`～`發言迄`（/speeches 的 `順序`）。
切分規則依會議種類不同（`切分器`：院會／委員會預算審查／公聽會／黨團協商）；委員會非預算審查日、考察等沒有段落。

```bash
# 一場會議的段落目錄（依單元、段的順序）
curl "https://ly.govapi.tw/v2/meet/委員會-11-5-20-18/meet_sections?sort=單元序<&limit=200"
# 這一段的發言內文：用 文件編號 加 順序 區間
curl "https://ly.govapi.tw/v2/speeches?文件編號=1154904_00004&順序:141,213&sort=順序<"
```

## 公報發言（`/speeches`）使用要點

公報逐字稿以「一次發言」為一筆（公報本來就是「發言者：內容」一段一段），不是逐句。公報的每個字都落在某個 block（附件、議事錄也是），所以 `q=` 等於公報內文全文搜尋；要只看口頭發言請加 `區塊種類=發言`。

| 欄位 | 說明 |
|------|------|
| `區塊種類` | `發言`／`主席程序`（主席宣布開會、請某人發言等程序話，統計發言時可排除）／`附件`（書面質詢、對照表、書面意見）／`議事錄`（上次會議議事錄全文）／`宣讀表決`／`標題` |
| `發言者原文` / `發言者` / `發言者身分` / `發言者職稱` | 公報寫法（`林委員德福`、`主席`）→ 正規化姓名（主席已換成主席本人姓名）、身分（`委員`／`主席`／`官員`／`其他`）、職稱 |
| `質詢輪次` / `質詢輪次委員` | 委員質詢與官員答詢算同一輪，`質詢輪次委員` 是這一輪的委員 |
| `發言時間` / `時間來源` | `gazette-clock`（公報的時鐘戳記）／`clip`（配到該委員同日的 IVOD Clip 起點）／`interpolated`（沿用前一個錨點，只保證順序不保證精確） |
| `影片.IVOD_ID` / `影片.秒數` | 對應的整場 Full 影片與秒數偏移；`Clip_IVOD_ID` 是配到的委員 Clip |
| `公報起始頁` / `公報結束頁` / `來源網址` | 公報頁碼與原始文件 |

限制：公報出刊比開會晚約兩週；公聽會、黨團協商、考察目前還沒有發言索引；`發言時間` 為 `interpolated` 的只有順序意義。

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
| 排序參數 | `sort`（欄位名後接 `<` 升冪、`>` 降冪，預設降冪） |
| OpenAPI spec | `https://ly.govapi.tw/v2/swagger.yaml` |
| 資料型別數 | 14（議案／立委／法律／法律版本／法條／委員會／會議／公報／公報目錄／質詢／影片／表決／公報發言／會議段落） |
