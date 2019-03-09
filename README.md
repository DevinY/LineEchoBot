# LineEchoBot
下載
<pre>
git clone https://github.com/DevinY/LineEchoBot.git line
</pre>


## 提供一個簡單的ChineseDictionary基本架構參考
### 包含回傳圖片及座標

安裝及產生App Key
<pre>
cd line
composer install
cp .env.example .env
php artisan key:generate
</pre>

編輯.env檔，填入Line Console的資料
<pre>
LINEBOT_CHANNEL_ID=
LINEBOT_CHANNEL_SECRET=
LINEBOT_CHANNEL_TOKEN=
</pre>

幫助大家快速進入，直接提供內建基本的測試用的資料庫。

請在.env中設定您測試用的資料庫。
<pre>
php artisan migrte
php artisan db:seed
</pre>


預設Webhook URL Requires SSL(需使用https://)

https://您的域名/callback

訊息API推播測試

網址傳入:

https://您的域名/api/text?message=這是推播訊息&to=[UserId]

查看Laravel的Log可以看到userId
<pre>
tail -f storage/logs/laravel.log 
</pre>
