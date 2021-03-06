<?php
require_once('require/backend.php');
require('require/credentials.php');

$db = new DatabaseConnection();

if (!$db->auth()) {
  header('Location: index');
}

session_start();

if (isset($_GET["draftid"])) {
  $draftid = $db->escape_string($_GET["draftid"]);
  $userid = $db->getCurUser();
  //get draft information
  $getDraft = $db->query("SELECT * FROM $drafttable WHERE DRAFTID = '$draftid'");
  //check if draft exists
  if ($getDraft->num_rows > 0) {
    $row = $getDraft->fetch_assoc();
    //draft exists
    //check if draft belongs to user
    if ($row['USERID'] == $userid) {
      //draft belongs to user
      $_SESSION['drafttitle'] = $row['TITLE'];
      $_SESSION['draftcontent'] = $row['CONTENT'];
      $_SESSION['newpostmsg'] = 'Successfully loaded your draft!';
      header('Location: newpost#popup1');
    } else {
      //draft does not belong to user
      $_SESSION['newpostmsg'] = 'You are not the owner of this draft.';
      header('Location: newpost#popup1');
    }
  } else {
    //draft does not exist;
    $_SESSION['newpostmsg'] = 'That draft does not exist.';
    header('Location: newpost#popup1');
  }
  exit();
}

if (isset($_SESSION['drafttitle'])) {
  $drafttitle = $_SESSION['drafttitle'];
  $draftcontent = $_SESSION['draftcontent'];
}

if (isset($_SESSION['newpostmsg'])) {
  $newpostmsg = $_SESSION['newpostmsg'];
}

if (isset($_POST["submit-post"])) {
  $db->createPost(
    $db->getCurUser(), //userid
    $db->escape_string($_POST["title"]), //title
    $db->escape_string($_POST["content"]) //content
  );
  header("Location: index");
}

if (isset($_POST["submit-draft"])) {
  $db->createDraft(
    $db->getCurUser(), //userid
    $db->escape_string($_POST["title"]), //title
    $db->escape_string($_POST["content"]) //content
  );
  header("Location: drafts");
}
?>

<!doctype html>
<html>
<head>
  <?php require 'require/head.php';?>
  <script>applyStyle();</script>
  <link rel="stylesheet" href="style/newpost.css">
  <script src="script/newpost.js"></script>
</head>
<body>

<div id='grid-wrap'>
  <?php require 'require/header.php';?>
  <?php require 'require/sidebar.php'; ?>
  <div id='content'>

    <div id='popup1' class='overlay'>
      <div class='popup'>
        <a class="close" href="#">&times;</a>
        <div class='popup-content'>
          <?php
          if (isset($newpostmsg)) {
              echo $newpostmsg;
            unset($_SESSION['newpostmsg']);
          }
          ?>
        </div>
      </div>
    </div>

    <form id='newpost' action="" method="POST">

      <div id='post-sheet'>

        <div id='title-wrapper'>
          <p class='char-counter' id="titlecharswrapper">200</p>
          <input id="titleField" name="title" type="text" placeholder="Title" maxlength="200" oninput="updateCharsLeft()" autocomplete='off' <?= (isset($drafttitle)) ? "value='$drafttitle'" : '' ?> required autofocus>
        </div>

        <div id='content-wrapper'>
          <p class='char-counter' id="contentcharswrapper">10000</p>
          <textarea id="contentArea" name="content" placeholder="Post content" spellcheck="false" maxlength="10000" oninput="refreshContentArea()" autocomplete='off' required><?php echo (isset($draftcontent)) ? $draftcontent : '' ?></textarea>
        </div>

        <?php
        if(isset($drafttitle) or isset($draftcontent)) {
          unset($_SESSION['drafttitle']);
          unset($_SESSION['draftcontent']);
        }
        ?>

      </div>

      <div id='preview-sheet'>
        <span id="preview" class="md"><p></p></span>
      </div>

      <div id="newpost-expand" class="floating-action-btn" onclick='toggleExpand();' title='Expand this menu'>
        <svg class='svg-24' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d='<?= getSVG('expand-vertical');?>'/></svg>
      </div>

      <div id='expand-wrapper'>

        <div id="newpost-expand-drafts" class="floating-action-btn" title='Access your drafts'>
          <a href='drafts'><svg class='svg-24' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d='<?= getSVG('drafts');?>'/></svg></a>
        </div>

        <button type="submit" id="newpost-expand-newdraft" class="floating-action-btn" name="submit-draft" title='Save as draft'>
          <svg class='svg-24' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d='<?= getSVG('savedraft');?>'/></svg>
        </button>

        <button type="submit" id="newpost-expand-submit" class="floating-action-btn" name="submit-post" title='Submit your post'>
          <svg class='svg-24' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d='<?= getSVG('confirm');?>'/></svg>
        </button>

      </div>
    </form>
  </div>
</div>

<script>
  applyStyle();
  addCtrlEnterListener();
  refreshContentArea();
  rezNewpost();
  updateMD();
</script>
</body>
</html>
