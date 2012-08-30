
SET NAMES 'utf8';
SET CHARACTER SET utf8;
DROP DATABASE IF EXISTS `hierarchy`;
CREATE DATABASE IF NOT EXISTS `hierarchy` CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `hierarchy`;

CREATE TABLE hierarchy (
    id int(11) NOT NULL auto_increment PRIMARY KEY,
    name varchar(100) NOT NULL,
    lft int(11) NOT NULL,
    rgt int(11) NOT NULL,
    code varchar(10),
    allow_course boolean not null default false,
    allow_user boolean NOT NULL default false
);

-- COURSES --
CREATE TABLE course_type (
    id int(11) NOT NULL auto_increment PRIMARY KEY,
    name varchar(255) NOT NULL
);

CREATE TABLE course (
    id int(11) NOT NULL auto_increment PRIMARY KEY,
    name varchar(255) default NULL
);

CREATE TABLE course_is_type (
    id int(11) NOT NULL auto_increment PRIMARY KEY,
    course int(11) NOT NULL references course(id),
    course_type int(11) NOT NULL references course_type(id)
);

CREATE TABLE course_department (
    id int(11) NOT NULL auto_increment PRIMARY KEY,
    course int(11) NOT NULL references course(id),
    department int(11) NOT NULL references hierarchy(id)
);

-- USERS --
CREATE TABLE user (
    user_id mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY,
    username varchar(50) NOT NULL,
    department int(11)
);

CREATE TABLE user_department (
    id int(11) NOT NULL auto_increment PRIMARY KEY,
    user mediumint(8) unsigned NOT NULL references user(user_id),
    department int(11) NOT NULL references hierarchy(id)
);


CREATE VIEW hierarchy_depth AS
SELECT node.id, node.name, node.lft, node.rgt, COUNT(parent.id) - 1 AS depth
FROM hierarchy AS node,
     hierarchy AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt
GROUP BY node.id
ORDER BY node.lft
