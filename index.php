<?php
/*
 *   Copyright (c) 2012 by Thanos Kyritsis
 *
 *   This file is part of eclass-hierarchy.
 *
 *   eclass-hierarchy is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, version 2 of the License.
 *
 *   eclass-hierarchy is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with eclass-hierarchy; if not, write to the Free Software
 *   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

require('config.php');
if (USE_PROCEDURES)
    require('lib/hierarchy5.class.php');
else
    require('lib/hierarchy.class.php');

$tree = new hierarchy(DB_TABLE);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";


if ($action == 'add')
{
    if (isset($_POST['name']) && strlen($_POST['name']) > 0 &&
        isset($_POST['nodelft']) && strlen($_POST['nodelft']) > 0)
    {
        $code = (isset($_POST['code'])) ? $_POST['code'] : null;
        $allow_course = (isset($_POST['allow_course'])) ? 1 : 0;
        $allow_user = (isset($_POST['allow_user'])) ? 1 : 0;
        
        $tree->addNode($_POST['name'], intval($_POST['nodelft']), $code, $allow_course, $allow_user);
    }
}

if ($action == 'edit')
{
    $id = intval($_GET['id']);
    $result = mysql_query("SELECT name, lft, rgt, code, allow_course, allow_user FROM ". DB_TABLE ." WHERE id = '$id'");
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $parentLft = $tree->getParent($row['lft'], $row['rgt']);
    $check_course = ($row['allow_course'] == 1) ? " checked=1 " : '';
    $check_user = ($row['allow_user'] == 1) ? " checked=1 " : '';
}

if ($action == 'update')
{
    if (isset($_POST['id']) && intval($_POST['id']) > 0 &&
        isset($_POST['name']) && strlen($_POST['name']) > 0 &&
        isset($_POST['nodelft']) &&
        isset($_POST['lft']) &&
        isset($_POST['rgt']) &&
        isset($_POST['parentLft']) )
    {
        $code = (isset($_POST['code'])) ? $_POST['code'] : null;
        $allow_course = (isset($_POST['allow_course'])) ? 1 : 0;
        $allow_user = (isset($_POST['allow_user'])) ? 1 : 0;
        
        $tree->updateNode(intval($_POST['id']), $_POST['name'], intval($_POST['nodelft']), 
                intval($_POST['lft']), intval($_POST['rgt']), intval($_POST['parentLft']),
                $code, $allow_course, $allow_user);
    }
}

if ($action == 'delete') 
{
    if (isset($_GET['id']) && intval($_GET['id']) > 0)
    {
        $tree->deleteNode(intval($_GET['id']));
    }
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Hierarchical Data</title>
</head>
<body>
  
<h3>Hierarchical Data</h3>

<table width="500px">
<tr><td>

<div>
<?php if ($action != 'edit') : ?>
  <form action="index.php?action=add" method="post">
    <fieldset title="Add New Node">
      <legend>Add New Node</legend>
        Name: <input type="text" name="name"/> <br/>
        Parent: <?php echo $tree->buildHtmlSelect('name="nodelft"'); ?> <br />
        Code: <input type="text" name="code"/> <br />
        Allow course: <input type='checkbox' name='allow_course' value='1' /> <br />
        Allow user: <input type='checkbox' name='allow_user' value='1' />
        <br/>
        <input type="submit" value="Add"/>
    </fieldset>
  </form>
<?php else : ?>
  <form action="index.php?action=update" method="post">
    <fieldset title="Update Node">
      <legend>Update Node</legend>
        Name: <input type="text" name="name" value="<?php echo $row['name']; ?>"/> <br/>
        Parent: <?php echo $tree->buildHtmlSelect('name="nodelft"', $parentLft['lft'], $id); ?> <br />
        Code: <input type="text" name="code" value="<?php echo $row['code']; ?>"/> <br />
        Allow course: <input type='checkbox' name='allow_course' value='1' <?php echo $check_course; ?> /> <br />
        Allow user: <input type='checkbox' name='allow_user' value='1' <?php echo $check_user; ?> />
        <br/>
        <input type="hidden" name="parentLft" value="<?php echo $parentLft['lft']; ?>"/>
        <input type="hidden" name="lft" value="<?php echo $row['lft']; ?>"/>
        <input type="hidden" name="rgt" value="<?php echo $row['rgt']; ?>"/>
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
        <input type="submit" value="Update"/> or <a href="index.php">Cancel</a>
    </fieldset>
  </form>
<?php endif; ?>
</div>

</td></tr><tr><td>

<div >
  <fieldset title="Nodes List">
    <legend>Nodes List</legend>
      <table width="100%">
        <?php
          $tree_array = $tree->build(array(), 'id');

          foreach($tree_array as $key => $value)
          {
            echo '<tr><td>'. $value .'</td><td><a href="index.php?action=edit&amp;id='. $key .'">Edit</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="index.php?action=delete&id='. $key .'" onClick="return confirm(\'Confirm delete?\')">Delete</a></td></tr>';
          }
        ?>
      </table>
  </fieldset>
</div>

</td></tr><tr><td>

<div >
  <fieldset title="Nodes Tree">
    <legend>Nodes Tree</legend>
    <?php echo $tree->buildHtmlUl(); ?>
  </fieldset>
</div>

</td></tr></table>

Goto: <a href="courses.php">Courses</a>

</body>
</html>
