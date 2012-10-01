<?php

/**
 * モデルクラス
 *
 * （注意）TwitterのAPIの返り値の配列の内容はわかりにくいので書いておく必要あり
 *
 *
 * サイトのURLでtwitterを検索して、ツイートされた記事のURLを取得する。
 * 取得した記事のURLをarticlesテーブルに保存。
 *
 * URLは検索用のキーワードを抜き出して検索する
 * htttp://www.yahoo.co.jp/ → yahoo.co.jp/
 *
 *
 */
App::uses('Site','Model');
App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');

class TweetSearcher extends AppModel{

	public $useTable = false;
	// Siteモデル
	private $Site;
	// 検索する件数
	private $rpp = 30;
	// テーブルに保存するURL
	private $savedUrls = array();

// 定数クラスに移す
	// twitterAPIのURL
	const API_URL = "http://search.twitter.com/search.json";


// 使用するのはコンストラクタでOKか？
	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->Site = new Site();
// この書き方でOK?
		$Collection = new ComponentCollection();
		$this->curlMulti = new CurlMultiComponent($Collection);
	}

	/**
	 * 処理実行
	 */
	public function exec() {
		$this->searchSingleThread();
	}

	/**
	 * 検索処理
	 *
	 * 並列処理を行わないロジック
	 *
	 */
	private function searchSingleThread() {
		$sites = $this->Site->getAllSites();
		// サイトの件数ループ
		foreach ($sites as $site) {
			// URLを検索用キーワードの形式にフォーマット
			$searchKey = $this->formatUrlForSearch($site['url']);
			// twitterAPIでURLを検索 24時間以内 100件
			$tweets = $this->searchByJson($searchKey);
			// 結果のツイートの件数ループ
			foreach ($tweets as $tweet) {
				// ツイート内のURLのデータを取得
				$urlInfo = end($tweet["entities"]["urls"]);// ['entities']['urls']が複数の要素の配列になってることがある （2件のURL）
echo $tweet['text'] . '<br />';
				// t.co～展開後のURLを取得
				if ( !isset($urlInfo["expanded_url"]) ) {
					continue;
				}
				$tweetedUrl = $urlInfo["expanded_url"];
				//echo $tweetedUrl . '<br /><br />' ;
				// 配列にURLを保存
				if ( !array_search($tweetedUrl, $this->savedUrls) ) {
					array_push($this->savedUrls, $tweetedUrl);
				}
			}
		} // end foreach サイトの件数ループ

		debug ($this->savedUrls);
		// 短縮URLの場合は展開（コンポーネント化）
		$longUrls = $this->curlMulti->expandUrls($this->savedUrls);

		debug($longUrls);


		// 取得したURLをarticlesテーブルに登録
	}


// 配列のデータ形式も書く
	/**
	 * ツイッターを検索して結果をJSON形式で取得
	 *
	 * @param  string $q 	  検索キーワード
	 * @return array  $tweets 配列
	 *
	 *
	 *
	 */
	private function searchByJson($q) {
		$socket = new HttpSocket();
// 24時間以内の条件も入れる必要あり
// until=～～
		$param = "rpp=" . $this->rpp . "&q=" . $q . "&include_entities=true";
		$res   = $socket->get(self::API_URL, $param);
		// HTTPのBody取得
		$json  = $res["body"];
		// JSONデータを配列にデコード
		$array = json_decode($json, true);
		$tweets = $array["results"];

		return $tweets;
	}

	/**
	 * URLを検索キーワードの形式にフォーマット
	 *
	 * http://と最後のファイル名、www.、?以降を取り除く
	 * （例）http://www.uefa.com/index.html → uefa.com/
	 *
	 * @param  string $url
	 * @return string $searchKey
	 */
	private function formatUrlForSearch($url) {
		$searchKey = $url;

		// ?以降を除く
		if (preg_match('/^http:\/\/[\w\.\/\-_=]+/', $searchKey, $matches)) {
			$searchKey = $matches[0];
		}
		// http://とファイル名を取り除く
		if (preg_match('/^http:\/\/([\w\.\/\-_=]+\/)[\w\-\._=]*$/', $searchKey, $matches)) {
			$searchKey = $matches[1];
			// www.を取り除く
			if (substr($searchKey, 0, 4) == 'www.') {
				$searchKey = substr($searchKey, 4);
			}
		}
		debug('検索キーワード' . $searchKey);

		return $searchKey;
	}


}