<?php

/**
 * SitesControllerのgetShareCountアクション
 * ツイート数を取得して記事を更新
 *
 * 依存クラス
 * ・Model/Site
 * ・Model/FacebookAPIAccessor
 *
 * エラー
 * ・？？
 * ・？？
 */
class SiteGetShareCountAction extends AppModel {

	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;


// リツイート数も取得できるようにしたい

	/**
	 * 処理実行
	 *
	 */
	public function exec() {
		// サイトを取得
		$siteModel = ClassRegistry::init('Site');
		$sites = $siteModel->getAllSites();

		$counts = $this->getFacebookShareCount($sites);

		// DB更新
		$i = 0;
		foreach ($counts as $url => $count) {
			$site = $sites[$i];
			if ($url != $site['url']) {
				continue;
			}
			$site['like_count'] = $count;

			$siteModel->save($site);
			$i++;
		}
	}

	/**
	 * Facebookのシェア数を取得
	 *
	 * @param  array $sites
	 * @return array $counts URLをキーにしたシェア数の配列
	 */
	protected function getFacebookShareCount($sites) {
		// URLを配列に入れる
		$urls = array();
		foreach ($sites as $site) {
			$urls[] = $site['url'];
		}

		// それぞれのURLのシェア数を取得
		$fb = ClassRegistry::init('FacebookAPIAccessor');
		$counts = $fb->getLikeCountOfUrls($urls);

		//debug($counts);

		return $counts;
	}
}