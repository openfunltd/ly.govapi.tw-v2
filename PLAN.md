# ly-api-v2 專案說明

## 專案概覽

**LYAPI**（Legislative Yuan API）是台灣立法院資料的統一 REST API 服務，將各式散落的國會資料整合成機器可讀、可搜尋的格式。

- 網站：https://ly.govapi.tw/
- Swagger UI：https://ly.govapi.tw/swagger
- 原始碼：https://github.com/openfunltd/ly.govapi.tw-v2

---

## 技術堆疊

| 層級 | 技術 |
|------|------|
| 語言 | PHP 7.4+ |
| 框架 | MiniEngine（自製輕量 MVC，v0.1.0，bundled 於 `mini-engine.php`） |
| 主要資料庫 | Elasticsearch 7.x（儲存所有立法院資料） |
| 次要資料庫 | PostgreSQL（API 使用量統計、token 管理） |
| 文件儲存 | S3-compatible（存放公報 PDF） |
| 文件 | Swagger UI 5.17.14（動態產生 OpenAPI 3.0.3 spec） |
| 前端 | Bootstrap 5.3.3（首頁），Noto Sans TC |

---

## 目錄結構

```
ly-api-v2/
├── index.php                      # 入口點，呼叫 MiniEngine 路由
├── init.inc.php                   # 環境初始化
├── config.inc.php                 # 機密設定（git-ignored）
├── config.sample.inc.php          # 設定範本
├── mini-engine.php                # 自製 MVC 核心框架
├── .htaccess                      # Apache rewrite，全導向 index.php
├── controllers/
│   ├── ApiController.php          # 主要 API 處理器
│   ├── SwaggerController.php      # OpenAPI spec 動態產生
│   ├── StatController.php         # 資料統計端點
│   ├── IndexController.php        # 首頁 & robots.txt
│   ├── ErrorController.php        # 錯誤處理
│   ├── LogController.php          # Debug 用（非正式環境）
│   └── GazetteAgendaDocController.php  # 公報文件檢視器
├── libraries/
│   ├── LYAPI/
│   │   ├── Helper.php             # URL 解析、type 識別
│   │   ├── Type.php               # 所有資料型別的基底類別
│   │   └── Type/                  # 11 種具體型別實作
│   │       ├── Bill.php           # 議案
│   │       ├── Legislator.php     # 立法委員
│   │       ├── Law.php            # 法律
│   │       ├── LawVersion.php     # 法律版本
│   │       ├── LawContent.php     # 法條（逐條）
│   │       ├── Committee.php      # 委員會
│   │       ├── Meet.php           # 會議
│   │       ├── Gazette.php        # 公報
│   │       ├── GazetteAgenda.php  # 公報目錄
│   │       ├── Interpellation.php # 質詢
│   │       └── Ivod.php           # 影片錄影（IVOD）
│   │   ├── SearchAction.php       # Elasticsearch 查詢建構
│   │   └── StatAction.php         # 統計聚合
│   ├── Elastic.php                # Elasticsearch 客戶端封裝
│   ├── OpenFunAPIHelper.php       # API 使用量追蹤、token 驗證
│   ├── ProgressHelper.php         # 議案關係追蹤
│   ├── GazetteParser.php          # 公報文件解析
│   └── GazetteTranscriptParser.php # 會議紀錄解析
├── views/                         # 回應樣板（首頁、Swagger UI）
└── public/swagger-ui/             # Swagger UI 靜態資源
```

---

## 資料型別（11 種）

| 型別 | 路徑 | 說明 |
|------|------|------|
| 議案 | `/bills` | 立法提案，含狀態追蹤 |
| 立法委員 | `/legislators` | 歷屆立委資料 |
| 法律 | `/laws` | 法規條文 |
| 法律版本 | `/lawversions` | 法律修訂歷程 |
| 法條 | `/lawcontents` | 逐條法條內容 |
| 委員會 | `/committees` | 各委員會及職掌 |
| 會議 | `/meets` | 委員會及院會會議 |
| 公報 | `/gazettes` | 立法院公報 |
| 公報目錄 | `/gazetteagendas` | 公報目錄條目 |
| 質詢 | `/interpellations` | 質詢紀錄 |
| 影片 | `/ivods` | IVOD 影片錄影 |

---

## 請求流程

```
瀏覽器/客戶端
  ↓ HTTP Request
index.php
  ↓ MiniEngine::dispatch(自訂路由函式)
LYAPI_Helper::getApiType(uri)       # 從 URL 判斷資料型別
  ↓
ApiController::collectionsAction()  # 列表查詢
  或
ApiController::itemAction()         # 單筆查詢
  ↓
LYAPI_SearchAction                  # 建構 Elasticsearch Query DSL
  ↓
Elastic::dbQuery()                  # 執行查詢
  ↓
LYAPI_Type::buildData()             # ES 結果轉換成 API 輸出格式
  ↓
cors_json()                         # 回傳 JSON + CORS headers
```

---

## URL 規則

| Pattern | 說明 |
|---------|------|
| `/bills` | 議案列表 |
| `/bill/202110068550000` | 單筆議案 |
| `/bills?屆=11&會期=2` | 帶篩選條件的列表 |
| `/legislator/11/韓國瑜` | 以屆+姓名查詢委員 |
| `/bill/202110068550000/interpellations` | 議案關聯的質詢 |
| `/v2/...` | `/v2` 前綴會自動正規化為 `/` |

---

## 型別系統設計

每個型別繼承 `LYAPI_Type`，需實作：

| 方法 | 用途 |
|------|------|
| `getFilterFieldsInfo()` | 宣告可用的篩選參數（同時產生 Swagger 文件） |
| `getFieldMap()` | ES 欄位名稱 ↔ API 欄位名稱對應 |
| `getIdFieldsInfo()` | URL 路徑中的 ID 欄位定義 |
| `getRelations()` | 關聯資源端點（如議案→質詢） |
| `queryFields()` | 全文搜尋欄位 |
| `sortFields()` | 可排序欄位 |
| `aggMap()` | 代碼值→可讀字串轉換（如委員會代碼→中文名稱） |

---

## API 回應格式

**列表端點（Collections）：**
```json
{
  "total": 150,
  "total_page": 3,
  "page": 1,
  "limit": 50,
  "bills": [...],
  "filter": {},
  "aggs": [...],
  "supported_filter_fields": [...]
}
```

**單筆端點（Item）：**
```json
{
  "error": false,
  "id": "202110068550000",
  "data": {},
  "supported_relations": {},
  "relations": []
}
```

---

## 設定與環境變數

**config.inc.php**（複製自 `config.sample.inc.php`）設定：

| 環境變數 | 說明 |
|----------|------|
| `ELASTIC_URL` | Elasticsearch 連線位址 |
| `ELASTIC_USER` | Elasticsearch 帳號 |
| `ELASTIC_PASSWORD` | Elasticsearch 密碼 |
| `ELASTIC_PREFIX` | Elasticsearch Index 前綴（用來區隔不同環境的 index） |
| `API_COUNTER_DATABASE_URL` | PostgreSQL 連線字串（使用量統計） |
| `SESSION_SECRET` | Session HMAC-SHA256 簽章金鑰 |
| `SESSION_DOMAIN` | Cookie 網域（選填） |
| `ENV` | `production` 或開發環境 |
| `APP_NAME` | 應用程式顯示名稱 |

---

## 如何啟動

**需求：**
- PHP 7.4+（需 `curl`、`PDO`、`json` 擴充）
- Apache + `mod_rewrite`
- Elasticsearch 7.x cluster
- PostgreSQL 資料庫

**安裝步驟：**
```bash
git clone git@github.com:openfunltd/ly.govapi.tw-v2.git
cd ly-api-v2
cp config.sample.inc.php config.inc.php
# 填入 config.inc.php 中的實際憑證
```

部署至 Apache document root，確認 `.htaccess` 啟用即可。無 build 步驟。

---

## 關鍵模式與慣例

**1. Elasticsearch Query DSL 手動建構**
```php
$cmd = new StdClass;
$cmd->bool->must[] = ['terms' => ['field' => $values]];
$cmd->aggs->agg_name = ['terms' => ['field' => $field]];
```

**2. Session 安全機制**
Cookie 格式：`sig|json_data`，簽章為 `HMAC-SHA256(data + domain + secret)`，30 天 TTL。

**3. 關聯資源（Relations）**
- 跨型別：父資料欄位對應子資料篩選條件
- 函式型（`_function`）：自訂聚合計算

**4. Swagger 動態產生**
`SwaggerController` 反射所有 Type 類別，自動建構完整 OpenAPI 3.0.3 spec，無需手動維護。

**5. 錯誤處理**
- Dispatcher 層 try/catch → ErrorController
- 正式環境錯誤回傳通用訊息 + 錯誤 ID（不暴露堆疊）
- Elasticsearch 錯誤記錄至 `error.data`

---

## 相關專案

| 專案 | 說明 |
|------|------|
| [Dataly](https://dataly.openfun.app) | 本 API 的 Web 瀏覽介面 |
| [Lawtrace](https://lawtrace.tw) | 議案歷程視覺化 |
| law-diff | 法律版本比較工具 |
| Hugging Face Datasets | 資料匯出供機器學習使用 |

資料來源：https://data.ly.gov.tw/（立法院開放資料平台）
