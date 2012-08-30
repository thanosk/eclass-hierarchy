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

class course {

    private $ctable;
    private $typetable;
    private $istypetable;
    private $departmenttable;
    private $allowmultidep;
    
    /**
     * Constructor
     *
     * @param string $ctable - Name of courses table
     * @param string $typetable - Name of courses types table
     * @param string $istype - Name of course <-> course_type lookup table
     * @param string $deptable - Name of course <-> department lookup table
     * @param boolean $allowmultidep - control flag for multiple relation between courses and departments
     */    
    public function course($ctable, $typetable, $istype, $deptable, $allowmultidep)
    {
        $this->ctable = $ctable;
        $this->typetable = $typetable;
        $this->istypetable = $istype;
        $this->departmenttable = $deptable;
        $this->allowmultidep = $allowmultidep;
    }
    
    /**
     * Add a course
     * 
     * @param string $name
     * @param array $types
     * @param array $departments
     */
    public function add($name, $types, $departments)
    {
        mysql_query("INSERT INTO ". $this->ctable ." (name) VALUES ('$name')");
        
        $this->refresh(mysql_insert_id(), $types, $departments);
    }
    
    /**
     * Update a course
     * 
     * @param int $id
     * @param string $name
     * @param array $types
     * @param array $departments
     */
    public function update($id, $name, $types, $departments)
    {
        mysql_query("UPDATE ". $this->ctable ." SET name = '$name' WHERE id = '$id'");
        
        $this->refresh($id, $types, $departments);
    }
    
    /**
     * Refresh types and departments of a course
     * 
     * @param int $id
     * @param array $types
     * @param array $departments
     */
    private function refresh($id, $types, $departments)
    {
        mysql_query("DELETE FROM $this->istypetable WHERE course = '$id'");
        mysql_query("DELETE FROM $this->departmenttable WHERE course = '$id'");
        
        if ($types != null)
        {
            foreach ($types as $key => $type)
            {
                mysql_query("INSERT INTO $this->istypetable (course, course_type) VALUES ($id, $type)");
            }
        }
        
        if ($departments != null)
        {
            foreach ($departments as $key => $department)
            {
                mysql_query("INSERT INTO $this->departmenttable (course, department) VALUES ($id, $department)");
            }
        }
    }
    
    /**
     * Delete course
     * 
     * @param int $id - The id of the course to delete
     */
    public function delete($id)
    {
        mysql_query("DELETE FROM $this->istypetable WHERE course = '$id'");
        mysql_query("DELETE FROM $this->departmenttable WHERE course = '$id'");
        mysql_query("DELETE FROM $this->ctable WHERE id = '$id'");
    }
    
    /**
     * Build courses array
     *
     * @return array
     */         
    public function build()
    {             
        $result = mysql_query("SELECT id, name FROM $this->ctable ORDER BY name ASC");
        
        $course_array = array();
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $course_array[$row['id']] = $row['name'];
        }  
        
        return $course_array;  
    }
    
    /**
     * Build array with all course types
     * 
     * @return array 
     */
    public function buildTypes()
    {
        $result = mysql_query("SELECT id, name FROM $this->typetable ORDER BY id ASC");
        
        $types = array();
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $types[$row['id']] = $row['name'];
        }  
        
        return $types;
    }
    
    /**
     * Build array with the types a specific course belongs to
     * 
     * @param int $id
     * 
     * @return array
     */
    private function buildCourseTypes($id)
    {
        $result = mysql_query("SELECT course_type FROM $this->istypetable WHERE course = '$id'");
        
        $coursetypes = array();
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $coursetypes[$row['course_type']] = true;
        }
        
        return $coursetypes;
    }
    
    public function buildTypesCheckboxes($id = null, $name = "coursetypes")
    {
        $html = "";
        
        $types = $this->buildTypes();
        $checkMap = ($id != null) ? $this->buildCourseTypes($id) : null ;
        
        foreach($types as $key => $value)
        {
            $check = (isset($checkMap[$key])) ? " checked='1' " : '';
            $html .= "<input type='checkbox' name='".$name."[]' value='$key' $check />$value";
        }
        
        return $html;
    }
    
    /**
     * Build array with the nodes a specific course belongs to
     * 
     * @param int $id
     * 
     * @return array
     */
    private function buildCourseDepartments($id)
    {
        $result = mysql_query("SELECT department FROM $this->departmenttable WHERE course = '$id'");
        
        $nodes = array();
        
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $nodes[$row['department']] = true;
        }
        
        return $nodes;
    }
    
    public function buildDepartmentSelect($nodes, $id = null, $name = "departments")
    {
        $html = ($this->allowmultidep) ? "<br/>" : "<select name='$name'>";
        
        $checkMap = ($id != null) ? $this->buildCourseDepartments($id) : null ;
        
        foreach($nodes as $key => $value)
        {
            if ($this->allowmultidep)
            {
                $check = (isset($checkMap[$key])) ? " checked='1' " : '';
                $html .= "<input type='checkbox' name='".$name."[]' value='$key' $check />$value <br />";
            }
            else
            {
                $select = (isset($checkMap[$key])) ? " selected " : '';
                $html .= "<option value='$key' $select>$value</option>";
            }
        }
        
        $html .= ($this->allowmultidep) ? "" : "</select>";
        
        return $html;
    }
}
?>
