
# 更新履歴 Ver: 8.~

## Ver: 8.9 @ 19/02/17
> ### 修正
 - 編集時、ファイルの変更しない場合に、エラーが発生する可能性が有ったのを修正。
 - エラー時のファイルの削除処理の調整。

## Ver: 8.8 @ 2019/02/11
> ### 追加
 - IPによるアクセス制限に、期間設定できる様に変更。

## Ver: 8.7 @ 2018/12/18
> ### 修正
 - 編集時、サイトURLやメールアドレスなどの値が書き換えられないのを修正。
 - SiteURLの置換方法の調整。

## Ver: 8.6 @ 2018/11/24
> ### 追加
 - 動画サイトViemoに対応。

## Ver: 8.5 @ 2018/10/10
> ### 修正
 - 動画のサムネイル情報を取得する時、xmlオブジェクトのままだと判定ミスが起きていたのを修正。

## Ver: 8.4 @ 2018/10/05
> ### 修正
 - PHPのバージョンが7.2の場合に記事編集時、colorに関するWarningエラー。
 - 最初のPVカウントや、Goodカウント時にcount()に関するWarningエラー。

## Ver: 8.3 @ 2018/04/01
> ### 修正
 - html.php , entry.phpのセキュリティ調整。
> ### 追加
 - 投稿時や、出力時のエラーの追加。
 - アップロードされた画像情報を取得失敗した時のエラー処理を念のために追加。

## Ver:8.2.1 2018/03/16
> ### 修正
 - rss.phpにて RSSの表示ソート($rss_set['sort'] = 1)が効かないミス修正。

## Ver: 8.2 @ 2017/02/21
> ### 追加
 - Goodカウント機能を正式追加。
 - 設定変数$good_set、表示制御タグ Good_Counter を追加。
 - $count_set['pvc']['sw']によるページビュー数の表示制御タグ PV_Counter を追加。

## Ver: 8.1 @ 2017/01/25
> ### 修正
 - 通知機能の修正。
 - データ保存時に整列処理。
> ### 追加
 - レスに管理者のみが投稿可能モードを追加。

## Ver:8.0.1 2016/12/28
> ### 追加
 - レス引用の記述を行った場合、コメントが表示されないミスを修正。

## Ver: 8.0 @ 2016/12/26
> ### 修正
 - 検索の調整。
> ### 追加
 - 個別版のRSS出力、PickUp出力の標準化。
 - IP_CheckerのTorCheckを追加。

