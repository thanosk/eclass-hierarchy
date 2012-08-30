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

mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
mysql_select_db(DB_DATABASE);
mysql_query("SET NAMES 'utf8'");

echo "digraph uni {\n\n";

$sql = "SELECT * FROM hierarchy ORDER BY id";

$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
    echo "\t". $row['id'] ."[label=\"". $row['name'] ."\"]\n";
}

$result = mysql_query("SELECT * FROM course ORDER BY id");

while ($row = mysql_fetch_assoc($result)) {
    echo "\tc". $row['id'] ."[label=\"". $row['name'] ."\"]\n";
}

echo "\n";

$sql = "SELECT parent.id AS pid, parent.name AS pname, node.id AS nid, node.name AS nname
        FROM hierarchy_depth AS node,
             hierarchy_depth AS parent
        WHERE node.lft BETWEEN parent.lft AND parent.rgt
          AND node.depth - parent.depth = 1";

$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
    echo "\t". $row['pid'] ." -> ". $row['nid'] ."\n";
}

$result = mysql_query("SELECT * FROM course_department ORDER BY id");

while ($row = mysql_fetch_assoc($result)) {
    echo "\t". $row['department'] ." -> c". $row['course'] ."\n";
}

echo "\n}\n";

?>

