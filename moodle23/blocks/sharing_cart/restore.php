<?php

require_once '../../config.php';

 require_once("XML/Parser.php");

class XMLFileEdit extends XML_Parser {
  protected $tagname;
  protected $tagvalue;
  protected $filename;
  protected $current_element;
  protected $tempfile;
  
  function XMLFileEdit($filename, $tagname, $tagvalue)
  {
    parent::XML_Parser();
    $this->tagname = $tagname;
    $this->tagvalue = $tagvalue;
    $this->filename = $filename;
  }

  function startHandler($xp, $name, $attribs)
  {
    $name = strtolower($name);
    $this->current_element = $name;
    fwrite($this->tempfile, "<".$name);
    foreach ($attribs as $attr_name=>$attr_value)
      fwrite($this->tempfile," ".strtolower($attr_name)."=\"".$attr_value."\"");
    fwrite($this->tempfile, ">");
  }
  function endHandler($xp, $name)
  {
    $name = strtolower($name);
    fwrite($this->tempfile, "</".$name.">");
  }

 function cdataHandler($xp, $cdata)
  {
    if ($this->current_element == $this->tagname)
      fwrite($this->tempfile, $this->tagvalue);
    else
      fwrite($this->tempfile, $cdata);
  }

 function execute()
 {
   $this->setInputFile($this->filename);
   $this->tempfile = fopen($this->filename.".tmp", 'w');
   fwrite($this->tempfile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
   $this->parse();
   fclose($this->tempfile);
   rename($this->filename.".tmp", $this->filename);
 }
}


//error_reporting(E_ALL);

require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once './shared/SharingCart_Restore.php';
require_once './sharing_cart_table.php';

$sc_id      = required_param('id', PARAM_INT);
$course_id  = required_param('course', PARAM_INT);
$section_id = required_param('section', PARAM_INT);
$return_to  = urldecode(required_param('return', PARAM_LOCALURL));

// 共有アイテムが存在するかチェック
$sharing_cart = sharing_cart_table::get_record_by_id($sc_id)
    or print_error('err_shared_id', 'block_sharing_cart', $return_to);

// 自分が所有する共有アイテムかチェック
$sharing_cart->userid == $USER->id
    or print_error('err_capability', 'block_sharing_cart', $return_to);

// ZIPファイル名取得
// $zip_name = $sharing_cart->file;

$fs = get_file_storage();
$file = $fs->get_file_by_id($sharing_cart->fileid);
$packer = get_file_packer('application/zip');
$packer->extract_to_pathname($file, $CFG->dataroot."/temp/backup/sharing_cart");

$rc = new restore_controller("sharing_cart", $course_id, backup::INTERACTIVE_NO,
			     backup::MODE_IMPORT, $USER->id, backup::TARGET_CURRENT_ADDING);

if ($handle = opendir( $CFG->dataroot."/temp/backup/sharing_cart/activities")) {
  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != "..") {
      $xed = new XMLFileEdit($CFG->dataroot."/temp/backup/sharing_cart/activities/".$file."/module.xml"
			     , "sectionnumber", $section_id);
      $xed->execute();
    }
  }
}

$rc->execute_precheck(true);
// $rc->get_precheck_results();
$rc->execute_plan();
redirect($return_to);

try {

    // リストアオブジェクト (※ $restore は Moodle グローバル変数として予約されているので使用不可)
    $worker = new SharingCart_Restore($course_id, $section_id);

    // サイレントモード
    $worker->setSilent();

    // 設定開始
    $worker->beginPreferences();

    // ZIPファイル名設定
    //  $worker->setZipName($zip_name);

    // 設定完了
    $worker->endPreferences();

    // リストア実行
    $worker->execute();


    if ($worker->succeeded()) {
        // 成功：リダイレクト
        redirect($return_to);
    } else {
        // 失敗：「続行」画面
        print_continue($return_to);
    }

} catch (SharingCart_CourseException $e) {
    //print_error('err_course_id', 'block_sharing_cart', $return_to);
    error((string)$e); // デバッグ用に詳細メッセージを表示

} catch (SharingCart_SectionException $e) {
    //print_error('err_section_id', 'block_sharing_cart', $return_to);
    error((string)$e); // デバッグ用に詳細メッセージを表示

} catch (SharingCart_ModuleException $e) {
    //print_error('err_module_id', 'block_sharing_cart', $return_to);
    error((string)$e); // デバッグ用に詳細メッセージを表示

} catch (SharingCart_XmlException $e) {
    //print_error('err_invalid_xml', 'block_sharing_cart', $return_to);
    error((string)$e); // デバッグ用に詳細メッセージを表示

} catch (SharingCart_Exception $e) {
    //print_error('err_backup', 'block_sharing_cart', $return_to);
    error((string)$e); // デバッグ用に詳細メッセージを表示

}

?>