<?php
/**
 * Plugin name: PPP PassWord
 * Description: Pablic Post Previewで投稿表示時にパスワード認証を行う
 * Version: 0.1.0
 *
 * @package ppp_password
 * @author kutsu
 * @license GPL-2.0+
 */

/**
 * Public Post Preview プラグインがない場合は起動しない
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); //plugin.phpを読み込む

//本当はPPPが有効化しているときのみ有効化されるようにしたい
if ( !is_plugin_active( 'public-post-preview/public-post-preview.php' ) ) {
	//エラー
}

//投稿で保存されたパスワードと入力されたパスワードが等しいか判断
add_action(
	'template_redirect',
	function(){
		global $post;
		if ( isset($_GET['_ppp']) && isset( $_POST['password'] ) ) { //PPP表示かつパスワードが送信されている
			$password = get_post_meta($post->ID, 'ppp_passwd', true); //投稿に保存されているパスワードを格納
			if ( isset($password) && $password == $_POST['password'] ) {
				set_query_var( 'ppp_password_check', 'ok' ); //パスワードが通った判断のquery var
			} else {
				set_query_var( 'ppp_password_check', 'ng' ); //パスワードが通った判断のquery var
				global $wp_query;
				$wp_query->set_404(); //通らなかった場合は404
			}
		}
	}
);

//パスワード入力画面のHTMLの挿入


//内容部
add_filter(
	'the_content',
	function($content){
		if ( isset($_GET['_ppp']) && !get_query_var( 'ppp_password_check' ) && in_the_loop() ) {
			$content = <<<EOM
<style>
.ppp-passwd_confirm{
	margin-top: 2em;
	margin-bottom: 3em;
	padding: 2em;
	background-color: #eee;
	border: 1px solid #ddd;
	box-shadow: 1px 1px 1px rgba(0,0,0,0.3);
}
</style>
<form action="" method="post" class="ppp-passwd_confirm">
	<p><strong>閲覧パスワードを入力してください</strong></p>
	<input type="text" name="password">
	<input type="submit" value="認証する">
</form>
EOM;
		}
		return $content;
	}
);
// add_filter(
// 	'the_title',
// 	function($title){
// 		if ( (( is_singular() || is_page()) && in_the_loop() ) && isset($_GET['_ppp']) && !get_query_var( 'ppp_password_check' ) ) {
// 			$title = "パスワード認証";
// 		}
// 		return $title;
// 	}
// );



// 固定カスタムフィールドボックス
function ppp_passwd_add_custom_field() {
	add_meta_box( 'ppp_password', 'Public Post Previewのパスワード', 'ppp_passwd_insert_field', 'post', 'side', 'high');
}
add_action('admin_menu', 'ppp_passwd_add_custom_field' );

// カスタムフィールドの入力エリア
function ppp_passwd_insert_field() {
	global $post;
	echo 'パスワード： <input type="text" name="ppp_passwd" value="'.get_post_meta($post->ID, 'ppp_passwd', true).'" style="100%" />';
}

// カスタムフィsave_cd_fieldsールドの値を保存
function ppp_passwd_save_field ( $post_id ) {
	if(!empty($_POST['ppp_passwd'])){
		update_post_meta($post_id, 'ppp_passwd', $_POST['ppp_passwd'] );
	}else{
		delete_post_meta($post_id, 'ppp_passwd');
	}
}
add_action('save_post', 'ppp_passwd_save_field');