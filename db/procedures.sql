
SET NAMES 'utf8';
SET CHARACTER SET utf8;

USE `hierarchy`;

DELIMITER //  


DROP PROCEDURE IF EXISTS `add_node`//
CREATE PROCEDURE `add_node` (IN name VARCHAR(255), IN parentlft INT(11), 
    IN p_code VARCHAR(10), IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN)
LANGUAGE SQL
BEGIN
    DECLARE lft, rgt INT(11);

    SET lft = parentlft + 1;
    SET rgt = parentlft + 2;

    CALL shift_right(parentlft, 2, 0);

    INSERT INTO `hierarchy` (name, lft, rgt, code, allow_course, allow_user) VALUES (name, lft, rgt, p_code, p_allow_course, p_allow_user);
END//


DROP PROCEDURE IF EXISTS `update_node`//
CREATE PROCEDURE `update_node` (IN p_id INT(11), IN p_name VARCHAR(255), 
    IN nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11), IN parentlft INT(11), 
    IN p_code VARCHAR(10), IN p_allow_course BOOLEAN, IN p_allow_user BOOLEAN)
LANGUAGE SQL  
BEGIN
    UPDATE `hierarchy` SET name = p_name, lft = p_lft, rgt = p_rgt, 
        code = p_code, allow_course = p_allow_course, allow_user = p_allow_user WHERE id = p_id;

    IF nodelft <> parentlft THEN
        CALL move_nodes(nodelft, p_lft, p_rgt);
    END IF;
END//


DROP PROCEDURE IF EXISTS `delete_node`//
CREATE PROCEDURE `delete_node` (IN p_id INT(11))
LANGUAGE SQL  
BEGIN  
    DECLARE p_lft, p_rgt INT(11);

    SELECT lft, rgt INTO p_lft, p_rgt FROM `hierarchy` WHERE id = p_id;
    DELETE FROM `hierarchy` WHERE id = p_id;

    CALL delete_nodes(p_lft, p_rgt);
END//


DROP PROCEDURE IF EXISTS `shift_right`//
CREATE PROCEDURE `shift_right` (IN node INT(11), IN shift INT(11), IN maxrgt INT(11))
LANGUAGE SQL
BEGIN
    IF maxrgt > 0 THEN
        UPDATE `hierarchy` SET rgt = rgt + shift WHERE rgt > node AND rgt <= maxrgt;
    ELSE
        UPDATE `hierarchy` SET rgt = rgt + shift WHERE rgt > node;
    END IF;
      
    IF maxrgt > 0 THEN
        UPDATE `hierarchy` SET lft = lft + shift WHERE lft > node AND lft <= maxrgt;
    ELSE
        UPDATE `hierarchy` SET lft = lft + shift WHERE lft > node;
    END IF;
END//


DROP PROCEDURE IF EXISTS `shift_left`//
CREATE PROCEDURE `shift_left` (IN node INT(11), IN shift INT(11), IN maxrgt INT(11))
LANGUAGE SQL
BEGIN
    IF maxrgt > 0 THEN
        UPDATE `hierarchy` SET rgt = rgt - shift WHERE rgt > node AND rgt <= maxrgt;
    ELSE
        UPDATE `hierarchy` SET rgt = rgt - shift WHERE rgt > node;
    END IF;
      
    IF maxrgt > 0 THEN
        UPDATE `hierarchy` SET lft = lft - shift WHERE lft > node AND lft <= maxrgt;
    ELSE
        UPDATE `hierarchy` SET lft = lft - shift WHERE lft > node;
    END IF;
END//


DROP PROCEDURE IF EXISTS `shift_end`//
CREATE PROCEDURE `shift_end` (IN p_lft INT(11), IN p_rgt INT(11), IN maxrgt INT(11))
LANGUAGE SQL
BEGIN
    UPDATE `hierarchy` 
    SET lft = (lft - (p_lft - 1)) + maxrgt, 
        rgt = (rgt - (p_lft - 1)) + maxrgt WHERE lft BETWEEN p_lft AND p_rgt;
END//


DROP PROCEDURE IF EXISTS `get_maxrgt`//
CREATE PROCEDURE `get_maxrgt` (OUT maxrgt INT(11))
LANGUAGE SQL
BEGIN
    SELECT rgt INTO maxrgt FROM `hierarchy` ORDER BY rgt DESC LIMIT 1;
END//


DROP PROCEDURE IF EXISTS `get_parent`//
CREATE PROCEDURE `get_parent` (IN p_lft INT(11), IN p_rgt INT(11))
LANGUAGE SQL
BEGIN
    SELECT * FROM `hierarchy` WHERE lft < p_lft AND rgt > p_rgt ORDER BY lft DESC LIMIT 1;
END//


DROP PROCEDURE IF EXISTS `delete_nodes`//
CREATE PROCEDURE `delete_nodes` (IN p_lft INT(11), IN p_rgt INT(11))
LANGUAGE SQL
BEGIN
    DECLARE node_width INT(11);
    SET node_width = p_rgt - p_lft + 1;

    DELETE FROM `hierarchy` WHERE lft BETWEEN p_lft AND p_rgt;
    UPDATE `hierarchy` SET rgt = rgt - node_width WHERE rgt > p_rgt;
    UPDATE `hierarchy` SET lft = lft - node_width WHERE lft > p_lft;
END//


DROP PROCEDURE IF EXISTS `move_nodes`//
CREATE PROCEDURE `move_nodes` (INOUT nodelft INT(11), IN p_lft INT(11), IN p_rgt INT(11))
LANGUAGE SQL
BEGIN
    DECLARE node_width, maxrgt INT(11);

    SET node_width = p_rgt - p_lft + 1;
    CALL get_maxrgt(maxrgt);

    CALL shift_end(p_lft, p_rgt, maxrgt);

    IF nodelft = 0 THEN
        CALL shift_left(p_rgt, node_width, 0);
    ELSE
        CALL shift_left(p_rgt, node_width, maxrgt);

        IF p_lft < nodelft THEN
            SET nodelft = nodelft - node_width;
        END IF;

        CALL shift_right(nodelft, node_width, maxrgt);

        UPDATE `hierarchy` SET rgt = (rgt - maxrgt) + nodelft WHERE rgt > maxrgt;
        UPDATE `hierarchy` SET lft = (lft - maxrgt) + nodelft WHERE lft > maxrgt;
    END IF;
END//


DELIMITER ;