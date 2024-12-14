# AI-Chat-Chat
![](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![](https://img.shields.io/badge/redis-%23DD0031.svg?&style=for-the-badge&logo=redis&logoColor=white)
![](https://img.shields.io/badge/Gmail-D14836?style=for-the-badge&logo=gmail&logoColor=white)
![](https://img.shields.io/badge/Postman-FF6C37?style=for-the-badge&logo=Postman&logoColor=white)

## 專案介紹
這是一個基於 **Laravel 11** 框架所開發的純後端API專案，透過 **Postman** 進行測試。本專案支援使用者透過Google信箱進行第三方登入，使用者可以輸入文字內容，並選擇AI使用文字、語音或是圖片回覆（透過OpenAI Api）。

## 功能
- 使用者註冊時，透過 OpenAI 的 **Moderation Api** 進行驗證使用者名稱是否符合善良風俗。
- 使用者註冊時，透過 OpenAI 的 **Images Api** 依據使用者的自我介紹生成大頭貼。
- 使用者註冊完成後，發送驗證信，使用者需點選驗證信以進行帳號的開通，並透過 **Mailtrap** 進行測試。
- 支援使用者透過 Google 進行第三方登入，使用 Laravel 的 **Socialite Provider** 套件實作。
- 對於免費使用者建立的對話串以及對話數量進行限制，並透過 **Redis** 實作。
