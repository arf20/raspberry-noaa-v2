<?php

namespace App\Controllers;

use Lib\View;
use Config\Config;

class AdminController extends \Lib\Controller {
  public function indexAction($args) {
    $capture = $this->loadModel('Capture');
    $total_pages = $capture->totalPages(Config::ADMIN_CAPTURES_PER_PAGE);

    # pagination - and check for sanity
    $page_number = 1;
    if (array_key_exists('page_no', $args) and $args['page_no'] > 0) $page_number = $args['page_no'];
    if ($page_number > $total_pages) $page_number = $total_pages;

    $capture->getList($page_number, Config::ADMIN_CAPTURES_PER_PAGE);
    $args = array_merge($args, array('capture' => $capture,
                                     'cur_page' => $page_number,
                                     'page_count' => $total_pages));

    View::renderTemplate('Admin/index.html', $args);
  }

  # TODO: This is not very DRY between this and the above function - do
  #       something about this in the future
  public function deleteCaptureAction($args) {
    $capture = $this->loadModel('Capture');
    $pass = $this->loadModel('Pass');

    # attempt to delete the user-specified capture
    $status_msg = 'Fail';
    if (array_key_exists('id', $args) and $args['id'] > 0) {
      $capture_id = $args['id'];
      $capture->getEnhancements($capture_id);
      $capture->getImagePath($capture_id);

      # delete images from disk
      foreach ($capture->enhancements as $enhancement) {
        $img = Config::IMAGE_PATH . '/' . $capture->image_path . $enhancement;
        $thumb = Config::THUMB_PATH . '/' . $capture->image_path . $enhancement;
        echo $img . "<br>";
        echo $thumb . "<br>";

        try {
          if (file_exists($img)) { unlink($img); }
        } catch (exception $e) {
          error_log("Could not delete file: " . $img . " - " . $e);
        }

        try {
          if (file_exists($thumb)) { unlink($thumb); }
        } catch (exception $e) {
          error_log("Could not delete file: " . $thumb . " - " . $e);
        }
      }

      # remove capture and pass records from database
      $capture->getStartEpoch($capture_id);
      echo "Capture ID: " . $capture_id . "<br>";
      echo "Capture Epoch start: " . $capture->start_epoch . "<br>";
      echo get_current_user();
      echo posix_getpwuid(posix_geteuid())['name'];
      $capture->deleteById($capture_id);
      $pass->deleteByPassStart($capture->start_epoch);
      $status_msg = 'Success';
    } else {
      $status_msg = lang['fail_delete_missing_id'];
    }

    $total_pages = $capture->totalPages(Config::ADMIN_CAPTURES_PER_PAGE);

    # pagination - and check for sanity
    $page_number = 1;
    if (array_key_exists('page_no', $args) and $args['page_no'] > 0) $page_number = $args['page_no'];
    if ($page_number > $total_pages) $page_number = $total_pages;

    $capture->getList($page_number, Config::ADMIN_CAPTURES_PER_PAGE);
    $args = array_merge($args, array('capture' => $capture,
                                     'cur_page' => $page_number,
                                     'page_count' => $total_pages,
                                     'status_msg' => $status_msg));

    View::renderTemplate('Admin/index.html', $args);
  }
}

?>
