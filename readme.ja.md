# [Amazoness](https://github.com/bottomzlife/amazoness/)

AmazonアソシエイトHTMLタグをかんたんに出力するショートコードを実装するWordPressプラグイン

## 使用方法

ブログ記事投稿画面（そのほかショートコードが有効な場所）で、以下のように
`[asin]` というショートコードを使用します：

````
ほげ ほげ ほげ  [asin asin="4063827216"] ほげ ほげ ほげ。
````

または：

````
ほげ ほげ ほげ  [asin]4063827216[/asin] ほげ ほげ ほげ。
````

の結果は：

>ほげ ほげ ほげ  
>  *:*  
>  *ASIN:4063827216に対応するAmazon商品紹介表示*  
>  *:*   
>ほげ ほげ ほげ  

のように表示されます。

`4063827216` は、Amazon の各商品に一意に割り付けられているコード＝ASINです。


また、Amazonの各商品ページのURLを貼ると、Amazoness が自動的にそこから
ASINを抽出して動作します：

````
[asin asin="http://www.amazon.co.jp/exec/obidos/ASIN/4063827747"]
[asin]http://www.amazon.co.jp/dp/4063827747[/asin]
````

ただし、あらゆるURL形式に対してうまく動くとは限りません。
基本的には前者の「ASINだけを指定する」ほうを利用してください。

`[asin asin="ASIN"]` 形式と `[asin]ASIN[/asin]` 形式の違いは、 
後者では ASIN がHTML中に出力されGoogleなどのクローラーにノイズ
として取得されてしまうことです。前者を使うのが好ましいでしょう。

## 長所・短所

* 長所
    * 使うのに Amazon API キー のようなものを取得する必要がない
    * ASINコードを指定するだけでAmazon商品紹介表示ができる
    * 商品名・商品説明・商品画像はクロールして自動的に表示される
    * 商品紹介の部分の見た目は、CSS・HTMLを使って自由にカスタマイズできる
    * 商品情報の取得にキャッシュを使用しているので動作がスピーディ  
* 短所
    * 価格などの表示には対応していません
    * クロールするためにAmazonの特別な了解を得ているわけではない。
      たぶん、おめこぼししてもらえる程度の使い方ですが…
    * ASINがわからない人には向いていない

ASIN はAmazon上の商品に割り当てられている9から13ケタの商品コードです。
国・出版業界が決めている「EAN」「JAN」「ISBN」と似たような感じになっています。
Amazonの商品ページに行くと「ASIN」という項目があるので確認してみてください。
また、ブラウザのURLの中にもASINが含まれています。

## インストール

以下、どちらかお好きな方法でインストールしてください：

* WordPress 管理画面メニューから [プラグイン] -> [インストール] をたどり、
  [検索] 欄に `Amazoness` と入れてプラグインを探してください。
  見つかったら「インストール」をクリックし、「有効化」をおこなってください
    * WordPressプラグインには2016年6月現在まだ登録されていないので、
      こちらはできないかもしれません
* ZIPファイルを [Amazoness 公式サイト](https://github.com/bottomzlife/amazoness/releases)からダウンロードし、
  WordPress管理画面メニューの [プラグイン] -> [インストール] -> [アップロード] を選びます。
  ドラッグ＆ドロップで Amazoness のZIPファイルをアップロードしたら、
  [インストール] ボタンをクリックし [有効化] してください

## 動作環境

Amazoness は以下の環境で正常動作します：

* PHP 5.6 およびそれ以上のバージョン   
  クロージャ、クラス、名前空間、`const array` を使用しています
* PHP の dom, xml, xmlreader 拡張   
  DOMDocument, DOMXPath を使用しています

開発・テストに使ったOSは以下のとおりです:

* CentOS 6.x, Alpine Linux 3.3.x

## 設定

WordPress の設定画面に行き, [設定] メニューの中にある [Amazoness] を選んでください。
ここで以下の設定をおこなうことができます：

<a name="setting_associate_id">&nbsp;</a>
### AmazonアソシエイトID 

**!必須!**  
あなたのAmazonアソシエイトIDを設定してください。
IDはAmazonアソシエイトのページに行くと確認できます。
`netspin-22` などとなっているはずです。
デフォルトでは本プラグイン開発者のIDが設定されてしまっています。

<a name="setting_image_size">&nbsp;</a>
### 画像サイズ記述子 

商品紹介中の画像の大きさを指定します。
ただし、実際の表示サイズはCSSで決定されるのであまり大きな意味はありません。
デフォルトでは最大（`LZZZZZZZ`）が設定されています。
ほかのサイズは以下のとおり：

* `THUMBZZZ` :   サムネイルサイズ
* `TZZZZZZZ` :   小
* `MZZZZZZZ` :   中
* `LZZZZZZZ` :   大

<a name="setting_css_definition">&nbsp;</a>
### CSS定義

Amazonessが表示する商品紹介ブロックのためのCSS定義です。

どのような id・class があるかは
<a href="#setting_html_template">HTMLテンプレート</a>
を確認してください。

<a name="setting_html_template">&nbsp;</a>
### HTMLテンプレート

Amazonessが表示する商品紹介ブロックのためのHTML定義です。
ここで誤った設定をするとセキュリティホールを引き起こす危険性が
あります。注意深く設定をおこなってください。

`%%` と `%%` に囲まれた中で、いくつかの「変数」と「フィルター」を
利用することができます。

* 変数：
    * `PRODUCT_URL`  
      Amazonの商品ページへのURL。AmazonアソシエイトIDも埋め込まれています
    * `PRODUCT_IMAGE_URL`  
      Amazonの商品画像URL。画像サイズ記述子も指定してあります
    * `PRODUCT_TITLE`  
      商品名
    * `PRODUCT_DESCRIPTION`  
      Amazonの商品ページに掲載されている説明文
    * `IS_CACHED`
      この商品情報の掲載時にヒットしたキャッシュを使ったか否か。
      通常、必要ありません
* フィルター:
    * `html`  
      HTML中で特殊な意味をもつ文字をエスケープします
    * `url`
      URL中で特殊な意味をもつ文字をエスケープします

変数はこのように利用します：

````
...ほげ ほげ ほげ %%PRODUCT_URL%% ほげ ほげ...
````

フィルターはこのように利用します：

````
...ほげ ほげ ほげ %%PRODUCT_URL|url%% ほげ ほげ...
````

## Q&A

* ASINってなに？　おいしいの？
    * すみませんがASINをご存じない方にはほかのもっときちんとしたWordPressプラグイン
      をおすすめします。Amazonessよりずっと便利です。
      どうしても使ってみたい場合は
      「Amazonの商品ページURLみたいなものだ」と思っておいてください
* 設定を変更したら Amazoness の動作がおかしくなってしまった…
    * 設定画面の [設定を元に戻す] ボタンをクリックすると、設定が元通りになります
* バグを見つけた！
    * ありがとうございます。[GitHub Issues Board](https://github.com/bottomzlife/amazoness/issues) などでご連絡ください 

## 開発者について、寄付・著作権

この WordPress プラグインは [bottomzlife](http://netsp.in/)
が開発しました。PHPやWordPressについてはほとんど知らないので、
コードに不審な点があるかもしれません。

このプラグインは許諾なく利用・再配布していただいてかまいません。
ただし…開発者は非常に貧乏なので、気が向いたら「Amazonほしいものリスト」
などで寄付をいただけると死ぬほど喜びます。

* [作者のAmazonほしいものリスト](http://www.amazon.co.jp/registry/wishlist/35RWBK7ZZQ8PF/ref=cm_sw_r_tw_ws_z.arxbD4ZYFG5)
* [作者のAmazonリンク](http://www.amazon.co.jp/?_encoding=UTF8&camp=247&creative=1211&linkCode=ur2&tag=netspin-22)

ライセンスは GPL と MIT のデュアルライセンスを検討中ですが、
WordPressの規定がどうなっているのか
よくわからないので未定です。

## Contact

公開されている開発者のメールアドレスは、
特段の事情がない限り SPAM 避けのためのダミーです。
以下でコンタクトをとってください：

* [GitHub Issue Board](https://github.com/bottomzlife/amazoness/issues)
* [GitHub Repository](https://github.com/bottomzlife/amazoness/)

## History

* v0.9.5
    * 2016/05/29
        * 最初の公開
