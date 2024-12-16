# AI-Chat-Chat
![](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![](https://img.shields.io/badge/redis-%23DD0031.svg?&style=for-the-badge&logo=redis&logoColor=white)
![](https://img.shields.io/badge/Gmail-D14836?style=for-the-badge&logo=gmail&logoColor=white)
![](https://img.shields.io/badge/Postman-FF6C37?style=for-the-badge&logo=Postman&logoColor=white)

# 專案介紹
這是一個基於 **Laravel 11** 框架所開發的純後端API專案，透過 **Postman** 進行測試。本專案支援使用者透過Google信箱進行第三方登入，使用者可以輸入文字內容，並選擇AI使用文字、語音或是圖片回覆（透過OpenAI Api）。

# 功能
### Authentication
- 註冊：
  - 使用 OpenAI 的 **Moderation Api** 進行驗證使用者名稱是否符合善良風俗。
  - 使用 OpenAI 的 **Images Api** 依據使用者的自我介紹生成大頭貼。
  - 支援 **Google** 第三方登入，使用 Laravel 的 **Socialite Provider** 套件實作。
- 發送驗證信：
  - 使用者需點選驗證信以進行帳號的開通，並透過 **Mailtrap** 進行測試。
- 登入
- 登出
### Thread
  - 新增對話串
  - 編輯對話串名稱
  - 查看自己建立的所有對話串
  - 刪除對話串
### Message
  - 新增訊息，AI進行回覆(文字、語音或圖片)
### 免費會員的限制
- 對於免費使用者建立的對話串以及對話數量進行限制，並透過 **Redis** 實作。

# API 文件

# 安裝與設定
1. 若未下載 Docker Desktop 或是 [OrbStack](https://orbstack.dev/)（建議）者，需先下載。
2. 先確認有沒有任何程序佔用 80 port（或是 Docker 要使用的 port 號），若有，需先停止。
3. 將 fork 的專案 clone 至本地，請執行以下 command：
( `Path` 為欲放專案的本地路徑， `Username` 為個人 GitHub 帳號， `Your Name` 為專案名稱後綴，請自行替換)
```
cd {Path}
```
```
git clone https://github.com/{Username}/BECamp_T13_HW2_Laravel-AI_{Your Name}
```
4. 將專案中的 .env.example 複製一份在專案中，並將檔名改為 .env ，完成後儲存。
5. 請執行以下 command ，安裝專案所需相關套件並啟動開發環境：
```
composer install
```
```
./vendor/bin/sail up -d
```
```
./vendor/bin/sail artisan key:generate
```
```
./vendor/bin/sail artisan migrate
```
