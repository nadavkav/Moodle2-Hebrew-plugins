<?php
/**
 * リポジトリアップロード
 *
 * @author VERSION2 Inc.
 * @version $Id: upload.php,v 1.4 2010/02/12 01:20:00 akiococom Exp $
 * @package repository
 */

global $DB;

require_once './SharingCart_Repository.php';
require_once '../../sharing_cart_lib.php';

require_once $CFG->libdir.'/formslib.php';

require_login();

$course_id = optional_param('course', SITEID, PARAM_INT);
$return_to = $CFG->wwwroot.'/course/view.php?id='.$course_id;

try {
	$sharing_cart_id = required_param('id', PARAM_INT);
	
	// 共有アイテムが存在するかチェック
	$sharing_cart = $DB->get_record('sharing_cart', 'id', $sharing_cart_id)
		or print_error('err_shared_id', 'block_sharing_cart', $return_to);
	
	// 自分が所有する共有アイテムかチェック
	$sharing_cart->user == $USER->id
		or print_error('err_capability', 'block_sharing_cart', $return_to);
	
	$config = SharingCart_Repository::getConfig($USER);
	
	// (暫定) リポジトリIDは先頭のものを取得
	$repo_id = 0;
	foreach ($config as $id => $info) {
		if (!empty($info->enabled)) {
			$repo_id = $id;
			break;
		}
	}
	
	if (empty($config[$repo_id]))
		throw new SharingCart_RepositoryException('Repository settings was missing');
	if (empty($config[$repo_id]->url))
		throw new SharingCart_RepositoryException('Repository URL was missing');
	if (empty($config[$repo_id]->instance))
		throw new SharingCart_RepositoryException('Repository course ID was missing');
	if (empty($config[$repo_id]->username))
		throw new SharingCart_RepositoryException('Repository username was missing');
	
	$zip_path = make_user_directory($USER->id, true) . '/' . $sharing_cart->file;
	$zip_data = file_get_contents($zip_path);
	
	$form = new MoodleQuickForm('upload', 'post',
		$config[$repo_id]->url.'/course/format/repository/material.php');
	$form->addElement('hidden', 'mode', 'upload');
	$form->addElement('hidden', 'id', $config[$repo_id]->instance);
	$form->addElement('hidden', 'username', $config[$repo_id]->username);
	$form->addElement('hidden', 'password', $config[$repo_id]->password);
	$form->addElement('hidden', 'icon', $sharing_cart->icon);
	$form->addElement('hidden', 'type', $sharing_cart->name);
	$form->addElement('hidden', 'title', $sharing_cart->text);
	$form->addElement('hidden', 'file', base64_encode($zip_data));
	$form->addElement('hidden', 'sha1', sha1($zip_data));
	$form->addElement('hidden', 'usersite', $CFG->wwwroot);
	$form->addElement('hidden', 'sitename', $SITE->fullname);
	
	$icon = sharing_cart_lib::get_icon($sharing_cart->name, $sharing_cart->icon);
	$text = '<span class="icon">'.$icon.'</span><span>'.$sharing_cart->text.'</span>';
	
	$form->addElement('static', NULL, '', $text);
	$form->addElement('static', NULL, '', SharingCart_Repository::getString('confirm_upload'));
	
	$form->addElement('submit', 'upload', SharingCart_Repository::getString('upload'));
	
	SharingCart_Repository::printForm(
		$form,
		SharingCart_Repository::getString('upload_to_repository'),
		$course_id
	);
	
} catch (Exception $e) {
	error((string)$e);
}

?>