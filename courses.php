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
require('lib/course.class.php');
if (USE_PROCEDURES)
    require('lib/hierarchy5.class.php');
else
    require('lib/hierarchy.class.php');
   
$tree = new hierarchy(DB_TABLE);
$course = new course(COURSE_TABLE, COURSE_TYPE_TABLE, COURSE_IS_TYPE_TABLE, COURSE_DEPARTMENT_TABLE, CONFIG_MULTI_CDEP);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";


if ($action == 'add')
{
    if (isset($_POST['name']) && strlen($_POST['name']) > 0)
    {
        $types = (isset($_POST['coursetypes'])) ? $_POST['coursetypes'] : NULL;
        $departments = (isset($_POST['departments'])) ? $_POST['departments'] : NULL;
        
        $course->add($_POST['name'], $types, $departments);
    }
}

if ($action == 'edit')
{
    $id = intval($_GET['id']);
    $result = mysql_query("SELECT name FROM ". COURSE_TABLE ." WHERE id = '$id'");
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
}

if ($action == 'update')
{
    if (isset($_POST['id']) && intval($_POST['id']) > 0 &&
        isset($_POST['name']) && strlen($_POST['name']) > 0 )
    {
        $types = (isset($_POST['coursetypes'])) ? $_POST['coursetypes'] : NULL;
        $departments = (isset($_POST['departments'])) ? $_POST['departments'] : NULL;
        
        $course->update(intval($_POST['id']), $_POST['name'], $types, $departments);
    }
}

if ($action == 'delete') 
{
    if (isset($_GET['id']) && intval($_GET['id']) > 0)
    {
        $course->delete(intval($_GET['id']));
    }
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Courses</title>
</head>
<body>
  
<h3>Courses</h3>

<table width="500px">
<tr><td>

<div>
<?php if ($action != 'edit') : ?>
  <form action="courses.php?action=add" method="post">
    <fieldset title="Add New Course">
      <legend>Add New Course</legend>
        Name: <input type="text" name="name"/> <br/>
        Type: <?php echo $course->buildTypesCheckboxes(); ?> <br/>
        Department: <?php echo $course->buildDepartmentSelect($tree->buildSimple("WHERE allow_course = true")); ?>
        <br/>
        <input type="submit" value="Add"/>
    </fieldset>
  </form>
<?php else : ?>
  <form action="courses.php?action=update" method="post">
    <fieldset title="Update Course">
      <legend>Update Course</legend>
        Name: <input type="text" name="name" value="<?php echo $row['name']; ?>"/> <br/>
        Type: <?php echo $course->buildTypesCheckboxes($id); ?> <br/>
        Department: <?php echo $course->buildDepartmentSelect($tree->buildSimple("WHERE allow_course = true"), $id); ?>
        <br/>
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
        <input type="submit" value="Update"/> or <a href="courses.php">Cancel</a>
    </fieldset>
  </form>
<?php endif; ?>
</div>

</td></tr><tr><td>

<div >
  <fieldset title="Courses List">
    <legend>Courses List</legend>
      <table width="100%">
        <?php
          $course_array = $course->build();

          foreach($course_array as $key => $value)
          {
            echo '<tr><td>'. $value .'</td><td><a href="courses.php?action=edit&amp;id='. $key .'">Edit</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="courses.php?action=delete&id='. $key .'" onClick="return confirm(\'Confirm delete?\')">Delete</a></td></tr>';
          }
        ?>
      </table>
  </fieldset>
</div>

</td></tr></table>

Goto: <a href="index.php">Nodes</a>

</body>
</html>
