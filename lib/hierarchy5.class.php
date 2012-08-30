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

class hierarchy {

    public $dbtable;
    
    /**
     * Constructor
     *
     * @param string $dbtable - Name of table with tree nodes
     */    
    function hierarchy($dbtable)
    {
        $this->dbtable = $dbtable;
    }
    
    /**
     * Add a node to the tree
     * 
     * @param string $name - The new node name
     * @param int $parentlft - The new node's parent lft
     * @param string $code - The new node code
     * @param int $allow_course
     * @param int $allow_user
     */
    function addNode($name, $parentlft, $code, $allow_course, $allow_user)
    {
        $this->db_query("CALL add_node('$name', $parentlft, '$code', $allow_course, $allow_user)");
    }
    
    /**
     * Update a tree node
     * 
     * @param int $id
     * @param string $name
     * @param int $nodelft
     * @param int $lft
     * @param int $rgt
     * @param int $parentlft
     * param string $code
     * @param int $allow_course
     * @param int $allow_user
     */
    function updateNode($id, $name, $nodelft, $lft, $rgt, $parentlft, $code, $allow_course, $allow_user)
    {
        $this->db_query("CALL update_node($id, '$name', $nodelft, $lft, $rgt, $parentlft, '$code', $allow_course, $allow_user)");
    }
    
    /**
     * Delete a node from the tree
     * 
     * @param int $id - The id of the node to delete
     */
    function deleteNode($id)
    {
        $this->db_query("CALL delete_node($id)");
    }
    
    /**
     * Get child's parent
     *
     * @param int $lft - left node of child
     * @param int $rgt - right node of child
     * 
     * @return array
     */
    public function getParent($lft, $rgt)
    {
        $query = "SELECT * FROM ". $this->dbtable ." WHERE lft < '". $lft ."' AND rgt > '". $rgt ."' ORDER BY lft DESC LIMIT 1";
        $result = $this->db_query($query);
        
        return mysql_fetch_array($result, MYSQL_ASSOC);
    }
    
    /**
     * Build tree array
     *
     * @param string $useKey - key for return array, can be 'left' node or 'id'
     * @param int $exclude - the id of the subtree parent node we want to exclude from the dropdown select
     */       
    public function build($tree_array = array('0' => 'Top'), $useKey = 'lft', $exclude = null)
    {
        if ($exclude != null)
        {
            $result = mysql_query("SELECT * FROM ". DB_TABLE ." WHERE id = '$exclude'");
            $row = mysql_fetch_array($result, MYSQL_ASSOC);
            
            $query = "SELECT node.id, node.lft AS lft, 
                CONCAT( REPEAT( '&nbsp;-&nbsp;', (COUNT(parent.name) - 1) ), node.name) AS name 
                FROM ". $this->dbtable ." AS node, ". $this->dbtable ." AS parent 
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                    AND (node.lft < ". $row['lft'] ." OR node.lft > ". $row['rgt'] .")
                    GROUP BY node.id 
                    ORDER BY node.lft";
        }
        else
        {
            $query = "SELECT node.id, node.lft AS lft, 
                CONCAT( REPEAT( '&nbsp;-&nbsp;', (COUNT(parent.name) - 1) ), node.name) AS name 
                FROM ". $this->dbtable ." AS node, ". $this->dbtable ." AS parent 
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                    GROUP BY node.id 
                    ORDER BY node.lft";
        }
        
        $result = $this->db_query($query);
              
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            switch($useKey)
            {
                case 'lft':
                    $tree_array[$row['lft']] = $row['name'];
                    break;
                case 'id':
                    $tree_array[$row['id']] = $row['name'];
                    break;
            }
        }  
        
        return $tree_array;  
    }
    
    /**
     * Build tree using <ul><li> html tags
     *
     * @param string $params - for any html params for tag <ul>
     * 
     * @return string $html - html output
     */
    public function buildHtmlUl($params = "")
    {
        $html = '<ul ' . $params . '>' . "\n";
        $current_depth = 0;
        
        $query = "SELECT node.name AS name, (COUNT(parent.name) - 1) AS depth FROM ". $this->dbtable ." AS node,  ". $this->dbtable ." AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt GROUP BY node.id ORDER BY node.lft";
        $result = $this->db_query($query);
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            if($row['depth'] > $current_depth)
            {
                $html = substr($html,0,-6);
                $html .= '<ul>' . "\n";

                $current_depth = $row['depth'];
            }
          
            if($row['depth'] < $current_depth)
            {
                for($i=$current_depth; $i>$row['depth']; $i--)
                {
                    $html .= '</ul></li>' . "\n";
                }

                $current_depth = $row['depth'];
            }
        
            $html .= '<li>' . $row['name'] . '</li>' . "\n";
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Build tree using <select> html tags
     *
     * @param string $params - parameters for <select> tag
     * @param int $exclude - the id of the subtree parent node we want to exclude from the dropdown select
     * 
     * @return string $html - html output
     */
    public function buildHtmlSelect($params = "", $default='', $exclude = null)
    {
        $html = '<select ' . $params . '>' . "\n";
        
        $tree_array = $this->build(array('0' => 'Top'), 'lft', $exclude);
        
        foreach($tree_array as $key => $value)
        {
            $html .= '<option value="'. $key .'" ' .($default==$key ? 'selected':'') .'>'. $value .'</option>';
        }
        
        $html .= '</select>' . "\n";
        
        return $html;
    }
    
    /**
     * Build simple tree array
     *
     * @param string $where
     */
    public function buildSimple($where = null)
    {
        $result = $this->db_query("SELECT id, name FROM $this->dbtable ". $where);
        
        $nodes = array();
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $nodes[$row['id']] = $row['name'];
        }  
        
        return $nodes;  
    }
    
    private function db_query($sql) {
        $result = mysql_query($sql);
        if (!$result)
            die('Invalid query: ' . mysql_error());
        return $result;
    }
}
?>
