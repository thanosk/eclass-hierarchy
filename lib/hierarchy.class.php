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
        $lft = $parentlft + 1;
        $rgt = $parentlft + 2;

        $this->shiftRight($parentlft);

        $query = "INSERT INTO ". $this->dbtable ." (name, lft, rgt, code allow_course, allow_user) "
                ."VALUES ('$name', '$lft', '$rgt', '$code', '$allow_course', '$allow_user')";
        mysql_query($query);
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
     * @param string $code
     * @param int $allow_course
     * @param int $allow_user
     */
    function updateNode($id, $name, $nodelft, $lft, $rgt, $parentlft, $code, $allow_course, $allow_user)
    {
        $query = "UPDATE ". $this->dbtable ." SET name = '$name',  lft = '$lft', rgt = '$rgt',
                code = '$code', allow_course = '$allow_course', allow_user = '$allow_user' WHERE id = '$id'";
        mysql_query($query);

        if($nodelft != $parentlft)
        {
            $this->moveNodes($nodelft, $lft, $rgt);
        }
    }
    
    /**
     * Delete a node from the tree
     * 
     * @param int $id - The id of the node to delete
     */
    function deleteNode($id)
    {
        $result = mysql_query("SELECT lft, rgt FROM ". $this->dbtable ." WHERE id = '$id'");

        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        $lft = $row['lft'];
        $rgt = $row['rgt'];

        mysql_query("DELETE FROM ". $this->dbtable ." WHERE id = '$id'");

        $this->delete($lft, $rgt);
    }
    
    /**
     * Shift tree nodes to the right
     *
     * @param int $node - This is the left of the node after which we want to shift
     * @param int $shift - Length of shift
     * @param int $maxrgt - Maximum rgt value in the tree                       
     */    
    public function shiftRight($node, $shift = 2, $maxrgt = 0)
    {
        $this->shift('+', $node, $shift, $maxrgt);     
    }
      
    /**
     * Shift tree nodes to the left
     * 
     * @param int $node - This is the left of the node after which we want to shift
     * @param int $shift - Length of shift
     * @param int $maxrgt - Maximum rgt value in the tree
     */    
    public function shiftLeft($node, $shift = 2, $maxrgt = 0)
    {
        $this->shift('-', $node, $shift, $maxrgt);     
    }
    
    //shift nodes to the end    
    public function shiftEnd($lft, $rgt, $maxrgt)
    {
        $query = "UPDATE ". $this->dbtable ." SET  lft = (lft - ". ($lft-1) .")+". $maxrgt .", rgt = (rgt - ". ($lft-1) .")+". $maxrgt ." WHERE lft BETWEEN ". $lft ." AND ". $rgt;
        mysql_query($query);
    }
    
    /**
     * Shift tree nodes 
     * 
     * @param string $action - '+' for shift to the right, '-' for shift to the left
     * @param int $node - This is the left of the node after which we want to shift
     * @param int $shift - Length of shift
     * @param int $maxrgt - Maximum rgt value in the tree
     */
    public function shift($action, $node, $shift = 2, $maxrgt = 0)
    {
        $query = "UPDATE ". $this->dbtable ." SET rgt = rgt ". $action ." ". $shift ." WHERE rgt > ". $node . ($maxrgt>0 ? " AND rgt<=" . $maxrgt : '');
        mysql_query($query);
      
        $query = "UPDATE ". $this->dbtable ." SET lft = lft ". $action ." ". $shift ." WHERE lft > ". $node . ($maxrgt>0 ? " AND lft<=" . $maxrgt : '');
        mysql_query($query);
    } 
    
    /**
     * Get maximum rgt value in the tree
     * 
     * @return int 
     */
    public function getMaxRgt()
    {
        $result = mysql_query("SELECT rgt FROM ". $this->dbtable ." ORDER BY rgt desc limit 1");
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        
        return $row['rgt'];
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
        $result = mysql_query($query);
        
        return mysql_fetch_array($result, MYSQL_ASSOC);
    }
    
    /**
     * Delete nodes
     * 
     * @param int $lft - left node of child
     * @param int $rgt - right node of child
     */
    public function delete($lft, $rgt)
    {
        $nodeWidth = $rgt - $lft + 1;
              
        $query = "DELETE FROM ". $this->dbtable ."  WHERE lft BETWEEN ". $lft ." AND ". $rgt;
        mysql_query($query);
        
        $query = "UPDATE ". $this->dbtable ."  SET rgt = rgt - ". $nodeWidth ." WHERE rgt > ". $rgt;
        mysql_query($query);
        
        $query = "UPDATE ". $this->dbtable ."  SET lft = lft - ". $nodeWidth ." WHERE lft > ". $lft;
        mysql_query($query);
    }
    
    //move nodes
    public function moveNodes($nodelft, $lft, $rgt)
    {
        $nodeWidth = $rgt - $lft + 1;
        $maxrgt = $this->getMaxRgt();
        
        $this->shiftEnd($lft, $rgt, $maxrgt);
        
        if($nodelft==0)
        {
            $this->shiftLeft($rgt, $nodeWidth);
        }
        else
        {
            $this->shiftLeft($rgt, $nodeWidth, $maxrgt);
          
            if($lft<$nodelft)
            {
                $nodelft = $nodelft - $nodeWidth;
            }
          
            $this->shiftRight($nodelft, $nodeWidth, $maxrgt);
          
            $query = "UPDATE ". $this->dbtable ." SET rgt = (rgt - ". $maxrgt .") + ". $nodelft ." WHERE rgt > ". $maxrgt;
            mysql_query($query);
        
            $query = "UPDATE ". $this->dbtable ." SET lft = (lft - ". $maxrgt .") + ". $nodelft ." WHERE lft > ". $maxrgt;
            mysql_query($query);
        }    
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
        $result = mysql_query($query);
        
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
    
    /**
     * Get branch of tree
     *
     * @param string $left - left node of branch     
     */         
    public function getBranch($left)
    {
        $where_str = '';
        
        if($left > 0)    
        {
            $query = "SELECT rgt FROM ". $this->dbtable ." WHERE lft = $left";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result, MYSQL_ASSOC);

            $where_str = ' AND parent.lft > '. $left .' AND parent.rgt < '. $row['rgt'];
        }
              
        $query = "SELECT node.rgt, node.lft, (node.rgt-node.lft-1) as length, node.id, node.sort_order, (COUNT(parent.name) - 1) AS depth, CONCAT( REPEAT( ' - ', (COUNT(parent.name) - 1) ), node.name) AS name FROM ". $this->dbtable ." AS node,  ". $this->dbtable ." AS parent  WHERE node.lft BETWEEN parent.lft AND parent.rgt ". $where_str ." GROUP BY node.id HAVING depth=0 ORDER BY node.lft";
        
        return mysql_query($query);
    } 
    
    /**
     * Sort tree
     */
    public function sortTree()
    {      
        $this->ksortTreeArray($this->getTreeArray());
    } 
    
    /**
     * Get tree array    
     */    
    public function getTreeArray($left=0, $items_array = array())
    {
        $db_query = $this->getBranch($left);
        
        while($branch = mysql_fetch_array($db_query, MYSQL_ASSOC))
        {
            $sort_order = $branch['sort_order'];
            $name       = $branch['name'];
            $id         = $branch['id'];
            $length     = $branch['length'];
            $lft        = $branch['lft'];

            $items_array[ $sort_order .'_'. $name .'_'. $id] = array('id'=> $id, 'length' => $length);

            if($length > 0)
            {
                $items_array[ $sort_order .'_'. $name .'_'. $id]['subcat'] = $this->getTreeArray($lft);
            }
        }
        
        return $items_array;
    }
    
    /**
     * Sort tree array by array key and
     * automatically regenerate left and right nodes        
     */    
    public function ksortTreeArray($array_value, $lft=0)
    {
        $key = key($array_value);
        
        if($key[0]=='0')
        {
            ksort($array_value, SORT_STRING);
        }
        else
        {
            ksort($array_value, SORT_NUMERIC);
        }
                      
        foreach($array_value as $key=>$value)
        {                
            $array_value[$key]['lft'] = $lft+1;        
            $array_value[$key]['rgt'] = $lft+2+$array_value[$key]['length'];

            $query = "UPDATE ". $this->dbtable ." SET lft = '". $array_value[$key]['lft'] ."', rgt = '". $array_value[$key]['rgt'] ."' WHERE id = '". $array_value[$key]['id'] ."'";
            mysql_query($query);

            if($array_value[$key]['length']>0)
            {
                $lft += 1;
            }
            else
            {
                $lft += 2;
            }        

            if(isset($array_value[$key]['subcat'])){           
                $array_value[$key]['subcat'] = $this->ksortTreeArray($array_value[$key]['subcat'], $lft);
            }

            if($array_value[$key]['length']>0)
            {
                $lft += $array_value[$key]['length']+1;
            }         
        }
        
        reset($array_value); 
                       
        return $array_value; 
    }    
}
?>
