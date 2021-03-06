<?php
/**
 * コントローラクラス
 *
 */
class ArticlesController extends Controller {

	/**
	 * データベース
	 *
	 * @var array
	 */
	public $uses = array('Article', 'Category');

	/**
	 * ヘルパー
	 *
	 * @var array
	 */
	public $helpers = array('Form', 'Html');


	/**
	 * アクションの前の処理
	 *
	 * カテゴリの取得
	 *
	 * @see Controller::beforeFilter()
	 */
	public function beforeFilter() {
		parent::beforeFilter();

		$categories = Configure::read('Category.names');
		$this->set('categories', $categories);
	}

	/**
	 * 記事の一覧 ユーザ向け
	 *
	 */
	public function index() {
		if (isset($this->passedArgs[0])) {
			$categoryId = (int) $this->passedArgs[0];
		} else {
			$categoryId = null;
		}
		$results = $this->Article->selectTodaysArticles($categoryId);

		$this->set('results', $results);
	}

// view未実装
	/**
	 * 記事の一覧 管理者向け
	 *
	 */
	public function editIndex($categoryId = null) {
		$results = $this->Article->selectTodaysArticles($categoryId);
		$this->set('results', $results);
	}

	/**
	 * RSSから記事の登録
	 *
	 */
	public function register() {
		$beforeTime = time();

		$action = ClassRegistry::init('ArticleRegisterAction');
		$action->exec();

		$afterTime = time();
		$actTime = $afterTime - $beforeTime;

		CakeLog::info("実行時間：{$actTime}秒");
		$this->render('editIndex');
	}

	/**
	 * スポーツナビ+から記事の登録
	 */
	public function registerFromSNavi() {
		$action = ClassRegistry::init('ArticleRegisterFromSNaviAction');
		$action->exec();

		$this->render('editIndex');
	}

	/**
	 * ツイート数を取得して記事を更新
	 */
	public function getShareCount() {
		$action = ClassRegistry::init('ArticleGetShareCountAction');
		$action->exec();

		$this->render('editIndex');
	}

	/**
	 * 1つの記事を削除
	 *
	 */
	public function delete() {

	}

	/**
	 * 過去の記事を自動的に削除
	 *
	 */
	public function deletePastArticles() {
		$this->Article->deletePastArticles();
	}



}