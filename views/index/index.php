<?php
$today = date('Y-m-d');
$year_now = explode('-', $today)[0];
$ch = curl_init('https://raw.githubusercontent.com/openfunltd/news/refs/heads/main/lyapi/latest.json');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
$ret = curl_exec($ch);
curl_close($ch);

$news = json_decode($ret) ?? [];
?>
<!DOCTYPE html>
<html lang="zh-tw">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<style>
  .date-cell {
    width: 20%
  }
</style>
<body class="bg-light">
<main role="main">
  <div class="container" style="max-width: 1120px;">
    <div class="pt-5 pb-4 text-center">
      <img src="/static/images/api.svg" width="140" height="140">
      <h1 class="display-4 fw-semibold">LYAPI</h1>
      <p class="lead">
        彙整四散於各立院資料庫的資料，並且將 PDF、Word 等文件轉換為機器可讀格式。不必翻閱數百頁文件或在多個網站與分頁間不停切換，讓你能快速檢索、組合資料，整合成所需的資訊。
      </p>
    </div>
    <div class="row pb-5">
      <div class="col-md-8">
        <div class="fs-4 mb-3 d-flex align-items-center">
          <img src="/static/images/news.svg" style="height: 1em;" class="me-2">
          <h2 class="mb-0 fs-4">最新消息</h2>
        </div>
        <table class="table table-hovertable table-light">
          <tbody>
            <?php foreach ($news as $news_item) { ?>
              <tr class="border-top border-secondary-subtle">
                <td class="text-center date-cell"><?= $news_item->date ?></td>
                <td>
                  <a href="<?= $news_item->link?>" target="_blank"><?= $news_item->title ?></a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="col-md-4 pt-5 pt-md-0">
        <div class="fs-4 mb-3 d-flex align-items-center">
          <img src="/static/images/terminal.svg" style="height: 1em;" class="me-2">
          <h2 class="mb-0 fs-4">相關應用</h2>
        </div>
        <ol class="list-group">
          <li class="list-group-item d-flex justify-content-between align-items-start">
            <div class="ms-2 me-auto">
              <div class="fw-bold">
                <a href="https://lawtrace.tw/" target="_blank">Lawtrace</a>
              </div>
              立法歷程查詢
            </div>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-start">
            <div class="ms-2 me-auto">
              <div class="fw-bold">
                <a href="https://dataly.openfun.app/" target="_blank">Dataly</a>
              </div>
              透過網頁瀏覽 API 資料
            </div>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-start">
            <div class="ms-2 me-auto">
              <div class="fw-bold">
                <a href="https://openfunltd.github.io/law-diff/" target="_blank">law-diff</a>
              </div>
              法律對照表
            </div>
          </li>
        </ol>
          
          
      </div>
    </div>
    <div class="row pb-5">
      <div class="col-md-4 pt-5 pt-md-0 text-center">
        <img class="rounded-circle" src="/static/images/swagger.svg" width="100" height="100">
        <h2>Swagger</h2>
        <p>LYAPI 提供 Swagger 頁面，開發者可直覺地瀏覽可用的資料類別與取得方式，並直接測試 API endpoint，快速驗證回應內容，加速開發流程。</p>
        <p><a class="btn btn-secondary" href="/swagger" role="button">查閱 API 文件</a></p>
      </div>
      <div class="col-md-4 pt-5 pt-md-0 text-center">
        <img src="/static/images/document.svg" width="100" height="100">
        <h2>使用指南</h2>
        <p>我們把開發相關應用過程中累積的經驗，編寫成使用指南。適合對立法院資料不熟悉的使用者快速了解如何使用 LYAPI 的資料與工具。</p>
        <p><a class="btn btn-secondary" href="https://hackmd.io/@openfunltd/S1iLBqP21l" role="button" target="_blank">瀏覽指南</a></p>
      </div>
      <div class="col-md-4 pt-5 pt-md-0 text-center">
        <img class="rounded-circle" src="/static/images/hf.svg" width="100" height="100">
        <h2>Hugging Face</h2>
        <p>針對需要進行統計或模型訓練的使用者，我們已將資料集打包並上傳至 Hugging Face，無需再透過 LYAPI 爬取整個資料庫。</p>
        <p><a class="btn btn-secondary" href="https://huggingface.co/collections/openfun/tw-legislative-yuan-data-67c7e14902935d02b0b97a3f" role="button" target="_blank">瀏覽 / 下載資料集</a></p>
      </div>
    </div>
    <footer class="row row-cols-1 row-cols-sm-2 row-cols-md-5 py-5 my-5 border-top" style="max-width: 1120px;">
      <div class="col mb-2">
        <a href="https://openfun.tw" target="_blank" class="d-flex align-items-center link-body-emphasis text-decoration-none">
          <img class="mb-1" src="https://raw.githubusercontent.com/openfunltd/openfun.tw/refs/heads/gh-pages/images/logo.svg" alt="OpenFun .Ltd logo">
        </a>
        <p class="text-body-secondary">&copy; <?= $year_now ?></p>
      </div>
      <div class="col mb-1"></div>
      <div class="col mb-3">
        <h5>API Infra</h5>
          <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="https://github.com/openfunltd/ly.govapi.tw-v2" target="_blank" class="nav-link p-0 text-body-secondary">Github</a></li>
            <li class="nav-item mb-2"><a href="https://github.com/openfunltd/ly.govapi.tw-v2/blob/main/LICENSE" target="_blank" class="nav-link p-0 text-body-secondary">BSD 3-clause</a></li>
          </ul>
      </div>
      <div class="col mb-3">
        <h5>Dataset Collection</h5>
          <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="#" target="_blank" class="nav-link p-0 text-body-secondary">免責聲明</a></li>
            <li class="nav-item mb-2"><a href="https://creativecommons.org/licenses/by/4.0/deed.zh-hant" target="_blank" class="nav-link p-0 text-body-secondary">CC-BY-4.0</a></li>
          </ul>
      </div>
      <div class="col mb-3">
        <h5>Contact Us</h5>
          <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="mailto:contact@openfun.tw" class="nav-link p-0 text-body-secondary">聯絡信箱</a></li>
          </ul>
      </div>
    </footer>
  </div>
</main>
</body>
</html>
