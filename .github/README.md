# Gallery Board

<br/>

> ## このプログラムについて
 - PHPスクリプト
 - 画像掲示板
 - jpg,jpeg,png,gif画像をアップロードして投稿
 - 動画共有サイトのURLから動画イメージを投稿
 - 縮小版のサムネイルを生成して表示する機能
 - 管理者だけが投稿ができるモードにも設定可能
 

>## 縮小版サムネイルを生成する機能について

> ### アニメーション画像ファイル
- GIFアニメーション
  - imagickが利用できる場合
    > サムネイルもアニメーションで生成。
  - imagickが利用できない場合
    > 静止画でサムネイルが生成されます。

 - アニメーションPNG(APNG)
   - imagick + ffmpegが利用できる場合
     > アニメーションPNGとしてサムネイルも生成されます。
   - imagickやffmpegが利用できない場合
     > 1フレーム目な静止画のPNGファイルでサムネイルが生成されます。

<br/>

> ## やっていくこと。
* Ver:10.0
  - スクリプト全体の構成の変更。コード調整。

<br/>

> ## 更新履歴
 - > ### Ver: 10.~
 - > ### [Ver: 9.~](History/History_v9.md)
 - > ### [Ver: 8.~](History/History_v8.md)
 - > ### [Ver: 7.~](History/History_v7.md)
 - > ### [Ver: 6.~](History/History_v6.md)
 - > ### [Ver: 5.~](History/History_v5.md)
 - > ### [Ver: 4.~](History/History_v4.md)
 - > ### [Ver: 3.~](History/History_v3.md)
 - > ### [Ver: 2.~](History/History_v2.md)
 - > ### [Ver: 1.~](History/History_v1.md)