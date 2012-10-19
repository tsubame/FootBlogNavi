<?php
App::import('Vendor', 'simplepie/autoloader');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');


/**
 * RSSを取得するコンポーネント
 *
 * 要：SimplePieライブラリ
 *     CurlMultiコンポーネント
 *
 *
 * エラー時の処理
 *
 *
 *
 */
class RssFetcherComponent extends Object {

	public $components = array("CurlMulti");

	/**
	 * @var object Instance of CurlMultiComponent
	 */
	private $CurlMulti;

	/**
	 * @var object Instance of ComponentCollection
	 */
	private $Collection;

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->Collection = new ComponentCollection();
		$this->CurlMulti  = new CurlMultiComponent($this->Collection);
	}

	/**
	 * RSSフィードを取得して配列で返す
	 *
	 *
	 * 戻り値の配列の形式
	 *
	 * 	$entries = array(
	 * 		0 => array(
	 * 			'url' => URL,
	 * 			'title' => タイトル,
	 * 			'description' => サマリー,
	 * 			'published' => 作成日時
	 * 		)
	 * 	)
	 *
	 * @param  string $url 	   フィードURL or 記事のURL
	 * @return array  $entries フィード内のエントリの配列
	 */
	public function getFeed($url) {
		$simplePie = new SimplePie();
		$simplePie->set_raw_data($url);
		$simplePie->init();
		$simplePie->handle_content_type();

		$entries = array();

		foreach ($simplePie->get_items() as $i => $item) {
			$entries[$i]['url']         = $item->get_permalink();
			$entries[$i]['title']       = $item->get_title();
			$entries[$i]['description'] = $item->get_description();
			$entries[$i]['published']   = $item->get_date('Y-m-d H:i:s');
		}

		return $entries;
	}

	/**
	 * 複数のURLのRSSフィードを並列に取得
	 *
	 * 戻り値の配列の形式
	 *
	 * 	$parsedFeeds = array(
	 * 		0 => array(
	 * 			0 => array(
	 * 				'url' => URL,
	 * 				'title' => タイトル,
	 * 				'description' => サマリー,
	 * 				'published' => 作成日時
	 * 			)
	 * 		)
	 * 	)
	 * @param  array $feedUrls フィードURLの配列
	 * @return array $parsedFeeds
	 *
	 *
	 */
	public function getFeedParallel($feedUrls) {
		// HTTPで並列にRSSフィードを取得
		$xmlRow = $this->CurlMulti->getContents($feedUrls);

		$parsedFeeds = array();
		foreach ($xmlRow as $j => $xml) {
			$simplePie = new SimplePie();
			$simplePie->set_raw_data($xml);
			$simplePie->init();
			$simplePie->handle_content_type();

			//echo '<a href = "' . $feedUrls[$j] . '" target = "_blank">' . $simplePie->get_title() . '</a>';
			$entries = array();

			foreach ($simplePie->get_items() as $i => $item) {
				$entries[$i]['url']         = $item->get_permalink();
				$entries[$i]['title']       = $item->get_title();
				$entries[$i]['published']   = $item->get_date('Y-m-d H:i:s');
				$entries[$i]['description'] = $item->get_description();
// 現在日時より後の日付であれば現在日時に直すべき？

			}

			array_push($parsedFeeds, $entries);
		}

		return $parsedFeeds;
	}

}
