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

//database settings
define('DB_SERVER', 'localhost');
define('DB_SERVER_USERNAME', 'hierarchy');
define('DB_SERVER_PASSWORD', 'hierarchy');
define('DB_DATABASE', 'hierarchy');
define('DB_TABLE', 'hierarchy');
define('COURSE_TABLE', 'course');
define('COURSE_TYPE_TABLE', 'course_type');
define('COURSE_IS_TYPE_TABLE', 'course_is_type');
define('COURSE_DEPARTMENT_TABLE', 'course_department');

define('USE_PROCEDURES', true);
define('CONFIG_MULTI_CDEP', true);

mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
mysql_select_db(DB_DATABASE);
mysql_query("SET NAMES 'utf8'");

?>
