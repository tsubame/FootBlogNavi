<p>記事のリスト</p>
<p>
<?php 	echo $this->Html->link('すべて', array(
			'controller' => 'articles',
			'action' => 'index')
		). '　　';
		//echo $this->Html->link( 'ブログ', array('3')) . '　　';

		foreach ($categories as $id => $categoryName) {
			echo $this->Html->link($categoryName, array($id)) . '　　';
		}
?>
</p>
<br />
<br />
<?php

	foreach ($results as $i => $data) {
		if ($i % 10 == 0) {
			echo '<br /><br />';
		}

		?>
		<p>
		<?php
		echo $data['Article']['tweeted_count'] . 'tweet　　';
		echo $this->Html->link($data['Article']['title'], $data['Article']['url'], array('target' => '_blank'));
		echo '　　' . $this->Html->link($data['Site']['name'], $data['Site']['url'], array('target' => '_blank')); //  $data['Site']['name'];
		?>
		</p>
		<?php
	}

?>